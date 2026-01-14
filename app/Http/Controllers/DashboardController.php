<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            // Summary Metrics
            'total_visitors' => $this->getTotalVisitors(),
            'total_revenue' => $this->getTotalRevenue(),
            'avg_daily_visitors' => $this->getAvgDailyVisitors(),
            'peak_ratio' => $this->getPeakRatio(),

            // Trend Analysis
            'trend_data' => $this->getTrendData(),
            'trend_insight' => $this->getTrendInsight(),

            // Holiday Impact
            'holiday_impact' => $this->getHolidayImpact(),

            // Forecast
            'forecast_data' => $this->getForecastData(),
            'forecast_details' => $this->getForecastDetails(),
        ];

        return view('pages.dashboard', $data);
    }

    private function getTotalVisitors()
    {
        $sixMonthsAgo = Carbon::now()->subMonths(6);
        return OrderItem::whereHas('order', function($q) use ($sixMonthsAgo) {
            $q->where('transaction_time', '>=', $sixMonthsAgo);
        })->sum('quantity') ?? 0;
    }

    private function getTotalRevenue()
    {
        $sixMonthsAgo = Carbon::now()->subMonths(6);
        return Transaction::where('transaction_time', '>=', $sixMonthsAgo)->sum('amount') ?? 0;
    }

    private function getAvgDailyVisitors()
    {
        $sixMonthsAgo = Carbon::now()->subMonths(6);
        $days = Carbon::now()->diffInDays($sixMonthsAgo);
        $total = $this->getTotalVisitors();
        return $days > 0 ? round($total / $days) : 0;
    }

    private function getPeakRatio()
    {
        $sixMonthsAgo = Carbon::now()->subMonths(6);

        $weekendAvg = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.transaction_time', '>=', $sixMonthsAgo)
            ->whereIn(DB::raw('DAYOFWEEK(orders.transaction_time)'), [1, 7])
            ->selectRaw('AVG(order_items.quantity) as avg')
            ->value('avg') ?? 1;

        $weekdayAvg = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.transaction_time', '>=', $sixMonthsAgo)
            ->whereNotIn(DB::raw('DAYOFWEEK(orders.transaction_time)'), [1, 7])
            ->selectRaw('AVG(order_items.quantity) as avg')
            ->value('avg') ?? 1;

        return $weekdayAvg > 0 ? round($weekendAvg / $weekdayAvg, 1) : 1;
    }

    private function getTrendData()
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();

            $total = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereBetween('orders.transaction_time', [$startOfMonth, $endOfMonth])
                ->sum('order_items.quantity') ?? 0;

            $weekend = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereBetween('orders.transaction_time', [$startOfMonth, $endOfMonth])
                ->whereIn(DB::raw('DAYOFWEEK(orders.transaction_time)'), [1, 7])
                ->sum('order_items.quantity') ?? 0;

            $weekday = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereBetween('orders.transaction_time', [$startOfMonth, $endOfMonth])
                ->whereNotIn(DB::raw('DAYOFWEEK(orders.transaction_time)'), [1, 7])
                ->sum('order_items.quantity') ?? 0;

            $data[] = [
                'month' => $month->format('M Y'),
                'total' => $total,
                'weekend' => $weekend,
                'weekday' => $weekday,
            ];
        }
        return $data;
    }

    private function getTrendInsight()
    {
        $trendData = $this->getTrendData();
        $maxMonth = collect($trendData)->sortByDesc('total')->first();

        if (count($trendData) >= 2) {
            $current = $trendData[count($trendData) - 1]['total'];
            $previous = $trendData[count($trendData) - 2]['total'];
            $change = $previous > 0 ? round((($current - $previous) / $previous) * 100) : 0;
        } else {
            $change = 0;
        }

        return [
            'peak_month' => $maxMonth['month'] ?? '-',
            'last_month_change' => $change,
        ];
    }

    private function getHolidayImpact()
    {
        $holidays = [
            ['name' => 'Tahun Baru', 'date' => '2026-01-01'],
            ['name' => 'Natal', 'date' => '2025-12-25'],
            ['name' => 'Idul Adha', 'date' => '2025-06-17'],
            ['name' => 'Kemerdekaan', 'date' => '2025-08-17'],
        ];

        $result = [];
        foreach ($holidays as $holiday) {
            $holidayVisitors = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereDate('orders.transaction_time', $holiday['date'])
                ->sum('order_items.quantity') ?? 1;

            $normalAvg = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.transaction_time', '>=', Carbon::now()->subMonths(6))
                ->whereNotIn(DB::raw('DAYOFWEEK(orders.transaction_time)'), [1, 7])
                ->selectRaw('AVG(order_items.quantity) as avg')
                ->value('avg') ?? 1;

            $impact = $normalAvg > 0 ? round((($holidayVisitors - $normalAvg) / $normalAvg) * 100) : 0;

            $result[] = [
                'name' => $holiday['name'],
                'holiday' => $holidayVisitors,
                'normal' => round($normalAvg),
                'impact' => $impact,
            ];
        }
        return $result;
    }

    private function getForecastData()
    {
        $baseData = $this->getLast4WeeksData();
        $forecast = [];

        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::now()->addDays($i);
            $isWeekend = $date->isWeekend();
            $isHoliday = Holiday::where('date', $date->format('Y-m-d'))->exists();

            $basePrediction = $isWeekend ? $baseData['weekend_avg'] : $baseData['weekday_avg'];
            $prediction = round($basePrediction * $baseData['growth_rate']);

            if ($isHoliday) {
                $prediction = round($prediction * 2.5);
            }

            $prediction = max($prediction, 3);
            $stdDev = $isWeekend ? $baseData['weekend_std'] : $baseData['weekday_std'];
            $lower = max(0, round($prediction - (2 * $stdDev)));
            $upper = round($prediction + (2 * $stdDev));

            $forecast[] = [
                'day' => $date->translatedFormat('D'),
                'date' => $date->format('Y-m-d'),
                'prediction' => $prediction,
                'lower' => $lower,
                'upper' => $upper,
                'is_weekend' => $isWeekend,
                'is_holiday' => $isHoliday,
            ];
        }
        return $forecast;
    }

    private function getForecastDetails()
    {
        $forecast = $this->getForecastData();
        $details = [];

        foreach ($forecast as $f) {
            $details[] = [
                'day' => Carbon::parse($f['date'])->translatedFormat('D, d M'),
                'prediction' => $f['prediction'],
                'range' => $f['lower'] . ' - ' . $f['upper'],
                'kasir' => ceil($f['prediction'] / 25),
                'revenue' => $f['prediction'] * 25000,
                'is_weekend' => $f['is_weekend'],
                'is_holiday' => $f['is_holiday'],
            ];
        }
        return $details;
    }

    private function getLast4WeeksData()
    {
        $fourWeeksAgo = Carbon::now()->subWeeks(4);

        $dailyData = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.transaction_time', '>=', $fourWeeksAgo)
            ->selectRaw('DATE(orders.transaction_time) as date, SUM(order_items.quantity) as total, DAYOFWEEK(orders.transaction_time) as dow')
            ->groupBy('date', 'dow')
            ->get();

        $weekendData = $dailyData->whereIn('dow', [1, 7]);
        $weekdayData = $dailyData->whereNotIn('dow', [1, 7]);

        return [
            'weekend_avg' => $weekendData->count() > 0 ? round($weekendData->avg('total')) : 30,
            'weekday_avg' => $weekdayData->count() > 0 ? round($weekdayData->avg('total')) : 15,
            'weekend_std' => $weekendData->count() > 0 ? $this->calculateStdDev($weekendData->pluck('total')->toArray()) : 15,
            'weekday_std' => $weekdayData->count() > 0 ? $this->calculateStdDev($weekdayData->pluck('total')->toArray()) : 8,
            'growth_rate' => $this->calculateGrowthRate($dailyData),
        ];
    }

    private function calculateStdDev($values)
    {
        if (count($values) < 2) return 10;

        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(function($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $values)) / count($values);

        return sqrt($variance);
    }

    private function calculateGrowthRate($data)
    {
        if ($data->count() < 7) return 1.0;

        $sorted = $data->sortBy('date');
        $firstWeek = $sorted->take(7)->avg('total');
        $lastWeek = $sorted->reverse()->take(7)->avg('total');

        return $firstWeek > 0 ? max($lastWeek / $firstWeek, 0.5) : 1.0;
    }
}

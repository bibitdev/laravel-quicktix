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
    public function __construct()
    {
        Carbon::setLocale('id');
    }

    private function getDayNameIndonesia($date)
    {
        $days = [
            'Sunday' => 'Min',
            'Monday' => 'Sen',
            'Tuesday' => 'Sel',
            'Wednesday' => 'Rab',
            'Thursday' => 'Kam',
            'Friday' => 'Jum',
            'Saturday' => 'Sab'
        ];
        return $days[$date->format('l')];
    }

    private function getMonthNameIndonesia($date)
    {
        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Ags',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];
        return $months[(int)$date->format('n')];
    }

    public function index()
    {
        $data = [
            // Summary Metrics with Comparison
            'total_visitors' => $this->getTotalVisitors(),
            'total_revenue' => $this->getTotalRevenue(),
            'avg_daily_visitors' => $this->getAvgDailyVisitors(),
            'peak_ratio' => $this->getPeakRatio(),

            // Daily metrics with comparison
            'daily_visitors' => $this->getDailyVisitors(),
            'daily_revenue' => $this->getDailyRevenue(),
            'tickets_sold' => $this->getTicketsSold(),
            'total_transactions' => $this->getTotalTransactions(),

            // Comparison data
            'visitor_comparison' => $this->getVisitorComparison(),
            'revenue_comparison' => $this->getRevenueComparison(),

            // Trend Analysis (1 month)
            'trend_data' => $this->getTrendData(),
            'trend_insight' => $this->getTrendInsight(),

            // Insights
            'insights' => $this->generateInsights(),

            // Holiday Impact
            'holiday_impact' => $this->getHolidayImpact(),

            // Forecast (7 days)
            'forecast_data' => $this->getForecastData(),
            'forecast_details' => $this->getForecastDetails(),

            // Charts data
            'ticket_sales_trend' => $this->getTicketSalesTrend(),
            'revenue_trend' => $this->getRevenueTrend(),
            'payment_methods' => $this->getPaymentMethods(),
        ];

        return view('pages.dashboard', $data);
    }

    private function getDailyVisitors()
    {
        return OrderItem::whereHas('order', function($q) {
            $q->whereDate('transaction_time', Carbon::today());
        })->sum('quantity') ?? 0;
    }

    private function getDailyRevenue()
    {
        return Transaction::whereDate('transaction_time', Carbon::today())->sum('amount') ?? 0;
    }

    private function getTicketsSold()
    {
        return OrderItem::whereHas('order', function($q) {
            $q->whereDate('transaction_time', Carbon::today());
        })->sum('quantity') ?? 0;
    }

    private function getTotalTransactions()
    {
        return Transaction::whereDate('transaction_time', Carbon::today())->count();
    }

    private function getVisitorComparison()
    {
        $today = $this->getDailyVisitors();
        $yesterday = OrderItem::whereHas('order', function($q) {
            $q->whereDate('transaction_time', Carbon::yesterday());
        })->sum('quantity') ?? 1;

        $lastMonth = OrderItem::whereHas('order', function($q) {
            $q->whereMonth('transaction_time', Carbon::now()->subMonth()->month)
                ->whereYear('transaction_time', Carbon::now()->subMonth()->year);
        })->sum('quantity') ?? 1;

        $difference = $today - $yesterday;
        $percentage = $yesterday > 0 ? round((($today - $yesterday) / $yesterday) * 100, 2) : 0;

        return [
            'today' => $today,
            'yesterday' => $yesterday,
            'last_month' => $lastMonth,
            'difference' => $difference,
            'percentage' => $percentage,
            'trend' => $difference >= 0 ? 'up' : 'down',
        ];
    }

    private function getRevenueComparison()
    {
        $today = $this->getDailyRevenue();
        $yesterday = Transaction::whereDate('transaction_time', Carbon::yesterday())->sum('amount') ?? 1;

        $difference = $today - $yesterday;
        $percentage = $yesterday > 0 ? round((($today - $yesterday) / $yesterday) * 100, 2) : 0;

        return [
            'today' => $today,
            'yesterday' => $yesterday,
            'difference' => $difference,
            'percentage' => $percentage,
            'trend' => $difference >= 0 ? 'up' : 'down',
        ];
    }

    private function getTicketSalesTrend()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $total = OrderItem::whereHas('order', function($q) use ($date) {
                $q->whereDate('transaction_time', $date);
            })->sum('quantity') ?? 0;

            $data[] = [
                'date' => $date->format('D, d M'),
                'total' => $total,
            ];
        }
        return $data;
    }

    private function getRevenueTrend()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $total = Transaction::whereDate('transaction_time', $date)->sum('amount') ?? 0;

            $data[] = [
                'date' => $date->format('D, d M'),
                'total' => $total,
            ];
        }
        return $data;
    }

    private function getPaymentMethods()
    {
        return Transaction::select('payment_method', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->whereDate('transaction_time', '>=', Carbon::today()->subDays(7))
            ->groupBy('payment_method')
            ->get()
            ->map(function($item) {
                return [
                    'payment_method' => ucfirst($item->payment_method),
                    'count' => $item->count,
                    'total' => $item->total,
                ];
            })
            ->toArray();
    }

    private function generateInsights()
    {
        $insights = [];
        $visitorComp = $this->getVisitorComparison();
        $revenueComp = $this->getRevenueComparison();
        $trendData = $this->getTrendData();

        // Visitor trend insight
        if ($visitorComp['percentage'] > 10) {
            $insights[] = [
                'type' => 'success',
                'icon' => '📈',
                'title' => 'Pengunjung Meningkat',
                'message' => 'Jumlah pengunjung hari ini meningkat ' . abs($visitorComp['percentage']) . '% dibanding kemarin (' . abs($visitorComp['difference']) . ' pengunjung lebih banyak).',
            ];
        } elseif ($visitorComp['percentage'] < -10) {
            $insights[] = [
                'type' => 'warning',
                'icon' => '📉',
                'title' => 'Pengunjung Menurun',
                'message' => 'Jumlah pengunjung hari ini menurun ' . abs($visitorComp['percentage']) . '% dibanding kemarin (' . abs($visitorComp['difference']) . ' pengunjung lebih sedikit).',
            ];
        } else {
            $insights[] = [
                'type' => 'info',
                'icon' => '📊',
                'title' => 'Pengunjung Stabil',
                'message' => 'Jumlah pengunjung hari ini relatif stabil dengan perubahan ' . $visitorComp['percentage'] . '% dibanding kemarin.',
            ];
        }

        // Revenue trend insight
        if ($revenueComp['percentage'] > 10) {
            $insights[] = [
                'type' => 'success',
                'icon' => '💰',
                'title' => 'Pendapatan Meningkat',
                'message' => 'Pendapatan hari ini meningkat ' . abs($revenueComp['percentage']) . '% dibanding kemarin (Rp ' . number_format(abs($revenueComp['difference'])) . ' lebih tinggi).',
            ];
        } elseif ($revenueComp['percentage'] < -10) {
            $insights[] = [
                'type' => 'warning',
                'icon' => '💸',
                'title' => 'Pendapatan Menurun',
                'message' => 'Pendapatan hari ini menurun ' . abs($revenueComp['percentage']) . '% dibanding kemarin (Rp ' . number_format(abs($revenueComp['difference'])) . ' lebih rendah).',
            ];
        }

        // Peak day insight
        $peakRatio = $this->getPeakRatio();
        if ($peakRatio >= 1.5) {
            $insights[] = [
                'type' => 'info',
                'icon' => '🏆',
                'title' => 'Weekend Lebih Ramai',
                'message' => 'Weekend ' . $peakRatio . 'x lebih ramai dibanding weekday. Siapkan staff tambahan untuk weekend.',
            ];
        }

        // Monthly trend insight
        if (count($trendData) >= 2) {
            $current = $trendData[count($trendData) - 1]['total'];
            $previous = $trendData[count($trendData) - 2]['total'];
            $monthChange = $previous > 0 ? round((($current - $previous) / $previous) * 100) : 0;

            if ($monthChange > 5) {
                $insights[] = [
                    'type' => 'success',
                    'icon' => '📈',
                    'title' => 'Trend Bulanan Positif',
                    'message' => 'Pengunjung bulan ini naik ' . $monthChange . '% dibanding bulan lalu. Pertahankan strategi!',
                ];
            }
        }

        return $insights;
    }

    private function getTotalVisitors()
    {
        $oneMonthAgo = Carbon::now()->subMonth();
        return OrderItem::whereHas('order', function($q) use ($oneMonthAgo) {
            $q->where('transaction_time', '>=', $oneMonthAgo);
        })->sum('quantity') ?? 0;
    }

    private function getTotalRevenue()
    {
        $oneMonthAgo = Carbon::now()->subMonth();
        return Transaction::where('transaction_time', '>=', $oneMonthAgo)->sum('amount') ?? 0;
    }

    private function getAvgDailyVisitors()
    {
        $oneMonthAgo = Carbon::now()->subMonth();
        $days = Carbon::now()->diffInDays($oneMonthAgo);
        $total = $this->getTotalVisitors();
        return $days > 0 ? round($total / $days) : 0;
    }

    private function getPeakRatio()
    {
        $oneMonthAgo = Carbon::now()->subMonth();

        $weekendAvg = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.transaction_time', '>=', $oneMonthAgo)
            ->whereIn(DB::raw('DAYOFWEEK(orders.transaction_time)'), [1, 7])
            ->selectRaw('AVG(order_items.quantity) as avg')
            ->value('avg') ?? 1;

        $weekdayAvg = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.transaction_time', '>=', $oneMonthAgo)
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

    public function getTrendByPeriod(Request $request)
    {
        $period = $request->input('period', '6_months');
        $data = [];

        switch ($period) {
            case '6_months':
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
                        'label' => $month->format('M Y'),
                        'total' => $total,
                        'weekend' => $weekend,
                        'weekday' => $weekday,
                    ];
                }
                break;

            case '1_month_weekly':
                $startOfMonth = Carbon::now()->startOfMonth();
                $endOfMonth = Carbon::now()->endOfMonth();
                $today = Carbon::now();

                for ($week = 1; $week <= 4; $week++) {
                    $weekStart = $startOfMonth->copy()->addWeeks($week - 1);
                    $weekEnd = $weekStart->copy()->addDays(6);

                    // Batasi weekEnd tidak melebihi akhir bulan atau hari ini
                    if ($weekEnd > $endOfMonth) {
                        $weekEnd = $endOfMonth;
                    }
                    if ($weekEnd > $today) {
                        $weekEnd = $today;
                    }

                    // Skip hanya jika minggu belum dimulai
                    if ($weekStart > $today) {
                        continue;
                    }

                    $total = DB::table('order_items')
                        ->join('orders', 'order_items.order_id', '=', 'orders.id')
                        ->whereBetween('orders.transaction_time', [$weekStart->startOfDay(), $weekEnd->endOfDay()])
                        ->sum('order_items.quantity') ?? 0;

                    $weekend = DB::table('order_items')
                        ->join('orders', 'order_items.order_id', '=', 'orders.id')
                        ->whereBetween('orders.transaction_time', [$weekStart->startOfDay(), $weekEnd->endOfDay()])
                        ->whereIn(DB::raw('DAYOFWEEK(orders.transaction_time)'), [1, 7])
                        ->sum('order_items.quantity') ?? 0;

                    $weekday = DB::table('order_items')
                        ->join('orders', 'order_items.order_id', '=', 'orders.id')
                        ->whereBetween('orders.transaction_time', [$weekStart->startOfDay(), $weekEnd->endOfDay()])
                        ->whereNotIn(DB::raw('DAYOFWEEK(orders.transaction_time)'), [1, 7])
                        ->sum('order_items.quantity') ?? 0;

                    $data[] = [
                        'label' => 'Minggu ' . $week . ' (' . $weekStart->format('d') . '-' . $weekEnd->format('d M') . ')',
                        'total' => $total,
                        'weekend' => $weekend,
                        'weekday' => $weekday,
                    ];
                }
                break;

            case 'daily':
                for ($i = 6; $i >= 0; $i--) {
                    $date = Carbon::today()->subDays($i);
                    $isWeekend = in_array($date->dayOfWeek, [0, 6]);

                    $total = DB::table('order_items')
                        ->join('orders', 'order_items.order_id', '=', 'orders.id')
                        ->whereDate('orders.transaction_time', $date)
                        ->sum('order_items.quantity') ?? 0;

                    $data[] = [
                        'label' => $this->getDayNameIndonesia($date) . ', ' . $date->format('d M'),
                        'total' => $total,
                        'weekend' => $isWeekend ? $total : 0,
                        'weekday' => !$isWeekend ? $total : 0,
                    ];
                }
                break;
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'period' => $period
        ]);
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
                ->where('orders.transaction_time', '>=', Carbon::now()->subMonth())
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
                'day' => $this->getDayNameIndonesia($date),
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
            $date = Carbon::parse($f['date']);
            $details[] = [
                'day' => $this->getDayNameIndonesia($date) . ', ' . $date->format('d') . ' ' . $this->getMonthNameIndonesia($date),
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

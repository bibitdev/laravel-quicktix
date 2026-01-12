<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Set timezone ke Asia/Jakarta (WIB)
        $today = Carbon::today('Asia/Jakarta');
        $yesterday = Carbon::yesterday('Asia/Jakarta');

        $data = [
            'daily_visitors' => $this->getDailyVisitors($today),
            'daily_revenue' => $this->getDailyRevenue($today),
            'tickets_sold' => $this->getTicketsSold($today),
            'total_transactions' => $this->getTotalTransactions($today),

            // Comparison with yesterday
            'revenue_comparison' => $this->getRevenueComparison($today, $yesterday),
            'tickets_comparison' => $this->getTicketsComparison($today, $yesterday),

            // Chart Data
            'ticket_sales_trend' => $this->getTicketSalesTrend(),
            'revenue_trend' => $this->getRevenueTrend(),
            'payment_methods' => $this->getPaymentMethodStats(),
        ];

        return view('pages.dashboard', $data);
    }

    private function getDailyVisitors($date)
    {
        // Hitung berdasarkan transaction_time dari Order, bukan created_at
        return OrderItem::whereHas('order', function($query) use ($date) {
            $query->whereDate('transaction_time', $date);
        })->sum('quantity');
    }

    private function getDailyRevenue($date)
    {
        // Hitung berdasarkan transaction_time, bukan created_at
        return Transaction::whereDate('transaction_time', $date)
            ->sum('amount');
    }

    private function getTicketsSold($date)
    {
        // Hitung berdasarkan transaction_time dari Order, bukan created_at
        return OrderItem::whereHas('order', function($query) use ($date) {
            $query->whereDate('transaction_time', $date);
        })->sum('quantity');
    }

    private function getTotalTransactions($date)
    {
        // Hitung berdasarkan transaction_time, bukan created_at
        return Transaction::whereDate('transaction_time', $date)
            ->count();
    }

    private function getTicketSalesTrend()
    {
        $endDate = Carbon::today('Asia/Jakarta');
        $startDate = Carbon::today('Asia/Jakarta')->subDays(6);

        // Query berdasarkan transaction_time dari Order
        $data = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->selectRaw('DATE(orders.transaction_time) as date, SUM(order_items.quantity) as total')
            ->whereBetween('orders.transaction_time', [$startDate, $endDate->endOfDay()])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Fill missing dates with 0
        $result = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $found = $data->firstWhere('date', $dateStr);

            $result[] = [
                'date' => $dateStr,
                'total' => $found ? (int) $found->total : 0
            ];
        }

        return $result;
    }

    private function getRevenueTrend()
    {
        $endDate = Carbon::today('Asia/Jakarta');
        $startDate = Carbon::today('Asia/Jakarta')->subDays(6);

        // Query berdasarkan transaction_time, bukan created_at
        $data = Transaction::selectRaw('DATE(transaction_time) as date, SUM(amount) as total')
            ->whereBetween('transaction_time', [$startDate, $endDate->endOfDay()])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Fill missing dates with 0
        $result = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $found = $data->firstWhere('date', $dateStr);

            $result[] = [
                'date' => $dateStr,
                'total' => $found ? (float) $found->total : 0
            ];
        }

        return $result;
    }

    private function getPaymentMethodStats()
    {
        return Transaction::selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get()
            ->map(function ($item) {
                return [
                    'payment_method' => $item->payment_method,
                    'count' => (int) $item->count,
                    'total' => (float) $item->total
                ];
            })
            ->values()
            ->toArray();
    }

    private function getRevenueComparison($today, $yesterday)
    {
        $todayRevenue = $this->getDailyRevenue($today);
        $yesterdayRevenue = $this->getDailyRevenue($yesterday);

        $difference = $todayRevenue - $yesterdayRevenue;
        $percentageChange = 0;

        if ($yesterdayRevenue > 0) {
            $percentageChange = (($difference / $yesterdayRevenue) * 100);
        } elseif ($todayRevenue > 0) {
            $percentageChange = 100; // Jika kemarin 0 tapi hari ini ada, berarti naik 100%
        }

        return [
            'today' => $todayRevenue,
            'yesterday' => $yesterdayRevenue,
            'difference' => $difference,
            'percentage' => round($percentageChange, 1),
            'trend' => $difference > 0 ? 'up' : ($difference < 0 ? 'down' : 'stable')
        ];
    }

    private function getTicketsComparison($today, $yesterday)
    {
        $todayTickets = $this->getTicketsSold($today);
        $yesterdayTickets = OrderItem::whereHas('order', function($query) use ($yesterday) {
            $query->whereDate('transaction_time', $yesterday);
        })->sum('quantity');

        $difference = $todayTickets - $yesterdayTickets;
        $percentageChange = 0;

        if ($yesterdayTickets > 0) {
            $percentageChange = (($difference / $yesterdayTickets) * 100);
        } elseif ($todayTickets > 0) {
            $percentageChange = 100;
        }

        return [
            'today' => $todayTickets,
            'yesterday' => $yesterdayTickets,
            'difference' => $difference,
            'percentage' => round($percentageChange, 1),
            'trend' => $difference > 0 ? 'up' : ($difference < 0 ? 'down' : 'stable')
        ];
    }
}

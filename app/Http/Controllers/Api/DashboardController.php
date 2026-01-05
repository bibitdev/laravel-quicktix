<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard analytics data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $today = Carbon::today();

        $data = [
            // Today's statistics
            'daily_visitors' => $this->getDailyVisitors($today),
            'daily_revenue' => $this->getDailyRevenue($today),
            'tickets_sold' => $this->getTicketsSold($today),
            'total_transactions' => $this->getTotalTransactions($today),

            // Chart Data (7 days)
            'ticket_sales_trend' => $this->getTicketSalesTrend(),
            'revenue_trend' => $this->getRevenueTrend(),
            'payment_methods' => $this->getPaymentMethodStats(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $data
        ], 200);
    }

    /**
     * Get daily visitors (total quantity of tickets sold today)
     */
    private function getDailyVisitors($date)
    {
        return OrderItem::whereDate('created_at', $date)
            ->sum('quantity');
    }

    /**
     * Get daily revenue (total amount from transactions today)
     */
    private function getDailyRevenue($date)
    {
        return Transaction::whereDate('created_at', $date)
            ->sum('amount');
    }

    /**
     * Get tickets sold today
     */
    private function getTicketsSold($date)
    {
        return OrderItem::whereDate('created_at', $date)
            ->sum('quantity');
    }

    /**
     * Get total transactions today
     */
    private function getTotalTransactions($date)
    {
        return Transaction::whereDate('created_at', $date)
            ->count();
    }

    /**
     * Get ticket sales trend for last 7 days
     */
    private function getTicketSalesTrend()
    {
        $endDate = Carbon::today();
        $startDate = Carbon::today()->subDays(6);

        $data = OrderItem::selectRaw('DATE(created_at) as date, SUM(quantity) as total')
            ->whereBetween('created_at', [$startDate, $endDate->endOfDay()])
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

    /**
     * Get revenue trend for last 7 days
     */
    private function getRevenueTrend()
    {
        $endDate = Carbon::today();
        $startDate = Carbon::today()->subDays(6);

        $data = Transaction::selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->whereBetween('created_at', [$startDate, $endDate->endOfDay()])
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

    /**
     * Get payment method statistics
     */
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
            });
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->format('Y-m-d');

        $data = [
            'daily_visitors' => OrderItem::whereDate('created_at', $today)->sum('quantity'),
            'daily_revenue' => Transaction::whereDate('created_at', $today)->sum('amount'),
            'tickets_sold' => OrderItem::whereDate('created_at', $today)->sum('quantity'),
            'total_transactions' => Transaction::whereDate('created_at', $today)->count(),

            // Chart Data
            'visitor_data' => $this->getVisitorTrend(),
            'sales_data' => $this->getSalesTrend(),
            'payment_data' => $this->getPaymentMethods(),
        ];

        return view('pages.dashboard', $data);
    }

    private function getVisitorTrend()
    {
        return OrderItem::selectRaw('DATE(created_at) as date, SUM(quantity) as count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get()
            ->reverse()
            ->values();
    }

    private function getSalesTrend()
    {
        return Transaction::selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get()
            ->reverse()
            ->values();
    }

    private function getPaymentMethods()
    {
        return Transaction::selectRaw('payment_method, COUNT(*) as count')
            ->groupBy('payment_method')
            ->get();
    }

    private function getPeakHours()
    {
        return Order::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }
}

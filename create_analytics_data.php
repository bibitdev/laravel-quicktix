<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Transaction;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

// Hapus data lama dulu
echo "Menghapus data lama...\n";
DB::statement('SET FOREIGN_KEY_CHECKS=0;');
Transaction::truncate();
Order::truncate();
OrderItem::truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

// Data untuk 6 bulan (Aug 2025 - Jan 2026)
echo "Membuat data dummy untuk 6 bulan terakhir (Aug 2025 - Jan 2026)...\n\n";

$startDate = Carbon::create(2025, 8, 1);
$endDate = Carbon::create(2026, 1, 31);
$currentDate = $startDate->copy();

// Holiday dates dengan multiplier
$holidays = [
    '2025-08-17' => 2.5,  // Kemerdekaan
    '2025-12-25' => 2.3,  // Natal
    '2026-01-01' => 3.0,  // Tahun Baru
];

$baseWeekday = 5;   // Base transactions per weekday
$baseWeekend = 12;  // Base transactions per weekend
$monthCounter = 0;
$totalOrders = 0;

while ($currentDate <= $endDate) {
    $isWeekend = $currentDate->isWeekend();
    $dateString = $currentDate->format('Y-m-d');
    $isHoliday = isset($holidays[$dateString]);

    // Calculate growth factor (15% growth every month)
    $growthFactor = 1 + ($monthCounter * 0.15);

    // Base transactions
    $baseTransactions = $isWeekend ? $baseWeekend : $baseWeekday;
    $numTransactions = round($baseTransactions * $growthFactor);

    // Apply holiday multiplier
    if ($isHoliday) {
        $numTransactions = round($numTransactions * $holidays[$dateString]);
    }

    // Add random variation (-20% to +30%)
    $variation = rand(80, 130) / 100;
    $numTransactions = max(2, round($numTransactions * $variation));

    // Print progress every 1st of month
    if ($currentDate->day == 1) {
        echo "Bulan: " . $currentDate->format('M Y') . " - Rata-rata ~" . round($numTransactions) . " transaksi/hari\n";
        $monthCounter++;
    }

    // Generate transactions for this day
    for ($j = 0; $j < $numTransactions; $j++) {
        $date = Carbon::create(
            $currentDate->year,
            $currentDate->month,
            $currentDate->day,
            rand(8, 18),  // Business hours
            rand(0, 59),
            rand(0, 59)
        );

        // Random metode pembayaran (50% cash, 30% transfer, 20% qris)
        $rand = rand(1, 100);
        if ($rand <= 50) {
            $paymentMethod = 'cash';
        } elseif ($rand <= 80) {
            $paymentMethod = 'transfer';
        } else {
            $paymentMethod = 'qris';
        }

        // Random product (1 atau 2) dan quantity
        $productId = rand(1, 2);
        $quantity = rand(1, 4);
        $price = 25000;  // Standard ticket price
        $amount = $price * $quantity;

        // Create Order
        $orderId = DB::table('orders')->insertGetId([
            'transaction_time' => $date->format('Y-m-d H:i:s'),
            'total_price' => $amount,
            'total_item' => $quantity,
            'payment_amount' => $amount,
            'cashier_id' => 1,
            'cashier_name' => 'Admin',
            'payment_method' => $paymentMethod,
            'created_at' => $date->format('Y-m-d H:i:s'),
            'updated_at' => $date->format('Y-m-d H:i:s'),
        ]);

        // Create OrderItem
        DB::table('order_items')->insert([
            'order_id' => $orderId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'total_price' => $amount,
            'created_at' => $date->format('Y-m-d H:i:s'),
            'updated_at' => $date->format('Y-m-d H:i:s'),
        ]);

        // Create Transaction
        $ticketNumber = 'TIK' . $date->format('Ymd') . str_pad($j + 1, 4, '0', STR_PAD_LEFT);
        DB::table('transactions')->insert([
            'ticket_number' => $ticketNumber,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'transaction_time' => $date->format('Y-m-d H:i:s'),
            'cashier_id' => 1,
            'created_at' => $date->format('Y-m-d H:i:s'),
            'updated_at' => $date->format('Y-m-d H:i:s'),
        ]);

        $totalOrders++;
    }

    $currentDate->addDay();
}

echo "\n=== Data dummy berhasil dibuat! ===\n";
echo "Period: Aug 2025 - Jan 2026 (6 bulan)\n";
echo "Total Transactions: " . Transaction::count() . "\n";
echo "Total Orders: " . Order::count() . "\n";
echo "Total Order Items: " . OrderItem::count() . "\n";
echo "Total Tiket Terjual: " . OrderItem::sum('quantity') . "\n";
echo "Total Pendapatan: Rp " . number_format(Transaction::sum('amount')) . "\n";
echo "\nSekarang buka dashboard untuk melihat analitik lengkap!\n";

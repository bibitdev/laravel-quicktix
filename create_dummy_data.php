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

// Data untuk 7 hari terakhir
echo "Membuat data dummy untuk 7 hari terakhir...\n";

for ($i = 6; $i >= 0; $i--) {
    // Random jumlah transaksi per hari (3-8 transaksi)
    $numTransactions = rand(3, 8);

    $targetDate = Carbon::today()->subDays($i);
    echo "Hari " . $targetDate->format('Y-m-d') . ": $numTransactions transaksi\n";

    for ($j = 0; $j < $numTransactions; $j++) {
        // Buat Carbon baru untuk setiap transaksi
        $date = Carbon::create(
            $targetDate->year,
            $targetDate->month,
            $targetDate->day,
            rand(9, 17),
            rand(0, 59),
            rand(0, 59)
        );

        // Random metode pembayaran
        $paymentMethods = ['Tunai', 'Transfer'];
        $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

        // Random product (1 atau 2)
        $productId = rand(1, 2);
        $quantity = rand(1, 5);
        $price = $productId == 1 ? 15000 : 10000;
        $amount = $price * $quantity;

        // Buat Order menggunakan DB raw
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

        $order = (object)['id' => $orderId];

        // Buat OrderItem menggunakan DB raw karena Laravel override timestamps
        DB::table('order_items')->insert([
            'order_id' => $order->id,
            'product_id' => $productId,
            'quantity' => $quantity,
            'total_price' => $amount,
            'created_at' => $date->format('Y-m-d H:i:s'),
            'updated_at' => $date->format('Y-m-d H:i:s'),
        ]);

        // Buat Transaction menggunakan DB raw
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
    }
}

echo "\n=== Data dummy berhasil dibuat! ===\n";
echo "Total Transactions: " . Transaction::count() . "\n";
echo "Total Orders: " . Order::count() . "\n";
echo "Total Order Items: " . OrderItem::count() . "\n";
echo "Total Tiket Terjual: " . OrderItem::sum('quantity') . "\n";
echo "Total Pendapatan: Rp " . number_format(Transaction::sum('amount')) . "\n";

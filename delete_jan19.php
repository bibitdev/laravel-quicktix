<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== MENGHAPUS DATA TANGGAL 19 JAN 2026 ===\n\n";

// Get order IDs
$orderIds = DB::table('orders')
    ->whereDate('transaction_time', '2026-01-19')
    ->pluck('id')
    ->toArray();

echo "Order IDs: " . implode(', ', $orderIds) . "\n";
echo "Total: " . count($orderIds) . " orders\n\n";

// Delete order_items
$deletedItems = DB::table('order_items')
    ->whereIn('order_id', $orderIds)
    ->delete();
echo "✓ Menghapus order_items: {$deletedItems} items\n";

// Delete orders
$deletedOrders = DB::table('orders')
    ->whereDate('transaction_time', '2026-01-19')
    ->delete();
echo "✓ Menghapus orders: {$deletedOrders} orders\n";

// Delete transactions
$deletedTrans = DB::table('transactions')
    ->whereDate('transaction_time', '2026-01-19')
    ->delete();
echo "✓ Menghapus transactions: {$deletedTrans} transactions\n\n";

// Verify
$check = DB::table('orders')->whereDate('transaction_time', '2026-01-19')->count();
echo "Verifikasi: {$check} orders tersisa (harus 0)\n";
echo "\n=== SELESAI! ===\n";

<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnalyticsDataSeeder extends Seeder
{
    /**
     * Seed historical data for analytics (Aug 2025 - Feb 2026)
     */
    public function run()
    {
        // Clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        OrderItem::truncate();
        Order::truncate();
        Transaction::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $startDate = Carbon::create(2025, 8, 1);
        $endDate = Carbon::create(2026, 2, 28);
        $user = User::first();
        $products = Product::all();

        if (!$user) {
            $this->command->error('No users found. Please run AdminSeeder first.');
            return;
        }

        if ($products->isEmpty()) {
            $this->command->error('No products found. Please create products first.');
            return;
        }

        $currentDate = $startDate->copy();
        $orderIdCounter = 1;
        $transactionIdCounter = 1;

        $this->command->info('Generating analytics data from ' . $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'));

        // Holiday dates with multipliers
        $holidays = [
            '2025-08-17' => 2.5,  // Kemerdekaan
            '2025-12-25' => 2.3,  // Natal
            '2026-01-01' => 3.0,  // Tahun Baru
        ];

        // Growth trend: gradually increase over time
        $baseWeekday = 10;
        $baseWeekend = 25;
        $monthCounter = 0;

        while ($currentDate <= $endDate) {
            $month = $currentDate->month;
            $isWeekend = $currentDate->isWeekend();
            $dateString = $currentDate->format('Y-m-d');
            $isHoliday = isset($holidays[$dateString]);

            // Calculate growth factor (10% growth every month)
            $growthFactor = 1 + ($monthCounter * 0.10);

            // Base visitors
            $baseVisitors = $isWeekend ? $baseWeekend : $baseWeekday;
            $visitors = round($baseVisitors * $growthFactor);

            // Apply holiday multiplier
            if ($isHoliday) {
                $visitors = round($visitors * $holidays[$dateString]);
            }

            // Add random variation (-20% to +30%)
            $variation = rand(80, 130) / 100;
            $visitors = max(3, round($visitors * $variation));

            // Generate orders for this day
            $numOrders = rand(max(1, $visitors - 5), $visitors + 2);

            for ($i = 0; $i < $numOrders; $i++) {
                // Random time during business hours (8:00 - 18:00)
                $hour = rand(8, 18);
                $minute = rand(0, 59);
                $transactionTime = $currentDate->copy()->setTime($hour, $minute, rand(0, 59));

                // Random product
                $product = $products->random();
                $quantity = rand(1, 4);
                $ticketPrice = 25000; // Standard ticket price
                $totalAmount = $quantity * $ticketPrice;

                // Payment method distribution
                $paymentMethods = ['cash', 'qris', 'transfer'];
                $paymentWeights = [0.5, 0.3, 0.2]; // 50% cash, 30% qris, 20% transfer
                $rand = rand(0, 100) / 100;
                if ($rand < $paymentWeights[0]) {
                    $paymentMethod = 'cash';
                } elseif ($rand < $paymentWeights[0] + $paymentWeights[1]) {
                    $paymentMethod = 'qris';
                } else {
                    $paymentMethod = 'transfer';
                }

                // Create order
                $order = Order::create([
                    'id' => $orderIdCounter++,
                    'transaction_time' => $transactionTime,
                    'total_price' => $totalAmount,
                    'total_item' => $quantity,
                    'payment_amount' => $totalAmount,
                    'cashier_id' => $user->id,
                    'cashier_name' => $user->name,
                    'payment_method' => $paymentMethod,
                    'created_at' => $transactionTime,
                    'updated_at' => $transactionTime,
                ]);

                // Create order item
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'total_price' => $totalAmount,
                    'created_at' => $transactionTime,
                    'updated_at' => $transactionTime,
                ]);

                // Create transaction
                Transaction::create([
                    'id' => $transactionIdCounter++,
                    'amount' => $totalAmount,
                    'created_at' => $transactionTime,
                    'updated_at' => $transactionTime,
                ]);
            }

            // Progress indicator
            if ($currentDate->day == 1) {
                $this->command->info('Generated data for ' . $currentDate->format('M Y') . ' - Avg visitors: ' . round($visitors));
                $monthCounter++;
            }

            $currentDate->addDay();
        }

        $this->command->info('✅ Analytics data generated successfully!');
        $this->command->info('Total Orders: ' . Order::count());
        $this->command->info('Total Transactions: ' . Transaction::count());
        $this->command->info('Total Revenue: Rp ' . number_format(Transaction::sum('amount')));
    }
}

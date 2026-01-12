<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Generate dummy transactions for last 7 days
     */
    public function run(): void
    {
        // Get admin/cashier user
        $cashier = User::where('role', 'admin')->first();

        if (!$cashier) {
            $this->command->error('No admin user found. Please run AdminSeeder first.');
            return;
        }

        // Get products
        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command->error('No products found. Please add products first.');
            return;
        }

        $this->command->info('Generating dummy transactions for last 7 days...');

        // Generate transactions for last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);

            // Random number of transactions per day (3-8)
            $transactionsPerDay = rand(3, 8);

            for ($j = 0; $j < $transactionsPerDay; $j++) {
                // Random time during business hours (9 AM - 5 PM)
                $randomTime = $date->copy()
                    ->setHour(rand(9, 17))
                    ->setMinute(rand(0, 59))
                    ->setSecond(rand(0, 59));

                // Random payment method (only Tunai and QRIS)
                $paymentMethod = ['Tunai', 'QRIS'][rand(0, 1)];

                // Random number of items (1-5)
                $numItems = rand(1, 5);
                $totalPrice = 0;
                $totalQuantity = 0;

                // Create Order
                $order = Order::create([
                    'transaction_time' => $randomTime,
                    'total_price' => 0, // Will update after calculating items
                    'total_item' => 0,
                    'payment_amount' => 0,
                    'cashier_id' => $cashier->id,
                    'cashier_name' => $cashier->name,
                    'payment_method' => $paymentMethod,
                    'created_at' => $randomTime,
                    'updated_at' => $randomTime,
                ]);

                // Add random items to order
                for ($k = 0; $k < $numItems; $k++) {
                    $product = $products->random();
                    $quantity = rand(1, 3);
                    $itemTotal = $product->price * $quantity;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'total_price' => $itemTotal,
                        'created_at' => $randomTime,
                        'updated_at' => $randomTime,
                    ]);

                    $totalPrice += $itemTotal;
                    $totalQuantity += $quantity;
                }

                // Update order totals
                $order->update([
                    'total_price' => $totalPrice,
                    'total_item' => $totalQuantity,
                    'payment_amount' => $totalPrice,
                ]);

                // Create Transaction record
                Transaction::create([
                    'ticket_number' => 'TRX-' . str_pad($order->id, 8, '0', STR_PAD_LEFT),
                    'amount' => $totalPrice,
                    'payment_method' => $paymentMethod,
                    'transaction_time' => $randomTime,
                    'cashier_id' => $cashier->id,
                    'created_at' => $randomTime,
                    'updated_at' => $randomTime,
                ]);
            }

            $this->command->info("Generated {$transactionsPerDay} transactions for {$date->format('Y-m-d')}");
        }

        $this->command->info('✅ Transaction seeder completed!');
        $this->command->info('Total Orders: ' . Order::count());
        $this->command->info('Total Transactions: ' . Transaction::count());
    }
}

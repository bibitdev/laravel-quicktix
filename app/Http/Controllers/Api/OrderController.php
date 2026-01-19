<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrderController extends Controller
{
    // store
    public function store(Request $request)
    {
        // Log incoming request
        Log::info('=== ORDER API REQUEST START ===');
        Log::info('Request Method: ' . $request->method());
        Log::info('Request URL: ' . $request->fullUrl());
        Log::info('Request Body: ', $request->all());
        Log::info('Order Items Count: ' . (is_array($request->order_items) ? count($request->order_items) : 0));

        $request->validate([
            'transaction_time' => 'required',
            'total_price' => 'required',
            'total_item' => 'required',
            'payment_amount' => 'required',
            'cashier_id' => 'required',
            'cashier_name' => 'required',
            'payment_method' => 'required|in:Tunai,QRIS',
            'order_items' => 'required',
        ]);

        $order = new Order;
        $order->transaction_time = Carbon::parse($request->transaction_time)->format('Y-m-d H:i:s');
        $order->total_price = $request->total_price;
        $order->total_item = $request->total_item;
        $order->payment_amount = $request->payment_amount;
        $order->cashier_id = $request->cashier_id;
        $order->cashier_name = $request->cashier_name;
        $order->payment_method = $request->payment_method;
        $order->save();

        Log::info('Order Created: ', ['order_id' => $order->id, 'total_items' => $request->total_item]);

        foreach ($request->order_items as $item) {
            $orderItem = new OrderItem;
            $orderItem->order_id = $order->id;
            $orderItem->product_id = $item['product_id'];
            $orderItem->quantity = $item['quantity'];
            $orderItem->total_price = $item['total_price'] * $item['quantity'];
            $orderItem->save();

            Log::info('OrderItem Created: ', [
                'order_item_id' => $orderItem->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'total_price' => $orderItem->total_price
            ]);

            // Kurangi stok produk
            $product = Product::find($item['product_id']);
            if ($product) {
                $product->stock -= $item['quantity'];
                $product->save();
            }
        }

        // Buat record Transaction untuk dashboard analytics
        $transaction = new Transaction;
        $transaction->ticket_number = 'TRX-' . str_pad($order->id, 8, '0', STR_PAD_LEFT);
        $transaction->amount = $order->total_price;
        $transaction->payment_method = $order->payment_method;
        $transaction->transaction_time = Carbon::parse($request->transaction_time)->format('Y-m-d H:i:s');
        $transaction->cashier_id = $order->cashier_id;
        $transaction->save();

        Log::info('Transaction Created: ', [
            'transaction_id' => $transaction->id,
            'ticket_number' => $transaction->ticket_number,
            'amount' => $transaction->amount
        ]);

        // Calculate total quantity from order_items
        $totalQuantity = OrderItem::where('order_id', $order->id)->sum('quantity');

        $response = [
            'status' => 'success',
            'data' => $order,
            'summary' => [
                'order_id' => $order->id,
                'total_items' => OrderItem::where('order_id', $order->id)->count(),
                'total_quantity' => $totalQuantity,
                'transaction_id' => $transaction->id
            ]
        ];

        Log::info('ORDER API RESPONSE: ', $response);
        Log::info('=== ORDER API REQUEST END ===');

        return response()->json($response, 201);
    }
}

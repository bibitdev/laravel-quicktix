<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use PDF;
use Illuminate\Support\Facades\Storage;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    // List transaksi & recap bulanan
    public function list(Request $request)
    {
        $query = Transaction::query();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();

            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        $recap = Transaction::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total_transactions, SUM(amount) as total_amount")
            ->groupBy('month')
            ->orderByDesc('month')
            ->get();

        return view('pages.transactions.index', compact('transactions', 'recap'));
    }

    // Cetak PDF transaksi per ID
    public function cetak($id)
    {
        $transaction = Transaction::findOrFail($id);

        $pdf = PDF::loadView('pages.pdf.receipt', [
            'transaction' => $transaction,
            'payment_method' => $transaction->payment_method,
            'transaction_time' => $transaction->created_at,
        ]);

        return $pdf->stream('transaction_' . $transaction->ticket_number . '.pdf');
    }

    // Buat transaksi + generate ticket_number + QR + simpan PDF
    public function print(Request $request)
    {
        // Log incoming request
        Log::info('=== TRANSACTION PRINT REQUEST START ===');
        Log::info('Request Method: ' . $request->method());
        Log::info('Request URL: ' . $request->fullUrl());
        Log::info('Request Body: ', $request->all());
        Log::info('Order Items: ', $request->order_items ?? []);

        // Validasi request dari Flutter
        $request->validate([
            'amount' => 'required|numeric',
            'payment_method' => 'required|string',
            'transaction_time' => 'required',
            'cashier_id' => 'required',
            'order_items' => 'required|array|min:1',
            'order_items.*.product_id' => 'required|exists:products,id',
            'order_items.*.quantity' => 'required|integer|min:1',
            'order_items.*.total_price' => 'required|numeric',
        ]);

        // Log warning jika tidak ada order items
        if (!$request->has('order_items') || empty($request->order_items)) {
            Log::error('CRITICAL: Transaction submitted WITHOUT order_items!', [
                'request' => $request->all()
            ]);
            return response()->json([
                'error' => 'order_items is required and must contain at least 1 item'
            ], 400);
        }

        // Hitung jumlah transaksi hari ini
        $countToday = Transaction::whereDate('created_at', now())->count() + 1;

        // Format nomor tiket unik: TIK202507260001
        $ticketNumber = 'TIK' . now()->format('Ymd') . str_pad($countToday, 4, '0', STR_PAD_LEFT);

        // Buat Order dulu untuk tracking di dashboard
        $order = Order::create([
            'transaction_time' => Carbon::parse($request->transaction_time)->format('Y-m-d H:i:s'),
            'total_price' => $request->amount,
            'total_item' => $request->total_item ?? 1,
            'payment_amount' => $request->amount,
            'cashier_id' => $request->cashier_id,
            'cashier_name' => $request->cashier_name ?? 'Kasir',
            'payment_method' => $request->payment_method,
        ]);

        Log::info('Order Created in Print: ', [
            'order_id' => $order->id,
            'total_price' => $order->total_price,
            'total_item' => $order->total_item
        ]);

        // Simpan order items dan kurangi stok
        foreach ($request->order_items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'total_price' => $item['total_price'] * $item['quantity'],
            ]);

            Log::info('OrderItem Created in Print: ', [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'total_price' => $item['total_price'] * $item['quantity']
            ]);

            // Kurangi stok produk
            $product = Product::find($item['product_id']);
            if ($product) {
                $product->stock -= $item['quantity'];
                $product->save();
            }
        }

        // Simpan transaksi
        $transaction = Transaction::create([
            'ticket_number' => $ticketNumber,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'transaction_time' => Carbon::parse($request->transaction_time)->format('Y-m-d H:i:s'),
            'cashier_id' => $request->cashier_id,
        ]);

        // QR content: tiket + timestamp
        $qrContent = $ticketNumber . '#' . Carbon::parse($transaction->transaction_time)->format('Y-m-d H:i:s');

        // QR options
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel'   => QRCode::ECC_L,
            'scale'      => 5,
        ]);

        // Pastikan folder qr ada
        $qrDir = storage_path('app/public/qr');
        if (!file_exists($qrDir)) {
            mkdir($qrDir, 0755, true);
        }

        $qrPath = $qrDir . '/' . $transaction->id . '.png';

        // Generate QR
        (new QRCode($options))->render($qrContent, $qrPath);

        // Buat PDF
        $pdf = PDF::loadView('pages.pdf.receipt', [
            'transaction' => $transaction,
            'payment_method' => $transaction->payment_method,
            'transaction_time' => $transaction->transaction_time,
        ]);

        // Simpan PDF
        $pdfPath = 'pdf/receipt_' . $transaction->ticket_number . '.pdf';
        Storage::disk('public')->put($pdfPath, $pdf->output());

        $response = ['pdf_url' => asset('storage/' . $pdfPath)];

        Log::info('Transaction Print Summary: ', [
            'transaction_id' => $transaction->id,
            'ticket_number' => $transaction->ticket_number,
            'order_id' => $order->id,
            'total_order_items' => OrderItem::where('order_id', $order->id)->count(),
            'total_quantity' => OrderItem::where('order_id', $order->id)->sum('quantity'),
            'pdf_path' => $pdfPath
        ]);

        Log::info('TRANSACTION PRINT RESPONSE: ', $response);
        Log::info('=== TRANSACTION PRINT REQUEST END ===');

        return response()->json($response);
    }

    // Download recap bulanan
    public function downloadRecap($month)
    {
        $recapData = Transaction::selectRaw('MONTH(created_at) as month, COUNT(*) as total_transactions, SUM(amount) as total_amount')
            ->whereMonth('created_at', $month)
            ->groupBy('month')
            ->first();

        if (!$recapData) {
            return abort(404, 'Data recap tidak ditemukan.');
        }

        $pdf = PDF::loadView('pages.pdf.recap', compact('recapData', 'month'));

        return $pdf->download("recap-bulanan-{$month}.pdf");
    }
}

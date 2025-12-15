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
        // Validasi request dari Flutter
        $request->validate([
            'amount' => 'required|numeric',
            'payment_method' => 'required|string',
            'transaction_time' => 'required',
            'cashier_id' => 'required',
            'order_items' => 'sometimes|array',
            'order_items.*.product_id' => 'required_with:order_items|exists:products,id',
            'order_items.*.quantity' => 'required_with:order_items|integer|min:1',
            'order_items.*.total_price' => 'required_with:order_items|numeric',
        ]);

        // Hitung jumlah transaksi hari ini
        $countToday = Transaction::whereDate('created_at', now())->count() + 1;

        // Format nomor tiket unik: TIK202507260001
        $ticketNumber = 'TIK' . now()->format('Ymd') . str_pad($countToday, 4, '0', STR_PAD_LEFT);

        // Buat Order dulu untuk tracking di dashboard
        $order = Order::create([
            'transaction_time' => $request->transaction_time,
            'total_price' => $request->amount,
            'total_item' => $request->total_item ?? 1,
            'payment_amount' => $request->amount,
            'cashier_id' => $request->cashier_id,
            'cashier_name' => $request->cashier_name ?? 'Kasir',
            'payment_method' => $request->payment_method,
        ]);

        // Simpan order items dan kurangi stok
        if ($request->has('order_items') && is_array($request->order_items)) {
            // Jika ada data order_items dari request
            foreach ($request->order_items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'total_price' => $item['total_price'] * $item['quantity'],
                ]);

                // Kurangi stok produk
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->stock -= $item['quantity'];
                    $product->save();
                }
            }
        } else {
            // Fallback: Jika tidak ada order_items, coba deteksi dari amount dan product_id
            // Atau buat order item default berdasarkan total_item
            $totalItem = $request->total_item ?? 1;

            // Cari produk yang sesuai dengan harga
            if ($request->has('product_id')) {
                $product = Product::find($request->product_id);
            } else {
                // Coba cari produk berdasarkan harga
                $product = Product::where('price', '<=', $request->amount)
                    ->orderBy('price', 'desc')
                    ->first();
            }

            if ($product) {
                $quantity = $totalItem;
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'total_price' => $request->amount,
                ]);

                // Kurangi stok produk
                $product->stock -= $quantity;
                $product->save();
            }
        }

        // Simpan transaksi
        $transaction = Transaction::create([
            'ticket_number' => $ticketNumber,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'transaction_time' => $request->transaction_time,
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

        return response()->json([
            'pdf_url' => asset('storage/' . $pdfPath),
        ]);
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

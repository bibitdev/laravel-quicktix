<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use PDF;
use Illuminate\Support\Facades\Storage;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Carbon\Carbon;

class TransactionController extends Controller
{
    // List transaksi & recap bulanan
    public function list()
    {
        $transactions = Transaction::orderBy('created_at', 'desc')->get();

        $recap = Transaction::selectRaw('MONTH(created_at) as month, COUNT(*) as total_transactions, SUM(amount) as total_amount')
            ->groupBy('month')
            ->get();

        return view('pages.transaksi.index', compact('transactions', 'recap'));
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

        return $pdf->stream('transaction_' . $id . '.pdf');
    }

    // Print transaksi baru + generate QR + simpan PDF
    public function print(Request $request)
    {
        $transaction = Transaction::create([
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'transaction_time' => $request->transaction_time,
            'cashier_id' => $request->cashier_id,
        ]);

        // QR content
        $qrContent = $transaction->cashier_id . '#' . Carbon::parse($transaction->transaction_time)->format('Y-m-d H:i:s');

        // QR options
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel'   => QRCode::ECC_L,
            'scale'      => 5,
        ]);

        // Path QR image
        $qrPath = storage_path('app/public/qr/' . $transaction->id . '.png');

        // Generate QR dan simpan
        (new QRCode($options))->render($qrContent, $qrPath);

        // Generate PDF
        $pdf = PDF::loadView('pages.pdf.receipt', [
            'transaction' => $transaction,
            'payment_method' => $transaction->payment_method,
            'transaction_time' => $transaction->transaction_time,
        ]);

        // Simpan PDF
        $pdfPath = 'pdf/receipt_' . $transaction->id . '.pdf';
        Storage::disk('public')->put($pdfPath, $pdf->output());

        return response()->json([
            'pdf_url' => asset('storage/' . $pdfPath),
        ]);
    }

    // Download PDF recap bulanan
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

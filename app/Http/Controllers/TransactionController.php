<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use PDF; // pastikan sudah install barryvdh/laravel-dompdf
use Illuminate\Support\Facades\Storage;
use QrCode;

class TransactionController extends Controller
{
    // List transaksi & recap bulanan
    public function list()
    {
        $transactions = Transaction::orderBy('created_at', 'desc')->get();

        // Recap Bulanan
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

        // Data untuk QR (cashier_id#transaction_time)
        $qrData = $transaction->cashier_id . '#' . $transaction->transaction_time;

        // Generate & simpan QR code
        QrCode::format('png')
            ->size(200)
            ->generate($qrData, storage_path('app/public/qr/' . $transaction->id . '.png'));

        // Generate PDF
        $pdf = PDF::loadView('pages.pdf.receipt', [
            'transaction' => $transaction,
            'payment_method' => $transaction->payment_method,
            'transaction_time' => $transaction->transaction_time,
        ]);

        // Simpan PDF sementara
        $pdfPath = storage_path('app/public/pdf/receipt_' . $transaction->id . '.pdf');
        $pdf->save($pdfPath);

        // Kirim URL PDF ke frontend
        return response()->json([
            'pdf_url' => asset('storage/pdf/receipt_' . $transaction->id . '.pdf')
        ]);
    }

    // Download PDF recap bulanan
    public function downloadRecap($month)
    {
        // Ambil data recap bulan tertentu
        $recapData = Transaction::selectRaw('MONTH(created_at) as month, COUNT(*) as total_transactions, SUM(amount) as total_amount')
            ->whereMonth('created_at', $month)
            ->groupBy('month')
            ->first();

        if (!$recapData) {
            return abort(404, 'Data recap tidak ditemukan.');
        }

        // Generate PDF dari view
        $pdf = PDF::loadView('pages.pdf.recap', compact('recapData', 'month'));

        return $pdf->download("recap-bulanan-{$month}.pdf");
    }
}

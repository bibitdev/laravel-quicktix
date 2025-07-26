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
        // Hitung jumlah transaksi hari ini
        $countToday = Transaction::whereDate('created_at', now())->count() + 1;

        // Format nomor tiket unik: TIK202507260001
        $ticketNumber = 'TIK' . now()->format('Ymd') . str_pad($countToday, 4, '0', STR_PAD_LEFT);

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

        $qrPath = storage_path('app/public/qr/' . $transaction->id . '.png');

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

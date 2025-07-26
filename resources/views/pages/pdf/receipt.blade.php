<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: 'Segoe UI', 'Helvetica Neue', sans-serif;
            color: #2c3e50;
            margin: 0;
            padding: 0;
            background-color: #f4f6fb;
        }

        .header {
            background-color: #3949AB;
            color: #fff;
            padding: 20px 0;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .content {
            width: 85%;
            max-width: 400px;
            margin: 20px auto;
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .location-header {
            margin-top: -10px;
            margin-bottom: 20px;
        }

        .location-header h2 {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
            color: #2c3e50;
        }

        .location-header p {
            margin: 5px 0 0 0;
            font-size: 13px;
            color: #555;
        }

        .qr-code {
            margin: 20px 0;
        }

        table {
            width: 100%;
            font-size: 13px;
            margin-top: 10px;
            text-align: left;
        }

        td {
            padding: 8px 0;
        }

        .label {
            color: #7f8c8d;
        }

        .value {
            font-weight: bold;
            color: #2c3e50;
        }

        .footer {
            margin-top: 25px;
            font-size: 11px;
            color: #7f8c8d;
        }
    </style>
</head>

<body>

    <div class="header">
        PAYMENT RECEIPT
    </div>

    <div class="content">
        <div class="location-header">
            <h2>CURUG PINANG</h2>
            <p>Dusun II, Karangsalam, Baturaden, Banyumas</p>
        </div>


        <div style="margin-top: 8px; font-size: 13px; font-weight: 600; color: #2c3e50;">
            Nomor Tiket: <span style="color:#3949AB">{{ $transaction->ticket_number }}</span>
        </div>

        <hr style="border: none; border-top: 1px dashed #ccc; margin: 10px auto; width: 60%;">

        <div class="qr-code">
            <img src="{{ base_path('storage/app/public/qr/' . $transaction->id . '.png') }}" alt="QR Code"
                width="150">
        </div>
        <div style="font-size:12px; margin-bottom:10px;">
            Scan this QR code to verify tickets
        </div>

        <table>
            <tr>
                <td class="label">Tagihan</td>
                <td class="value">Rp. {{ number_format($transaction->amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Metode Bayar</td>
                <td class="value">{{ $payment_method }}</td>
            </tr>
            <tr>
                <td class="label">Waktu</td>
                <td class="value">{{ \Carbon\Carbon::parse($transaction_time)->format('d.m.Y') }}</td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td class="value">Lunas</td>
            </tr>
        </table>

        <div class="footer">
            &copy; {{ date('Y') }} Sistem Tiketing Curug Pinang
        </div>
    </div>

</body>

</html>

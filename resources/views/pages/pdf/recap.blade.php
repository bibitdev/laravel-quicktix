<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Bulanan - Curug Pinang</title>
    <style>
        body {
            font-family: "Segoe UI", "Helvetica Neue", sans-serif;
            color: #2c3e50;
            margin: 0;
            padding: 0;
            background-color: #f4f6fb;
        }
        .header {
            background-color: #3949AB; /* warna utama biru */
            color: #fff;
            padding: 25px 0;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            letter-spacing: 1px;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 13px;
        }
        .content {
            margin: 40px auto;
            width: 85%;
            max-width: 600px;
            background: #fff;
            padding: 30px;
            border: 1px solid #dcdde1;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }
        .content h2 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 20px;
            color: #3949AB;
            border-bottom: 2px solid #3949AB;
            padding-bottom: 8px;
            display: inline-block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #dcdde1;
            padding: 12px 15px;
        }
        th {
            background-color: #3949AB;
            color: #fff;
            text-align: left;
            font-weight: 600;
        }
        td {
            background-color: #f9f9f9;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 30px;
        }
        .logo {
            text-align: center;
            margin-bottom: 15px;
        }
        .logo img {
            width: 80px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CURUG PINANG</h1>
        <p>Laporan Rekapitulasi Bulanan Tiket Wisata</p>
    </div>

    <div class="content">
        {{-- Jika punya logo, bisa aktifkan --}}
        {{-- <div class="logo">
            <img src="{{ public_path('logo.png') }}" alt="Logo Curug Pinang">
        </div> --}}

        <h2>Rekap Bulan {{ $month }}</h2>

        <table>
            <tr>
                <th>Total Transaksi</th>
                <td>{{ $recapData->total_transactions }}</td>
            </tr>
            <tr>
                <th>Total Pendapatan</th>
                <td>Rp. {{ number_format($recapData->total_amount, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div class="footer">
            Dicetak pada: {{ date('d M Y') }}<br>
            &copy; {{ date('Y') }} Sistem Tiketing Curug Pinang
        </div>
    </div>
</body>
</html>

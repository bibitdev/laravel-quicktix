@extends('layouts.app')

@section('title', 'Transaksi')

@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Transaksi</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
                <div class="breadcrumb-item">Transaksi</div>
            </div>
        </div>

        <div class="section-body">
            {{-- Alert (kalau ada) --}}
            @include('layouts.alert')

            {{-- Daftar Semua Transaksi --}}
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Daftar Semua Transaksi</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Amount</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($transactions as $trx)
                                            <tr>
                                                <td>{{ $trx->id }}</td>
                                                <td>Rp. {{ number_format($trx->amount, 0, ',', '.') }}</td>
                                                <td>{{ $trx->created_at }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recap Bulanan --}}
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Recap Bulanan</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Bulan</th>
                                            <th>Total Transaksi</th>
                                            <th>Total Amount</th>
                                            <th>Aksi</th> {{-- Tambah kolom Aksi untuk Download PDF --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recap as $item)
                                            <tr>
                                                <td>{{ $item->month }}</td>
                                                <td>{{ $item->total_transactions }}</td>
                                                <td>Rp. {{ number_format($item->total_amount, 0, ',', '.') }}</td>
                                                <td>
                                                    <a href="{{ route('recap.download', $item->month) }}"
                                                        target="_blank"
                                                        class="btn btn-sm btn-primary">
                                                        Download PDF
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

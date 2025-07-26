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
            @include('layouts.alert')

            {{-- Filter Tanggal --}}
            <form method="GET" action="{{ route('transaksi.index') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <label for="start_date">Dari Tanggal</label>
                        <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}"
                            class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date">Sampai Tanggal</label>
                        <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}"
                            class="form-control">
                    </div>
                    <div class="col-md-3 align-self-end">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>

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
                                        @forelse ($transactions as $trx)
                                            <tr>
                                                <td>{{ $trx->id }}</td>
                                                <td>Rp. {{ number_format($trx->amount, 0, ',', '.') }}</td>
                                                <td>{{ $trx->created_at->format('d-m-Y H:i') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center">Tidak ada transaksi.</td>
                                            </tr>
                                        @endforelse
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
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recap as $item)
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
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">Belum ada recap.</td>
                                            </tr>
                                        @endforelse
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

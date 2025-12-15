@extends('layouts.app')

@section('title', 'Produk')

@push('style')
    <link rel="stylesheet" href="{{ asset('library/selectric/public/selectric.css') }}">
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Produk</h1>
                <div class="section-header-button">
                    @can('create', App\Models\Product::class)
                        <a href="{{ route('products.create') }}" class="btn btn-primary">Tambah Baru</a>
                    @endcan
                </div>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="#">Produk</a></div>
                    <div class="breadcrumb-item">Semua Produk</div>
                </div>
            </div>

            {{-- Info Box: Total Stok dan Produk Habis --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <strong>Total Stok Produk:</strong> {{ $totalStok }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-danger">
                        <strong>Produk Stok Habis:</strong> {{ $produkHabis }}
                    </div>
                </div>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        @include('layouts.alert')
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Semua Produk</h4>
                            </div>
                            <div class="card-body">

                                <div class="float-right">
                                    <form method="GET" action="{{ route('products.index') }}">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Cari" name="keyword" value="{{ request('keyword') }}">
                                            <div class="input-group-append">
                                                <button class="btn btn-primary"><i class="fas fa-search"></i></button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="clearfix mb-3"></div>

                                <div class="table-responsive">
                                    <table class="table-striped table">
                                        <thead>
                                            <tr>
                                                <th>Nama</th>
                                                <th>Kategori</th>
                                                <th>Harga</th>
                                                <th>Status</th>
                                                <th>Dibuat Pada</th>
                                                <th>Stok</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($products as $product)
                                                <tr>
                                                    <td>{{ $product->name }}</td>
                                                    <td>{{ $product->category->name }}</td>
                                                    <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                                    <td>{{ ucfirst($product->status ?? 'active') }}</td>
                                                    <td>{{ $product->created_at->format('d M Y') }}</td>
                                                    <td>
                                                        @if ($product->stock == 0)
                                                            <span class="badge badge-danger">Habis</span>
                                                        @else
                                                            {{ $product->stock }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="d-flex justify-content-center">
                                                            @can('update', $product)
                                                                <a href="{{ route('products.edit', $product->id) }}"
                                                                    class="btn btn-sm btn-info btn-icon">
                                                                    <i class="fas fa-edit"></i> Edit
                                                                </a>
                                                            @endcan

                                                            @can('delete', $product)
                                                                <form action="{{ route('products.destroy', $product->id) }}"
                                                                    method="POST" class="ml-2">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button class="btn btn-sm btn-danger btn-icon confirm-delete">
                                                                        <i class="fas fa-times"></i> Hapus
                                                                    </button>
                                                                </form>
                                                            @endcan
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="float-right mt-3">
                                    {{ $products->withQueryString()->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('library/selectric/public/jquery.selectric.min.js') }}"></script>
    <script src="{{ asset('js/page/features-posts.js') }}"></script>
@endpush

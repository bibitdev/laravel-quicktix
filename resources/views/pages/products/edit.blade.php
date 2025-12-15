@extends('layouts.app')

@section('title', 'Edit Produk')

@push('style')
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('library/bootstrap-daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('library/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/selectric/public/selectric.css') }}">
    <link rel="stylesheet" href="{{ asset('library/bootstrap-timepicker/css/bootstrap-timepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/bootstrap-tagsinput/dist/bootstrap-tagsinput.css') }}">
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Edit Produk</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="#">Produk</a></div>
                    <div class="breadcrumb-item">Edit</div>
                </div>
            </div>

            <div class="section-body">
                <h2 class="section-title">Edit Produk</h2>



                <div class="card">
                    <form action="{{ route('products.update', $product) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-header">
                            <h4>Data Produk</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Nama</label>
                                <input type="text"
                                    class="form-control @error('name')
                                is-invalid
                            @enderror"
                                    name="name" value="{{ $product->name }}">
                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Deskripsi</label>
                                <input type="text"
                                    class="form-control @error('description')
                                is-invalid
                            @enderror"
                                    name="description" value="{{ $product->description }}">
                                @error('description')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Harga</label>
                                <input type="number"
                                    class="form-control @error('price')
                                is-invalid
                            @enderror"
                                    name="price" value="{{ $product->price }}">
                                @error('price')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Stok</label>
                                <input type="number"
                                    class="form-control @error('stock')
                                is-invalid
                            @enderror"
                                    name="stock" value="{{ $product->stock }}">
                                @error('stock')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Kategori</label>
                                <select
                                    class="form-control selectric @error('category_id')
                                    is-invalid
                                @enderror"
                                    name="category_id">
                                    <option value="">Pilih Kategori</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ $category->id == $product->category_id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-0">
                                <label class="form-label w-100">Kriteria</label>
                                <div class="selectgroup selectgroup-pills">
                                    <label class="selectgroup-item">
                                        <input type="radio" name="criteria" value="perorangan" class="selectgroup-input"
                                            {{ $product->criteria == 'perorangan' ? 'checked' : '' }}>
                                        <span class="selectgroup-button">Perorangan</span>
                                    </label>
                                    <label class="selectgroup-item">
                                        <input type="radio" name="criteria" value="rombongan" class="selectgroup-input"
                                            {{ $product->criteria == 'rombongan' ? 'checked' : '' }}>
                                        <span class="selectgroup-button">Rombongan</span>
                                    </label>
                                </div>
                            </div>

                        </div>
                        <div class="card-footer text-right">
                            <button class="btn btn-primary">Perbarui</button>
                        </div>
                    </form>
                </div>

            </div>
        </section>
    </div>
@endsection

@push('scripts')
@endpush

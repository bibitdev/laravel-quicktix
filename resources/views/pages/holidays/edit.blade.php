@extends('layouts.app')

@section('title', 'Edit Hari Libur')

@push('style')
    <!-- CSS Libraries -->
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <div class="section-header-back">
                    <a href="{{ route('holidays.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
                </div>
                <h1>Edit Hari Libur</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('holidays.index') }}">Hari Libur</a></div>
                    <div class="breadcrumb-item">Edit</div>
                </div>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Form Edit Hari Libur</h4>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('holidays.update', $holiday->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="form-group">
                                        <label for="name">Nama Hari Libur <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name', $holiday->name) }}"
                                            placeholder="Contoh: Tahun Baru Masehi" required>
                                        @error('name')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="date">Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('date') is-invalid @enderror"
                                            id="date" name="date" value="{{ old('date', $holiday->date) }}" required>
                                        @error('date')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="description">Keterangan (opsional)</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror"
                                            id="description" name="description" rows="3"
                                            placeholder="Keterangan tambahan tentang hari libur ini">{{ old('description', $holiday->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update
                                        </button>
                                        <a href="{{ route('holidays.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Kembali
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <!-- JS Libraries -->
@endpush

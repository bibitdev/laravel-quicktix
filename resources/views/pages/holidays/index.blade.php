@extends('layouts.app')

@section('title', 'Kelola Hari Libur')

@push('style')
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('library/selectric/public/selectric.css') }}">
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Kelola Hari Libur Nasional</h1>
                <div class="section-header-button">
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('holidays.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Hari Libur
                    </a>
                    @endif
                </div>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item">Hari Libur</div>
                </div>
            </div>
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Daftar Hari Libur Nasional</h4>
                            </div>
                            <div class="card-body">
                                @if (session('success'))
                                    <div class="alert alert-success alert-dismissible show fade">
                                        <div class="alert-body">
                                            <button class="close" data-dismiss="alert">
                                                <span>×</span>
                                            </button>
                                            {{ session('success') }}
                                        </div>
                                    </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-1">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>Nama Hari Libur</th>
                                                <th>Tanggal</th>
                                                <th>Tahun</th>
                                                <th>Hari</th>
                                                <th>Keterangan</th>
                                                @if(auth()->user()->role === 'admin')
                                                <th>Aksi</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($holidays as $holiday)
                                                <tr>
                                                    <td class="text-center">{{ $loop->iteration }}</td>
                                                    <td>{{ $holiday->name }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($holiday->date)->format('d M Y') }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($holiday->date)->format('Y') }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($holiday->date)->locale('id')->isoFormat('dddd') }}</td>
                                                    <td>{{ $holiday->description ?? '-' }}</td>
                                                    @if(auth()->user()->role === 'admin')
                                                    <td>
                                                        <div class="d-flex">
                                                            <a href="{{ route('holidays.edit', $holiday->id) }}"
                                                                class="btn btn-sm btn-info btn-icon mr-1">
                                                                <i class="far fa-edit"></i>
                                                            </a>
                                                            <form action="{{ route('holidays.destroy', $holiday->id) }}"
                                                                method="POST" class="d-inline"
                                                                onsubmit="return confirm('Yakin ingin menghapus hari libur ini?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger btn-icon">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                    @endif
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

@push('scripts')
    <!-- JS Libraries -->
    <script src="{{ asset('library/selectric/public/jquery.selectric.min.js') }}"></script>

    <!-- Page Specific JS File -->
    <script src="{{ asset('js/page/features-posts.js') }}"></script>
@endpush

@extends('layouts.auth')

@section('title', 'Login')

@push('style')
    <link rel="stylesheet" href="{{ asset('library/bootstrap-social/bootstrap-social.css') }}">
@endpush

@section('main')
<div class="card card-primary">
    <div class="card-header">
        <h4>Login</h4>
    </div>

    <div class="card-body">
        {{-- Pesan error global kalau login gagal --}}
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>
                <input id="email"
                       type="email"
                       name="email"
                       value="{{ old('email') }}"
                       required
                       autofocus
                       oninvalid="this.setCustomValidity('Email wajib diisi dan format harus benar.')"
                       oninput="this.setCustomValidity('')"
                       class="form-control @error('email') is-invalid @enderror">
                @error('email')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="control-label">Password</label>
                <div class="input-group">
                    <input id="password"
                           type="password"
                           name="password"
                           required minlength="8"
                           oninvalid="this.setCustomValidity('Password minimal 8 karakter.')"
                           oninput="this.setCustomValidity('')"
                           class="form-control @error('password') is-invalid @enderror">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                @error('password')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    Login
                </button>
            </div>
        </form>
    </div>
</div>

<div class="text-muted mt-5 text-center">
    Belum punya akun? <a href="#">Buat Akun</a>
</div>
@endsection

@push('scripts')
<script>
    // Show/hide password
    const togglePassword = document.querySelector('#togglePassword');
    const passwordInput = document.querySelector('#password');

    togglePassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
</script>
@endpush

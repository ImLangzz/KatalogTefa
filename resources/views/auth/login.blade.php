@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
    <section class="auth-page">
        <div class="auth-card">
            <div class="auth-icon">↗</div>
            <span class="eyebrow">SENANG BERTEMU KEMBALI</span>
            <h1>Masuk ke akunmu</h1>
            <p>Kelola katalog dan promosikan produk TEFA-mu.</p>
            <form method="POST" action="{{ route('login.store') }}" class="stack-form">
                @csrf
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                    @error('email')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="password">Kata sandi</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required>
                    @error('password')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <label class="check-line"><input type="checkbox" name="remember" value="1"> Ingat saya</label>
                <button class="button form-submit" type="submit">Masuk</button>
            </form>
            <div class="auth-switch">Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a></div>
        </div>
    </section>
@endsection

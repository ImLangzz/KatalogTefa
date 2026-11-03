@extends('layouts.app')

@section('title', 'Daftar')

@section('content')
    <section class="auth-page">
        <div class="auth-card">
            <div class="auth-icon">✦</div>
            <span class="eyebrow">MULAI BERKARYA</span>
            <h1>Buat akun baru</h1>
            <p>Daftar untuk membagikan produk dan menjangkau lebih banyak pembeli.</p>
            <form method="POST" action="{{ route('register.store') }}" class="stack-form">
                @csrf
                <div class="field">
                    <label for="name">Nama</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" maxlength="255" required autofocus>
                    @error('name')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                    @error('email')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="password">Kata sandi</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required>
                    @error('password')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="password_confirmation">Ulangi kata sandi</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                </div>
                <button class="button form-submit" type="submit">Buat akun</button>
            </form>
            <div class="auth-switch">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></div>
        </div>
    </section>
@endsection

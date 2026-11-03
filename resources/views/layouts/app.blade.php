<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Katalog TEFA') · Katalog TEFA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header">
        <div class="nav-wrap">
            <a class="brand" href="{{ route('catalog.index') }}">
                Katalog TEFA
            </a>
            <nav class="main-nav" aria-label="Navigasi utama">
                <a href="{{ route('catalog.index') }}#produk">Produk</a>
                @auth
                    @if (auth()->user()->is_admin)
                        <a href="{{ route('admin.index') }}">Dashboard admin</a>
                    @else
                        <a href="{{ route('catalog.create') }}">Promosikan produk</a>
                    @endif
                @else
                    <a href="{{ route('login') }}">Masuk</a>
                    <a class="button button-small" href="{{ route('register') }}">Daftar</a>
                @endauth
                @auth
                    <span class="nav-user">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="nav-link" type="submit">Keluar</button>
                    </form>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        @if (session('success'))
            <div class="flash flash-success" role="status">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="flash flash-error" role="alert">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container footer-inner">
            <a class="brand footer-brand" href="{{ route('catalog.index') }}">Katalog TEFA</a>
            <span>Katalog produk Teaching Factory</span>
        </div>
    </footer>
</body>
</html>

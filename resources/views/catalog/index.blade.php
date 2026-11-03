@extends('layouts.app')

@section('title', 'Temukan produk')

@section('content')
    <section class="hero">
        <div class="container hero-content">
            <div class="hero-copy">
                <span class="eyebrow">KATALOG TEACHING FACTORY</span>
                <h1>Temukan produk TEFA</h1>
                <p>Lihat produk unggulan, cek harga, dan kunjungi langsung lokasi penjual.</p>
                <form class="search-form" method="GET" action="{{ route('catalog.index') }}">
                    <label class="sr-only" for="catalog-search">Cari produk atau kategori</label>
                    <input id="catalog-search" type="search" name="q" value="{{ $search }}" placeholder="Cari nama atau keterangan produk...">
                    <button class="button" type="submit">Cari</button>
                </form>
            </div>
        </div>
    </section>

    <section class="container catalog-section" id="produk">
        <div class="section-heading">
            <div>
                <span class="eyebrow">KATALOG TEFA</span>
                <h2>{{ $search ? 'Hasil pencarian' : 'Produk terbaru' }}</h2>
                <p>{{ $search ? 'Menampilkan hasil untuk “'.$search.'”' : 'Temukan produk, informasi harga, dan lokasi untuk dikunjungi.' }}</p>
            </div>
            @auth
                @if (!auth()->user()->is_admin)
                    <a class="button button-outline" href="{{ route('catalog.create') }}">＋ Buat katalog</a>
                @endif
            @else
                <a class="button button-outline" href="{{ route('register') }}">＋ Promosikan produk</a>
            @endauth
        </div>

        @if ($products->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">⌕</div>
                <h3>{{ $search ? 'Produk tidak ditemukan' : 'Belum ada katalog' }}</h3>
                <p>{{ $search ? 'Coba kata kunci lain, ya.' : 'Jadilah yang pertama membagikan produk TEFA di sini.' }}</p>
                @if (!$search)
                    <a class="button" href="{{ auth()->check() ? route('catalog.create') : route('register') }}">Mulai buat katalog</a>
                @endif
            </div>
        @else
            <div class="product-grid">
                @foreach ($products as $product)
                    @php
                        $mapQuery = $product->location_address ?: (
                            $product->location_latitude !== null && $product->location_longitude !== null
                                ? $product->location_latitude.','.$product->location_longitude
                                : null
                        );
                    @endphp
                    <article class="product-card">
                        <a class="product-card-image" href="{{ route('catalog.show', $product) }}" aria-label="Lihat {{ $product->title }}">
                            @if ($product->images->isNotEmpty())
                                <img src="{{ Storage::url($product->images->first()->path) }}" alt="{{ $product->title }}" loading="lazy">
                            @else
                                <div class="image-placeholder">✿</div>
                            @endif
                        </a>
                        <div class="product-card-body">
                            <a class="product-title" href="{{ route('catalog.show', $product) }}">{{ $product->title }}</a>
                            <strong class="product-price">{{ $product->price !== null ? 'Rp '.number_format($product->price, 0, ',', '.') : 'Hubungi penjual' }}</strong>
                            <p class="product-description">{{ \Illuminate\Support\Str::limit($product->description, 105) }}</p>
                            <div class="product-location">
                                <span aria-hidden="true">⌖</span>
                                <span>{{ $product->location_address ?: ($mapQuery ? 'Titik lokasi tersedia' : 'Alamat belum dicantumkan') }}</span>
                            </div>
                            <div class="product-card-actions">
                                @if ($mapQuery)
                                    <a class="map-link" href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode($mapQuery) }}" target="_blank" rel="noopener noreferrer">Lihat lokasi ↗</a>
                                @else
                                    <span class="map-link map-link-muted">Lokasi belum tersedia</span>
                                @endif
                                <a class="detail-link" href="{{ route('catalog.show', $product) }}">Detail produk</a>
                                @if (!auth()->check() || (!auth()->user()->is_admin && auth()->id() !== $product->user_id))
                                    <a class="report-link" href="{{ route('catalog.show', $product) }}#laporkan">Laporkan</a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="pagination-wrap">{{ $products->links() }}</div>
        @endif
    </section>
@endsection

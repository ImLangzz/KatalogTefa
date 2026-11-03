@extends('layouts.app')

@section('title', $product->title)

@section('content')
    <section class="container detail-section">
        <a class="back-link" href="{{ route('catalog.index') }}">← Kembali ke katalog</a>
        <div class="detail-grid">
            <div>
                <div class="gallery" data-carousel>
                    @foreach ($product->images as $image)
                        <div class="gallery-slide {{ $loop->first ? 'is-active' : '' }}" data-slide>
                            <img src="{{ Storage::url($image->path) }}" alt="{{ $product->title }} — foto {{ $loop->iteration }}">
                        </div>
                    @endforeach
                    @if ($product->images->count() > 1)
                        <button class="gallery-arrow gallery-prev" type="button" aria-label="Foto sebelumnya" data-prev>‹</button>
                        <button class="gallery-arrow gallery-next" type="button" aria-label="Foto berikutnya" data-next>›</button>
                        <div class="gallery-count"><span data-current>1</span> / {{ $product->images->count() }}</div>
                    @endif
                </div>
                <div class="gallery-dots" data-dots>
                    @foreach ($product->images as $image)
                        <button type="button" class="gallery-dot {{ $loop->first ? 'is-active' : '' }}" aria-label="Tampilkan foto {{ $loop->iteration }}" data-dot="{{ $loop->index }}"></button>
                    @endforeach
                </div>
            </div>
            <div class="detail-info">
                <h1>{{ $product->title }}</h1>
                <div class="detail-price">{{ $product->price !== null ? 'Rp '.number_format($product->price, 0, ',', '.') : 'Hubungi penjual untuk harga' }}</div>
                <div class="detail-description">
                    <h2>Tentang produk</h2>
                    <p>{{ $product->description }}</p>
                </div>
                @if ($product->location_latitude !== null && $product->location_longitude !== null)
                    <div class="location-address">
                        <h2>Lokasi tempat jualan</h2>
                        <p>{{ $product->location_address ?: 'Penjual menandai titik lokasinya saat ini.' }}</p>
                    </div>
                    <a class="button map-button" href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode($product->location_latitude.','.$product->location_longitude) }}" target="_blank" rel="noopener noreferrer">Lihat lokasi di Google Maps</a>
                @elseif ($product->location_address)
                    <div class="location-address">
                        <h2>Alamat tempat jualan</h2>
                        <p>{{ $product->location_address }}</p>
                    </div>
                    <a class="button map-button" href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode($product->location_address) }}" target="_blank" rel="noopener noreferrer">Lihat lokasi di Google Maps</a>
                @endif

                @auth
                    @if (!auth()->user()->is_admin && auth()->id() === $product->user_id)
                        <div class="owner-actions">
                            <a class="button button-outline" href="{{ route('catalog.edit', $product) }}">Edit katalog</a>
                            <form method="POST" action="{{ route('catalog.destroy', $product) }}" onsubmit="return confirm('Yakin ingin menghapus katalog ini?')">
                                @csrf @method('DELETE')
                                <button class="button button-danger-outline" type="submit">Hapus</button>
                            </form>
                        </div>
                    @elseif (!auth()->user()->is_admin)
                        <details class="report-box" id="laporkan" @if ($errors->has('reason') || $errors->has('details')) open @endif>
                            <summary>⚑ Laporkan produk ini</summary>
                            <form method="POST" action="{{ route('reports.store', $product) }}" class="report-form">
                                @csrf
                                <label for="reason">Alasan laporan</label>
                                <select id="reason" name="reason" required>
                                    <option value="">Pilih alasan</option>
                                    @foreach (\App\Models\ProductReport::REASONS as $value => $label)
                                        <option value="{{ $value }}" @selected(old('reason') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('reason')<span class="field-error">{{ $message }}</span>@enderror
                                <label for="details">Keterangan (opsional)</label>
                                <textarea id="details" name="details" rows="3" maxlength="2000" placeholder="Ceritakan masalahnya...">{{ old('details') }}</textarea>
                                @error('details')<span class="field-error">{{ $message }}</span>@enderror
                                <button class="button button-danger" type="submit">Kirim laporan</button>
                            </form>
                        </details>
                    @endif
                @else
                    <p class="login-hint" id="laporkan"><a href="{{ route('login') }}">Masuk</a> untuk melaporkan produk yang berbahaya atau ilegal.</p>
                @endauth
            </div>
        </div>
    </section>
@endsection

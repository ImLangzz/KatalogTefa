@extends('layouts.app')

@section('title', $product->exists ? 'Edit katalog' : 'Buat katalog')

@section('content')
    <section class="container form-page">
        <a class="back-link" href="{{ $product->exists ? route('catalog.show', $product) : route('catalog.index') }}">← Kembali</a>
        <div class="form-card">
            <span class="eyebrow">{{ $product->exists ? 'PERBARUI PROMOSI' : 'PROMOSIKAN PRODUK' }}</span>
            <h1>{{ $product->exists ? 'Edit katalog' : 'Ceritakan produkmu' }}</h1>
            <p class="form-intro">Bagikan informasi yang jelas agar calon pembeli mudah mengenal produkmu.</p>
            <form method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('catalog.update', $product) : route('catalog.store') }}" class="stack-form">
                @csrf
                @if ($product->exists) @method('PUT') @endif

                <div class="field">
                    <label for="title">Nama produk</label>
                    <input id="title" name="title" type="text" value="{{ old('title', $product->title) }}" maxlength="120" placeholder="Contoh: Roti sourdough rumahan" required>
                    @error('title')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field-row">
                    <div class="field">
                        <label for="category">Kategori</label>
                        <input id="category" name="category" type="text" value="{{ old('category', $product->category) }}" maxlength="80" placeholder="Contoh: Makanan" required>
                        @error('category')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field">
                        <label for="price">Harga <span class="label-muted">(opsional)</span></label>
                        <div class="price-input"><span>Rp</span><input id="price" name="price" type="number" min="0" step="1" value="{{ old('price', $product->price) }}" placeholder="25000"></div>
                        @error('price')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="field">
                    <label for="description">Deskripsi produk</label>
                    <textarea id="description" name="description" rows="5" maxlength="5000" placeholder="Jelaskan keunikan, bahan, ukuran, atau cara pemesanan..." required>{{ old('description', $product->description) }}</textarea>
                    @error('description')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label>Lokasi tempat jualan <span class="label-muted">(opsional)</span></label>
                    <button class="button button-outline location-button" type="button" data-get-location>Gunakan lokasi saya saat ini</button>
                    <span class="field-hint" data-location-status role="status">Izinkan akses lokasi di browser. Fitur ini memerlukan HTTPS atau localhost.</span>
                    <input type="hidden" name="location_latitude" value="{{ old('location_latitude', $product->location_latitude) }}" data-location-latitude>
                    <input type="hidden" name="location_longitude" value="{{ old('location_longitude', $product->location_longitude) }}" data-location-longitude>
                    <button class="location-clear" type="button" data-clear-location hidden>Hapus titik lokasi</button>
                    @error('location_latitude')<span class="field-error">{{ $message }}</span>@enderror
                    @error('location_longitude')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="location_address">Alamat lengkap tempat jualan <span class="label-muted">(opsional)</span></label>
                    <textarea id="location_address" name="location_address" rows="3" maxlength="1000" placeholder="Contoh: Jl. Merdeka No. 10, Kecamatan, Kota, Provinsi">{{ old('location_address', $product->location_address) }}</textarea>
                    <span class="field-hint">Isi alamat jika ingin memberi petunjuk tambahan atau tidak memakai lokasi saat ini.</span>
                    @error('location_address')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                @if ($product->exists && $product->images->isNotEmpty())
                    <div class="field">
                        <label>Foto saat ini</label>
                        <div class="existing-images">
                            @foreach ($product->images as $image)
                                <label class="existing-image">
                                    <img src="{{ Storage::url($image->path) }}" alt="Foto katalog {{ $loop->iteration }}">
                                    <span><input type="checkbox" name="delete_images[]" value="{{ $image->id }}"> Hapus foto</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="field">
                    <label for="images">{{ $product->exists ? 'Tambah foto' : 'Foto produk' }}</label>
                    <input id="images" name="images[]" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple {{ $product->exists ? '' : 'required' }}>
                    <span class="field-hint">JPG, PNG, atau WEBP. Maksimal 8 foto per unggahan, 5 MB per foto. Lebih dari 2 foto akan berganti otomatis.</span>
                    @error('images')<span class="field-error">{{ $message }}</span>@enderror
                    @error('images.*')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <button class="button form-submit" type="submit">{{ $product->exists ? 'Simpan perubahan' : 'Publikasikan katalog' }}</button>
            </form>
        </div>
    </section>
@endsection

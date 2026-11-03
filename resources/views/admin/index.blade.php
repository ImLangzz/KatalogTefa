@extends('layouts.app')

@section('title', 'Dashboard admin')

@section('content')
    <section class="container admin-page">
        <div class="admin-heading">
            <div>
                <span class="eyebrow">PANEL PENGELOLAAN TEFA</span>
                <h1>Dashboard admin</h1>
                <p>Pantau katalog dan tindak lanjuti laporan dari pengguna.</p>
            </div>
            <div class="admin-welcome">
                <span class="admin-welcome-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                <span><small>Masuk sebagai</small><strong>{{ auth()->user()->name }}</strong></span>
            </div>
        </div>

        <div class="admin-stats-grid">
            <a class="admin-stat-card admin-stat-alert" href="#laporan">
                <span class="admin-stat-label">Perlu ditinjau</span>
                <strong>{{ $pendingReports }}</strong>
                <span class="admin-stat-note">laporan menunggu</span>
            </a>
            <a class="admin-stat-card" href="#laporan">
                <span class="admin-stat-label">Semua laporan</span>
                <strong>{{ $reports->total() }}</strong>
                <span class="admin-stat-note">laporan tercatat</span>
            </a>
            <a class="admin-stat-card" href="#katalog">
                <span class="admin-stat-label">Katalog aktif</span>
                <strong>{{ $products->total() }}</strong>
                <span class="admin-stat-note">produk dipublikasikan</span>
            </a>
        </div>

        <nav class="admin-shortcuts" aria-label="Navigasi dashboard admin">
            <a href="#laporan">Kelola laporan <span>↓</span></a>
            <a href="#katalog">Kelola katalog <span>↓</span></a>
        </nav>

        <section class="admin-section" id="laporan">
            <div class="admin-section-heading">
                <div><span class="eyebrow">MODERASI</span><h2>Laporan pengguna</h2><p>Laporan terbaru yang belum ditinjau muncul terlebih dahulu.</p></div>
                <span class="admin-total">{{ $reports->total() }} laporan</span>
            </div>
            @if ($reports->isEmpty())
                <div class="admin-empty-state"><span class="admin-empty-icon">✓</span><h3>Semua aman untuk saat ini</h3><p>Belum ada laporan dari pengguna. Laporan baru akan muncul di bagian ini.</p></div>
            @else
                <div class="report-list">
                    @foreach ($reports as $report)
                        <article class="report-card">
                            <div class="report-card-top">
                                <div>
                                    <div class="report-badges">
                                        <span class="status-pill status-{{ $report->status }}">{{ $report->status === 'pending' ? 'Perlu ditinjau' : ($report->status === 'reviewed' ? 'Ditinjau' : 'Ditolak') }}</span>
                                        <span class="report-reason-pill">{{ \App\Models\ProductReport::REASONS[$report->reason] ?? str_replace('_', ' ', ucfirst($report->reason)) }}</span>
                                    </div>
                                    <h3>{{ $report->product?->title ?? 'Katalog sudah dihapus' }}</h3>
                                    @if ($report->product)
                                        <p class="report-product-owner">{{ $report->product->category }} <span>·</span> Dipromosikan oleh {{ $report->product->seller->name }}</p>
                                    @endif
                                    <p class="report-meta">Dikirim {{ $report->created_at->format('d M Y, H:i') }} oleh {{ $report->reporter->name }}</p>
                                </div>
                            </div>
                            @if ($report->details)
                                <div class="report-detail-box"><strong>Keterangan pelapor</strong><p>{{ $report->details }}</p></div>
                            @endif
                            <div class="report-actions">
                                @if ($report->product)
                                    <a class="button button-small button-outline" href="{{ route('catalog.show', $report->product) }}" target="_blank" rel="noopener noreferrer">Lihat katalog ↗</a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $report->product) }}" onsubmit="return confirm('Hapus katalog ini? Laporan yang masih menunggu akan ditandai telah ditinjau, dan riwayat laporan tetap tersimpan.')">
                                        @csrf @method('DELETE')
                                        <button class="button button-small button-danger-outline" type="submit">Hapus katalog</button>
                                    </form>
                                @endif
                                @if ($report->status === 'pending')
                                    <span class="report-action-spacer"></span>
                                    <form method="POST" action="{{ route('admin.reports.update', $report) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="reviewed"><button class="button button-small" type="submit">Tandai ditinjau</button></form>
                                    <form method="POST" action="{{ route('admin.reports.update', $report) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="dismissed"><button class="button button-small button-outline" type="submit">Tolak laporan</button></form>
                            @endif
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="pagination-wrap">{{ $reports->links() }}</div>
            @endif
        </section>

        <section class="admin-section" id="katalog">
            <div class="admin-section-heading"><div><span class="eyebrow">KONTEN</span><h2>Semua katalog</h2><p>Kelola katalog yang saat ini tampil untuk pengunjung.</p></div><span class="admin-total">{{ $products->total() }} katalog</span></div>
            @if ($products->isEmpty())
                <div class="empty-state compact-empty"><h3>Belum ada katalog</h3><p>Katalog dari pengguna akan tampil di sini.</p></div>
            @else
                <div class="admin-product-list">
                    @foreach ($products as $product)
                        <article class="admin-product-row">
                            @if ($product->images->isNotEmpty())
                                <img class="admin-product-image" src="{{ Storage::url($product->images->first()->path) }}" alt="">
                            @else
                                <div class="admin-product-image image-placeholder">✿</div>
                            @endif
                            <div class="admin-product-info"><a href="{{ route('catalog.show', $product) }}">{{ $product->title }}</a><span>{{ $product->category }} · {{ $product->seller->name }}</span></div>
                            <span class="admin-price">{{ $product->price !== null ? 'Rp '.number_format($product->price, 0, ',', '.') : 'Tanpa harga' }}</span>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Hapus katalog ini?')">@csrf @method('DELETE')<button class="button button-small button-danger-outline" type="submit">Hapus</button></form>
                        </article>
                    @endforeach
                </div>
                <div class="pagination-wrap">{{ $products->links() }}</div>
            @endif
        </section>
    </section>
@endsection

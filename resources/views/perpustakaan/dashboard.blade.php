@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4">
    {{-- 1. HEADER UTAMA --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 mt-2 gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Dashboard Perpustakaan</h3>
            <span class="text-muted small uppercase">Monitoring Sirkulasi Peminjaman & Inventaris Koleksi Buku</span>
        </div>
        <div class="text-sm-end">
            <span class="badge bg-dark text-white font-monospace uppercase fw-bold p-2 px-3 shadow-2xs" style="font-size: 11px;">
                {{ now()->translatedFormat('d F Y, H:i') }} WIT
            </span>
        </div>
    </div>

    {{-- 2. KARTU STATISTIK UTAMA LENGKAP --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card wm-card-exclusive hover-lift border-0 shadow-sm bg-dark text-white h-100">
                <div class="card-body p-3 p-md-4">
                    <h6 class="uppercase fw-bold small text-white-50 mb-1" style="font-size: 11px;">Total Master Judul</h6>
                    <h2 class="fw-bold text-white mb-0 font-monospace">{{ number_format($totalJudul ?? 0) }}</h2>
                    <span class="text-white-50 small uppercase font-monospace" style="font-size: 10px;">Katalog Terdaftar</span>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-md-3">
            <div class="card hover-lift border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3 p-md-4">
                    <h6 class="uppercase fw-bold small text-muted mb-1" style="font-size: 11px;">Total Eksemplar</h6>
                    <h2 class="fw-bold text-dark mb-0 font-monospace">{{ number_format($totalEksemplar ?? 0) }}</h2>
                    <span class="text-muted small uppercase font-monospace" style="font-size: 10px;">Fisik Ber-Barcode</span>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-md-3">
            <div class="card hover-lift border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3 p-md-4">
                    <h6 class="uppercase fw-bold small text-muted mb-1" style="font-size: 11px;">Sirkulasi Aktif</h6>
                    <h2 class="fw-bold text-dark mb-0 font-monospace">{{ number_format($peminjamanAktif ?? 0) }}</h2>
                    <span class="text-muted small uppercase font-monospace" style="font-size: 10px;">Buku Sedang Dipinjam</span>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-md-3">
            <div class="card hover-lift border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3 p-md-4">
                    <h6 class="uppercase fw-bold small text-muted mb-1" style="font-size: 11px;">Keterlambatan Retur</h6>
                    <h2 class="fw-bold text-danger mb-0 font-monospace">{{ number_format($terlambatCount ?? 0) }}</h2>
                    <span class="text-muted small uppercase font-monospace" style="font-size: 10px;">Melewati Jatuh Tempo</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- 3. PANEL TERMINAL SIRKULASI CEPAT (DIKEMBALIKAN PENUH) --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-header bg-dark text-white py-3 uppercase fw-bold small">
                    Layanan Sirkulasi Cepat
                </div>
                <div class="card-body p-4 bg-light d-flex flex-column justify-content-center">
                    <div class="d-grid gap-3">
                        <a href="{{ route('perpustakaan.peminjaman.create') }}" class="btn btn-dark py-3 text-start uppercase fw-bold small shadow-sm hover-lift">
                            <i class="bi bi-upc-scan me-2 fs-5 align-middle"></i> Transaksi Peminjaman Buku
                        </a>
                        <a href="{{ route('perpustakaan.peminjaman.returnForm') }}" class="btn btn-outline-dark bg-white py-3 text-start uppercase fw-bold small shadow-2xs hover-lift">
                            <i class="bi bi-arrow-return-left me-2 fs-5 align-middle"></i> Transaksi Pengembalian Buku
                        </a>
                        <a href="{{ route('perpustakaan.koleksi.index') }}" class="btn btn-outline-dark bg-white py-3 text-start uppercase fw-bold small shadow-2xs hover-lift">
                            <i class="bi bi-box-seam me-2 fs-5 align-middle"></i> Kelola Master Koleksi & Barcode
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. BUKU TERPOPULER & PROGRESS BAR KAPASITAS STOK --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100 border-top border-dark border-4 bg-white">
                <div class="card-header bg-white py-3 border-bottom uppercase fw-bold small text-dark d-flex justify-content-between align-items-center">
                    <span>Koleksi Buku & Kapasitas Stok Tersedia</span>
                    <a href="{{ route('perpustakaan.koleksi.index') }}" class="text-dark small fw-bold text-decoration-none uppercase" style="font-size: 10px;">Lihat Semua Katalog &rarr;</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0 align-middle small">
                            <thead class="bg-light text-dark uppercase fw-bold">
                                <tr>
                                    <th class="text-center" style="width: 6%;">NO</th>
                                    <th style="width: 46%;">JUDUL BUKU & PENGARANG</th>
                                    <th style="width: 32%;">KAPASITAS STOK TERSEDIA</th>
                                    <th class="text-center" style="width: 16%;">LOKASI RAK</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($koleksiPopuler as $index => $buku)
                                    @php
                                        $stokRatio = $buku->jumlah_stok > 0 ? ($buku->jumlah_tersedia / $buku->jumlah_stok) * 100 : 0;
                                        $barColor = $stokRatio > 0 ? 'bg-dark' : 'bg-danger';
                                    @endphp
                                    <tr>
                                        <td class="text-center font-monospace text-muted">{{ $index + 1 }}</td>
                                        <td class="ps-3">
                                            <div class="uppercase fw-bold text-dark mb-0">{{ $buku->judul }}</div>
                                            <span class="text-muted font-monospace" style="font-size: 10px;">{{ $buku->pengarang }}</span>
                                        </td>
                                        <td class="px-3">
                                            <div class="d-flex justify-content-between font-monospace text-muted mb-1" style="font-size: 10px;">
                                                <span class="{{ $buku->jumlah_tersedia > 0 ? 'text-dark fw-bold' : 'text-danger fw-bold' }}">{{ $buku->jumlah_tersedia }} TERSEDIA</span>
                                                <span>{{ $buku->jumlah_stok }} TOTAL</span>
                                            </div>
                                            <div class="progress" style="height: 5px; background-color: #e9ecef;">
                                                <div class="progress-bar {{ $barColor }}" role="progressbar" style="width: {{ $stokRatio }}%;"></div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border font-monospace uppercase px-2 py-1" style="font-size: 10px;">
                                                {{ $buku->lokasi_rak }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small uppercase fw-bold">
                                            Belum ada data koleksi buku.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. LOG AKTIVITAS SIRKULASI TERKINI LENGKAP --}}
    <div class="card border-0 shadow-sm border-top border-dark border-4 mb-5 bg-white">
        <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
            <span class="uppercase fw-bold small">Log Aktivitas Sirkulasi Terkini</span>
            <a href="{{ route('perpustakaan.peminjaman.history') }}" class="btn btn-sm btn-outline-light py-0.5 px-2.5 uppercase fw-bold font-monospace" style="font-size: 10px;">
                Lihat Riwayat Lengkap
            </a>
        </div>
        <div class="list-group list-group-flush">
            @forelse ($aktivitasTerakhir as $aktivitas)
                <div class="list-group-item px-3 px-md-4 py-3 border-bottom">
                    <div class="d-flex flex-column flex-sm-row w-100 justify-content-between align-items-sm-center gap-2">
                        <div>
                            <div class="uppercase fw-bold text-dark small">{{ $aktivitas->user->name ?? 'PENGGUNA' }}</div>
                            <span class="text-muted small uppercase font-monospace" style="font-size: 11px;">
                                BUKU: {{ $aktivitas->koleksi->judul ?? '-' }}
                            </span>
                        </div>
                        <div class="text-sm-end">
                            @if($aktivitas->status == 'Dipinjam')
                                <span class="badge bg-dark text-white uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">
                                    DIPINJAM ({{ \Carbon\Carbon::parse($aktivitas->tanggal_pinjam)->format('d/m/Y') }})
                                </span>
                            @else
                                <span class="badge bg-light text-dark border uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">
                                    DIKEMBALIKAN
                                </span>
                            @endif
                            <div class="small text-muted font-monospace mt-1" style="font-size: 10px;">
                                {{ strtoupper($aktivitas->updated_at->diffForHumans()) }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 uppercase fw-bold text-muted small">
                    Belum ada rekam jejak aktivitas sirkulasi di sistem.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
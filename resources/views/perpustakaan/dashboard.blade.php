@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Dashboard E-Library POS</h3>
            <span class="text-muted small uppercase">Monitoring Sirkulasi Peminjaman & Pantauan Kapasitas Fisik</span>
        </div>
        <div class="text-end">
            <span class="badge bg-dark text-white rounded-0 font-monospace uppercase fw-bold p-2 px-3 shadow-sm" style="font-size: 11px;">
                <i class="bi bi-clock me-1"></i> {{ now()->translatedFormat('d F Y, H:i') }} WIT
            </span>
        </div>
    </div>

    {{-- KARTU STATISTIK UTAMA (SUDUT SIKU 0PX, FLAT DESIGN) --}}
    <div class="row g-4 mb-4">
        {{-- Total Judul --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="uppercase fw-bold small text-muted mb-0">Total Master Judul</h6>
                        <i class="bi bi-journal-bookmark-fill fs-4 text-dark opacity-50"></i>
                    </div>
                    <h2 class="fw-bold text-dark mb-0 font-monospace">{{ number_format($totalJudul ?? 0) }}</h2>
                    <span class="text-muted small uppercase" style="font-size: 10px;">Katalog Terdaftar</span>
                </div>
            </div>
        </div>
        
        {{-- Total Eksemplar --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="uppercase fw-bold small text-muted mb-0">Total Eksemplar</h6>
                        <i class="bi bi-upc-scan fs-4 text-dark opacity-50"></i>
                    </div>
                    <h2 class="fw-bold text-dark mb-0 font-monospace">{{ number_format($totalEksemplar ?? 0) }}</h2>
                    <span class="text-muted small uppercase" style="font-size: 10px;">Fisik Ber-Barcode</span>
                </div>
            </div>
        </div>
        
        {{-- Sedang Dipinjam --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-primary border-4 h-100 bg-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="uppercase fw-bold small text-muted mb-0">Sirkulasi Aktif</h6>
                        <i class="bi bi-cart-check-fill fs-4 text-primary opacity-50"></i>
                    </div>
                    <h2 class="fw-bold text-primary mb-0 font-monospace">{{ number_format($peminjamanAktif ?? 0) }}</h2>
                    <span class="text-muted small uppercase" style="font-size: 10px;">Buku Sedang Dipinjam</span>
                </div>
            </div>
        </div>
        
        {{-- Terlambat --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-danger border-4 h-100 bg-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="uppercase fw-bold small text-muted mb-0">Keterlambatan Retur</h6>
                        <i class="bi bi-exclamation-triangle-fill fs-4 text-danger opacity-50 pulse-icon"></i>
                    </div>
                    <h2 class="fw-bold text-danger mb-0 font-monospace">{{ number_format($terlambatCount ?? 0) }}</h2>
                    <span class="text-muted small uppercase" style="font-size: 10px;">Melewati Jatuh Tempo</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- MENU TERMINAL KASIR POS --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100">
                <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark d-flex align-items-center">
                    <i class="bi bi-terminal-fill me-2 fs-6"></i> Terminal POS Sirkulasi
                </div>
                <div class="card-body p-4 bg-light">
                    <div class="d-grid gap-3">
                        <a href="{{ route('perpustakaan.peminjaman.create') }}" class="btn btn-primary rounded-0 py-3 text-start uppercase fw-bold small text-white shadow-sm transition-lift">
                            <i class="bi bi-upc-scan me-2 fs-5 align-middle"></i> Kasir Peminjaman (POS)
                        </a>
                        <a href="{{ route('perpustakaan.peminjaman.returnForm') }}" class="btn btn-success rounded-0 py-3 text-start uppercase fw-bold small text-white shadow-sm transition-lift">
                            <i class="bi bi-arrow-return-left me-2 fs-5 align-middle"></i> Kasir Pengembalian (POS)
                        </a>
                        <hr class="my-1 border-dark border-opacity-25">
                        <a href="{{ route('perpustakaan.koleksi.index') }}" class="btn btn-dark rounded-0 py-3 text-start uppercase fw-bold small text-white shadow-sm transition-lift">
                            <i class="bi bi-box-seam-fill me-2 fs-5 align-middle"></i> Master Data Inventaris
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- BUKU TERPOPULER & KAPASITAS STOK --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-0 h-100 border-top border-dark border-4">
                <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-bar-chart-fill me-2"></i> Top 5 Koleksi Terpopuler</span>
                    <a href="{{ route('perpustakaan.koleksi.index') }}" class="text-muted small text-decoration-none uppercase" style="font-size: 10px;">Lihat Katalog</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-borderless table-sm mb-0 align-middle">
                            <thead class="bg-light border-bottom">
                                <tr>
                                    <th class="ps-4 text-muted uppercase fw-bold" style="font-size: 10px; width: 5%;">#</th>
                                    <th class="text-muted uppercase fw-bold" style="font-size: 10px; width: 45%;">Judul Buku & Pengarang</th>
                                    <th class="text-muted uppercase fw-bold" style="font-size: 10px; width: 35%;">Kapasitas Stok Tersedia</th>
                                    <th class="text-center pe-4 text-muted uppercase fw-bold" style="font-size: 10px; width: 15%;">Lokasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($koleksiPopuler as $index => $buku)
                                    @php
                                        $stokRatio = $buku->jumlah_stok > 0 ? ($buku->jumlah_tersedia / $buku->jumlah_stok) * 100 : 0;
                                        $barColor = $stokRatio > 50 ? 'bg-success' : ($stokRatio > 0 ? 'bg-warning' : 'bg-danger');
                                    @endphp
                                    <tr class="border-bottom">
                                        <td class="ps-4 font-monospace text-muted small">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="uppercase fw-bold text-dark mb-0 line-clamp-1" style="font-size: 12px;">{{ $buku->judul }}</div>
                                            <span class="text-muted font-monospace" style="font-size: 10px;">{{ $buku->pengarang }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-between font-monospace text-muted mb-1" style="font-size: 10px;">
                                                <span class="{{ $buku->jumlah_tersedia > 0 ? 'text-success fw-bold' : 'text-danger fw-bold' }}">{{ $buku->jumlah_tersedia }} SISA</span>
                                                <span>{{ $buku->jumlah_stok }} TOTAL</span>
                                            </div>
                                            <div class="progress rounded-0" style="height: 4px; background-color: #e9ecef;">
                                                <div class="progress-bar {{ $barColor }}" role="progressbar" style="width: {{ $stokRatio }}%;"></div>
                                            </div>
                                        </td>
                                        <td class="text-center pe-4">
                                            <span class="badge bg-light text-dark border border-dark border-opacity-25 rounded-0 font-monospace uppercase px-2 py-1" style="font-size: 10px;">
                                                {{ $buku->lokasi_rak }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small uppercase fw-bold">
                                            <i class="bi bi-bar-chart-line fs-3 d-block mb-1 opacity-50"></i>
                                            Belum ada data sirkulasi buku untuk dianalisis.
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

    {{-- LOG AKTIVITAS TERAKHIR (FLAT LIST GROUP) --}}
    <div class="card border-0 shadow-sm rounded-0 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
            <span class="uppercase fw-bold small"><i class="bi bi-clock-history me-2"></i> Log Aktivitas Sirkulasi Terkini</span>
            <a href="{{ route('perpustakaan.peminjaman.history') }}" class="btn btn-sm btn-outline-light rounded-0 py-0 px-2 uppercase fw-bold font-monospace" style="font-size: 10px;">
                Lihat Log Lengkap
            </a>
        </div>
        <div class="list-group list-group-flush rounded-0">
            @forelse ($aktivitasTerakhir as $aktivitas)
                <div class="list-group-item rounded-0 px-4 py-3 border-bottom list-hover">
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            {{-- Ikon Kotak Tajam Berdasarkan Status --}}
                            @if($aktivitas->status == 'Dipinjam')
                                <div class="bg-primary bg-opacity-10 border border-primary rounded-0 d-flex align-items-center justify-content-center me-3 text-primary" style="width: 40px; height: 40px;">
                                    <i class="bi bi-box-arrow-right fs-5"></i>
                                </div>
                            @else
                                <div class="bg-success bg-opacity-10 border border-success rounded-0 d-flex align-items-center justify-content-center me-3 text-success" style="width: 40px; height: 40px;">
                                    <i class="bi bi-arrow-return-left fs-5"></i>
                                </div>
                            @endif
                            
                            <div>
                                <div class="uppercase fw-bold text-dark fs-6">{{ $aktivitas->user->name ?? 'USER TERHAPUS' }}</div>
                                <span class="text-muted small uppercase font-monospace" style="font-size: 11px;">
                                    BUKU: {{ $aktivitas->koleksi->judul ?? 'MASTER BUKU DIHAPUS' }}
                                </span>
                            </div>
                        </div>
                        <div class="text-end">
                            @if($aktivitas->status == 'Dipinjam')
                                <span class="badge bg-primary text-white rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">DIPINJAM PADA {{ \Carbon\Carbon::parse($aktivitas->tanggal_pinjam)->format('d/m/y') }}</span>
                            @else
                                <span class="badge bg-success text-white rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">DIKEMBALIKAN</span>
                            @endif
                            <div class="small text-muted font-monospace mt-1" style="font-size: 10px;">
                                {{ strtoupper($aktivitas->updated_at->diffForHumans()) }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 uppercase fw-bold text-muted small">
                    <i class="bi bi-clipboard-x fs-2 d-block mb-2 text-dark opacity-25"></i>
                    Belum ada rekam jejak aktivitas sirkulasi di sistem.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Transisi Halus pada Tombol */
    .transition-lift {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .transition-lift:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
    }

    /* Hover State untuk List Log */
    .list-hover {
        transition: background-color 0.2s;
    }
    .list-hover:hover {
        background-color: #f8f9fa;
    }

    /* Memotong Teks Panjang */
    .line-clamp-1 { 
        display: -webkit-box; 
        -webkit-line-clamp: 1; 
        -webkit-box-orient: vertical; 
        overflow: hidden; 
    }

    /* Animasi Berkedip Halus untuk Ikon Warning */
    @keyframes pulse {
        0% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.1); }
        100% { opacity: 1; transform: scale(1); }
    }
    .pulse-icon {
        animation: pulse 1.5s infinite;
    }
</style>
@endpush
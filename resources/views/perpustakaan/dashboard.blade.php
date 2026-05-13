@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Dashboard Perpustakaan</h3>
            <span class="text-muted small uppercase">Monitoring Sirkulasi Peminjaman & Rekapitulasi Koleksi Fisik</span>
        </div>
        <div class="text-end">
            <span class="badge bg-dark text-white rounded-0 font-monospace uppercase fw-bold p-2 px-3 shadow-none" style="font-size: 11px;">
                <i class="bi bi-clock me-1"></i> {{ now()->format('d M Y, H:i') }} WIT
            </span>
        </div>
    </div>

    {{-- KARTU STATISTIK UTAMA (SUDUT SIKU 0PX, FLAT DESIGN) --}}
    <div class="row g-4 mb-5">
        {{-- Total Judul --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="uppercase fw-bold small text-muted mb-2">Total Master Judul</h6>
                            <h2 class="fw-bold text-dark mb-0 font-monospace">{{ $totalJudul ?? 0 }}</h2>
                        </div>
                        <i class="bi bi-journal-bookmark-fill fs-3 text-dark opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Total Eksemplar --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="uppercase fw-bold small text-muted mb-2">Total Eksemplar Fisik</h6>
                            <h2 class="fw-bold text-dark mb-0 font-monospace">{{ $totalEksemplar ?? 0 }}</h2>
                        </div>
                        <i class="bi bi-collection-fill fs-3 text-dark opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Sedang Dipinjam --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="uppercase fw-bold small text-muted mb-2">Sirkulasi Aktif (Pinjam)</h6>
                            <h2 class="fw-bold text-primary mb-0 font-monospace">{{ $peminjamanAktif ?? 0 }}</h2>
                        </div>
                        <i class="bi bi-box-arrow-right fs-3 text-primary opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Terlambat --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="uppercase fw-bold small text-muted mb-2">Keterlambatan Retur</h6>
                            <h2 class="fw-bold text-danger mb-0 font-monospace">{{ $terlambat ?? 0 }}</h2>
                        </div>
                        <i class="bi bi-exclamation-triangle-fill fs-3 text-danger opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        {{-- MENU AKSI CEPAT FORMAL --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100">
                <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                    Aksi Cepat Sirkulasi
                </div>
                <div class="card-body p-4">
                    <div class="d-grid gap-3">
                        <a href="{{ route('perpustakaan.peminjaman.create') }}" class="btn btn-outline-dark rounded-0 py-3 text-start uppercase fw-bold small">
                            <i class="bi bi-plus-circle-fill me-2 fs-5 align-middle text-primary"></i> Catat Peminjaman Baru
                        </a>
                        <a href="{{ route('perpustakaan.peminjaman.returnForm') }}" class="btn btn-outline-dark rounded-0 py-3 text-start uppercase fw-bold small">
                            <i class="bi bi-arrow-return-left me-2 fs-5 align-middle text-success"></i> Proses Pengembalian Buku
                        </a>
                        <a href="{{ route('perpustakaan.koleksi.index') }}" class="btn btn-dark rounded-0 py-3 text-start uppercase fw-bold small">
                            <i class="bi bi-book-half me-2 fs-5 align-middle text-white"></i> Kelola Master Koleksi
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- AKTIVITAS TERAKHIR (FLAT LIST GROUP) --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-0 h-100">
                <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
                    <span class="uppercase fw-bold small">Log Aktivitas Sirkulasi Terkini</span>
                    <a href="{{ route('perpustakaan.peminjaman.history') }}" class="btn btn-sm btn-outline-light rounded-0 py-0 px-2 uppercase fw-bold font-monospace" style="font-size: 10px;">
                        Lihat Log Lengkap
                    </a>
                </div>
                <div class="list-group list-group-flush rounded-0">
                    @forelse ($aktivitasTerakhir as $aktivitas)
                        <div class="list-group-item rounded-0 px-4 py-3 border-bottom">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    {{-- Ikon Kotak Tajam 0px --}}
                                    <div class="bg-light border border-dark border-opacity-25 rounded-0 d-flex align-items-center justify-content-center me-3 text-dark" style="width: 40px; height: 40px;">
                                        <i class="bi bi-person-fill fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="uppercase fw-bold text-dark fs-6">{{ $aktivitas->user->name ?? 'USER TERHAPUS' }}</div>
                                        <span class="text-muted small uppercase font-monospace" style="font-size: 11px;">
                                            BUKU: {{ $aktivitas->koleksi->judul ?? 'MASTER BUKU DIHAPUS' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    @if($aktivitas->status == 'Dipinjam')
                                        <span class="badge bg-warning text-dark rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">PEMINJAMAN</span>
                                    @else
                                        <span class="badge bg-success text-white rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">RETUR SELESAI</span>
                                    @endif
                                    <div class="small text-muted font-monospace mt-1" style="font-size: 10px;">
                                        {{ strtoupper($aktivitas->updated_at->diffForHumans()) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 uppercase fw-bold text-muted small">
                            <i class="bi bi-clock-history fs-2 d-block mb-2 text-dark opacity-25"></i>
                            Belum ada rekam jejak aktivitas sirkulasi di sistem.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
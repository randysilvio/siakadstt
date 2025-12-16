@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Dashboard Perpustakaan</h2>
            <p class="text-muted mb-0">Kelola sirkulasi dan koleksi perpustakaan.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-light text-dark border p-2">
                <i class="bi bi-clock me-1"></i> {{ now()->format('d M Y, H:i') }}
            </span>
        </div>
    </div>

    {{-- KARTU STATISTIK UTAMA --}}
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-primary text-white" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-uppercase small fw-bold opacity-75 mb-1">Total Judul</h6>
                            <h2 class="fw-bold mb-0">{{ $totalJudul ?? 0 }}</h2>
                        </div>
                        <i class="bi bi-journal-bookmark-fill fs-3 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-info text-white" style="background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-uppercase small fw-bold opacity-75 mb-1">Total Eksemplar</h6>
                            <h2 class="fw-bold mb-0">{{ $totalEksemplar ?? 0 }}</h2>
                        </div>
                        <i class="bi bi-collection-fill fs-3 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-success text-white" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-uppercase small fw-bold opacity-75 mb-1">Sedang Dipinjam</h6>
                            <h2 class="fw-bold mb-0">{{ $peminjamanAktif ?? 0 }}</h2>
                        </div>
                        <i class="bi bi-box-arrow-right fs-3 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-danger text-white" style="background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-uppercase small fw-bold opacity-75 mb-1">Terlambat</h6>
                            <h2 class="fw-bold mb-0">{{ $terlambat ?? 0 }}</h2>
                        </div>
                        <i class="bi bi-exclamation-triangle-fill fs-3 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        {{-- MENU AKSI CEPAT --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-secondary"><i class="bi bi-lightning-fill me-2 text-warning"></i>Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="{{ route('perpustakaan.peminjaman.create') }}" class="btn btn-outline-primary py-3 text-start">
                            <i class="bi bi-plus-circle-fill me-2 fs-5 align-middle"></i> Catat Peminjaman
                        </a>
                        <a href="{{ route('perpustakaan.peminjaman.returnForm') }}" class="btn btn-outline-success py-3 text-start">
                            <i class="bi bi-arrow-return-left me-2 fs-5 align-middle"></i> Proses Pengembalian
                        </a>
                        <a href="{{ route('perpustakaan.koleksi.index') }}" class="btn btn-outline-dark py-3 text-start">
                            <i class="bi bi-book-half me-2 fs-5 align-middle"></i> Kelola Koleksi Buku
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- AKTIVITAS TERAKHIR --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-secondary"><i class="bi bi-clock-history me-2"></i>Aktivitas Sirkulasi</h5>
                    <a href="{{ route('perpustakaan.peminjaman.history') }}" class="small text-decoration-none fw-bold">Lihat Semua</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($aktivitasTerakhir as $aktivitas)
                        <div class="list-group-item px-4 py-3">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle p-2 me-3 text-secondary">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">{{ $aktivitas->user->name ?? 'User Dihapus' }}</h6>
                                        <small class="text-muted">{{ $aktivitas->koleksi->judul ?? 'Buku Dihapus' }}</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    @if($aktivitas->status == 'Dipinjam')
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning rounded-pill px-3">Meminjam</span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3">Mengembalikan</span>
                                    @endif
                                    <div class="small text-muted mt-1">{{ $aktivitas->updated_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <p class="text-muted mb-0">Belum ada aktivitas sirkulasi.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
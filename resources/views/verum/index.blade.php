@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Kelas Verum (E-Learning)</h3>
            <span class="text-muted small uppercase">Daftar Pengelolaan Ruang Kelas Virtual & Pertemuan Terpusat</span>
        </div>
        
        @if(Auth::user()->hasRole('dosen'))
            <div>
                <a href="{{ route('verum.create') }}" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm">
                    <i class="bi bi-plus-lg me-1"></i> Buat Kelas Baru
                </a>
            </div>
        @endif
    </div>

    {{-- Pesan Notifikasi --}}
    @if(session('error'))
        <div class="alert alert-danger border rounded-0 p-3 mb-4 font-monospace small uppercase shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        </div>
    @endif
    
    @if(session('success'))
        <div class="alert alert-success border rounded-0 p-3 mb-4 font-monospace small uppercase shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Konten Utama Grid Kelas --}}
    @if($semuaKelas->isEmpty() && !session('error'))
        <div class="text-center py-5 bg-light border border-dark border-opacity-25 rounded-0 mb-5">
            <i class="bi bi-journal-x fs-2 d-block mb-2 text-dark opacity-25"></i>
            <h6 class="uppercase fw-bold text-dark mb-1">Belum Ada Kelas Terdaftar</h6>
            <p class="text-muted small uppercase font-monospace mb-0">Anda belum dialokasikan pada kelas Verum manapun di siklus akademik berjalan.</p>
        </div>
    @else
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
            @foreach($semuaKelas as $kelas)
                <div class="col">
                    {{-- Kartu Kelas Standar Enterprise 0px --}}
                    <div class="card h-100 border border-dark border-opacity-25 rounded-0 shadow-none border-top border-dark border-4 bg-white">
                        {{-- Header Kartu Formal Gelap --}}
                        <div class="card-header bg-dark text-white rounded-0 py-3 border-bottom-0">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-light text-dark rounded-0 font-monospace uppercase fw-bold px-2 py-1" style="font-size: 10px;">
                                    {{ optional($kelas->mataKuliah)->kode_mk ?? 'KODE' }}
                                </span>
                                @if($kelas->is_meeting_active)
                                    <span class="badge bg-danger text-white rounded-0 font-monospace uppercase fw-bold px-2 py-1 blink shadow-none" style="font-size: 10px;">
                                        <i class="bi bi-record-circle me-1"></i> LIVE VICON
                                    </span>
                                @endif
                            </div>
                            <h6 class="fw-bold mb-0 text-white uppercase line-clamp-2" style="line-height: 1.35;">
                                <a href="{{ route('verum.show', $kelas) }}" class="text-white text-decoration-none stretched-link">
                                    {{ $kelas->nama_kelas }}
                                </a>
                            </h6>
                        </div>

                        {{-- Uraian Mata Kuliah --}}
                        <div class="card-body p-4 flex-grow-1 bg-white">
                            <h6 class="text-primary fw-bold small uppercase mb-2 line-clamp-1">
                                {{ optional($kelas->mataKuliah)->nama_mk }}
                            </h6>
                            <p class="text-dark small uppercase mb-0 line-clamp-2" style="line-height: 1.6; text-align: justify;">
                                {{ $kelas->deskripsi ? $kelas->deskripsi : 'TIDAK ADA DESKRIPSI KELAS.' }}
                            </p>
                        </div>
                        
                        {{-- Identitas Pengampu / Akses --}}
                        <div class="card-footer bg-light border-top border-dark border-opacity-25 rounded-0 p-3 d-flex align-items-center">
                            <div class="bg-white border border-dark border-opacity-25 rounded-0 p-2 me-3 text-dark d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div class="small text-truncate">
                                @if(Auth::user()->hasRole('mahasiswa'))
                                    <span class="text-muted font-monospace uppercase d-block" style="font-size: 10px;">PENGAJAR UTAMA</span>
                                    <span class="fw-bold text-dark uppercase fs-6">{{ optional(optional($kelas->dosen)->user)->name ?? 'DOSEN PENGAMPU' }}</span>
                                @else
                                    <span class="text-muted font-monospace uppercase d-block" style="font-size: 10px;">KODE AKSES KELAS</span>
                                    <span class="fw-bold text-dark font-monospace fs-6">{{ $kelas->kode_kelas }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<style>
    .line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    @keyframes blink { 50% { opacity: 0; } }
    .blink { animation: blink 1.5s linear infinite; }
</style>
@endsection
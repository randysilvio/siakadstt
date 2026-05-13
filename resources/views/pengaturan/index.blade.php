@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- 1. BAGIAN HEADER & NAVIGASI ATAS --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Pengaturan Sistem</h3>
            <span class="text-muted small uppercase">Konfigurasi Umum Aplikasi SIAKAD</span>
        </div>
        <div>
            <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    {{-- NOTIFIKASI SUKSES --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3" role="alert">
            <div class="d-flex align-items-center small fw-bold uppercase">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            </div>
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 2. GRID FORMULIR & INFORMASI --}}
    <div class="row g-4 mb-5">
        {{-- Kolom Kiri: Formulir Pengaturan --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100">
                <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                    <i class="bi bi-gear-fill me-2"></i>Informasi Instansi & Pimpinan
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.pengaturan.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="nama_rektor" class="form-label small fw-bold uppercase text-dark">
                                Nama Ketua / Rektor <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light rounded-0"><i class="bi bi-person-badge text-dark"></i></span>
                                <input type="text" class="form-control rounded-0" id="nama_rektor" name="nama_rektor"
                                       value="{{ old('nama_rektor', $pengaturans['nama_rektor']->value ?? '') }}" 
                                       placeholder="Contoh: Dr. Nama Lengkap, M.Th." required>
                            </div>
                            <div class="form-text text-muted small mt-2">
                                <i class="bi bi-info-circle me-1"></i> Nama ini akan otomatis muncul pada bagian tanda tangan dokumen resmi (KRS, KHS, Transkrip).
                            </div>
                        </div>

                        {{-- Tambahkan field pengaturan lain di sini jika ada --}}
                        {{-- Contoh: Alamat Kampus, Logo, dll --}}

                        <div class="d-flex justify-content-end pt-3 border-top">
                            <button type="submit" class="btn btn-primary rounded-0 py-2 px-4 uppercase fw-bold small shadow-sm">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        {{-- Kolom Kanan: Card Petunjuk / Informasi Tambahan --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-header bg-light py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                    <i class="bi bi-lightbulb-fill text-warning me-2"></i>Petunjuk Pengisian
                </div>
                <div class="card-body p-4">
                    <ul class="small text-muted ps-3 mb-0">
                        <li class="mb-2">Pastikan nama pimpinan ditulis lengkap dengan gelar akademik.</li>
                        <li>Perubahan nama pimpinan akan langsung berlaku pada semua cetakan dokumen baru.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
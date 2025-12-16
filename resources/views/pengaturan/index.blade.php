@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 fw-bold">Pengaturan Sistem</h2>
            <p class="text-muted mb-0">Konfigurasi umum aplikasi SIAKAD.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-gear-fill me-2"></i>Informasi Instansi & Pimpinan</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.pengaturan.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="nama_rektor" class="form-label fw-bold">Nama Ketua / Rektor</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person-badge"></i></span>
                                <input type="text" class="form-control" id="nama_rektor" name="nama_rektor"
                                       value="{{ old('nama_rektor', $pengaturans['nama_rektor']->value ?? '') }}" 
                                       placeholder="Contoh: Dr. Nama Lengkap, M.Th.">
                            </div>
                            <div class="form-text text-muted small mt-1">
                                <i class="bi bi-info-circle me-1"></i> Nama ini akan otomatis muncul pada bagian tanda tangan dokumen resmi (KRS, KHS, Transkrip).
                            </div>
                        </div>

                        {{-- Tambahkan field pengaturan lain di sini jika ada --}}
                        {{-- Contoh: Alamat Kampus, Logo, dll --}}

                        <div class="d-flex justify-content-end pt-3 border-top">
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        {{-- Card Informasi Tambahan (Opsional) --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-lightbulb-fill text-warning me-2"></i>Petunjuk</h6>
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
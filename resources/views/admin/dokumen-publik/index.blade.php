@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Manajemen Dokumen Publik</h3>
            <span class="text-muted small uppercase">Repositori File & Arsip Resmi Institusi</span>
        </div>
        <div>
            <a href="{{ route('admin.dokumen-publik.create') }}" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm">
                <i class="bi bi-cloud-arrow-up-fill me-1"></i> Unggah Dokumen Baru
            </a>
        </div>
    </div>

    {{-- NOTIFIKASI SISTEM --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm small fw-bold uppercase" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- SMART FILTER BAR FLAT --}}
    <div class="card border-0 shadow-sm mb-4 rounded-0 bg-light">
        <div class="card-body p-3">
            <form action="{{ route('admin.dokumen-publik.index') }}" method="GET" id="filterForm">
                <div class="row g-2 align-items-end">
                    {{-- Pencarian Teks --}}
                    <div class="col-md-5">
                        <label class="form-label text-muted small fw-bold mb-1 uppercase">Pencarian Judul Dokumen</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white rounded-0"><i class="bi bi-search text-dark"></i></span>
                            <input type="text" class="form-control rounded-0 font-monospace" placeholder="Ketik kata kunci pencarian..." name="search" value="{{ request('search') }}">
                        </div>
                    </div>
                    
                    {{-- Filter Kategori --}}
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold mb-1 uppercase">Saring Berdasarkan Kategori</label>
                        <select class="form-select rounded-0 uppercase small fw-bold" name="kategori" onchange="document.getElementById('filterForm').submit()">
                            <option value="">-- SEMUA KATEGORI ARSIP --</option>
                            @if(isset($kategoris) && $kategoris->isNotEmpty())
                                @foreach($kategoris as $kat)
                                    <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ strtoupper($kat) }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    
                    {{-- Tombol Aksi Filter --}}
                    <div class="col-md-3 d-grid d-md-flex gap-2">
                        <button type="submit" class="btn btn-dark rounded-0 px-3 w-100 uppercase fw-bold small">TERAPKAN</button>
                        <a href="{{ route('admin.dokumen-publik.index') }}" class="btn btn-outline-dark rounded-0 px-3 w-100 uppercase fw-bold small">RESET</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABEL DATA ENTERPRISE --}}
    <div class="card border-0 shadow-sm rounded-0 mb-5 border-top border-dark border-4">
        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
            <i class="bi bi-table me-2"></i>Daftar Arsip Digital
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center text-muted small uppercase fw-bold" style="width: 50px;">NO</th>
                            <th class="text-muted small uppercase fw-bold" style="width: 35%">INFORMASI DOKUMEN</th>
                            <th class="text-muted small uppercase fw-bold" style="width: 30%">DESKRIPSI SINGKAT</th>
                            <th class="text-center text-muted small uppercase fw-bold" style="width: 15%">TGL UNGGAH</th>
                            <th class="text-center text-muted small uppercase fw-bold" style="width: 15%">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dokumens as $dokumen)
                            <tr>
                                <td class="text-center text-muted small font-monospace fw-bold">
                                    {{ $loop->iteration + $dokumens->firstItem() - 1 }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-start py-1">
                                        <i class="bi bi-file-earmark-pdf-fill text-danger fs-3 me-3 mt-1"></i>
                                        <div>
                                            <div class="fw-bold text-dark mb-1 lh-sm">{{ $dokumen->judul_dokumen }}</div>
                                            <span class="badge bg-light text-secondary border rounded-0 px-2 py-1 uppercase" style="font-size: 0.65rem;">
                                                <i class="bi bi-folder2-open me-1"></i> {{ $dokumen->kategori ?? 'UMUM' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted small lh-sm">
                                    {{ Str::limit($dokumen->deskripsi ?? 'Tidak ada deskripsi spesifik untuk dokumen ini.', 70) }}
                                </td>
                                <td class="text-center font-monospace small text-muted">
                                    <span class="d-block text-dark fw-bold">{{ $dokumen->created_at->format('d/m/Y') }}</span>
                                    <span style="font-size: 0.70rem;">{{ $dokumen->created_at->format('H:i') }} WIT</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group rounded-0 shadow-sm" role="group">
                                        <a href="{{ route('admin.dokumen-publik.show', $dokumen->id) }}" class="btn btn-sm btn-outline-info border-secondary-subtle rounded-0 py-1 px-2" title="Lihat Detail Dokumen">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        <a href="{{ asset('storage/' . $dokumen->file_path) }}" target="_blank" class="btn btn-sm btn-outline-success border-secondary-subtle rounded-0 py-1 px-2" title="Buka / Unduh Berkas">
                                            <i class="bi bi-cloud-arrow-down-fill"></i>
                                        </a>
                                        <form action="{{ route('admin.dokumen-publik.destroy', $dokumen->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-secondary-subtle rounded-0 py-1 px-2" onclick="return confirm('Peringatan: Dokumen yang dihapus tidak dapat dikembalikan. Lanjutkan?');" title="Hapus Dokumen">
                                                <i class="bi bi-trash3-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted small uppercase">
                                    <i class="bi bi-folder-x fs-1 d-block mb-3 text-secondary opacity-50"></i>
                                    Belum ada dokumen publik yang didaftarkan ke dalam sistem.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($dokumens->hasPages())
            <div class="card-footer bg-light border-top py-3 rounded-0">
                <div class="d-flex justify-content-center">
                    {{ $dokumens->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
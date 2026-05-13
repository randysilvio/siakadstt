@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0 text-dark fw-bold">Manajemen Dokumen Publik</h3>
            <span class="text-muted small">Repositori file dan dokumen resmi institusi</span>
        </div>
        <a href="{{ route('admin.dokumen-publik.create') }}" class="btn btn-primary">
            <i class="bi bi-upload me-2"></i>Unggah Dokumen Baru
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 bg-success text-white py-2 px-3 rounded-1 mb-4" role="alert">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter Bar Formal --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.dokumen-publik.index') }}" method="GET">
                <div class="row g-3 align-items-center">
                    <div class="col-md-10">
                        <label class="form-label text-muted small fw-bold mb-1">PENCARIAN DOKUMEN</label>
                        <input type="text" class="form-control rounded-1" placeholder="Ketik judul dokumen yang dicari..." name="search" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2 mt-4 text-end d-grid">
                        <button type="submit" class="btn btn-dark rounded-1">Terapkan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th class="text-center text-muted small" style="width: 50px;">NO</th>
                            <th class="text-muted small" style="width: 30%">JUDUL DOKUMEN</th>
                            <th class="text-muted small" style="width: 35%">DESKRIPSI</th>
                            <th class="text-muted small" style="width: 15%">TANGGAL UNGGAH</th>
                            <th class="text-end text-muted small pe-4" style="width: 15%">TINDAKAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dokumens as $dokumen)
                            <tr>
                                <td class="text-center text-muted">{{ $loop->iteration + $dokumens->firstItem() - 1 }}</td>
                                <td class="fw-bold text-dark">
                                    <i class="bi bi-file-earmark-text text-secondary me-2"></i>
                                    {{ $dokumen->judul_dokumen }}
                                </td>
                                <td class="text-muted small">
                                    {{ Str::limit($dokumen->deskripsi ?? '-', 80) }}
                                </td>
                                <td>
                                    {{ $dokumen->created_at->translatedFormat('d M Y') }}
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ asset('storage/' . $dokumen->file_path) }}" target="_blank" class="btn btn-sm btn-light border text-dark me-1" title="Unduh Dokumen">Unduh</a>
                                    <form action="{{ route('admin.dokumen-publik.destroy', $dokumen->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus dokumen publik ini secara permanen?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada dokumen publik yang didaftarkan ke dalam sistem.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($dokumens->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $dokumens->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
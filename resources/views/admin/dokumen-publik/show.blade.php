@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- HEADER HALAMAN --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Detail Dokumen Publik</h3>
            <span class="text-muted small uppercase">Rincian Informasi Berkas yang Dipublikasikan</span>
        </div>
        <div>
            <a href="{{ route('admin.dokumen-publik.index') }}" class="btn btn-outline-dark btn-sm rounded-0 px-3 uppercase fw-bold small">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-lg-8 mx-auto mb-5">
        <div class="card-header bg-dark text-white py-3 rounded-0">
            <div class="d-flex align-items-center">
                <i class="bi bi-file-earmark-pdf-fill fs-3 text-danger me-3 bg-white px-2 py-1 rounded-0"></i>
                <div>
                    <h5 class="mb-0 fw-bold uppercase">{{ $dokumen_publik->judul_dokumen }}</h5>
                    <span class="badge bg-light text-dark mt-1 rounded-0 font-monospace">{{ strtoupper($dokumen_publik->kategori ?? 'UMUM') }}</span>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <table class="table table-bordered mb-0 small">
                <tbody>
                    <tr>
                        <th class="ps-4 py-3 text-dark uppercase w-25 bg-light">Tanggal Rilis</th>
                        <td class="py-3 ps-3 fw-bold text-dark font-monospace">
                            {{ $dokumen_publik->created_at->translatedFormat('l, d F Y') }} ({{ $dokumen_publik->created_at->format('H:i') }} WIT)
                        </td>
                    </tr>
                    <tr>
                        <th class="ps-4 py-3 text-dark uppercase bg-light">Deskripsi</th>
                        <td class="py-3 ps-3 text-dark uppercase">
                            @if($dokumen_publik->deskripsi)
                                {!! nl2br(e($dokumen_publik->deskripsi)) !!}
                            @else
                                <span class="text-muted font-monospace">TIDAK ADA DESKRIPSI TAMBAHAN.</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="ps-4 py-3 text-dark uppercase bg-light">Lokasi Berkas</th>
                        <td class="py-3 ps-3">
                            <span class="font-monospace small bg-light p-1 border rounded-0 text-dark">
                                /storage/{{ $dokumen_publik->file_path }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="card-footer bg-light border-top py-3 rounded-0 text-end">
            <a href="{{ asset('storage/' . $dokumen_publik->file_path) }}" target="_blank" class="btn btn-sm btn-primary px-4 py-2 rounded-0 uppercase fw-bold small shadow-sm">
                <i class="bi bi-cloud-arrow-down me-1"></i> Unduh / Buka Dokumen
            </a>
            
            <form action="{{ route('admin.dokumen-publik.destroy', $dokumen_publik->id) }}" method="POST" class="d-inline ms-2" onsubmit="return confirm('Hapus dokumen publik ini secara permanen?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger px-4 py-2 rounded-0 uppercase fw-bold small">
                    <i class="bi bi-trash me-1"></i> Hapus Berkas
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
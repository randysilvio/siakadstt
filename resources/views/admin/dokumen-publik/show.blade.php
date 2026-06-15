@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center mt-3">
        <div class="col-lg-8">
            {{-- HEADER HALAMAN --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-0 text-dark fw-bold">Detail Dokumen Publik</h3>
                    <span class="text-muted small">Rincian informasi berkas yang dipublikasikan</span>
                </div>
                <a href="{{ route('admin.dokumen-publik.index') }}" class="btn btn-outline-dark btn-sm rounded-1">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-2 overflow-hidden">
                {{-- KOP KARTU --}}
                <div class="card-header bg-dark text-white py-3 border-0">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-file-earmark-pdf fs-3 text-danger me-3 bg-white px-2 py-1 rounded"></i>
                        <div>
                            <h5 class="mb-0 fw-bold">{{ $dokumen_publik->judul_dokumen }}</h5>
                            <span class="badge bg-light text-dark mt-1 border">{{ strtoupper($dokumen_publik->kategori ?? 'UMUM') }}</span>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr class="border-bottom">
                                <th class="ps-4 py-3 text-muted small uppercase w-25 bg-light">Tanggal Rilis</th>
                                <td class="py-3 fw-bold text-dark">
                                    {{ $dokumen_publik->created_at->translatedFormat('l, d F Y') }}
                                    <span class="text-muted fw-normal ms-2">({{ $dokumen_publik->created_at->format('H:i') }} WIT)</span>
                                </td>
                            </tr>
                            <tr class="border-bottom">
                                <th class="ps-4 py-3 text-muted small uppercase bg-light">Deskripsi</th>
                                <td class="py-3 text-dark">
                                    @if($dokumen_publik->deskripsi)
                                        {!! nl2br(e($dokumen_publik->deskripsi)) !!}
                                    @else
                                        <span class="text-muted font-monospace italic">Tidak ada deskripsi tambahan.</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-4 py-3 text-muted small uppercase bg-light">Lokasi Berkas</th>
                                <td class="py-3">
                                    <span class="font-monospace small bg-light p-1 border rounded text-muted">
                                        /storage/{{ $dokumen_publik->file_path }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                {{-- FOOTER KARTU (AKSI) --}}
                <div class="card-footer bg-white border-top py-4 text-center">
                    <a href="{{ asset('storage/' . $dokumen_publik->file_path) }}" target="_blank" class="btn btn-primary px-5 rounded-1 fw-bold shadow-sm">
                        <i class="bi bi-cloud-arrow-down me-2"></i> Unduh / Buka Dokumen
                    </a>
                    
                    <form action="{{ route('admin.dokumen-publik.destroy', $dokumen_publik->id) }}" method="POST" class="d-inline ms-2" onsubmit="return confirm('Hapus dokumen publik ini secara permanen?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger px-4 rounded-1 fw-bold">
                            <i class="bi bi-trash me-2"></i> Hapus Berkas
                        </button>
                    </form>
                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection
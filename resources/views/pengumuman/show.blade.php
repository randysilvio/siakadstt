@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Pratinjau Internal Publikasi</h3>
            <span class="text-muted small uppercase">Validasi Tata Letak & Keterbacaan Konten Trix WYSIWYG</span>
        </div>
        <div>
            <a href="{{ route('admin.pengumuman.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali ke Daftar
            </a>
        </div>
    </div>

    <div class="row justify-content-center mb-5">
        <div class="col-md-10">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4">
                {{-- Header Pembungkus --}}
                <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
                    <i class="bi bi-file-text-fill me-2"></i>{{ $pengumuman->judul }}
                </div>
                
                {{-- Area Utama --}}
                <div class="card-body p-5">
                    
                    {{-- Blok Informasi Metadata Monospace --}}
                    <div class="p-3 bg-light border border-dark border-opacity-25 rounded-0 mb-4 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="small font-monospace text-muted uppercase d-block">WAKTU DISTRIBUSI SISTEM:</span>
                            <strong class="font-monospace text-dark fs-6">{{ $pengumuman->created_at->format('d F Y - H:i') }} WIT</strong>
                        </div>
                        <div class="text-end">
                            <span class="small font-monospace text-muted uppercase d-block">TARGET AKSES PENGGUNA:</span>
                            <span class="badge bg-dark rounded-0 font-monospace uppercase fw-bold px-3 py-1" style="font-size: 11px;">
                                {{ $pengumuman->target_role === 'tendik' ? 'TENDIK' : strtoupper($pengumuman->target_role) }}
                            </span>
                        </div>
                    </div>
                    
                    {{-- Render HTML Murni Tanpa Escaping --}}
                    <div class="text-dark" style="line-height: 1.8;">
                        {!! $pengumuman->konten !!}
                    </div>
                </div>
                
                {{-- Kaki Kartu --}}
                <div class="card-footer bg-light border-top py-3 rounded-0 text-end">
                    <a href="{{ route('admin.pengumuman.edit', $pengumuman->id) }}" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm me-1">
                        <i class="bi bi-pencil-square me-1"></i> Edit Konten Ini
                    </a>
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small">
                        Tutup Pratinjau
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
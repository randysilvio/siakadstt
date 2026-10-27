@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Pengumuman Internal</h3>
            <span class="text-muted small uppercase">{{ ucfirst($pengumuman->kategori) }} untuk sivitas akademika</span>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    <div class="row justify-content-center mb-5">
        <div class="col-md-10">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
                    <i class="bi bi-megaphone-fill me-2"></i>{{ $pengumuman->judul }}
                </div>

                <div class="card-body p-5">
                    <div class="p-3 bg-light border border-dark border-opacity-25 rounded-0 mb-4 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="small font-monospace text-muted uppercase d-block">DITERBITKAN:</span>
                            <strong class="font-monospace text-dark fs-6">{{ $pengumuman->created_at->translatedFormat('d F Y - H:i') }} WIT</strong>
                        </div>
                        <div class="text-end">
                            <span class="small font-monospace text-muted uppercase d-block">DITUJUKAN UNTUK:</span>
                            <span class="badge bg-dark rounded-0 font-monospace uppercase fw-bold px-3 py-1" style="font-size: 11px;">
                                {{ $pengumuman->target_role === 'tendik' ? 'TENDIK' : strtoupper($pengumuman->target_role) }}
                            </span>
                        </div>
                    </div>

                    @if($pengumuman->foto)
                        <img src="{{ asset('storage/' . $pengumuman->foto) }}" alt="{{ $pengumuman->judul }}" class="img-fluid border mb-4">
                    @endif

                    {{-- Konten sudah disanitasi saat disimpan --}}
                    <div class="text-dark" style="line-height: 1.8;">
                        {!! \App\Support\HtmlSanitizer::clean($pengumuman->konten) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

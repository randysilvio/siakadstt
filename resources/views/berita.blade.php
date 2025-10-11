@extends('layouts.app')

@section('content')
<div class="container py-8 mx-auto px-4">
    <h1 class="text-3xl font-bold mb-8 text-gray-800">Semua Berita & Pengumuman</h1>

    @if($semuaBerita->isEmpty())
        <div class="text-center py-16">
            <p class="text-gray-500 text-lg">Saat ini belum ada berita atau pengumuman yang tersedia.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            @foreach($semuaBerita as $berita)
                
                {{-- KARTU KONTEN SEBAGAI PEMICU POP-UP --}}
                <div class="bg-white rounded-lg shadow-md p-6 hover:-translate-y-2 transition-transform duration-300 cursor-pointer" 
                     data-bs-toggle="modal" 
                     data-bs-target="#detailBeritaModal{{ $berita->id }}">
                
                    <p class="text-sm text-gray-500 mb-1">
                        {{ \Carbon\Carbon::parse($berita->created_at)->translatedFormat('d F Y') }}
                    </p>
                
                    <h2 class="text-lg font-bold text-gray-900 truncate" title="{{ $berita->judul }}">
                        {{ $berita->judul }}
                    </h2>
                
                    <p class="text-gray-700 mt-2">
                        {!! Str::limit(strip_tags($berita->konten), 75) !!}
                    </p>
                </div>
                
                {{-- POP-UP (MODAL) DETAIL BERITA --}}
                <div class="modal fade" id="detailBeritaModal{{ $berita->id }}" tabindex="-1" aria-labelledby="detailBeritaModalLabel{{ $berita->id }}" aria-hidden="true">
                  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title font-bold" id="detailBeritaModalLabel{{ $berita->id }}">{{ $berita->judul }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <p class="text-sm text-gray-500 mb-4">
                            Dipublikasikan pada: {{ \Carbon\Carbon::parse($berita->created_at)->translatedFormat('d F Y, H:i') }}
                        </p>
                        <hr class="mb-4">
                        {!! $berita->konten !!}
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                      </div>
                    </div>
                  </div>
                </div>

            @endforeach
        </div>
    
        <div class="mt-8">
            {{ $semuaBerita->links() }}
        </div>
    @endif
</div>
@endsection
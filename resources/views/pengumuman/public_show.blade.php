@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <article>
                <h1 class="mb-3 display-5 fw-bold">{{ $pengumuman->judul }}</h1>
                <div class="mb-4 text-muted">
                    <small>Kategori: {{ Str::ucfirst($pengumuman->kategori) }}</small> |
                    <small>Dipublikasikan: {{ $pengumuman->created_at->format('d F Y') }}</small>
                </div>

                @if($pengumuman->foto)
                    <img src="{{ asset('storage/' . $pengumuman->foto) }}" class="img-fluid rounded mb-4" alt="Foto {{ $pengumuman->judul }}">
                @endif
                
                <div class="fs-5">
                    {!! $pengumuman->konten !!}
                </div>
                
                <hr class="my-4">
                <a href="{{ url()->previous() }}" class="btn btn-secondary">← Kembali</a>
            </article>
        </div>
    </div>
</div>
@endsection
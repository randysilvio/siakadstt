@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h1 class="mb-0">{{ $pengumuman->judul }}</h1>
                </div>
                <div class="card-body">
                    <div class="mb-3 text-muted">
                        <small>Dipublikasikan pada: {{ $pengumuman->created_at->format('d F Y, H:i') }}</small> | 
                        <small>Untuk: {{ Str::ucfirst($pengumuman->target_role) }}</small>
                    </div>
                    <hr>
                    <div class="fs-5">
                        {!! $pengumuman->konten !!}
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
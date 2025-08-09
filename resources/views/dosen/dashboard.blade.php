@extends('layouts.app')
@section('content')
    <h1 class="mb-4">Dashboard Dosen</h1>
    <p>Selamat datang, {{ $dosen->nama_lengkap }}</p>

    <h4 class="mt-5">Mata Kuliah yang Anda Ajar</h4>
    <div class="list-group">
        @forelse ($mata_kuliahs as $mk)
            <a href="{{ route('nilai.show', $mk->id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                {{ $mk->kode_mk }} - {{ $mk->nama_mk }}
                <span class="badge bg-primary rounded-pill">{{ $mk->mahasiswas_count }} Mahasiswa</span>
            </a>
        @empty
            <div class="list-group-item">Anda belum mengajar mata kuliah apapun.</div>
        @endforelse
    </div>
@endsection
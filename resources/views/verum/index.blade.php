@extends('layouts.app')

@push('styles')
<style>
    .class-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .class-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15)!important;
    }
    .card-title a {
        text-decoration: none;
        color: inherit;
    }
    .card-title a:hover {
        text-decoration: underline;
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Kelas Verum Anda</h2>
        {{-- Tombol untuk membuat kelas baru, hanya muncul untuk dosen --}}
        @if(Auth::user()->hasRole('dosen'))
            <a href="{{ route('verum.create') }}" class="btn btn-primary">
                + Buat Kelas Baru
            </a>
        @endif
    </div>

    {{-- Tampilkan pesan error jika ada (misal: tidak ada tahun akademik aktif) --}}
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    {{-- Tampilkan pesan sukses jika ada --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($semuaKelas->isEmpty() && !session('error'))
        <div class="alert alert-info">
            Anda belum terdaftar di kelas Verum manapun pada tahun akademik ini.
        </div>
    @else
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @foreach($semuaKelas as $kelas)
                <div class="col">
                    <div class="card h-100 shadow-sm class-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="{{ route('verum.show', $kelas) }}">
                                    {{ $kelas->nama_kelas }}
                                </a>
                            </h5>
                            {{-- PERBAIKAN: Gunakan optional() untuk keamanan jika relasi tidak ada --}}
                            <p class="card-text text-muted">{{ optional($kelas->mataKuliah)->nama_mk }}</p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            @if(Auth::user()->hasRole('mahasiswa'))
                                {{-- PERBAIKAN: Gunakan optional() untuk keamanan --}}
                                <small class="text-muted">Pengajar: {{ optional(optional($kelas->dosen)->user)->name }}</small>
                            @else {{-- Untuk Dosen --}}
                                <small class="text-muted">Kode Mata Kuliah: {{ optional($kelas->mataKuliah)->kode_mk }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Dasbor Perpustakaan</h2>

    <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Total Judul Buku</div>
                <div class="card-body">
                    <h5 class="card-title">{{ $totalJudul }}</h5>
                    <p class="card-text">Judul unik dalam koleksi.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Total Eksemplar</div>
                <div class="card-body">
                    <h5 class="card-title">{{ $totalEksemplar }}</h5>
                    <p class="card-text">Total semua buku fisik.</p>
                </div>
            </div>
        </div>
    </div>

    <hr>

    <h4>Aksi Cepat</h4>
    <div class="mt-3">
        {{-- PERBAIKAN DI SINI: Menggunakan nama rute yang benar --}}
        <a href="{{ route('perpustakaan.koleksi.index') }}" class="btn btn-lg btn-info">Kelola Koleksi Buku</a>
        {{-- Tombol lain bisa ditambahkan di sini nanti --}}
    </div>
</div>
@endsection

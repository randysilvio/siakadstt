@extends('layouts.app')
@section('content')
    <h1 class="mb-4">Input Nilai Mata Kuliah</h1>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="list-group">
        @foreach ($mata_kuliahs as $mk)
            <a href="{{ route('nilai.show', $mk->id) }}" class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">{{ $mk->kode_mk }} - {{ $mk->nama_mk }}</h5>
                    <small>Semester {{ $mk->semester }}</small>
                </div>
                {{-- Tampilkan nama Dosen Pengampu --}}
                <p class="mb-1">
                    Dosen Pengampu: <strong>{{ $mk->dosen->nama_lengkap ?? 'Belum Ditentukan' }}</strong>
                </p>
            </a>
        @endforeach
    </div>
@endsection
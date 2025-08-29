@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="p-5 mb-4 bg-light rounded-3">
        <div class="container-fluid py-5">
            <h1 class="display-5 fw-bold">Direktori Dosen</h1>
            <p class="col-md-8 fs-4">Temukan informasi mengenai dosen pengajar di STT GPI Papua.</p>
        </div>
    </div>

    {{-- Form Pencarian dan Filter --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('dosen.public.index') }}" method="GET">
                <div class="row g-2 align-items-center">
                    <div class="col-md-8">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama dosen..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary w-100" type="submit">Cari Dosen</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Daftar Dosen --}}
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
        @forelse ($dosens as $dosen)
            <div class="col">
                <div class="card h-100 text-center shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <img src="{{ $dosen->foto_profil_url }}" class="rounded-circle mx-auto mb-3" alt="Foto {{ $dosen->nama_lengkap }}" style="width: 120px; height: 120px; object-fit: cover;">
                        <h5 class="card-title">{{ $dosen->nama_lengkap }}</h5>
                        <p class="card-text text-muted">{{ $dosen->jabatan_akademik ?? 'Dosen' }}</p>
                        <a href="{{ route('dosen.public.show', $dosen->nidn) }}" class="btn btn-outline-primary mt-auto">Lihat Profil</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    Tidak ada data dosen yang ditemukan.
                </div>
            </div>
        @endforelse
    </div>

    {{-- Paginasi --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $dosens->appends(request()->query())->links() }}
    </div>

</div>
@endsection
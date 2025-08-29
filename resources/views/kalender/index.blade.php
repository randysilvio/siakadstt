@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Manajemen Kalender Akademik</h2>
        </div>
        <div class="col-md-6 text-end">
            @can('manage-kalender')
                <a href="{{ route('admin.kalender.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Kegiatan Baru
                </a>
            @endcan
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Judul Kegiatan</th>
                            <th scope="col">Tanggal Mulai</th>
                            <th scope="col">Tanggal Selesai</th>
                            <th scope="col">Target</th>
                            <th scope="col" style="width: 15%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kegiatans as $kegiatan)
                            <tr>
                                <th scope="row">{{ $loop->iteration + ($kegiatans->currentPage() - 1) * $kegiatans->perPage() }}</th>
                                <td>{{ $kegiatan->judul_kegiatan }}</td>
                                <td>{{ $kegiatan->tanggal_mulai->isoFormat('D MMMM YYYY') }}</td>
                                <td>{{ $kegiatan->tanggal_selesai->isoFormat('D MMMM YYYY') }}</td>
                                <td>
                                    {{-- PERBAIKAN: Loop melalui relasi roles --}}
                                    @foreach($kegiatan->roles as $role)
                                        <span class="badge bg-secondary">{{ ucfirst($role->name) }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    @can('manage-kalender')
                                        <a href="{{ route('admin.kalender.edit', $kegiatan->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                        <form action="{{ route('admin.kalender.destroy', $kegiatan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kegiatan ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada data kegiatan akademik.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $kegiatans->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Manajemen Kalender Akademik</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('kalender.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Kegiatan Baru
            </a>
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
                                <th scope="row">{{ $loop->iteration + $kegiatans->firstItem() - 1 }}</th>
                                <td>{{ $kegiatan->judul_kegiatan }}</td>
                                <td>{{ \Carbon\Carbon::parse($kegiatan->tanggal_mulai)->isoFormat('D MMMM YYYY') }}</td>
                                <td>{{ \Carbon\Carbon::parse($kegiatan->tanggal_selesai)->isoFormat('D MMMM YYYY') }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst($kegiatan->target_role) }}</span></td>
                                <td>
                                    <a href="{{ route('kalender.edit', $kegiatan->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('kalender.destroy', $kegiatan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kegiatan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
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
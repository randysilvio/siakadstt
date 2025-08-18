@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Manajemen Slideshow</h1>
        <a href="{{ route('slideshows.create') }}" class="btn btn-primary">Tambah Slide Baru</a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Gambar</th>
                        <th>Judul</th>
                        <th>Urutan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($slides as $slide)
                        <tr>
                            <td>
                                <img src="{{ asset('storage/' . $slide->gambar) }}" alt="{{ $slide->judul }}" class="img-thumbnail" style="width: 150px; height: auto;">
                            </td>
                            <td>{{ $slide->judul ?? '-' }}</td>
                            <td>{{ $slide->urutan }}</td>
                            <td>
                                @if ($slide->is_aktif)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('slideshows.edit', $slide->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('slideshows.destroy', $slide->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus slide ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Belum ada slide yang ditambahkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($slides->hasPages())
        <div class="card-footer">
            {{ $slides->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
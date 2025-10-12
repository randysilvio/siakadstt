@extends('layouts.app')
@section('content')
    <h1 class="mb-4">Manajemen Berita & Pengumuman</h1>
    <a href="{{ route('admin.pengumuman.create') }}" class="btn btn-primary mb-3">
      <i class="bi bi-plus-circle"></i> Buat Baru
    </a>
    
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Judul</th>
                <th>Kategori</th>
                <th>Target</th>
                <th>Tanggal Dibuat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($pengumumans as $item)
            <tr>
                <td>{{ $item->judul }}</td>
                <td>
                    <span class="badge {{ $item->kategori == 'berita' ? 'bg-primary' : 'bg-info' }}">
                        {{ ucfirst($item->kategori) }}
                    </span>
                </td>
                <td>{{ ucfirst($item->target_role) }}</td>
                <td>{{ $item->created_at->format('d M Y H:i') }}</td>
                <td>
                    <a href="{{ route('admin.pengumuman.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('admin.pengumuman.destroy', $item->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin?')">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center">Belum ada berita atau pengumuman.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="d-flex justify-content-center">{{ $pengumumans->links() }}</div>
@endsection
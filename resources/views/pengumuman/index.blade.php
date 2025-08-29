@extends('layouts.app')
@section('content')
    <h1 class="mb-4">Manajemen Pengumuman</h1>
    <a href="{{ route('admin.pengumuman.create') }}" class="btn btn-primary mb-3">Buat Pengumuman Baru</a>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr><th>Judul</th><th>Target</th><th>Tanggal Dibuat</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        @forelse ($pengumumans as $pengumuman)
            <tr>
                <td>{{ $pengumuman->judul }}</td>
                <td>{{ ucfirst($pengumuman->target_role) }}</td>
                <td>{{ $pengumuman->created_at->format('d M Y H:i') }}</td>
                <td>
                    <a href="{{ route('admin.pengumuman.edit', $pengumuman->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('admin.pengumuman.destroy', $pengumuman->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin?')">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center">Belum ada pengumuman.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="d-flex justify-content-center">{{ $pengumumans->links() }}</div>
@endsection
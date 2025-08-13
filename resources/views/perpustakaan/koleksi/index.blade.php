@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manajemen Koleksi Buku</h1>
        {{-- Perbaikan: Menggunakan nama rute yang benar --}}
        <a href="{{ route('perpustakaan.koleksi.create') }}" class="btn btn-primary">+ Tambah Buku Baru</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Pengarang</th>
                            <th>Stok</th>
                            <th>Tersedia</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($koleksis as $koleksi)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $koleksi->judul }}</td>
                            <td>{{ $koleksi->pengarang }}</td>
                            <td>{{ $koleksi->jumlah_stok }}</td>
                            <td>{{ $koleksi->jumlah_tersedia }}</td>
                            <td>
                                {{-- Perbaikan: Menggunakan nama rute yang benar --}}
                                <a href="{{ route('perpustakaan.koleksi.show', $koleksi) }}" class="btn btn-sm btn-info">Lihat</a>
                                <a href="{{ route('perpustakaan.koleksi.edit', $koleksi) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('perpustakaan.koleksi.destroy', $koleksi) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada koleksi buku yang ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                {{ $koleksis->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
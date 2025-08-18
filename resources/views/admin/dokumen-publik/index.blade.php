@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Manajemen Dokumen Publik</h1>
        <a href="{{ route('dokumen-publik.create') }}" class="btn btn-primary">Unggah Dokumen Baru</a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Judul Dokumen</th>
                        <th>Deskripsi</th>
                        <th>Tanggal Unggah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dokumens as $dokumen)
                        <tr>
                            <td>{{ $dokumen->judul_dokumen }}</td>
                            <td>{{ $dokumen->deskripsi ?? '-' }}</td>
                            <td>{{ $dokumen->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ asset('storage/' . $dokumen->file_path) }}" class="btn btn-info btn-sm" target="_blank">Lihat</a>
                                <form action="{{ route('dokumen-publik.destroy', $dokumen->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Belum ada dokumen yang diunggah.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($dokumens->hasPages())
        <div class="card-footer">
            {{ $dokumens->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
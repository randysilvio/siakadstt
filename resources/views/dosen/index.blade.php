@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Manajemen Dosen</h1>
        <a href="{{ route('dosen.create') }}" class="btn btn-primary">Tambah Dosen Baru</a>
    </div>

    {{-- Tombol Export & Import --}}
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('dosen.export') }}" class="btn btn-success me-2">
            Export Dosen
        </a>
        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importDosenModal">
            Import Dosen
        </button>
    </div>

    <div class="modal fade" id="importDosenModal" tabindex="-1" aria-labelledby="importDosenModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importDosenModalLabel">Import Data Dosen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('dosen.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label">Pilih file Excel (.xlsx, .xls)</label>
                            <input class="form-control @error('file') is-invalid @enderror" type="file" name="file" id="file" required>
                            
                            {{-- PERBAIKAN: Menambahkan tempat untuk menampilkan error file --}}
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <div class="form-text mt-2">
                                Pastikan file Excel Anda memiliki kolom: <strong>nidn, nama_lengkap, email, password</strong>.
                                <a href="{{ route('dosen.import.template') }}">Download Template Disini</a>.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>NIDN</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dosens as $dosen)
                            <tr>
                                <td>{{ $dosen->nidn }}</td>
                                <td>{{ $dosen->nama_lengkap }}</td>
                                <td>{{ $dosen->user->email ?? '-' }}</td>
                                <td>{{ $dosen->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('dosen.edit', $dosen->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('dosen.destroy', $dosen->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data dosen ini? Ini juga akan menghapus akun user terkait.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Belum ada data dosen.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($dosens->hasPages())
        <div class="card-footer">
            {{ $dosens->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
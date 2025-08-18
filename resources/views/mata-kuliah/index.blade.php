@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Manajemen Mata Kuliah</h1>
        <a href="{{ route('mata-kuliah.create') }}" class="btn btn-primary">Tambah Mata Kuliah Baru</a>
    </div>

    {{-- Tombol Export & Import --}}
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('mata-kuliah.export') }}" class="btn btn-success me-2">
            Export Mata Kuliah
        </a>
        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importMataKuliahModal">
            Import Mata Kuliah
        </button>
    </div>

    <div class="modal fade" id="importMataKuliahModal" tabindex="-1" aria-labelledby="importMataKuliahModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importMataKuliahModalLabel">Import Data Mata Kuliah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('mata-kuliah.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label">Pilih file Excel (.xlsx, .xls)</label>
                            <input class="form-control" type="file" name="file" id="file" required>
                            <div class="form-text">
                                Pastikan file Excel Anda memiliki kolom: <strong>kode_mk, nama_mk, sks, semester, nidn_dosen</strong>.
                                <a href="{{ route('mata-kuliah.import.template') }}">Download Template Disini</a>.
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
                            <th>Kode MK</th>
                            <th>Nama Mata Kuliah</th>
                            <th>SKS</th>
                            <th>Semester</th>
                            <th>Dosen Pengampu</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mata_kuliahs as $matkul)
                            <tr>
                                <td>{{ $matkul->kode_mk }}</td>
                                <td>{{ $matkul->nama_mk }}</td>
                                <td>{{ $matkul->sks }}</td>
                                <td>{{ $matkul->semester }}</td>
                                <td>{{ $matkul->dosen->nama_lengkap ?? 'Belum ditentukan' }}</td>
                                <td>
                                    <a href="{{ route('mata-kuliah.edit', $matkul->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('mata-kuliah.destroy', $matkul->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus mata kuliah ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada data mata kuliah.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($mata_kuliahs->hasPages())
        <div class="card-footer">
            {{ $mata_kuliahs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
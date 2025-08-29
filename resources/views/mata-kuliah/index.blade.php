@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Manajemen Mata Kuliah</h1>
        {{-- PERBAIKAN: Menambahkan prefix 'admin.' pada nama rute --}}
        <a href="{{ route('admin.mata-kuliah.create') }}" class="btn btn-primary">Tambah Mata Kuliah Baru</a>
    </div>

    {{-- Tombol Export & Import --}}
    <div class="d-flex justify-content-end mb-3">
        {{-- PERBAIKAN: Menambahkan prefix 'admin.' pada nama rute --}}
        <a href="{{ route('admin.mata-kuliah.export') }}" class="btn btn-success me-2">Export Mata Kuliah</a>
        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importMataKuliahModal">Import Mata Kuliah</button>
    </div>

    {{-- Modal Box untuk Import --}}
    <div class="modal fade" id="importMataKuliahModal" tabindex="-1" aria-labelledby="importMataKuliahModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importMataKuliahModalLabel">Import Data Mata Kuliah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                {{-- PERBAIKAN: Menambahkan prefix 'admin.' pada nama rute --}}
                <form action="{{ route('admin.mata-kuliah.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label">Pilih file Excel (.xlsx, .xls)</label>
                            <input class="form-control" type="file" id="file" name="file" required>
                        </div>
                        {{-- PERBAIKAN: Menambahkan prefix 'admin.' pada nama rute --}}
                        <a href="{{ route('admin.mata-kuliah.import.template') }}">Unduh Template Excel</a>
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
            {{-- PERBAIKAN: Menambahkan prefix 'admin.' pada nama rute --}}
            <form action="{{ route('admin.mata-kuliah.index') }}" method="GET" class="mb-4">
                <div class="row g-2">
                    <div class="col-md-8">
                        <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan Kode atau Nama MK..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="semester" class="form-select">
                            <option value="">Semua Semester</option>
                            @for ($i = 1; $i <= 8; $i++)
                                <option value="{{ $i }}" {{ request('semester') == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-primary w-100" type="submit">Cari</button>
                    </div>
                </div>
            </form>

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
                                    {{-- PERBAIKAN: Menambahkan prefix 'admin.' pada nama rute --}}
                                    <a href="{{ route('admin.mata-kuliah.edit', $matkul->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                    {{-- PERBAIKAN: Menambahkan prefix 'admin.' pada nama rute --}}
                                    <form action="{{ route('admin.mata-kuliah.destroy', $matkul->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus mata kuliah ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    @if(request('search') || request('semester'))
                                        Data tidak ditemukan untuk filter yang diterapkan.
                                    @else
                                        Belum ada data mata kuliah.
                                    @endif
                                </td>
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
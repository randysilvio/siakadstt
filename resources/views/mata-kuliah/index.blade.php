@extends('layouts.app')

@section('content')
    <h1 class="mb-4">Manajemen Mata Kuliah</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <a href="/mata-kuliah/create" class="btn btn-primary mb-3">Tambah Mata Kuliah Baru</a>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Kode MK</th>
                <th>Nama Mata Kuliah</th>
                <th>SKS</th>
                <th>Semester</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($mata_kuliahs as $mk)
                <tr>
                    <td>{{ $mk->kode_mk }}</td>
                    <td>{{ $mk->nama_mk }}</td>
                    <td>{{ $mk->sks }}</td>
                    <td>{{ $mk->semester }}</td>
                    <td>
                        <a href="/mata-kuliah/{{ $mk->id }}/edit" class="btn btn-warning btn-sm">Edit</a>
                        <form action="/mata-kuliah/{{ $mk->id }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Data mata kuliah masih kosong.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
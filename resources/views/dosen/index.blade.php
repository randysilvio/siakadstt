@extends('layouts.app')
@section('content')
    <h1 class="mb-4">Manajemen Dosen</h1>
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <a href="{{ route('dosen.create') }}" class="btn btn-primary mb-3">Tambah Dosen Baru</a>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>NIDN</th>
                <th>Nama Lengkap</th>
                <th>Akun Login (Email)</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($dosens as $dosen)
            <tr>
                <td>{{ $dosen->nidn }}</td>
                <td>{{ $dosen->nama_lengkap }}</td>
                <td>{{ $dosen->user->email ?? 'Tidak ada' }}</td>
                <td>
                    <a href="{{ route('dosen.edit', $dosen->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('dosen.destroy', $dosen->id) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin?')">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center">Data masih kosong.</td></tr>
        @endforelse
        </tbody>
    </table>
@endsection
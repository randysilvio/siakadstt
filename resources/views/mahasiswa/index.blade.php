@extends('layouts.app')
@section('content')
    <h1 class="mb-4">Daftar Mahasiswa</h1>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="row mb-3">
        <div class="col-md-6">
            <a href="{{ route('mahasiswa.create') }}" class="btn btn-primary">Tambah Mahasiswa Baru</a>
        </div>
        <div class="col-md-6">
            <form action="/mahasiswa" method="GET">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Cari..." name="search" value="{{ request('search') }}">
                    <button class="btn btn-primary" type="submit">Cari</button>
                </div>
            </form>
        </div>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>NIM</th>
                <th>Nama Lengkap</th>
                <th>Program Studi</th>
                <th>Akun Login (Email)</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($mahasiswas as $mahasiswa)
                <tr>
                    <td>{{ $mahasiswa->nim }}</td>
                    <td>{{ $mahasiswa->nama_lengkap }}</td>
                    <td>{{ $mahasiswa->programStudi->nama_prodi }}</td>
                    <td>{{ $mahasiswa->user->email ?? 'Belum Ada Akun' }}</td>
                    <td>
                        <a href="{{ route('mahasiswa.edit', $mahasiswa->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('mahasiswa.destroy', $mahasiswa->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin?')">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Data tidak ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-center">
        {{ $mahasiswas->appends(request()->query())->links() }}
    </div>
@endsection
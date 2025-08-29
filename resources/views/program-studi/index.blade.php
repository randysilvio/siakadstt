@extends('layouts.app')

@section('content')
    <h1 class="mb-4">Manajemen Program Studi</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    {{-- PERBAIKAN: Menggunakan helper route() dengan nama yang benar --}}
    <a href="{{ route('admin.program-studi.create') }}" class="btn btn-primary mb-3">Tambah Program Studi Baru</a>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Nama Program Studi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($program_studis as $key => $prodi)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $prodi->nama_prodi }}</td>
                    <td>
                        {{-- PERBAIKAN: Menggunakan helper route() dengan nama yang benar --}}
                        <a href="{{ route('admin.program-studi.edit', $prodi->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        {{-- PERBAIKAN: Menggunakan helper route() dengan nama yang benar --}}
                        <form action="{{ route('admin.program-studi.destroy', $prodi->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">Data program studi masih kosong.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
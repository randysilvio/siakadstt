@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Notifikasi Sukses --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Manajemen Pengguna & Peran</h1>
        {{-- ====================================================== --}}
        {{-- ===== PENAMBAHAN TOMBOL BARU ===== --}}
        {{-- Tombol ini mengarah ke form pembuatan pengguna baru --}}
        <a href="{{ route('admin.tendik.create') }}" class="btn btn-primary">
             + Tambah Pengguna Baru
        </a>
        {{-- ====================================================== --}}
    </div>

    <div class="card">
        <div class="card-body">
            {{-- Form Pencarian (opsional, tapi baik untuk ada) --}}
            <form action="{{ route('user.index') }}" method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan nama atau email..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">Cari</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Peran</th>
                            <th style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Diasumsikan variabel dari UserController adalah $users --}}
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    {{-- Menampilkan semua peran yang dimiliki user --}}
                                    @foreach($user->roles as $role)
                                        <span class="badge bg-secondary">{{ $role->display_name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    {{-- Tombol untuk mengedit peran user --}}
                                    <a href="{{ route('user.edit', $user->id) }}" class="btn btn-warning btn-sm">Ubah Peran</a>
                                    {{-- Form untuk menghapus user --}}
                                    <form action="{{ route('user.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada data pengguna.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        {{-- Pagination Links --}}
        @if($users->hasPages())
        <div class="card-footer">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

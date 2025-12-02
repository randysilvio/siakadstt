@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Manajemen Pengguna</h1>
        <a href="{{ route('admin.tendik.create') }}" class="btn btn-primary">
             <i class="bi bi-person-plus-fill"></i> Tambah Pengguna Baru
        </a>
    </div>

    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Smart Filter Bar --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body bg-light">
            <form action="{{ route('admin.user.index') }}" method="GET" id="filterForm">
                <div class="row g-2">
                    {{-- Pencarian Teks --}}
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari Nama atau Email..." value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Filter Role --}}
                    <div class="col-md-4">
                        <select class="form-select" name="role_id" onchange="document.getElementById('filterForm').submit()">
                            <option value="" {{ request('role_id') == '' ? 'selected' : '' }}>-- Semua Peran --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tombol Reset --}}
                    <div class="col-md-3">
                        <a href="{{ route('admin.user.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </div>
                <button type="submit" class="d-none"></button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Pengguna</th>
                            <th>Email</th>
                            <th>Peran / Hak Akses</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="fw-bold">{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @foreach($user->roles as $role)
                                        <span class="badge bg-secondary me-1">{{ $role->display_name }}</span>
                                    @endforeach
                                    @if($user->roles->isEmpty())
                                        <span class="text-muted small fst-italic">Tidak ada peran</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.user.edit', $user->id) }}" class="btn btn-sm btn-outline-warning" title="Ubah Peran">
                                            <i class="bi bi-shield-lock"></i>
                                        </a>
                                        <form action="{{ route('admin.user.destroy', $user->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('PERINGATAN: Menghapus user ini juga akan menghapus data Dosen/Mahasiswa yang terkait. Lanjutkan?');" title="Hapus User">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-2"></i>
                                    Data pengguna tidak ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    {{-- Pagination --}}
    @if($users->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $users->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection
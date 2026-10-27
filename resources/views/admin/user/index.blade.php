@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Manajemen Akun Pengguna</h3>
            <span class="text-muted small uppercase">Pengaturan Kredensial & Otoritas Akses Sistem Terintegrasi</span>
        </div>
        <div>
            <a href="{{ route('admin.tendik.create') }}" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm">
                 <i class="bi bi-person-plus-fill me-1"></i> Tambah Pegawai / User
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Filter Panel Flat --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-light">
        <div class="card-body p-4">
            <form action="{{ route('admin.user.index') }}" method="GET" id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label text-dark small fw-bold uppercase mb-1">Pencarian Akun</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white rounded-0"><i class="bi bi-search text-dark"></i></span>
                            <input type="text" name="search" class="form-control rounded-0 font-monospace uppercase" placeholder="CARI NAMA ATAU EMAIL..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-dark small fw-bold uppercase mb-1">Otoritas Peran (Role)</label>
                        <select class="form-select rounded-0 uppercase small fw-bold" name="role_id" onchange="document.getElementById('filterForm').submit()">
                            <option value="" {{ request('role_id') == '' ? 'selected' : '' }}>-- SEMUA HAK AKSES --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ strtoupper($role->display_name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-0 flex-grow-1 uppercase fw-bold small py-2 shadow-sm">Filter</button>
                        <a href="{{ route('admin.user.index') }}" class="btn btn-outline-dark rounded-0 px-3 py-2 uppercase fw-bold small">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Akun Pengguna Sistem
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 6%;">NO</th>
                            <th class="text-start" style="width: 28%;">NAMA PENGGUNA</th>
                            <th class="text-start" style="width: 26%;">ALAMAT EMAIL (LOGIN)</th>
                            <th style="width: 20%;">HAK AKSES / PERAN</th>
                            <th style="width: 20%;">KONTROL MANAJEMEN</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse ($users as $index => $user)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted">{{ $users->firstItem() + $index }}</td>
                                <td class="text-start ps-3">
                                    <span class="fw-bold text-dark uppercase">{{ $user->name }}</span>
                                </td>
                                <td class="text-start ps-3 font-monospace text-muted">{{ $user->email }}</td>
                                <td class="text-center">
                                    @foreach($user->roles as $role)
                                        <span class="badge bg-dark text-white rounded-0 font-monospace uppercase px-2 py-1" style="font-size: 10px;">
                                            {{ strtoupper($role->display_name) }}
                                        </span>
                                    @endforeach
                                    @if($user->roles->isEmpty())
                                        <span class="badge bg-danger text-white rounded-0 font-monospace uppercase px-2 py-1" style="font-size: 10px;">TANPA PERAN</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group rounded-0" role="group">
                                        @if($user->id !== Auth::id() && !$user->hasRole('admin'))
                                            <form action="{{ route('admin.user.impersonate', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Sistem akan melakukan otentikasi menyamar sebagai {{ addslashes($user->name) }}. Lanjutkan?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-dark rounded-0 py-1 px-2 uppercase fw-bold" style="font-size: 10px;" title="Masuk sebagai user ini">
                                                    <i class="bi bi-box-arrow-in-right me-1"></i> Login
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('admin.user.edit', $user->id) }}" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2 uppercase fw-bold" style="font-size: 10px;" title="Konfigurasi Hak Akses">
                                            <i class="bi bi-shield-lock me-1"></i> Role
                                        </a>

                                        @if($user->id !== Auth::id())
                                            <form action="{{ route('admin.user.destroy', $user->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2" onclick="return confirm('Peringatan: Penghapusan akun pengguna akan menghapus data entitas Dosen/Mahasiswa/Tendik yang berelasi. Eksekusi?');" title="Hapus Akun">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 uppercase fw-bold text-muted">
                                    <i class="bi bi-people fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                    Data akun pengguna tidak ditemukan dalam basis data.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($users->hasPages())
            <div class="card-footer bg-white border-top py-3 rounded-0">
                <div class="d-flex justify-content-center">
                    {{ $users->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
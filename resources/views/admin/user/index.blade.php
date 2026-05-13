@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0 text-dark fw-bold">Manajemen Akun Pengguna</h3>
            <span class="text-muted small">Pengaturan kredensial dan otoritas akses sistem terintegrasi</span>
        </div>
        <a href="{{ route('admin.tendik.create') }}" class="btn btn-primary">
             <i class="bi bi-person-plus-fill me-2"></i>Registrasi Pengguna Baru
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 bg-success text-white py-2 px-3 rounded-1 mb-4" role="alert">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter Panel Formal --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.user.index') }}" method="GET" id="filterForm">
                <div class="row g-3 align-items-center">
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold mb-1">PENCARIAN AKUN</label>
                        <input type="text" name="search" class="form-control rounded-1" placeholder="Cari berdasarkan nama lengkap atau alamat email..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold mb-1">OTORITAS PERAN</label>
                        <select class="form-select rounded-1" name="role_id" onchange="document.getElementById('filterForm').submit()">
                            <option value="" {{ request('role_id') == '' ? 'selected' : '' }}>-- Semua Hak Akses --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ strtoupper($role->display_name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mt-4 text-end d-grid">
                        <a href="{{ route('admin.user.index') }}" class="btn btn-light border rounded-1">Reset Filter</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th class="ps-4 py-3 text-muted small">NAMA PENGGUNA</th>
                            <th class="text-muted small">ALAMAT EMAIL (LOGIN)</th>
                            <th class="text-muted small">HAK AKSES / PERAN</th>
                            <th class="text-end text-muted small pe-4" style="width: 250px;">KONTROL MANAJEMEN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-dark">{{ $user->name }}</span>
                                </td>
                                <td class="font-monospace small text-muted">{{ $user->email }}</td>
                                <td>
                                    @foreach($user->roles as $role)
                                        <span class="badge bg-light text-dark border rounded-1 fw-normal">{{ strtoupper($role->display_name) }}</span>
                                    @endforeach
                                    @if($user->roles->isEmpty())
                                        <span class="text-danger small fst-italic">Tanpa Peran</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        @if($user->id !== Auth::id())
                                            <a href="{{ route('admin.user.impersonate', $user->id) }}" class="btn btn-sm btn-dark" title="Masuk sebagai user ini" onclick="return confirm('Sistem akan melakukan otentikasi sebagai {{ $user->name }}. Lanjutkan?');">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Login
                                            </a>
                                        @endif

                                        <a href="{{ route('admin.user.edit', $user->id) }}" class="btn btn-sm btn-light border text-dark ms-1" title="Konfigurasi Hak Akses">
                                            <i class="bi bi-shield-lock me-1"></i> Role
                                        </a>

                                        <form action="{{ route('admin.user.destroy', $user->id) }}" method="POST" class="d-inline ms-1">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger rounded-1" onclick="return confirm('Peringatan: Penghapusan akun pengguna akan berakibat pada hilangnya data entitas Dosen/Mahasiswa yang berelasi. Eksekusi?');" title="Hapus Akun">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">Data akun pengguna tidak ditemukan dalam basis data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    @if($users->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $users->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
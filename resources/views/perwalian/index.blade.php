@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-0 fw-bold">Manajemen Perwalian</h1>
            <p class="text-muted mb-0">Kelola daftar mahasiswa bimbingan akademik Anda.</p>
        </div>
        <div class="card bg-primary text-white border-0 shadow-sm px-3 py-2">
            <div class="d-flex align-items-center">
                <i class="bi bi-people-fill fs-3 me-3"></i>
                <div>
                    <h6 class="mb-0 text-uppercase small opacity-75">Total Bimbingan</h6>
                    <span class="fs-4 fw-bold">{{ $mahasiswa_wali->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- TABEL 1: MAHASISWA PERWALIAN SAAT INI --}}
    <div class="card shadow-sm border-0 mb-5">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-person-check-fill me-2"></i>Mahasiswa Bimbingan Saya</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Program Studi</th>
                            <th>Angkatan</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mahasiswa_wali as $mahasiswa)
                            <tr>
                                <td class="ps-4 fw-bold">{{ $mahasiswa->nim }}</td>
                                <td>{{ $mahasiswa->nama_lengkap }}</td>
                                <td>{{ $mahasiswa->programStudi->nama_prodi }}</td>
                                <td>{{ $mahasiswa->tahun_masuk }}</td>
                                <td class="text-end pe-4">
                                    <form action="{{ route('perwalian.destroy', $mahasiswa->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Hapus {{ $mahasiswa->nama_lengkap }} dari daftar bimbingan?')">
                                            <i class="bi bi-person-dash"></i> Lepas
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-folder2-open fs-1 d-block mb-2"></i>
                                    Anda belum memiliki mahasiswa bimbingan. Silakan tambahkan di bawah.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <hr class="my-5 border-secondary opacity-25">

    {{-- BAGIAN 2: TAMBAH MAHASISWA BARU --}}
    <h3 class="mb-3 fw-bold">Tambah Mahasiswa Bimbingan</h3>
    
    {{-- Smart Filter Bar --}}
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body bg-light rounded">
            <form action="{{ route('perwalian.index') }}" method="GET" id="filterForm">
                <div class="row g-2">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari Nama / NIM..." value="{{ request('search') }}">
                        </div>
                    </div>
                    
                    {{-- [PERBAIKAN] Menggunakan submitFilter() --}}
                    <div class="col-md-3">
                        <select name="program_studi_id" class="form-select" onchange="submitFilter()">
                            <option value="">-- Semua Prodi --</option>
                            @foreach($program_studis as $prodi)
                                <option value="{{ $prodi->id }}" {{ request('program_studi_id') == $prodi->id ? 'selected' : '' }}>
                                    {{ $prodi->nama_prodi }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- [PERBAIKAN] Menggunakan submitFilter() --}}
                    <div class="col-md-3">
                        <select name="angkatan" class="form-select" onchange="submitFilter()">
                            <option value="">-- Angkatan --</option>
                            @foreach($angkatans as $thn)
                                <option value="{{ $thn }}" {{ request('angkatan') == $thn ? 'selected' : '' }}>
                                    {{ $thn }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('perwalian.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </div>
                <button type="submit" class="d-none"></button>
            </form>
        </div>
    </div>

    {{-- Form Tambah Mahasiswa --}}
    <form action="{{ route('perwalian.store') }}" method="POST">
        @csrf
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-secondary">Daftar Mahasiswa Belum Memiliki Wali</h6>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-person-plus-fill me-1"></i> Tambahkan yang Dipilih
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">
                                    <input class="form-check-input" type="checkbox" id="checkAll">
                                </th>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th>Program Studi</th>
                                <th>Angkatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($mahasiswa_tersedia as $mahasiswa)
                                <tr>
                                    <td class="text-center">
                                        <input class="form-check-input student-checkbox" type="checkbox" name="mahasiswa_ids[]" value="{{ $mahasiswa->id }}">
                                    </td>
                                    <td class="fw-bold">{{ $mahasiswa->nim }}</td>
                                    <td>{{ $mahasiswa->nama_lengkap }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $mahasiswa->programStudi->nama_prodi }}</span></td>
                                    <td>{{ $mahasiswa->tahun_masuk }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        @if(request('search') || request('program_studi_id') || request('angkatan'))
                                            <i class="bi bi-search fs-1 d-block mb-2"></i>
                                            Tidak ditemukan mahasiswa yang cocok dengan filter.
                                        @else
                                            <i class="bi bi-check2-all fs-1 d-block mb-2 text-success"></i>
                                            Semua mahasiswa sudah memiliki dosen wali.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($mahasiswa_tersedia->hasPages())
                <div class="card-footer bg-white border-top-0">
                    {{ $mahasiswa_tersedia->links() }}
                </div>
            @endif
        </div>
    </form>
</div>

{{-- SCRIPT JAVASCRIPT UNTUK MENJAGA POSISI SCROLL --}}
<script>
    // 1. Script Check All
    document.getElementById('checkAll').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('.student-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    // 2. Script Simpan Posisi Scroll saat Filter
    function submitFilter() {
        // Simpan posisi scroll saat ini ke Session Storage
        sessionStorage.setItem('scrollPosition', window.scrollY);
        // Submit form
        document.getElementById('filterForm').submit();
    }

    // 3. Script Kembalikan Posisi Scroll setelah Reload
    document.addEventListener("DOMContentLoaded", function(event) { 
        var scrollpos = sessionStorage.getItem('scrollPosition');
        if (scrollpos) {
            window.scrollTo(0, scrollpos);
            sessionStorage.removeItem('scrollPosition');
        }
    });
</script>
@endsection
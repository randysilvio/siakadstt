@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Manajemen Perwalian</h3>
            <span class="text-muted small uppercase">Kelola Mahasiswa Bimbingan Akademik & Validasi Pengisian KRS</span>
        </div>
        <div>
            <div class="bg-light border border-dark border-opacity-25 rounded-0 px-4 py-2 d-flex align-items-center shadow-none">
                <i class="bi bi-people-fill text-dark fs-4 me-3"></i>
                <div>
                    <span class="small text-muted font-monospace uppercase d-block" style="font-size: 10px;">TOTAL BIMBINGAN</span>
                    <span class="fs-5 fw-bold font-monospace text-dark">{{ $mahasiswa_wali->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- NOTIFIKASI SISTEM --}}
    @if (session('success'))
        <div class="alert alert-success border rounded-0 p-3 mb-4 font-monospace small uppercase shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger border rounded-0 p-3 mb-4 font-monospace small uppercase shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        </div>
    @endif

    {{-- TABEL 1: MAHASISWA PERWALIAN SAAT INI --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5 bg-white">
        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
            Daftar Mahasiswa Bimbingan Terdaftar
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-dark text-white small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 15%;">NIM</th>
                            <th class="text-start" style="width: 35%;">NAMA MAHASISWA & PRODI</th>
                            <th style="width: 15%;">ANGKATAN</th>
                            <th style="width: 15%;">STATUS KRS</th>
                            <th style="width: 20%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse ($mahasiswa_wali as $mahasiswa)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-dark">{{ $mahasiswa->nim }}</td>
                                <td class="text-start ps-3">
                                    <div class="uppercase fw-bold text-dark">{{ $mahasiswa->nama_lengkap }}</div>
                                    <span class="text-muted font-monospace" style="font-size: 11px;">{{ optional($mahasiswa->programStudi)->nama_prodi ?? '-' }}</span>
                                </td>
                                <td class="text-center font-monospace">{{ $mahasiswa->tahun_masuk }}</td>
                                <td class="text-center">
                                    @if($mahasiswa->status_krs == 'Disetujui')
                                        <span class="badge bg-success text-white rounded-0 font-monospace uppercase fw-bold px-2 py-1" style="font-size: 10px;">DISETUJUI</span>
                                    @elseif($mahasiswa->status_krs == 'Menunggu Persetujuan')
                                        <span class="badge bg-warning text-dark rounded-0 font-monospace uppercase fw-bold px-2 py-1" style="font-size: 10px;">MENUNGGU</span>
                                    @elseif($mahasiswa->status_krs == 'Ditolak')
                                        <span class="badge bg-danger text-white rounded-0 font-monospace uppercase fw-bold px-2 py-1" style="font-size: 10px;">DITOLAK</span>
                                    @else
                                        <span class="badge bg-secondary text-white rounded-0 font-monospace uppercase fw-bold px-2 py-1" style="font-size: 10px;">DRAFT</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group rounded-0" role="group">
                                        <a href="{{ route('perwalian.show', $mahasiswa->id) }}" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-3 uppercase fw-bold small">
                                            <i class="bi bi-eye me-1"></i> Detail
                                        </a>
                                        <form action="{{ route('perwalian.destroy', $mahasiswa->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Lepas perwalian bimbingan mahasiswa bersangkutan secara permanen?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2" title="Lepas Bimbingan">
                                                <i class="bi bi-person-dash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 uppercase fw-bold text-muted">
                                    <i class="bi bi-folder2-open fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                    Anda belum memiliki alokasi mahasiswa bimbingan akademik.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- BAGIAN 2: TAMBAH MAHASISWA BARU --}}
    <div class="mt-5 mb-3 border-bottom pb-2">
        <h5 class="fw-bold text-dark uppercase mb-1">Klaim Mahasiswa Bimbingan Baru</h5>
        <span class="text-muted small uppercase font-monospace">Penelusuran mahasiswa aktif yang belum memiliki alokasi dosen wali</span>
    </div>

    {{-- Filter Bar Flat --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-light">
        <div class="card-body p-4">
            <form action="{{ route('perwalian.index') }}" method="GET" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold uppercase text-dark">Pencarian Mahasiswa</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white rounded-0"><i class="bi bi-search text-dark"></i></span>
                            <input type="text" name="search" class="form-control rounded-0 font-monospace uppercase" placeholder="CARI NAMA / NIM..." value="{{ request('search') }}">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label small fw-bold uppercase text-dark">Program Studi</label>
                        <select name="program_studi_id" class="form-select rounded-0 uppercase small fw-bold text-dark" onchange="submitFilter()">
                            <option value="">-- SEMUA PRODI --</option>
                            @foreach($program_studis as $prodi)
                                <option value="{{ $prodi->id }}" {{ request('program_studi_id') == $prodi->id ? 'selected' : '' }}>
                                    {{ $prodi->nama_prodi }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label small fw-bold uppercase text-dark">Angkatan</label>
                        <select name="angkatan" class="form-select rounded-0 font-monospace text-dark fw-bold" onchange="submitFilter()">
                            <option value="">-- SEMUA ANGKATAN --</option>
                            @foreach($angkatans as $thn)
                                <option value="{{ $thn }}" {{ request('angkatan') == $thn ? 'selected' : '' }}>
                                    {{ $thn }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <a href="{{ route('perwalian.index') }}" class="btn btn-outline-dark rounded-0 w-100 uppercase fw-bold small py-2 text-center">
                            Reset Filter
                        </a>
                    </div>
                </div>
                <button type="submit" class="d-none"></button>
            </form>
        </div>
    </div>

    {{-- Tabel Mahasiswa Tersedia --}}
    <form action="{{ route('perwalian.store') }}" method="POST">
        @csrf
        <div class="card border-0 shadow-sm rounded-0 mb-5">
            <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
                <span class="uppercase fw-bold small">Daftar Mahasiswa Tersedia (Tanpa Wali)</span>
                <button type="submit" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm">
                    <i class="bi bi-plus-lg me-1"></i> Klaim Mahasiswa Terpilih
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="bg-light text-dark small uppercase text-center fw-bold">
                            <tr>
                                <th style="width: 5%;">
                                    <input class="form-check-input rounded-0 border-dark" type="checkbox" id="checkAll">
                                </th>
                                <th style="width: 15%;">NIM</th>
                                <th class="text-start">NAMA LENGKAP MAHASISWA</th>
                                <th class="text-start" style="width: 30%;">PROGRAM STUDI</th>
                                <th style="width: 15%;">ANGKATAN</th>
                            </tr>
                        </thead>
                        <tbody class="small text-dark">
                            @forelse ($mahasiswa_tersedia as $mahasiswa)
                                <tr>
                                    <td class="text-center">
                                        <input class="form-check-input rounded-0 border-dark student-checkbox" type="checkbox" name="mahasiswa_ids[]" value="{{ $mahasiswa->id }}">
                                    </td>
                                    <td class="text-center font-monospace fw-bold text-primary">{{ $mahasiswa->nim }}</td>
                                    <td class="text-start uppercase fw-bold text-dark">{{ $mahasiswa->nama_lengkap }}</td>
                                    <td class="text-start uppercase text-muted">{{ optional($mahasiswa->programStudi)->nama_prodi ?? '-' }}</td>
                                    <td class="text-center font-monospace">{{ $mahasiswa->tahun_masuk }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 uppercase fw-bold text-muted">
                                        @if(request('search') || request('program_studi_id') || request('angkatan'))
                                            <i class="bi bi-search fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                            Tidak ditemukan data mahasiswa yang cocok dengan parameter filter.
                                        @else
                                            <i class="bi bi-check2-all fs-2 d-block mb-2 text-success"></i>
                                            Seluruh mahasiswa aktif telah memiliki alokasi dosen wali di sistem.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if(method_exists($mahasiswa_tersedia, 'hasPages') && $mahasiswa_tersedia->hasPages())
                <div class="card-footer bg-white border-top py-3 rounded-0">
                    <div class="d-flex justify-content-center">
                        {{ $mahasiswa_tersedia->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </div>
    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const checkAll = document.getElementById('checkAll');
        if(checkAll){
            checkAll.addEventListener('change', function() {
                let checkboxes = document.querySelectorAll('.student-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
        }

        var scrollpos = sessionStorage.getItem('scrollPosition');
        if (scrollpos) {
            window.scrollTo(0, scrollpos);
            sessionStorage.removeItem('scrollPosition');
        }
    });

    function submitFilter() {
        sessionStorage.setItem('scrollPosition', window.scrollY);
        document.getElementById('filterForm').submit();
    }
</script>
@endsection
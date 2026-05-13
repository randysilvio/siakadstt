@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Input Nilai Mata Kuliah</h3>
            <span class="text-muted small uppercase">Pilih Mata Kuliah untuk Pengisian Nilai Akhir Semester</span>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    {{-- NOTIFIKASI SUKSES --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3" role="alert">
            <div class="d-flex align-items-center small fw-bold uppercase">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            </div>
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- KOTAK INFORMASI --}}
    <div class="alert alert-light border rounded-0 p-4 mb-4 shadow-sm">
        <h6 class="uppercase fw-bold small text-dark mb-2">Petunjuk Pengisian</h6>
        <p class="small mb-0 text-muted">
            Silakan klik tombol <strong class="text-dark">Input Nilai</strong> pada kolom aksi di bawah ini untuk mengelola nilai mahasiswa yang terdaftar pada kelas yang bersangkutan.
        </p>
    </div>

    {{-- TABEL MATA KULIAH ENTERPRISE --}}
    <div class="card border-0 shadow-sm rounded-0 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Mata Kuliah Tersedia
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 8%;">NO</th>
                            <th style="width: 15%;">KODE MK</th>
                            <th class="text-start">NAMA MATA KULIAH</th>
                            <th style="width: 12%;">SEMESTER</th>
                            <th class="text-start" style="width: 25%;">DOSEN PENGAMPU</th>
                            <th style="width: 15%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse ($mata_kuliahs as $index => $mk)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted">{{ $index + 1 }}</td>
                                <td class="text-center font-monospace fw-bold text-primary">{{ $mk->kode_mk }}</td>
                                <td class="text-start uppercase fw-bold text-dark">{{ $mk->nama_mk }}</td>
                                <td class="text-center font-monospace">SEM {{ $mk->semester }}</td>
                                <td class="text-start uppercase">
                                    <span class="fw-bold text-dark">{{ $mk->dosen->nama_lengkap ?? 'BELUM DITENTUKAN' }}</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('nilai.show', $mk->id) }}" class="btn btn-sm btn-dark rounded-0 px-3 py-1 uppercase fw-bold small">
                                        Input Nilai <i class="bi bi-arrow-right-short ms-1"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 uppercase fw-bold text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Belum ada data mata kuliah yang dapat dikelola.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
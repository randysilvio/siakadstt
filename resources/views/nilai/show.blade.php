@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <span class="text-muted font-monospace small uppercase">KODE: {{ $mataKuliah->kode_mk }} | SEMESTER {{ $mataKuliah->semester }} ({{ $mataKuliah->sks }} SKS)</span>
            <h3 class="fw-bold text-dark mb-0 uppercase">Input Nilai: {{ $mataKuliah->nama_mk }}</h3>
        </div>
        <div>
            @if(Auth::user()->hasRole('admin'))
                <a href="{{ route('admin.nilai.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                    Kembali ke Daftar MK
                </a>
            @else
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                    Kembali ke Dashboard
                </a>
            @endif
        </div>
    </div>

    {{-- NOTIFIKASI SUKSES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3" role="alert">
            <div class="d-flex align-items-center small uppercase fw-bold">
                <i class="bi bi-check-circle-fill fs-5 me-2"></i> {{ session('success') }}
            </div>
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- NOTIFIKASI ERROR SEMESTER --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3" role="alert">
            <div class="d-flex align-items-center small uppercase fw-bold">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i> {{ session('error') }}
            </div>
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('nilai.store') }}" method="POST">
        @csrf
        <input type="hidden" name="mata_kuliah_id" value="{{ $mataKuliah->id }}">
        
        {{-- TABEL INPUT DATA (STANDAR ENTERPRISE) --}}
        <div class="card border-0 shadow-sm rounded-0 mb-4 border-top border-dark border-4">
            <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
                <span class="uppercase fw-bold small">Daftar Peserta Kelas & Formulir Nilai Akhir</span>
                <span class="badge bg-light text-dark rounded-0 font-monospace uppercase fw-bold px-2 py-1" style="font-size: 11px;">
                    TOTAL PESERTA: {{ $mataKuliah->mahasiswas->count() }} MAHASISWA
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="bg-light text-dark small uppercase text-center fw-bold">
                            <tr>
                                <th style="width: 8%;">NO</th>
                                <th style="width: 20%;">NIM</th>
                                <th class="text-start">NAMA MAHASISWA</th>
                                <th style="width: 32%;">NILAI HURUF (A, B, C, D, E)</th>
                            </tr>
                        </thead>
                        <tbody class="small text-dark">
                            @forelse ($mataKuliah->mahasiswas as $index => $mahasiswa)
                                @php
                                    $nilaiTersimpan = $mahasiswa->pivot->nilai ?? '';
                                @endphp
                                <tr>
                                    <td class="text-center font-monospace fw-bold text-muted">{{ $index + 1 }}</td>
                                    <td class="text-center font-monospace fw-bold text-dark">{{ $mahasiswa->nim }}</td>
                                    <td class="text-start ps-3 uppercase fw-bold text-dark">{{ $mahasiswa->nama_lengkap }}</td>
                                    <td class="px-3">
                                        <div class="input-group">
                                            <select name="nilai[{{ $mahasiswa->id }}]" class="form-select rounded-0 font-monospace uppercase text-center fw-bold border-dark border-opacity-25">
                                                <option value="" {{ $nilaiTersimpan === '' ? 'selected' : '' }}>-- BELUM DINILAI --</option>
                                                @foreach(['A' => 'A (Sangat Baik - 4.00)', 'B' => 'B (Baik - 3.00)', 'C' => 'C (Cukup - 2.00)', 'D' => 'D (Kurang - 1.00)', 'E' => 'E (Gagal - 0.00)'] as $huruf => $label)
                                                    <option value="{{ $huruf }}" {{ $nilaiTersimpan === $huruf ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            
                                            @if($nilaiTersimpan)
                                                <span class="input-group-text rounded-0 bg-success text-white small uppercase fw-bold font-monospace px-3" style="font-size: 11px;">
                                                    Tersimpan: <strong class="ms-1 fs-6">{{ $nilaiTersimpan }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 uppercase fw-bold text-muted">
                                        <i class="bi bi-people fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                        Belum ada mahasiswa dengan KRS Disetujui pada mata kuliah ini di semester aktif.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        {{-- BARIS TOMBOL AKSI --}}
        <div class="d-flex justify-content-end mb-5">
            @if(Auth::user()->hasRole('admin'))
                <a href="{{ route('admin.nilai.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
            @else
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
            @endif
            
            @if($mataKuliah->mahasiswas->isNotEmpty())
                <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                    <i class="bi bi-save me-1"></i> Simpan Nilai
                </button>
            @endif
        </div>
    </form>
</div>
@endsection
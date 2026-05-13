@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <span class="text-muted font-monospace small uppercase">KODE: {{ $mataKuliah->kode_mk }} | SEMESTER {{ $mataKuliah->semester }}</span>
            <h3 class="fw-bold text-dark mb-0 uppercase">Input Nilai: {{ $mataKuliah->nama_mk }}</h3>
        </div>
        <div>
            @if(Auth::user()->hasRole('admin'))
                <a href="{{ route('nilai.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                    Kembali
                </a>
            @else
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                    Kembali ke Dashboard
                </a>
            @endif
        </div>
    </div>

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
            <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
                Daftar Peserta Kelas & Formulir Nilai Akhir
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="bg-light text-dark small uppercase text-center fw-bold">
                            <tr>
                                <th style="width: 8%;">NO</th>
                                <th style="width: 20%;">NIM</th>
                                <th class="text-start">NAMA MAHASISWA</th>
                                <th style="width: 30%;">INPUT NILAI (A, B, C, D, E)</th>
                            </tr>
                        </thead>
                        <tbody class="small text-dark">
                            @forelse ($mataKuliah->mahasiswas as $index => $mahasiswa)
                                <tr>
                                    <td class="text-center font-monospace fw-bold text-muted">{{ $index + 1 }}</td>
                                    <td class="text-center font-monospace fw-bold text-dark">{{ $mahasiswa->nim }}</td>
                                    <td class="text-start uppercase fw-bold text-dark">{{ $mahasiswa->nama_lengkap }}</td>
                                    <td class="px-3">
                                        {{-- INPUT GROUP SIKU PRESISI DENGAN KOTAK DISPLAY --}}
                                        <div class="input-group">
                                            <input type="text" name="nilai[{{ $mahasiswa->id }}]" 
                                                   class="form-control rounded-0 font-monospace uppercase text-center fw-bold" 
                                                   value="{{ $mahasiswa->pivot->nilai ?? '' }}" 
                                                   maxlength="2" 
                                                   placeholder="-">
                                            
                                            @if($mahasiswa->pivot->nilai)
                                                <span class="input-group-text rounded-0 bg-success text-white small uppercase fw-bold font-monospace px-3" style="font-size: 11px;">
                                                    Tersimpan: <strong class="ms-1 fs-6">{{ $mahasiswa->pivot->nilai }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 uppercase fw-bold text-muted">
                                        <i class="bi bi-people fs-2 d-block mb-2"></i>
                                        Belum ada mahasiswa yang mengambil mata kuliah ini pada semester aktif.
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
                <a href="{{ route('nilai.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
            @else
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
            @endif
            
            <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                <i class="bi bi-save me-1"></i> Simpan Nilai
            </button>
        </div>
    </form>
</div>
@endsection
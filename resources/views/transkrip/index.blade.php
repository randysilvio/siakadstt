@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3 border-bottom pb-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Transkrip Nilai Akademik</h3>
            <span class="text-muted small uppercase">Rekapitulasi Histori Capaian Studi & Evaluasi Kelulusan Sementara</span>
        </div>
        <div>
            <a href="{{ route('transkrip.cetak') }}" target="_blank" class="btn btn-sm btn-dark rounded-0 px-4 uppercase fw-bold small shadow-sm">
                <i class="bi bi-printer-fill me-1"></i> Cetak Transkrip Resmi (PDF)
            </a>
        </div>
    </div>

    {{-- KARTU DATA MAHASISWA (SIKU PRESISI 0PX) --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-white">
        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
            Informasi Portofolio Mahasiswa
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <span class="small text-muted font-monospace uppercase d-block" style="font-size: 10px;">NAMA LENGKAP</span>
                    <strong class="text-dark uppercase fs-6">{{ $mahasiswa->nama_lengkap }}</strong>
                    
                    <span class="small text-muted font-monospace uppercase d-block mt-3" style="font-size: 10px;">PROGRAM STUDI</span>
                    <strong class="text-dark uppercase small">{{ optional($mahasiswa->programStudi)->nama_prodi ?? '-' }}</strong>
                </div>
                <div class="col-md-6 border-start ps-md-4">
                    <span class="small text-muted font-monospace uppercase d-block" style="font-size: 10px;">NOMOR INDUK MAHASISWA</span>
                    <strong class="text-primary font-monospace fs-6">{{ $mahasiswa->nim }}</strong>
                    
                    <span class="small text-muted font-monospace uppercase d-block mt-3" style="font-size: 10px;">STATUS REGISTRASI AKTIF</span>
                    <div class="mt-1">
                        <span class="badge bg-dark text-white rounded-0 font-monospace uppercase fw-bold px-3 py-1" style="font-size: 10px;">
                            TERDAFTAR RESMI
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- KARTU REKAPITULASI SKS & IPK --}}
    <div class="row g-3 mb-5">
        <div class="col-md-6">
            <div class="bg-light border border-dark border-opacity-25 rounded-0 p-3 d-flex justify-content-between align-items-center">
                <span class="small text-muted font-monospace uppercase fw-bold">TOTAL SKS DITEMPUH:</span>
                <span class="fs-4 fw-bold font-monospace text-primary">{{ $total_sks }}</span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="bg-light border border-dark border-opacity-25 rounded-0 p-3 d-flex justify-content-between align-items-center">
                <span class="small text-muted font-monospace uppercase fw-bold">INDEKS PRESTASI KUMULATIF (IPK):</span>
                <span class="fs-4 fw-bold font-monospace text-dark">{{ number_format((float)$ipk, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- TABEL TRANSKRIP PER SEMESTER --}}
    @forelse ($krs_per_semester as $semester => $matkuls)
        <div class="card border-0 shadow-sm rounded-0 mb-4">
            <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
                CAPAIAN SEMESTER {{ $semester }}
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="bg-light text-dark small uppercase text-center fw-bold">
                            <tr>
                                <th style="width: 15%;">KODE MK</th>
                                <th class="text-start" style="width: 55%;">NAMA MATA KULIAH</th>
                                <th style="width: 15%;">BOBOT SKS</th>
                                <th style="width: 15%;">NILAI HURUF</th>
                            </tr>
                        </thead>
                        <tbody class="small text-dark">
                            @foreach ($matkuls as $mk)
                                <tr>
                                    <td class="text-center font-monospace fw-bold text-primary">{{ $mk->kode_mk }}</td>
                                    <td class="text-start uppercase fw-bold text-dark">{{ $mk->nama_mk }}</td>
                                    <td class="text-center font-monospace">{{ $mk->sks }}</td>
                                    <td class="text-center font-monospace fw-bold fs-6">
                                        {{ optional($mk->pivot)->nilai ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-5 border border-dark border-opacity-25 rounded-0 bg-white mb-5">
            <i class="bi bi-journal-x fs-2 d-block mb-2 text-secondary opacity-50"></i>
            <p class="text-muted small uppercase fw-bold font-monospace mb-0">Belum ada histori nilai mata kuliah yang disahkan untuk mahasiswa ini.</p>
        </div>
    @endforelse
</div>
@endsection
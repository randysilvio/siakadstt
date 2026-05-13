@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- Header & Aksi --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Jadwal Kuliah Anda</h3>
            <span class="text-muted small uppercase">Tahun Akademik {{ $periodeAktif->tahun }} - Semester {{ $periodeAktif->semester }}</span>
        </div>
        <a href="{{ route('krs.cetak.final') }}" class="btn btn-sm btn-dark rounded-0 px-4 uppercase fw-bold small shadow-sm" target="_blank">
            <i class="bi bi-printer me-2"></i>Cetak KRS Final
        </a>
    </div>

    {{-- Banner Persetujuan --}}
    <div class="alert alert-success rounded-0 border-0 bg-success text-white p-4 mb-4 shadow-sm">
        <h6 class="uppercase fw-bold mb-1"><i class="bi bi-check-circle-fill me-2"></i>KRS Telah Disetujui!</h6>
        <p class="small mb-0 opacity-85 font-sans-serif">KRS Anda untuk semester ini telah divalidasi oleh Ketua Program Studi dan tidak dapat diubah lagi. Berikut adalah jadwal kuliah final Anda.</p>
    </div>

    {{-- Tabel Jadwal Final --}}
    <div class="card border-0 shadow-sm rounded-0 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Jadwal Kuliah Semester Ini
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark text-center small uppercase">
                        <tr>
                            <th style="width: 12%;">HARI</th>
                            <th style="width: 15%;">JAM</th>
                            <th class="text-start">MATA KULIAH</th>
                            <th style="width: 8%;">SKS</th>
                            <th class="text-start" style="width: 25%;">DOSEN PENGAMPU</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse ($jadwalKuliah as $jadwal)
                            <tr>
                                <td class="text-center uppercase fw-bold text-dark">{{ $jadwal->hari }}</td>
                                <td class="text-center font-monospace text-muted">{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</td>
                                <td>
                                    <strong class="uppercase text-dark">{{ $jadwal->mataKuliah->nama_mk }}</strong>
                                    <small class="d-block text-muted font-monospace mt-1">{{ $jadwal->mataKuliah->kode_mk }}</small>
                                </td>
                                <td class="text-center font-monospace fw-bold">{{ $jadwal->mataKuliah->sks }}</td>
                                <td class="uppercase fw-bold text-muted" style="font-size: 11px;">{{ $jadwal->mataKuliah->dosen->nama_lengkap ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted small uppercase">
                                    <p class="fw-bold mb-1">Tidak ada jadwal yang ditemukan untuk KRS Anda.</p>
                                    <span class="opacity-75" style="font-size: 11px;">Silakan hubungi bagian administrasi akademik untuk informasi lebih lanjut.</span>
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
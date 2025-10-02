@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Jadwal Kuliah Anda</h1>
            <h5 class="text-muted">Tahun Akademik {{ $periodeAktif->tahun }} - Semester {{ $periodeAktif->semester }}</h5>
        </div>
        <a href="{{ route('krs.cetak.final') }}" class="btn btn-primary" target="_blank">
            Cetak KRS Final
        </a>
    </div>

    <div class="alert alert-success" role="alert">
        <h4 class="alert-heading">KRS Telah Disetujui!</h4>
        <p>KRS Anda untuk semester ini telah divalidasi oleh Ketua Program Studi dan tidak dapat diubah lagi. Berikut adalah jadwal kuliah final Anda.</p>
    </div>

    <div class="card">
        <div class="card-header fw-bold">
            Jadwal Kuliah Semester Ini
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Hari</th>
                            <th>Jam</th>
                            <th>Mata Kuliah</th>
                            <th>SKS</th>
                            <th>Dosen Pengampu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jadwalKuliah as $jadwal)
                            <tr>
                                <td>{{ $jadwal->hari }}</td>
                                <td>{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</td>
                                <td>
                                    <strong>{{ $jadwal->mataKuliah->nama_mk }}</strong>
                                    <small class="d-block text-muted">{{ $jadwal->mataKuliah->kode_mk }}</small>
                                </td>
                                <td class="text-center">{{ $jadwal->mataKuliah->sks }}</td>
                                <td>{{ $jadwal->mataKuliah->dosen->nama_lengkap ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <p class="mb-0">Tidak ada jadwal yang ditemukan untuk KRS Anda.</p>
                                    <small>Silakan hubungi bagian administrasi akademik untuk informasi lebih lanjut.</small>
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
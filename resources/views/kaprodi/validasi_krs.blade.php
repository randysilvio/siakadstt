@extends('layouts.app')

@section('content')
@php
    $istilahPembimbing = \App\Models\Pengaturan::get('istilah_pembimbing', 'Dosen Tutor');
@endphp
<div class="container-fluid px-3 px-md-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 mt-2 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Validasi Rencana Studi (Kaprodi)</h3>
            <span class="text-muted small uppercase">{{ $mahasiswa->nama_lengkap }} ({{ $mahasiswa->nim }})</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if(isset($prevMahasiswaId) && $prevMahasiswaId)
                <a href="{{ route('kaprodi.krs.show', $prevMahasiswaId) }}" class="btn btn-sm btn-outline-dark px-3 fw-bold uppercase small">
                    &larr; Sebelumnya
                </a>
            @endif
            @if(isset($nextMahasiswaId) && $nextMahasiswaId)
                <a href="{{ route('kaprodi.krs.show', $nextMahasiswaId) }}" class="btn btn-sm btn-dark px-3 fw-bold uppercase small shadow-sm">
                    Antrean Berikutnya &rarr;
                </a>
            @endif
            <a href="{{ route('kaprodi.dashboard') }}" class="btn btn-sm btn-outline-dark px-3 fw-bold uppercase small">
                Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-5">
            <div class="card shadow-sm border-0 border-top border-dark border-4 h-100 bg-white">
                <div class="card-header bg-white py-3 border-bottom uppercase fw-bold small text-dark">
                    Identitas & Portofolio Mahasiswa
                </div>
                <div class="card-body p-4">
                    <table class="table table-borderless small uppercase text-dark mb-0">
                        <tr>
                            <td style="width: 150px;" class="fw-bold text-muted">NAMA MAHASISWA</td>
                            <td>: <strong>{{ $mahasiswa->nama_lengkap }}</strong></td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">NIM / ANGKATAN</td>
                            <td class="font-monospace fw-bold text-dark">: {{ $mahasiswa->nim }} ({{ $mahasiswa->tahun_masuk }})</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">{{ strtoupper($istilahPembimbing) }}</td>
                            <td>: {{ optional($mahasiswa->dosenWali)->nama_lengkap ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">IPK / SKS LULUS</td>
                            <td class="font-monospace fw-bold">: {{ number_format($ipk ?? $mahasiswa->hitungIpk(), 2) }} ({{ $sksLulus ?? $mahasiswa->totalSksLulus() }} SKS)</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted align-middle">STATUS KRS</td>
                            <td class="align-middle">:
                                <span class="badge bg-dark text-white uppercase font-monospace fw-bold px-2 py-1">
                                    {{ strtoupper($mahasiswa->status_krs ?? 'DRAFT') }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm border-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-header bg-dark text-white py-3 uppercase fw-bold small">
                    Otorisasi & Catatan Ketua Program Studi
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <form action="{{ route('kaprodi.krs.update', $mahasiswa->id) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label small fw-bold uppercase text-dark mb-1">Catatan Validasi Kaprodi / {{ $istilahPembimbing }} (Opsional)</label>
                            <textarea name="catatan_kaprodi" rows="2" class="form-control small uppercase" placeholder="Tulis catatan atau alasan penolakan KRS jika perlu revisi...">{{ old('catatan_kaprodi', $mahasiswa->catatan_kaprodi) }}</textarea>
                        </div>

                        <div class="d-flex flex-wrap gap-2 justify-content-end pt-2 border-top">
                            @if($mahasiswa->status_krs != 'Menunggu Persetujuan')
                                <button type="submit" name="status_krs" value="Menunggu Persetujuan" class="btn btn-outline-dark btn-sm fw-bold uppercase px-3 py-2">
                                    Reset ke Menunggu
                                </button>
                            @endif

                            <button type="submit" name="status_krs" value="Ditolak" class="btn btn-outline-danger btn-sm fw-bold uppercase px-4 py-2" {{ $mahasiswa->status_krs == 'Ditolak' ? 'disabled' : '' }}>
                                Tolak / Minta Revisi
                            </button>

                            <button type="submit" name="status_krs" value="Disetujui" class="btn btn-dark btn-sm fw-bold uppercase px-4 py-2 shadow-sm" {{ $mahasiswa->status_krs == 'Disetujui' ? 'disabled' : '' }}>
                                Setujui KRS
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 border-top border-dark border-4 mb-5 bg-white">
        <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 uppercase fw-bold small">Daftar Mata Kuliah yang Diambil</h6>
            <span class="badge bg-light text-dark font-monospace">TOTAL: {{ $mahasiswa->mataKuliahs->sum('sks') }} SKS</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 align-middle small">
                    <thead class="bg-light uppercase fw-bold text-center text-dark">
                        <tr>
                            <th style="width: 6%;">NO</th>
                            <th style="width: 13%;">KODE MK</th>
                            <th class="text-start">MATA KULIAH</th>
                            <th style="width: 9%;">KELAS</th>
                            <th style="width: 7%;">SMT</th>
                            <th style="width: 7%;">SKS</th>
                            <th class="text-start" style="width: 32%;">JADWAL & DOSEN PENGAMPU</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalSks = 0; @endphp
                        @forelse ($mahasiswa->mataKuliahs as $index => $mk)
                            @php
                                $klsDiambil = strtoupper($mk->pivot->nama_kelas ?? 'A');
                                $jadwalKelas = $mk->jadwals->where('nama_kelas', $klsDiambil);
                                if ($jadwalKelas->isEmpty()) {
                                    $jadwalKelas = $mk->jadwals;
                                }
                            @endphp
                            <tr>
                                <td class="text-center font-monospace text-muted fw-bold">{{ $index + 1 }}</td>
                                <td class="text-center font-monospace fw-bold text-dark">{{ $mk->kode_mk }}</td>
                                <td class="uppercase fw-bold text-dark ps-3">{{ $mk->nama_mk }}</td>
                                <td class="text-center font-monospace fw-bold">{{ $klsDiambil }}</td>
                                <td class="text-center font-monospace">{{ $mk->semester }}</td>
                                <td class="text-center font-monospace fw-bold">{{ $mk->sks }}</td>
                                <td class="ps-3">
                                    @if(isset($mk->dosen) && $mk->dosen)
                                        <div class="text-dark fw-bold uppercase mb-0.5" style="font-size: 10px;">{{ $mk->dosen->nama_lengkap }}</div>
                                    @endif
                                    @if($jadwalKelas->isNotEmpty())
                                        @foreach($jadwalKelas as $jadwal)
                                            <div class="text-muted font-monospace" style="font-size: 10px;">
                                                {{ strtoupper($jadwal->hari) }}, {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}-{{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }} WIT ({{ $jadwal->ruang ?? '-' }})
                                            </div>
                                        @endforeach
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 10px;">-</span>
                                    @endif
                                </td>
                            </tr>
                            @php $totalSks += $mk->sks; @endphp
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted small uppercase fw-bold">
                                    Mahasiswa ini belum memilih mata kuliah pada semester aktif.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-light fw-bold uppercase small text-dark">
                        <tr>
                            <td colspan="5" class="text-end py-2 pe-3">TOTAL BEBAN SKS DIAMBIL</td>
                            <td class="text-center py-2 text-dark font-monospace fs-6">{{ $totalSks }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
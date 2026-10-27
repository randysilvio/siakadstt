@extends('layouts.app')

@section('content')
@php
    $istilahPerwalian  = \App\Models\Pengaturan::get('istilah_perwalian', 'Tutorship');
    $istilahPembimbing = \App\Models\Pengaturan::get('istilah_pembimbing', 'Dosen Tutor');
@endphp
<div class="container-fluid px-3 px-md-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 mt-2 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Detail Rencana Studi & Bimbingan {{ $istilahPerwalian }}</h3>
            <span class="text-muted small uppercase">{{ $mahasiswa->nama_lengkap }} ({{ $mahasiswa->nim }})</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if(isset($prevMahasiswaId) && $prevMahasiswaId)
                <a href="{{ route('perwalian.show', $prevMahasiswaId) }}" class="btn btn-sm btn-outline-dark px-3 uppercase fw-bold small">
                    &larr; Sebelumnya
                </a>
            @endif
            @if(isset($nextMahasiswaId) && $nextMahasiswaId)
                <a href="{{ route('perwalian.show', $nextMahasiswaId) }}" class="btn btn-sm btn-dark px-3 uppercase fw-bold small shadow-sm">
                    Mahasiswa Berikutnya &rarr;
                </a>
            @endif
            <a href="{{ route('perwalian.index') }}" class="btn btn-sm btn-outline-dark px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border p-3 mb-4 font-monospace small uppercase fw-bold shadow-sm" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border p-3 mb-4 font-monospace small uppercase fw-bold shadow-sm" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @php
        $hasKrsThisSemester = $krs->isNotEmpty();
        $displayStatus = $hasKrsThisSemester ? $mahasiswa->status_krs : 'Belum Mengajukan';
        $ipkVal = $ipk ?? $mahasiswa->hitungIpk();
        $sksLulusVal = $sksLulus ?? $mahasiswa->totalSksLulus();
    @endphp

    @if($ipkVal > 0 && $ipkVal < 2.00)
        <div class="alert alert-danger border-start border-danger border-4 shadow-sm mb-4 p-3 uppercase small fw-bold">
            Perhatian Akademik: Mahasiswa ini memiliki IPK di bawah 2.00 ({{ number_format($ipkVal, 2) }}). Mohon berikan pendampingan khusus dalam pengambilan beban SKS semester ini.
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-header bg-white py-3 border-bottom uppercase fw-bold small text-dark">
                    Informasi Akademik Mahasiswa Bimbingan
                </div>
                <div class="card-body p-4">
                    <table class="table table-borderless small uppercase text-dark mb-0">
                        <tr>
                            <td style="width: 180px;" class="fw-bold text-muted">NAMA LENGKAP</td>
                            <td>: <strong class="text-dark">{{ $mahasiswa->nama_lengkap }}</strong></td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">NIM / ANGKATAN</td>
                            <td class="font-monospace text-dark fw-bold">: {{ $mahasiswa->nim }} ({{ $mahasiswa->tahun_masuk }})</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">PROGRAM STUDI</td>
                            <td>: {{ optional($mahasiswa->programStudi)->nama_prodi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">CAPAIAN KUMULATIF</td>
                            <td class="font-monospace fw-bold">: IPK {{ number_format($ipkVal, 2) }} | {{ $sksLulusVal }} SKS LULUS</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted align-middle">STATUS KRS SAAT INI</td>
                            <td class="align-middle">: 
                                <span class="badge bg-dark text-white font-monospace uppercase fw-bold px-2 py-1 ms-1" style="font-size: 10px;">
                                    {{ strtoupper($displayStatus) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-5">
            <div class="card border-0 shadow-sm border-top border-dark border-4 h-100 bg-light">
                <div class="card-header bg-dark text-white py-3 border-bottom uppercase fw-bold small text-center">
                    Keputusan & Catatan {{ $istilahPembimbing }}
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-center">
                    @if($hasKrsThisSemester)
                        <form action="{{ route('perwalian.updateStatus', $mahasiswa->id) }}" method="POST">
                            @csrf @method('PATCH')
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold uppercase text-dark mb-1">Catatan Bimbingan / Alasan Revisi (Opsional)</label>
                                <textarea name="catatan_kaprodi" rows="2" class="form-control small uppercase" placeholder="Tulis arahan bimbingan atau alasan penolakan KRS...">{{ old('catatan_kaprodi', $mahasiswa->catatan_kaprodi) }}</textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="status_krs" value="Disetujui" class="btn btn-dark py-2 uppercase fw-bold small text-white shadow-sm" {{ $displayStatus == 'Disetujui' ? 'disabled' : '' }}>
                                    Setujui Pengajuan KRS
                                </button>
                                
                                <button type="submit" name="status_krs" value="Ditolak" class="btn btn-outline-danger py-2 uppercase fw-bold small" {{ $displayStatus == 'Ditolak' ? 'disabled' : '' }}>
                                    Tolak / Minta Revisi KRS
                                </button>

                                @if($displayStatus != 'Menunggu Persetujuan')
                                    <button type="submit" name="status_krs" value="Menunggu Persetujuan" class="btn btn-outline-dark py-1 uppercase fw-bold mt-1" style="font-size: 10px;">
                                        Reset Status ke Menunggu
                                    </button>
                                @endif
                            </div>
                        </form>
                    @else
                        <div class="text-center py-3">
                            <span class="small text-muted uppercase fw-bold d-block">Belum Ada Pengajuan KRS</span>
                            <p class="small text-muted mb-0 mt-1" style="font-size: 11px;">Mahasiswa belum mengisi rencana studi untuk periode ini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm border-top border-dark border-4 mb-5 bg-white">
        <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
            <span class="uppercase fw-bold small">Daftar Mata Kuliah Diambil ({{ $periodeAktif->tahun }} - {{ strtoupper($periodeAktif->semester) }})</span>
            <span class="badge bg-light text-dark font-monospace uppercase fw-bold px-2 py-1" style="font-size: 11px;">
                TOTAL BEBAN: {{ $totalSks }} SKS
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 small">
                    <thead class="bg-light text-dark uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 12%;">KODE MK</th>
                            <th class="text-start">NAMA MATA KULIAH</th>
                            <th style="width: 9%;">KELAS</th>
                            <th style="width: 7%;">SKS</th>
                            <th style="width: 7%;">SMT</th>
                            <th class="text-start" style="width: 26%;">JADWAL</th>
                            <th style="width: 12%;">KOREKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($krs as $mk)
                            @php
                                $klsDiambil = strtoupper($mk->pivot->nama_kelas ?? 'A');
                                $jadwalKelas = $mk->jadwals->where('nama_kelas', $klsDiambil);
                                if ($jadwalKelas->isEmpty()) {
                                    $jadwalKelas = $mk->jadwals;
                                }
                            @endphp
                            <tr>
                                <td class="text-center font-monospace fw-bold text-dark">{{ $mk->kode_mk }}</td>
                                <td class="text-start uppercase fw-bold text-dark ps-3">{{ $mk->nama_mk }}</td>
                                <td class="text-center font-monospace fw-bold">{{ $klsDiambil }}</td>
                                <td class="text-center font-monospace">{{ $mk->sks }}</td>
                                <td class="text-center font-monospace">{{ $mk->semester }}</td>
                                <td class="text-start ps-3 font-monospace text-muted">
                                    @forelse($jadwalKelas as $jadwal)
                                        <span class="d-block" style="font-size: 11px;">
                                            <strong class="text-dark">{{ strtoupper($jadwal->hari) }}</strong>, {{ substr($jadwal->jam_mulai, 0, 5) }}-{{ substr($jadwal->jam_selesai, 0, 5) }} WIT ({{ $jadwal->ruang ?? '-' }})
                                        </span>
                                    @empty
                                        <span class="text-muted small uppercase">-</span>
                                    @endforelse
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('perwalian.krs.destroy', ['mahasiswa' => $mahasiswa->id, 'mk' => $mk->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus mata kuliah ini dari KRS mahasiswa?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0.5 px-2 uppercase fw-bold" style="font-size: 10px;">
                                            Hapus MK
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 uppercase fw-bold text-muted">
                                    Mahasiswa belum memilih mata kuliah pada semester ini.
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
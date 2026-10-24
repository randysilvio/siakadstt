@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA & NAVIGASI CEPAT ANTREAN TUTORSHIP --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 mt-3 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Detail Rencana Studi & Bimbingan Tutorship</h3>
            <span class="text-muted small uppercase">Pengawasan Akademik Mahasiswa Bimbingan & Validasi KRS oleh Dosen Tutor</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if(isset($prevMahasiswaId) && $prevMahasiswaId)
                <a href="{{ route('perwalian.show', $prevMahasiswaId) }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                    <i class="bi bi-chevron-left me-1"></i> Sebelumnya
                </a>
            @endif
            @if(isset($nextMahasiswaId) && $nextMahasiswaId)
                <a href="{{ route('perwalian.show', $nextMahasiswaId) }}" class="btn btn-sm btn-primary rounded-0 px-3 uppercase fw-bold small shadow-sm">
                    Mahasiswa Berikutnya <i class="bi bi-chevron-right ms-1"></i>
                </a>
            @endif
            <a href="{{ route('perwalian.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                <i class="bi bi-arrow-left me-1"></i> Daftar Tutorship
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 p-3 mb-4 font-monospace small uppercase fw-bold shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @php
        $hasKrsThisSemester = $krs->isNotEmpty();
        $displayStatus = $hasKrsThisSemester ? $mahasiswa->status_krs : 'Belum Mengajukan';
        $ipkVal = $ipk ?? $mahasiswa->hitungIpk();
        $sksLulusVal = $sksLulus ?? $mahasiswa->totalSksLulus();
    @endphp

    @if($ipkVal > 0 && $ipkVal < 2.00)
        <div class="alert alert-danger border-start border-danger border-5 rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold">
            <i class="bi bi-exclamation-octagon-fill me-2"></i> PERINGATAN DINI TUTORSHIP (EARLY WARNING SYSTEM): Mahasiswa ini memiliki IPK di bawah standar minimum ({{ number_format($ipkVal, 2) }}). Mohon berikan pendampingan khusus dalam pengambilan beban SKS semester ini.
        </div>
    @endif

    {{-- DATA BIODATA & PANEL VALIDASI KAKU --}}
    <div class="row g-4 mb-4">
        {{-- Kolom Kiri: Informasi Mahasiswa --}}
        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-white">
                <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                    Informasi Portofolio Akademik Mahasiswa Tutorship
                </div>
                <div class="card-body p-4">
                    <table class="table table-borderless small uppercase text-dark mb-0">
                        <tr>
                            <td style="width: 190px;" class="fw-bold">NAMA LENGKAP</td>
                            <td>: <strong class="text-dark">{{ $mahasiswa->nama_lengkap }}</strong></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">NIM / ANGKATAN</td>
                            <td class="font-monospace text-primary fw-bold">: {{ $mahasiswa->nim }} ({{ $mahasiswa->tahun_masuk }})</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">PROGRAM STUDI</td>
                            <td>: {{ optional($mahasiswa->programStudi)->nama_prodi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">CAPAIAN KUMULATIF</td>
                            <td class="font-monospace fw-bold">: IPK {{ number_format($ipkVal, 2) }} | {{ $sksLulusVal }} SKS LULUS</td>
                        </tr>
                        <tr>
                            <td class="fw-bold align-middle">STATUS KRS SAAT INI</td>
                            <td class="align-middle">: 
                                @if($displayStatus == 'Disetujui')
                                    <span class="badge bg-success text-white rounded-0 font-monospace uppercase fw-bold px-2 py-1 ms-1" style="font-size: 10px;">DISETUJUI</span>
                                @elseif($displayStatus == 'Ditolak')
                                    <span class="badge bg-danger text-white rounded-0 font-monospace uppercase fw-bold px-2 py-1 ms-1" style="font-size: 10px;">DITOLAK / REVISI</span>
                                @elseif($displayStatus == 'Menunggu Persetujuan')
                                    <span class="badge bg-warning text-dark rounded-0 font-monospace uppercase fw-bold px-2 py-1 ms-1" style="font-size: 10px;">MENUNGGU PERSETUJUAN</span>
                                @else
                                    <span class="badge bg-secondary text-white rounded-0 font-monospace uppercase fw-bold px-2 py-1 ms-1" style="font-size: 10px;">DRAFT / BELUM MENGAJUKAN</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        {{-- Kolom Kanan: Aksi Eksekusi Dosen Tutor (Stay-on-Page) --}}
        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-header bg-dark text-white py-3 border-bottom rounded-0 uppercase fw-bold small text-center">
                    Keputusan & Catatan Dosen Tutor
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-center">
                    @if($hasKrsThisSemester)
                        <form action="{{ route('perwalian.updateStatus', $mahasiswa->id) }}" method="POST">
                            @csrf @method('PATCH')
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold uppercase text-dark mb-1">Catatan Bimbingan Tutorship / Alasan Revisi (Opsional)</label>
                                <textarea name="catatan_kaprodi" rows="2" class="form-control rounded-0 small uppercase" placeholder="TULIS ARAHAN TUTORSHIP ATAU ALASAN PENOLAKAN KRS...">{{ old('catatan_kaprodi', $mahasiswa->catatan_kaprodi) }}</textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="status_krs" value="Disetujui" class="btn btn-success rounded-0 py-2 uppercase fw-bold small text-white shadow-sm" {{ $displayStatus == 'Disetujui' ? 'disabled' : '' }}>
                                    <i class="bi bi-check-lg me-1 align-middle"></i> Setujui Pengajuan KRS
                                </button>
                                
                                <button type="submit" name="status_krs" value="Ditolak" class="btn btn-danger rounded-0 py-2 uppercase fw-bold small text-white shadow-sm" {{ $displayStatus == 'Ditolak' ? 'disabled' : '' }}>
                                    <i class="bi bi-x-lg me-1 align-middle"></i> Tolak / Minta Revisi KRS
                                </button>

                                @if($displayStatus != 'Menunggu Persetujuan')
                                    <button type="submit" name="status_krs" value="Menunggu Persetujuan" class="btn btn-outline-dark rounded-0 py-1 uppercase fw-bold mt-1" style="font-size: 10px;">
                                        Reset Status ke Menunggu
                                    </button>
                                @endif
                            </div>
                        </form>
                    @else
                        <div class="text-center">
                            <i class="bi bi-shield-lock text-muted fs-1 mb-2 d-block"></i>
                            <span class="small text-muted uppercase fw-bold">Aksi Dikunci</span>
                            <p class="small text-muted mb-0 mt-1" style="font-size: 11px;">Mahasiswa belum mengisi rencana studi untuk periode ini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL MATA KULIAH DIAMBIL --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
            <span class="uppercase fw-bold small">Daftar Mata Kuliah Diambil ({{ $periodeAktif->tahun }} - {{ strtoupper($periodeAktif->semester) }})</span>
            <span class="badge bg-light text-dark rounded-0 font-monospace uppercase fw-bold px-2 py-1" style="font-size: 11px;">
                TOTAL BEBAN: {{ $totalSks }} SKS
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 12%;">KODE MK</th>
                            <th class="text-start" style="width: 33%;">NAMA MATA KULIAH</th>
                            <th style="width: 8%;">SKS</th>
                            <th style="width: 8%;">SMT</th>
                            <th class="text-start" style="width: 27%;">ALOKASI JADWAL</th>
                            <th style="width: 12%;">AKSI KOREKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse($krs as $mk)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-primary">{{ $mk->kode_mk }}</td>
                                <td class="text-start uppercase fw-bold text-dark ps-3">{{ $mk->nama_mk }}</td>
                                <td class="text-center font-monospace">{{ $mk->sks }}</td>
                                <td class="text-center font-monospace">{{ $mk->semester }}</td>
                                <td class="text-start ps-3 font-monospace text-muted">
                                    @forelse($mk->jadwals as $jadwal)
                                        <span class="d-block">
                                            <strong class="text-dark">{{ strtoupper($jadwal->hari) }}</strong>, {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIT ({{ $jadwal->ruang ?? 'TBA' }})
                                        </span>
                                    @empty
                                        <span class="text-danger small uppercase">BELUM ADA JADWAL</span>
                                    @endforelse
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('perwalian.krs.destroy', ['mahasiswa' => $mahasiswa->id, 'mk' => $mk->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Koreksi Dosen Tutor: Hapus mata kuliah ini dari borang pengisian KRS mahasiswa bersangkutan?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2 uppercase fw-bold" style="font-size: 10px;" title="Batalkan Pengambilan">
                                            <i class="bi bi-trash me-1"></i> Batalkan MK
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 uppercase fw-bold text-muted">
                                    <i class="bi bi-journal-x fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                    Mahasiswa bersangkutan belum memasukkan mata kuliah ke dalam borang KRS.
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
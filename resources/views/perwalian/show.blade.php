@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Detail Rencana Studi Mahasiswa</h3>
            <span class="text-muted small uppercase">Pengawasan Bimbingan & Eksekusi Validasi KRS Akademik</span>
        </div>
        <div>
            <a href="{{ route('perwalian.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali ke Daftar Wali
            </a>
        </div>
    </div>

    {{-- DATA BIODATA & PANEL VALIDASI KAKU --}}
    <div class="row g-4 mb-4">
        {{-- Kolom Kiri: Informasi Mahasiswa --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-white">
                <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                    Informasi Portofolio Mahasiswa
                </div>
                <div class="card-body p-4">
                    <table class="table table-borderless small uppercase text-dark mb-0">
                        <tr>
                            <td style="width: 180px;" class="fw-bold">NAMA LENGKAP</td>
                            <td>: <strong class="text-dark">{{ $mahasiswa->nama_lengkap }}</strong></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">NIM</td>
                            <td class="font-monospace text-primary fw-bold">: {{ $mahasiswa->nim }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">PROGRAM STUDI</td>
                            <td>: {{ optional($mahasiswa->programStudi)->nama_prodi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold align-middle">STATUS KRS SAAT INI</td>
                            <td class="align-middle">: 
                                @if($mahasiswa->status_krs == 'Disetujui')
                                    <span class="badge bg-success text-white rounded-0 font-monospace uppercase fw-bold px-2 py-1 ms-1" style="font-size: 10px;">DISETUJUI</span>
                                @elseif($mahasiswa->status_krs == 'Ditolak')
                                    <span class="badge bg-danger text-white rounded-0 font-monospace uppercase fw-bold px-2 py-1 ms-1" style="font-size: 10px;">DITOLAK / REVISI</span>
                                @else
                                    <span class="badge bg-warning text-dark rounded-0 font-monospace uppercase fw-bold px-2 py-1 ms-1" style="font-size: 10px;">MENUNGGU PERSETUJUAN</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        {{-- Kolom Kanan: Aksi Eksekusi Dosen --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-header bg-light py-3 border-bottom rounded-0 uppercase fw-bold small text-dark text-center">
                    Eksekusi Status Validasi
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-center">
                    <form action="{{ route('perwalian.updateStatus', $mahasiswa->id) }}" method="POST">
                        @csrf @method('PATCH')
                        
                        <div class="d-grid gap-2">
                            <button type="submit" name="status_krs" value="Disetujui" class="btn btn-success rounded-0 py-2 uppercase fw-bold small text-white shadow-sm" {{ $mahasiswa->status_krs == 'Disetujui' ? 'disabled' : '' }}>
                                <i class="bi bi-check-lg me-1 align-middle"></i> Setujui Pengajuan KRS
                            </button>
                            
                            <button type="submit" name="status_krs" value="Ditolak" class="btn btn-danger rounded-0 py-2 uppercase fw-bold small text-white shadow-sm" {{ $mahasiswa->status_krs == 'Ditolak' ? 'disabled' : '' }}>
                                <i class="bi bi-x-lg me-1 align-middle"></i> Tolak Pengajuan KRS
                            </button>

                            @if($mahasiswa->status_krs != 'Menunggu Persetujuan')
                                <button type="submit" name="status_krs" value="Menunggu Persetujuan" class="btn btn-outline-dark rounded-0 py-1 uppercase fw-bold mt-2" style="font-size: 10px;">
                                    Reset Status ke Menunggu
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL MATA KULIAH DIAMBIL (MONOSPACE KETAT) --}}
    <div class="card border-0 shadow-sm rounded-0 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
            <span class="uppercase fw-bold small">Daftar Mata Kuliah Diambil</span>
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
                                <td class="text-start uppercase fw-bold text-dark">{{ $mk->nama_mk }}</td>
                                <td class="text-center font-monospace">{{ $mk->sks }}</td>
                                <td class="text-center font-monospace">{{ $mk->semester }}</td>
                                <td class="text-start ps-3 font-monospace text-muted">
                                    @forelse($mk->jadwals as $jadwal)
                                        <span class="d-block">
                                            <strong class="text-dark">{{ strtoupper($jadwal->hari) }}</strong>, {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIT
                                        </span>
                                    @empty
                                        <span class="text-danger small uppercase">BELUM ADA JADWAL</span>
                                    @endforelse
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('perwalian.krs.destroy', ['mahasiswa' => $mahasiswa->id, 'mk' => $mk->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Koreksi paksa: Hapus mata kuliah ini dari borang pengisian KRS mahasiswa bersangkutan?');">
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
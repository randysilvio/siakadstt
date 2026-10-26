@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4">
    {{-- HEADER UTAMA --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 mt-2 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Pusat Sinkronisasi PDDIKTI & Cadangan Basis Data</h3>
            <span class="text-muted small uppercase">Ekspor Data Standar Neo Feeder Kemendikbudristek (Kode PT: {{ $kodePt }}) & Backup SQL</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.feeder.backup-sql') }}" class="btn btn-sm btn-dark px-4 py-2 uppercase fw-bold small shadow-sm">
                <i class="bi bi-database-down me-1"></i> Unduh Backup Database (.SQL)
            </a>
        </div>
    </div>

    {{-- 1. INDIKATOR KESIAPAN DATA PELAPORAN PDDIKTI --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card wm-card-exclusive hover-lift border-0 shadow-sm bg-dark text-white h-100">
                <div class="card-body p-3 p-md-4">
                    <h6 class="uppercase fw-bold small text-white-50 mb-1" style="font-size: 11px;">Kode PT PDDIKTI</h6>
                    <h3 class="fw-bold text-white mb-1 font-monospace">{{ $kodePt }}</h3>
                    <span class="small text-white-50 uppercase font-monospace" style="font-size: 10px;">{{ \App\Models\Pengaturan::get('singkatan_kampus', 'STT GPI PAPUA') }}</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card hover-lift border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3 p-md-4">
                    <h6 class="uppercase fw-bold small text-muted mb-1" style="font-size: 11px;">Validasi NIK Mahasiswa</h6>
                    <h3 class="fw-bold font-monospace mb-1 {{ $mhsTanpaNik == 0 ? 'text-dark' : 'text-danger' }}">
                        {{ $totalMhsAktif - $mhsTanpaNik }} / {{ $totalMhsAktif }}
                    </h3>
                    <span class="small font-monospace uppercase {{ $mhsTanpaNik == 0 ? 'text-muted' : 'text-danger fw-bold' }}" style="font-size: 10px;">
                        {{ $mhsTanpaNik == 0 ? '100% NIK Lengkap' : $mhsTanpaNik . ' Mhs Belum Isi NIK 16 Digit' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card hover-lift border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3 p-md-4">
                    <h6 class="uppercase fw-bold small text-muted mb-1" style="font-size: 11px;">Data Ibu Kandung</h6>
                    <h3 class="fw-bold font-monospace mb-1 {{ $mhsTanpaIbu == 0 ? 'text-dark' : 'text-danger' }}">
                        {{ $totalMhsAktif - $mhsTanpaIbu }} / {{ $totalMhsAktif }}
                    </h3>
                    <span class="small font-monospace uppercase {{ $mhsTanpaIbu == 0 ? 'text-muted' : 'text-danger fw-bold' }}" style="font-size: 10px;">
                        {{ $mhsTanpaIbu == 0 ? '100% Terisi' : $mhsTanpaIbu . ' Mhs Belum Lengkap' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card hover-lift border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3 p-md-4">
                    <h6 class="uppercase fw-bold small text-muted mb-1" style="font-size: 11px;">Validasi NIDN Dosen</h6>
                    <h3 class="fw-bold font-monospace mb-1 {{ $dosenTanpaNidn == 0 ? 'text-dark' : 'text-danger' }}">
                        {{ $totalDosen - $dosenTanpaNidn }} / {{ $totalDosen }}
                    </h3>
                    <span class="small font-monospace uppercase {{ $dosenTanpaNidn == 0 ? 'text-muted' : 'text-danger fw-bold' }}" style="font-size: 10px;">
                        {{ $dosenTanpaNidn == 0 ? '100% NIDN Lengkap' : $dosenTanpaNidn . ' Dosen Belum Isi NIDN' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. PARAMETER FILTER SEMESTER & PROGRAM STUDI UNTUK EKSPOR FEEDER --}}
    <div class="card border-0 shadow-sm border-top border-dark border-4 mb-4 bg-white">
        <div class="card-header bg-white py-3 border-bottom uppercase fw-bold small text-dark">
            Filter Periode Pelaporan & Program Studi
        </div>
        <div class="card-body p-4 bg-light">
            <form id="formFilterFeeder" method="GET" action="{{ route('admin.feeder.index') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-bold uppercase text-dark">Periode Tahun Akademik Pelaporan</label>
                    <select name="tahun_akademik_id" id="selectTaFeeder" class="form-select uppercase small fw-bold">
                        @foreach($tahunAkademiks as $ta)
                            <option value="{{ $ta->id }}" {{ optional($selectedTa)->id == $ta->id ? 'selected' : '' }}>
                                {{ $ta->tahun }} - Semester {{ strtoupper($ta->semester) }} {{ $ta->is_active ? '(AKTIF)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-bold uppercase text-dark">Filter Program Studi (Opsional)</label>
                    <select name="program_studi_id" id="selectProdiFeeder" class="form-select uppercase small fw-bold">
                        <option value="">-- SEMUA PROGRAM STUDI --</option>
                        @foreach($programStudis as $prodi)
                            <option value="{{ $prodi->id }}" {{ request('program_studi_id') == $prodi->id ? 'selected' : '' }}>
                                {{ $prodi->kode_prodi }} - {{ $prodi->nama_prodi }} ({{ $prodi->jenjang ?? 'S1' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-dark w-100 py-2 uppercase fw-bold small">Terapkan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 3. DAFTAR 5 MODUL EKSPOR CSV STANDAR NEO FEEDER PDDIKTI --}}
    <div class="card border-0 shadow-sm border-top border-dark border-4 mb-4 bg-white">
        <div class="card-header bg-dark text-white py-3 uppercase fw-bold small">
            Daftar Paket Berkas Importer Neo Feeder PDDIKTI (Format CSV Delimiter Titik Koma ';')
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 small">
                    <thead class="bg-light text-dark uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 6%;">NO</th>
                            <th class="text-start" style="width: 28%;">MODUL PELAPORAN NEO FEEDER</th>
                            <th class="text-start" style="width: 46%;">STRUKTUR KOLOM YANG DIEKSPOR</th>
                            <th style="width: 20%;">UNDUH BERKAS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center font-monospace fw-bold">1</td>
                            <td class="ps-3">
                                <strong class="uppercase text-dark d-block">Biodata Mahasiswa</strong>
                                <span class="text-muted font-monospace" style="font-size: 10px;">Tabel: mahasiswa_feeder.csv</span>
                            </td>
                            <td class="ps-3 text-muted font-monospace" style="font-size: 11px;">
                                NIM, Nama, Tempat/Tgl Lahir, JK, NIK, NISN, Agama, Nama Ibu Kandung, Alamat, RT/RW, Kelurahan, Kecamatan, Jenis Tinggal, Alat Transportasi, Kode Prodi, Angkatan
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.feeder.export', ['kategori' => 'mahasiswa', 'tahun_akademik_id' => optional($selectedTa)->id, 'program_studi_id' => request('program_studi_id')]) }}" class="btn btn-sm btn-dark px-3 py-1.5 uppercase fw-bold small">
                                    <i class="bi bi-download me-1"></i> Unduh CSV
                                </a>
                            </td>
                        </tr>

                        <tr>
                            <td class="text-center font-monospace fw-bold">2</td>
                            <td class="ps-3">
                                <strong class="uppercase text-dark d-block">Kelas Perkuliahan & Rombel</strong>
                                <span class="text-muted font-monospace" style="font-size: 10px;">Tabel: kelas_kuliah_feeder.csv</span>
                            </td>
                            <td class="ps-3 text-muted font-monospace" style="font-size: 11px;">
                                Kode Semester (5 Digit), Kode Mata Kuliah, Nama Mata Kuliah, Nama Kelas Paralel (A/B), Bobot SKS, Kapasitas Kuota, Ruang, Dosen Pengampu
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.feeder.export', ['kategori' => 'kelas_kuliah', 'tahun_akademik_id' => optional($selectedTa)->id, 'program_studi_id' => request('program_studi_id')]) }}" class="btn btn-sm btn-dark px-3 py-1.5 uppercase fw-bold small">
                                    <i class="bi bi-download me-1"></i> Unduh CSV
                                </a>
                            </td>
                        </tr>

                        <tr>
                            <td class="text-center font-monospace fw-bold">3</td>
                            <td class="ps-3">
                                <strong class="uppercase text-dark d-block">KRS & Nilai Perkuliahan Kelas</strong>
                                <span class="text-muted font-monospace" style="font-size: 10px;">Tabel: krs_nilai_feeder.csv</span>
                            </td>
                            <td class="ps-3 text-muted font-monospace" style="font-size: 11px;">
                                NIM, Nama Mahasiswa, Kode Semester, Kode MK, Nama MK, Nama Kelas (A/B), Bobot SKS, Nilai Angka (0-100), Nilai Huruf (A-E), Nilai Indeks (4.00-0.00), Kode Prodi
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.feeder.export', ['kategori' => 'krs_nilai', 'tahun_akademik_id' => optional($selectedTa)->id, 'program_studi_id' => request('program_studi_id')]) }}" class="btn btn-sm btn-dark px-3 py-1.5 uppercase fw-bold small">
                                    <i class="bi bi-download me-1"></i> Unduh CSV
                                </a>
                            </td>
                        </tr>

                        <tr>
                            <td class="text-center font-monospace fw-bold">4</td>
                            <td class="ps-3">
                                <strong class="uppercase text-dark d-block">Aktivitas Kuliah Mahasiswa (AKM)</strong>
                                <span class="text-muted font-monospace" style="font-size: 10px;">Tabel: akm_feeder.csv</span>
                            </td>
                            <td class="ps-3 text-muted font-monospace" style="font-size: 11px;">
                                NIM, Nama Mahasiswa, Kode Semester, Status Mahasiswa (A=Aktif, C=Cuti, L=Lulus, N=Non-Aktif), IPS Semester, IPK Kumulatif, SKS Semester, Total SKS Kumulatif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.feeder.export', ['kategori' => 'akm', 'tahun_akademik_id' => optional($selectedTa)->id, 'program_studi_id' => request('program_studi_id')]) }}" class="btn btn-sm btn-dark px-3 py-1.5 uppercase fw-bold small">
                                    <i class="bi bi-download me-1"></i> Unduh CSV
                                </a>
                            </td>
                        </tr>

                        <tr>
                            <td class="text-center font-monospace fw-bold">5</td>
                            <td class="ps-3">
                                <strong class="uppercase text-dark d-block">Penugasan Ajar Dosen</strong>
                                <span class="text-muted font-monospace" style="font-size: 10px;">Tabel: ajar_dosen_feeder.csv</span>
                            </td>
                            <td class="ps-3 text-muted font-monospace" style="font-size: 11px;">
                                NIDN / NUPTK, Nama Dosen, Kode Semester, Kode Mata Kuliah, Nama Mata Kuliah, Nama Kelas (A/B), Beban SKS Dosen, Rencana & Realisasi Tatap Muka (16 Pertemuan)
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.feeder.export', ['kategori' => 'ajar_dosen', 'tahun_akademik_id' => optional($selectedTa)->id, 'program_studi_id' => request('program_studi_id')]) }}" class="btn btn-sm btn-dark px-3 py-1.5 uppercase fw-bold small">
                                    <i class="bi bi-download me-1"></i> Unduh CSV
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- 4. PANEL PENCADANGAN BASIS DATA PENUH (1-CLICK SQL BACKUP) --}}
    <div class="card border-0 shadow-sm border-top border-dark border-4 mb-5 bg-white">
        <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h6 class="fw-bold text-dark uppercase mb-1">Cadangan Penuh Pangkalan Data (Full SQL Database Dump)</h6>
                <p class="small text-muted mb-0">
                    Unduh salinan lengkap seluruh tabel dan data transaksi SIAKAD dalam format <code>.sql</code> standar MySQL/MariaDB. Disarankan untuk mengunduh cadangan secara berkala setiap akhir pekan atau sebelum pergantian semester.
                </p>
            </div>
            <div class="shrink-0">
                <a href="{{ route('admin.feeder.backup-sql') }}" class="btn btn-outline-dark px-4 py-2 uppercase fw-bold small">
                    <i class="bi bi-hdd-network me-1"></i> Download Full Backup (.SQL)
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
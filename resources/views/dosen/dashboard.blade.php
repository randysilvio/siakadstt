@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    
    {{-- HEADER PROFIL DOSEN --}}
    <div class="card border-0 shadow-sm mb-4 rounded-0 bg-dark text-white border-top border-secondary border-4">
        <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <span class="badge bg-light text-dark rounded-0 font-monospace uppercase mb-2">DOSEN COMMAND CENTER & TRIDHARMA</span>
                <h3 class="fw-bold mb-1 uppercase">{{ strtoupper($dosen->nama_lengkap) }}</h3>
                <p class="mb-0 text-white-50 font-monospace small uppercase">
                    NIDN: {{ $dosen->nidn ?? 'BELUM TERDAFTAR' }} | 
                    JABATAN: {{ strtoupper($dosen->jabatan_akademik ?? 'TENAGA PENGAJAR') }} | 
                    HOMEBASE: {{ strtoupper(optional($dosen->programStudi)->nama_prodi ?? 'STT GPI PAPUA') }}
                </p>
            </div>
            <div class="text-md-end">
                <span class="text-white-50 small font-monospace uppercase d-block mb-1">PERIODE AKADEMIK AKTIF</span>
                <span class="badge bg-primary rounded-0 px-3 py-2 uppercase font-monospace fs-6">
                    {{ isset($tahunAkademik) && $tahunAkademik ? $tahunAkademik->tahun . ' (' . strtoupper($tahunAkademik->semester) . ')' : 'BELUM DIATUR' }}
                </span>
            </div>
        </div>
    </div>

    {{-- NOTIFIKASI SISTEM & ERROR VALIDASI RPS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small font-monospace" role="alert">
            <strong class="d-block mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i> GAGAL MEMPROSES DATA:</strong>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @php
        $totalSksAmpu = $mata_kuliahs->sum('sks');
        $totalKelasAmpu = $mata_kuliahs->count();
        $totalRpsTerisi = $mata_kuliahs->whereNotNull('file_rps')->count();
        $mkBelumRps = $totalKelasAmpu - $totalRpsTerisi;
        $persenRps = $totalKelasAmpu > 0 ? round(($totalRpsTerisi / $totalKelasAmpu) * 100) : 100;
        $totalSkBkd = isset($ringkasanBkd['total_sks_bkd']) ? $ringkasanBkd['total_sks_bkd'] : $totalSksAmpu;
    @endphp

    {{-- DOSEN COMMAND CENTER: SMART TO-DO LIST & INDIKATOR PRIORITAS KERJA --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3">
                    <span class="text-muted small font-monospace uppercase fw-bold d-block">1. TOTAL BEBAN BKD</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <h3 class="fw-bold font-monospace text-dark mb-0">{{ $totalSkBkd }} <small class="fs-6">SKS</small></h3>
                        <a href="{{ route('dosen.tridharma.index') }}" class="badge {{ ($totalSkBkd >= 12 && $totalSkBkd <= 16) ? 'bg-success' : 'bg-warning text-dark' }} rounded-0 font-monospace text-decoration-none" style="font-size: 10px;">
                             PENGAJARAN: {{ $totalSksAmpu }} SKS
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3">
                    <span class="text-muted small font-monospace uppercase fw-bold d-block">2. KELENGKAPAN RPS OBE</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <h3 class="fw-bold font-monospace {{ $persenRps == 100 ? 'text-success' : 'text-danger' }} mb-0">{{ $persenRps }}%</h3>
                        <span class="badge {{ $mkBelumRps == 0 ? 'bg-success' : 'bg-danger' }} rounded-0 font-monospace" style="font-size: 10px;">
                            {{ $mkBelumRps == 0 ? 'LENGKAP' : $mkBelumRps . ' MK BELUM RPS' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3">
                    <span class="text-muted small font-monospace uppercase fw-bold d-block">3. BIMBINGAN TUTORSHIP</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <h3 class="fw-bold font-monospace text-primary mb-0">{{ $jumlahMahasiswaWali }} <small class="fs-6">MHS</small></h3>
                        @if(($krsWaliMenunggu ?? 0) > 0)
                            <a href="{{ route('perwalian.index') }}" class="badge bg-warning text-dark rounded-0 font-monospace text-decoration-none" style="font-size: 10px;">
                                {{ $krsWaliMenunggu }} ANTRE KRS
                            </a>
                        @else
                            <a href="{{ route('perwalian.index') }}" class="badge bg-light text-dark border rounded-0 font-monospace text-decoration-none" style="font-size: 10px;">
                                TERKENDALI
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3">
                    <span class="text-muted small font-monospace uppercase fw-bold d-block">4. PROGRES BUKU NILAI</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <h3 class="fw-bold font-monospace {{ ($persenNilaiTerisi ?? 100) == 100 ? 'text-success' : 'text-primary' }} mb-0">{{ $persenNilaiTerisi ?? 100 }}%</h3>
                        <span class="badge {{ ($mkBelumLengkapNilai ?? 0) == 0 ? 'bg-success' : 'bg-dark' }} rounded-0 font-monospace" style="font-size: 10px;">
                            {{ ($mkBelumLengkapNilai ?? 0) == 0 ? 'TUNTAS' : ($mkBelumLengkapNilai ?? 0) . ' KELAS BELUM' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- MATA KULIAH YANG DIAMPU --}}
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4">
                <div class="card-header bg-dark text-white py-3 rounded-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 uppercase small"><i class="bi bi-journal-bookmark-fill me-2"></i>Beban Pengajaran Semester Berjalan</h6>
                    <a href="{{ route('verum.index') }}" class="btn btn-sm btn-light rounded-0 uppercase fw-bold small py-1 px-3">
                        <i class="bi bi-laptop me-1"></i> Ruang Kelas Verum
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="bg-light text-dark small uppercase fw-bold text-center">
                                <tr>
                                    <th class="text-start ps-3">IDENTITAS MATA KULIAH</th>
                                    <th style="width: 7%;">SKS</th>
                                    <th style="width: 14%;">PESERTA</th>
                                    <th style="width: 14%;">DOKUMEN RPS</th>
                                    <th style="width: 14%;">PROGRES NILAI</th>
                                    <th style="width: 25%;">AKSI CEPAT</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                @forelse($mata_kuliahs as $mk)
                                    <tr>
                                        <td class="ps-3">
                                            <span class="fw-bold text-dark uppercase d-block">
                                                {{ $mk->nama_mk }}
                                                @if((int)$mk->dosen_id === (int)$dosen->id)
                                                    <span class="badge bg-dark rounded-0 ms-1 font-monospace" style="font-size: 9px;">UTAMA</span>
                                                @else
                                                    <span class="badge bg-secondary rounded-0 ms-1 font-monospace" style="font-size: 9px;">TIM TEACHING</span>
                                                @endif
                                            </span>
                                            <span class="text-muted small font-monospace">{{ $mk->kode_mk }} | SMT {{ $mk->semester }}</span>
                                        </td>
                                        <td class="text-center fw-bold font-monospace">{{ $mk->sks }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-0 px-2 py-1 font-monospace fw-bold" style="font-size: 11px;" data-bs-toggle="modal" data-bs-target="#mhsModal-{{ $mk->id }}">
                                                <i class="bi bi-people-fill me-1"></i>{{ $mk->mahasiswas_count }} MHS
                                            </button>
                                        </td>
                                        <td class="text-center">
                                            @if($mk->file_rps)
                                                <a href="{{ asset('storage/' . $mk->file_rps) }}" target="_blank" class="badge bg-success rounded-0 text-decoration-none font-monospace py-1 px-2">
                                                    <i class="bi bi-file-earmark-check me-1"></i>TERSEDIA
                                                </a>
                                            @else
                                                <span class="badge bg-warning text-dark rounded-0 font-monospace py-1 px-2">BELUM ADA</span>
                                            @endif
                                        </td>
                                        <td class="text-center font-monospace">
                                            @if(($mk->jml_peserta_disetujui ?? 0) > 0)
                                                <span class="badge {{ $mk->jml_sudah_dinilai == $mk->jml_peserta_disetujui ? 'bg-success' : 'bg-light text-dark border' }} rounded-0">
                                                    {{ $mk->jml_sudah_dinilai }}/{{ $mk->jml_peserta_disetujui }} DINILAI
                                                </span>
                                            @else
                                                <span class="text-muted" style="font-size: 10px;">0 PESERTA</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group rounded-0" role="group">
                                                <a href="{{ route('dosen.cetak_absensi', $mk->id) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2 uppercase fw-bold" style="font-size: 10px;" title="Cetak Daftar Hadir 16 Pertemuan">
                                                    <i class="bi bi-printer me-1"></i>Absen
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2 uppercase fw-bold" style="font-size: 10px;" data-bs-toggle="modal" data-bs-target="#uploadRpsModal-{{ $mk->id }}" title="Unggah / Perbarui RPS">
                                                    <i class="bi bi-upload me-1"></i>RPS
                                                </button>
                                                <a href="{{ route('nilai.show', $mk->id) }}" class="btn btn-sm btn-dark rounded-0 py-1 px-2 uppercase fw-bold" style="font-size: 10px;" title="Input Buku Nilai OBE">
                                                    <i class="bi bi-pencil-square me-1"></i>Nilai
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-5 text-muted small uppercase fw-bold">Tidak terdapat penugasan pengajaran pada periode ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @foreach($mata_kuliahs as $mk)
                        <div class="modal fade" id="mhsModal-{{ $mk->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content rounded-0 border-0 border-top border-dark border-4">
                                    <div class="modal-header bg-dark text-white rounded-0 py-3">
                                        <h6 class="modal-title fw-bold uppercase small">Daftar Peserta Kelas: {{ $mk->nama_mk }} ({{ $mk->kode_mk }})</h6>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-bordered align-middle mb-0 small">
                                                <thead class="bg-light text-dark uppercase text-center fw-bold">
                                                    <tr>
                                                        <th style="width: 8%;">NO</th>
                                                        <th style="width: 20%;">NIM</th>
                                                        <th class="text-start">NAMA MAHASISWA</th>
                                                        <th style="width: 25%;">PROGRAM STUDI</th>
                                                        <th style="width: 15%;">STATUS KRS</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($mk->mahasiswas as $index => $mhs)
                                                        <tr>
                                                            <td class="text-center font-monospace">{{ $index + 1 }}</td>
                                                            <td class="text-center font-monospace fw-bold text-primary">{{ $mhs->nim }}</td>
                                                            <td class="fw-bold uppercase text-dark ps-3">{{ $mhs->nama_lengkap }}</td>
                                                            <td class="text-center uppercase">{{ optional($mhs->programStudi)->nama_prodi ?? '-' }}</td>
                                                            <td class="text-center">
                                                                <span class="badge {{ $mhs->status_krs == 'Disetujui' ? 'bg-success' : 'bg-warning text-dark' }} rounded-0 font-monospace" style="font-size: 10px;">
                                                                    {{ strtoupper($mhs->status_krs ?? 'DRAFT') }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr><td colspan="5" class="text-center py-4 text-muted uppercase fw-bold">Belum ada mahasiswa yang mengambil mata kuliah ini pada semester aktif.</td></tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="uploadRpsModal-{{ $mk->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content rounded-0 border-0 border-top border-dark border-4">
                                    <form action="{{ route('dosen.upload_rps', $mk->id) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="modal-header bg-dark text-white rounded-0 py-3">
                                            <h6 class="modal-title fw-bold uppercase small">Unggah Dokumen RPS: {{ $mk->nama_mk }}</h6>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <label class="form-label small fw-bold uppercase text-dark">Berkas RPS Resmi (Format PDF, Maks. 5MB) <span class="text-danger">*</span></label>
                                            <input type="file" name="file_rps" class="form-control rounded-0" accept="application/pdf,.pdf" required>
                                            @if($mk->file_rps)
                                                <div class="mt-2 small font-monospace text-success">
                                                    <i class="bi bi-check-circle-fill me-1"></i> File RPS sudah ada. Mengunggah file baru akan menggantikan file lama.
                                                </div>
                                            @endif
                                        </div>
                                        <div class="modal-footer bg-light border-top rounded-0">
                                            <button type="button" class="btn btn-sm btn-outline-dark rounded-0 uppercase fw-bold small" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm">Simpan RPS</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- JADWAL KULIAH MINGGUAN --}}
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom rounded-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark uppercase small"><i class="bi bi-calendar3 me-2"></i>Agenda Perkuliahan Mingguan</h6>
                    <a href="{{ route('dosen.cetak_jadwal') }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-0 px-3 fw-bold uppercase small">
                        <i class="bi bi-printer me-1"></i> Cetak Jadwal
                    </a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered align-middle mb-0 small">
                        <thead class="bg-light text-dark uppercase text-center fw-bold">
                            <tr>
                                <th style="width: 15%;">HARI</th>
                                <th style="width: 22%;">WAKTU (WIT)</th>
                                <th class="text-start">MATA KULIAH</th>
                                <th style="width: 18%;">RUANG</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($jadwalKuliah as $jadwal)
                                <tr>
                                    <td class="text-center fw-bold text-dark uppercase">{{ $jadwal->hari }}</td>
                                    <td class="text-center font-monospace text-muted fw-bold">
                                        {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                                    </td>
                                    <td class="fw-bold text-dark uppercase ps-3">
                                        {{ $jadwal->mataKuliah->nama_mk }}
                                        <span class="d-block text-muted font-monospace" style="font-size: 10px;">{{ $jadwal->mataKuliah->kode_mk }} ({{ $jadwal->mataKuliah->sks }} SKS)</span>
                                    </td>
                                    <td class="text-center font-monospace fw-bold text-primary">{{ $jadwal->ruang ?? 'TBA' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center py-4 text-muted small uppercase fw-bold">Jadwal perkuliahan belum diterbitkan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- PORTOFOLIO SURAT & SK --}}
            <div class="card border-0 shadow-sm rounded-0 mb-4 border-top border-dark border-4">
                <div class="card-header bg-white py-3 border-bottom rounded-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark uppercase small"><i class="bi bi-envelope-paper-fill me-2 text-primary"></i>Arsip Surat Keputusan (SK) & Penugasan Tridharma</h6>
                    <form action="{{ route('dosen.dashboard') }}" method="GET" class="d-flex">
                        <input type="text" name="search_surat" class="form-control form-control-sm rounded-0 border-end-0 font-monospace uppercase" placeholder="CARI SK..." value="{{ request('search_surat') }}">
                        <button class="btn btn-sm btn-dark rounded-0" type="submit"><i class="bi bi-search"></i></button>
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 small">
                            <thead class="bg-light text-dark uppercase text-center fw-bold">
                                <tr>
                                    <th style="width: 25%;">NOMOR SURAT</th>
                                    <th class="text-start">JUDUL / PERIHAL SK</th>
                                    <th style="width: 20%;">PERAN / JABATAN</th>
                                    <th style="width: 15%;">UNDUH</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($suratKeputusans as $surat)
                                    <tr>
                                        <td class="text-center font-monospace fw-bold">{{ $surat->nomor_surat ?? '-' }}</td>
                                        <td class="ps-3">
                                            <span class="d-block fw-bold text-dark uppercase">{{ $surat->judul }}</span>
                                            <span class="badge bg-light text-dark border rounded-0 mt-1 font-monospace" style="font-size: 10px;">{{ strtoupper($surat->jenis_surat) }}</span>
                                        </td>
                                        <td class="text-center font-monospace uppercase small">
                                            {{ $surat->pivot->jabatan_dalam_surat ?? 'ANGGOTA' }}
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('surat-keputusan.download', $surat->id) }}" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2 font-monospace fw-bold" style="font-size: 10px;">
                                               <i class="bi bi-file-earmark-pdf-fill me-1"></i>PDF
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-4 text-muted small uppercase fw-bold">Belum ada arsip SK yang tertaut ke profil Anda.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($suratKeputusans->hasPages())
                        <div class="p-3 border-top bg-light">
                            {{ $suratKeputusans->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- PORTAL MULTI-PERAN & TRIDHARMA --}}
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4">
                <div class="card-header bg-dark text-white py-3 rounded-0">
                    <h6 class="fw-bold mb-0 uppercase small">Aksesibilitas Tugas & Karir Dosen</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('dosen.tridharma.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center bg-light">
                        <div>
                            <span class="fw-bold small text-primary d-block uppercase"><i class="bi bi-award-fill me-1"></i>PORTOFOLIO TRIDHARMA & BKD (SISTER)</span>
                            <small class="text-muted uppercase" style="font-size: 11px;">Penelitian, PkM, Kalkulator BKD & KUM Jafung</small>
                        </div>
                        <i class="bi bi-chevron-right text-primary"></i>
                    </a>

                    <a href="{{ route('perwalian.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-bold small text-dark d-block uppercase">Bimbingan Tutorship Akademik</span>
                            <small class="text-muted uppercase" style="font-size: 11px;">Validasi KRS & pendampingan mahasiswa Tutorship</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-dark rounded-0 px-2 font-monospace">{{ $jumlahMahasiswaWali }} MHS</span>
                            @if(($krsWaliMenunggu ?? 0) > 0)
                                <span class="badge bg-warning text-dark rounded-0 d-block mt-1 font-monospace" style="font-size: 9px;">{{ $krsWaliMenunggu }} MENUNGGU</span>
                            @endif
                        </div>
                    </a>
                    
                    @if(Auth::user()->isKaprodi() || Auth::user()->hasRole('kaprodi'))
                        <a href="{{ route('kaprodi.dashboard') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold small text-dark d-block uppercase">Dashboard Ketua Program Studi</span>
                                <small class="text-muted uppercase" style="font-size: 11px;">{{ optional($prodiYangDikepalai)->nama_prodi ?? 'Otoritas Validasi Prodi' }}</small>
                            </div>
                            <div class="text-end">
                                @if(isset($dataKaprodi['krs_count']) && $dataKaprodi['krs_count'] > 0)
                                    <span class="badge bg-warning text-dark rounded-0 font-monospace">{{ $dataKaprodi['krs_count'] }} KRS</span>
                                @else
                                    <i class="bi bi-chevron-right text-muted"></i>
                                @endif
                            </div>
                        </a>
                    @endif

                    @if($dosen->nidn)
                        <a href="{{ route('dosen.public.show', $dosen->nidn) }}" target="_blank" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold small text-dark d-block uppercase">Profil Publik & Portofolio Riset</span>
                                <small class="text-muted uppercase" style="font-size: 11px;">Lihat tampilan direktori publik (SINTA/Scholar)</small>
                            </div>
                            <i class="bi bi-box-arrow-up-right text-muted"></i>
                        </a>
                    @endif

                    @if(Auth::user()->hasRole('rektorat'))
                        <a href="{{ route('rektorat.dashboard') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold small text-dark d-block uppercase">Monitoring Eksekutif Rektorat</span>
                                <small class="text-muted uppercase" style="font-size: 11px;">Panel pimpinan institusi</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                    @endif

                    @if(Auth::user()->hasRole('penjaminan_mutu'))
                        <a href="{{ route('mutu.dashboard') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold small text-dark d-block uppercase">Panel Penjaminan Mutu (UPM)</span>
                                <small class="text-muted uppercase" style="font-size: 11px;">Akses laporan EDOM & Borang Akreditasi</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                    @endif
                </div>
            </div>

            {{-- WARTA AKADEMIK --}}
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-dark text-white py-3 rounded-0">
                    <h6 class="fw-bold mb-0 uppercase small">Warta Resmi Institusi</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($pengumumans as $p)
                            @if($p->target_role === 'semua')
                                <a href="{{ route('pengumuman.public.show', $p) }}" target="_blank" class="list-group-item list-group-item-action py-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted font-monospace" style="font-size: 10px;">{{ $p->created_at->translatedFormat('d M Y') }}</small>
                                        <span class="badge bg-light text-dark border rounded-0 font-monospace" style="font-size: 9px;">PUBLIK</span>
                                    </div>
                                    <span class="fw-bold small text-dark uppercase d-block">{{ Str::limit($p->judul, 65) }}</span>
                                </a>
                            @else
                                <div class="list-group-item py-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted font-monospace" style="font-size: 10px;">{{ $p->created_at->translatedFormat('d M Y') }}</small>
                                        <span class="badge bg-dark text-white rounded-0 font-monospace" style="font-size: 9px;">INTERNAL {{ strtoupper($p->target_role) }}</span>
                                    </div>
                                    <span class="fw-bold small text-dark uppercase d-block mb-1">{{ $p->judul }}</span>
                                    <p class="small text-muted mb-0" style="font-size: 11px;">{{ Str::limit(strip_tags($p->konten), 120) }}</p>
                                </div>
                            @endif
                        @empty
                            <div class="p-4 text-center text-muted small uppercase fw-bold">Belum ada pengumuman terbaru.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    
    {{-- HEADER PROFIL DOSEN --}}
    <div class="card border-0 shadow-sm mb-4 rounded-0 bg-dark text-white border-top border-secondary border-4">
        <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <span class="badge bg-light text-dark rounded-0 font-monospace uppercase mb-2">DASHBOARD DOSEN</span>
                <h3 class="fw-bold mb-1 uppercase">{{ strtoupper($dosen->nama_lengkap) }}</h3>
                <p class="mb-0 text-white-50 font-monospace small uppercase">
                    NIDN: {{ $dosen->nidn ?? '-' }} | 
                    JABATAN: {{ strtoupper($dosen->jabatan_akademik ?? 'TENAGA PENGAJAR') }} | 
                    PRODI: {{ strtoupper(optional($dosen->programStudi)->nama_prodi ?? 'STT GPI PAPUA') }}
                </p>
            </div>
            <div class="text-md-end">
                <span class="text-white-50 small font-monospace uppercase d-block mb-1">PERIODE AKADEMIK</span>
                <span class="badge bg-light text-dark rounded-0 px-3 py-2 uppercase font-monospace fs-6">
                    {{ isset($tahunAkademik) && $tahunAkademik ? $tahunAkademik->tahun . ' (' . strtoupper($tahunAkademik->semester) . ')' : 'BELUM DIATUR' }}
                </span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold">
            {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold">
            {{ session('error') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert"></button>
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

    {{-- RINGKASAN BEBAN KERJA SEMESTER --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3">
                    <span class="text-muted small font-monospace uppercase fw-bold d-block">BEBAN SKS TRIDHARMA <x-info-aturan kunci="bkd_total" /></span>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <h3 class="fw-bold font-monospace text-dark mb-0">{{ $totalSkBkd }} <small class="fs-6">SKS</small></h3>
                        <span class="badge bg-light text-dark border rounded-0 font-monospace" style="font-size: 10px;">
                            PENGAJARAN: {{ $totalSksAmpu }} SKS
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3">
                    <span class="text-muted small font-monospace uppercase fw-bold d-block">DOKUMEN RPS</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <h3 class="fw-bold font-monospace text-dark mb-0">{{ $persenRps }}%</h3>
                        <span class="badge bg-light text-dark border rounded-0 font-monospace" style="font-size: 10px;">
                            {{ $mkBelumRps == 0 ? 'LENGKAP' : $mkBelumRps . ' BELUM DIUNGGAH' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3">
                    <span class="text-muted small font-monospace uppercase fw-bold d-block">MAHASISWA BIMBINGAN</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <h3 class="fw-bold font-monospace text-dark mb-0">{{ $jumlahMahasiswaWali }} <small class="fs-6">MHS</small></h3>
                        <span class="badge bg-light text-dark border rounded-0 font-monospace" style="font-size: 10px;">
                            {{ ($krsWaliMenunggu ?? 0) > 0 ? $krsWaliMenunggu . ' ANTRE KRS' : 'SELESAI' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3">
                    <span class="text-muted small font-monospace uppercase fw-bold d-block">PENGISIAN NILAI</span>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <h3 class="fw-bold font-monospace text-dark mb-0">{{ $persenNilaiTerisi ?? 100 }}%</h3>
                        <span class="badge bg-light text-dark border rounded-0 font-monospace" style="font-size: 10px;">
                            {{ ($mkBelumLengkapNilai ?? 0) == 0 ? 'SELESAI' : ($mkBelumLengkapNilai ?? 0) . ' KELAS' }}
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
                    <h6 class="fw-bold mb-0 uppercase small">Mata Kuliah yang Diampu</h6>
                    <a href="{{ route('verum.index') }}" class="btn btn-sm btn-light rounded-0 uppercase fw-bold small py-1 px-3">
                        Kelas Verum
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="bg-light text-dark small uppercase fw-bold text-center">
                                <tr>
                                    <th class="text-start ps-3">MATA KULIAH</th>
                                    <th style="width: 7%;">SKS</th>
                                    <th style="width: 14%;">PESERTA</th>
                                    <th style="width: 14%;">RPS</th>
                                    <th style="width: 14%;">NILAI</th>
                                    <th style="width: 25%;">AKSI</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                @forelse($mata_kuliahs as $mk)
                                    <tr>
                                        <td class="ps-3">
                                            <span class="fw-bold text-dark uppercase d-block">
                                                {{ $mk->nama_mk }}
                                                @if((int)$mk->dosen_id !== (int)$dosen->id)
                                                    <span class="badge bg-secondary rounded-0 ms-1 font-monospace" style="font-size: 9px;">TIM</span>
                                                @endif
                                            </span>
                                            <span class="text-muted small font-monospace">{{ $mk->kode_mk }} | SMT {{ $mk->semester }}</span>
                                        </td>
                                        <td class="text-center fw-bold font-monospace">{{ $mk->sks }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-dark rounded-0 px-2 py-1 font-monospace fw-bold" style="font-size: 11px;" data-bs-toggle="modal" data-bs-target="#mhsModal-{{ $mk->id }}">
                                                {{ $mk->mahasiswas_count }} MHS
                                            </button>
                                        </td>
                                        <td class="text-center">
                                            @if($mk->file_rps)
                                                <a href="{{ asset('storage/' . $mk->file_rps) }}" target="_blank" class="badge bg-dark text-white rounded-0 text-decoration-none font-monospace py-1 px-2">
                                                    ADA
                                                </a>
                                            @else
                                                <span class="badge bg-light text-muted border rounded-0 font-monospace py-1 px-2">KOSONG</span>
                                            @endif
                                        </td>
                                        <td class="text-center font-monospace">
                                            @if(($mk->jml_peserta_disetujui ?? 0) > 0)
                                                <span class="badge bg-light text-dark border rounded-0">
                                                    {{ $mk->jml_sudah_dinilai }}/{{ $mk->jml_peserta_disetujui }}
                                                </span>
                                            @else
                                                <span class="text-muted" style="font-size: 10px;">0</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group rounded-0" role="group">
                                                <a href="{{ route('dosen.cetak_absensi', $mk->id) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2 uppercase fw-bold" style="font-size: 10px;">
                                                    Absen
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2 uppercase fw-bold" style="font-size: 10px;" data-bs-toggle="modal" data-bs-target="#uploadRpsModal-{{ $mk->id }}">
                                                    RPS
                                                </button>
                                                <a href="{{ route('nilai.show', $mk->id) }}" class="btn btn-sm btn-dark rounded-0 py-1 px-2 uppercase fw-bold" style="font-size: 10px;">
                                                    Nilai
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-5 text-muted small uppercase fw-bold">Tidak ada mata kuliah pada semester ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @foreach($mata_kuliahs as $mk)
                        <div class="modal fade" id="mhsModal-{{ $mk->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content rounded-0 border-0 border-top border-dark border-4">
                                    <div class="modal-header bg-dark text-white rounded-0 py-3">
                                        <h6 class="modal-title fw-bold uppercase small">Peserta Kelas: {{ $mk->nama_mk }} ({{ $mk->kode_mk }})</h6>
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
                                                            <td class="text-center font-monospace fw-bold">{{ $mhs->nim }}</td>
                                                            <td class="fw-bold uppercase text-dark ps-3">{{ $mhs->nama_lengkap }}</td>
                                                            <td class="text-center uppercase">{{ optional($mhs->programStudi)->nama_prodi ?? '-' }}</td>
                                                            <td class="text-center font-monospace">{{ strtoupper($mhs->status_krs ?? 'DRAFT') }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr><td colspan="5" class="text-center py-4 text-muted uppercase fw-bold">Belum ada mahasiswa terdaftar.</td></tr>
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
                                            <h6 class="modal-title fw-bold uppercase small">Unggah RPS: {{ $mk->nama_mk }}</h6>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <label class="form-label small fw-bold uppercase text-dark">Berkas RPS (PDF, Maks. 5MB) <span class="text-danger">*</span></label>
                                            <input type="file" name="file_rps" class="form-control rounded-0" accept="application/pdf,.pdf" required>
                                        </div>
                                        <div class="modal-footer bg-light border-top rounded-0">
                                            <button type="button" class="btn btn-sm btn-outline-dark rounded-0 uppercase fw-bold small" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small">Simpan</button>
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
                    <h6 class="fw-bold mb-0 text-dark uppercase small">Jadwal Mengajar</h6>
                    <a href="{{ route('dosen.cetak_jadwal') }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-0 px-3 fw-bold uppercase small">
                        Cetak Jadwal
                    </a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered align-middle mb-0 small">
                        <thead class="bg-light text-dark uppercase text-center fw-bold">
                            <tr>
                                <th style="width: 15%;">HARI</th>
                                <th style="width: 22%;">WAKTU</th>
                                <th class="text-start">MATA KULIAH</th>
                                <th style="width: 12%;">KELAS</th>
                                <th style="width: 15%;">RUANG</th>
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
                                    <td class="text-center font-monospace fw-bold">{{ $jadwal->nama_kelas ?? 'A' }}</td>
                                    <td class="text-center font-monospace">{{ $jadwal->ruang ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-4 text-muted small uppercase fw-bold">Jadwal belum tersedia.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ARSIP SURAT KEPUTUSAN --}}
            <div class="card border-0 shadow-sm rounded-0 mb-4 border-top border-dark border-4">
                <div class="card-header bg-white py-3 border-bottom rounded-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark uppercase small">Arsip Surat Keputusan (SK)</h6>
                    <form action="{{ route('dosen.dashboard') }}" method="GET" class="d-flex">
                        <input type="text" name="search_surat" class="form-control form-control-sm rounded-0 border-end-0 font-monospace uppercase" placeholder="CARI SK..." value="{{ request('search_surat') }}">
                        <button class="btn btn-sm btn-dark rounded-0" type="submit">Cari</button>
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 small">
                            <thead class="bg-light text-dark uppercase text-center fw-bold">
                                <tr>
                                    <th style="width: 25%;">NOMOR SURAT</th>
                                    <th class="text-start">PERIHAL</th>
                                    <th style="width: 20%;">PERAN</th>
                                    <th style="width: 15%;">BERKAS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($suratKeputusans as $surat)
                                    <tr>
                                        <td class="text-center font-monospace fw-bold">{{ $surat->nomor_surat ?? '-' }}</td>
                                        <td class="ps-3 fw-bold text-dark uppercase">{{ $surat->judul }}</td>
                                        <td class="text-center font-monospace uppercase small">{{ $surat->pivot->jabatan_dalam_surat ?? 'ANGGOTA' }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('surat-keputusan.download', $surat->id) }}" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2 font-monospace fw-bold" style="font-size: 10px;">
                                               Unduh PDF
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-4 text-muted small uppercase fw-bold">Belum ada arsip SK.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- MENU TUGAS DOSEN (SERAGAM & BERSIH TANPA WARNA-WARNI) --}}
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4">
                <div class="card-header bg-dark text-white py-3 rounded-0">
                    <h6 class="fw-bold mb-0 uppercase small">Menu Dosen</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('dosen.tridharma.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-dark uppercase">Data Tridharma & BKD</span>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>

                    <a href="{{ route('perwalian.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-dark uppercase">Bimbingan {{ \App\Models\Pengaturan::get('istilah_perwalian', 'Tutorship') }}</span>
                        <span class="badge bg-dark rounded-0 px-2 font-monospace">{{ $jumlahMahasiswaWali }} MHS</span>
                    </a>

                    <a href="{{ route('bimbingan.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-dark uppercase">Bimbingan Tugas Akhir & PPL</span>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                    
                    @if(Auth::user()->isKaprodi() || Auth::user()->hasRole('kaprodi'))
                        <a href="{{ route('kaprodi.dashboard') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-dark uppercase">Dashboard Kaprodi</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                    @endif

                    @if($dosen->nidn)
                        <a href="{{ route('dosen.public.show', $dosen->nidn) }}" target="_blank" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-dark uppercase">Profil Publik Dosen</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                    @endif

                    @if(Auth::user()->hasRole('rektorat'))
                        <a href="{{ route('rektorat.dashboard') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-dark uppercase">Dashboard Rektorat</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                    @endif

                    @if(Auth::user()->hasRole('penjaminan_mutu'))
                        <a href="{{ route('mutu.dashboard') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-dark uppercase">Dashboard Penjaminan Mutu</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                    @endif
                </div>
            </div>

            {{-- PENGUMUMAN --}}
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-dark text-white py-3 rounded-0">
                    <h6 class="fw-bold mb-0 uppercase small">Pengumuman</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($pengumumans as $p)
                            <a href="{{ route('pengumuman.internal.show', $p) }}" class="list-group-item list-group-item-action py-3 border-bottom">
                                <small class="text-muted font-monospace d-block mb-1" style="font-size: 10px;">{{ $p->created_at->translatedFormat('d M Y') }}</small>
                                <span class="fw-bold small text-dark uppercase d-block">{{ $p->judul }}</span>
                            </a>
                        @empty
                            <div class="p-4 text-center text-muted small uppercase fw-bold">Belum ada pengumuman.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
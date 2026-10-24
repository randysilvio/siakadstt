@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    
    {{-- PROFIL SINGKAT MAHASISWA / ALUMNI --}}
    <div class="card border-0 shadow-sm mb-4 rounded-0 bg-white border-top border-dark border-4">
        <div class="card-body p-4 d-flex flex-column flex-md-row align-items-md-center gap-3">
            <div>
                @if($mahasiswa->foto_profil)
                    <img src="{{ asset('storage/' . $mahasiswa->foto_profil) }}" class="rounded-0 border border-dark" style="width: 80px; height: 100px; object-fit: cover;">
                @else
                    <div class="bg-light text-secondary border border-dark d-flex align-items-center justify-content-center" style="width: 80px; height: 100px;">
                        <i class="bi bi-person-fill fs-1"></i>
                    </div>
                @endif
            </div>
            <div class="flex-grow-1">
                <h4 class="fw-bold text-dark mb-1 uppercase">{{ $mahasiswa->nama_lengkap }}</h4>
                <p class="text-muted small mb-0 font-monospace uppercase">NIM: {{ $mahasiswa->nim }} | PRODI: {{ optional($mahasiswa->programStudi)->nama_prodi ?? '-' }} | ANGKATAN: {{ $mahasiswa->tahun_masuk }}</p>
                <div class="mt-2 d-flex flex-wrap gap-1">
                    @if($mahasiswa->status_mahasiswa === 'Aktif')
                        <span class="badge bg-success rounded-0 px-2 py-1 font-monospace">STATUS: AKTIF</span>
                    @elseif($mahasiswa->status_mahasiswa === 'Lulus')
                        <span class="badge bg-warning text-dark border border-warning rounded-0 px-2 py-1 font-monospace fw-bold"><i class="bi bi-mortarboard-fill me-1"></i> ALUMNI / LULUS</span>
                        @if($mahasiswa->sudah_isi_tracer)
                            <span class="badge bg-success text-white rounded-0 px-2 py-1 font-monospace"><i class="bi bi-check-circle-fill me-1"></i> TRACER STUDY TERISI</span>
                        @else
                            <span class="badge bg-danger text-white rounded-0 px-2 py-1 font-monospace"><i class="bi bi-exclamation-circle-fill me-1"></i> TRACER STUDY BELUM DIISI</span>
                        @endif
                    @else
                        <span class="badge bg-secondary rounded-0 px-2 py-1 font-monospace">STATUS: {{ strtoupper($mahasiswa->status_mahasiswa) }}</span>
                    @endif
                    <span class="badge bg-light text-dark border rounded-0 px-2 py-1 font-monospace">DOSEN TUTOR: {{ strtoupper(optional($mahasiswa->dosenWali)->nama_lengkap ?? 'BELUM DIPLOT') }}</span>
                </div>
            </div>
        </div>
    </div>

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

    {{-- HORIZONTAL STEPPER BAR ALUR AKADEMIK SEMESTER BERJALAN (KHUSUS MAHASISWA AKTIF) --}}
    @if($mahasiswa->status_mahasiswa === 'Aktif')
        @php
            $periodeAktif = \App\Models\TahunAkademik::where('is_active', true)->first();
            $krsSemesterIni = false;
            if ($periodeAktif) {
                $krsSemesterIni = $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->exists();
            }
            $step1Done = !$memiliki_tagihan;
            $step2Done = $krsSemesterIni;
            $step3Done = $krsSemesterIni && $mahasiswa->status_krs === 'Disetujui';
            $step4Done = $step3Done;
            $step5Done = $step3Done && $mahasiswa->status_evaluasi === 'sudah_isi';
        @endphp
        <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-white">
            <div class="card-header bg-dark text-white py-2 px-3 rounded-0 d-flex justify-content-between align-items-center">
                <span class="uppercase fw-bold small"><i class="bi bi-diagram-3-fill me-2"></i>Alur Progres Akademik Semester Berjalan</span>
                <span class="font-monospace small text-white-50" style="font-size: 11px;">{{ $periodeAktif ? $periodeAktif->tahun . ' (' . strtoupper($periodeAktif->semester) . ')' : '' }}</span>
            </div>
            <div class="card-body p-3">
                <div class="row g-2 text-center">
                    {{-- Step 1: SPP --}}
                    <div class="col-6 col-md-2">
                        <a href="{{ route('pembayaran.riwayat') }}" class="text-decoration-none d-block p-2 border rounded-0 h-100 {{ $step1Done ? 'border-success bg-success bg-opacity-10 text-dark' : 'border-danger bg-danger bg-opacity-10 text-dark' }}">
                            <span class="badge {{ $step1Done ? 'bg-success' : 'bg-danger' }} rounded-0 font-monospace mb-1">1. KEUANGAN SPP</span>
                            <div class="small fw-bold uppercase" style="font-size: 10px;">{{ $step1Done ? 'LUNAS / AMAN' : 'ADA TAGIHAN' }}</div>
                        </a>
                    </div>
                    {{-- Step 2: Pengisian KRS --}}
                    <div class="col-6 col-md-2">
                        <a href="{{ route('krs.index') }}" class="text-decoration-none d-block p-2 border rounded-0 h-100 {{ $step2Done ? 'border-success bg-success bg-opacity-10 text-dark' : 'border-warning bg-warning bg-opacity-10 text-dark' }}">
                            <span class="badge {{ $step2Done ? 'bg-success' : 'bg-warning text-dark' }} rounded-0 font-monospace mb-1">2. ISI KRS</span>
                            <div class="small fw-bold uppercase" style="font-size: 10px;">{{ $step2Done ? 'SUDAH DIAJUKAN' : 'BELUM ISI KRS' }}</div>
                        </a>
                    </div>
                    {{-- Step 3: Validasi Tutor & Kaprodi --}}
                    <div class="col-6 col-md-2">
                        <div class="p-2 border rounded-0 h-100 {{ $step3Done ? 'border-success bg-success bg-opacity-10 text-dark' : ($step2Done ? 'border-warning bg-warning bg-opacity-10 text-dark' : 'bg-light text-muted') }}">
                            <span class="badge {{ $step3Done ? 'bg-success' : ($step2Done ? 'bg-warning text-dark' : 'bg-secondary') }} rounded-0 font-monospace mb-1">3. VALIDASI TUTOR</span>
                            <div class="small fw-bold uppercase" style="font-size: 10px;">{{ $step3Done ? 'DISETUJUI' : ($step2Done ? strtoupper($mahasiswa->status_krs) : 'MENUNGGU KRS') }}</div>
                        </div>
                    </div>
                    {{-- Step 4: Perkuliahan & Verum --}}
                    <div class="col-6 col-md-2">
                        <a href="{{ route('verum.index') }}" class="text-decoration-none d-block p-2 border rounded-0 h-100 {{ $step4Done ? 'border-primary bg-primary bg-opacity-10 text-dark' : 'bg-light text-muted' }}">
                            <span class="badge {{ $step4Done ? 'bg-primary' : 'bg-secondary' }} rounded-0 font-monospace mb-1">4. KULIAH & VERUM</span>
                            <div class="small fw-bold uppercase" style="font-size: 10px;">{{ $step4Done ? 'AKTIF KULIAH' : 'TERKUNCI' }}</div>
                        </a>
                    </div>
                    {{-- Step 5: Evaluasi EDOM --}}
                    <div class="col-6 col-md-2">
                        <a href="{{ route('evaluasi.index') }}" class="text-decoration-none d-block p-2 border rounded-0 h-100 {{ $step5Done ? 'border-success bg-success bg-opacity-10 text-dark' : 'border-warning bg-warning bg-opacity-10 text-dark' }}">
                            <span class="badge {{ $step5Done ? 'bg-success' : 'bg-warning text-dark' }} rounded-0 font-monospace mb-1">5. SURVEI EDOM</span>
                            <div class="small fw-bold uppercase" style="font-size: 10px;">{{ $step5Done ? 'TERPENUHI' : 'WAJIB DIISI' }}</div>
                        </a>
                    </div>
                    {{-- Step 6: KHS & Nilai --}}
                    <div class="col-6 col-md-2">
                        <a href="{{ route('khs.index') }}" class="text-decoration-none d-block p-2 border rounded-0 h-100 {{ $step5Done ? 'border-dark bg-white text-dark' : 'bg-light text-muted' }}">
                            <span class="badge {{ $step5Done ? 'bg-dark' : 'bg-secondary' }} rounded-0 font-monospace mb-1">6. CETAK KHS</span>
                            <div class="small fw-bold uppercase" style="font-size: 10px;">{{ $step5Done ? 'SIAP DIAKSES' : 'TUNGGU EDOM' }}</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- NOTIFIKASI PESAN LULUS ATAU TAGIHAN --}}
    @if($mahasiswa->status_mahasiswa === 'Lulus')
        <div class="alert alert-warning border-0 border-start border-4 border-warning shadow-sm rounded-0 d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 bg-white p-3 gap-3" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-trophy-fill fs-3 me-3 text-warning"></i>
                <div>
                    <span class="fw-bold d-block uppercase small text-dark">Selamat! Anda Resmi Menjadi Alumni STT GPI Papua</span>
                    <small class="text-muted">Dasbor ini berfungsi sebagai arsip akademik Anda. Pastikan Anda telah mengisi <strong>Tracer Study Alumni</strong> untuk membuka akses unduh Transkrip Nilai Akhir.</small>
                </div>
            </div>
            <a href="{{ route('tracer.index') }}" class="btn btn-sm btn-dark rounded-0 px-4 py-2 uppercase fw-bold small text-nowrap">
                <i class="bi bi-briefcase-fill me-1"></i> {{ $mahasiswa->sudah_isi_tracer ? 'Perbarui Tracer Study' : 'Isi Tracer Study Sekarang' }}
            </a>
        </div>
    @elseif($memiliki_tagihan)
        <div class="alert alert-danger border-0 border-start border-4 border-danger shadow-sm rounded-0 d-flex align-items-center mb-4 p-3" role="alert">
            <i class="bi bi-exclamation-octagon-fill fs-4 me-3"></i>
            <div>
                <span class="fw-bold d-block uppercase small">Administrasi Keuangan Tertunda</span>
                <small>Silakan lakukan pelunasan atau unggah bukti pembayaran melalui menu <a href="{{ route('pembayaran.riwayat') }}" class="fw-bold text-danger text-decoration-underline">Riwayat Pembayaran</a>.</small>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- INDIKATOR AKADEMIK --}}
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <div class="card border-0 bg-dark text-white rounded-0 shadow-sm">
                        <div class="card-body text-center py-4">
                            <small class="d-block uppercase opacity-75 mb-1 font-monospace">Indeks Prestasi Kumulatif (IPK)</small>
                            <h2 class="fw-bold font-monospace mb-0">{{ number_format($ipk, 2) }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card border-0 bg-primary text-white rounded-0 shadow-sm">
                        <div class="card-body text-center py-4">
                            <small class="d-block uppercase opacity-75 mb-1 font-monospace">Total SKS Lulus</small>
                            <h2 class="fw-bold font-monospace mb-0">{{ $total_sks }} SKS</h2>
                        </div>
                    </div>
                </div>
            </div>

            {{-- JADWAL KULIAH ATAU ARSIP LULUSAN --}}
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4">
                @if($mahasiswa->status_mahasiswa === 'Lulus')
                    <div class="card-header bg-white py-3 border-bottom text-center rounded-0">
                        <h6 class="fw-bold mb-0 text-dark uppercase small">Arsip Akademik Lulusan</h6>
                    </div>
                    <div class="card-body py-5 text-center">
                        <i class="bi bi-archive text-muted mb-3 d-block" style="font-size: 3rem;"></i>
                        <h5 class="fw-bold text-dark uppercase">Data Jadwal Perkuliahan Ditutup</h5>
                        <p class="text-muted small mb-3">Anda telah menyelesaikan seluruh proses akademik pada program studi ini.</p>
                        <a href="{{ route('transkrip.index') }}" class="btn btn-sm btn-primary rounded-0 px-4 py-2 uppercase fw-bold small shadow-sm">
                            <i class="bi bi-file-earmark-check me-1"></i> Lihat Transkrip Nilai Akhir
                        </a>
                    </div>
                @else
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center rounded-0">
                        <h6 class="fw-bold mb-0 text-dark uppercase small">Jadwal Kuliah Semester Berjalan</h6>
                        <a href="{{ route('jadwal.cetak') }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-0 px-3 fw-bold uppercase small">
                            <i class="bi bi-printer me-1"></i> Cetak Jadwal
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered align-middle mb-0 small">
                            <thead class="bg-light text-dark uppercase text-center fw-bold">
                                <tr>
                                    <th style="width: 22%;">HARI & WAKTU</th>
                                    <th class="text-start">MATA KULIAH & DOSEN</th>
                                    <th style="width: 15%;">RUANG</th>
                                    <th style="width: 12%;">RPS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jadwalKuliah as $j)
                                    <tr>
                                        <td class="text-center">
                                            <span class="fw-bold text-dark d-block uppercase">{{ $j->hari }}</span>
                                            <span class="text-muted font-monospace" style="font-size: 11px;">
                                                {{ \Carbon\Carbon::parse($j->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($j->jam_selesai)->format('H:i') }} WIT
                                            </span>
                                        </td>
                                        <td class="ps-3">
                                            <span class="fw-bold text-dark uppercase d-block">{{ $j->mataKuliah->nama_mk }} ({{ $j->mataKuliah->sks }} SKS)</span>
                                            <span class="text-muted uppercase" style="font-size: 11px;">{{ optional($j->mataKuliah->dosen)->nama_lengkap ?? '-' }}</span>
                                        </td>
                                        <td class="text-center font-monospace fw-bold text-primary">{{ $j->ruang ?? 'TBA' }}</td>
                                        <td class="text-center">
                                            @if($j->mataKuliah->file_rps)
                                                <a href="{{ asset('storage/' . $j->mataKuliah->file_rps) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-0 py-0 px-2 font-monospace" style="font-size: 10px;">
                                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                                </a>
                                            @else
                                                <span class="text-muted font-monospace">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-5 text-muted small uppercase fw-bold">Belum ada jadwal kuliah yang terdaftar pada semester aktif.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            {{-- AKSES LAYANAN DINAMIS --}}
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4">
                <div class="card-header bg-dark text-white py-3 rounded-0">
                    <h6 class="fw-bold mb-0 uppercase small">Layanan Akademik</h6>
                </div>
                <div class="list-group list-group-flush">
                    @if($mahasiswa->status_mahasiswa === 'Lulus')
                        <a href="{{ route('tracer.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center bg-light">
                            <div>
                                <span class="fw-bold small text-primary d-block uppercase"><i class="bi bi-briefcase-fill me-2"></i>TRACER STUDY / KARIR ALUMNI</span>
                                <small class="text-muted font-monospace" style="font-size: 10px;">{{ $mahasiswa->sudah_isi_tracer ? 'DATA KARIR SUDAH TERISI' : 'WAJIB DIISI SEBELUM UNDUH TRANSKRIP' }}</small>
                            </div>
                            <i class="bi bi-chevron-right text-primary"></i>
                        </a>
                        <a href="{{ route('transkrip.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small uppercase">Transkrip Nilai Akhir</span>
                            <i class="bi bi-download"></i>
                        </a>
                        <a href="{{ route('pembayaran.riwayat') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small uppercase">Riwayat Pembayaran</span>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    @else
                        @php
                            $isKrsLocked = ($mahasiswa->status_krs === 'Disetujui' && ($krsSemesterIni ?? false)) || 
                                           (($krsSemesterIni ?? false) && $mahasiswa->status_krs === 'Belum Mengajukan');
                        @endphp

                        @if($isKrsLocked)
                            <a href="{{ route('krs.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center bg-light">
                                <div>
                                    <span class="fw-bold small d-block text-dark uppercase">Kartu Rencana Studi (KRS)</span>
                                    <span class="badge bg-success rounded-0 mt-1 font-monospace" style="font-size: 0.65rem;">DISETUJUI & TERKUNCI</span>
                                </div>
                                <i class="bi bi-check-circle-fill text-success"></i>
                            </a>
                        @else
                            <a href="{{ route('krs.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                                <span class="fw-bold small uppercase">Kartu Rencana Studi (KRS)</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        @endif
                        
                        <a href="{{ route('khs.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small uppercase">Kartu Hasil Studi (KHS)</span>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                        <a href="{{ route('transkrip.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small uppercase">Transkrip Nilai Sementara</span>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                        <a href="{{ route('evaluasi.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small uppercase">Evaluasi Dosen (EDOM)</span>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    @endif
                </div>
            </div>

            {{-- INFORMASI INSTITUSI --}}
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-dark text-white py-3 rounded-0">
                    <h6 class="fw-bold mb-0 uppercase small">Warta Kampus</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($pengumumans as $p)
                            @if($p->target_role === 'semua')
                                <a href="{{ route('pengumuman.public.show', $p) }}" target="_blank" class="list-group-item list-group-item-action py-3 border-bottom">
                                    <small class="text-muted d-block font-monospace" style="font-size: 10px;">{{ $p->created_at->format('d/m/Y') }}</small>
                                    <span class="fw-bold small text-dark uppercase">{{ Str::limit($p->judul, 55) }}</span>
                                </a>
                            @else
                                <div class="list-group-item py-3 border-bottom">
                                    <small class="text-muted d-block font-monospace" style="font-size: 10px;">{{ $p->created_at->format('d/m/Y') }} | INTERNAL</small>
                                    <span class="fw-bold small text-dark uppercase d-block">{{ $p->judul }}</span>
                                    <p class="small text-muted mb-0 mt-1" style="font-size: 11px;">{{ Str::limit(strip_tags($p->konten), 100) }}</p>
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
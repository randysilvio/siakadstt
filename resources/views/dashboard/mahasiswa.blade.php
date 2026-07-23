@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    
    {{-- PROFIL SINGKAT MAHASISWA --}}
    <div class="card border-0 shadow-sm mb-4 rounded-0 bg-white border-bottom border-primary border-4">
        <div class="card-body p-4 d-flex align-items-center">
            <div class="me-4">
                @if($mahasiswa->foto_profil)
                    <img src="{{ asset('storage/' . $mahasiswa->foto_profil) }}" class="rounded-0 border" style="width: 80px; height: 100px; object-fit: cover;">
                @else
                    <div class="bg-light text-secondary border d-flex align-items-center justify-content-center" style="width: 80px; height: 100px;">
                        <i class="bi bi-person-fill fs-1"></i>
                    </div>
                @endif
            </div>
            <div class="flex-grow-1">
                <h4 class="fw-bold text-dark mb-1 uppercase">{{ $mahasiswa->nama_lengkap }}</h4>
                <p class="text-muted small mb-0 font-monospace">{{ $mahasiswa->nim }} | {{ optional($mahasiswa->programStudi)->nama_prodi ?? '-' }}</p>
                <div class="mt-2">
                    {{-- INDIKATOR STATUS DINAMIS --}}
                    @if($mahasiswa->status_mahasiswa === 'Aktif')
                        <span class="badge bg-success rounded-0 px-2">STATUS: AKTIF</span>
                    @elseif($mahasiswa->status_mahasiswa === 'Lulus')
                        <span class="badge bg-warning text-dark border border-warning rounded-0 px-2 fw-bold"><i class="bi bi-mortarboard-fill me-1"></i> ALUMNI / LULUS</span>
                    @else
                        <span class="badge bg-secondary rounded-0 px-2">STATUS: {{ strtoupper($mahasiswa->status_mahasiswa) }}</span>
                    @endif
                    <span class="badge bg-light text-dark border rounded-0 px-2">AKADEMIK TERVERIFIKASI</span>
                </div>
            </div>
        </div>
    </div>

    {{-- NOTIFIKASI PESAN LULUS ATAU TAGIHAN --}}
    @if($mahasiswa->status_mahasiswa === 'Lulus')
        <div class="alert alert-warning border-0 border-start border-4 border-warning shadow-sm rounded-0 d-flex align-items-center mb-4 bg-white" role="alert">
            <i class="bi bi-trophy-fill fs-4 me-3 text-warning"></i>
            <div>
                <span class="fw-bold d-block uppercase small text-dark">Selamat! Anda Resmi Menjadi Alumni</span>
                <small class="text-muted">Dasbor ini sekarang berfungsi sebagai arsip akademik Anda. Jangan lupa untuk memperbarui data karir Anda melalui Pusat Karir institusi.</small>
            </div>
        </div>
    @elseif($memiliki_tagihan)
        <div class="alert alert-danger border-0 shadow-sm rounded-0 d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-octagon-fill fs-4 me-3"></i>
            <div>
                <span class="fw-bold d-block uppercase small">Administrasi Keuangan Tertunda</span>
                <small>Silakan lakukan pelunasan tagihan melalui menu pembayaran. <a href="{{ route('pembayaran.riwayat') }}" class="fw-bold text-danger">Tinjau Detail</a></small>
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
                            <small class="d-block uppercase opacity-75 mb-1">Indeks Prestasi Kumulatif</small>
                            <h2 class="fw-bold mb-0">{{ number_format($ipk, 2) }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card border-0 bg-primary text-white rounded-0 shadow-sm">
                        <div class="card-body text-center py-4">
                            <small class="d-block uppercase opacity-75 mb-1">SKS Terselesaikan</small>
                            <h2 class="fw-bold mb-0">{{ $total_sks }} SKS</h2>
                        </div>
                    </div>
                </div>
            </div>

            {{-- JADWAL KULIAH ATAU ARSIP LULUSAN --}}
            <div class="card border-0 shadow-sm rounded-0">
                @if($mahasiswa->status_mahasiswa === 'Lulus')
                    <div class="card-header bg-white py-3 border-bottom text-center">
                        <h6 class="fw-bold mb-0 text-dark uppercase small">Arsip Akademik Lulusan</h6>
                    </div>
                    <div class="card-body py-5 text-center">
                        <i class="bi bi-archive text-muted mb-3 d-block" style="font-size: 3rem;"></i>
                        <h5 class="fw-bold text-dark">Data Jadwal Perkuliahan Ditutup</h5>
                        <p class="text-muted small">Anda telah menyelesaikan seluruh proses akademik pada program studi ini.</p>
                    </div>
                @else
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark uppercase small">Jadwal Kuliah Semester Berjalan</h6>
                        <a href="{{ route('jadwal.cetak') }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-0 px-3">CETAK</a>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 small">WAKTU</th>
                                    <th class="small">MATA KULIAH</th>
                                    <th class="small text-center">FILE</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jadwalKuliah as $j)
                                    <tr>
                                        <td class="ps-3">
                                            <span class="fw-bold small d-block">{{ strtoupper($j->hari) }}</span>
                                            <span class="text-muted small font-monospace">{{ \Carbon\Carbon::parse($j->jam_mulai)->format('H:i') }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-bold small text-dark d-block">{{ $j->mataKuliah->nama_mk }}</span>
                                            <span class="text-muted small">{{ optional($j->mataKuliah->dosen)->nama_lengkap ?? '-' }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($j->mataKuliah->file_rps)
                                                <a href="{{ asset('storage/' . $j->mataKuliah->file_rps) }}" target="_blank" class="text-dark"><i class="bi bi-file-pdf"></i></a>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-5 text-muted small uppercase">Belum ada jadwal kuliah yang terdaftar.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            {{-- AKSES LAYANAN DINAMIS --}}
            <div class="card border-0 shadow-sm rounded-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark uppercase small">Layanan Akademik</h6>
                </div>
                <div class="list-group list-group-flush">
                    @if($mahasiswa->status_mahasiswa === 'Lulus')
                        {{-- LAYANAN KHUSUS ALUMNI --}}
                        <a href="{{ route('transkrip.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small">TRANSKRIP NILAI AKHIR</span>
                            <i class="bi bi-download"></i>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center bg-light">
                            <span class="fw-bold small text-primary"><i class="bi bi-briefcase-fill me-2"></i> TRACER STUDY / KARIR</span>
                            <i class="bi bi-chevron-right text-primary"></i>
                        </a>
                    @else
                        {{-- LAYANAN MAHASISWA AKTIF --}}
                        
                        {{-- [PERBAIKAN LOGIKA KRS] --}}
                        @php
                            $periodeAktif = \App\Models\TahunAkademik::where('is_active', true)->first();
                            $krsSemesterIni = false;
                            if($periodeAktif){
                                $krsSemesterIni = $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->exists();
                            }
                            // Kunci jika sudah disetujui DAN punya KRS semester ini
                            $isKrsLocked = ($mahasiswa->status_krs === 'Disetujui' && $krsSemesterIni);
                        @endphp

                        @if($isKrsLocked)
                            <a href="#" onclick="alert('Akses Ditolak: Rencana Studi Anda untuk semester ini sudah divalidasi dan dikunci oleh Dosen Wali. Hubungi Dosen Wali jika ada perubahan.'); return false;" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center bg-light">
                                <div>
                                    <span class="fw-bold small d-block text-muted">KARTU RENCANA STUDI</span>
                                    <span class="badge bg-success rounded-0 mt-1" style="font-size: 0.6rem;">DISETUJUI</span>
                                </div>
                                <i class="bi bi-lock-fill text-muted"></i>
                            </a>
                        @else
                            <a href="{{ route('krs.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                                <span class="fw-bold small">KARTU RENCANA STUDI</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        @endif
                        
                        <a href="{{ route('khs.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small">HASIL STUDI SEMESTER</span>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                        <a href="{{ route('transkrip.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small">TRANSKRIP NILAI</span>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                        <a href="{{ route('evaluasi.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small">EVALUASI DOSEN (EDOM)</span>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    @endif
                </div>
            </div>

            {{-- INFORMASI INSTITUSI --}}
            <div class="card border-0 shadow-sm rounded-0">
                <div class="card-header bg-dark text-white py-3">
                    <h6 class="fw-bold mb-0 uppercase small">Warta Kampus</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($pengumumans as $p)
                            <a href="{{ route('pengumuman.public.show', $p->id) }}" class="list-group-item list-group-item-action py-3">
                                <small class="text-muted d-block font-monospace">{{ $p->created_at->format('d/m/Y') }}</small>
                                <span class="fw-bold small text-dark">{{ Str::limit($p->judul, 50) }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
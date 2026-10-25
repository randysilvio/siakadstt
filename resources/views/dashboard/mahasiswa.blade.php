@extends('layouts.app')

@section('content')
@php
    $cfg = \App\Models\Pengaturan::allCached();
    $logoUrl = \App\Models\Pengaturan::logoUrl();
    $tokenKtm = \App\Http\Controllers\VerifikasiDokumenController::buatTokenDokumen('AKTIF', (int) $mahasiswa->id);
@endphp
<div class="container-fluid px-3 px-md-4">
    
    {{-- PROFIL SINGKAT & TOMBOL E-KTM DIGITAL --}}
    <div class="card border-0 shadow-sm mb-4 bg-white border-top border-dark border-4">
        <div class="card-body p-3 p-md-4 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
            <div class="d-flex flex-column flex-sm-row align-items-center text-center text-sm-start gap-3">
                <div>
                    @if($mahasiswa->foto_profil)
                        <img src="{{ asset('storage/' . $mahasiswa->foto_profil) }}" class="border border-dark" style="width: 72px; height: 90px; object-fit: cover;">
                    @else
                        <div class="bg-light text-secondary border border-dark d-flex align-items-center justify-content-center mx-auto" style="width: 72px; height: 90px;">
                            <span class="fw-bold fs-3 font-monospace">{{ substr($mahasiswa->nama_lengkap, 0, 1) }}</span>
                        </div>
                    @endif
                </div>
                <div>
                    <h4 class="fw-bold text-dark mb-1 uppercase">{{ $mahasiswa->nama_lengkap }}</h4>
                    <p class="text-muted small mb-2 font-monospace uppercase">
                        NIM: {{ $mahasiswa->nim }} | {{ optional($mahasiswa->programStudi)->nama_prodi ?? '-' }} ({{ $mahasiswa->tahun_masuk }})
                    </p>
                    <div class="d-flex flex-wrap justify-content-center justify-content-sm-start gap-1">
                        <span class="badge bg-dark text-white px-2 py-1 font-monospace">STATUS: {{ strtoupper($mahasiswa->status_mahasiswa) }}</span>
                        <span class="badge bg-light text-dark border px-2 py-1 font-monospace">{{ strtoupper($cfg['istilah_pembimbing'] ?? 'Dosen Tutor') }}: {{ strtoupper(optional($mahasiswa->dosenWali)->nama_lengkap ?? '-') }}</span>
                    </div>
                </div>
            </div>
            <div class="w-100 w-md-auto text-center text-md-end">
                <button type="button" class="btn btn-sm btn-dark px-3 py-2 uppercase fw-bold small w-100 w-md-auto shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEktm">
                    Kartu Mahasiswa (E-KTM)
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border shadow-sm mb-4 p-3 uppercase small fw-bold">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border shadow-sm mb-4 p-3 uppercase small fw-bold">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($mahasiswa->status_mahasiswa === 'Aktif')
        @php
            $periodeAktif = \App\Models\TahunAkademik::where('is_active', true)->first();
            $krsSemesterIni = $periodeAktif ? $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->exists() : false;
        @endphp
        <div class="row g-2 g-md-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card wm-card-exclusive border-0 shadow-sm bg-dark text-white h-100">
                    <div class="card-body p-3">
                        <span class="text-white-50 small font-monospace uppercase fw-bold d-block" style="font-size: 10px;">INDEKS PRESTASI (IPK)</span>
                        <span class="fw-bold font-monospace fs-4 text-white">{{ number_format($ipk, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm border-top border-dark border-4 bg-white h-100">
                    <div class="card-body p-3">
                        <span class="text-muted small font-monospace uppercase fw-bold d-block" style="font-size: 10px;">TOTAL SKS LULUS</span>
                        <span class="fw-bold font-monospace fs-4 text-dark">{{ $total_sks }} <small class="fs-6">SKS</small></span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm border-top border-dark border-4 bg-white h-100">
                    <div class="card-body p-3">
                        <span class="text-muted small font-monospace uppercase fw-bold d-block" style="font-size: 10px;">STATUS KRS</span>
                        <span class="fw-bold font-monospace small uppercase text-dark d-block mt-1">
                            {{ $krsSemesterIni ? strtoupper($mahasiswa->status_krs) : 'BELUM ISI' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm border-top border-dark border-4 bg-white h-100">
                    <div class="card-body p-3">
                        <span class="text-muted small font-monospace uppercase fw-bold d-block" style="font-size: 10px;">KEUANGAN</span>
                        <span class="fw-bold font-monospace small uppercase d-block mt-1 {{ !$memiliki_tagihan ? 'text-dark' : 'text-danger' }}">
                            {{ !$memiliki_tagihan ? 'LUNAS / AMAN' : 'ADA TAGIHAN' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($mahasiswa->status_mahasiswa === 'Lulus')
        <div class="alert bg-white border border-dark d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 p-3 gap-3">
            <div>
                <span class="fw-bold d-block uppercase small text-dark">Status Akademik: Lulus / Alumni</span>
                <small class="text-muted">Lengkapi data Tracer Study untuk membuka unduhan Transkrip Nilai Akhir.</small>
            </div>
            <a href="{{ route('tracer.index') }}" class="btn btn-sm btn-dark px-4 py-2 uppercase fw-bold small text-nowrap">
                Tracer Study Alumni
            </a>
        </div>
    @elseif($memiliki_tagihan)
        <div class="alert alert-danger border-0 border-start border-4 border-danger shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4 p-3">
            <div>
                <span class="fw-bold d-block uppercase small">Tagihan Keuangan Belum Lunas</span>
                <small>Silakan lihat nomor rekening resmi dan unggah bukti transfer pada menu Riwayat Pembayaran.</small>
            </div>
            <a href="{{ route('pembayaran.riwayat') }}" class="btn btn-sm btn-danger uppercase fw-bold small">Bayar Sekarang</a>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm border-top border-dark border-4">
                @if($mahasiswa->status_mahasiswa === 'Lulus')
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="fw-bold mb-0 text-dark uppercase small">Arsip Akademik</h6>
                    </div>
                    <div class="card-body py-5 text-center">
                        <h6 class="fw-bold text-dark uppercase">Masa Perkuliahan Selesai</h6>
                        <p class="text-muted small mb-3">Seluruh riwayat nilai dapat dilihat pada halaman Transkrip Nilai.</p>
                        <a href="{{ route('transkrip.index') }}" class="btn btn-sm btn-dark px-4 py-2 uppercase fw-bold small">
                            Buka Transkrip Nilai
                        </a>
                    </div>
                @else
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark uppercase small">Jadwal Kuliah Semester Ini</h6>
                        <a href="{{ route('jadwal.cetak') }}" target="_blank" class="btn btn-sm btn-outline-dark px-3 fw-bold uppercase small">
                            Cetak PDF
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0 small">
                                <thead class="bg-light text-dark uppercase text-center fw-bold">
                                    <tr>
                                        <th style="width: 22%;">HARI & WAKTU</th>
                                        <th class="text-start">MATA KULIAH & DOSEN</th>
                                        <th style="width: 10%;">KELAS</th>
                                        <th style="width: 14%;">RUANG</th>
                                        <th style="width: 12%;">RPS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($jadwalKuliah as $j)
                                        <tr>
                                            <td class="text-center">
                                                <span class="fw-bold text-dark d-block uppercase">{{ $j->hari }}</span>
                                                <span class="text-muted font-monospace" style="font-size: 11px;">
                                                    {{ \Carbon\Carbon::parse($j->jam_mulai)->format('H:i') }}-{{ \Carbon\Carbon::parse($j->jam_selesai)->format('H:i') }}
                                                </span>
                                            </td>
                                            <td class="ps-3">
                                                <span class="fw-bold text-dark uppercase d-block">{{ $j->mataKuliah->nama_mk }} ({{ $j->mataKuliah->sks }} SKS)</span>
                                                <span class="text-muted uppercase" style="font-size: 11px;">{{ optional($j->mataKuliah->dosen)->nama_lengkap ?? '-' }}</span>
                                            </td>
                                            <td class="text-center font-monospace fw-bold">{{ $j->nama_kelas ?? 'A' }}</td>
                                            <td class="text-center font-monospace">{{ $j->ruang ?? '-' }}</td>
                                            <td class="text-center">
                                                @if($j->mataKuliah->file_rps)
                                                    <a href="{{ asset('storage/' . $j->mataKuliah->file_rps) }}" target="_blank" class="btn btn-sm btn-outline-dark py-0 px-2 font-monospace" style="font-size: 10px;">PDF</a>
                                                @else
                                                    <span class="text-muted font-monospace">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center py-5 text-muted small uppercase fw-bold">Belum ada jadwal kuliah pada semester ini.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm border-top border-dark border-4 mb-4">
                <div class="card-header bg-dark text-white py-3">
                    <h6 class="fw-bold mb-0 uppercase small">Menu Akademik</h6>
                </div>
                <div class="list-group list-group-flush">
                    @if($mahasiswa->status_mahasiswa === 'Lulus')
                        <a href="{{ route('tracer.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-dark uppercase">Tracer Study Alumni</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="{{ route('transkrip.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-dark uppercase">Transkrip Nilai Akhir</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="{{ route('pembayaran.riwayat') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-dark uppercase">Riwayat Pembayaran</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                    @else
                        <a href="{{ route('krs.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-dark uppercase">Kartu Rencana Studi (KRS)</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="{{ route('khs.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-dark uppercase">Kartu Hasil Studi (KHS)</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="{{ route('transkrip.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-dark uppercase">Transkrip Nilai</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="{{ route('bimbingan.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-dark uppercase">Bimbingan Tugas Akhir & PPL</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="{{ route('surat-mandiri.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-dark uppercase">Permohonan Surat</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        <a href="{{ route('evaluasi.index') }}" class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-dark uppercase">Evaluasi Dosen</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm border-top border-dark border-4">
                <div class="card-header bg-dark text-white py-3">
                    <h6 class="fw-bold mb-0 uppercase small">Pengumuman</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($pengumumans as $p)
                            <div class="list-group-item py-3 border-bottom">
                                <small class="text-muted d-block font-monospace" style="font-size: 10px;">{{ $p->created_at->format('d/m/Y') }}</small>
                                <span class="fw-bold small text-dark uppercase d-block">{{ $p->judul }}</span>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted small uppercase fw-bold">Belum ada pengumuman.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL E-KTM (KARTU TANDA MAHASISWA DIGITAL BER-WATERMARK) --}}
<div class="modal fade" id="modalEktm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2.5 px-3">
                <span class="fw-bold uppercase small font-monospace">Kartu Tanda Mahasiswa Digital (E-KTM)</span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 p-sm-4 bg-light">
                {{-- Desain Fisik Kartu E-KTM --}}
                <div class="wm-card-exclusive bg-dark text-white p-4 shadow" style="border-radius: 14px; min-height: 235px; border: 1px solid rgba(255,255,255,0.15);">
                    <div class="d-flex align-items-center justify-content-between border-bottom border-secondary pb-2 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $logoUrl }}" alt="Logo" class="bg-white p-1 rounded" style="width: 34px; height: 34px; object-fit: contain;">
                            <div>
                                <div class="fw-bold uppercase" style="font-size: 12px; letter-spacing: 0.5px;">{{ $cfg['singkatan_kampus'] ?? 'STT GPI PAPUA' }}</div>
                                <div class="text-white-50 uppercase" style="font-size: 9px;">Kartu Identitas Mahasiswa Resmi</div>
                            </div>
                        </div>
                        <span class="badge bg-light text-dark font-monospace" style="font-size: 9px;">{{ strtoupper($mahasiswa->status_mahasiswa) }}</span>
                    </div>

                    <div class="d-flex align-items-center gap-3 my-2">
                        @if($mahasiswa->foto_profil)
                            <img src="{{ asset('storage/' . $mahasiswa->foto_profil) }}" alt="Foto" class="border border-light rounded" style="width: 68px; height: 86px; object-fit: cover; flex-shrink: 0;">
                        @else
                            <div class="bg-secondary text-white border border-light rounded d-flex align-items-center justify-content-center" style="width: 68px; height: 86px; flex-shrink: 0;">
                                <span class="fw-bold fs-3 font-monospace">{{ substr($mahasiswa->nama_lengkap, 0, 1) }}</span>
                            </div>
                        @endif
                        <div class="overflow-hidden">
                            <div class="fw-bold uppercase text-truncate" style="font-size: 14px;">{{ $mahasiswa->nama_lengkap }}</div>
                            <div class="font-monospace text-white-50 mb-1" style="font-size: 12px;">NIM: <strong class="text-white">{{ $mahasiswa->nim }}</strong></div>
                            <div class="uppercase text-white-50" style="font-size: 10px;">PRODI: <strong class="text-white">{{ optional($mahasiswa->programStudi)->nama_prodi ?? '-' }}</strong></div>
                            <div class="uppercase text-white-50" style="font-size: 10px;">ANGKATAN: <strong class="text-white">{{ $mahasiswa->tahun_masuk }}</strong></div>
                        </div>
                    </div>

                    <div class="border-top border-secondary pt-2 mt-3 d-flex justify-content-between align-items-center" style="font-size: 9px;">
                        <span class="font-monospace text-white-50">ID VALIDASI: {{ $tokenKtm }}</span>
                        <span class="font-monospace text-white fw-bold">{{ $cfg['kota_domisili'] ?? 'FAKFAK' }}</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white py-2 px-3 d-flex justify-content-between">
                <a href="{{ route('verifikasi.dokumen', $tokenKtm) }}" target="_blank" class="btn btn-sm btn-outline-dark uppercase fw-bold small">
                    Halaman Verifikasi
                </a>
                <button type="button" class="btn btn-sm btn-dark uppercase fw-bold small" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- HEADER PROFIL CAMABA --}}
    <div class="card border-0 shadow-sm mb-4 rounded-0 bg-dark text-white border-top border-secondary border-4">
        <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <span class="badge bg-warning text-dark rounded-0 font-monospace uppercase mb-2 fw-bold">PORTAL CALON MAHASISWA BARU (PMB)</span>
                <h3 class="fw-bold mb-1 uppercase">{{ strtoupper(Auth::user()->name) }}</h3>
                <p class="mb-0 text-white-50 font-monospace small uppercase">
                    NO. PENDAFTARAN: <strong class="text-white">{{ $camaba->no_pendaftaran ?? '-' }}</strong> |
                    GELOMBANG: <strong class="text-white">{{ strtoupper(optional($camaba->period)->nama_gelombang ?? '-') }}</strong> |
                    PRODI PILIHAN 1: <strong class="text-white">{{ strtoupper(optional($camaba->prodi1)->nama_prodi ?? '-') }}</strong>
                </p>
            </div>
            <div class="text-md-end">
                <span class="text-white-50 small font-monospace uppercase d-block mb-1">STATUS SELEKSI SAAT INI</span>
                @php $stCamaba = $camaba->status_pendaftaran ?? 'draft'; @endphp
                @if($stCamaba == 'lulus')
                    <span class="badge bg-success rounded-0 px-3 py-2 uppercase font-monospace fs-6">LULUS SELEKSI PMB</span>
                @elseif($stCamaba == 'tidak_lulus')
                    <span class="badge bg-danger rounded-0 px-3 py-2 uppercase font-monospace fs-6">TIDAK LULUS SELEKSI</span>
                @elseif($stCamaba == 'menunggu_verifikasi')
                    <span class="badge bg-info text-dark rounded-0 px-3 py-2 uppercase font-monospace fs-6">TERVERIFIKASI / SIAP UJIAN</span>
                @else
                    <span class="badge bg-secondary rounded-0 px-3 py-2 uppercase font-monospace fs-6">TAHAP PEMBERKASAN</span>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php
        $step1Done = true;
        $step2Done = $tagihan && $tagihan->status === 'lunas';
        $step2Pending = $tagihan && $tagihan->status === 'menunggu_konfirmasi';
        $step3Done = $step2Done && !empty($camaba->tempat_lahir) && !empty($camaba->sekolah_asal);
        $step4Done = $step3Done;
    @endphp

    {{-- HORIZONTAL STEPPER BAR 4 TAHAP PMB --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-white">
        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
            <i class="bi bi-signpost-split-fill me-2"></i>Alur Tahapan Seleksi Penerimaan Mahasiswa Baru (PMB)
        </div>
        <div class="card-body p-3">
            <div class="row g-3 text-center">
                <div class="col-md-3 col-6">
                    <div class="p-3 border rounded-0 h-100 border-success bg-success bg-opacity-10">
                        <span class="badge bg-success rounded-0 font-monospace mb-1">TAHAP 1</span>
                        <div class="fw-bold small uppercase text-dark">Registrasi Akun</div>
                        <small class="text-success font-monospace" style="font-size: 10px;"><i class="bi bi-check-circle-fill"></i> SELESAI</small>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <a href="{{ route('pmb.pembayaran.show') }}" class="text-decoration-none d-block p-3 border rounded-0 h-100 {{ $step2Done ? 'border-success bg-success bg-opacity-10' : 'border-warning bg-warning bg-opacity-10' }}">
                        <span class="badge {{ $step2Done ? 'bg-success' : 'bg-warning text-dark' }} rounded-0 font-monospace mb-1">TAHAP 2</span>
                        <div class="fw-bold small uppercase text-dark">Pembayaran Formulir</div>
                        <small class="font-monospace {{ $step2Done ? 'text-success' : 'text-dark' }}" style="font-size: 10px;">
                            {{ $step2Done ? 'LUNAS TERVERIFIKASI' : ($step2Pending ? 'MENUNGGU VERIFIKASI' : 'BELUM LUNAS') }}
                        </small>
                    </a>
                </div>
                <div class="col-md-3 col-6">
                    <a href="{{ $step2Done ? route('pmb.biodata.show') : '#' }}" class="text-decoration-none d-block p-3 border rounded-0 h-100 {{ $step3Done ? 'border-success bg-success bg-opacity-10' : ($step2Done ? 'border-primary bg-primary bg-opacity-10' : 'bg-light text-muted') }}">
                        <span class="badge {{ $step3Done ? 'bg-success' : ($step2Done ? 'bg-primary' : 'bg-secondary') }} rounded-0 font-monospace mb-1">TAHAP 3</span>
                        <div class="fw-bold small uppercase text-dark">Biodata & Dokumen</div>
                        <small class="font-monospace" style="font-size: 10px;">
                            {{ $step3Done ? 'BIODATA LENGKAP' : ($step2Done ? 'KLIK UNTUK MENGISI' : 'TUNGGU PEMBAYARAN') }}
                        </small>
                    </a>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-3 border rounded-0 h-100 {{ $step4Done ? 'border-dark bg-white' : 'bg-light text-muted' }}">
                        <span class="badge {{ $step4Done ? 'bg-dark' : 'bg-secondary' }} rounded-0 font-monospace mb-1">TAHAP 4</span>
                        <div class="fw-bold small uppercase text-dark">Kartu Ujian & Seleksi</div>
                        <small class="font-monospace" style="font-size: 10px;">
                            {{ $step4Done ? 'SIAP CETAK KARTU' : 'TERKUNCI' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- PANEL AKSI UTAMA SESUAI TAHAPAN --}}
    <div class="row g-4 mb-5">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-white">
                <div class="card-header bg-dark text-white py-3 rounded-0 uppercase fw-bold small">
                    Instruksi & Tindakan Selanjutnya
                </div>
                <div class="card-body p-4">
                    @if(!$step2Done)
                        <h5 class="fw-bold text-dark uppercase mb-2">Langkah Saat Ini: Pelunasan Biaya Formulir PMB</h5>
                        <p class="small text-muted mb-3">
                            Silakan selesaikan pembayaran biaya pendaftaran sebesar <strong class="text-dark font-monospace">Rp {{ number_format($tagihan->jumlah ?? 250000, 0, ',', '.') }}</strong> dan unggah bukti pembayaran Anda.
                        </p>
                        @if($step2Pending)
                            <div class="alert alert-warning rounded-0 small uppercase fw-bold font-monospace">
                                <i class="bi bi-hourglass-split me-1"></i> Bukti pembayaran Anda sedang dalam antrean verifikasi Panitia PMB / Keuangan.
                            </div>
                        @endif
                        <a href="{{ route('pmb.pembayaran.show') }}" class="btn btn-primary rounded-0 px-4 py-2 uppercase fw-bold small shadow-sm">
                            <i class="bi bi-upload me-1"></i> {{ $step2Pending ? 'Lihat / Perbarui Bukti Pembayaran' : 'Unggah Bukti Pembayaran Sekarang' }}
                        </a>
                    @elseif(!$step3Done)
                        <h5 class="fw-bold text-dark uppercase mb-2">Langkah Saat Ini: Lengkapi Biodata & Unggah Berkas</h5>
                        <p class="small text-muted mb-3">
                            Pembayaran Anda telah diverifikasi <strong>LUNAS</strong>. Silakan lengkapi formulir biodata diri, asal sekolah, pilihan program studi, serta unggah berkas Ijazah, KK, dan Pas Foto resmi.
                        </p>
                        <a href="{{ route('pmb.biodata.show') }}" class="btn btn-primary rounded-0 px-4 py-2 uppercase fw-bold small shadow-sm">
                            <i class="bi bi-person-lines-fill me-1"></i> Isi Formulir Biodata & Berkas
                        </a>
                    @else
                        <h5 class="fw-bold text-success uppercase mb-2"><i class="bi bi-check-circle-fill me-1"></i> Pemberkasan Selesai — Siap Mengikuti Ujian Seleksi</h5>
                        <p class="small text-muted mb-4">
                            Seluruh tahapan pemberkasan awal Anda telah lengkap. Silakan unduh dan cetak <strong>Kartu Tanda Peserta Ujian Seleksi PMB</strong> di bawah ini untuk dibawa saat pelaksanaan ujian.
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('pmb.kartu-ujian.cetak') }}" target="_blank" class="btn btn-dark rounded-0 px-4 py-2 uppercase fw-bold small shadow-sm">
                                <i class="bi bi-printer-fill me-1"></i> Cetak Kartu Peserta Ujian (PDF)
                            </a>
                            @if(!in_array($stCamaba, ['lulus', 'tidak_lulus']))
                                <a href="{{ route('pmb.biodata.show') }}" class="btn btn-outline-dark rounded-0 px-3 py-2 uppercase fw-bold small">
                                    <i class="bi bi-pencil-square me-1"></i> Perbarui Biodata
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                    Informasi Jadwal Ujian Gelombang Ini
                </div>
                <div class="card-body p-4 small">
                    <table class="table table-borderless uppercase mb-0">
                        <tr>
                            <td class="fw-bold p-1" style="width: 140px;">GELOMBANG</td>
                            <td class="p-1 font-monospace fw-bold text-dark">: {{ optional($camaba->period)->nama_gelombang ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold p-1">TANGGAL UJIAN</td>
                            <td class="p-1 font-monospace text-primary fw-bold">: {{ optional($camaba->period)->tanggal_ujian ? \Carbon\Carbon::parse($camaba->period->tanggal_ujian)->translatedFormat('d F Y') : 'MENUNGGU JADWAL' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold p-1">WAKTU UJIAN</td>
                            <td class="p-1 font-monospace">: {{ optional($camaba->period)->jam_mulai_ujian ? substr($camaba->period->jam_mulai_ujian, 0, 5) . ' - ' . substr($camaba->period->jam_selesai_ujian, 0, 5) . ' WIT' : '08:00 WIT' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold p-1">METODE</td>
                            <td class="p-1 font-monospace">: {{ strtoupper(optional($camaba->period)->jenis_ujian ?? 'OFFLINE') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold p-1">LOKASI UJIAN</td>
                            <td class="p-1">: {{ optional($camaba->period)->lokasi_ujian ?? 'KAMPUS STT GPI PAPUA FAKFAK' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
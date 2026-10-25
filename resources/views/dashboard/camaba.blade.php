@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4">
    {{-- 1. HEADER PROFIL CAMABA BER-WATERMARK EKSKLUSIF --}}
    <div class="card wm-card-exclusive border-0 shadow-sm mb-4 bg-dark text-white">
        <div class="card-body p-3 p-md-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <span class="badge bg-light text-dark font-monospace uppercase mb-2 fw-bold">Portal Calon Mahasiswa Baru (PMB)</span>
                <h3 class="fw-bold mb-1 uppercase text-white">{{ strtoupper(Auth::user()->name) }}</h3>
                <p class="mb-0 text-white-50 font-monospace small uppercase">
                    NO. PENDAFTARAN: <strong class="text-white">{{ $camaba->no_pendaftaran ?? '-' }}</strong> |
                    GELOMBANG: <strong class="text-white">{{ strtoupper(optional($camaba->period)->nama_gelombang ?? '-') }}</strong> |
                    PRODI PILIHAN: <strong class="text-white">{{ strtoupper(optional($camaba->prodi1)->nama_prodi ?? '-') }}</strong>
                </p>
            </div>
            <div class="text-md-end">
                <span class="text-white-50 small font-monospace uppercase d-block mb-1" style="font-size: 10px;">STATUS SELEKSI SAAT INI</span>
                @php $stCamaba = $camaba->status_pendaftaran ?? 'draft'; @endphp
                @if($stCamaba == 'lulus')
                    <span class="badge bg-light text-dark px-3 py-2 uppercase font-monospace fw-bold">LULUS SELEKSI PMB</span>
                @elseif($stCamaba == 'tidak_lulus')
                    <span class="badge bg-danger text-white px-3 py-2 uppercase font-monospace fw-bold">TIDAK LULUS SELEKSI</span>
                @elseif($stCamaba == 'menunggu_verifikasi')
                    <span class="badge bg-light text-dark px-3 py-2 uppercase font-monospace fw-bold">TERVERIFIKASI / SIAP UJIAN</span>
                @else
                    <span class="badge bg-secondary text-white px-3 py-2 uppercase font-monospace fw-bold">TAHAP PEMBERKASAN</span>
                @endif
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

    @php
        $step1Done = true;
        $step2Done = $tagihan && $tagihan->status === 'lunas';
        $step2Pending = $tagihan && $tagihan->status === 'menunggu_konfirmasi';
        $step3Done = $step2Done && !empty($camaba->tempat_lahir) && !empty($camaba->sekolah_asal);
        $step4Done = $step3Done;
    @endphp

    {{-- 2. STEPPER BAR 4 TAHAP PMB (RESPONSIF 2x2 DI LAYAR HP) --}}
    <div class="card border-0 shadow-sm border-top border-dark border-4 mb-4 bg-white">
        <div class="card-header bg-white py-3 border-bottom uppercase fw-bold small text-dark">
            Alur Tahapan Seleksi Penerimaan Mahasiswa Baru (PMB)
        </div>
        <div class="card-body p-3">
            <div class="row g-2 g-md-3 text-center">
                <div class="col-6 col-md-3">
                    <div class="p-3 border border-dark h-100 bg-light">
                        <span class="badge bg-dark font-monospace mb-1">TAHAP 1</span>
                        <div class="fw-bold small uppercase text-dark">Registrasi Akun</div>
                        <small class="text-dark font-monospace fw-bold" style="font-size: 10px;">SELESAI</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ route('pmb.pembayaran.show') }}" class="text-decoration-none d-block p-3 border h-100 hover-lift {{ $step2Done ? 'border-dark bg-light' : 'border-dark bg-white' }}">
                        <span class="badge {{ $step2Done ? 'bg-dark' : 'bg-secondary' }} font-monospace mb-1">TAHAP 2</span>
                        <div class="fw-bold small uppercase text-dark">Pembayaran Formulir</div>
                        <small class="font-monospace text-dark fw-bold" style="font-size: 10px;">
                            {{ $step2Done ? 'LUNAS TERVERIFIKASI' : ($step2Pending ? 'MENUNGGU VERIFIKASI' : 'KLIK UNTUK BAYAR') }}
                        </small>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ $step2Done ? route('pmb.biodata.show') : '#' }}" class="text-decoration-none d-block p-3 border h-100 {{ $step2Done ? 'hover-lift border-dark bg-white' : 'bg-light text-muted' }}">
                        <span class="badge {{ $step3Done ? 'bg-dark' : 'bg-secondary' }} font-monospace mb-1">TAHAP 3</span>
                        <div class="fw-bold small uppercase text-dark">Biodata & Dokumen</div>
                        <small class="font-monospace text-dark" style="font-size: 10px;">
                            {{ $step3Done ? 'BIODATA LENGKAP' : ($step2Done ? 'KLIK UNTUK MENGISI' : 'TUNGGU PEMBAYARAN') }}
                        </small>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 border h-100 {{ $step4Done ? 'border-dark bg-white' : 'bg-light text-muted' }}">
                        <span class="badge {{ $step4Done ? 'bg-dark' : 'bg-secondary' }} font-monospace mb-1">TAHAP 4</span>
                        <div class="fw-bold small uppercase text-dark">Kartu Ujian & Seleksi</div>
                        <small class="font-monospace text-dark" style="font-size: 10px;">
                            {{ $step4Done ? 'SIAP CETAK KARTU' : 'TERKUNCI' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. PANEL INSTRUKSI TINDAKAN & JADWAL UJIAN --}}
    <div class="row g-4 mb-5">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-header bg-dark text-white py-3 uppercase fw-bold small">
                    Instruksi & Tindakan Selanjutnya
                </div>
                <div class="card-body p-4">
                    @if(!$step2Done)
                        <h5 class="fw-bold text-dark uppercase mb-2">Langkah Saat Ini: Pelunasan Biaya Formulir PMB</h5>
                        <p class="small text-muted mb-3">
                            Silakan selesaikan pembayaran biaya pendaftaran sebesar <strong class="text-dark font-monospace">Rp {{ number_format($tagihan->jumlah ?? 250000, 0, ',', '.') }}</strong> ke rekening resmi kampus dan unggah bukti pembayaran Anda.
                        </p>
                        @if($step2Pending)
                            <div class="alert bg-light border border-dark small uppercase fw-bold font-monospace text-dark">
                                Bukti pembayaran Anda sedang dalam antrean verifikasi Panitia PMB / Keuangan.
                            </div>
                        @endif
                        <a href="{{ route('pmb.pembayaran.show') }}" class="btn btn-dark px-4 py-2 uppercase fw-bold small shadow-sm">
                            {{ $step2Pending ? 'Lihat / Perbarui Bukti Pembayaran' : 'Unggah Bukti Pembayaran Sekarang' }}
                        </a>
                    @elseif(!$step3Done)
                        <h5 class="fw-bold text-dark uppercase mb-2">Langkah Saat Ini: Lengkapi Biodata & Unggah Berkas</h5>
                        <p class="small text-muted mb-3">
                            Pembayaran Anda telah diverifikasi <strong>LUNAS</strong>. Silakan lengkapi formulir biodata diri, asal sekolah, pilihan program studi, serta unggah berkas Ijazah, KK, dan Pas Foto resmi.
                        </p>
                        <a href="{{ route('pmb.biodata.show') }}" class="btn btn-dark px-4 py-2 uppercase fw-bold small shadow-sm">
                            Isi Formulir Biodata & Berkas
                        </a>
                    @else
                        <h5 class="fw-bold text-dark uppercase mb-2">Pemberkasan Selesai — Siap Mengikuti Ujian Seleksi</h5>
                        <p class="small text-muted mb-4">
                            Seluruh tahapan pemberkasan awal Anda telah lengkap. Silakan unduh dan cetak <strong>Kartu Tanda Peserta Ujian Seleksi PMB</strong> di bawah ini untuk dibawa saat pelaksanaan ujian.
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('pmb.kartu-ujian.cetak') }}" target="_blank" class="btn btn-dark px-4 py-2 uppercase fw-bold small shadow-sm">
                                Cetak Kartu Peserta Ujian (PDF)
                            </a>
                            @if(!in_array($stCamaba, ['lulus', 'tidak_lulus']))
                                <a href="{{ route('pmb.biodata.show') }}" class="btn btn-outline-dark px-3 py-2 uppercase fw-bold small">
                                    Perbarui Biodata
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-header bg-white py-3 border-bottom uppercase fw-bold small text-dark">
                    Informasi Jadwal Ujian Gelombang Ini
                </div>
                <div class="card-body p-4 small">
                    <table class="table table-borderless uppercase mb-0">
                        <tr>
                            <td class="fw-bold p-1 text-muted" style="width: 140px;">GELOMBANG</td>
                            <td class="p-1 font-monospace fw-bold text-dark">: {{ optional($camaba->period)->nama_gelombang ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold p-1 text-muted">TANGGAL UJIAN</td>
                            <td class="p-1 font-monospace text-dark fw-bold">: {{ optional($camaba->period)->tanggal_ujian ? \Carbon\Carbon::parse($camaba->period->tanggal_ujian)->translatedFormat('d F Y') : 'MENUNGGU JADWAL' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold p-1 text-muted">WAKTU UJIAN</td>
                            <td class="p-1 font-monospace text-dark">: {{ optional($camaba->period)->jam_mulai_ujian ? substr($camaba->period->jam_mulai_ujian, 0, 5) . ' - ' . substr($camaba->period->jam_selesai_ujian, 0, 5) . ' WIT' : '08:00 WIT' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold p-1 text-muted">METODE</td>
                            <td class="p-1 font-monospace text-dark">: {{ strtoupper(optional($camaba->period)->jenis_ujian ?? 'OFFLINE') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold p-1 text-muted">LOKASI UJIAN</td>
                            <td class="p-1 text-dark fw-bold">: {{ optional($camaba->period)->lokasi_ujian ?? (\App\Models\Pengaturan::get('singkatan_kampus', 'KAMPUS')) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
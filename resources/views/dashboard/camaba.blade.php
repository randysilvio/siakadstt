@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            
            {{-- Header --}}
            <div class="text-center mb-5">
                <h2 class="fw-bold text-dark">Portal Penerimaan Mahasiswa Baru</h2>
                <p class="text-muted">Halo, <strong>{{ Auth::user()->name }}</strong>! Selamat datang di STT GPI Papua.</p>
            </div>

            {{-- Status Card --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-light p-3 rounded-circle">
                            <i class="bi bi-person-lines-fill fs-2 text-primary"></i>
                        </div>
                        <div class="ms-4">
                            <h5 class="mb-1 fw-bold">Nomor Pendaftaran: {{ $camaba->no_pendaftaran }}</h5>
                            <p class="mb-0 text-secondary">Gelombang: {{ $camaba->period->nama_gelombang ?? '-' }}</p>
                        </div>
                        <div class="ms-auto">
                            @if($camaba->status_pendaftaran == 'lulus')
                                <span class="badge bg-success fs-6 px-3 py-2">LULUS SELEKSI</span>
                            @elseif($camaba->status_pendaftaran == 'tidak_lulus')
                                <span class="badge bg-danger fs-6 px-3 py-2">TIDAK LULUS</span>
                            @elseif($camaba->status_pendaftaran == 'menunggu_verifikasi')
                                <span class="badge bg-info text-dark fs-6 px-3 py-2">MENUNGGU VERIFIKASI</span>
                            @else
                                <span class="badge bg-warning text-dark fs-6 px-3 py-2">DRAFT / BELUM LENGKAP</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step Pendaftaran --}}
            <div class="row g-4 mb-4">
                {{-- Step 1: Pembayaran --}}
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm {{ $tagihan && $tagihan->status == 'lunas' ? 'border-start border-success border-5' : ($tagihan && $tagihan->status == 'menunggu_konfirmasi' ? 'border-start border-warning border-5' : 'border-start border-danger border-5') }}">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3">1. Biaya Pendaftaran</h5>
                            <p class="small text-muted mb-3">Lakukan pembayaran formulir untuk membuka akses pengisian biodata.</p>
                            
                            @if($tagihan)
                                <h3 class="fw-bold text-primary mb-3">Rp {{ number_format($tagihan->jumlah, 0, ',', '.') }}</h3>
                                
                                @if($tagihan->status == 'lunas')
                                    <div class="alert alert-success py-2 mb-0"><i class="bi bi-check-circle me-1"></i> Pembayaran Lunas</div>
                                @elseif($tagihan->status == 'menunggu_konfirmasi')
                                    <div class="alert alert-warning py-2 mb-0"><i class="bi bi-hourglass-split me-1"></i> Menunggu Konfirmasi Admin</div>
                                @else
                                    <div class="d-grid">
                                        {{-- [UPDATED] Link ke Halaman Upload --}}
                                        <a href="{{ route('pmb.pembayaran.show') }}" class="btn btn-danger">
                                            <i class="bi bi-upload me-1"></i> Bayar / Upload Bukti
                                        </a>
                                    </div>
                                    <small class="d-block mt-2 text-danger fst-italic">*Segera upload bukti transfer</small>
                                @endif
                            @else
                                <div class="alert alert-warning">Tagihan belum dibuat. Hubungi Admin.</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Step 2: Biodata --}}
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3">2. Lengkapi Biodata</h5>
                            <p class="small text-muted mb-3">Isi data diri, sekolah asal, dan upload berkas persyaratan.</p>
                            
                            @if($tagihan && $tagihan->status == 'lunas')
                                <div class="d-grid">
                                    <a href="{{ route('pmb.biodata.show') }}" class="btn btn-primary">
                                        <i class="bi bi-pencil-square me-1"></i> {{ $camaba->status_pendaftaran == 'draft' ? 'Isi Biodata' : 'Lihat/Edit Biodata' }}
                                    </a>
                                </div>
                                @if($camaba->status_pendaftaran == 'menunggu_verifikasi')
                                    <small class="d-block mt-2 text-info fst-italic"><i class="bi bi-info-circle"></i> Data tersimpan. Menunggu verifikasi.</small>
                                @endif
                            @else
                                <div class="d-grid">
                                    <button class="btn btn-secondary" disabled>
                                        <i class="bi bi-lock-fill me-1"></i> Terkunci
                                    </button>
                                </div>
                                <small class="d-block mt-2 text-muted fst-italic">*Selesaikan pembayaran terlebih dahulu.</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- [BARU] Step 3: Jadwal Tes (Hanya Muncul Jika Sudah Submit Biodata) --}}
            @if($camaba->status_pendaftaran != 'draft')
            <div class="card border-0 shadow-sm border-top border-4 border-info">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="fw-bold text-dark"><i class="bi bi-calendar-check me-2 text-info"></i>Jadwal Ujian Seleksi</h5>
                            <p class="text-muted mb-0">Berikut adalah jadwal ujian masuk Anda. Harap hadir tepat waktu.</p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            @if($camaba->period->tanggal_ujian)
                                <h4 class="fw-bold text-primary mb-0">{{ \Carbon\Carbon::parse($camaba->period->tanggal_ujian)->translatedFormat('d F Y') }}</h4>
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-clock me-1"></i> 
                                    {{ \Carbon\Carbon::parse($camaba->period->jam_mulai_ujian)->format('H:i') }} - {{ \Carbon\Carbon::parse($camaba->period->jam_selesai_ujian)->format('H:i') }} WIT
                                </span>
                                <div class="mt-2 text-small fw-bold text-uppercase text-secondary">
                                    {{ $camaba->period->jenis_ujian }} @ {{ $camaba->period->lokasi_ujian }}
                                </div>
                            @else
                                <span class="badge bg-warning text-dark">Jadwal Belum Ditentukan</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
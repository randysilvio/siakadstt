@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Portal Penerimaan Mahasiswa Baru</h3>
            <span class="text-muted small uppercase">Pantau Status Seleksi Admisi & Kelengkapan Berkas Pendaftaran</span>
        </div>
        <div>
            <span class="badge bg-dark text-white rounded-0 font-monospace uppercase fw-bold px-3 py-2">
                GELOMBANG: {{ optional($camaba->period)->nama_gelombang ?? 'PMB AKTIF' }}
            </span>
        </div>
    </div>

    {{-- NOTIFIKASI SISTEM --}}
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

    {{-- KARTU STATUS UTAMA --}}
    <div class="card border-0 shadow-sm mb-4 rounded-0 bg-dark text-white border-top border-secondary border-4">
        <div class="card-body p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <span class="text-white-50 small font-monospace uppercase d-block mb-1">NOMOR REGISTRASI ADMISI</span>
                <h3 class="fw-bold mb-0 font-monospace text-warning">{{ $camaba->no_pendaftaran }}</h3>
                <span class="small text-light uppercase font-monospace">PILIHAN UTAMA: {{ optional($camaba->prodi1)->nama_prodi ?? 'BELUM DIPILIH' }}</span>
            </div>
            <div class="text-md-end">
                <span class="text-white-50 small font-monospace uppercase d-block mb-1">STATUS SELEKSI SAAT INI</span>
                @if($camaba->status_pendaftaran == 'lulus')
                    <span class="badge bg-success text-white rounded-0 px-3 py-2 font-monospace uppercase fw-bold">
                        <i class="bi bi-check-circle-fill me-1"></i> DITERIMA / LULUS SELEKSI
                    </span>
                @elseif($camaba->status_pendaftaran == 'tidak_lulus')
                    <span class="badge bg-danger text-white rounded-0 px-3 py-2 font-monospace uppercase fw-bold">
                        <i class="bi bi-x-circle-fill me-1"></i> TIDAK LULUS SELEKSI
                    </span>
                @elseif($camaba->status_pendaftaran == 'menunggu_verifikasi')
                    <span class="badge bg-warning text-dark rounded-0 px-3 py-2 font-monospace uppercase fw-bold">
                        <i class="bi bi-hourglass-split me-1"></i> MENUNGGU VERIFIKASI PANITIA
                    </span>
                @else
                    <span class="badge bg-light text-dark rounded-0 px-3 py-2 font-monospace uppercase fw-bold">
                        <i class="bi bi-pencil-square me-1"></i> DRAFT / BELUM LENGKAP
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- GRID TAHAPAN PENDAFTARAN --}}
    <div class="row g-4 mb-5">
        {{-- Alur 1: Administrasi Biaya --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 rounded-0 border-top border-dark border-4">
                <div class="card-header bg-white py-3 border-bottom rounded-0 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark uppercase small">1. Administrasi Keuangan Formulir</span>
                    @if($tagihan && $tagihan->status == 'lunas')
                        <span class="badge bg-success rounded-0 font-monospace uppercase">SELESAI</span>
                    @elseif($tagihan && $tagihan->status == 'menunggu_konfirmasi')
                        <span class="badge bg-warning text-dark rounded-0 font-monospace uppercase">VERIFIKASI</span>
                    @else
                        <span class="badge bg-danger rounded-0 font-monospace uppercase">WAJIB</span>
                    @endif
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <p class="text-muted small uppercase mb-3">
                            Selesaikan pelunasan biaya formulir pendaftaran sebesar <strong class="text-dark font-monospace">Rp {{ number_format($tagihan->jumlah ?? 250000, 0, ',', '.') }}</strong> untuk membuka akses pengisian biodata dan unggah dokumen.
                        </p>
                    </div>
                    
                    @if($tagihan && $tagihan->status == 'lunas')
                        <div class="p-3 bg-light border border-success rounded-0 text-success text-center small fw-bold uppercase font-monospace">
                            <i class="bi bi-check-circle-fill me-1"></i> Pembayaran Terverifikasi Lunas
                        </div>
                    @elseif($tagihan && $tagihan->status == 'menunggu_konfirmasi')
                        <div>
                            <div class="p-2 bg-warning bg-opacity-10 border border-warning rounded-0 text-dark text-center small fw-bold uppercase font-monospace mb-2">
                                <i class="bi bi-clock-history me-1"></i> Bukti Transfer Sedang Diverifikasi
                            </div>
                            <a href="{{ route('pmb.pembayaran.show') }}" class="btn btn-sm btn-outline-dark rounded-0 w-100 uppercase fw-bold small py-2">
                                <i class="bi bi-eye me-1"></i> Lihat / Perbarui Bukti Transfer
                            </a>
                        </div>
                    @else
                        <a href="{{ route('pmb.pembayaran.show') }}" class="btn btn-sm btn-primary rounded-0 w-100 uppercase fw-bold small py-2 shadow-sm">
                            <i class="bi bi-upload me-1"></i> Unggah Bukti Pembayaran
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Alur 2: Pengisian Biodata --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 rounded-0 border-top border-dark border-4 {{ $tagihan && $tagihan->status == 'lunas' ? '' : 'bg-light opacity-75' }}">
                <div class="card-header bg-white py-3 border-bottom rounded-0 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark uppercase small">2. Biodata & Berkas Persyaratan</span>
                    @if($camaba->tempat_lahir)
                        <span class="badge bg-success rounded-0 font-monospace uppercase">TERISI</span>
                    @else
                        <span class="badge bg-secondary rounded-0 font-monospace uppercase">TAHAP 2</span>
                    @endif
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <p class="text-muted small uppercase mb-3">
                        Lengkapi identitas pribadi, asal sekolah, nilai rata-rata rapor, serta unggah berkas Ijazah/SKL, Kartu Keluarga, dan Pas Foto terbaru.
                    </p>
                    
                    <div>
                        @if($tagihan && $tagihan->status == 'lunas')
                            <a href="{{ route('pmb.biodata.show') }}" class="btn btn-sm btn-dark rounded-0 w-100 uppercase fw-bold small py-2 shadow-sm">
                                <i class="bi bi-person-lines-fill me-1"></i> {{ $camaba->tempat_lahir ? 'Tinjau / Perbarui Biodata' : 'Lengkapi Biodata Sekarang' }}
                            </a>
                        @else
                            <button class="btn btn-sm btn-secondary rounded-0 w-100 uppercase fw-bold small py-2" disabled>
                                <i class="bi bi-lock-fill me-1"></i> Terkunci (Selesaikan Pembayaran)
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
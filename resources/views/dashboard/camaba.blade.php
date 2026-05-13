@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="text-center mb-5 mt-3">
        <h3 class="fw-bold text-dark uppercase tracking-tight">Portal Penerimaan Mahasiswa Baru</h3>
        <p class="text-muted small">Selamat datang di sistem seleksi akademik STT GPI Papua</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            {{-- Kartu Status Formal --}}
            <div class="card border-0 shadow-sm mb-4 rounded-1 bg-dark text-white">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-white-50 small d-block mb-1">NOMOR REGISTRASI ADMISI</span>
                        <h4 class="fw-bold mb-0 font-monospace">{{ $camaba->no_pendaftaran }}</h4>
                    </div>
                    <div class="text-end">
                        <span class="text-white-50 small d-block mb-1">STATUS PENDAFTARAN</span>
                        @if($camaba->status_pendaftaran == 'lulus')
                            <span class="badge bg-success rounded-1 px-3 py-2">DITERIMA LULUS</span>
                        @elseif($camaba->status_pendaftaran == 'menunggu_verifikasi')
                            <span class="badge bg-warning text-dark rounded-1 px-3 py-2">VERIFIKASI BERKAS</span>
                        @else
                            <span class="badge bg-light text-dark rounded-1 px-3 py-2">DRAFT / BELUM LENGKAP</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row g-4">
                {{-- Alur 1: Administrasi Biaya --}}
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100 rounded-1">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">1. ADMINISTRASI KEUANGAN</h6>
                            <p class="text-muted small mb-4">Selesaikan pelunasan biaya formulir untuk membuka akses pengisian biodata akademik.</p>
                            
                            @if($tagihan && $tagihan->status == 'lunas')
                                <div class="bg-success bg-opacity-10 text-success p-3 rounded-1 text-center fw-bold">
                                    PEMBAYARAN TERVERIFIKASI LUNAS
                                </div>
                            @else
                                <div class="d-grid">
                                    <a href="{{ route('pmb.pembayaran.show') }}" class="btn btn-primary rounded-1 fw-bold">SUBMIT BUKTI PEMBAYARAN</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Alur 2: Pengisian Biodata --}}
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100 rounded-1 {{ $tagihan && $tagihan->status == 'lunas' ? '' : 'bg-light opacity-75' }}">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">2. BIODATA & BERKAS AKADEMIK</h6>
                            <p class="text-muted small mb-4">Lengkapi data profil pendaftar dan unggah dokumen persyaratan seleksi akademik.</p>
                            
                            <div class="d-grid">
                                @if($tagihan && $tagihan->status == 'lunas')
                                    <a href="{{ route('pmb.biodata.show') }}" class="btn btn-dark rounded-1 fw-bold">LENGKAPI DATA KANDIDAT</a>
                                @else
                                    <button class="btn btn-secondary rounded-1 fw-bold" disabled>AKSES TERCUNCI</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
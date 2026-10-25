@extends('layouts.app')

@section('content')
@php
    $namaBank = \App\Models\Pengaturan::get('nama_bank_kampus', 'Bank Papua / BRI');
    $noRek    = \App\Models\Pengaturan::get('no_rekening_kampus', '100-02-0012345-6');
    $atasNama = \App\Models\Pengaturan::get('atas_nama_rekening', 'STT GPI PAPUA');
@endphp
<div class="container-fluid px-3 px-md-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Konfirmasi Bukti Pembayaran PMB</h3>
            <span class="text-muted small uppercase">Unggah Bukti Transfer Pendaftaran Mahasiswa Baru</span>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-dark px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm border-top border-dark border-4 col-lg-6 mx-auto mb-5">
        <div class="card-header bg-dark text-white text-center py-3 uppercase fw-bold small">
            Formulir Konfirmasi Pembayaran
        </div>
        <div class="card-body p-4">
            <div class="p-4 bg-light border text-center mb-4">
                <span class="small font-monospace text-muted uppercase d-block mb-1">NOMINAL TAGIHAN PENDAFTARAN:</span>
                <h2 class="fw-bold text-dark font-monospace mb-1">Rp {{ number_format($tagihan->jumlah, 0, ',', '.') }}</h2>
                <span class="badge bg-dark text-white font-monospace uppercase fw-bold px-3 py-1" style="font-size: 11px;">
                    {{ strtoupper(str_replace('_', ' ', $tagihan->jenis_pembayaran)) }}
                </span>
            </div>

            <div class="p-3 bg-white border border-dark mb-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <div class="small font-monospace">
                    <span class="text-muted d-block" style="font-size: 10px;">REKENING TUJUAN RESMI:</span>
                    <strong class="text-dark uppercase d-block">{{ $namaBank }}: {{ $noRek }}</strong>
                    <span class="text-muted uppercase" style="font-size: 11px;">A.N. {{ $atasNama }}</span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-dark uppercase fw-bold small" style="font-size: 10px;" onclick="navigator.clipboard.writeText('{{ preg_replace('/[^0-9]/', '', $noRek) }}'); this.textContent='Tersalin!'; setTimeout(() => this.textContent='Salin Rekening', 2000);">
                    Salin Rekening
                </button>
            </div>

            <form action="{{ route('pmb.pembayaran.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="pembayaran_id" value="{{ $tagihan->id }}">

                <div class="mb-4">
                    <label class="form-label small fw-bold uppercase text-dark">Unggah Bukti Transfer (JPG/PNG, Maks 2MB) <span class="text-danger">*</span></label>
                    <input type="file" name="bukti_bayar" class="form-control" accept="image/*" required>
                </div>

                @if($tagihan->bukti_bayar)
                    <div class="mb-4 p-3 border bg-light text-center">
                        <span class="small font-monospace text-muted uppercase d-block mb-2">BUKTI TERAKHIR YANG DIUNGGAH:</span>
                        <img src="{{ Storage::url($tagihan->bukti_bayar) }}" class="img-fluid border d-block mx-auto" style="max-height: 180px; object-fit: cover;" alt="Bukti Transfer">
                    </div>
                @endif

                <div class="d-flex justify-content-end pt-3 border-top">
                    <button type="submit" class="btn btn-dark px-5 uppercase fw-bold small text-white shadow-sm w-100 py-2.5">
                        Kirim Bukti Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
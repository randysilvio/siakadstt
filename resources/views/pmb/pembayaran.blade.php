@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Konfirmasi Bukti Transfer</h3>
            <span class="text-muted small uppercase">Validasi Pembayaran Kewajiban Keuangan Pendaftaran PMB</span>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali ke Portal
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-md-6 mx-auto mb-5">
        <div class="card-header bg-dark text-white text-center rounded-0 py-3 uppercase fw-bold small">
            Formulir Pengiriman Bukti Pembayaran
        </div>
        <div class="card-body p-4">
            {{-- Kotak Nominal Siku Presisi --}}
            <div class="p-4 bg-light border border-dark border-opacity-25 rounded-0 text-center mb-4">
                <span class="small font-monospace text-muted uppercase d-block mb-1">TOTAL NOMINAL YANG HARUS DIBAYARKAN:</span>
                <h2 class="fw-bold text-dark font-monospace mb-1">Rp {{ number_format($tagihan->jumlah, 0, ',', '.') }}</h2>
                <span class="badge bg-dark text-white rounded-0 font-monospace uppercase fw-bold px-3 py-1" style="font-size: 11px;">
                    {{ strtoupper(str_replace('_', ' ', $tagihan->jenis_pembayaran)) }}
                </span>
            </div>

            <div class="alert alert-light border border-dark border-opacity-25 rounded-0 small text-dark uppercase font-monospace p-3 mb-4">
                <i class="bi bi-bank text-dark me-2"></i> REKENING TUJUAN RESMI:<br>
                Silakan lakukan transfer ke rekening <strong class="text-dark">Bank Papua: 123-456-7890</strong> atas nama <strong class="text-dark">STT GPI Papua</strong>.
            </div>

            <form action="{{ route('pmb.pembayaran.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="pembayaran_id" value="{{ $tagihan->id }}">

                <div class="mb-4">
                    <label class="form-label small fw-bold uppercase text-dark">Upload Bukti Transfer (Foto / Screenshot) <span class="text-danger">*</span></label>
                    <input type="file" name="bukti_bayar" class="form-control rounded-0" accept="image/*" required>
                    <div class="form-text text-muted small uppercase mt-1">Format berkas: JPG, PNG. Ukuran berkas maksimal: 2MB.</div>
                </div>

                @if($tagihan->bukti_bayar)
                    <div class="mb-4 p-3 border border-dark border-opacity-25 rounded-0 bg-light text-center">
                        <span class="small font-monospace text-muted uppercase d-block mb-2">BUKTI TERAKHIR YANG TELAH DIKIRIMKAN:</span>
                        <img src="{{ Storage::url($tagihan->bukti_bayar) }}" class="img-fluid rounded-0 border border-dark border-opacity-25 d-block mx-auto" style="max-height: 180px; object-fit: cover;" alt="Bukti Transfer">
                    </div>
                @endif

                <div class="d-flex justify-content-end pt-3 border-top">
                    <button type="submit" class="btn btn-sm btn-success rounded-0 px-5 uppercase fw-bold small text-white shadow-sm w-100 py-2">
                        <i class="bi bi-send-fill me-1"></i> Kirim Verifikasi Bukti
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
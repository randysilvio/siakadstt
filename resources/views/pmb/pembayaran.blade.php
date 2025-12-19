@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h5 class="mb-0 fw-bold">Konfirmasi Pembayaran</h5>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <p class="text-muted mb-1">Total yang harus dibayar:</p>
                        <h2 class="fw-bold text-primary">Rp {{ number_format($tagihan->jumlah, 0, ',', '.') }}</h2>
                        <span class="badge bg-info text-dark mt-2">{{ strtoupper(str_replace('_', ' ', $tagihan->jenis_pembayaran)) }}</span>
                    </div>

                    <div class="alert alert-warning small">
                        <i class="bi bi-bank me-1"></i> Silakan transfer ke <strong>Bank Papua: 123-456-7890</strong> a.n STT GPI Papua.
                    </div>

                    <form action="{{ route('pmb.pembayaran.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="pembayaran_id" value="{{ $tagihan->id }}">

                        <div class="mb-4">
                            <label class="form-label fw-bold">Upload Bukti Transfer (Foto/Screenshot)</label>
                            <input type="file" name="bukti_bayar" class="form-control" accept="image/*" required>
                            <div class="form-text">Format: JPG, PNG. Maks: 2MB.</div>
                        </div>

                        @if($tagihan->bukti_bayar)
                            <div class="mb-3 p-2 border rounded bg-light text-center">
                                <p class="small text-muted mb-1">Bukti Terupload:</p>
                                <img src="{{ Storage::url($tagihan->bukti_bayar) }}" class="img-fluid rounded" style="max-height: 150px;">
                            </div>
                        @endif

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success fw-bold">
                                <i class="bi bi-send me-1"></i> Kirim Bukti Pembayaran
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
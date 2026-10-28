@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Edit Tagihan & Status Dispensasi</h3>
            <span class="text-muted small uppercase">Modifikasi Nominal, Status Pelunasan, atau Otorisasi Dispensasi Cicilan KRS</span>
        </div>
        <div>
            <a href="{{ route('pembayaran.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-lg-8 mx-auto mb-5">
        <div class="card-header bg-dark text-white py-3 rounded-0 uppercase fw-bold small">
            Formulir Pembaruan Tagihan: {{ $pembayaran->mahasiswa->nama_lengkap ?? ($pembayaran->user->name ?? 'Pembayar') }}
        </div>
        <div class="card-body p-4">
            <form action="{{ route('pembayaran.update', $pembayaran->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold uppercase text-dark">Periode Semester <span class="text-danger">*</span></label>
                        @include('pembayaran._pilih_semester', ['terpilih' => $pembayaran->semester])
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold uppercase text-dark">Nominal Tagihan (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah" class="form-control rounded-0 font-monospace fw-bold" value="{{ old('jumlah', $pembayaran->jumlah) }}" min="0" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-bold uppercase text-dark">Status Tagihan <span class="text-danger">*</span></label>
                        <select name="status" class="form-select rounded-0 uppercase small fw-bold" required>
                            <option value="belum_lunas" {{ in_array($pembayaran->status, ['belum_lunas', 'belum_bayar']) ? 'selected' : '' }}>BELUM LUNAS (KUNCI KRS)</option>
                            <option value="menunggu_konfirmasi" {{ $pembayaran->status == 'menunggu_konfirmasi' ? 'selected' : '' }}>MENUNGGU KONFIRMASI BUKTI TRANSFER</option>
                            <option value="dispensasi" {{ $pembayaran->status == 'dispensasi' ? 'selected' : '' }}>DISPENSASI / CICILAN RESMI (BUKA KUNCI KRS)</option>
                            <option value="lunas" {{ $pembayaran->status == 'lunas' ? 'selected' : '' }}>LUNAS TERVERIFIKASI</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-bold uppercase text-dark">Keterangan / Catatan Cicilan</label>
                        <textarea name="keterangan" rows="3" class="form-control rounded-0 uppercase" placeholder="Catat nomor kuitansi atau rincian kesepakatan dispensasi cicilan...">{{ old('keterangan', $pembayaran->keterangan) }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end pt-3 border-top">
                    <a href="{{ route('pembayaran.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
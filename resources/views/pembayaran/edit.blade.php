@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold text-dark">Edit Tagihan / Bayar Sebagian</h2>
                <a href="{{ route('pembayaran.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    {{-- Info Mahasiswa (Read Only) --}}
                    <div class="alert alert-info d-flex align-items-center mb-4">
                        <i class="bi bi-person-circle fs-3 me-3"></i>
                        <div>
                            <div class="fw-bold">{{ $pembayaran->mahasiswa->nama_lengkap }}</div>
                            <div class="small">NIM: {{ $pembayaran->mahasiswa->nim }} | Prodi: {{ $pembayaran->mahasiswa->programStudi->nama_prodi ?? '-' }}</div>
                        </div>
                    </div>

                    <form action="{{ route('pembayaran.update', $pembayaran->id) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label fw-bold">Semester</label>
                            <input type="text" name="semester" class="form-control" value="{{ old('semester', $pembayaran->semester) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Jumlah Tagihan (Sisa)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="jumlah" class="form-control" value="{{ old('jumlah', $pembayaran->jumlah) }}" min="0" required>
                            </div>
                            <div class="form-text text-muted">
                                Jika mahasiswa membayar sebagian (cicilan), kurangi jumlah ini sesuai sisa tagihan.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" class="form-select">
                                <option value="belum_lunas" {{ $pembayaran->status == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                                <option value="lunas" {{ $pembayaran->status == 'lunas' ? 'selected' : '' }}>Lunas</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Keterangan / Catatan</label>
                            <textarea name="keterangan" class="form-control" rows="3" placeholder="Contoh: Cicilan ke-1 sudah masuk Rp 1.000.000">{{ old('keterangan', $pembayaran->keterangan) }}</textarea>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-warning text-dark fw-bold">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
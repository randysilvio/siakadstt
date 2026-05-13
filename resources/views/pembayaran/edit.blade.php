@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Edit Tagihan / Bayar Sebagian</h3>
            <span class="text-muted small uppercase">Pembaruan Nilai Kewajiban & Verifikasi Manual</span>
        </div>
        <div>
            <a href="{{ route('pembayaran.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    <div class="row justify-content-center mb-5">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                    Formulir Pembaruan Data
                </div>
                <div class="card-body p-4">
                    
                    {{-- INFO IDENTITAS PEMBAYAR SIKU 0PX --}}
                    <div class="p-3 bg-light border border-dark border-opacity-25 rounded-0 mb-4 d-flex align-items-center">
                        @if($pembayaran->mahasiswa)
                            <div class="bg-dark text-white rounded-0 d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                <i class="bi bi-mortarboard-fill fs-5"></i>
                            </div>
                            <div>
                                <div class="small font-monospace text-muted uppercase">MAHASISWA AKTIF</div>
                                <div class="fw-bold fs-6 uppercase text-dark">{{ $pembayaran->mahasiswa->nama_lengkap }}</div>
                                <div class="small font-monospace text-muted">NIM: {{ $pembayaran->mahasiswa->nim }} | PRODI: {{ $pembayaran->mahasiswa->programStudi->nama_prodi ?? '-' }}</div>
                            </div>
                        @elseif($pembayaran->user)
                            <div class="bg-secondary text-white rounded-0 d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                <i class="bi bi-person-fill fs-5"></i>
                            </div>
                            <div>
                                <div class="small font-monospace text-muted uppercase">CALON MAHASISWA (PMB)</div>
                                <div class="fw-bold fs-6 uppercase text-dark">{{ $pembayaran->user->name }}</div>
                                <div class="small font-monospace text-muted">{{ $pembayaran->user->email }}</div>
                            </div>
                        @else
                            <div class="text-danger fw-bold uppercase font-monospace">DATA USER/MAHASISWA TIDAK DITEMUKAN</div>
                        @endif
                    </div>

                    <form action="{{ route('pembayaran.update', $pembayaran->id) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label small fw-bold uppercase text-dark">Semester / Keterangan Tagihan <span class="text-danger">*</span></label>
                            <input type="text" name="semester" class="form-control rounded-0 font-monospace uppercase" value="{{ old('semester', $pembayaran->semester) }}" required>
                            <div class="form-text text-muted small font-monospace">CONTOH: GASAL 2024 ATAU PMB GELOMBANG 1</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold uppercase text-dark">Jumlah Tagihan (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light rounded-0 font-monospace">Rp</span>
                                <input type="number" name="jumlah" class="form-control rounded-0 font-monospace text-primary fw-bold" value="{{ old('jumlah', $pembayaran->jumlah) }}" min="0" required>
                            </div>
                            <div class="form-text text-muted small uppercase mt-1">
                                * Jika ini pembayaran cicilan, masukkan sisa tagihan yang belum dibayar.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold uppercase text-dark">Status Pembayaran <span class="text-danger">*</span></label>
                            <select name="status" class="form-select rounded-0 uppercase fw-bold">
                                <option value="belum_lunas" {{ $pembayaran->status == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                                <option value="menunggu_konfirmasi" {{ $pembayaran->status == 'menunggu_konfirmasi' ? 'selected' : '' }}>Menunggu Konfirmasi (Cek Bukti)</option>
                                <option value="lunas" {{ $pembayaran->status == 'lunas' ? 'selected' : '' }}>Lunas</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold uppercase text-dark">Catatan Tambahan (Opsional)</label>
                            <textarea name="keterangan" class="form-control rounded-0 uppercase" rows="3" placeholder="PEMBAYARAN VIA TRANSFER BANK PAPUA TGL 20 DES...">{{ old('keterangan', $pembayaran->keterangan) }}</textarea>
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
    </div>
</div>
@endsection
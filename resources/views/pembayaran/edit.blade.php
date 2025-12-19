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
                    
                    {{-- INFO IDENTITAS PEMBAYAR (UPDATED) --}}
                    <div class="alert alert-info d-flex align-items-center mb-4">
                        @if($pembayaran->mahasiswa)
                            {{-- Jika Mahasiswa --}}
                            <div class="bg-primary bg-opacity-25 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                <i class="bi bi-mortarboard-fill fs-4"></i>
                            </div>
                            <div>
                                <div class="small text-uppercase fw-bold text-muted">Mahasiswa</div>
                                <div class="fw-bold fs-5">{{ $pembayaran->mahasiswa->nama_lengkap }}</div>
                                <div class="small">NIM: {{ $pembayaran->mahasiswa->nim }} | Prodi: {{ $pembayaran->mahasiswa->programStudi->nama_prodi ?? '-' }}</div>
                            </div>
                        @elseif($pembayaran->user)
                            {{-- Jika Camaba --}}
                            <div class="bg-warning bg-opacity-25 text-dark rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                <i class="bi bi-person-fill fs-4"></i>
                            </div>
                            <div>
                                <div class="small text-uppercase fw-bold text-muted">Calon Mahasiswa (PMB)</div>
                                <div class="fw-bold fs-5">{{ $pembayaran->user->name }}</div>
                                <div class="small text-muted">{{ $pembayaran->user->email }}</div>
                            </div>
                        @else
                            {{-- Error Handling --}}
                            <div class="text-danger fw-bold">Data User/Mahasiswa Tidak Ditemukan</div>
                        @endif
                    </div>

                    <form action="{{ route('pembayaran.update', $pembayaran->id) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label fw-bold">Semester / Keterangan Tagihan</label>
                            <input type="text" name="semester" class="form-control" value="{{ old('semester', $pembayaran->semester) }}" required>
                            <div class="form-text">Contoh: "Gasal 2024" atau "PMB Gelombang 1"</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Jumlah Tagihan (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="jumlah" class="form-control fw-bold text-primary" value="{{ old('jumlah', $pembayaran->jumlah) }}" min="0" required>
                            </div>
                            <div class="form-text text-muted">
                                <i class="bi bi-info-circle me-1"></i> Jika ini pembayaran cicilan, masukkan sisa tagihan yang belum dibayar.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Status Pembayaran</label>
                            <select name="status" class="form-select">
                                <option value="belum_lunas" {{ $pembayaran->status == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                                <option value="menunggu_konfirmasi" {{ $pembayaran->status == 'menunggu_konfirmasi' ? 'selected' : '' }}>Menunggu Konfirmasi (Cek Bukti)</option>
                                <option value="lunas" {{ $pembayaran->status == 'lunas' ? 'selected' : '' }}>Lunas</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Catatan Tambahan (Opsional)</label>
                            <textarea name="keterangan" class="form-control" rows="3" placeholder="Contoh: Pembayaran via Transfer Bank Papua tgl 20 Des...">{{ old('keterangan', $pembayaran->keterangan) }}</textarea>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary fw-bold py-2">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('pembayaran.index') }}" class="btn btn-light border">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
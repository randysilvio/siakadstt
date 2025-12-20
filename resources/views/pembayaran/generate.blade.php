@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm border-0 col-md-8 mx-auto">
        <div class="card-header bg-primary text-white py-3">
            <h4 class="mb-0 fw-bold"><i class="bi bi-lightning-charge-fill me-2"></i>Generate Tagihan Massal</h4>
        </div>
        <div class="card-body p-4">
            
            {{-- Tampilkan Pesan Error Validasi --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                <div>
                    Fitur ini akan membuat tagihan secara otomatis untuk <strong>banyak mahasiswa sekaligus</strong>. 
                    Sistem akan otomatis melewati mahasiswa yang sudah memiliki tagihan dengan jenis dan semester yang sama.
                </div>
            </div>

            <form action="{{ route('pembayaran.storeGenerate') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin men-generate tagihan untuk kriteria ini?');">
                @csrf
                
                {{-- BAGIAN 1: TARGET MAHASISWA --}}
                <h5 class="fw-bold text-muted mb-3 border-bottom pb-2">1. Target Mahasiswa</h5>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="prodi_id" class="form-label fw-bold">Program Studi</label>
                        <select name="prodi_id" class="form-select">
                            <option value="">- Semua Program Studi -</option>
                            @foreach ($prodis as $prodi)
                                {{-- Menggunakan properti nama yg umum, sesuaikan jika di database anda 'nama_prodi' --}}
                                <option value="{{ $prodi->id }}">{{ $prodi->nama_program_studi ?? $prodi->nama_prodi }}</option>
                            @endforeach
                        </select>
                        <div class="form-text small">Biarkan kosong untuk memilih SEMUA prodi.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="angkatan" class="form-label fw-bold">Angkatan (Tahun Masuk)</label>
                        <select name="angkatan" class="form-select">
                            <option value="">- Semua Angkatan -</option>
                            @foreach ($angkatans as $angkatan)
                                <option value="{{ $angkatan }}">{{ $angkatan }}</option>
                            @endforeach
                        </select>
                        <div class="form-text small">Biarkan kosong untuk memilih SEMUA angkatan.</div>
                    </div>
                </div>

                {{-- BAGIAN 2: DETAIL TAGIHAN --}}
                <h5 class="fw-bold text-muted mb-3 border-bottom pb-2">2. Detail Tagihan</h5>

                <div class="mb-3">
                    <label for="jenis_pembayaran" class="form-label fw-bold">Jenis Pembayaran <span class="text-danger">*</span></label>
                    <select name="jenis_pembayaran" class="form-select" required>
                        <option value="">- Pilih Jenis -</option>
                        <option value="spp">SPP</option>
                        <option value="uang_gedung">Uang Gedung / Pembangunan</option>
                        <option value="sks">Biaya SKS</option>
                        <option value="registrasi">Registrasi Ulang / Heregistrasi</option>
                        {{-- [TAMBAHAN BARU] Opsi PPL --}}
                        <option value="biaya_ppl">Biaya PPL (Praktek Pengalaman Lapangan)</option>
                        <option value="wisuda">Biaya Wisuda</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="semester" class="form-label fw-bold">Semester / Periode <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="semester" placeholder="Contoh: Gasal 2024/2025" required>
                </div>

                <div class="mb-4">
                    <label for="jumlah" class="form-label fw-bold">Jumlah Tagihan (Rp) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control" name="jumlah" placeholder="Contoh: 2500000" min="1" required>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('pembayaran.index') }}" class="btn btn-light border">Kembali</a>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="bi bi-gear-wide-connected me-1"></i> Jalankan Generate
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
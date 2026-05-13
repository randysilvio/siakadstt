@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Generate Tagihan Massal</h3>
            <span class="text-muted small uppercase">Otomatisasi Pembuatan Kewajiban Finansial Kolektif</span>
        </div>
        <div>
            <a href="{{ route('pembayaran.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-md-8 mx-auto mb-5">
        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
            Parameter Eksekusi Otomatis
        </div>
        <div class="card-body p-4">
            
            {{-- Tampilkan Pesan Error Validasi --}}
            @if ($errors->any())
                <div class="alert alert-danger border rounded-0 p-3 mb-4 font-monospace small uppercase shadow-sm">
                    <strong class="d-block mb-1">GAGAL MEMPROSES:</strong>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="p-3 bg-light border rounded-0 mb-4 small text-muted uppercase">
                <strong class="text-dark d-block mb-1 font-monospace">INFORMASI SISTEM:</strong>
                Fitur ini akan membuat tagihan secara otomatis untuk <strong class="text-dark">banyak mahasiswa sekaligus</strong>. Sistem akan otomatis melewati mahasiswa yang sudah memiliki tagihan dengan jenis dan semester yang sama.
            </div>

            <form action="{{ route('pembayaran.storeGenerate') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin men-generate tagihan secara massal untuk kriteria ini?');">
                @csrf
                
                {{-- BAGIAN 1: TARGET MAHASISWA --}}
                <h6 class="uppercase fw-bold small text-dark mb-3 border-bottom pb-2">1. Kriteria Target Mahasiswa</h6>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="prodi_id" class="form-label small fw-bold uppercase text-dark">Program Studi</label>
                        <select name="prodi_id" class="form-select rounded-0 uppercase">
                            <option value="">-- SEMUA PROGRAM STUDI --</option>
                            @foreach ($prodis as $prodi)
                                <option value="{{ $prodi->id }}">{{ $prodi->nama_program_studi ?? $prodi->nama_prodi }}</option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted small uppercase mt-1">* Biarkan kosong untuk memilih SEMUA prodi.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="angkatan" class="form-label small fw-bold uppercase text-dark">Angkatan (Tahun Masuk)</label>
                        <select name="angkatan" class="form-select rounded-0 font-monospace">
                            <option value="">-- SEMUA ANGKATAN --</option>
                            @foreach ($angkatans as $angkatan)
                                <option value="{{ $angkatan }}">{{ $angkatan }}</option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted small uppercase mt-1">* Biarkan kosong untuk memilih SEMUA angkatan.</div>
                    </div>
                </div>

                {{-- BAGIAN 2: DETAIL TAGIHAN --}}
                <h6 class="uppercase fw-bold small text-dark mb-3 border-bottom pb-2 mt-4">2. Spesifikasi Tagihan Baru</h6>

                <div class="mb-3">
                    <label for="jenis_pembayaran" class="form-label small fw-bold uppercase text-dark">Jenis Pembayaran <span class="text-danger">*</span></label>
                    <select name="jenis_pembayaran" class="form-select rounded-0 uppercase" required>
                        <option value="">-- PILIH JENIS --</option>
                        <option value="spp">SPP</option>
                        <option value="uang_gedung">Uang Gedung / Pembangunan</option>
                        <option value="sks">Biaya SKS</option>
                        <option value="registrasi">Registrasi Ulang / Heregistrasi</option>
                        <option value="biaya_ppl">Biaya PPL (Praktek Pengalaman Lapangan)</option>
                        <option value="wisuda">Biaya Wisuda</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="semester" class="form-label small fw-bold uppercase text-dark">Semester / Periode <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-0 font-monospace uppercase" name="semester" placeholder="GASAL 2024/2025" required>
                </div>

                <div class="mb-4">
                    <label for="jumlah" class="form-label small fw-bold uppercase text-dark">Jumlah Tagihan (Rp) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light rounded-0 font-monospace">Rp</span>
                        <input type="number" class="form-control rounded-0 font-monospace" name="jumlah" placeholder="2500000" min="1" required>
                    </div>
                </div>

                <div class="d-flex justify-content-end pt-4 mt-4 border-top">
                    <a href="{{ route('pembayaran.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                        Jalankan Generate
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
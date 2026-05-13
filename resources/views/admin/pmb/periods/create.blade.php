@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-0 text-dark fw-bold">Registrasi Gelombang PMB</h3>
                    <span class="text-muted small">Konfigurasi administrasi dan jadwal seleksi penerimaan</span>
                </div>
                <a href="{{ route('admin.pmb-periods.index') }}" class="btn btn-outline-dark btn-sm rounded-1">Batal</a>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('admin.pmb-periods.store') }}" method="POST">
                        @csrf
                        
                        {{-- Bagian 1: Administrasi Pendaftaran --}}
                        <div class="mb-4">
                            <h6 class="text-dark fw-bold border-bottom pb-2 mb-3 text-uppercase">Tahap Administrasi Registrasi</h6>
                            
                            <div class="row mb-3 g-3">
                                <div class="col-md-7">
                                    <label class="form-label text-dark fw-semibold">Identifikasi Gelombang <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_gelombang" class="form-control rounded-1" placeholder="Contoh: Gelombang I Tahun Akademik 2026" required autofocus>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label text-dark fw-semibold">Biaya Formulir (Rp) <span class="text-danger">*</span></label>
                                    <input type="number" name="biaya_pendaftaran" class="form-control rounded-1 font-monospace" value="250000" required>
                                </div>
                            </div>

                            <div class="row mb-3 g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-dark fw-semibold">Waktu Pembukaan Portal <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_buka" class="form-control rounded-1" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-dark fw-semibold">Waktu Penutupan Portal <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_tutup" class="form-control rounded-1" required>
                                </div>
                            </div>
                        </div>

                        {{-- Bagian 2: Jadwal Ujian Masuk --}}
                        <div class="mb-4">
                            <h6 class="text-dark fw-bold border-bottom pb-2 mb-3 mt-4 text-uppercase">Rencana Seleksi Ujian Masuk (CBT/Wawancara)</h6>
                            
                            <div class="row mb-3 g-3">
                                <div class="col-md-4">
                                    <label class="form-label text-dark fw-semibold">Tanggal Pelaksanaan</label>
                                    <input type="date" name="tanggal_ujian" class="form-control rounded-1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-dark fw-semibold">Waktu Mulai (WIB/WIT)</label>
                                    <input type="time" name="jam_mulai_ujian" class="form-control rounded-1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-dark fw-semibold">Waktu Selesai (WIB/WIT)</label>
                                    <input type="time" name="jam_selesai_ujian" class="form-control rounded-1">
                                </div>
                            </div>

                            <div class="row mb-3 g-3">
                                <div class="col-md-4">
                                    <label class="form-label text-dark fw-semibold">Metode Evaluasi</label>
                                    <select name="jenis_ujian" class="form-select rounded-1">
                                        <option value="offline">Luring (Tatap Muka di Kampus)</option>
                                        <option value="online">Daring (Virtual / Online)</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label text-dark fw-semibold">Titik Lokasi / Tautan Daring</label>
                                    <input type="text" name="lokasi_ujian" class="form-control rounded-1" placeholder="Rincian ruangan atau tautan platform konferensi video...">
                                </div>
                            </div>
                        </div>

                        {{-- Bagian 3: Status Publikasi --}}
                        <div class="mb-4 bg-light p-3 border rounded-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="isActive" name="is_active" value="1" checked>
                                <label class="form-check-label text-dark fw-bold ms-2" for="isActive">Aktifkan Gelombang Admisi</label>
                            </div>
                            <div class="form-text text-danger mt-2">Perhatian: Sistem hanya mengizinkan satu gelombang PMB berstatus aktif pada waktu yang bersamaan. Mengaktifkan data ini akan menonaktifkan gelombang sebelumnya.</div>
                        </div>

                        <hr class="text-muted my-4">
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-5 rounded-1">Simpan Parameter PMB</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
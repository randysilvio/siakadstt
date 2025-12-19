@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold py-3">Tambah Gelombang & Jadwal Ujian</div>
                <div class="card-body">
                    <form action="{{ route('admin.pmb-periods.store') }}" method="POST">
                        @csrf
                        
                        <h6 class="text-teal-700 fw-bold border-bottom pb-2 mb-3">Informasi Gelombang</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Gelombang</label>
                                <input type="text" name="nama_gelombang" class="form-control" placeholder="Contoh: Gelombang I 2025" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Biaya Pendaftaran (Rp)</label>
                                <input type="number" name="biaya_pendaftaran" class="form-control" value="250000" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Buka Pendaftaran</label>
                                <input type="date" name="tanggal_buka" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Tutup Pendaftaran</label>
                                <input type="date" name="tanggal_tutup" class="form-control" required>
                            </div>
                        </div>

                        <h6 class="text-teal-700 fw-bold border-bottom pb-2 mb-3">Jadwal Ujian Masuk</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Ujian</label>
                                <input type="date" name="tanggal_ujian" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Jam Mulai</label>
                                <input type="time" name="jam_mulai_ujian" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Jam Selesai</label>
                                <input type="time" name="jam_selesai_ujian" class="form-control">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Jenis Ujian</label>
                                <select name="jenis_ujian" class="form-select">
                                    <option value="offline">Offline (Tatap Muka)</option>
                                    <option value="online">Online</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Lokasi / Link Ujian</label>
                                <input type="text" name="lokasi_ujian" class="form-control" placeholder="Contoh: Gedung A Lt. 2 / Link Zoom...">
                            </div>
                        </div>

                        <div class="mb-4 form-check form-switch p-3 bg-light rounded ms-1">
                            <input class="form-check-input" type="checkbox" id="isActive" name="is_active" value="1" checked>
                            <label class="form-check-label fw-bold" for="isActive">Set sebagai Gelombang Aktif</label>
                            <div class="form-text">Hanya satu gelombang yang bisa aktif dalam satu waktu.</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.pmb-periods.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
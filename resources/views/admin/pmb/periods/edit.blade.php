@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Pembaruan Parameter Gelombang PMB</h3>
            <span class="text-muted small uppercase">Modifikasi Jadwal Pendaftaran & Rencana Seleksi Penerimaan</span>
        </div>
        <div>
            <a href="{{ route('admin.pmb-periods.index') }}" class="btn btn-outline-dark btn-sm rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-lg-8 mx-auto mb-5">
        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
            Formulir Pembaruan Parameter Gelombang
        </div>
        <div class="card-body p-4">

            @if ($errors->any())
                <div class="alert alert-danger border rounded-0 shadow-sm mb-4 p-3 small uppercase font-monospace">
                    <strong class="d-block mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i> GAGAL MEMPERBARUI DATA:</strong>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.pmb-periods.update', $pmbPeriod->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <h6 class="text-dark fw-bold border-bottom pb-2 mb-3 uppercase small">1. Tahap Administrasi Registrasi</h6>
                
                <div class="row mb-3 g-3">
                    <div class="col-md-5">
                        <label class="form-label text-dark small fw-bold uppercase">Nama Gelombang <span class="text-danger">*</span></label>
                        <input type="text" name="nama_gelombang" class="form-control rounded-0 uppercase" value="{{ old('nama_gelombang', $pmbPeriod->nama_gelombang) }}" required autofocus>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-dark small fw-bold uppercase">Tahun PMB (Angkatan) <span class="text-danger">*</span></label>
                        <input type="number" name="tahun_pmb" class="form-control rounded-0 font-monospace fw-bold" value="{{ old('tahun_pmb', $pmbPeriod->tahun_pmb) }}" min="2000" max="2100" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-dark small fw-bold uppercase">Biaya Formulir (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light rounded-0 font-monospace">Rp</span>
                            <input type="number" name="biaya_pendaftaran" class="form-control rounded-0 font-monospace fw-bold" value="{{ old('biaya_pendaftaran', $pmbPeriod->biaya_pendaftaran) }}" min="0" required>
                        </div>
                    </div>
                </div>

                <div class="row mb-4 g-3">
                    <div class="col-md-6">
                        <label class="form-label text-dark small fw-bold uppercase">Tanggal Buka Pendaftaran <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_buka" class="form-control rounded-0 font-monospace" value="{{ old('tanggal_buka', $pmbPeriod->tanggal_buka->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-dark small fw-bold uppercase">Tanggal Tutup Pendaftaran <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_tutup" class="form-control rounded-0 font-monospace" value="{{ old('tanggal_tutup', $pmbPeriod->tanggal_tutup->format('Y-m-d')) }}" required>
                    </div>
                </div>

                <h6 class="text-dark fw-bold border-bottom pb-2 mb-3 mt-4 uppercase small">2. Rencana Seleksi Ujian Masuk</h6>
                
                <div class="row mb-3 g-3">
                    <div class="col-md-4">
                        <label class="form-label text-dark small fw-bold uppercase">Tanggal Ujian</label>
                        <input type="date" name="tanggal_ujian" class="form-control rounded-0 font-monospace" value="{{ old('tanggal_ujian', $pmbPeriod->tanggal_ujian ? \Carbon\Carbon::parse($pmbPeriod->tanggal_ujian)->format('Y-m-d') : '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-dark small fw-bold uppercase">Jam Mulai (WIT)</label>
                        <input type="time" name="jam_mulai_ujian" class="form-control rounded-0 font-monospace" value="{{ old('jam_mulai_ujian', $pmbPeriod->jam_mulai_ujian ? \Carbon\Carbon::parse($pmbPeriod->jam_mulai_ujian)->format('H:i') : '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-dark small fw-bold uppercase">Jam Selesai (WIT)</label>
                        <input type="time" name="jam_selesai_ujian" class="form-control rounded-0 font-monospace" value="{{ old('jam_selesai_ujian', $pmbPeriod->jam_selesai_ujian ? \Carbon\Carbon::parse($pmbPeriod->jam_selesai_ujian)->format('H:i') : '') }}">
                    </div>
                </div>

                <div class="row mb-4 g-3">
                    <div class="col-md-4">
                        <label class="form-label text-dark small fw-bold uppercase">Metode Ujian</label>
                        <select name="jenis_ujian" class="form-select rounded-0 uppercase">
                            <option value="offline" {{ old('jenis_ujian', $pmbPeriod->jenis_ujian) == 'offline' ? 'selected' : '' }}>Luring (Tatap Muka)</option>
                            <option value="online" {{ old('jenis_ujian', $pmbPeriod->jenis_ujian) == 'online' ? 'selected' : '' }}>Daring (Online)</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label text-dark small fw-bold uppercase">Lokasi Ruangan / Tautan Daring</label>
                        <input type="text" name="lokasi_ujian" class="form-control rounded-0 uppercase" value="{{ old('lokasi_ujian', $pmbPeriod->lokasi_ujian) }}">
                    </div>
                </div>

                <div class="mb-4 bg-light p-3 border rounded-0">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="isActive" name="is_active" value="1" {{ old('is_active', $pmbPeriod->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label text-dark fw-bold uppercase small ms-2" for="isActive">Aktifkan Gelombang Admisi</label>
                    </div>
                </div>

                <div class="d-flex justify-content-end pt-3 border-top">
                    <a href="{{ route('admin.pmb-periods.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                        Simpan Pembaruan Parameter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
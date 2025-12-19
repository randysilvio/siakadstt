@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold py-3">Edit Gelombang & Jadwal</div>
                <div class="card-body">
                    <form action="{{ route('admin.pmb-periods.update', $pmbPeriod->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <h6 class="text-teal-700 fw-bold border-bottom pb-2 mb-3">Informasi Gelombang</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Gelombang</label>
                                <input type="text" name="nama_gelombang" class="form-control" value="{{ $pmbPeriod->nama_gelombang }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Biaya Pendaftaran (Rp)</label>
                                <input type="number" name="biaya_pendaftaran" class="form-control" value="{{ $pmbPeriod->biaya_pendaftaran }}" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Buka</label>
                                <input type="date" name="tanggal_buka" class="form-control" value="{{ $pmbPeriod->tanggal_buka->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Tutup</label>
                                <input type="date" name="tanggal_tutup" class="form-control" value="{{ $pmbPeriod->tanggal_tutup->format('Y-m-d') }}" required>
                            </div>
                        </div>

                        <h6 class="text-teal-700 fw-bold border-bottom pb-2 mb-3">Jadwal Ujian Masuk</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Ujian</label>
                                <input type="date" name="tanggal_ujian" class="form-control" value="{{ $pmbPeriod->tanggal_ujian ? \Carbon\Carbon::parse($pmbPeriod->tanggal_ujian)->format('Y-m-d') : '' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Jam Mulai</label>
                                <input type="time" name="jam_mulai_ujian" class="form-control" value="{{ $pmbPeriod->jam_mulai_ujian ? \Carbon\Carbon::parse($pmbPeriod->jam_mulai_ujian)->format('H:i') : '' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Jam Selesai</label>
                                <input type="time" name="jam_selesai_ujian" class="form-control" value="{{ $pmbPeriod->jam_selesai_ujian ? \Carbon\Carbon::parse($pmbPeriod->jam_selesai_ujian)->format('H:i') : '' }}">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Jenis Ujian</label>
                                <select name="jenis_ujian" class="form-select">
                                    <option value="offline" {{ $pmbPeriod->jenis_ujian == 'offline' ? 'selected' : '' }}>Offline (Tatap Muka)</option>
                                    <option value="online" {{ $pmbPeriod->jenis_ujian == 'online' ? 'selected' : '' }}>Online</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Lokasi / Link Ujian</label>
                                <input type="text" name="lokasi_ujian" class="form-control" value="{{ $pmbPeriod->lokasi_ujian }}">
                            </div>
                        </div>

                        <div class="mb-4 form-check form-switch p-3 bg-light rounded ms-1">
                            <input class="form-check-input" type="checkbox" id="isActive" name="is_active" value="1" {{ $pmbPeriod->is_active ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="isActive">Set sebagai Gelombang Aktif</label>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.pmb-periods.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
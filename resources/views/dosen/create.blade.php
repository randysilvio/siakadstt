@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Tambah Dosen Baru</h2>
        <a href="{{ route('admin.dosen.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    <form action="{{ route('admin.dosen.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <ul class="nav nav-tabs mb-4" id="dosenTabs" role="tablist">
            <li class="nav-item"><button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#pribadi" type="button">1. Data Pribadi</button></li>
            <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#kepegawaian" type="button">2. Kepegawaian & Akademik</button></li>
            <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#akun" type="button">3. Akun Login</button></li>
        </ul>

        <div class="tab-content">
            {{-- TAB 1: PRIBADI --}}
            <div class="tab-pane fade show active" id="pribadi">
                <div class="card shadow-sm border-0"><div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap (dengan Gelar) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIDN <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nidn" value="{{ old('nidn') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIK (KTP) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nik" value="{{ old('nik') }}" maxlength="16" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NPWP</label>
                            <input type="text" class="form-control" name="npwp" value="{{ old('npwp') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" class="form-control" name="tempat_lahir" value="{{ old('tempat_lahir') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select class="form-select" name="jenis_kelamin" required>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat Domisili</label>
                            <textarea class="form-control" name="alamat" rows="2">{{ old('alamat') }}</textarea>
                        </div>
                    </div>
                </div></div>
            </div>

            {{-- TAB 2: KEPEGAWAIAN --}}
            <div class="tab-pane fade" id="kepegawaian">
                <div class="card shadow-sm border-0"><div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Status Kepegawaian <span class="text-danger">*</span></label>
                            <select class="form-select" name="status_kepegawaian" required>
                                <option value="Dosen Tetap">Dosen Tetap</option>
                                <option value="Dosen Tidak Tetap">Dosen Tidak Tetap</option>
                                <option value="Dosen Tamu">Dosen Tamu</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NUPTK</label>
                            <input type="text" class="form-control" name="nuptk" value="{{ old('nuptk') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jabatan Akademik (Jafung)</label>
                            <select class="form-select" name="jabatan_akademik">
                                <option value="">- Pilih -</option>
                                <option value="Tenaga Pengajar">Tenaga Pengajar</option>
                                <option value="Asisten Ahli">Asisten Ahli</option>
                                <option value="Lektor">Lektor</option>
                                <option value="Lektor Kepala">Lektor Kepala</option>
                                <option value="Guru Besar">Guru Besar</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pangkat / Golongan</label>
                            <input type="text" class="form-control" name="pangkat_golongan" value="{{ old('pangkat_golongan') }}" placeholder="Contoh: III/b">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. SK Pengangkatan</label>
                            <input type="text" class="form-control" name="no_sk_pengangkatan" value="{{ old('no_sk_pengangkatan') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">TMT SK Pengangkatan</label>
                            <input type="date" class="form-control" name="tmt_sk_pengangkatan" value="{{ old('tmt_sk_pengangkatan') }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Bidang Keahlian</label>
                            <input type="text" class="form-control" name="bidang_keahlian" value="{{ old('bidang_keahlian') }}">
                        </div>
                    </div>
                </div></div>
            </div>

            {{-- TAB 3: AKUN --}}
            <div class="tab-pane fade" id="akun">
                <div class="card shadow-sm border-0"><div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Email Login <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Institusi (Opsional)</label>
                            <input type="email" class="form-control" name="email_institusi" value="{{ old('email_institusi') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" name="password_confirmation" required>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" id="is_keuangan" name="is_keuangan" value="1">
                                <label class="form-check-label" for="is_keuangan">Berikan Akses Modul Keuangan?</label>
                            </div>
                        </div>
                    </div>
                </div></div>
            </div>
        </div>

        <div class="mt-4 mb-5 text-end">
            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save"></i> Simpan Data Dosen</button>
        </div>
    </form>
</div>
@endsection
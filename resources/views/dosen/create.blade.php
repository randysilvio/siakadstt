@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Registrasi Dosen Baru</h3>
            <span class="text-muted small">Penambahan entitas tenaga pendidik ke dalam basis data</span>
        </div>
        <a href="{{ route('admin.dosen.index') }}" class="btn btn-outline-dark btn-sm rounded-0">KEMBALI</a>
    </div>

    <form action="{{ route('admin.dosen.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <ul class="nav nav-tabs mb-4 border-bottom-2" id="dosenTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active fw-bold text-dark rounded-0 uppercase small" data-bs-toggle="tab" data-bs-target="#pribadi" type="button">1. Identitas Pribadi</button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold text-dark rounded-0 uppercase small" data-bs-toggle="tab" data-bs-target="#kepegawaian" type="button">2. Status Kepegawaian</button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold text-dark rounded-0 uppercase small" data-bs-toggle="tab" data-bs-target="#akun" type="button">3. Kredensial & Foto</button>
            </li>
        </ul>

        <div class="tab-content">
            {{-- TAB 1: PRIBADI --}}
            <div class="tab-pane fade show active" id="pribadi">
                <div class="card border-0 shadow-sm rounded-0">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">Nama Lengkap (Beserta Gelar) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-0" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">NIDN / NIDK <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-0 font-monospace" name="nidn" value="{{ old('nidn') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">NIK (KTP) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-0 font-monospace" name="nik" value="{{ old('nik') }}" maxlength="16" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">NPWP</label>
                                <input type="text" class="form-control rounded-0 font-monospace" name="npwp" value="{{ old('npwp') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase">Tempat Lahir</label>
                                <input type="text" class="form-control rounded-0" name="tempat_lahir" value="{{ old('tempat_lahir') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase">Tanggal Lahir</label>
                                <input type="date" class="form-control rounded-0" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select class="form-select rounded-0" name="jenis_kelamin" required>
                                    <option value="L">LAKI-LAKI</option>
                                    <option value="P">PEREMPUAN</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold uppercase">Alamat Domisili Sesuai KTP</label>
                                <textarea class="form-control rounded-0" name="alamat" rows="2">{{ old('alamat') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 2: KEPEGAWAIAN --}}
            <div class="tab-pane fade" id="kepegawaian">
                <div class="card border-0 shadow-sm rounded-0">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">Status Ikatan Kerja <span class="text-danger">*</span></label>
                                <select class="form-select rounded-0" name="status_kepegawaian" required>
                                    <option value="Dosen Tetap">DOSEN TETAP</option>
                                    <option value="Dosen Tidak Tetap">DOSEN TIDAK TETAP</option>
                                    <option value="Dosen Tamu">DOSEN TAMU</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">Homebase Program Studi</label>
                                <select class="form-select rounded-0" name="program_studi_id">
                                    <option value="">-- PILIH UNIT KERJA --</option>
                                    @foreach($programStudis as $prodi)
                                        <option value="{{ $prodi->id }}" {{ old('program_studi_id') == $prodi->id ? 'selected' : '' }}>
                                            {{ strtoupper($prodi->nama_prodi) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">Jabatan Akademik (JAFUNG)</label>
                                <select class="form-select rounded-0" name="jabatan_akademik">
                                    <option value="">-- TANPA JAFUNG --</option>
                                    <option value="Tenaga Pengajar">TENAGA PENGAJAR</option>
                                    <option value="Asisten Ahli">ASISTEN AHLI</option>
                                    <option value="Lektor">LEKTOR</option>
                                    <option value="Lektor Kepala">LEKTOR KEPALA</option>
                                    <option value="Guru Besar">GURU BESAR</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">Pangkat / Golongan</label>
                                <input type="text" class="form-control rounded-0" name="pangkat_golongan" value="{{ old('pangkat_golongan') }}" placeholder="Contoh: III/b">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold uppercase">Bidang Keahlian Spesifik</label>
                                <input type="text" class="form-control rounded-0" name="bidang_keahlian" value="{{ old('bidang_keahlian') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 3: AKUN & FOTO --}}
            <div class="tab-pane fade" id="akun">
                <div class="card border-0 shadow-sm rounded-0">
                    <div class="card-body p-4">
                        <div class="row g-4 justify-content-center">
                            <div class="col-md-10">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold uppercase">Alamat Email Login <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control rounded-0 font-monospace" name="email" value="{{ old('email') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold uppercase">Email Institusi (Opsional)</label>
                                        <input type="email" class="form-control rounded-0 font-monospace" name="email_institusi" value="{{ old('email_institusi') }}">
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold uppercase">Kata Sandi Baru <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control rounded-0" name="password" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold uppercase">Konfirmasi Kata Sandi <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control rounded-0" name="password_confirmation" required>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold uppercase">Pas Foto Profil</label>
                                        <input type="file" class="form-control rounded-0" name="foto_profil" accept="image/*">
                                        <div class="form-text small">Rekomendasi rasio 3:4, Maksimal 2MB.</div>
                                    </div>
                                    <div class="col-md-6 d-flex align-items-end">
                                        <div class="bg-light p-3 border w-100">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input ms-0" type="checkbox" id="is_keuangan" name="is_keuangan" value="1">
                                                <label class="form-check-label fw-bold small uppercase ms-2" for="is_keuangan">Otoritas Modul Keuangan</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 mb-5 text-end">
            <button type="submit" class="btn btn-primary px-5 rounded-0 fw-bold">SIMPAN DATA DOSEN</button>
        </div>
    </form>
</div>
@endsection
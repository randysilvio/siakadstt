@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Tambah Mahasiswa Baru</h2>
        <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    <form action="{{ route('admin.mahasiswa.store') }}" method="POST">
        @csrf

        {{-- Navigasi Tab --}}
        <ul class="nav nav-tabs mb-4" id="mahasiswaTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold" id="pribadi-tab" data-bs-toggle="tab" data-bs-target="#pribadi" type="button" role="tab">1. Data Pribadi</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="akademik-tab" data-bs-toggle="tab" data-bs-target="#akademik" type="button" role="tab">2. Akademik & Akun</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="alamat-tab" data-bs-toggle="tab" data-bs-target="#alamat" type="button" role="tab">3. Alamat Detail</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="ortu-tab" data-bs-toggle="tab" data-bs-target="#ortu" type="button" role="tab">4. Data Orang Tua</button>
            </li>
        </ul>

        {{-- Isi Tab --}}
        <div class="tab-content" id="myTabContent">
            
            {{-- TAB 1: DATA PRIBADI --}}
            <div class="tab-pane fade show active" id="pribadi" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">NIK (KTP) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nik" value="{{ old('nik') }}" maxlength="16" required placeholder="16 digit">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">NISN</label>
                                <input type="text" class="form-control" name="nisn" value="{{ old('nisn') }}" maxlength="10">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kewarganegaraan</label>
                                <select class="form-select" name="kewarganegaraan">
                                    <option value="WNI" selected>Indonesia (WNI)</option>
                                    <option value="WNA">Asing (WNA)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="tempat_lahir" value="{{ old('tempat_lahir') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select class="form-select" name="jenis_kelamin" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Agama</label>
                                <select class="form-select" name="agama">
                                    <option value="Kristen Protestan">Kristen Protestan</option>
                                    <option value="Katolik">Katolik</option>
                                    <option value="Islam">Islam</option>
                                    <option value="Hindu">Hindu</option>
                                    <option value="Buddha">Buddha</option>
                                    <option value="Konghucu">Konghucu</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor Telepon / WA</label>
                                <input type="text" class="form-control" name="nomor_telepon" value="{{ old('nomor_telepon') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 2: AKADEMIK & AKUN --}}
            <div class="tab-pane fade" id="akademik" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">Informasi Akademik</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">NIM <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nim" value="{{ old('nim') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Program Studi <span class="text-danger">*</span></label>
                                <select class="form-select" name="program_studi_id" required>
                                    <option value="">-- Pilih Prodi --</option>
                                    @foreach ($program_studis as $prodi)
                                        <option value="{{ $prodi->id }}">{{ $prodi->nama_prodi }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Angkatan (Tahun Masuk) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="tahun_masuk" value="{{ date('Y') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Dosen Wali</label>
                                <select class="form-select" name="dosen_wali_id">
                                    <option value="">-- Pilih Dosen --</option>
                                    @foreach ($dosens as $dosen)
                                        <option value="{{ $dosen->id }}">{{ $dosen->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jalur Pendaftaran</label>
                                <select class="form-select" name="jalur_pendaftaran">
                                    <option value="Mandiri">Mandiri</option>
                                    <option value="Beasiswa">Beasiswa</option>
                                    <option value="Prestasi">Prestasi</option>
                                    <option value="Kerjasama">Kerjasama</option>
                                </select>
                            </div>
                        </div>

                        <hr>
                        <h5 class="card-title text-primary mb-3">Akun Login Sistem</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password_confirmation" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 3: ALAMAT LENGKAP --}}
            <div class="tab-pane fade" id="alamat" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Jalan / Alamat Lengkap</label>
                                <textarea class="form-control" name="alamat" rows="2">{{ old('alamat') }}</textarea>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Dusun / Lingkungan</label>
                                <input type="text" class="form-control" name="dusun" value="{{ old('dusun') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">RT / RW</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="rt" placeholder="RT" value="{{ old('rt') }}">
                                    <span class="input-group-text">/</span>
                                    <input type="text" class="form-control" name="rw" placeholder="RW" value="{{ old('rw') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Kelurahan / Desa</label>
                                <input type="text" class="form-control" name="kelurahan" value="{{ old('kelurahan') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Kecamatan</label>
                                <input type="text" class="form-control" name="kecamatan" value="{{ old('kecamatan') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Kode Pos</label>
                                <input type="text" class="form-control" name="kode_pos" value="{{ old('kode_pos') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Jenis Tinggal</label>
                                <select class="form-select" name="jenis_tinggal">
                                    <option value="">Pilih...</option>
                                    <option value="Bersama Orang Tua">Bersama Orang Tua</option>
                                    <option value="Wali">Wali</option>
                                    <option value="Kos">Kos</option>
                                    <option value="Asrama">Asrama</option>
                                    <option value="Panti Asuhan">Panti Asuhan</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Alat Transportasi</label>
                                <select class="form-select" name="alat_transportasi">
                                    <option value="">Pilih...</option>
                                    <option value="Jalan Kaki">Jalan Kaki</option>
                                    <option value="Kendaraan Pribadi">Kendaraan Pribadi (Motor/Mobil)</option>
                                    <option value="Angkutan Umum">Angkutan Umum</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 4: DATA ORANG TUA --}}
            <div class="tab-pane fade" id="ortu" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        
                        {{-- Data Ibu --}}
                        <h5 class="card-title text-primary"><i class="bi bi-gender-female"></i> Data Ibu Kandung</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Nama Ibu Kandung <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_ibu_kandung" value="{{ old('nama_ibu_kandung') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">NIK Ibu</label>
                                <input type="text" class="form-control" name="nik_ibu" maxlength="16" value="{{ old('nik_ibu') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pendidikan Ibu</label>
                                <select class="form-select" name="pendidikan_ibu">
                                    <option value="">Pilih...</option>
                                    <option value="SD">SD</option>
                                    <option value="SMP">SMP</option>
                                    <option value="SMA">SMA/Sederajat</option>
                                    <option value="D3">D3</option>
                                    <option value="S1">S1</option>
                                    <option value="S2">S2</option>
                                    <option value="S3">S3</option>
                                    <option value="Tidak Sekolah">Tidak Sekolah</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pekerjaan Ibu</label>
                                <select class="form-select" name="pekerjaan_ibu">
                                    <option value="">Pilih...</option>
                                    <option value="Tidak Bekerja">Tidak Bekerja</option>
                                    <option value="PNS">PNS</option>
                                    <option value="Wiraswasta">Wiraswasta</option>
                                    <option value="Petani">Petani</option>
                                    <option value="Nelayan">Nelayan</option>
                                    <option value="Karyawan Swasta">Karyawan Swasta</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Penghasilan Ibu</label>
                                <select class="form-select" name="penghasilan_ibu">
                                    <option value="">Pilih...</option>
                                    <option value="Kurang dari 500rb">Kurang dari 500rb</option>
                                    <option value="500rb - 1 Juta">500rb - 1 Juta</option>
                                    <option value="1 Juta - 2 Juta">1 Juta - 2 Juta</option>
                                    <option value="2 Juta - 5 Juta">2 Juta - 5 Juta</option>
                                    <option value="Lebih dari 5 Juta">Lebih dari 5 Juta</option>
                                </select>
                            </div>
                        </div>

                        <hr>

                        {{-- Data Ayah --}}
                        <h5 class="card-title text-primary mt-3"><i class="bi bi-gender-male"></i> Data Ayah</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Ayah</label>
                                <input type="text" class="form-control" name="nama_ayah" value="{{ old('nama_ayah') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">NIK Ayah</label>
                                <input type="text" class="form-control" name="nik_ayah" maxlength="16" value="{{ old('nik_ayah') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pendidikan Ayah</label>
                                <select class="form-select" name="pendidikan_ayah">
                                    <option value="">Pilih...</option>
                                    <option value="SD">SD</option>
                                    <option value="SMP">SMP</option>
                                    <option value="SMA">SMA/Sederajat</option>
                                    <option value="D3">D3</option>
                                    <option value="S1">S1</option>
                                    <option value="S2">S2</option>
                                    <option value="S3">S3</option>
                                    <option value="Tidak Sekolah">Tidak Sekolah</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pekerjaan Ayah</label>
                                <select class="form-select" name="pekerjaan_ayah">
                                    <option value="">Pilih...</option>
                                    <option value="Tidak Bekerja">Tidak Bekerja</option>
                                    <option value="PNS">PNS</option>
                                    <option value="Wiraswasta">Wiraswasta</option>
                                    <option value="Petani">Petani</option>
                                    <option value="Nelayan">Nelayan</option>
                                    <option value="Karyawan Swasta">Karyawan Swasta</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Penghasilan Ayah</label>
                                <select class="form-select" name="penghasilan_ayah">
                                    <option value="">Pilih...</option>
                                    <option value="Kurang dari 500rb">Kurang dari 500rb</option>
                                    <option value="500rb - 1 Juta">500rb - 1 Juta</option>
                                    <option value="1 Juta - 2 Juta">1 Juta - 2 Juta</option>
                                    <option value="2 Juta - 5 Juta">2 Juta - 5 Juta</option>
                                    <option value="Lebih dari 5 Juta">Lebih dari 5 Juta</option>
                                </select>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <div class="mt-4 mb-5 text-end">
            <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-light border me-2">Batal</a>
            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Simpan Data Mahasiswa</button>
        </div>
    </form>
</div>
@endsection
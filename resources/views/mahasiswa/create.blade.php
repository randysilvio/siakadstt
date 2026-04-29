@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Tambah Mahasiswa Baru</h2>
        <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    <form action="{{ route('admin.mahasiswa.store') }}" method="POST">
        @csrf

        {{-- NOTIFIKASI ERROR VALIDASI GLOBAL --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                    <div>
                        <strong>Gagal Menyimpan!</strong> Silakan periksa kembali isian Anda:
                        <ul class="mb-0 mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

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
                                    <option value="WNI" {{ old('kewarganegaraan', 'WNI') == 'WNI' ? 'selected' : '' }}>Indonesia (WNI)</option>
                                    <option value="WNA" {{ old('kewarganegaraan') == 'WNA' ? 'selected' : '' }}>Asing (WNA)</option>
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
                                    <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Agama</label>
                                <select class="form-select" name="agama">
                                    <option value="">-- Pilih --</option>
                                    @foreach(['Kristen Protestan', 'Katolik', 'Islam', 'Hindu', 'Buddha', 'Konghucu'] as $agama)
                                        <option value="{{ $agama }}" {{ old('agama') == $agama ? 'selected' : '' }}>{{ $agama }}</option>
                                    @endforeach
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
                                        <option value="{{ $prodi->id }}" {{ old('program_studi_id') == $prodi->id ? 'selected' : '' }}>{{ $prodi->nama_prodi }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Angkatan (Tahun Masuk) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="tahun_masuk" value="{{ old('tahun_masuk', date('Y')) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Dosen Wali</label>
                                <select class="form-select" name="dosen_wali_id">
                                    <option value="">-- Pilih Dosen --</option>
                                    @foreach ($dosens as $dosen)
                                        <option value="{{ $dosen->id }}" {{ old('dosen_wali_id') == $dosen->id ? 'selected' : '' }}>{{ $dosen->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jalur Pendaftaran</label>
                                <select class="form-select" name="jalur_pendaftaran">
                                    <option value="">-- Pilih --</option>
                                    @foreach(['Mandiri', 'Beasiswa', 'Prestasi', 'Kerjasama'] as $jalur)
                                        <option value="{{ $jalur }}" {{ old('jalur_pendaftaran') == $jalur ? 'selected' : '' }}>{{ $jalur }}</option>
                                    @endforeach
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
                                    @foreach(['Bersama Orang Tua', 'Wali', 'Kos', 'Asrama', 'Panti Asuhan'] as $jt)
                                        <option value="{{ $jt }}" {{ old('jenis_tinggal') == $jt ? 'selected' : '' }}>{{ $jt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Alat Transportasi</label>
                                <select class="form-select" name="alat_transportasi">
                                    <option value="">Pilih...</option>
                                    @foreach(['Jalan Kaki', 'Kendaraan Pribadi', 'Angkutan Umum'] as $at)
                                        <option value="{{ $at }}" {{ old('alat_transportasi') == $at ? 'selected' : '' }}>{{ $at }}</option>
                                    @endforeach
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
                                    @foreach(['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3', 'Tidak Sekolah'] as $p)
                                        <option value="{{ $p }}" {{ old('pendidikan_ibu') == $p ? 'selected' : '' }}>{{ $p }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pekerjaan Ibu</label>
                                <select class="form-select" name="pekerjaan_ibu">
                                    <option value="">Pilih...</option>
                                    @foreach(['Tidak Bekerja', 'PNS', 'Wiraswasta', 'Petani', 'Nelayan', 'Karyawan Swasta'] as $pk)
                                        <option value="{{ $pk }}" {{ old('pekerjaan_ibu') == $pk ? 'selected' : '' }}>{{ $pk }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Penghasilan Ibu</label>
                                <select class="form-select" name="penghasilan_ibu">
                                    <option value="">Pilih...</option>
                                    @foreach(['Kurang dari 500rb', '500rb - 1 Juta', '1 Juta - 2 Juta', '2 Juta - 5 Juta', 'Lebih dari 5 Juta'] as $ph)
                                        <option value="{{ $ph }}" {{ old('penghasilan_ibu') == $ph ? 'selected' : '' }}>{{ $ph }}</option>
                                    @endforeach
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
                                    @foreach(['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3', 'Tidak Sekolah'] as $p)
                                        <option value="{{ $p }}" {{ old('pendidikan_ayah') == $p ? 'selected' : '' }}>{{ $p }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pekerjaan Ayah</label>
                                <select class="form-select" name="pekerjaan_ayah">
                                    <option value="">Pilih...</option>
                                    @foreach(['Tidak Bekerja', 'PNS', 'Wiraswasta', 'Petani', 'Nelayan', 'Karyawan Swasta'] as $pk)
                                        <option value="{{ $pk }}" {{ old('pekerjaan_ayah') == $pk ? 'selected' : '' }}>{{ $pk }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Penghasilan Ayah</label>
                                <select class="form-select" name="penghasilan_ayah">
                                    <option value="">Pilih...</option>
                                    @foreach(['Kurang dari 500rb', '500rb - 1 Juta', '1 Juta - 2 Juta', '2 Juta - 5 Juta', 'Lebih dari 5 Juta'] as $ph)
                                        <option value="{{ $ph }}" {{ old('penghasilan_ayah') == $ph ? 'selected' : '' }}>{{ $ph }}</option>
                                    @endforeach
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
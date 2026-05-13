@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Edit Data Mahasiswa</h3>
            <span class="text-muted small uppercase">Pembaruan Master Data & Kredensial</span>
        </div>
        <div>
            <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    <form action="{{ route('admin.mahasiswa.update', $mahasiswa->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- NOTIFIKASI ERROR VALIDASI GLOBAL --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3" role="alert">
                <div class="d-flex align-items-center small uppercase">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>
                    <div>
                        <strong class="fw-bold">Gagal Menyimpan!</strong> Silakan periksa kembali isian Anda:
                        <ul class="mb-0 mt-1 font-monospace ps-3 text-lowercase">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Navigasi Tab Flat --}}
        <ul class="nav nav-tabs mb-4 rounded-0" id="mahasiswaTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-0 fw-bold px-3 small uppercase text-dark" id="pribadi-tab" data-bs-toggle="tab" data-bs-target="#pribadi" type="button" role="tab">1. Data Pribadi</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-0 fw-bold px-3 small uppercase text-dark" id="akademik-tab" data-bs-toggle="tab" data-bs-target="#akademik" type="button" role="tab">2. Akademik & Akun</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-0 fw-bold px-3 small uppercase text-dark" id="alamat-tab" data-bs-toggle="tab" data-bs-target="#alamat" type="button" role="tab">3. Alamat Detail</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-0 fw-bold px-3 small uppercase text-dark" id="ortu-tab" data-bs-toggle="tab" data-bs-target="#ortu" type="button" role="tab">4. Data Orang Tua</button>
            </li>
        </ul>

        <div class="tab-content">
            
            {{-- TAB 1: PRIBADI --}}
            <div class="tab-pane fade show active" id="pribadi" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                        Formulir Identitas Pribadi
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label uppercase fw-bold small text-dark">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-0 uppercase" name="nama_lengkap" value="{{ old('nama_lengkap', $mahasiswa->nama_lengkap) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label uppercase fw-bold small text-dark">NIK (KTP) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-0 font-monospace" name="nik" value="{{ old('nik', $mahasiswa->nik) }}" required minlength="16" maxlength="16">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label uppercase fw-bold small text-dark">NISN</label>
                                <input type="text" class="form-control rounded-0 font-monospace" name="nisn" value="{{ old('nisn', $mahasiswa->nisn) }}" maxlength="12">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label uppercase fw-bold small text-dark">Kewarganegaraan <span class="text-danger">*</span></label>
                                <select class="form-select rounded-0 uppercase" name="kewarganegaraan" required>
                                    <option value="WNI" {{ old('kewarganegaraan', $mahasiswa->kewarganegaraan) == 'WNI' ? 'selected' : '' }}>Indonesia (WNI)</option>
                                    <option value="WNA" {{ old('kewarganegaraan', $mahasiswa->kewarganegaraan) == 'WNA' ? 'selected' : '' }}>Asing (WNA)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label uppercase fw-bold small text-dark">Tempat Lahir <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-0 uppercase" name="tempat_lahir" value="{{ old('tempat_lahir', $mahasiswa->tempat_lahir) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label uppercase fw-bold small text-dark">Tanggal Lahir <span class="text-danger">*</span></label>
                                <input type="date" class="form-control rounded-0 font-monospace" name="tanggal_lahir" value="{{ old('tanggal_lahir', $mahasiswa->tanggal_lahir) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label uppercase fw-bold small text-dark">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select class="form-select rounded-0 uppercase" name="jenis_kelamin" required>
                                    <option value="L" {{ old('jenis_kelamin', $mahasiswa->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('jenis_kelamin', $mahasiswa->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label uppercase fw-bold small text-dark">Agama</label>
                                <select class="form-select rounded-0 uppercase" name="agama">
                                    <option value="">-- PILIH --</option>
                                    @foreach(['Kristen Protestan', 'Katolik', 'Islam', 'Hindu', 'Buddha', 'Konghucu'] as $agama)
                                        <option value="{{ $agama }}" {{ old('agama', $mahasiswa->agama) == $agama ? 'selected' : '' }}>{{ $agama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label uppercase fw-bold small text-dark">Nomor Telepon / WA</label>
                                <input type="text" class="form-control rounded-0 font-monospace" name="nomor_telepon" value="{{ old('nomor_telepon', $mahasiswa->nomor_telepon) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 2: AKADEMIK --}}
            <div class="tab-pane fade" id="akademik" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                        Parameter Akademik & Kredensial Akses
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label uppercase fw-bold small text-dark">NIM <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-0 font-monospace uppercase" name="nim" value="{{ old('nim', $mahasiswa->nim) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label uppercase fw-bold small text-dark">Program Studi <span class="text-danger">*</span></label>
                                <select class="form-select rounded-0 uppercase" name="program_studi_id" required>
                                    @foreach ($program_studis as $prodi)
                                        <option value="{{ $prodi->id }}" {{ old('program_studi_id', $mahasiswa->program_studi_id) == $prodi->id ? 'selected' : '' }}>{{ $prodi->nama_prodi }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label uppercase fw-bold small text-dark">Status Mahasiswa <span class="text-danger">*</span></label>
                                <select class="form-select rounded-0 uppercase" name="status_mahasiswa" required>
                                    @foreach(['Aktif', 'Cuti', 'Lulus', 'Drop Out', 'Non-Aktif', 'Keluar'] as $status)
                                        <option value="{{ $status }}" {{ old('status_mahasiswa', $mahasiswa->status_mahasiswa) == $status ? 'selected' : '' }}>{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label uppercase fw-bold small text-dark">Dosen Wali</label>
                                <select class="form-select rounded-0 uppercase" name="dosen_wali_id">
                                    <option value="">-- PILIH DOSEN --</option>
                                    @foreach ($dosens as $dosen)
                                        <option value="{{ $dosen->id }}" {{ old('dosen_wali_id', $mahasiswa->dosen_wali_id) == $dosen->id ? 'selected' : '' }}>{{ $dosen->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label uppercase fw-bold small text-dark">Angkatan <span class="text-danger">*</span></label>
                                <input type="number" class="form-control rounded-0 font-monospace" name="tahun_masuk" value="{{ old('tahun_masuk', $mahasiswa->tahun_masuk) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label uppercase fw-bold small text-dark">Jalur Daftar</label>
                                <select class="form-select rounded-0 uppercase" name="jalur_pendaftaran">
                                    @foreach(['Mandiri', 'Beasiswa', 'Prestasi', 'Kerjasama'] as $jalur)
                                        <option value="{{ $jalur }}" {{ old('jalur_pendaftaran', $mahasiswa->jalur_pendaftaran) == $jalur ? 'selected' : '' }}>{{ $jalur }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <h6 class="uppercase fw-bold small text-dark mb-3 border-bottom pb-2 mt-4">Kredensial Login</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label uppercase fw-bold small text-dark">Email Login <span class="text-danger">*</span></label>
                                <input type="email" class="form-control rounded-0" name="email" value="{{ old('email', optional($mahasiswa->user)->email) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label uppercase fw-bold small text-dark">Password Baru (Opsional)</label>
                                <input type="password" class="form-control rounded-0 font-monospace" name="password" placeholder="ISI JIKA INGIN GANTI PASSWORD">
                            </div>
                            <div class="col-md-6 offset-md-6">
                                <label class="form-label uppercase fw-bold small text-dark">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control rounded-0 font-monospace" name="password_confirmation" placeholder="ULANGI PASSWORD BARU">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 3: ALAMAT --}}
            <div class="tab-pane fade" id="alamat" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                        Detail Domisili & Transportasi
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label uppercase fw-bold small text-dark">Alamat Lengkap</label>
                                <textarea class="form-control rounded-0 uppercase" name="alamat" rows="2">{{ old('alamat', $mahasiswa->alamat) }}</textarea>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label uppercase fw-bold small text-dark">Dusun</label>
                                <input type="text" class="form-control rounded-0 uppercase" name="dusun" value="{{ old('dusun', $mahasiswa->dusun) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label uppercase fw-bold small text-dark">RT / RW</label>
                                <div class="input-group">
                                    <input type="text" class="form-control rounded-0 font-monospace text-center" name="rt" value="{{ old('rt', $mahasiswa->rt) }}" placeholder="RT">
                                    <span class="input-group-text rounded-0 bg-light">/</span>
                                    <input type="text" class="form-control rounded-0 font-monospace text-center" name="rw" value="{{ old('rw', $mahasiswa->rw) }}" placeholder="RW">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label uppercase fw-bold small text-dark">Kelurahan</label>
                                <input type="text" class="form-control rounded-0 uppercase" name="kelurahan" value="{{ old('kelurahan', $mahasiswa->kelurahan) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label uppercase fw-bold small text-dark">Kecamatan</label>
                                <input type="text" class="form-control rounded-0 uppercase" name="kecamatan" value="{{ old('kecamatan', $mahasiswa->kecamatan) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label uppercase fw-bold small text-dark">Kode Pos</label>
                                <input type="text" class="form-control rounded-0 font-monospace" name="kode_pos" value="{{ old('kode_pos', $mahasiswa->kode_pos) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label uppercase fw-bold small text-dark">Jenis Tinggal</label>
                                <select class="form-select rounded-0 uppercase" name="jenis_tinggal">
                                    <option value="">-- PILIH --</option>
                                    @foreach(['Bersama Orang Tua', 'Wali', 'Kos', 'Asrama', 'Panti Asuhan'] as $jt)
                                        <option value="{{ $jt }}" {{ old('jenis_tinggal', $mahasiswa->jenis_tinggal) == $jt ? 'selected' : '' }}>{{ $jt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label uppercase fw-bold small text-dark">Alat Transportasi</label>
                                <select class="form-select rounded-0 uppercase" name="alat_transportasi">
                                    <option value="">-- PILIH --</option>
                                    @foreach(['Jalan Kaki', 'Kendaraan Pribadi', 'Angkutan Umum'] as $at)
                                        <option value="{{ $at }}" {{ old('alat_transportasi', $mahasiswa->alat_transportasi) == $at ? 'selected' : '' }}>{{ $at }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 4: ORANG TUA --}}
            <div class="tab-pane fade" id="ortu" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                        Data Latar Belakang Orang Tua
                    </div>
                    <div class="card-body p-4">
                        <h6 class="uppercase fw-bold small text-dark mb-3 border-bottom pb-2">Data Ibu Kandung</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label uppercase fw-bold small text-dark">Nama Ibu <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-0 uppercase" name="nama_ibu_kandung" value="{{ old('nama_ibu_kandung', $mahasiswa->nama_ibu_kandung) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label uppercase fw-bold small text-dark">NIK Ibu</label>
                                <input type="text" class="form-control rounded-0 font-monospace" name="nik_ibu" maxlength="16" value="{{ old('nik_ibu', $mahasiswa->nik_ibu) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label uppercase fw-bold small text-dark">Pendidikan Ibu</label>
                                <select class="form-select rounded-0 uppercase" name="pendidikan_ibu">
                                    <option value="">-- PILIH --</option>
                                    @foreach(['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3', 'Tidak Sekolah'] as $p)
                                        <option value="{{ $p }}" {{ old('pendidikan_ibu', $mahasiswa->pendidikan_ibu) == $p ? 'selected' : '' }}>{{ $p }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label uppercase fw-bold small text-dark">Pekerjaan Ibu</label>
                                <select class="form-select rounded-0 uppercase" name="pekerjaan_ibu">
                                    <option value="">-- PILIH --</option>
                                    @foreach(['Tidak Bekerja', 'PNS', 'Wiraswasta', 'Petani', 'Nelayan', 'Karyawan Swasta'] as $pk)
                                        <option value="{{ $pk }}" {{ old('pekerjaan_ibu', $mahasiswa->pekerjaan_ibu) == $pk ? 'selected' : '' }}>{{ $pk }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label uppercase fw-bold small text-dark">Penghasilan Ibu</label>
                                <select class="form-select rounded-0 uppercase" name="penghasilan_ibu">
                                    <option value="">-- PILIH --</option>
                                    @foreach(['Kurang dari 500rb', '500rb - 1 Juta', '1 Juta - 2 Juta', '2 Juta - 5 Juta', 'Lebih dari 5 Juta'] as $ph)
                                        <option value="{{ $ph }}" {{ old('penghasilan_ibu', $mahasiswa->penghasilan_ibu) == $ph ? 'selected' : '' }}>{{ $ph }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <h6 class="uppercase fw-bold small text-dark mb-3 border-bottom pb-2 mt-4">Data Ayah</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label uppercase fw-bold small text-dark">Nama Ayah</label>
                                <input type="text" class="form-control rounded-0 uppercase" name="nama_ayah" value="{{ old('nama_ayah', $mahasiswa->nama_ayah) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label uppercase fw-bold small text-dark">NIK Ayah</label>
                                <input type="text" class="form-control rounded-0 font-monospace" name="nik_ayah" maxlength="16" value="{{ old('nik_ayah', $mahasiswa->nik_ayah) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label uppercase fw-bold small text-dark">Pendidikan Ayah</label>
                                <select class="form-select rounded-0 uppercase" name="pendidikan_ayah">
                                    <option value="">-- PILIH --</option>
                                    @foreach(['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3', 'Tidak Sekolah'] as $p)
                                        <option value="{{ $p }}" {{ old('pendidikan_ayah', $mahasiswa->pendidikan_ayah) == $p ? 'selected' : '' }}>{{ $p }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label uppercase fw-bold small text-dark">Pekerjaan Ayah</label>
                                <select class="form-select rounded-0 uppercase" name="pekerjaan_ayah">
                                    <option value="">-- PILIH --</option>
                                    @foreach(['Tidak Bekerja', 'PNS', 'Wiraswasta', 'Petani', 'Nelayan', 'Karyawan Swasta'] as $pk)
                                        <option value="{{ $pk }}" {{ old('pekerjaan_ayah', $mahasiswa->pekerjaan_ayah) == $pk ? 'selected' : '' }}>{{ $pk }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label uppercase fw-bold small text-dark">Penghasilan Ayah</label>
                                <select class="form-select rounded-0 uppercase" name="penghasilan_ayah">
                                    <option value="">-- PILIH --</option>
                                    @foreach(['Kurang dari 500rb', '500rb - 1 Juta', '1 Juta - 2 Juta', '2 Juta - 5 Juta', 'Lebih dari 5 Juta'] as $ph)
                                        <option value="{{ $ph }}" {{ old('penghasilan_ayah', $mahasiswa->penghasilan_ayah) == $ph ? 'selected' : '' }}>{{ $ph }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="d-flex justify-content-end mb-5">
            <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
            <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                <i class="bi bi-save me-1"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
        <h3 class="mb-0 fw-bold text-teal-700">Edit Data Mahasiswa</h3>
        <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    <form action="{{ route('admin.mahasiswa.update', $mahasiswa->id) }}" method="POST">
        @csrf
        @method('PUT')

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
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Navigasi Tab --}}
        <ul class="nav nav-tabs mb-4" id="mahasiswaTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active fw-bold" id="pribadi-tab" data-bs-toggle="tab" data-bs-target="#pribadi" type="button">1. Data Pribadi</button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold" id="akademik-tab" data-bs-toggle="tab" data-bs-target="#akademik" type="button">2. Akademik & Akun</button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold" id="alamat-tab" data-bs-toggle="tab" data-bs-target="#alamat" type="button">3. Alamat Detail</button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold" id="ortu-tab" data-bs-toggle="tab" data-bs-target="#ortu" type="button">4. Data Orang Tua</button>
            </li>
        </ul>

        <div class="tab-content">
            
            {{-- TAB 1: PRIBADI --}}
            <div class="tab-pane fade show active" id="pribadi">
                <div class="card shadow-sm border-0"><div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_lengkap" value="{{ old('nama_lengkap', $mahasiswa->nama_lengkap) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIK (KTP) <span class="text-danger">*</span></label>
                            {{-- PERBAIKAN: value memanggil $mahasiswa->nik --}}
                            <input type="text" class="form-control" name="nik" value="{{ old('nik', $mahasiswa->nik) }}" required minlength="16" maxlength="16">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NISN</label>
                            <input type="text" class="form-control" name="nisn" value="{{ old('nisn', $mahasiswa->nisn) }}" maxlength="12">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kewarganegaraan <span class="text-danger">*</span></label>
                            <select class="form-select" name="kewarganegaraan" required>
                                <option value="WNI" {{ old('kewarganegaraan', $mahasiswa->kewarganegaraan) == 'WNI' ? 'selected' : '' }}>Indonesia (WNI)</option>
                                <option value="WNA" {{ old('kewarganegaraan', $mahasiswa->kewarganegaraan) == 'WNA' ? 'selected' : '' }}>Asing (WNA)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="tempat_lahir" value="{{ old('tempat_lahir', $mahasiswa->tempat_lahir) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal_lahir" value="{{ old('tanggal_lahir', $mahasiswa->tanggal_lahir) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select class="form-select" name="jenis_kelamin" required>
                                <option value="L" {{ old('jenis_kelamin', $mahasiswa->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('jenis_kelamin', $mahasiswa->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Agama</label>
                            <select class="form-select" name="agama">
                                <option value="">-- Pilih --</option>
                                @foreach(['Kristen Protestan', 'Katolik', 'Islam', 'Hindu', 'Buddha', 'Konghucu'] as $agama)
                                    <option value="{{ $agama }}" {{ old('agama', $mahasiswa->agama) == $agama ? 'selected' : '' }}>{{ $agama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nomor Telepon / WA</label>
                            <input type="text" class="form-control" name="nomor_telepon" value="{{ old('nomor_telepon', $mahasiswa->nomor_telepon) }}">
                        </div>
                    </div>
                </div></div>
            </div>

            {{-- TAB 2: AKADEMIK --}}
            <div class="tab-pane fade" id="akademik">
                <div class="card shadow-sm border-0"><div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">NIM <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nim" value="{{ old('nim', $mahasiswa->nim) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Program Studi <span class="text-danger">*</span></label>
                            <select class="form-select" name="program_studi_id" required>
                                @foreach ($program_studis as $prodi)
                                    <option value="{{ $prodi->id }}" {{ old('program_studi_id', $mahasiswa->program_studi_id) == $prodi->id ? 'selected' : '' }}>{{ $prodi->nama_prodi }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status Mahasiswa <span class="text-danger">*</span></label>
                            <select class="form-select" name="status_mahasiswa" required>
                                @foreach(['Aktif', 'Cuti', 'Lulus', 'Drop Out', 'Non-Aktif', 'Keluar'] as $status)
                                    <option value="{{ $status }}" {{ old('status_mahasiswa', $mahasiswa->status_mahasiswa) == $status ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Dosen Wali</label>
                            <select class="form-select" name="dosen_wali_id">
                                <option value="">-- Pilih Dosen --</option>
                                @foreach ($dosens as $dosen)
                                    <option value="{{ $dosen->id }}" {{ old('dosen_wali_id', $mahasiswa->dosen_wali_id) == $dosen->id ? 'selected' : '' }}>{{ $dosen->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Angkatan (Tahun Masuk) <span class="text-danger">*</span></label>
                            {{-- PERBAIKAN: Input name tahun_masuk dan value memanggil $mahasiswa->tahun_masuk --}}
                            <input type="number" class="form-control" name="tahun_masuk" value="{{ old('tahun_masuk', $mahasiswa->tahun_masuk) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Jalur Daftar</label>
                            <select class="form-select" name="jalur_pendaftaran">
                                @foreach(['Mandiri', 'Beasiswa', 'Prestasi', 'Kerjasama'] as $jalur)
                                    <option value="{{ $jalur }}" {{ old('jalur_pendaftaran', $mahasiswa->jalur_pendaftaran) == $jalur ? 'selected' : '' }}>{{ $jalur }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <hr>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Email Login <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', optional($mahasiswa->user)->email) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password Baru (Opsional)</label>
                            <input type="password" class="form-control" name="password" placeholder="Isi hanya jika ingin ganti password">
                        </div>
                        <div class="col-md-6 offset-md-6">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" name="password_confirmation" placeholder="Ulangi password baru">
                        </div>
                    </div>
                </div></div>
            </div>

            {{-- TAB 3: ALAMAT --}}
            <div class="tab-pane fade" id="alamat">
                <div class="card shadow-sm border-0"><div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Alamat Lengkap</label>
                            <textarea class="form-control" name="alamat" rows="2">{{ old('alamat', $mahasiswa->alamat) }}</textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Dusun</label>
                            <input type="text" class="form-control" name="dusun" value="{{ old('dusun', $mahasiswa->dusun) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">RT/RW</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="rt" value="{{ old('rt', $mahasiswa->rt) }}" placeholder="RT">
                                <span class="input-group-text">/</span>
                                <input type="text" class="form-control" name="rw" value="{{ old('rw', $mahasiswa->rw) }}" placeholder="RW">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Kelurahan</label>
                            <input type="text" class="form-control" name="kelurahan" value="{{ old('kelurahan', $mahasiswa->kelurahan) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Kecamatan</label>
                            <input type="text" class="form-control" name="kecamatan" value="{{ old('kecamatan', $mahasiswa->kecamatan) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kode Pos</label>
                            <input type="text" class="form-control" name="kode_pos" value="{{ old('kode_pos', $mahasiswa->kode_pos) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jenis Tinggal</label>
                            <select class="form-select" name="jenis_tinggal">
                                <option value="">Pilih...</option>
                                @foreach(['Bersama Orang Tua', 'Wali', 'Kos', 'Asrama', 'Panti Asuhan'] as $jt)
                                    <option value="{{ $jt }}" {{ old('jenis_tinggal', $mahasiswa->jenis_tinggal) == $jt ? 'selected' : '' }}>{{ $jt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Alat Transportasi</label>
                            <select class="form-select" name="alat_transportasi">
                                <option value="">Pilih...</option>
                                @foreach(['Jalan Kaki', 'Kendaraan Pribadi', 'Angkutan Umum'] as $at)
                                    <option value="{{ $at }}" {{ old('alat_transportasi', $mahasiswa->alat_transportasi) == $at ? 'selected' : '' }}>{{ $at }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div></div>
            </div>

            {{-- TAB 4: ORANG TUA --}}
            <div class="tab-pane fade" id="ortu">
                <div class="card shadow-sm border-0"><div class="card-body">
                    <h5 class="text-primary">Data Ibu Kandung</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Nama Ibu <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_ibu_kandung" value="{{ old('nama_ibu_kandung', $mahasiswa->nama_ibu_kandung) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIK Ibu</label>
                            <input type="text" class="form-control" name="nik_ibu" maxlength="16" value="{{ old('nik_ibu', $mahasiswa->nik_ibu) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pendidikan Ibu</label>
                            <select class="form-select" name="pendidikan_ibu">
                                <option value="">Pilih...</option>
                                @foreach(['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3', 'Tidak Sekolah'] as $p)
                                    <option value="{{ $p }}" {{ old('pendidikan_ibu', $mahasiswa->pendidikan_ibu) == $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pekerjaan Ibu</label>
                            <select class="form-select" name="pekerjaan_ibu">
                                <option value="">Pilih...</option>
                                @foreach(['Tidak Bekerja', 'PNS', 'Wiraswasta', 'Petani', 'Nelayan', 'Karyawan Swasta'] as $pk)
                                    <option value="{{ $pk }}" {{ old('pekerjaan_ibu', $mahasiswa->pekerjaan_ibu) == $pk ? 'selected' : '' }}>{{ $pk }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Penghasilan Ibu</label>
                            <select class="form-select" name="penghasilan_ibu">
                                <option value="">Pilih...</option>
                                @foreach(['Kurang dari 500rb', '500rb - 1 Juta', '1 Juta - 2 Juta', '2 Juta - 5 Juta', 'Lebih dari 5 Juta'] as $ph)
                                    <option value="{{ $ph }}" {{ old('penghasilan_ibu', $mahasiswa->penghasilan_ibu) == $ph ? 'selected' : '' }}>{{ $ph }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <h5 class="text-primary">Data Ayah</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Ayah</label>
                            <input type="text" class="form-control" name="nama_ayah" value="{{ old('nama_ayah', $mahasiswa->nama_ayah) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIK Ayah</label>
                            <input type="text" class="form-control" name="nik_ayah" maxlength="16" value="{{ old('nik_ayah', $mahasiswa->nik_ayah) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pendidikan Ayah</label>
                            <select class="form-select" name="pendidikan_ayah">
                                <option value="">Pilih...</option>
                                @foreach(['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3', 'Tidak Sekolah'] as $p)
                                    <option value="{{ $p }}" {{ old('pendidikan_ayah', $mahasiswa->pendidikan_ayah) == $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pekerjaan Ayah</label>
                            <select class="form-select" name="pekerjaan_ayah">
                                <option value="">Pilih...</option>
                                @foreach(['Tidak Bekerja', 'PNS', 'Wiraswasta', 'Petani', 'Nelayan', 'Karyawan Swasta'] as $pk)
                                    <option value="{{ $pk }}" {{ old('pekerjaan_ayah', $mahasiswa->pekerjaan_ayah) == $pk ? 'selected' : '' }}>{{ $pk }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Penghasilan Ayah</label>
                            <select class="form-select" name="penghasilan_ayah">
                                <option value="">Pilih...</option>
                                @foreach(['Kurang dari 500rb', '500rb - 1 Juta', '1 Juta - 2 Juta', '2 Juta - 5 Juta', 'Lebih dari 5 Juta'] as $ph)
                                    <option value="{{ $ph }}" {{ old('penghasilan_ayah', $mahasiswa->penghasilan_ayah) == $ph ? 'selected' : '' }}>{{ $ph }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div></div>
            </div>

        </div>

        <div class="mt-4 mb-5 text-end">
            <button type="submit" class="btn btn-primary px-4 shadow-sm"><i class="bi bi-save"></i> Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
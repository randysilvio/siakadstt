@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Edit Data Pegawai / Tendik</h3>
            <span class="text-muted small uppercase">Pembaruan Master Data Kepegawaian, Unit Kerja, & Kredensial Akses</span>
        </div>
        <div>
            <a href="{{ route('admin.tendik.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    {{-- Tampilkan Error Validasi Global --}}
    @if ($errors->any())
        <div class="alert alert-danger border rounded-0 p-3 mb-4 font-monospace small uppercase shadow-sm">
            <strong class="d-block mb-1">GAGAL MEMPERBARUI DATA:</strong>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.tendik.update', $tendik->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Navigasi Tab Flat Presisi 0px --}}
        <ul class="nav nav-tabs mb-4 rounded-0" id="tendikEditTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-0 fw-bold px-4 small uppercase text-dark" data-bs-toggle="tab" data-bs-target="#pribadi" type="button" role="tab">1. Data Pribadi</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-0 fw-bold px-4 small uppercase text-dark" data-bs-toggle="tab" data-bs-target="#kepegawaian" type="button" role="tab">2. Data Kepegawaian</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-0 fw-bold px-4 small uppercase text-dark" data-bs-toggle="tab" data-bs-target="#akun" type="button" role="tab">3. Akun & Hak Akses</button>
            </li>
        </ul>

        <div class="tab-content">
            {{-- TAB 1: PRIBADI --}}
            <div class="tab-pane fade show active" id="pribadi" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                        Formulir Identitas Pribadi Pegawai
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase text-dark">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-0 uppercase @error('name') is-invalid @enderror" name="name" value="{{ old('name', $tendik->nama_lengkap) }}" required>
                                @error('name') <div class="invalid-feedback font-monospace small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase text-dark">NIK (KTP) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-0 font-monospace @error('nik') is-invalid @enderror" name="nik" value="{{ old('nik', $tendik->nik) }}" maxlength="16" placeholder="WAJIB 16 DIGIT ANGKA" required>
                                @error('nik') <div class="invalid-feedback font-monospace small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase text-dark">Tempat Lahir</label>
                                <input type="text" class="form-control rounded-0 uppercase" name="tempat_lahir" value="{{ old('tempat_lahir', $tendik->tempat_lahir) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase text-dark">Tanggal Lahir</label>
                                <input type="date" class="form-control rounded-0 font-monospace text-center" name="tanggal_lahir" value="{{ old('tanggal_lahir', $tendik->tanggal_lahir) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase text-dark">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select class="form-select rounded-0 uppercase" name="jenis_kelamin" required>
                                    <option value="L" {{ old('jenis_kelamin', $tendik->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('jenis_kelamin', $tendik->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase text-dark">Nomor Telepon</label>
                                <input type="text" class="form-control rounded-0 font-monospace" name="nomor_telepon" value="{{ old('nomor_telepon', $tendik->nomor_telepon) }}" placeholder="08...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase text-dark">Foto Profil (Opsional)</label>
                                <input type="file" class="form-control rounded-0" name="foto_profil" accept="image/jpeg,image/png,image/jpg">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold uppercase text-dark">Alamat Lengkap</label>
                                <textarea class="form-control rounded-0 uppercase" name="alamat" rows="2">{{ old('alamat', $tendik->alamat) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 2: KEPEGAWAIAN --}}
            <div class="tab-pane fade" id="kepegawaian" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                        Parameter Penugasan & Status Pegawai
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase text-dark">Unit Kerja <span class="text-danger">*</span></label>
                                <select class="form-select rounded-0 uppercase @error('unit_kerja') is-invalid @enderror" name="unit_kerja" required>
                                    <option value="">-- PILIH UNIT KERJA --</option>
                                    @foreach(['BAAK' => 'BAAK (Akademik)', 'BAUK' => 'BAUK (Keuangan & Umum)', 'Perpustakaan' => 'Perpustakaan', 'Pusat Komputer' => 'Pusat Komputer/IT', 'Sarana Prasarana' => 'Sarana Prasarana', 'Keamanan' => 'Keamanan', 'Kebersihan' => 'Kebersihan'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('unit_kerja', $tendik->unit_kerja) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('unit_kerja') <div class="invalid-feedback font-monospace small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase text-dark">Jabatan Teknis <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-0 uppercase @error('jabatan') is-invalid @enderror" name="jabatan" value="{{ old('jabatan', $tendik->jabatan) }}" required>
                                @error('jabatan') <div class="invalid-feedback font-monospace small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase text-dark">Jenis Tendik</label>
                                <select class="form-select rounded-0 uppercase" name="jenis_tendik">
                                    @foreach(['Administrasi' => 'Tenaga Administrasi', 'Pustakawan' => 'Pustakawan', 'Laboran' => 'Laboran', 'Teknisi' => 'Teknisi', 'Lainnya' => 'Lainnya'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('jenis_tendik', $tendik->jenis_tendik) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase text-dark">Status Kepegawaian</label>
                                <select class="form-select rounded-0 uppercase" name="status_kepegawaian">
                                    @foreach(['Tetap', 'Kontrak', 'Honorer'] as $status)
                                        <option value="{{ $status }}" {{ old('status_kepegawaian', $tendik->status_kepegawaian) == $status ? 'selected' : '' }}>{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase text-dark">NIP Yayasan</label>
                                <input type="text" class="form-control rounded-0 font-monospace @error('nip_yayasan') is-invalid @enderror" name="nip_yayasan" value="{{ old('nip_yayasan', $tendik->nip_yayasan) }}">
                                @error('nip_yayasan') <div class="invalid-feedback font-monospace small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase text-dark">NITK (Dikti)</label>
                                <input type="text" class="form-control rounded-0 font-monospace @error('nitk') is-invalid @enderror" name="nitk" value="{{ old('nitk', $tendik->nitk) }}">
                                @error('nitk') <div class="invalid-feedback font-monospace small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase text-dark">TMT Kerja</label>
                                <input type="date" class="form-control rounded-0 font-monospace text-center" name="tmt_kerja" value="{{ old('tmt_kerja', $tendik->tmt_kerja) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 3: AKUN --}}
            <div class="tab-pane fade" id="akun" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                        Kredensial Autentikasi Sistem
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase text-dark">Email Login <span class="text-danger">*</span></label>
                                <input type="email" class="form-control rounded-0 @error('email') is-invalid @enderror" name="email" value="{{ old('email', $tendik->user->email ?? '') }}" required>
                                @error('email') <div class="invalid-feedback font-monospace small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase text-dark">Peran Akses (Role) <span class="text-danger">*</span></label>
                                <select class="form-select rounded-0 uppercase @error('role_id') is-invalid @enderror" name="role_id" required>
                                    <option value="">-- PILIH HAK AKSES --</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id', $currentRoleId) == $role->id ? 'selected' : '' }}>
                                            {{ $role->display_name ?? $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role_id') <div class="invalid-feedback font-monospace small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase text-dark">Password Baru (Kosongkan jika tidak diubah)</label>
                                <input type="password" class="form-control rounded-0 font-monospace @error('password') is-invalid @enderror" name="password" placeholder="••••••••">
                                @error('password') <div class="invalid-feedback font-monospace small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase text-dark">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control rounded-0 font-monospace" name="password_confirmation" placeholder="••••••••">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mb-5">
            <a href="{{ route('admin.tendik.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
            <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                <i class="bi bi-save me-1"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
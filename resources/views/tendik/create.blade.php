@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Tambah Pegawai / Tendik Baru</h2>
        <a href="{{ route('admin.tendik.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    {{-- Tampilkan Error Validasi Global --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.tendik.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <ul class="nav nav-tabs mb-4" id="tendikTab" role="tablist">
            <li class="nav-item"><button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#pribadi" type="button">1. Data Pribadi</button></li>
            <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#kepegawaian" type="button">2. Data Kepegawaian</button></li>
            <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#akun" type="button">3. Akun Login</button></li>
        </ul>

        <div class="tab-content">
            {{-- TAB 1: PRIBADI --}}
            <div class="tab-pane fade show active" id="pribadi">
                <div class="card shadow-sm border-0"><div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIK (KTP) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nik') is-invalid @enderror" name="nik" value="{{ old('nik') }}" maxlength="16" placeholder="Wajib 16 Digit" required>
                            @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                        <div class="col-md-6">
                            <label class="form-label">Nomor Telepon</label>
                            <input type="text" class="form-control" name="nomor_telepon" value="{{ old('nomor_telepon') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat Lengkap</label>
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
                            <label class="form-label">Unit Kerja <span class="text-danger">*</span></label>
                            <select class="form-select @error('unit_kerja') is-invalid @enderror" name="unit_kerja" required>
                                <option value="">Pilih Unit...</option>
                                <option value="BAAK" {{ old('unit_kerja') == 'BAAK' ? 'selected' : '' }}>BAAK (Akademik)</option>
                                <option value="BAUK" {{ old('unit_kerja') == 'BAUK' ? 'selected' : '' }}>BAUK (Keuangan & Umum)</option>
                                <option value="Perpustakaan" {{ old('unit_kerja') == 'Perpustakaan' ? 'selected' : '' }}>Perpustakaan</option>
                                <option value="Pusat Komputer" {{ old('unit_kerja') == 'Pusat Komputer' ? 'selected' : '' }}>Pusat Komputer/IT</option>
                                <option value="Sarana Prasarana" {{ old('unit_kerja') == 'Sarana Prasarana' ? 'selected' : '' }}>Sarana Prasarana</option>
                                <option value="Keamanan" {{ old('unit_kerja') == 'Keamanan' ? 'selected' : '' }}>Keamanan</option>
                                <option value="Kebersihan" {{ old('unit_kerja') == 'Kebersihan' ? 'selected' : '' }}>Kebersihan</option>
                            </select>
                            @error('unit_kerja') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('jabatan') is-invalid @enderror" name="jabatan" value="{{ old('jabatan') }}" placeholder="Contoh: Staff" required>
                            @error('jabatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jenis Tendik</label>
                            <select class="form-select" name="jenis_tendik">
                                <option value="Administrasi">Tenaga Administrasi</option>
                                <option value="Pustakawan">Pustakawan</option>
                                <option value="Laboran">Laboran</option>
                                <option value="Teknisi">Teknisi</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status Kepegawaian</label>
                            <select class="form-select" name="status_kepegawaian">
                                <option value="Tetap">Tetap</option>
                                <option value="Kontrak">Kontrak</option>
                                <option value="Honorer">Honorer</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">NIP Yayasan</label>
                            <input type="text" class="form-control @error('nip_yayasan') is-invalid @enderror" name="nip_yayasan" value="{{ old('nip_yayasan') }}">
                            @error('nip_yayasan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">NITK (Dikti)</label>
                            <input type="text" class="form-control" name="nitk" value="{{ old('nitk') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">TMT Kerja</label>
                            <input type="date" class="form-control" name="tmt_kerja" value="{{ old('tmt_kerja') }}">
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
                            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Peran (Role) <span class="text-danger">*</span></label>
                            <select class="form-select @error('role_id') is-invalid @enderror" name="role_id" required>
                                <option value="">Pilih Akses...</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->display_name ?? $role->name }}</option>
                                @endforeach
                            </select>
                            @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" required>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password_confirmation" required>
                        </div>
                    </div>
                </div></div>
            </div>
        </div>

        <div class="mt-4 mb-5 text-end">
            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save"></i> Simpan Pegawai</button>
        </div>
    </form>
</div>
@endsection
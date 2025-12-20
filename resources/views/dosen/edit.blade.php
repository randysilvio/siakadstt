@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Edit Data Dosen</h2>
        <a href="{{ route('admin.dosen.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    <form action="{{ route('admin.dosen.update', $dosen->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Navigasi Tab --}}
        <ul class="nav nav-tabs mb-4" id="dosenTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active fw-bold" id="pribadi-tab" data-bs-toggle="tab" data-bs-target="#pribadi" type="button" role="tab">1. Data Pribadi</button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold" id="kepegawaian-tab" data-bs-toggle="tab" data-bs-target="#kepegawaian" type="button" role="tab">2. Kepegawaian & Akademik</button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold" id="akun-tab" data-bs-toggle="tab" data-bs-target="#akun" type="button" role="tab">3. Akun & Keuangan</button>
            </li>
        </ul>

        <div class="tab-content">
            
            {{-- TAB 1: DATA PRIBADI --}}
            <div class="tab-pane fade show active" id="pribadi" role="tabpanel">
                <div class="card shadow-sm border-0"><div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap (Gelar)</label>
                            <input type="text" class="form-control" name="nama_lengkap" value="{{ old('nama_lengkap', $dosen->nama_lengkap) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIDN</label>
                            <input type="text" class="form-control" name="nidn" value="{{ old('nidn', $dosen->nidn) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIK (KTP) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nik" value="{{ old('nik', $dosen->nik) }}" maxlength="16" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NPWP</label>
                            <input type="text" class="form-control" name="npwp" value="{{ old('npwp', $dosen->npwp) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" class="form-control" name="tempat_lahir" value="{{ old('tempat_lahir', $dosen->tempat_lahir) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control" name="tanggal_lahir" value="{{ old('tanggal_lahir', $dosen->tanggal_lahir) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jenis Kelamin</label>
                            <select class="form-select" name="jenis_kelamin" required>
                                <option value="L" {{ $dosen->jenis_kelamin == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ $dosen->jenis_kelamin == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nomor Telepon</label>
                            <input type="text" class="form-control" name="nomor_telepon" value="{{ old('nomor_telepon', $dosen->nomor_telepon) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat Domisili</label>
                            <textarea class="form-control" name="alamat" rows="2">{{ old('alamat', $dosen->alamat) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Deskripsi Diri (Singkat)</label>
                            <textarea class="form-control" name="deskripsi_diri" rows="2">{{ old('deskripsi_diri', $dosen->deskripsi_diri) }}</textarea>
                        </div>
                    </div>
                </div></div>
            </div>

            {{-- TAB 2: KEPEGAWAIAN --}}
            <div class="tab-pane fade" id="kepegawaian" role="tabpanel">
                <div class="card shadow-sm border-0"><div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Status Kepegawaian</label>
                            <select class="form-select" name="status_kepegawaian" required>
                                @foreach(['Dosen Tetap', 'Dosen Tidak Tetap', 'Dosen Tamu'] as $st)
                                    <option value="{{ $st }}" {{ $dosen->status_kepegawaian == $st ? 'selected' : '' }}>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NUPTK</label>
                            <input type="text" class="form-control" name="nuptk" value="{{ old('nuptk', $dosen->nuptk) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jabatan Akademik</label>
                            <select class="form-select" name="jabatan_akademik">
                                <option value="">- Pilih -</option>
                                @foreach(['Tenaga Pengajar', 'Asisten Ahli', 'Lektor', 'Lektor Kepala', 'Guru Besar'] as $jb)
                                    <option value="{{ $jb }}" {{ $dosen->jabatan_akademik == $jb ? 'selected' : '' }}>{{ $jb }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pangkat / Golongan</label>
                            <input type="text" class="form-control" name="pangkat_golongan" value="{{ old('pangkat_golongan', $dosen->pangkat_golongan) }}" placeholder="Contoh: III/b">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. SK Pengangkatan</label>
                            <input type="text" class="form-control" name="no_sk_pengangkatan" value="{{ old('no_sk_pengangkatan', $dosen->no_sk_pengangkatan) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">TMT SK Pengangkatan</label>
                            <input type="date" class="form-control" name="tmt_sk_pengangkatan" value="{{ old('tmt_sk_pengangkatan', $dosen->tmt_sk_pengangkatan) }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Bidang Keahlian</label>
                            <input type="text" class="form-control" name="bidang_keahlian" value="{{ old('bidang_keahlian', $dosen->bidang_keahlian) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Link Google Scholar</label>
                            <input type="url" class="form-control" name="link_google_scholar" value="{{ old('link_google_scholar', $dosen->link_google_scholar) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Link SINTA</label>
                            <input type="url" class="form-control" name="link_sinta" value="{{ old('link_sinta', $dosen->link_sinta) }}">
                        </div>
                    </div>
                </div></div>
            </div>

            {{-- TAB 3: AKUN --}}
            <div class="tab-pane fade" id="akun" role="tabpanel">
                <div class="card shadow-sm border-0"><div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Email Login</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', optional($dosen->user)->email) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Institusi</label>
                            <input type="email" class="form-control" name="email_institusi" value="{{ old('email_institusi', $dosen->email_institusi) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password Baru (Opsional)</label>
                            <input type="password" class="form-control" name="password" placeholder="Isi jika ingin ganti password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" name="password_confirmation">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Foto Profil</label>
                            @if($dosen->foto_profil)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $dosen->foto_profil) }}" alt="Foto" class="img-thumbnail" width="100">
                                </div>
                            @endif
                            <input type="file" class="form-control" name="foto_profil">
                        </div>
                        <div class="col-md-12">
                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" id="is_keuangan" name="is_keuangan" value="1" {{ $dosen->is_keuangan ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_keuangan">Berikan Akses Modul Keuangan?</label>
                            </div>
                        </div>
                    </div>
                </div></div>
            </div>

        </div>

        <div class="mt-4 mb-5 text-end">
            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save"></i> Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
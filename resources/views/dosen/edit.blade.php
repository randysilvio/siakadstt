@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Modifikasi Data Dosen</h3>
            <span class="text-muted small">ID Pengguna: {{ $dosen->user->id ?? '-' }} | Terakhir diperbarui: {{ $dosen->updated_at->format('d/m/Y H:i') }}</span>
        </div>
        <a href="{{ route('admin.dosen.index') }}" class="btn btn-outline-dark btn-sm rounded-0">KEMBALI</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 bg-danger text-white py-2 px-3 rounded-0 mb-4 small" role="alert">
            <span class="fw-bold">GAGAL MEMPERBARUI DATA:</span> Harap periksa kembali isian formulir Anda.
        </div>
    @endif

    <form action="{{ route('admin.dosen.update', $dosen->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <ul class="nav nav-tabs mb-4 border-bottom-2" id="dosenTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active fw-bold text-dark rounded-0 uppercase small" data-bs-toggle="tab" data-bs-target="#pribadi" type="button">Identitas Personal</button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold text-dark rounded-0 uppercase small" data-bs-toggle="tab" data-bs-target="#kepegawaian" type="button">Status & Jabatan</button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold text-dark rounded-0 uppercase small" data-bs-toggle="tab" data-bs-target="#akun" type="button">Akses & Foto</button>
            </li>
        </ul>

        <div class="tab-content">
            {{-- TAB 1: DATA PRIBADI --}}
            <div class="tab-pane fade show active" id="pribadi">
                <div class="card border-0 shadow-sm rounded-0">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">Nama Lengkap & Gelar</label>
                                <input type="text" class="form-control rounded-0 @error('nama_lengkap') is-invalid @enderror" name="nama_lengkap" value="{{ old('nama_lengkap', $dosen->nama_lengkap) }}" required>
                                @error('nama_lengkap') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">NIDN / NIDK</label>
                                <input type="text" class="form-control rounded-0 font-monospace @error('nidn') is-invalid @enderror" name="nidn" value="{{ old('nidn', $dosen->nidn) }}" required>
                                @error('nidn') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">NIK (KTP)</label>
                                <input type="text" class="form-control rounded-0 font-monospace @error('nik') is-invalid @enderror" name="nik" value="{{ old('nik', $dosen->nik) }}" maxlength="16" required>
                                @error('nik') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">NPWP</label>
                                <input type="text" class="form-control rounded-0 font-monospace" name="npwp" value="{{ old('npwp', $dosen->npwp) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase">Tempat Lahir</label>
                                <input type="text" class="form-control rounded-0" name="tempat_lahir" value="{{ old('tempat_lahir', $dosen->tempat_lahir) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase">Tanggal Lahir</label>
                                <input type="date" class="form-control rounded-0" name="tanggal_lahir" value="{{ old('tanggal_lahir', $dosen->tanggal_lahir) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase">Jenis Kelamin</label>
                                <select class="form-select rounded-0" name="jenis_kelamin" required>
                                    <option value="L" {{ old('jenis_kelamin', $dosen->jenis_kelamin) == 'L' ? 'selected' : '' }}>LAKI-LAKI</option>
                                    <option value="P" {{ old('jenis_kelamin', $dosen->jenis_kelamin) == 'P' ? 'selected' : '' }}>PEREMPUAN</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold uppercase">Alamat Domisili</label>
                                <textarea class="form-control rounded-0" name="alamat" rows="2">{{ old('alamat', $dosen->alamat) }}</textarea>
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
                                <label class="form-label small fw-bold uppercase">Status Kepegawaian</label>
                                <select class="form-select rounded-0" name="status_kepegawaian" required>
                                    @foreach(['Dosen Tetap', 'Dosen Tidak Tetap', 'Dosen Tamu'] as $st)
                                        <option value="{{ $st }}" {{ old('status_kepegawaian', $dosen->status_kepegawaian) == $st ? 'selected' : '' }}>{{ strtoupper($st) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">Program Studi (Homebase)</label>
                                <select class="form-select rounded-0" name="program_studi_id">
                                    <option value="">-- PILIH UNIT KERJA --</option>
                                    @foreach($programStudis as $prodi)
                                        <option value="{{ $prodi->id }}" {{ old('program_studi_id', $dosen->program_studi_id) == $prodi->id ? 'selected' : '' }}>
                                            {{ strtoupper($prodi->nama_prodi) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">Jabatan Fungsional</label>
                                <select class="form-select rounded-0" name="jabatan_akademik">
                                    <option value="">-- TANPA JAFUNG --</option>
                                    @foreach(['Tenaga Pengajar', 'Asisten Ahli', 'Lektor', 'Lektor Kepala', 'Guru Besar'] as $jb)
                                        <option value="{{ $jb }}" {{ old('jabatan_akademik', $dosen->jabatan_akademik) == $jb ? 'selected' : '' }}>{{ strtoupper($jb) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">Pangkat / Golongan</label>
                                <input type="text" class="form-control rounded-0" name="pangkat_golongan" value="{{ old('pangkat_golongan', $dosen->pangkat_golongan) }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold uppercase">Bidang Keahlian Spesifik</label>
                                <input type="text" class="form-control rounded-0" name="bidang_keahlian" value="{{ old('bidang_keahlian', $dosen->bidang_keahlian) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 3: AKUN & FOTO --}}
            <div class="tab-pane fade" id="akun">
                <div class="card border-0 shadow-sm rounded-0">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">Email Utama Sistem</label>
                                <input type="email" class="form-control rounded-0 font-monospace @error('email') is-invalid @enderror" name="email" value="{{ old('email', optional($dosen->user)->email) }}" required>
                                @error('email') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">Ubah Kata Sandi (Opsional)</label>
                                <input type="password" class="form-control rounded-0" name="password" placeholder="Kosongkan jika tidak ingin diubah">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">Pas Foto Profil</label>
                                <input type="file" class="form-control rounded-0 mb-3" name="foto_profil" accept="image/*">
                                @if($dosen->foto_profil)
                                    <div class="bg-light p-2 border d-inline-block">
                                        <img src="{{ asset('storage/' . $dosen->foto_profil) }}" alt="Foto Profil" height="100" class="border">
                                        <p class="text-center mb-0 small text-muted font-monospace">AKTIF</p>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6 d-flex align-items-center">
                                <div class="form-check form-switch bg-light p-3 border w-100">
                                    <input class="form-check-input ms-0" type="checkbox" id="is_keuangan" name="is_keuangan" value="1" {{ old('is_keuangan', $dosen->is_keuangan) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold small uppercase ms-2" for="is_keuangan">Akses Modul Keuangan</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 mb-5 text-end">
            <button type="submit" class="btn btn-primary px-5 rounded-0 fw-bold">PERBARUI DATA DOSEN</button>
        </div>
    </form>
</div>
@endsection
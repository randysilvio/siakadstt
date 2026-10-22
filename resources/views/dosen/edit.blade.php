@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Modifikasi Data Dosen</h3>
            <span class="text-muted small uppercase">ID Pengguna: {{ $dosen->user->id ?? '-' }} | Terakhir diperbarui: {{ $dosen->updated_at->format('d/m/Y H:i') }}</span>
        </div>
        <a href="{{ route('admin.dosen.index') }}" class="btn btn-outline-dark btn-sm rounded-0 px-3 uppercase fw-bold small">Kembali</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border rounded-0 shadow-sm mb-4 p-3 small uppercase" role="alert">
            <strong class="fw-bold d-block mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>GAGAL MEMPERBARUI DATA:</strong>
            <ul class="mb-0 ps-3 font-monospace">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.dosen.update', $dosen->id) }}" method="POST" enctype="multipart/form-data" autocomplete="off">
        @csrf
        @method('PUT')

        <ul class="nav nav-tabs mb-4 rounded-0" id="dosenTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active fw-bold text-dark rounded-0 uppercase small" data-bs-toggle="tab" data-bs-target="#pribadi" type="button">1. Identitas Personal</button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold text-dark rounded-0 uppercase small" data-bs-toggle="tab" data-bs-target="#kepegawaian" type="button">2. Status & Jabatan</button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold text-dark rounded-0 uppercase small" data-bs-toggle="tab" data-bs-target="#akun" type="button">3. Akses & Foto</button>
            </li>
        </ul>

        <div class="tab-content">
            {{-- TAB 1: DATA PRIBADI --}}
            <div class="tab-pane fade show active" id="pribadi">
                <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-0 @error('nama_lengkap') is-invalid @enderror" name="nama_lengkap" value="{{ old('nama_lengkap', $dosen->nama_lengkap) }}" required autocomplete="off">
                                @error('nama_lengkap') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">NIDN / NIP / NIDK</label>
                                <input type="text" class="form-control rounded-0 font-monospace @error('nidn') is-invalid @enderror" name="nidn" value="{{ old('nidn', $dosen->nidn) }}" autocomplete="off" placeholder="Kosongkan jika tidak ada">
                                @error('nidn') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">NIK (KTP) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-0 font-monospace @error('nik') is-invalid @enderror" name="nik" value="{{ old('nik', $dosen->nik) }}" maxlength="16" required autocomplete="off">
                                @error('nik') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">NPWP</label>
                                <input type="text" class="form-control rounded-0 font-monospace" name="npwp" value="{{ old('npwp', $dosen->npwp) }}" autocomplete="off">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase">Tempat Lahir</label>
                                <input type="text" class="form-control rounded-0" name="tempat_lahir" value="{{ old('tempat_lahir', $dosen->tempat_lahir) }}" autocomplete="off">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase">Tanggal Lahir</label>
                                <input type="date" class="form-control rounded-0 font-monospace" name="tanggal_lahir" value="{{ old('tanggal_lahir', $dosen->tanggal_lahir) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select class="form-select rounded-0" name="jenis_kelamin" required>
                                    <option value="L" {{ old('jenis_kelamin', $dosen->jenis_kelamin) == 'L' ? 'selected' : '' }}>LAKI-LAKI</option>
                                    <option value="P" {{ old('jenis_kelamin', $dosen->jenis_kelamin) == 'P' ? 'selected' : '' }}>PEREMPUAN</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold uppercase">Alamat Domisili</label>
                                <textarea class="form-control rounded-0" name="alamat" rows="2" autocomplete="off">{{ old('alamat', $dosen->alamat) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 2: KEPEGAWAIAN --}}
            <div class="tab-pane fade" id="kepegawaian">
                <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">Kategori Pengajar <span class="text-danger">*</span></label>
                                <select class="form-select rounded-0" name="jenis_pengajar" required>
                                    <option value="Dosen Tetap" {{ old('jenis_pengajar', $dosen->jenis_pengajar) == 'Dosen Tetap' ? 'selected' : '' }}>DOSEN UTAMA / TETAP</option>
                                    <option value="Asisten Dosen" {{ old('jenis_pengajar', $dosen->jenis_pengajar) == 'Asisten Dosen' ? 'selected' : '' }}>ASISTEN DOSEN</option>
                                    <option value="Dosen Praktisi" {{ old('jenis_pengajar', $dosen->jenis_pengajar) == 'Dosen Praktisi' ? 'selected' : '' }}>DOSEN PRAKTISI</option>
                                    <option value="Dosen Tamu" {{ old('jenis_pengajar', $dosen->jenis_pengajar) == 'Dosen Tamu' ? 'selected' : '' }}>DOSEN TAMU</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">Status Ikatan Kerja (HRD) <span class="text-danger">*</span></label>
                                @php
                                    $standardStatus = ['Dosen Tetap', 'Dosen Tidak Tetap', 'Dosen Tamu'];
                                    $currentStatus = old('status_kepegawaian', $dosen->status_kepegawaian);
                                    $isCustomStatus = $currentStatus && !in_array($currentStatus, $standardStatus);
                                @endphp
                                <select class="form-select rounded-0" name="status_kepegawaian" required>
                                    @if($isCustomStatus)
                                        <option value="{{ $currentStatus }}" selected>{{ strtoupper($currentStatus) }} (DATA CUSTOM)</option>
                                    @endif
                                    @foreach($standardStatus as $st)
                                        <option value="{{ $st }}" {{ $currentStatus == $st ? 'selected' : '' }}>{{ strtoupper($st) }}</option>
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
                                <label class="form-label small fw-bold uppercase">Jabatan Fungsional / Akademik</label>
                                @php
                                    $standardJafung = ['Tenaga Pengajar', 'Asisten Ahli', 'Lektor', 'Lektor Kepala', 'Guru Besar'];
                                    $currentJafung = old('jabatan_akademik', $dosen->jabatan_akademik);
                                    $isCustomJafung = $currentJafung && !in_array($currentJafung, $standardJafung);
                                @endphp
                                <select class="form-select rounded-0" name="jabatan_akademik">
                                    <option value="">-- TANPA JAFUNG --</option>
                                    @if($isCustomJafung)
                                        <option value="{{ $currentJafung }}" selected>{{ strtoupper($currentJafung) }} (DATA CUSTOM)</option>
                                    @endif
                                    @foreach($standardJafung as $jb)
                                        <option value="{{ $jb }}" {{ $currentJafung == $jb ? 'selected' : '' }}>{{ strtoupper($jb) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">Pangkat / Golongan</label>
                                <input type="text" class="form-control rounded-0" name="pangkat_golongan" value="{{ old('pangkat_golongan', $dosen->pangkat_golongan) }}" autocomplete="off">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">Bidang Keahlian Spesifik</label>
                                <input type="text" class="form-control rounded-0" name="bidang_keahlian" value="{{ old('bidang_keahlian', $dosen->bidang_keahlian) }}" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 3: AKUN & FOTO --}}
            <div class="tab-pane fade" id="akun">
                <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">Email Utama Sistem (Login) <span class="text-danger">*</span></label>
                                <input type="email" class="form-control rounded-0 font-monospace @error('email') is-invalid @enderror" name="email" value="{{ old('email', optional($dosen->user)->email) }}" required autocomplete="off">
                                @error('email') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">Email Institusi (Publik/Opsional)</label>
                                <input type="email" class="form-control rounded-0 font-monospace @error('email_institusi') is-invalid @enderror" name="email_institusi" value="{{ old('email_institusi', $dosen->email_institusi) }}" autocomplete="off">
                                @error('email_institusi') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">Kata Sandi Baru (Opsional)</label>
                                <input type="password" class="form-control rounded-0 font-monospace @error('password') is-invalid @enderror" name="password" placeholder="Kosongkan jika tidak ingin diubah" autocomplete="new-password">
                                @error('password') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase">Konfirmasi Kata Sandi Baru</label>
                                <input type="password" class="form-control rounded-0 font-monospace" name="password_confirmation" placeholder="Ulangi kata sandi baru" autocomplete="new-password">
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
                                    <input class="form-check-input ms-0" type="checkbox" id="is_keuangan" name="is_keuangan" value="1" {{ (old('is_keuangan') ?? $dosen->is_keuangan) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold small uppercase ms-2" for="is_keuangan">Akses Modul Keuangan</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 mb-5 text-end">
            <a href="{{ route('admin.dosen.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 py-2 uppercase fw-bold small me-2">Batal</a>
            <button type="submit" class="btn btn-sm btn-primary px-5 py-2 rounded-0 uppercase fw-bold small shadow-sm">
                <i class="bi bi-save me-1"></i> Perbarui Data Dosen
            </button>
        </div>
    </form>
</div>
@endsection
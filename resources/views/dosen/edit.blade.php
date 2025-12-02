@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2>Edit Dosen: {{ $dosen->nama_lengkap }}</h2>
            <hr>
            
            {{-- Form Action ke rute admin --}}
            <form action="{{ route('admin.dosen.update', $dosen->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="card mb-4 shadow-sm">
                    <div class="card-header fw-bold">Data Utama</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="nidn" class="form-label">NIDN</label>
                            <input type="text" class="form-control @error('nidn') is-invalid @enderror" id="nidn" name="nidn" value="{{ old('nidn', $dosen->nidn) }}" required>
                            @error('nidn') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $dosen->nama_lengkap) }}" required>
                            @error('nama_lengkap') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_keuangan" name="is_keuangan" value="1" {{ old('is_keuangan', $dosen->is_keuangan) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_keuangan">Berikan akses Staff Keuangan</label>
                        </div>
                    </div>
                </div>

                <div class="card mb-4 shadow-sm">
                    <div class="card-header fw-bold">Profil Akademik & Publik</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="foto_profil" class="form-label">Foto Profil</label>
                            @if($dosen->getRawOriginal('foto_profil'))
                                <div class="mb-2">
                                    <img src="{{ $dosen->foto_profil }}" alt="Foto Profil" class="img-thumbnail" width="100">
                                </div>
                            @endif
                            <input type="file" class="form-control @error('foto_profil') is-invalid @enderror" id="foto_profil" name="foto_profil">
                            <div class="form-text">Format: JPG, PNG. Maksimal 2MB. Kosongkan jika tidak ingin mengubah.</div>
                            @error('foto_profil') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="jabatan_akademik" class="form-label">Jabatan Akademik</label>
                                <input type="text" class="form-control" id="jabatan_akademik" name="jabatan_akademik" value="{{ old('jabatan_akademik', $dosen->jabatan_akademik) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="bidang_keahlian" class="form-label">Bidang Keahlian</label>
                                <input type="text" class="form-control" id="bidang_keahlian" name="bidang_keahlian" value="{{ old('bidang_keahlian', $dosen->bidang_keahlian) }}">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="deskripsi_diri" class="form-label">Deskripsi Diri</label>
                            <textarea class="form-control" id="deskripsi_diri" name="deskripsi_diri" rows="3">{{ old('deskripsi_diri', $dosen->deskripsi_diri) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="email_institusi" class="form-label">Email Institusi</label>
                            <input type="email" class="form-control" id="email_institusi" name="email_institusi" value="{{ old('email_institusi', $dosen->email_institusi) }}">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="link_google_scholar" class="form-label">Link Google Scholar</label>
                                <input type="url" class="form-control" id="link_google_scholar" name="link_google_scholar" value="{{ old('link_google_scholar', $dosen->link_google_scholar) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="link_sinta" class="form-label">Link SINTA</label>
                                <input type="url" class="form-control" id="link_sinta" name="link_sinta" value="{{ old('link_sinta', $dosen->link_sinta) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4 shadow-sm">
                    <div class="card-header fw-bold">Akun Login</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Login</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $dosen->user->email ?? '') }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        
                        <hr>
                        <p class="text-muted small">Isi password di bawah HANYA jika ingin menggantinya.</p>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password Baru</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-5">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('admin.dosen.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
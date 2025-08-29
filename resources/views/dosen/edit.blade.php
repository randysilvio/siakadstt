@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2>Edit Dosen: {{ $dosen->nama_lengkap }}</h2>
            <hr>
            <div class="card">
                <div class="card-body">
                    {{-- PERBAIKAN: Menggunakan rute admin & menambahkan enctype untuk upload file --}}
                    <form action="{{ route('admin.dosen.update', $dosen) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <h4>Data Utama</h4>
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
                        
                        <hr class="my-4">

                        {{-- =============================================== --}}
                        {{-- ===== PENAMBAHAN FORM UNTUK PROFIL PUBLIK ===== --}}
                        {{-- =============================================== --}}
                        <h4>Profil Publik</h4>

                        <div class="mb-3">
                            <label for="foto_profil" class="form-label">Foto Profil</label>
                            @if($dosen->foto_profil)
                                <img src="{{ $dosen->foto_profil_url }}" alt="Foto Profil" class="img-thumbnail d-block mb-2" width="150">
                            @endif
                            <input type="file" class="form-control @error('foto_profil') is-invalid @enderror" id="foto_profil" name="foto_profil">
                            <div class="form-text">Kosongkan jika tidak ingin mengubah foto. Format: JPG, PNG. Maksimal 2MB.</div>
                            @error('foto_profil') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="jabatan_akademik" class="form-label">Jabatan Akademik</label>
                            <input type="text" class="form-control @error('jabatan_akademik') is-invalid @enderror" id="jabatan_akademik" name="jabatan_akademik" value="{{ old('jabatan_akademik', $dosen->jabatan_akademik) }}">
                            @error('jabatan_akademik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="bidang_keahlian" class="form-label">Bidang Keahlian</label>
                            <input type="text" class="form-control @error('bidang_keahlian') is-invalid @enderror" id="bidang_keahlian" name="bidang_keahlian" value="{{ old('bidang_keahlian', $dosen->bidang_keahlian) }}">
                            <div class="form-text">Pisahkan dengan koma, contoh: Teologi, Pendidikan Agama, Misiologi</div>
                            @error('bidang_keahlian') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="deskripsi_diri" class="form-label">Deskripsi Diri / Biografi Singkat</label>
                            <textarea class="form-control @error('deskripsi_diri') is-invalid @enderror" id="deskripsi_diri" name="deskripsi_diri" rows="4">{{ old('deskripsi_diri', $dosen->deskripsi_diri) }}</textarea>
                            @error('deskripsi_diri') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email_institusi" class="form-label">Email Institusi</label>
                            <input type="email" class="form-control @error('email_institusi') is-invalid @enderror" id="email_institusi" name="email_institusi" value="{{ old('email_institusi', $dosen->email_institusi) }}">
                            @error('email_institusi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="link_google_scholar" class="form-label">Link Google Scholar</label>
                            <input type="url" class="form-control @error('link_google_scholar') is-invalid @enderror" id="link_google_scholar" name="link_google_scholar" value="{{ old('link_google_scholar', $dosen->link_google_scholar) }}">
                            @error('link_google_scholar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="link_sinta" class="form-label">Link SINTA</label>
                            <input type="url" class="form-control @error('link_sinta') is-invalid @enderror" id="link_sinta" name="link_sinta" value="{{ old('link_sinta', $dosen->link_sinta) }}">
                            @error('link_sinta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <hr class="my-4">
                        <h4>Data Akun Login</h4>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Login</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $dosen->user->email ?? '') }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="form-text mb-3">
                            Password tidak dapat diubah dari halaman ini. Pengguna dapat mengubah passwordnya sendiri melalui halaman profil atau fitur "Lupa Password".
                        </div>
                        
                        {{-- PERBAIKAN: Menggunakan rute admin --}}
                        <a href="{{ route('admin.dosen.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
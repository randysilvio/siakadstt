@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3 border-bottom pb-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Manajemen Profil & Kredensial</h3>
            <span class="text-muted small uppercase">Pembaruan Master Data Akun & Pengesahan Autentikasi Pengguna</span>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali ke Dasbor
            </a>
        </div>
    </div>

    {{-- NOTIFIKASI SUKSES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 p-3 mb-4 font-monospace small uppercase fw-bold shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @elseif(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 p-3 mb-4 font-monospace small uppercase fw-bold shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @elseif(session('status') === 'profile-updated')
         <div class="alert alert-success alert-dismissible fade show border rounded-0 p-3 mb-4 font-monospace small uppercase fw-bold shadow-sm" role="alert">
             <i class="bi bi-check-circle-fill me-2"></i> Informasi profil berhasil diperbarui secara permanen.
             <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>
    @elseif(session('status') === 'password-updated')
         <div class="alert alert-success alert-dismissible fade show border rounded-0 p-3 mb-4 font-monospace small uppercase fw-bold shadow-sm" role="alert">
             <i class="bi bi-shield-check me-2"></i> Kredensial kata sandi berhasil diperbarui.
             <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>
    @endif

    {{-- NOTIFIKASI ERROR VALIDASI GANTI KATA SANDI (MENCEGAH SILENT ERROR) --}}
    @if($errors->updatePassword->any())
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 p-3 mb-4 font-monospace small uppercase shadow-sm" role="alert">
            <strong class="d-block mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i> GAGAL MENGGANTI KATA SANDI:</strong>
            <ul class="mb-0 ps-3">
                @foreach($errors->updatePassword->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-5">
        {{-- KANVAS KIRI: Form Informasi Profil & Password --}}
        <div class="col-md-8">
            {{-- Form Informasi Dasar --}}
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-white">
                <div class="card-header bg-dark text-white py-3 border-bottom rounded-0 uppercase fw-bold small">
                    <i class="bi bi-person-gear me-2"></i>Parameter Akun Pengguna
                </div>
                <div class="card-body p-4">
                    <form method="post" action="{{ route('profile.update') }}">
                        @csrf
                        @method('patch')

                        <div class="mb-3">
                            <label for="name" class="form-label small fw-bold uppercase text-dark">Nama Tampilan Pengguna <span class="text-danger">*</span></label>
                            <input id="name" name="name" type="text" class="form-control rounded-0 uppercase @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus>
                            @error('name')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label small fw-bold uppercase text-dark">Alamat Surel Resmi <span class="text-danger">*</span></label>
                            <input id="email" name="email" type="email" class="form-control rounded-0 font-monospace @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="d-flex justify-content-end pt-2 border-top">
                            <button type="submit" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm py-2">
                                <i class="bi bi-save me-1 align-middle"></i> Simpan Parameter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Form Penggantian Kata Sandi --}}
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 bg-white">
                <div class="card-header bg-dark text-white py-3 border-bottom rounded-0 uppercase fw-bold small">
                    <i class="bi bi-key-fill me-2"></i>Pembaruan Kunci Keamanan (Kata Sandi)
                </div>
                <div class="card-body p-4">
                    <form method="post" action="{{ route('password.update') }}" autocomplete="off">
                        @csrf
                        @method('put')

                        <div class="mb-3">
                            <label for="current_password" class="form-label small fw-bold uppercase text-dark">Kata Sandi Saat Ini <span class="text-danger">*</span></label>
                            <input id="current_password" name="current_password" type="password" class="form-control rounded-0 font-monospace @if($errors->updatePassword->has('current_password')) is-invalid @endif" placeholder="Masukkan kata sandi lama..." required autocomplete="current-password">
                            @if($errors->updatePassword->has('current_password'))
                                <div class="invalid-feedback font-monospace small">{{ $errors->updatePassword->first('current_password') }}</div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label small fw-bold uppercase text-dark">Kata Sandi Baru (Minimal 8 Karakter) <span class="text-danger">*</span></label>
                            <input id="password" name="password" type="password" class="form-control rounded-0 font-monospace @if($errors->updatePassword->has('password')) is-invalid @endif" placeholder="Minimal 8 karakter..." required autocomplete="new-password">
                            @if($errors->updatePassword->has('password'))
                                <div class="invalid-feedback font-monospace small">{{ $errors->updatePassword->first('password') }}</div>
                            @endif
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label small fw-bold uppercase text-dark">Konfirmasi Kata Sandi Baru <span class="text-danger">*</span></label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control rounded-0 font-monospace @if($errors->updatePassword->has('password_confirmation')) is-invalid @endif" placeholder="Ketik ulang kata sandi baru..." required autocomplete="new-password">
                            @if($errors->updatePassword->has('password_confirmation'))
                                <div class="invalid-feedback font-monospace small">{{ $errors->updatePassword->first('password_confirmation') }}</div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-end pt-2 border-top">
                            <button type="submit" class="btn btn-sm btn-dark rounded-0 px-4 uppercase fw-bold small shadow-sm py-2">
                                <i class="bi bi-shield-lock me-1 align-middle text-white"></i> Terapkan Sandi Baru
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        {{-- KANVAS KANAN: Manajemen Pas Foto --}}
        @if(Auth::user()->mahasiswa || Auth::user()->dosen || Auth::user()->tendik)
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 bg-light">
                <div class="card-header bg-dark text-white py-3 border-bottom rounded-0 uppercase fw-bold small text-center">
                    Pas Foto Identitas Resmi
                </div>
                <div class="card-body p-4 text-center">
                    @php
                        $fotoUrl = asset('images/logo.png');
                        if (Auth::user()->mahasiswa) {
                            $fotoUrl = Auth::user()->mahasiswa->foto_profil_url;
                        } elseif (Auth::user()->dosen) {
                            $fotoUrl = Auth::user()->dosen->foto_url;
                        } elseif (Auth::user()->tendik) {
                            $fotoUrl = Auth::user()->tendik->foto_url;
                        }
                    @endphp
                    <div class="mx-auto mb-3 bg-white p-1 border border-dark border-opacity-25 rounded-0" style="width: 160px; height: 200px;">
                        <img src="{{ $fotoUrl }}" alt="Pas Foto Resmi" class="img-fluid rounded-0 w-100 h-100 shadow-none" style="object-fit: cover;">
                    </div>
                    
                    <form method="post" action="{{ route('profile.update.photo') }}" enctype="multipart/form-data">
                        @csrf
                        @method('patch')
                        <div class="mb-3 text-start">
                            <label for="foto_profil" class="form-label small fw-bold uppercase text-dark">Ganti Pas Foto (Formal)</label>
                            <input id="foto_profil" name="foto_profil" type="file" class="form-control rounded-0 @error('foto_profil') is-invalid @enderror" accept="image/jpeg,image/png,image/jpg" required>
                            <div class="form-text text-muted font-monospace small">Ukuran maks: 2MB. Format: JPG/PNG.</div>
                            @error('foto_profil')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary rounded-0 w-100 uppercase fw-bold small py-2 shadow-sm">
                            <i class="bi bi-cloud-upload me-1 align-middle text-white"></i> Validasi Foto Baru
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
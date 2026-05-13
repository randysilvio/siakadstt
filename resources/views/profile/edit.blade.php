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

    {{-- NOTIFIKASI SISTEM --}}
    @if(session('success'))
        <div class="alert alert-success border rounded-0 p-3 mb-4 font-monospace small uppercase shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('status') === 'profile-updated')
         <div class="alert alert-success border rounded-0 p-3 mb-4 font-monospace small uppercase shadow-sm">
             <i class="bi bi-check-circle-fill me-2"></i> Informasi profil berhasil diperbarui secara permanen.
         </div>
    @endif
    @if(session('status') === 'password-updated')
         <div class="alert alert-success border rounded-0 p-3 mb-4 font-monospace small uppercase shadow-sm">
             <i class="bi bi-shield-check me-2"></i> Kredensial kata sandi berhasil diperbarui.
         </div>
    @endif

    <div class="row g-4 mb-5">
        {{-- KANVAS KIRI: Form Informasi Profil & Password --}}
        <div class="col-md-8">
            {{-- Form Informasi Dasar --}}
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-white">
                <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                    Parameter Akun Pengguna
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
                            <input id="email" name="email" type="email" class="form-control rounded-0 @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
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
                <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                    Pembaruan Kunci Keamanan
                </div>
                <div class="card-body p-4">
                    <form method="post" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')

                        <div class="mb-3">
                            <label for="current_password" class="form-label small fw-bold uppercase text-dark">Kata Sandi Saat Ini <span class="text-danger">*</span></label>
                            <input id="current_password" name="current_password" type="password" class="form-control rounded-0 font-monospace @error('current_password', 'updatePassword') is-invalid @enderror" required>
                            @error('current_password', 'updatePassword')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label small fw-bold uppercase text-dark">Kata Sandi Baru <span class="text-danger">*</span></label>
                            <input id="password" name="password" type="password" class="form-control rounded-0 font-monospace @error('password', 'updatePassword') is-invalid @enderror" required>
                            @error('password', 'updatePassword')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label small fw-bold uppercase text-dark">Konfirmasi Kata Sandi Baru <span class="text-danger">*</span></label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control rounded-0 font-monospace" required>
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
        
        {{-- KANVAS KANAN: Manajemen Pas Foto Mahasiswa --}}
        @if(Auth::user()->hasRole('mahasiswa') && Auth::user()->mahasiswa)
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 bg-light">
                <div class="card-header bg-light py-3 border-bottom rounded-0 uppercase fw-bold small text-dark text-center">
                    Pas Foto Identitas Resmi
                </div>
                <div class="card-body p-4 text-center">
                    {{-- Avatar Pas Foto Bingkai Kotak Siku Mutlak 0px --}}
                    <div class="mx-auto mb-3 bg-white p-1 border border-dark border-opacity-25 rounded-0" style="width: 160px; height: 200px;">
                        <img src="{{ Auth::user()->mahasiswa->foto_profil_url }}" alt="Pas Foto Resmi" class="img-fluid rounded-0 w-100 h-100 shadow-none" style="object-fit: cover;">
                    </div>
                    
                    <form method="post" action="{{ route('profile.update.photo') }}" enctype="multipart/form-data">
                        @csrf
                        @method('patch')
                        <div class="mb-3 text-start">
                            <label for="foto_profil" class="form-label small fw-bold uppercase text-dark">Ganti Pas Foto (Formal)</label>
                            <input id="foto_profil" name="foto_profil" type="file" class="form-control rounded-0 @error('foto_profil') is-invalid @enderror" accept="image/*" required>
                            <div class="form-text text-muted font-monospace small">Ukuran maks: 2MB. Rasio: 4x5.</div>
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
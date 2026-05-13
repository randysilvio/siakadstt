@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3 border-bottom pb-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Profil Autentikasi Pengguna</h3>
            <span class="text-muted small uppercase">Tinjauan Akses Terdaftar & Manajemen Kredensial Mandiri</span>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali ke Dasbor
            </a>
        </div>
    </div>

    {{-- NOTIFIKASI SISTEM --}}
    @if (session('success'))
        <div class="alert alert-success border rounded-0 p-3 mb-4 font-monospace small uppercase shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row justify-content-center mb-5">
        <div class="col-md-9">
            
            {{-- Form Pemantauan Informasi Profil --}}
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-white">
                <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                    Informasi Akun Identitas
                </div>
                <div class="card-body p-4">
                    <form method="post" action="{{ route('profile.update') }}">
                        @csrf
                        @method('patch')

                        <div class="mb-3">
                            <label for="name" class="form-label small fw-bold uppercase text-dark">Nama Tampilan Sistem <span class="text-danger">*</span></label>
                            <input id="name" name="name" type="text" class="form-control rounded-0 uppercase @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus>
                            @error('name')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label small fw-bold uppercase text-dark">Surel Autentikasi <span class="text-danger">*</span></label>
                            <input id="email" name="email" type="email" class="form-control rounded-0 @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="d-flex justify-content-end pt-2 border-top">
                            <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm py-2">
                                Terapkan Perubahan Profil
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Form Pemantauan Kredensial Sandi --}}
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 bg-white">
                <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                    Sistem Penyelarasan Kata Sandi
                </div>
                <div class="card-body p-4">
                    <form method="post" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')

                        <div class="mb-3">
                            <label for="current_password" class="form-label small fw-bold uppercase text-dark">Kata Sandi Sesi Berjalan <span class="text-danger">*</span></label>
                            <input id="current_password" name="current_password" type="password" class="form-control rounded-0 font-monospace @error('current_password', 'updatePassword') is-invalid @enderror" required>
                            @error('current_password', 'updatePassword')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label small fw-bold uppercase text-dark">Kata Sandi Sasaran Baru <span class="text-danger">*</span></label>
                            <input id="password" name="password" type="password" class="form-control rounded-0 font-monospace @error('password', 'updatePassword') is-invalid @enderror" required>
                            @error('password', 'updatePassword')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label small fw-bold uppercase text-dark">Verifikasi Ulang Sandi Baru <span class="text-danger">*</span></label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control rounded-0 font-monospace" required>
                        </div>

                        <div class="d-flex justify-content-end pt-2 border-top">
                            <button type="submit" class="btn btn-sm btn-dark rounded-0 px-5 uppercase fw-bold small shadow-sm py-2 text-white">
                                Mutakhirkan Akses Sandi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
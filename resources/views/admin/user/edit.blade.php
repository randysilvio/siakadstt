@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Konfigurasi Akun & Otoritas Pengguna</h3>
            <span class="text-muted small uppercase">Pembaruan Kredensial Login, Reset Kata Sandi, & Peran Operasional</span>
        </div>
        <div>
            <a href="{{ route('admin.user.index') }}" class="btn btn-outline-dark btn-sm rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    <div class="col-lg-8 mx-auto mb-5">
        <form action="{{ route('admin.user.update', $user->id) }}" method="POST" autocomplete="off">
            @csrf
            @method('PUT')

            {{-- Kartu 1: Kredensial & Reset Kata Sandi --}}
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4">
                <div class="card-header bg-dark text-white rounded-0 py-3">
                    <h6 class="mb-0 fw-bold uppercase small"><i class="bi bi-person-Vcard me-2"></i>1. Kredensial Akun & Reset Kata Sandi</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-dark small fw-bold uppercase">Nama Lengkap Pengguna <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-0 uppercase @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-dark small fw-bold uppercase">Alamat Email Login <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control rounded-0 font-monospace @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-dark small fw-bold uppercase">Kata Sandi Baru (Opsional)</label>
                            <input type="password" name="password" class="form-control rounded-0 font-monospace @error('password') is-invalid @enderror" placeholder="ISI JIKA INGIN MERESET SANDI..." autocomplete="new-password">
                            @error('password')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-dark small fw-bold uppercase">Konfirmasi Kata Sandi Baru</label>
                            <input type="password" name="password_confirmation" class="form-control rounded-0 font-monospace" placeholder="ULANGI KATA SANDI BARU..." autocomplete="new-password">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kartu 2: Matriks Otoritas (Role) --}}
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-dark text-white rounded-0 py-3">
                    <h6 class="mb-0 fw-bold uppercase small"><i class="bi bi-shield-lock me-2"></i>2. Matriks Otoritas (Role)</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        @foreach ($roles as $role)
                            <div class="col-md-6">
                                <div class="border border-dark border-opacity-25 rounded-0 p-3 bg-white">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input rounded-0 border-dark" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}"
                                            {{ in_array($role->id, old('roles', $userRoles)) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark uppercase small ms-2" for="role_{{ $role->id }}">
                                            {{ strtoupper($role->display_name) }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="alert bg-light border border-dark border-opacity-25 rounded-0 mt-4 mb-0 p-3">
                        <p class="small text-muted uppercase mb-0">
                            <i class="bi bi-info-circle-fill text-dark me-1"></i> <strong class="text-dark">Catatan:</strong> Pengguna dapat memiliki lebih dari satu peran (Multi-Role). Sistem akan menggabungkan seluruh hak akses dari setiap peran yang dicentang di atas.
                        </p>
                    </div>

                    <div class="d-flex justify-content-end pt-4 mt-4 border-top">
                        <a href="{{ route('admin.user.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
                        <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan Akun
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
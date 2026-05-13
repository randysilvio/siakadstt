@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-0 text-dark fw-bold">Konfigurasi Otoritas Pengguna</h3>
                    <span class="text-muted small">Penetapan hak akses dan peran operasional dalam sistem</span>
                </div>
                <a href="{{ route('admin.user.index') }}" class="btn btn-outline-dark btn-sm rounded-1">Batal</a>
            </div>

            {{-- Profil Akun Read-only --}}
            <div class="card border-0 shadow-sm mb-4 bg-light">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold mb-0">NAMA LENGKAP</label>
                            <p class="fw-bold text-dark fs-5 mb-md-0">{{ $user->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold mb-0">ALAMAT EMAIL SISTEM</label>
                            <p class="font-monospace text-dark mb-0">{{ $user->email }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark">Matriks Otoritas (Role)</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.user.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        @error('roles')
                            <div class="alert alert-danger border-0 small py-2 mb-4">{{ $message }}</div>
                        @enderror

                        <div class="row g-3">
                            @foreach ($roles as $role)
                                <div class="col-md-6">
                                    <div class="border rounded-1 p-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}"
                                                {{ in_array($role->id, $userRoles) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold text-dark ms-2" for="role_{{ $role->id }}">
                                                {{ strtoupper($role->display_name) }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="alert bg-light border mt-4 mb-0">
                            <p class="form-text small mb-0">
                                <i class="bi bi-info-circle me-1"></i> <strong>Catatan:</strong> Pengguna dapat memiliki lebih dari satu peran (Multi-Role). Sistem akan menggabungkan seluruh hak akses dari setiap peran yang dicentang di atas.
                            </p>
                        </div>

                        <hr class="text-muted my-4">
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-5 rounded-1">Simpan Pembaruan Peran</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
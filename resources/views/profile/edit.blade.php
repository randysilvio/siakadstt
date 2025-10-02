@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Edit Profil</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('status') === 'profile-updated')
         <div class="alert alert-success">Informasi profil berhasil diperbarui.</div>
    @endif
     @if(session('status') === 'password-updated')
         <div class="alert alert-success">Password berhasil diperbarui.</div>
    @endif

    <div class="row">
        <div class="col-md-8">
            {{-- Form Update Informasi Profil --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">Informasi Profil</div>
                <div class="card-body">
                    <form method="post" action="{{ route('profile.update') }}">
                        @csrf
                        @method('patch')

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama</label>
                            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                             @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Simpan Informasi</button>
                    </form>
                </div>
            </div>

            {{-- Form Update Password --}}
            <div class="card">
                <div class="card-header fw-bold">Ubah Password</div>
                <div class="card-body">
                    <form method="post" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Password Saat Ini</label>
                            <input id="current_password" name="current_password" type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" required>
                            @error('current_password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password Baru</label>
                            <input id="password" name="password" type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" required>
                             @error('password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan Password</button>
                    </form>
                </div>
            </div>
        </div>
        
        @if(Auth::user()->hasRole('mahasiswa') && Auth::user()->mahasiswa)
        <div class="col-md-4">
            <div class="card">
                <div class="card-header fw-bold">Foto Profil</div>
                <div class="card-body text-center">
                    <img src="{{ Auth::user()->mahasiswa->foto_profil_url }}" alt="Foto Profil" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    <form method="post" action="{{ route('profile.update.photo') }}" enctype="multipart/form-data">
                        @csrf
                        @method('patch')
                        <div class="mb-3">
                            <label for="foto_profil" class="form-label">Unggah Foto Baru</label>
                            <input id="foto_profil" name="foto_profil" type="file" class="form-control @error('foto_profil') is-invalid @enderror">
                            @error('foto_profil')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Simpan Foto</button>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
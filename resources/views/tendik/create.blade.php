@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            {{-- Judul diubah menjadi lebih umum --}}
            <h2>Buat Akun Pengguna Baru (Tendik/Staf)</h2>
            <hr>
            <div class="card">
                <div class="card-body">
                    {{-- Form action diubah agar sesuai dengan route baru yang didaftarkan --}}
                    <form action="{{ route('admin.tendik.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Dropdown Jabatan diubah total untuk mengambil data dari tabel 'roles' --}}
                        <div class="mb-3">
                            <label for="role_id" class="form-label">Peran / Jabatan</label>
                            {{-- Nama input diubah menjadi 'role_id' --}}
                            <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                                <option value="">Pilih Peran</option>
                                {{-- Looping data $roles yang dikirim dari TendikController --}}
                                @foreach($roles as $role)
                                    {{-- Nilai dari option adalah ID peran, dan teksnya adalah nama tampilan peran --}}
                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->display_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>
                        
                        {{-- PERBAIKAN: Menggunakan nama rute admin --}}
                        <a href="{{ route('admin.user.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Buat Akun</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
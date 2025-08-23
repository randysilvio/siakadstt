@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Ubah Peran Pengguna</h1>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Profil Pengguna</h5>
        </div>
        <div class="card-body">
            <p><strong>Nama:</strong> {{ $user->name }}</p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
        </div>
    </div>

    <form action="{{ route('user.update', $user->id) }}" method="POST" class="mt-4">
        @csrf
        @method('PUT')
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Atur Peran</h5>
            </div>
            <div class="card-body">
                @error('roles')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
                @foreach ($roles as $role)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}"
                            {{ in_array($role->id, $userRoles) ? 'checked' : '' }}>
                        <label class="form-check-label" for="role_{{ $role->id }}">
                            {{ $role->display_name }}
                        </label>
                    </div>
                @endforeach
                 <div class="form-text">Pengguna akan memiliki hak akses sesuai dengan semua peran yang dicentang.</div>
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="{{ route('user.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection

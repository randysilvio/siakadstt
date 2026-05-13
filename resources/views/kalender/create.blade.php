@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
                <div>
                    <h3 class="mb-0 text-dark fw-bold uppercase">Registrasi Agenda Baru</h3>
                    <span class="text-muted small uppercase">Penetapan Jadwal Kegiatan Akademik Semester Berjalan</span>
                </div>
                <a href="{{ route('admin.kalender.index') }}" class="btn btn-outline-dark btn-sm rounded-0 uppercase small px-3">Kembali</a>
            </div>

            <div class="card border-0 shadow-sm rounded-0">
                <div class="card-body p-4">
                    <form action="{{ route('admin.kalender.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="judul_kegiatan" class="form-label small fw-bold uppercase">Nama / Judul Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-0 @error('judul_kegiatan') is-invalid @enderror" id="judul_kegiatan" name="judul_kegiatan" value="{{ old('judul_kegiatan') }}" placeholder="Misal: Ujian Akhir Semester Gasal" required autofocus>
                            @error('judul_kegiatan')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="tanggal_mulai" class="form-label small fw-bold uppercase">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control rounded-0 font-monospace @error('tanggal_mulai') is-invalid @enderror" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required>
                                @error('tanggal_mulai')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="tanggal_selesai" class="form-label small fw-bold uppercase">Tanggal Selesai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control rounded-0 font-monospace @error('tanggal_selesai') is-invalid @enderror" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" required>
                                @error('tanggal_selesai')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label small fw-bold uppercase mb-0">Klasifikasi Target Peserta <span class="text-danger">*</span></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="checkAllRoles">
                                    <label class="form-check-label small text-muted uppercase font-bold" for="checkAllRoles" style="font-size: 10px;">Pilih Semua</label>
                                </div>
                            </div>
                            
                            <div class="p-3 bg-light border">
                                <div class="row g-3">
                                    @foreach($roles as $role)
                                    <div class="col-md-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input role-checkbox" type="checkbox" name="target_roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}"
                                                {{ (is_array(old('target_roles')) && in_array($role->id, old('target_roles'))) ? 'checked' : '' }}>
                                            <label class="form-check-label small fw-bold text-dark uppercase" for="role_{{ $role->id }}">
                                                {{ $role->name }}
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @error('target_roles')<div class="text-danger small mt-2 uppercase fw-bold" style="font-size: 10px;">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="deskripsi" class="form-label small fw-bold uppercase">Deskripsi Penjelasan (Opsional)</label>
                            <textarea class="form-control rounded-0" id="deskripsi" name="deskripsi" rows="4" placeholder="Tambahkan informasi detail mengenai kegiatan ini...">{{ old('deskripsi') }}</textarea>
                        </div>

                        <hr class="text-muted my-4">
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-5 rounded-0 fw-bold uppercase">Simpan Agenda</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('checkAllRoles').addEventListener('change', function() {
        document.querySelectorAll('.role-checkbox').forEach(cb => cb.checked = this.checked);
    });
</script>
@endsection
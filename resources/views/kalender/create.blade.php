@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0 fw-bold">Tambah Kegiatan Baru</h2>
                <a href="{{ route('admin.kalender.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="{{ route('admin.kalender.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="judul_kegiatan" class="form-label fw-bold">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg @error('judul_kegiatan') is-invalid @enderror" id="judul_kegiatan" name="judul_kegiatan" value="{{ old('judul_kegiatan') }}" placeholder="Contoh: Ujian Tengah Semester Gasal" required>
                            @error('judul_kegiatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="tanggal_mulai" class="form-label fw-bold">Tanggal Mulai <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-calendar-event"></i></span>
                                    <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required>
                                </div>
                                @error('tanggal_mulai')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="tanggal_selesai" class="form-label fw-bold">Tanggal Selesai <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-calendar-check"></i></span>
                                    <input type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror" id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" required>
                                </div>
                                @error('tanggal_selesai')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-bold mb-0">Target Peserta <span class="text-danger">*</span></label>
                                {{-- [PERBAIKAN] Checkbox Pilih Semua --}}
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="checkAllRoles">
                                    <label class="form-check-label small text-muted" for="checkAllRoles">Pilih Semua</label>
                                </div>
                            </div>
                            
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <div class="row g-2">
                                        @foreach($roles as $role)
                                        <div class="col-sm-4">
                                            <div class="form-check">
                                                <input class="form-check-input role-checkbox" type="checkbox" name="target_roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}"
                                                    {{ (is_array(old('target_roles')) && in_array($role->id, old('target_roles'))) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="role_{{ $role->id }}">
                                                    {{ ucfirst($role->name) }}
                                                </label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @error('target_roles')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="deskripsi" class="form-label fw-bold">Deskripsi (Opsional)</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" placeholder="Tambahkan detail kegiatan di sini...">{{ old('deskripsi') }}</textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Simpan Kegiatan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Script untuk Check All --}}
<script>
    document.getElementById('checkAllRoles').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('.role-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
</script>
@endsection
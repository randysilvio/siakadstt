@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Edit Program Studi</h3>
            <span class="text-muted small uppercase">Pembaruan Informasi Nomenklatur & Alokasi Pimpinan Kaprodi</span>
        </div>
        <div>
            <a href="{{ route('admin.program-studi.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-md-8 mx-auto mb-5">
        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
            Formulir Pembaruan Data Program Studi
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.program-studi.update', $programStudi->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="nama_prodi" class="form-label small fw-bold uppercase text-dark">Nama Program Studi <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light rounded-0"><i class="bi bi-building text-dark"></i></span>
                        <input type="text" class="form-control rounded-0 uppercase @error('nama_prodi') is-invalid @enderror" id="nama_prodi" name="nama_prodi" value="{{ old('nama_prodi', $programStudi->nama_prodi) }}" required>
                        @error('nama_prodi')
                            <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="kaprodi_dosen_id" class="form-label small fw-bold uppercase text-dark">Ketua Program Studi (Kaprodi)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light rounded-0"><i class="bi bi-person-badge text-dark"></i></span>
                        <select class="form-select rounded-0 uppercase @error('kaprodi_dosen_id') is-invalid @enderror" id="kaprodi_dosen_id" name="kaprodi_dosen_id">
                            <option value="">-- PILIH DOSEN KAPRODI --</option>
                            @foreach ($dosens as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('kaprodi_dosen_id', $programStudi->kaprodi_dosen_id) == $dosen->id ? 'selected' : '' }}>
                                    {{ $dosen->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                        @error('kaprodi_dosen_id')
                            <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-text text-muted small uppercase mt-2">
                        <i class="bi bi-info-circle me-1"></i> Dosen yang ditunjuk akan otomatis memiliki hak akses pada dasbor manajemen Kaprodi.
                    </div>
                </div>

                <div class="d-flex justify-content-end pt-4 mt-4 border-top">
                    <a href="{{ route('admin.program-studi.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
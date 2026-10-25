@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Edit Program Studi</h3>
            <span class="text-muted small uppercase">Perbarui Data Program Studi</span>
        </div>
        <a href="{{ route('admin.program-studi.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">Kembali</a>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-md-8 mx-auto mb-5">
        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
            Data Program Studi
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.program-studi.update', $programStudi->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold uppercase text-dark">Kode Prodi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-0 font-monospace uppercase @error('kode_prodi') is-invalid @enderror" name="kode_prodi" value="{{ old('kode_prodi', $programStudi->kode_prodi) }}" maxlength="10" required>
                        @error('kode_prodi')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold uppercase text-dark">Jenjang</label>
                        <select name="jenjang" class="form-select rounded-0 font-monospace fw-bold">
                            @foreach(['S1', 'S2', 'S3', 'D3', 'D4'] as $jjg)
                                <option value="{{ $jjg }}" {{ old('jenjang', $programStudi->jenjang ?? 'S1') == $jjg ? 'selected' : '' }}>{{ $jjg }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold uppercase text-dark">Nama Program Studi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-0 uppercase @error('nama_prodi') is-invalid @enderror" name="nama_prodi" value="{{ old('nama_prodi', $programStudi->nama_prodi) }}" required>
                        @error('nama_prodi')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                    </div>
                </div>

                @if(isset($fakultas) && $fakultas->isNotEmpty())
                <div class="mb-3">
                    <label class="form-label small fw-bold uppercase text-dark">Fakultas</label>
                    <select class="form-select rounded-0 uppercase" name="fakultas_id">
                        <option value="">-- TANPA FAKULTAS / SEKOLAH TINGGI --</option>
                        @foreach ($fakultas as $fak)
                            <option value="{{ $fak->id }}" {{ old('fakultas_id', $programStudi->fakultas_id) == $fak->id ? 'selected' : '' }}>{{ $fak->nama_fakultas }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="mb-4">
                    <label class="form-label small fw-bold uppercase text-dark">Ketua Program Studi</label>
                    <select class="form-select rounded-0 uppercase" name="kaprodi_dosen_id">
                        <option value="">-- PILIH DOSEN --</option>
                        @foreach ($dosens as $dosen)
                            <option value="{{ $dosen->id }}" {{ old('kaprodi_dosen_id', $programStudi->kaprodi_dosen_id) == $dosen->id ? 'selected' : '' }}>{{ $dosen->nama_lengkap }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex justify-content-end pt-3 border-top">
                    <a href="{{ route('admin.program-studi.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
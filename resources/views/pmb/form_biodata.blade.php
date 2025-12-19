@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-teal-600 text-white py-3">
                    <h5 class="mb-0 fw-bold text-center"><i class="bi bi-person-vcard me-2"></i>Lengkapi Biodata Pendaftaran</h5>
                </div>
                <div class="card-body p-5">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('pmb.biodata.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- BAGIAN 1: DATA DIRI --}}
                        <h6 class="text-teal-700 fw-bold border-bottom pb-2 mb-3"><i class="bi bi-person me-1"></i> Data Pribadi</h6>
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Tempat Lahir</label>
                                <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir', $camaba->tempat_lahir) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir', $camaba->tanggal_lahir) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-select" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="L" {{ old('jenis_kelamin', $camaba->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('jenis_kelamin', $camaba->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Agama</label>
                                <select name="agama" class="form-select" required>
                                    <option value="Kristen Protestan" {{ old('agama', $camaba->agama) == 'Kristen Protestan' ? 'selected' : '' }}>Kristen Protestan</option>
                                    <option value="Katolik" {{ old('agama', $camaba->agama) == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                                    <option value="Islam" {{ old('agama', $camaba->agama) == 'Islam' ? 'selected' : '' }}>Islam</option>
                                    <option value="Hindu" {{ old('agama', $camaba->agama) == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                                    <option value="Buddha" {{ old('agama', $camaba->agama) == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                                    <option value="Lainnya" {{ old('agama', $camaba->agama) == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold small">Alamat Lengkap</label>
                                <textarea name="alamat" class="form-control" rows="2" required>{{ old('alamat', $camaba->alamat) }}</textarea>
                            </div>
                        </div>

                        {{-- BAGIAN 2: DATA SEKOLAH --}}
                        <h6 class="text-teal-700 fw-bold border-bottom pb-2 mb-3"><i class="bi bi-building me-1"></i> Data Sekolah Asal</h6>
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Nama Sekolah Asal</label>
                                <input type="text" name="sekolah_asal" class="form-control" placeholder="Contoh: SMA Negeri 1 Fakfak" value="{{ old('sekolah_asal', $camaba->sekolah_asal) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">NISN</label>
                                <input type="number" name="nisn" class="form-control" value="{{ old('nisn', $camaba->nisn) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Tahun Lulus</label>
                                <input type="number" name="tahun_lulus" class="form-control" placeholder="Contoh: 2024" value="{{ old('tahun_lulus', $camaba->tahun_lulus) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Nilai Rata-rata Rapor (Skala 100)</label>
                                <input type="number" step="0.01" name="nilai_rata_rata_rapor" class="form-control" placeholder="Contoh: 85.50" value="{{ old('nilai_rata_rata_rapor', $camaba->nilai_rata_rata_rapor) }}" required>
                            </div>
                        </div>

                        {{-- BAGIAN 3: PILIHAN PRODI --}}
                        <h6 class="text-teal-700 fw-bold border-bottom pb-2 mb-3"><i class="bi bi-mortarboard me-1"></i> Pilihan Program Studi</h6>
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Pilihan Prodi 1 (Prioritas)</label>
                                <select name="pilihan_prodi_1_id" class="form-select" required>
                                    <option value="">-- Pilih Prodi --</option>
                                    @foreach($programStudis as $prodi)
                                        <option value="{{ $prodi->id }}" {{ old('pilihan_prodi_1_id', $camaba->pilihan_prodi_1_id) == $prodi->id ? 'selected' : '' }}>
                                            {{ $prodi->nama_prodi }} ({{ $prodi->jenjang }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Pilihan Prodi 2 (Opsional)</label>
                                <select name="pilihan_prodi_2_id" class="form-select">
                                    <option value="">-- Pilih Prodi (Boleh Kosong) --</option>
                                    @foreach($programStudis as $prodi)
                                        <option value="{{ $prodi->id }}" {{ old('pilihan_prodi_2_id', $camaba->pilihan_prodi_2_id) == $prodi->id ? 'selected' : '' }}>
                                            {{ $prodi->nama_prodi }} ({{ $prodi->jenjang }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- BAGIAN 4: UPLOAD DOKUMEN --}}
                        <h6 class="text-teal-700 fw-bold border-bottom pb-2 mb-3"><i class="bi bi-cloud-upload me-1"></i> Upload Dokumen</h6>
                        <div class="alert alert-info small mb-3">
                            Format diizinkan: JPG, PNG, PDF. Maksimal 2MB per file.
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold small">Scan Ijazah / SKL</label>
                                <input type="file" name="file_ijazah" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                                @if(isset($dokumen['Ijazah']))
                                    <div class="mt-1 text-success small"><i class="bi bi-check-circle"></i> Sudah diupload</div>
                                @endif
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold small">Scan Kartu Keluarga (KK)</label>
                                <input type="file" name="file_kk" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                                @if(isset($dokumen['Kartu Keluarga']))
                                    <div class="mt-1 text-success small"><i class="bi bi-check-circle"></i> Sudah diupload</div>
                                @endif
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold small">Pas Foto Terbaru (Formal)</label>
                                <input type="file" name="file_pas_foto" class="form-control" accept=".jpg,.jpeg,.png">
                                @if(isset($dokumen['Pas Foto']))
                                    <div class="mt-1 text-success small"><i class="bi bi-check-circle"></i> Sudah diupload</div>
                                @endif
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary px-4">Batal</a>
                            <button type="submit" class="btn btn-primary px-5 fw-bold">
                                <i class="bi bi-save me-1"></i> SIMPAN BIODATA
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
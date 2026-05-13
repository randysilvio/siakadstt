@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Lengkapi Biodata Pendaftaran</h3>
            <span class="text-muted small uppercase">Entri Kelengkapan Identitas Pribadi, Riwayat Akademik, & Berkas PMB</span>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali ke Portal
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-md-10 mx-auto mb-5">
        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
            Formulir Portofolio Calon Mahasiswa
        </div>
        <div class="card-body p-4">

            @if ($errors->any())
                <div class="alert alert-danger border rounded-0 p-3 mb-4 font-monospace small uppercase shadow-sm">
                    <strong class="d-block mb-1">GAGAL MENYIMPAN:</strong>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('pmb.biodata.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- BAGIAN 1: DATA PRIBADI --}}
                <h6 class="uppercase fw-bold small text-dark mb-3 border-bottom pb-2">1. Informasi Identitas Pribadi</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold uppercase text-dark">Tempat Lahir <span class="text-danger">*</span></label>
                        <input type="text" name="tempat_lahir" class="form-control rounded-0 uppercase" value="{{ old('tempat_lahir', $camaba->tempat_lahir) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold uppercase text-dark">Tanggal Lahir <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_lahir" class="form-control rounded-0 font-monospace text-center" value="{{ old('tanggal_lahir', $camaba->tanggal_lahir) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold uppercase text-dark">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select name="jenis_kelamin" class="form-select rounded-0 uppercase" required>
                            <option value="">-- PILIH --</option>
                            <option value="L" {{ old('jenis_kelamin', $camaba->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin', $camaba->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold uppercase text-dark">Agama <span class="text-danger">*</span></label>
                        <select name="agama" class="form-select rounded-0 uppercase" required>
                            <option value="Kristen Protestan" {{ old('agama', $camaba->agama) == 'Kristen Protestan' ? 'selected' : '' }}>Kristen Protestan</option>
                            <option value="Katolik" {{ old('agama', $camaba->agama) == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                            <option value="Islam" {{ old('agama', $camaba->agama) == 'Islam' ? 'selected' : '' }}>Islam</option>
                            <option value="Hindu" {{ old('agama', $camaba->agama) == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                            <option value="Buddha" {{ old('agama', $camaba->agama) == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                            <option value="Lainnya" {{ old('agama', $camaba->agama) == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold uppercase text-dark">Alamat Lengkap Domisili <span class="text-danger">*</span></label>
                        <textarea name="alamat" class="form-control rounded-0 uppercase" rows="2" required>{{ old('alamat', $camaba->alamat) }}</textarea>
                    </div>
                </div>

                {{-- BAGIAN 2: DATA SEKOLAH --}}
                <h6 class="uppercase fw-bold small text-dark mb-3 border-bottom pb-2 mt-4">2. Riwayat Pendidikan Asal</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold uppercase text-dark">Nama Sekolah Asal <span class="text-danger">*</span></label>
                        <input type="text" name="sekolah_asal" class="form-control rounded-0 uppercase" placeholder="CONTOH: SMA NEGERI 1 FAKFAK" value="{{ old('sekolah_asal', $camaba->sekolah_asal) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold uppercase text-dark">NISN <span class="text-danger">*</span></label>
                        <input type="number" name="nisn" class="form-control rounded-0 font-monospace" value="{{ old('nisn', $camaba->nisn) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold uppercase text-dark">Tahun Lulus <span class="text-danger">*</span></label>
                        <input type="number" name="tahun_lulus" class="form-control rounded-0 font-monospace" placeholder="2024" value="{{ old('tahun_lulus', $camaba->tahun_lulus) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold uppercase text-dark">Nilai Rata-rata Rapor (Skala 100) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="nilai_rata_rata_rapor" class="form-control rounded-0 font-monospace" placeholder="85.50" value="{{ old('nilai_rata_rata_rapor', $camaba->nilai_rata_rata_rapor) }}" required>
                    </div>
                </div>

                {{-- BAGIAN 3: PILIHAN PRODI --}}
                <h6 class="uppercase fw-bold small text-dark mb-3 border-bottom pb-2 mt-4">3. Pilihan Program Studi</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold uppercase text-dark">Pilihan Prodi 1 (Prioritas Utama) <span class="text-danger">*</span></label>
                        <select name="pilihan_prodi_1_id" class="form-select rounded-0 uppercase" required>
                            <option value="">-- PILIH PRODI --</option>
                            @foreach($programStudis as $prodi)
                                <option value="{{ $prodi->id }}" {{ old('pilihan_prodi_1_id', $camaba->pilihan_prodi_1_id) == $prodi->id ? 'selected' : '' }}>
                                    {{ $prodi->nama_prodi }} ({{ $prodi->jenjang }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold uppercase text-dark">Pilihan Prodi 2 (Cadangan)</label>
                        <select name="pilihan_prodi_2_id" class="form-select rounded-0 uppercase">
                            <option value="">-- PILIH PRODI (OPSIONAL) --</option>
                            @foreach($programStudis as $prodi)
                                <option value="{{ $prodi->id }}" {{ old('pilihan_prodi_2_id', $camaba->pilihan_prodi_2_id) == $prodi->id ? 'selected' : '' }}>
                                    {{ $prodi->nama_prodi }} ({{ $prodi->jenjang }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- BAGIAN 4: UPLOAD DOKUMEN --}}
                <h6 class="uppercase fw-bold small text-dark mb-3 border-bottom pb-2 mt-4">4. Berkas Persyaratan Digital</h6>
                <div class="alert alert-light border border-dark border-opacity-25 rounded-0 small text-muted mb-3 uppercase font-monospace p-3">
                    <strong class="text-dark d-block mb-1">KETENTUAN UNGGAH:</strong>
                    Format berkas yang didukung adalah JPG, PNG, atau PDF. Ukuran berkas maksimal dibatasi hingga 2MB per komponen.
                </div>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold uppercase text-dark">Scan Ijazah / SKL</label>
                        <input type="file" name="file_ijazah" class="form-control rounded-0" accept=".jpg,.jpeg,.png,.pdf">
                        @if(isset($dokumen['Ijazah']))
                            <div class="mt-1 text-success font-monospace uppercase fw-bold" style="font-size: 10px;">
                                <i class="bi bi-check-circle-fill me-1"></i> SUDAH TERUNGGAH
                            </div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold uppercase text-dark">Scan Kartu Keluarga (KK)</label>
                        <input type="file" name="file_kk" class="form-control rounded-0" accept=".jpg,.jpeg,.png,.pdf">
                        @if(isset($dokumen['Kartu Keluarga']))
                            <div class="mt-1 text-success font-monospace uppercase fw-bold" style="font-size: 10px;">
                                <i class="bi bi-check-circle-fill me-1"></i> SUDAH TERUNGGAH
                            </div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold uppercase text-dark">Pas Foto Formal Terbaru</label>
                        <input type="file" name="file_pas_foto" class="form-control rounded-0" accept=".jpg,.jpeg,.png">
                        @if(isset($dokumen['Pas Foto']))
                            <div class="mt-1 text-success font-monospace uppercase fw-bold" style="font-size: 10px;">
                                <i class="bi bi-check-circle-fill me-1"></i> SUDAH TERUNGGAH
                            </div>
                        @endif
                    </div>
                </div>

                <div class="d-flex justify-content-end pt-4 mt-4 border-top">
                    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                        Simpan Biodata
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Edit Draf Surat</h3>
            <span class="text-muted small uppercase">Pembaruan Dokumen / Template Baru</span>
        </div>
        <a href="{{ route('administrasi.surat-keputusan.index') }}" class="btn btn-outline-dark rounded-0 fw-bold shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Arsip
        </a>
    </div>

    <form action="{{ route('administrasi.surat-keputusan.update', $suratKeputusan->id) }}" method="POST">
        @csrf @method('PUT')
        
        <div class="card border-0 shadow-sm rounded-0 mb-4">
            <div class="card-header bg-dark text-white py-3">
                <h6 class="fw-bold mb-0 uppercase small">1. Metadata Dokumen</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Kategori Surat *</label>
                        {{-- [PERBAIKAN]: Tambahkan onchange dan id --}}
                        <select name="jenis_surat" id="jenis_surat" class="form-select rounded-0" required onchange="toggleSegments()">
                            <option value="Surat Keputusan (SK)" {{ $suratKeputusan->jenis_surat == 'Surat Keputusan (SK)' ? 'selected' : '' }}>Surat Keputusan (SK)</option>
                            <option value="Surat Tugas" {{ $suratKeputusan->jenis_surat == 'Surat Tugas' ? 'selected' : '' }}>Surat Tugas</option>
                            <option value="Surat Keterangan" {{ $suratKeputusan->jenis_surat == 'Surat Keterangan' ? 'selected' : '' }}>Surat Keterangan</option>
                            <option value="Surat Undangan" {{ $suratKeputusan->jenis_surat == 'Surat Undangan' ? 'selected' : '' }}>Surat Undangan</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Nomor Surat</label>
                        <input type="text" name="nomor_surat" class="form-control rounded-0" value="{{ $suratKeputusan->nomor_surat }}" placeholder="Bisa dikosongkan jika draf">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Tanggal Terbit</label>
                        <input type="date" name="tanggal_terbit" class="form-control rounded-0" value="{{ $suratKeputusan->tanggal_terbit }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold small">Judul / Perihal *</label>
                        <input type="text" name="judul" class="form-control rounded-0" value="{{ $suratKeputusan->judul }}" required>
                    </div>
                </div>
            </div>
        </div>

        {{-- [PERBAIKAN]: Tambahkan ID segment-konsideran --}}
        <div id="segment-konsideran" class="card border-0 shadow-sm rounded-0 mb-4">
            <div class="card-header bg-dark text-white py-3">
                <h6 class="fw-bold mb-0 uppercase small">2. Konsideran & Diktum (Khusus SK)</h6>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label fw-bold small">MENIMBANG</label>
                    <div id="container-menimbang">
                        @if(is_array($suratKeputusan->menimbang) && count($suratKeputusan->menimbang) > 0)
                            @foreach($suratKeputusan->menimbang as $val)
                            <div class="input-group mb-2 row-item">
                                <input type="text" name="menimbang[]" class="form-control rounded-0" value="{{ $val }}">
                                <button type="button" class="btn btn-danger rounded-0 btn-remove"><i class="bi bi-x"></i></button>
                            </div>
                            @endforeach
                        @else
                            <div class="input-group mb-2 row-item">
                                <input type="text" name="menimbang[]" class="form-control rounded-0">
                                <button type="button" class="btn btn-danger rounded-0 btn-remove"><i class="bi bi-x"></i></button>
                            </div>
                        @endif
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-0" onclick="addRow('container-menimbang', 'menimbang[]')">+ Tambah Baris Menimbang</button>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small">MENGINGAT</label>
                    <div id="container-mengingat">
                        @if(is_array($suratKeputusan->mengingat) && count($suratKeputusan->mengingat) > 0)
                            @foreach($suratKeputusan->mengingat as $val)
                            <div class="input-group mb-2 row-item">
                                <input type="text" name="mengingat[]" class="form-control rounded-0" value="{{ $val }}">
                                <button type="button" class="btn btn-danger rounded-0 btn-remove"><i class="bi bi-x"></i></button>
                            </div>
                            @endforeach
                        @else
                            <div class="input-group mb-2 row-item">
                                <input type="text" name="mengingat[]" class="form-control rounded-0">
                                <button type="button" class="btn btn-danger rounded-0 btn-remove"><i class="bi bi-x"></i></button>
                            </div>
                        @endif
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-0" onclick="addRow('container-mengingat', 'mengingat[]')">+ Tambah Baris Mengingat</button>
                </div>

                 <div class="mb-4">
                    <label class="form-label fw-bold small">MEMPERHATIKAN</label>
                    <div id="container-memperhatikan">
                        @if(is_array($suratKeputusan->memperhatikan) && count($suratKeputusan->memperhatikan) > 0)
                            @foreach($suratKeputusan->memperhatikan as $val)
                            <div class="input-group mb-2 row-item">
                                <input type="text" name="memperhatikan[]" class="form-control rounded-0" value="{{ $val }}">
                                <button type="button" class="btn btn-danger rounded-0 btn-remove"><i class="bi bi-x"></i></button>
                            </div>
                            @endforeach
                        @else
                            <div class="input-group mb-2 row-item">
                                <input type="text" name="memperhatikan[]" class="form-control rounded-0">
                                <button type="button" class="btn btn-danger rounded-0 btn-remove"><i class="bi bi-x"></i></button>
                            </div>
                        @endif
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-0" onclick="addRow('container-memperhatikan', 'memperhatikan[]')">+ Tambah Baris Memperhatikan</button>
                </div>

                {{-- [PERBAIKAN]: Tambahkan ID blok-diktum --}}
                <div id="blok-diktum" class="mb-4">
                    <label class="form-label fw-bold small">MENETAPKAN / MEMUTUSKAN</label>
                    <div id="container-menetapkan">
                         @if(is_array($suratKeputusan->menetapkan) && count($suratKeputusan->menetapkan) > 0)
                            @foreach($suratKeputusan->menetapkan as $val)
                            <div class="input-group mb-2 row-item">
                                <input type="text" name="menetapkan[]" class="form-control rounded-0" value="{{ $val }}">
                                <button type="button" class="btn btn-danger rounded-0 btn-remove"><i class="bi bi-x"></i></button>
                            </div>
                            @endforeach
                        @else
                            <div class="input-group mb-2 row-item">
                                <input type="text" name="menetapkan[]" class="form-control rounded-0">
                                <button type="button" class="btn btn-danger rounded-0 btn-remove"><i class="bi bi-x"></i></button>
                            </div>
                        @endif
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-0" onclick="addRow('container-menetapkan', 'menetapkan[]')">+ Tambah Diktum</button>
                </div>
            </div>
        </div>

        {{-- [PERBAIKAN]: Tambahkan ID blok-isi-surat --}}
        <div id="blok-isi-surat" class="card border-0 shadow-sm rounded-0 mb-4" style="display: none;">
            <div class="card-header bg-dark text-white py-3">
                <h6 class="fw-bold mb-0 uppercase small">3. Isi Surat (Khusus Non-SK)</h6>
            </div>
            <div class="card-body">
                <textarea name="isi_surat" class="form-control rounded-0" rows="6" placeholder="Isi surat jika ini adalah Surat Tugas atau Keterangan biasa...">{{ $suratKeputusan->isi_surat }}</textarea>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-0 mb-4 border-start border-success border-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-success uppercase small"><i class="bi bi-people-fill me-2"></i>4. Tagging Dosen / Panitia</h6>
            </div>
            <div class="card-body bg-light">
                <div id="container-dosen">
                    @if($suratKeputusan->dosens->count() > 0)
                        @foreach($suratKeputusan->dosens as $taggedDosen)
                        <div class="row g-2 mb-2 row-item">
                            <div class="col-md-5">
                                <select name="dosen_id[]" class="form-select rounded-0">
                                    <option value="">-- Pilih Dosen --</option>
                                    @foreach($dosens as $dsn)
                                        <option value="{{ $dsn->id }}" {{ $dsn->id == $taggedDosen->id ? 'selected' : '' }}>{{ $dsn->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="jabatan_dalam_surat[]" class="form-control rounded-0" value="{{ $taggedDosen->pivot->jabatan_dalam_surat }}" placeholder="Jabatan dlm SK (Misal: Ketua Panitia)">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger w-100 rounded-0 btn-remove"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="row g-2 mb-2 row-item">
                            <div class="col-md-5">
                                <select name="dosen_id[]" class="form-select rounded-0">
                                    <option value="">-- Pilih Dosen --</option>
                                    @foreach($dosens as $dsn)
                                        <option value="{{ $dsn->id }}">{{ $dsn->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="jabatan_dalam_surat[]" class="form-control rounded-0" placeholder="Jabatan dlm SK (Misal: Ketua Panitia)">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger w-100 rounded-0 btn-remove"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    @endif
                </div>
                <button type="button" class="btn btn-sm btn-outline-success rounded-0 mt-2 fw-bold" onclick="addDosenRow()">+ Tambah Anggota / Dosen</button>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-0 mb-4">
            <div class="card-header bg-dark text-white py-3">
                <h6 class="fw-bold mb-0 uppercase small">5. Penutup & Otoritas Pengesahan</h6>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label fw-bold small">TEMBUSAN</label>
                    <div id="container-tembusan">
                        @if(is_array($suratKeputusan->tembusan) && count($suratKeputusan->tembusan) > 0)
                            @foreach($suratKeputusan->tembusan as $val)
                            <div class="input-group mb-2 row-item">
                                <input type="text" name="tembusan[]" class="form-control rounded-0" value="{{ $val }}">
                                <button type="button" class="btn btn-danger rounded-0 btn-remove"><i class="bi bi-x"></i></button>
                            </div>
                            @endforeach
                        @else
                            <div class="input-group mb-2 row-item">
                                <input type="text" name="tembusan[]" class="form-control rounded-0">
                                <button type="button" class="btn btn-danger rounded-0 btn-remove"><i class="bi bi-x"></i></button>
                            </div>
                        @endif
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-0" onclick="addRow('container-tembusan', 'tembusan[]')">+ Tambah Tembusan</button>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Jabatan Otoritas *</label>
                        <input type="text" name="penandatangan_jabatan" class="form-control rounded-0" value="{{ $suratKeputusan->penandatangan_jabatan }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Nama Penandatangan *</label>
                        <input type="text" name="penandatangan_nama" class="form-control rounded-0" value="{{ $suratKeputusan->penandatangan_nama }}" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end mb-5">
            <button type="submit" class="btn btn-dark rounded-0 px-5 py-2 fw-bold uppercase shadow"><i class="bi bi-save me-2"></i> SIMPAN PERUBAHAN</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // [PERBAIKAN]: Menambahkan fungsi Javascript untuk mengontrol antarmuka
    function toggleSegments() {
        let jenis = document.getElementById('jenis_surat').value;
        if (jenis === 'Surat Keputusan (SK)') {
            document.getElementById('segment-konsideran').style.display = 'block';
            document.getElementById('blok-diktum').style.display = 'block';
            document.getElementById('blok-isi-surat').style.display = 'none';
        } else {
            document.getElementById('segment-konsideran').style.display = 'none';
            document.getElementById('blok-diktum').style.display = 'none';
            document.getElementById('blok-isi-surat').style.display = 'block';
        }
    }

    // Panggil saat halaman pertama kali dimuat agar sesuai dengan kondisi data
    document.addEventListener("DOMContentLoaded", function() {
        toggleSegments();
    });

    function addRow(containerId, inputName) {
        const container = document.getElementById(containerId);
        const html = `
            <div class="input-group mb-2 row-item">
                <input type="text" name="${inputName}" class="form-control rounded-0">
                <button type="button" class="btn btn-danger rounded-0 btn-remove"><i class="bi bi-x"></i></button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    }

    function addDosenRow() {
        const container = document.getElementById('container-dosen');
        const options = `@foreach($dosens as $dsn)<option value="{{ $dsn->id }}">{{ addslashes($dsn->nama_lengkap) }}</option>@endforeach`;
        const html = `
            <div class="row g-2 mb-2 row-item">
                <div class="col-md-5">
                    <select name="dosen_id[]" class="form-select rounded-0">
                        <option value="">-- Pilih Dosen --</option>
                        ${options}
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="text" name="jabatan_dalam_surat[]" class="form-control rounded-0" placeholder="Jabatan dlm SK (Misal: Ketua Panitia)">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger w-100 rounded-0 btn-remove"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    }

    document.addEventListener('click', function(e) {
        if(e.target.closest('.btn-remove')) {
            e.target.closest('.row-item').remove();
        }
    });
</script>
@endpush
@endsection
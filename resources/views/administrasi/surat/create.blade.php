@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Buat Draf Surat Baru</h3>
            <span class="text-muted small uppercase">Sistem Builder Dokumen & Auto-Tagging Portofolio</span>
        </div>
        <div>
            <a href="{{ route('administrasi.surat-keputusan.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali ke Arsip
            </a>
        </div>
    </div>

    <form action="{{ route('administrasi.surat-keputusan.store') }}" method="POST">
        @csrf

        {{-- SEGMEN 1: IDENTITAS SURAT --}}
        <div class="card border-0 shadow-sm rounded-0 mb-4 border-top border-dark border-4">
            <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
                Segmen 1: Metadata Dokumen
            </div>
            <div class="card-body p-4 bg-white">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold uppercase">Kategori Surat <span class="text-danger">*</span></label>
                        <select name="jenis_surat" id="jenis_surat" class="form-select rounded-0" required onchange="toggleSegments()">
                            <option value="Surat Keputusan (SK)">Surat Keputusan (SK)</option>
                            <option value="Surat Tugas">Surat Tugas</option>
                            <option value="Surat Keterangan">Surat Keterangan</option>
                            <option value="Surat Undangan">Surat Undangan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold uppercase">Nomor Surat (Opsional)</label>
                        <input type="text" name="nomor_surat" class="form-control rounded-0 font-monospace" placeholder="___-D2.4.1/SK.STT.XXXIX/{{ date('Y') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold uppercase">Perihal / Judul Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" name="judul" class="form-control rounded-0 uppercase fw-bold" placeholder="Contoh: PANITIA PRAKTEK PENGALAMAN LAPANGAN (PPL III)" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold uppercase">Tanggal Terbit</label>
                        <input type="date" name="tanggal_terbit" class="form-control rounded-0 font-monospace" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- SEGMEN 2: KONSIDERAN (HANYA UNTUK SK) --}}
        <div id="segment-konsideran" class="card border-0 shadow-sm rounded-0 mb-4">
            <div class="card-header bg-light border-bottom text-dark rounded-0 py-3 uppercase fw-bold small">
                Segmen 2: Dasar Hukum & Konsideran
            </div>
            <div class="card-body p-4 bg-white">
                <div class="mb-4">
                    <label class="form-label small fw-bold uppercase text-primary">MENIMBANG</label>
                    <div id="wrapper-menimbang">
                        <div class="input-group mb-2">
                            <span class="input-group-text rounded-0 bg-light font-monospace">a.</span>
                            <input type="text" name="menimbang[]" class="form-control rounded-0" placeholder="Bahwa...">
                            <button type="button" class="btn btn-outline-danger rounded-0" onclick="removeRow(this)"><i class="bi bi-x"></i></button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-0 mt-1 uppercase fw-bold" style="font-size:11px;" onclick="addRow('wrapper-menimbang', 'menimbang[]', 'huruf')">+ Tambah Baris Menimbang</button>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold uppercase text-primary">MENGINGAT</label>
                    <div id="wrapper-mengingat">
                        <div class="input-group mb-2">
                            <span class="input-group-text rounded-0 bg-light font-monospace">1.</span>
                            <input type="text" name="mengingat[]" class="form-control rounded-0" value="Undang-undang RI. No. 20 Tahun 2003 tentang Sistem Pendidikan Nasional;">
                            <button type="button" class="btn btn-outline-danger rounded-0" onclick="removeRow(this)"><i class="bi bi-x"></i></button>
                        </div>
                        <div class="input-group mb-2">
                            <span class="input-group-text rounded-0 bg-light font-monospace">2.</span>
                            <input type="text" name="mengingat[]" class="form-control rounded-0" value="Statuta STT GPI Papua;">
                            <button type="button" class="btn btn-outline-danger rounded-0" onclick="removeRow(this)"><i class="bi bi-x"></i></button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-0 mt-1 uppercase fw-bold" style="font-size:11px;" onclick="addRow('wrapper-mengingat', 'mengingat[]', 'angka')">+ Tambah Baris Mengingat</button>
                </div>

                <div>
                    <label class="form-label small fw-bold uppercase text-primary">MEMPERHATIKAN</label>
                    <div id="wrapper-memperhatikan">
                        <div class="input-group mb-2">
                            <span class="input-group-text rounded-0 bg-light"><i class="bi bi-arrow-right-short"></i></span>
                            <input type="text" name="memperhatikan[]" class="form-control rounded-0" placeholder="Rapat Koordinasi Pimpinan STT GPI Papua...">
                            <button type="button" class="btn btn-outline-danger rounded-0" onclick="removeRow(this)"><i class="bi bi-x"></i></button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-0 mt-1 uppercase fw-bold" style="font-size:11px;" onclick="addRow('wrapper-memperhatikan', 'memperhatikan[]', 'bullet')">+ Tambah Baris Memperhatikan</button>
                </div>
            </div>
        </div>

        {{-- SEGMEN 3: DIKTUM & ISI SURAT --}}
        <div class="card border-0 shadow-sm rounded-0 mb-4">
            <div class="card-header bg-light border-bottom text-dark rounded-0 py-3 uppercase fw-bold small">
                Segmen 3: Tubuh Surat Utama
            </div>
            <div class="card-body p-4 bg-white">
                <div id="blok-diktum">
                    <label class="form-label small fw-bold uppercase text-success d-block mb-3 border-bottom pb-2">MEMUTUSKAN / MENETAPKAN :</label>
                    <div id="wrapper-menetapkan">
                        <div class="input-group mb-2 align-items-start">
                            <span class="input-group-text rounded-0 bg-light uppercase fw-bold" style="width: 100px;">PERTAMA</span>
                            <textarea name="menetapkan[]" class="form-control rounded-0" rows="2" placeholder="Mengangkat nama-nama yang terlampir..."></textarea>
                            <button type="button" class="btn btn-outline-danger rounded-0" style="height: 100%;" onclick="removeRow(this)"><i class="bi bi-x"></i></button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-success rounded-0 mt-1 uppercase fw-bold" style="font-size:11px;" onclick="addDiktum()">+ Tambah Diktum Baru</button>
                </div>

                <div id="blok-isi-surat" style="display: none;">
                    <label class="form-label small fw-bold uppercase text-dark">Isi Surat (Non-SK)</label>
                    <textarea name="isi_surat" class="form-control rounded-0" rows="6" placeholder="Ketik isi paragraf surat di sini..."></textarea>
                </div>
            </div>
        </div>

        {{-- SEGMEN 4: AUTO-TAGGING & LAMPIRAN --}}
        <div class="card border-0 shadow-sm rounded-0 mb-4 border-start border-primary border-4">
            <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
                <span class="uppercase fw-bold small">Segmen 4: Susunan Panitia & Lampiran Otoritas</span>
            </div>
            <div class="card-body p-4 bg-white">
                
                {{-- TABEL DOSEN (PORTFOLIO LINKED) --}}
                <h6 class="uppercase fw-bold text-primary small mb-3"><i class="bi bi-person-check-fill me-1"></i> A. Entitas Dosen (Terhubung Portofolio)</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle mb-2" id="tabel-panitia">
                        <thead class="bg-light text-dark small uppercase text-center fw-bold">
                            <tr>
                                <th style="width: 45%;">NAMA DOSEN</th>
                                <th style="width: 45%;">JABATAN DALAM SK / PANITIA</th>
                                <th style="width: 10%;">HAPUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select name="dosen_id[]" class="form-select rounded-0 select2-dosen">
                                        <option value="">-- Pilih Dosen --</option>
                                        @foreach($dosens as $d)
                                            <option value="{{ $d->id }}">{{ $d->nama_lengkap }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="jabatan_dalam_surat[]" class="form-control rounded-0 uppercase" placeholder="Contoh: Ketua Panitia">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-0" onclick="removeRowTr(this)"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-primary rounded-0 uppercase fw-bold shadow-sm" style="font-size:11px;" onclick="addPanitia()">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Dosen
                    </button>
                </div>

                {{-- [TAMBAHAN BARU] TABEL ENTITAS NON-DOSEN (TEKS BEBAS) --}}
                <h6 class="uppercase fw-bold text-info small mb-3 mt-5 border-top pt-4"><i class="bi bi-people-fill me-1"></i> B. Entitas Tendik / Mahasiswa / Eksternal (Teks Bebas)</h6>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-2" id="tabel-eksternal">
                        <thead class="bg-light text-dark small uppercase text-center fw-bold">
                            <tr>
                                <th style="width: 45%;">NAMA LENGKAP & GELAR</th>
                                <th style="width: 45%;">JABATAN DALAM SK / PANITIA</th>
                                <th style="width: 10%;">HAPUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <input type="text" name="panitia_lainnya_nama[]" class="form-control rounded-0 uppercase" placeholder="Ketik nama lengkap...">
                                </td>
                                <td>
                                    <input type="text" name="panitia_lainnya_jabatan[]" class="form-control rounded-0 uppercase" placeholder="Contoh: Anggota (Perwakilan BEM)">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-0" onclick="removeRowTrEksternal(this)"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-info text-white rounded-0 uppercase fw-bold shadow-sm" style="font-size:11px;" onclick="addEksternal()">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Anggota Lainnya
                    </button>
                </div>

            </div>
        </div>

        {{-- SEGMEN 5: KAKI SURAT (PENGESAHAN) --}}
        <div class="card border-0 shadow-sm rounded-0 mb-5">
            <div class="card-header bg-light border-bottom text-dark rounded-0 py-3 uppercase fw-bold small">
                Segmen 5: Penutup & Otoritas Pengesahan
            </div>
            <div class="card-body p-4 bg-white">
                <div class="row g-4">
                    <div class="col-md-6 border-end pe-4">
                        <label class="form-label small fw-bold uppercase text-dark">Tembusan (Opsional)</label>
                        <div id="wrapper-tembusan">
                            <div class="input-group mb-2">
                                <span class="input-group-text rounded-0 bg-light font-monospace">1.</span>
                                <input type="text" name="tembusan[]" class="form-control rounded-0" placeholder="Unit Penjaminan Mutu STT GPI Papua">
                                <button type="button" class="btn btn-outline-danger rounded-0" onclick="removeRow(this)"><i class="bi bi-x"></i></button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-dark rounded-0 mt-1 uppercase fw-bold" style="font-size:11px;" onclick="addRow('wrapper-tembusan', 'tembusan[]', 'angka')">+ Tambah Tembusan</button>
                    </div>

                    <div class="col-md-6 ps-4">
                        <label class="form-label small fw-bold uppercase text-dark d-block mb-3">Kolom Tanda Tangan</label>
                        <div class="mb-3">
                            <label class="small text-muted font-monospace d-block mb-1">Jabatan Otoritas</label>
                            <input type="text" name="penandatangan_jabatan" class="form-control rounded-0 fw-bold uppercase" value="KETUA" required>
                        </div>
                        <div>
                            <label class="small text-muted font-monospace d-block mb-1">Nama Lengkap & NUPTK/NIDN</label>
                            <input type="text" name="penandatangan_nama" class="form-control rounded-0 fw-bold" value="Wensly Peniel Raprap, S.Th., M.Pd" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TOMBOL FINALISASI --}}
        <div class="d-flex justify-content-end mb-5">
            <a href="{{ route('administrasi.surat-keputusan.index') }}" class="btn btn-outline-dark rounded-0 px-4 fw-bold uppercase me-3">BATAL</a>
            <button type="submit" class="btn btn-dark rounded-0 px-5 fw-bold uppercase shadow">
                <i class="bi bi-file-earmark-check me-2"></i> GENERATE DRAF SURAT
            </button>
        </div>
    </form>
</div>

{{-- SCRIPT INTERAKTIF FORM BUILDER --}}
<script>
    if(typeof jQuery !== 'undefined'){
        $(document).ready(function() {
            $('.select2-dosen').select2({ theme: 'bootstrap-5', placeholder: "-- Pilih Dosen --" });
        });
    }

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

    function addRow(wrapperId, inputName, type) {
        let wrapper = document.getElementById(wrapperId);
        let rowCount = wrapper.children.length;
        let label = '';
        if (type === 'huruf') { label = String.fromCharCode(97 + rowCount) + '.'; }
        else if (type === 'angka') { label = (rowCount + 1) + '.'; }
        else { label = '<i class="bi bi-arrow-right-short"></i>'; }

        let html = `
            <div class="input-group mb-2">
                <span class="input-group-text rounded-0 bg-light font-monospace">${label}</span>
                <input type="text" name="${inputName}" class="form-control rounded-0">
                <button type="button" class="btn btn-outline-danger rounded-0" onclick="removeRow(this)"><i class="bi bi-x"></i></button>
            </div>
        `;
        wrapper.insertAdjacentHTML('beforeend', html);
    }

    const diktumLabels = ['PERTAMA', 'KEDUA', 'KETIGA', 'KEEMPAT', 'KELIMA', 'KEENAM', 'KETUJUH', 'KEDELAPAN'];
    function addDiktum() {
        let wrapper = document.getElementById('wrapper-menetapkan');
        let rowCount = wrapper.children.length;
        let label = diktumLabels[rowCount] || 'KEDIKTUM ' + (rowCount+1);
        let html = `
            <div class="input-group mb-2 align-items-start">
                <span class="input-group-text rounded-0 bg-light uppercase fw-bold" style="width: 100px;">${label}</span>
                <textarea name="menetapkan[]" class="form-control rounded-0" rows="2"></textarea>
                <button type="button" class="btn btn-outline-danger rounded-0" style="height: 100%;" onclick="removeRow(this)"><i class="bi bi-x"></i></button>
            </div>
        `;
        wrapper.insertAdjacentHTML('beforeend', html);
    }

    function addPanitia() {
        let tbody = document.querySelector('#tabel-panitia tbody');
        let firstRow = tbody.querySelector('tr').cloneNode(true);
        let inputs = firstRow.querySelectorAll('input, select');
        inputs.forEach(input => input.value = '');
        let selectSpan = firstRow.querySelector('.select2-container');
        if(selectSpan) selectSpan.remove();
        firstRow.querySelector('select').classList.remove('select2-hidden-accessible');
        tbody.appendChild(firstRow);
        if(typeof jQuery !== 'undefined'){
            $('.select2-dosen').select2({ theme: 'bootstrap-5', placeholder: "-- Pilih Dosen --" });
        }
    }

    // [TAMBAHAN BARU] Javascript untuk form teks bebas
    function addEksternal() {
        let tbody = document.querySelector('#tabel-eksternal tbody');
        let html = `
            <tr>
                <td><input type="text" name="panitia_lainnya_nama[]" class="form-control rounded-0 uppercase" placeholder="Ketik nama lengkap..."></td>
                <td><input type="text" name="panitia_lainnya_jabatan[]" class="form-control rounded-0 uppercase" placeholder="Contoh: Anggota (Perwakilan BEM)"></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger rounded-0" onclick="removeRowTrEksternal(this)"><i class="bi bi-trash"></i></button></td>
            </tr>
        `;
        tbody.insertAdjacentHTML('beforeend', html);
    }

    function removeRow(button) { button.parentElement.remove(); }
    
    function removeRowTr(button) {
        let tbody = button.closest('tbody');
        if(tbody.children.length > 1) { button.closest('tr').remove(); } 
        else { alert('Minimal harus ada 1 baris (bisa dibiarkan kosong).'); }
    }

    function removeRowTrEksternal(button) {
        let tbody = button.closest('tbody');
        if(tbody.children.length > 1) { button.closest('tr').remove(); } 
        else {
            // Kosongkan nilainya saja jika hanya sisa 1 baris
            button.closest('tr').querySelectorAll('input').forEach(input => input.value = '');
        }
    }

    toggleSegments();
</script>
@endsection
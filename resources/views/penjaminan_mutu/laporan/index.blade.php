@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Pusat Laporan Mutu & Akreditasi</h3>
            <span class="text-muted small uppercase">Ekstraksi Dokumen Resmi Pendukung Evaluasi Diri & Penjaminan Mutu Internal (SPMI)</span>
        </div>
        <div>
            <a href="{{ route('mutu.dashboard') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger border rounded-0 mb-4 font-monospace small uppercase shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        </div>
    @endif

    {{-- KARTU LAPORAN RINGKASAN KINERJA (EKSEKUTIF) --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-white">
        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center p-4 gap-3">
            <div>
                <h6 class="fw-bold text-dark uppercase mb-1">
                    <i class="bi bi-file-earmark-bar-graph-fill me-2"></i>Laporan Eksekutif Terpadu (Ringkasan Kinerja Mutu)
                </h6>
                <p class="text-muted small mb-0 uppercase" style="max-width: 760px;">
                    Cetak hasil analisis komprehensif (Rasio Dosen, IPK Institusi, Hasil EDOM, Profil Lulusan, Tracer Study, & Produktivitas Tridharma) dalam satu dokumen PDF resmi ber-Kop Surat.
                </p>
            </div>
            <a href="{{ route('mutu.laporan.cetak-ringkasan') }}" target="_blank" class="btn btn-sm btn-dark rounded-0 px-4 py-2 uppercase fw-bold small shadow-sm text-nowrap">
                <i class="bi bi-printer-fill me-2"></i>Cetak Laporan Eksekutif
            </a>
        </div>
    </div>

    {{-- GRID MENU LAPORAN KHUSUS --}}
    <div class="row g-4 mb-5">
        {{-- KARTU 1: LAPORAN RPS --}}
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-white py-3 border-bottom rounded-0 d-flex justify-content-between align-items-center">
                    <span class="fw-bold uppercase small text-dark">1. Ketersediaan RPS</span>
                    <span class="badge bg-dark rounded-0 uppercase font-monospace" style="font-size: 10px;">Standar Proses</span>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <form action="{{ route('mutu.laporan.cetak-rps') }}" method="POST" target="_blank">
                        @csrf
                        <p class="text-muted small mb-3 uppercase">
                            Monitoring ketersediaan berkas Rencana Pembelajaran Semester (RPS) per mata kuliah terdaftar.
                        </p>
                        <div class="mb-3">
                            <label class="form-label small fw-bold uppercase text-dark">Lingkup Distribusi</label>
                            <select name="lingkup" class="form-select rounded-0 uppercase small fw-bold text-dark" onchange="toggleProdi('rps', this.value === 'prodi')">
                                <option value="institusi">Seluruh Institusi</option>
                                <option value="prodi">Per Program Studi</option>
                            </select>
                        </div>

                        <div class="mb-3 d-none" id="rps_prodi_select">
                            <label class="form-label small fw-bold uppercase text-dark">Pilih Prodi</label>
                            <select name="program_studi_id" class="form-select rounded-0 uppercase small fw-bold text-dark">
                                @foreach($prodis as $prodi)
                                    <option value="{{ $prodi->id }}">{{ $prodi->nama_prodi }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary rounded-0 w-100 uppercase fw-bold small py-2 shadow-sm mt-2">
                            <i class="bi bi-printer me-1"></i> Cetak Laporan RPS
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- KARTU 2: LAPORAN BEBAN DOSEN & BKD --}}
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-white py-3 border-bottom rounded-0 d-flex justify-content-between align-items-center">
                    <span class="fw-bold uppercase small text-dark">2. Beban Kerja Dosen (BKD)</span>
                    <span class="badge bg-dark rounded-0 uppercase font-monospace" style="font-size: 10px;">Standar SDM</span>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <p class="text-muted small mb-3 uppercase">
                            Rekapitulasi total SKS pengajaran riil dan ekuivalensi BKD (12-16 SKS) setiap dosen tetap.
                        </p>
                        <div class="alert alert-light border border-dark border-opacity-25 rounded-0 small text-muted mb-3 uppercase font-monospace p-2" style="font-size: 11px;">
                            Data ditarik otomatis dari alokasi mata kuliah & portofolio BKD dosen.
                        </div>
                    </div>
                    <a href="{{ route('mutu.laporan.cetak-beban-dosen') }}" target="_blank" class="btn btn-primary rounded-0 w-100 uppercase fw-bold small py-2 shadow-sm text-center">
                        <i class="bi bi-printer me-1"></i> Cetak Beban Mengajar
                    </a>
                </div>
            </div>
        </div>

        {{-- KARTU 3: LAPORAN STUDENT BODY --}}
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-white py-3 border-bottom rounded-0 d-flex justify-content-between align-items-center">
                    <span class="fw-bold uppercase small text-dark">3. Populasi Student Body</span>
                    <span class="badge bg-dark rounded-0 uppercase font-monospace" style="font-size: 10px;">Standar MHS</span>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <form action="{{ route('mutu.laporan.cetak-mahasiswa') }}" method="POST" target="_blank">
                        @csrf
                        <p class="text-muted small mb-3 uppercase">
                            Tabel metrik rekapitulasi seleksi pendaftar PMB dan populasi mahasiswa aktif (TS, TS-1, TS-2).
                        </p>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold uppercase text-dark">Tahun (TS)</label>
                                <select name="tahun_saat_ini" class="form-select rounded-0 font-monospace text-dark fw-bold">
                                    @foreach($tahunTersedia as $tahun)
                                        <option value="{{ $tahun }}">{{ $tahun }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold uppercase text-dark">Lingkup</label>
                                <select name="lingkup" class="form-select rounded-0 uppercase small fw-bold text-dark" onchange="toggleProdi('mhs', this.value === 'prodi')">
                                    <option value="institusi">Institusi</option>
                                    <option value="prodi">Per Prodi</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3 d-none" id="mhs_prodi_select">
                            <label class="form-label small fw-bold uppercase text-dark">Pilih Prodi</label>
                            <select name="program_studi_id" class="form-select rounded-0 uppercase small fw-bold text-dark">
                                @foreach($prodis as $prodi)
                                    <option value="{{ $prodi->id }}">{{ $prodi->nama_prodi }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary rounded-0 w-100 uppercase fw-bold small py-2 shadow-sm">
                            <i class="bi bi-printer me-1"></i> Cetak Student Body
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- KARTU 4: LAPORAN SEBARAN NILAI (GRADE DISTRIBUTION) --}}
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-white py-3 border-bottom rounded-0 d-flex justify-content-between align-items-center">
                    <span class="fw-bold uppercase small text-dark">4. Sebaran Nilai (A s/d E)</span>
                    <span class="badge bg-dark rounded-0 uppercase font-monospace" style="font-size: 10px;">Evaluasi OBE</span>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <form action="{{ route('mutu.laporan.cetak-distribusi-nilai') }}" method="POST" target="_blank">
                        @csrf
                        <p class="text-muted small mb-3 uppercase">
                            Analisis proporsi huruf mutu (A, B, C, D, E) dan persentase kelulusan per mata kuliah.
                        </p>
                        <div class="mb-3">
                            <label class="form-label small fw-bold uppercase text-dark">Lingkup Evaluasi</label>
                            <select name="lingkup" class="form-select rounded-0 uppercase small fw-bold text-dark" onchange="toggleProdi('nilai', this.value === 'prodi')">
                                <option value="institusi">Seluruh Institusi</option>
                                <option value="prodi">Per Program Studi</option>
                            </select>
                        </div>

                        <div class="mb-3 d-none" id="nilai_prodi_select">
                            <label class="form-label small fw-bold uppercase text-dark">Pilih Prodi</label>
                            <select name="program_studi_id" class="form-select rounded-0 uppercase small fw-bold text-dark">
                                @foreach($prodis as $prodi)
                                    <option value="{{ $prodi->id }}">{{ $prodi->nama_prodi }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary rounded-0 w-100 uppercase fw-bold small py-2 shadow-sm">
                            <i class="bi bi-printer me-1"></i> Cetak Distribusi Nilai
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- KARTU 5: PRODUKTIVITAS PENELITIAN & PKM DOSEN --}}
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-white py-3 border-bottom rounded-0 d-flex justify-content-between align-items-center">
                    <span class="fw-bold uppercase small text-dark">5. Riset & PkM Dosen</span>
                    <span class="badge bg-dark rounded-0 uppercase font-monospace" style="font-size: 10px;">Tridharma</span>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <p class="text-muted small mb-3 uppercase">
                            Rekapitulasi publikasi ilmiah jurnal (SINTA/Internasional), buku ber-ISBN, serta Pengabdian Masyarakat (PkM) dosen.
                        </p>
                    </div>
                    <a href="{{ route('mutu.laporan.cetak-tridharma') }}" target="_blank" class="btn btn-primary rounded-0 w-100 uppercase fw-bold small py-2 shadow-sm text-center">
                        <i class="bi bi-printer me-1"></i> Cetak Laporan Tridharma
                    </a>
                </div>
            </div>
        </div>

        {{-- KARTU 6: LAPORAN TRACER STUDY ALUMNI --}}
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-white py-3 border-bottom rounded-0 d-flex justify-content-between align-items-center">
                    <span class="fw-bold uppercase small text-dark">6. Pelacakan Alumni</span>
                    <span class="badge bg-dark rounded-0 uppercase font-monospace" style="font-size: 10px;">Luaran Alumni</span>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <p class="text-muted small mb-3 uppercase">
                            Rekapitulasi masa tunggu pelayanan/kerja pertama lulusan dan tingkat kesesuaian bidang ilmu.
                        </p>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.tracer.index') }}" class="btn btn-outline-dark rounded-0 uppercase fw-bold small py-2">
                            <i class="bi bi-table me-1"></i> Buka Data Tracer Study
                        </a>
                        <a href="{{ route('admin.tracer.cetak') }}" target="_blank" class="btn btn-primary rounded-0 uppercase fw-bold small py-2 shadow-sm">
                            <i class="bi bi-printer me-1"></i> Cetak PDF Tracer Study
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleProdi(type, show) {
        const el = document.getElementById(type + '_prodi_select');
        if (el) {
            el.classList.toggle('d-none', !show);
        }
    }
</script>
@endsection
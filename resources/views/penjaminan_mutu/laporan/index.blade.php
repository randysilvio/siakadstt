@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Pusat Laporan Akreditasi</h3>
            <span class="text-muted small uppercase">Dokumen Borang Pendukung APS (Prodi) & APT (Institusi)</span>
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
        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center p-4">
            <div class="mb-3 mb-md-0">
                <h6 class="fw-bold text-dark uppercase mb-1">
                    <i class="bi bi-file-earmark-bar-graph-fill me-2"></i>Laporan Eksekutif (Ringkasan Kinerja)
                </h6>
                <p class="text-muted small mb-0 uppercase" style="max-width: 700px;">
                    Cetak hasil analisis komprehensif (Rasio Dosen, Tren Pertumbuhan, & Hasil EDOM) dalam satu dokumen PDF resmi ber-Kop Surat.
                </p>
            </div>
            <a href="{{ route('mutu.laporan.cetak-ringkasan') }}" target="_blank" class="btn btn-sm btn-dark rounded-0 px-4 uppercase fw-bold small shadow-sm">
                <i class="bi bi-printer-fill me-2"></i>Cetak Ringkasan
            </a>
        </div>
    </div>

    {{-- GRID MENU LAPORAN KHUSUS --}}
    <div class="row g-4 mb-5">
        {{-- KARTU 1: LAPORAN RPS --}}
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-white py-3 border-bottom rounded-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold uppercase small text-dark">Ketersediaan RPS</span>
                        <span class="badge bg-dark rounded-0 uppercase font-monospace" style="font-size: 10px;">Standar Proses</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-4 uppercase" style="min-height: 40px;">
                        Monitoring ketersediaan berkas Rencana Pembelajaran Semester (RPS) per mata kuliah terdaftar.
                    </p>
                    <form action="{{ route('mutu.laporan.cetak-rps') }}" method="POST" target="_blank">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold uppercase text-dark">Lingkup Distribusi</label>
                            <div class="d-flex gap-3 pt-1 border-top border-bottom py-2 bg-light px-2">
                                <div class="form-check rounded-0 mb-0">
                                    <input class="form-check-input rounded-0 border-dark" type="radio" name="lingkup" id="rps_institusi" value="institusi" checked onchange="toggleProdi('rps', false)">
                                    <label class="form-check-label small uppercase fw-bold text-dark" for="rps_institusi">Institusi</label>
                                </div>
                                <div class="form-check rounded-0 mb-0">
                                    <input class="form-check-input rounded-0 border-dark" type="radio" name="lingkup" id="rps_prodi" value="prodi" onchange="toggleProdi('rps', true)">
                                    <label class="form-check-label small uppercase fw-bold text-dark" for="rps_prodi">Per Prodi</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 d-none" id="rps_prodi_select">
                            <label class="form-label small fw-bold uppercase text-dark">Pilih Prodi</label>
                            <select name="program_studi_id" class="form-select rounded-0 uppercase small fw-bold text-dark">
                                @foreach($prodis as $prodi)
                                    <option value="{{ $prodi->id }}">{{ $prodi->nama_prodi }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary rounded-0 w-100 uppercase fw-bold small py-2 shadow-sm">
                            <i class="bi bi-printer me-1"></i> Cetak Laporan RPS
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- KARTU 2: LAPORAN BEBAN DOSEN --}}
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-white py-3 border-bottom rounded-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold uppercase small text-dark">Beban Kerja Dosen</span>
                        <span class="badge bg-dark rounded-0 uppercase font-monospace" style="font-size: 10px;">Standar SDM</span>
                    </div>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <p class="text-muted small mb-4 uppercase" style="min-height: 40px;">
                            Rekapitulasi total SKS pengajaran riil setiap dosen guna memantau rasio beban kerja pendidik.
                        </p>
                        <div class="alert alert-light border border-dark border-opacity-25 rounded-0 small text-muted mb-4 uppercase font-monospace p-3">
                            <strong class="text-dark d-block mb-1">PARAMETER AKTIF:</strong>
                            Data ditarik otomatis dari alokasi mata kuliah semester berjalan saat ini.
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
                <div class="card-header bg-white py-3 border-bottom rounded-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold uppercase small text-dark">Student Body</span>
                        <span class="badge bg-dark rounded-0 uppercase font-monospace" style="font-size: 10px;">Standar MHS</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-4 uppercase" style="min-height: 40px;">
                        Tabel metrik rekapitulasi seleksi pendaftar dan populasi mahasiswa aktif (TS, TS-1, TS-2).
                    </p>
                    <form action="{{ route('mutu.laporan.cetak-mahasiswa') }}" method="POST" target="_blank">
                        @csrf
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold uppercase text-dark">Tahun (TS)</label>
                                <select name="tahun_saat_ini" class="form-select rounded-0 font-monospace text-dark fw-bold">
                                    @foreach($tahunTersedia as $tahun)
                                        <option value="{{ $tahun }}">{{ $tahun }}</option>
                                    @endforeach
                                    @if($tahunTersedia->isEmpty())
                                        <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                                    @endif
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

                        <div class="mb-4 d-none" id="mhs_prodi_select">
                            <label class="form-label small fw-bold uppercase text-dark">Pilih Prodi</label>
                            <select name="program_studi_id" class="form-select rounded-0 uppercase small fw-bold text-dark">
                                @foreach($prodis as $prodi)
                                    <option value="{{ $prodi->id }}">{{ $prodi->nama_prodi }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary rounded-0 w-100 uppercase fw-bold small py-2 shadow-sm mt-2">
                            <i class="bi bi-printer me-1"></i> Cetak Student Body
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleProdi(type, show) {
        const el = document.getElementById(type + '_prodi_select');
        if (show) {
            el.classList.remove('d-none');
        } else {
            el.classList.add('d-none');
        }
    }
</script>
@endsection
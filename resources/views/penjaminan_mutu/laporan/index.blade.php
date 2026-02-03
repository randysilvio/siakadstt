@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Pusat Laporan Akreditasi</h2>
            <p class="text-muted mb-0">Halaman ini menyediakan dokumen pendukung untuk borang APS (Prodi) dan APT (Institusi).</p>
        </div>
        <a href="{{ route('mutu.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Kembali ke Dashboard
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger mb-4">{{ session('error') }}</div>
    @endif

    {{-- KARTU LAPORAN RINGKASAN KINERJA (EKSEKUTIF) --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary border-2 shadow-sm bg-white">
                <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center p-4">
                    <div class="mb-3 mb-md-0">
                        <h5 class="fw-bold text-primary mb-1">
                            <i class="bi bi-file-earmark-bar-graph-fill me-2"></i>Laporan Eksekutif (Ringkasan Kinerja)
                        </h5>
                        <p class="text-muted mb-0">
                            Cetak seluruh hasil analisa dashboard (Rasio Dosen, Tren Pertumbuhan, & Hasil EDOM) dalam satu dokumen PDF resmi dengan Kop Surat.
                        </p>
                    </div>
                    <a href="{{ route('mutu.laporan.cetak-ringkasan') }}" target="_blank" class="btn btn-primary btn-lg px-4 shadow-sm">
                        <i class="bi bi-printer-fill me-2"></i>Cetak Ringkasan
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- KARTU 1: LAPORAN RPS --}}
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 text-primary rounded p-3 me-3">
                            <i class="bi bi-book-half fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Ketersediaan RPS</h5>
                            <span class="badge bg-primary">Standar Proses</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        Monitoring ketersediaan Rencana Pembelajaran Semester (RPS) per Mata Kuliah.
                    </p>
                    <hr>
                    <form action="{{ route('mutu.laporan.cetak-rps') }}" method="POST" target="_blank">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Lingkup Data</label>
                            <div class="d-flex gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="lingkup" id="rps_institusi" value="institusi" checked onchange="toggleProdi('rps', false)">
                                    <label class="form-check-label small" for="rps_institusi">Institusi</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="lingkup" id="rps_prodi" value="prodi" onchange="toggleProdi('rps', true)">
                                    <label class="form-check-label small" for="rps_prodi">Per Prodi</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 d-none" id="rps_prodi_select">
                            <select name="program_studi_id" class="form-select form-select-sm">
                                @foreach($prodis as $prodi)
                                    <option value="{{ $prodi->id }}">{{ $prodi->nama_prodi }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-outline-primary w-100 btn-sm">
                            <i class="bi bi-printer me-2"></i>Cetak Laporan RPS
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- KARTU 2: LAPORAN BEBAN DOSEN (BARU) --}}
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 text-warning rounded p-3 me-3">
                            <i class="bi bi-briefcase-fill fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Beban Kerja Dosen</h5>
                            <span class="badge bg-warning text-dark">Standar SDM</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        Rekapitulasi total SKS pengajaran setiap dosen untuk melihat rasio beban kerja.
                    </p>
                    <hr>
                    {{-- Form Cetak Beban Dosen --}}
                    <div class="alert alert-light border small text-muted mb-3">
                        <i class="bi bi-info-circle me-1"></i> Data diambil dari Mata Kuliah yang diampu saat ini.
                    </div>
                    <a href="{{ route('mutu.laporan.cetak-beban-dosen') }}" target="_blank" class="btn btn-outline-warning text-dark w-100 btn-sm">
                        <i class="bi bi-printer me-2"></i>Cetak Beban Mengajar
                    </a>
                </div>
            </div>
        </div>

        {{-- KARTU 3: LAPORAN STUDENT BODY --}}
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 text-success rounded p-3 me-3">
                            <i class="bi bi-people-fill fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Student Body</h5>
                            <span class="badge bg-success">Standar MHS</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        Tabel seleksi mahasiswa baru & aktif (TS, TS-1, TS-2).
                    </p>
                    <hr>
                    <form action="{{ route('mutu.laporan.cetak-mahasiswa') }}" method="POST" target="_blank">
                        @csrf
                        <div class="row mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Tahun (TS)</label>
                                <select name="tahun_saat_ini" class="form-select form-select-sm">
                                    @foreach($tahunTersedia as $tahun)
                                        <option value="{{ $tahun }}">{{ $tahun }}</option>
                                    @endforeach
                                    @if($tahunTersedia->isEmpty())
                                        <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Lingkup</label>
                                <select name="lingkup" class="form-select form-select-sm" onchange="toggleProdi('mhs', this.value === 'prodi')">
                                    <option value="institusi">Institusi</option>
                                    <option value="prodi">Per Prodi</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3 d-none" id="mhs_prodi_select">
                            <select name="program_studi_id" class="form-select form-select-sm">
                                @foreach($prodis as $prodi)
                                    <option value="{{ $prodi->id }}">{{ $prodi->nama_prodi }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-outline-success w-100 btn-sm">
                            <i class="bi bi-printer me-2"></i>Cetak Student Body
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
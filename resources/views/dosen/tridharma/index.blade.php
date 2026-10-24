@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- HEADER UTAMA --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 mt-3 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Portofolio Tridharma & Beban Kerja Dosen (BKD)</h3>
            <span class="text-muted small uppercase">Repositori Nasional Standar SISTER, BKD (12-16 SKS), & Angka Kredit Jafung (PAK)</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-primary rounded-0 px-4 py-2 uppercase fw-bold small shadow-sm" data-bs-toggle="modal" data-bs-target="#tambahTridharmaModal">
                <i class="bi bi-plus-lg me-1"></i> Tambah Karya Tridharma
            </button>
            <a href="{{ route('dosen.tridharma.cetak') }}" target="_blank" class="btn btn-sm btn-dark rounded-0 px-3 py-2 uppercase fw-bold small shadow-sm">
                <i class="bi bi-printer-fill me-1"></i> Cetak BKD & CV Akademik
            </a>
            <a href="{{ route('dosen.dashboard') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 py-2 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small font-monospace" role="alert">
            <strong class="d-block mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i> GAGAL MENYIMPAN DATA TRIDHARMA:</strong>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- PANEL SIMULATOR BKD 12-16 SKS & TRACKER JAFUNG --}}
    <div class="card border-0 shadow-sm rounded-0 bg-dark text-white border-top border-secondary border-4 mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-5 border-end border-secondary">
                    <span class="badge bg-{{ $ringkasanBkd['warna_bkd'] }} text-{{ $ringkasanBkd['warna_bkd'] == 'warning' ? 'dark' : 'white' }} rounded-0 font-monospace uppercase mb-2 px-3 py-1">
                        STATUS EVALUASI: {{ $ringkasanBkd['status_bkd'] }}
                    </span>
                    <h4 class="fw-bold uppercase mb-1">{{ $dosen->nama_lengkap }}</h4>
                    <p class="text-white-50 font-monospace small uppercase mb-0">
                        JABATAN FUNGSIONAL: <strong class="text-white">{{ strtoupper($dosen->jabatan_akademik ?? 'TENAGA PENGAJAR') }}</strong> |
                        GOLONGAN: <strong class="text-white">{{ $dosen->pangkat_golongan ?? '-' }}</strong>
                    </p>
                </div>
                <div class="col-lg-7">
                    <div class="row g-2 text-center">
                        <div class="col-6 col-md-2">
                            <div class="border border-secondary p-2 bg-black bg-opacity-25">
                                <span class="d-block text-white-50 font-monospace" style="font-size: 10px;">PENDIDIKAN</span>
                                <span class="fs-5 fw-bold font-monospace text-info">{{ $ringkasanBkd['sks_pendidikan'] }}</span>
                                <small class="d-block text-white-50" style="font-size: 9px;">SKS</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="border border-secondary p-2 bg-black bg-opacity-25">
                                <span class="d-block text-white-50 font-monospace" style="font-size: 10px;">PENELITIAN</span>
                                <span class="fs-5 fw-bold font-monospace text-warning">{{ $ringkasanBkd['sks_penelitian'] }}</span>
                                <small class="d-block text-white-50" style="font-size: 9px;">SKS</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="border border-secondary p-2 bg-black bg-opacity-25">
                                <span class="d-block text-white-50 font-monospace" style="font-size: 10px;">PENGABDIAN</span>
                                <span class="fs-5 fw-bold font-monospace text-success">{{ $ringkasanBkd['sks_pengabdian'] }}</span>
                                <small class="d-block text-white-50" style="font-size: 9px;">SKS</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="border border-secondary p-2 bg-black bg-opacity-25">
                                <span class="d-block text-white-50 font-monospace" style="font-size: 10px;">PENUNJANG</span>
                                <span class="fs-5 fw-bold font-monospace text-light">{{ $ringkasanBkd['sks_penunjang'] }}</span>
                                <small class="d-block text-white-50" style="font-size: 9px;">SKS</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="border border-light p-2 bg-white text-dark">
                                <span class="d-block text-muted font-monospace fw-bold" style="font-size: 10px;">TOTAL BKD</span>
                                <span class="fs-5 fw-bold font-monospace text-dark">{{ $ringkasanBkd['total_sks_bkd'] }}</span>
                                <small class="d-block text-muted fw-bold" style="font-size: 9px;">STANDAR 12-16</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="border border-warning p-2 bg-warning text-dark">
                                <span class="d-block font-monospace fw-bold" style="font-size: 10px;">ESTIMASI KUM</span>
                                <span class="fs-5 fw-bold font-monospace">{{ $ringkasanBkd['total_kum'] }}</span>
                                <small class="d-block fw-bold" style="font-size: 9px;">POIN PAK</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- NAVIGASI TAB 4 UNSUR TRIDHARMA --}}
    <ul class="nav nav-tabs mb-4 rounded-0" id="tridharmaTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-0 fw-bold px-4 small uppercase text-dark" data-bs-toggle="tab" data-bs-target="#tab-penelitian" type="button">
                <i class="bi bi-journal-richtext me-1"></i> 1. Penelitian & Publikasi ({{ $penelitians->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-0 fw-bold px-4 small uppercase text-dark" data-bs-toggle="tab" data-bs-target="#tab-pengabdian" type="button">
                <i class="bi bi-globe-asia-australia me-1"></i> 2. Pengabdian Masyarakat / PkM ({{ $pengabdians->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-0 fw-bold px-4 small uppercase text-dark" data-bs-toggle="tab" data-bs-target="#tab-pendidikan" type="button">
                <i class="bi bi-mortarboard-fill me-1"></i> 3. Pendidikan & Pengajaran ({{ $mataKuliahs->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-0 fw-bold px-4 small uppercase text-dark" data-bs-toggle="tab" data-bs-target="#tab-penunjang" type="button">
                <i class="bi bi-award-fill me-1"></i> 4. Penunjang & SK ({{ $penunjangs->count() + $suratKeputusans->count() }})
            </button>
        </li>
    </ul>

    <div class="tab-content mb-5">
        {{-- TAB 1: PENELITIAN & PUBLIKASI ILMIAH --}}
        <div class="tab-pane fade show active" id="tab-penelitian">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
                    <span class="uppercase fw-bold small">Daftar Karya Ilmiah, Jurnal SINTA/Internasional, Buku, & HKI</span>
                    <span class="badge bg-warning text-dark rounded-0 font-monospace">TOTAL: {{ $penelitians->sum('sks_bkd') }} SKS BKD</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 small">
                            <thead class="bg-light text-dark uppercase text-center fw-bold">
                                <tr>
                                    <th style="width: 5%;">NO</th>
                                    <th class="text-start" style="width: 34%;">JUDUL KARYA ILMIAH / PENELITIAN</th>
                                    <th style="width: 18%;">KATEGORI & PERAN</th>
                                    <th style="width: 18%;">JURNAL / PENERBIT / ISSN</th>
                                    <th style="width: 8%;">TAHUN</th>
                                    <th style="width: 7%;">SKS/KUM</th>
                                    <th style="width: 10%;">BUKTI / AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($penelitians as $index => $item)
                                    <tr>
                                        <td class="text-center font-monospace fw-bold text-muted">{{ $index + 1 }}</td>
                                        <td class="ps-3">
                                            <span class="fw-bold text-dark uppercase d-block">{{ $item->judul }}</span>
                                            @if($item->tautan_eksternal)
                                                <a href="{{ $item->tautan_eksternal }}" target="_blank" class="small font-monospace text-primary text-decoration-none">
                                                    <i class="bi bi-link-45deg"></i> Buka Tautan Publikasi / DOI
                                                </a>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-dark text-white rounded-0 font-monospace d-block mb-1" style="font-size: 10px;">{{ strtoupper($item->kategori) }}</span>
                                            <span class="text-muted font-monospace" style="font-size: 11px;">{{ strtoupper($item->peran) }}</span>
                                        </td>
                                        <td class="text-center font-monospace small">
                                            <div class="fw-bold text-dark">{{ $item->nama_jurnal_penerbit_lokasi ?? '-' }}</div>
                                            <div class="text-muted" style="font-size: 10px;">{{ $item->nomor_identitas ?? '' }}</div>
                                        </td>
                                        <td class="text-center font-monospace fw-bold">{{ $item->tahun }} ({{ $item->semester }})</td>
                                        <td class="text-center font-monospace">
                                            <span class="fw-bold text-primary">{{ $item->sks_bkd }} SKS</span><br>
                                            <span class="text-muted" style="font-size: 10px;">{{ $item->angka_kredit }} KUM</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group rounded-0">
                                                @if($item->file_bukti)
                                                    <a href="{{ asset('storage/' . $item->file_bukti) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2" title="Lihat Bukti PDF">
                                                        <i class="bi bi-file-earmark-pdf"></i>
                                                    </a>
                                                @endif
                                                <form action="{{ route('dosen.tridharma.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus karya penelitian ini dari portofolio?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2" title="Hapus">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center py-5 text-muted uppercase fw-bold">Belum ada data penelitian atau publikasi ilmiah yang ditambahkan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 2: PENGABDIAN KEPADA MASYARAKAT (PKM) --}}
        <div class="tab-pane fade" id="tab-pengabdian">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
                    <span class="uppercase fw-bold small">Daftar Pengabdian Masyarakat (PkM) & Pelayanan Gerejawi</span>
                    <span class="badge bg-success text-white rounded-0 font-monospace">TOTAL: {{ $pengabdians->sum('sks_bkd') }} SKS BKD</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 small">
                            <thead class="bg-light text-dark uppercase text-center fw-bold">
                                <tr>
                                    <th style="width: 5%;">NO</th>
                                    <th class="text-start" style="width: 34%;">JUDUL KEGIATAN PENGABDIAN / PELAYANAN</th>
                                    <th style="width: 18%;">KATEGORI & PERAN</th>
                                    <th style="width: 18%;">LOKASI / MITRA / NO. ST</th>
                                    <th style="width: 8%;">TAHUN</th>
                                    <th style="width: 7%;">SKS/KUM</th>
                                    <th style="width: 10%;">BUKTI / AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pengabdians as $index => $item)
                                    <tr>
                                        <td class="text-center font-monospace fw-bold text-muted">{{ $index + 1 }}</td>
                                        <td class="ps-3 fw-bold text-dark uppercase">{{ $item->judul }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-success text-white rounded-0 font-monospace d-block mb-1" style="font-size: 10px;">{{ strtoupper($item->kategori) }}</span>
                                            <span class="text-muted font-monospace" style="font-size: 11px;">{{ strtoupper($item->peran) }}</span>
                                        </td>
                                        <td class="text-center font-monospace small">
                                            <div class="fw-bold text-dark">{{ $item->nama_jurnal_penerbit_lokasi ?? '-' }}</div>
                                            <div class="text-muted" style="font-size: 10px;">{{ $item->nomor_identitas ?? '' }}</div>
                                        </td>
                                        <td class="text-center font-monospace fw-bold">{{ $item->tahun }} ({{ $item->semester }})</td>
                                        <td class="text-center font-monospace">
                                            <span class="fw-bold text-primary">{{ $item->sks_bkd }} SKS</span><br>
                                            <span class="text-muted" style="font-size: 10px;">{{ $item->angka_kredit }} KUM</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group rounded-0">
                                                @if($item->file_bukti)
                                                    <a href="{{ asset('storage/' . $item->file_bukti) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2">
                                                        <i class="bi bi-file-earmark-pdf"></i>
                                                    </a>
                                                @endif
                                                <form action="{{ route('dosen.tridharma.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kegiatan PkM ini?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center py-5 text-muted uppercase fw-bold">Belum ada data kegiatan Pengabdian kepada Masyarakat (PkM) yang dicatat.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 3: PENDIDIKAN & PENGAJARAN (OTOMATIS DARI SIAKAD) --}}
        <div class="tab-pane fade" id="tab-pendidikan">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
                    <span class="uppercase fw-bold small">Unsur Pendidikan & Pengajaran (Tersinkron Otomatis dari SIAKAD)</span>
                    <span class="badge bg-info text-dark rounded-0 font-monospace">TOTAL: {{ $ringkasanBkd['sks_pendidikan'] }} SKS BKD</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 small">
                            <thead class="bg-light text-dark uppercase text-center fw-bold">
                                <tr>
                                    <th style="width: 6%;">NO</th>
                                    <th style="width: 14%;">KODE MK</th>
                                    <th class="text-start">NAMA MATA KULIAH DIAMPU</th>
                                    <th style="width: 10%;">SMT</th>
                                    <th style="width: 10%;">BEBAN SKS</th>
                                    <th style="width: 15%;">BUKTI RPS</th>
                                    <th style="width: 18%;">BUKTI HADIR & NILAI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mataKuliahs as $index => $mk)
                                    <tr>
                                        <td class="text-center font-monospace">{{ $index + 1 }}</td>
                                        <td class="text-center font-monospace fw-bold text-primary">{{ $mk->kode_mk }}</td>
                                        <td class="ps-3 fw-bold uppercase text-dark">{{ $mk->nama_mk }}</td>
                                        <td class="text-center font-monospace">{{ $mk->semester }}</td>
                                        <td class="text-center font-monospace fw-bold">{{ $mk->sks }} SKS</td>
                                        <td class="text-center">
                                            @if($mk->file_rps)
                                                <a href="{{ asset('storage/' . $mk->file_rps) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-0 py-1 px-2 font-monospace fw-bold" style="font-size: 10px;">
                                                    <i class="bi bi-file-earmark-pdf me-1"></i>UNDUH RPS
                                                </a>
                                            @else
                                                <span class="badge bg-warning text-dark rounded-0 font-monospace">BELUM ADA</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('dosen.cetak_absensi', $mk->id) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2 font-monospace fw-bold" style="font-size: 10px;">
                                                <i class="bi bi-printer me-1"></i>PRESENSI
                                            </a>
                                            <a href="{{ route('nilai.show', $mk->id) }}" class="btn btn-sm btn-dark rounded-0 py-1 px-2 font-monospace fw-bold" style="font-size: 10px;">
                                                DPNA
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center py-4 text-muted uppercase fw-bold">Belum ada mata kuliah yang diampu pada kurikulum aktif.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 4: PENUNJANG & SK KEPANITIAAN --}}
        <div class="tab-pane fade" id="tab-penunjang">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
                    <span class="uppercase fw-bold small">Unsur Penunjang Akademik & SK Kepanitiaan E-Office</span>
                    <span class="badge bg-light text-dark rounded-0 font-monospace">TOTAL: {{ $ringkasanBkd['sks_penunjang'] }} SKS BKD</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 small">
                            <thead class="bg-light text-dark uppercase text-center fw-bold">
                                <tr>
                                    <th style="width: 15%;">SUMBER DATA</th>
                                    <th class="text-start">KEGIATAN PENUNJANG / PERIHAL SK</th>
                                    <th style="width: 20%;">JABATAN / PERAN</th>
                                    <th style="width: 18%;">NOMOR SK / SERTIFIKAT</th>
                                    <th style="width: 12%;">BERKAS BUKTI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($suratKeputusans as $sk)
                                    <tr>
                                        <td class="text-center"><span class="badge bg-primary rounded-0 font-monospace">E-OFFICE SK</span></td>
                                        <td class="ps-3 fw-bold uppercase text-dark">{{ $sk->judul }}</td>
                                        <td class="text-center font-monospace uppercase">{{ $sk->pivot->jabatan_dalam_surat ?? 'ANGGOTA' }}</td>
                                        <td class="text-center font-monospace">{{ $sk->nomor_surat }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('surat-keputusan.download', $sk->id) }}" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2 font-monospace fw-bold" style="font-size: 10px;">
                                                <i class="bi bi-file-earmark-pdf me-1"></i>UNDUH SK
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                @foreach($penunjangs as $item)
                                    <tr>
                                        <td class="text-center"><span class="badge bg-dark rounded-0 font-monospace">INPUT MANDIRI</span></td>
                                        <td class="ps-3 fw-bold uppercase text-dark">{{ $item->judul }} ({{ $item->tahun }})</td>
                                        <td class="text-center font-monospace uppercase">{{ $item->peran }}</td>
                                        <td class="text-center font-monospace">{{ $item->nomor_identitas ?? '-' }}</td>
                                        <td class="text-center">
                                            @if($item->file_bukti)
                                                <a href="{{ asset('storage/' . $item->file_bukti) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                </a>
                                            @endif
                                            <form action="{{ route('dosen.tridharma.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data penunjang ini?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                @if($suratKeputusans->isEmpty() && $penunjangs->isEmpty())
                                    <tr><td colspan="5" class="text-center py-5 text-muted uppercase fw-bold">Belum ada data kegiatan penunjang atau SK kepanitiaan.</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH KARYA TRIDHARMA --}}
<div class="modal fade" id="tambahTridharmaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-0 border-0 border-top border-dark border-4">
            <form action="{{ route('dosen.tridharma.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-dark text-white rounded-0 py-3">
                    <h6 class="modal-title fw-bold uppercase small"><i class="bi bi-plus-circle me-2"></i>Catat Karya Tridharma Baru (Standar SISTER & BKD)</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold uppercase text-dark">Unsur Tridharma <span class="text-danger">*</span></label>
                            <select name="unsur" id="unsurSelect" class="form-select rounded-0 uppercase small fw-bold" required>
                                <option value="penelitian">Penelitian & Publikasi Ilmiah</option>
                                <option value="pengabdian">Pengabdian Masyarakat (PkM)</option>
                                <option value="penunjang">Penunjang Akademik / Sertifikasi</option>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label small fw-bold uppercase text-dark">Kategori Capaian (Standar SISTER) <span class="text-danger">*</span></label>
                            <select name="kategori" id="kategoriSelect" class="form-select rounded-0 uppercase small fw-bold" required>
                                <option value="Jurnal Nasional Terakreditasi (SINTA 1-6)" data-sks="3" data-kum="25">Jurnal Nasional Terakreditasi (SINTA 1-6)</option>
                                <option value="Jurnal Internasional Bereputasi (Scopus/WoS)" data-sks="4" data-kum="40">Jurnal Internasional Bereputasi (Scopus/WoS)</option>
                                <option value="Jurnal Nasional / Prosiding Seminar" data-sks="2" data-kum="10">Jurnal Nasional / Prosiding Seminar</option>
                                <option value="Buku Ajar / Monograf / Referensi Ber-ISBN" data-sks="3" data-kum="20">Buku Ajar / Monograf / Referensi Ber-ISBN</option>
                                <option value="Pelayanan Gerejawi / Pembinaan Warga Jemaat" data-sks="1" data-kum="3">Pelayanan Gerejawi / Pembinaan Warga Jemaat</option>
                                <option value="Penyuluhan / Pengabdian Masyarakat Terpadu" data-sks="2" data-kum="5">Penyuluhan / Pengabdian Masyarakat Terpadu</option>
                                <option value="Narasumber / Reviewer / Anggota Profesi" data-sks="1" data-kum="2">Narasumber / Reviewer / Anggota Profesi</option>
                                <option value="Sertifikasi Dosen / Pekerti / Applied Approach" data-sks="1" data-kum="3">Sertifikasi Dosen / Pekerti / Applied Approach</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold uppercase text-dark">Judul Publikasi / Penelitian / Kegiatan PkM <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control rounded-0" placeholder="Ketik judul lengkap artikel jurnal, buku, atau kegiatan pengabdian..." required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold uppercase text-dark">Peran / Kedudukan <span class="text-danger">*</span></label>
                            <select name="peran" class="form-select rounded-0 uppercase small fw-bold" required>
                                <option value="Penulis Utama / Ketua">Penulis Utama / Ketua</option>
                                <option value="Penulis Anggota">Penulis Anggota</option>
                                <option value="Ketua Pelaksana PkM">Ketua Pelaksana PkM</option>
                                <option value="Narasumber / Pembicara">Narasumber / Pembicara</option>
                                <option value="Peserta / Anggota">Peserta / Anggota</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold uppercase text-dark">Tahun Pelaksanaan <span class="text-danger">*</span></label>
                            <input type="number" name="tahun" class="form-control rounded-0 font-monospace fw-bold" value="{{ date('Y') }}" min="2000" max="{{ date('Y') + 1 }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold uppercase text-dark">Semester <span class="text-danger">*</span></label>
                            <select name="semester" class="form-select rounded-0 uppercase small fw-bold" required>
                                <option value="Ganjil">Ganjil</option>
                                <option value="Genap">Genap</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold uppercase text-dark">Nama Jurnal / Penerbit / Lokasi Mitra</label>
                            <input type="text" name="nama_jurnal_penerbit_lokasi" class="form-control rounded-0" placeholder="Contoh: Jurnal Teologi Фидеi / Jemaat GPI Fakfak">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold uppercase text-dark">ISSN / ISBN / DOI / Nomor Surat Tugas</label>
                            <input type="text" name="nomor_identitas" class="form-control rounded-0 font-monospace" placeholder="Contoh: ISSN 2407-1234 / ST-012/STT/2026">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold uppercase text-dark">Beban SKS BKD <span class="text-danger">*</span></label>
                            <input type="number" step="0.25" name="sks_bkd" id="sksInput" class="form-control rounded-0 font-monospace fw-bold" value="3.00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold uppercase text-dark">Angka Kredit (KUM PAK) <span class="text-danger">*</span></label>
                            <input type="number" step="0.5" name="angka_kredit" id="kumInput" class="form-control rounded-0 font-monospace fw-bold" value="25.00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold uppercase text-dark">Unggah Bukti (PDF, Maks 5MB)</label>
                            <input type="file" name="file_bukti" class="form-control rounded-0" accept=".pdf,.jpg,.jpeg,.png">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold uppercase text-dark">Tautan Artikel Jurnal / Google Scholar / SINTA / Repositori (Opsional)</label>
                            <input type="url" name="tautan_eksternal" class="form-control rounded-0 font-monospace" placeholder="https://...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top rounded-0">
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                        <i class="bi bi-save me-1"></i> Simpan ke Portofolio
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const kategoriSelect = document.getElementById('kategoriSelect');
        const sksInput = document.getElementById('sksInput');
        const kumInput = document.getElementById('kumInput');

        if (kategoriSelect) {
            kategoriSelect.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                if (opt.dataset.sks) sksInput.value = parseFloat(opt.dataset.sks).toFixed(2);
                if (opt.dataset.kum) kumInput.value = parseFloat(opt.dataset.kum).toFixed(2);
            });
        }
    });
</script>
@endsection
@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 mt-3 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Data Tridharma & Beban Kerja Dosen <x-info-aturan kunci="bkd_total" /></h3>
            <span class="text-muted small uppercase">{{ $dosen->nama_lengkap }} — NIDN: {{ $dosen->nidn ?? '-' }}</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-primary rounded-0 px-4 py-2 uppercase fw-bold small shadow-sm" data-bs-toggle="modal" data-bs-target="#tambahTridharmaModal">
                Tambah Kegiatan
            </button>
            <a href="{{ route('dosen.tridharma.cetak') }}" target="_blank" class="btn btn-sm btn-dark rounded-0 px-3 py-2 uppercase fw-bold small shadow-sm">
                Cetak Laporan BKD
            </a>
            <a href="{{ route('dosen.dashboard') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 py-2 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold">
            {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small font-monospace">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- RINGKASAN SKS TRIDHARMA SERAGAM MONOKROM --}}
    <div class="row g-3 mb-4">
        <div class="col-md-2 col-6">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 bg-white h-100">
                <div class="card-body p-3">
                    <span class="text-muted small font-monospace uppercase fw-bold d-block">PENDIDIKAN <x-info-aturan kunci="bkd_pendidikan" /></span>
                    <h4 class="fw-bold font-monospace text-dark mb-0 mt-1">{{ $ringkasanBkd['sks_pendidikan'] }} <small class="fs-6">SKS</small></h4>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 bg-white h-100">
                <div class="card-body p-3">
                    <span class="text-muted small font-monospace uppercase fw-bold d-block">PENELITIAN <x-info-aturan kunci="bkd_tridharma" /></span>
                    <h4 class="fw-bold font-monospace text-dark mb-0 mt-1">{{ $ringkasanBkd['sks_penelitian'] }} <small class="fs-6">SKS</small></h4>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 bg-white h-100">
                <div class="card-body p-3">
                    <span class="text-muted small font-monospace uppercase fw-bold d-block">PENGABDIAN <x-info-aturan kunci="bkd_tridharma" /></span>
                    <h4 class="fw-bold font-monospace text-dark mb-0 mt-1">{{ $ringkasanBkd['sks_pengabdian'] }} <small class="fs-6">SKS</small></h4>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 bg-white h-100">
                <div class="card-body p-3">
                    <span class="text-muted small font-monospace uppercase fw-bold d-block">PENUNJANG <x-info-aturan kunci="bkd_tridharma" /></span>
                    <h4 class="fw-bold font-monospace text-dark mb-0 mt-1">{{ $ringkasanBkd['sks_penunjang'] }} <small class="fs-6">SKS</small></h4>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 bg-dark text-white h-100">
                <div class="card-body p-3">
                    <span class="text-white-50 small font-monospace uppercase fw-bold d-block">TOTAL BEBAN <x-info-aturan kunci="bkd_total" terang /></span>
                    <h4 class="fw-bold font-monospace text-white mb-0 mt-1">{{ $ringkasanBkd['total_sks_bkd'] }} <small class="fs-6">SKS</small></h4>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 bg-white h-100">
                <div class="card-body p-3">
                    <span class="text-muted small font-monospace uppercase fw-bold d-block">ANGKA KREDIT <x-info-aturan kunci="bkd_kum" /></span>
                    <h4 class="fw-bold font-monospace text-dark mb-0 mt-1">{{ $ringkasanBkd['total_kum'] }}</h4>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs mb-4 rounded-0 border-bottom border-dark border-2" id="tridharmaTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-0 fw-bold px-4 small uppercase text-dark" data-bs-toggle="tab" data-bs-target="#tab-penelitian" type="button">
                Penelitian ({{ $penelitians->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-0 fw-bold px-4 small uppercase text-dark" data-bs-toggle="tab" data-bs-target="#tab-pengabdian" type="button">
                Pengabdian ({{ $pengabdians->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-0 fw-bold px-4 small uppercase text-dark" data-bs-toggle="tab" data-bs-target="#tab-pendidikan" type="button">
                Pengajaran ({{ $mataKuliahs->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-0 fw-bold px-4 small uppercase text-dark" data-bs-toggle="tab" data-bs-target="#tab-penunjang" type="button">
                Penunjang ({{ $penunjangs->count() + $suratKeputusans->count() }})
            </button>
        </li>
    </ul>

    <div class="tab-content mb-5">
        <div class="tab-pane fade show active" id="tab-penelitian">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
                    <span class="uppercase fw-bold small">Penelitian & Publikasi Ilmiah</span>
                    <span class="badge bg-light text-dark rounded-0 font-monospace">{{ $penelitians->sum('sks_bkd') }} SKS</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 small">
                            <thead class="bg-light text-dark uppercase text-center fw-bold">
                                <tr>
                                    <th style="width: 5%;">NO</th>
                                    <th class="text-start" style="width: 34%;">JUDUL</th>
                                    <th style="width: 18%;">KATEGORI & PERAN</th>
                                    <th style="width: 18%;">PENERBIT / JURNAL</th>
                                    <th style="width: 8%;">TAHUN</th>
                                    <th style="width: 7%;">SKS</th>
                                    <th style="width: 10%;">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($penelitians as $index => $item)
                                    <tr>
                                        <td class="text-center font-monospace fw-bold text-muted">{{ $index + 1 }}</td>
                                        <td class="ps-3">
                                            <span class="fw-bold text-dark uppercase d-block">{{ $item->judul }}</span>
                                            @if($item->tautan_eksternal)
                                                <a href="{{ $item->tautan_eksternal }}" target="_blank" class="small font-monospace text-dark text-decoration-underline">Tautan Publikasi</a>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="fw-bold text-dark uppercase d-block" style="font-size: 11px;">{{ $item->kategori }}</span>
                                            <span class="text-muted font-monospace" style="font-size: 10px;">{{ strtoupper($item->peran) }}</span>
                                        </td>
                                        <td class="text-center font-monospace small">
                                            <div class="fw-bold text-dark">{{ $item->nama_jurnal_penerbit_lokasi ?? '-' }}</div>
                                            <div class="text-muted" style="font-size: 10px;">{{ $item->nomor_identitas ?? '' }}</div>
                                        </td>
                                        <td class="text-center font-monospace fw-bold">{{ $item->tahun }} ({{ $item->semester }})</td>
                                        <td class="text-center font-monospace fw-bold">{{ $item->sks_bkd }}</td>
                                        <td class="text-center">
                                            <div class="btn-group rounded-0">
                                                @if($item->file_bukti)
                                                    <a href="{{ asset('storage/' . $item->file_bukti) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2">Berkas</a>
                                                @endif
                                                <form action="{{ route('dosen.tridharma.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data ini?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center py-5 text-muted uppercase fw-bold">Belum ada data penelitian.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-pengabdian">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
                    <span class="uppercase fw-bold small">Pengabdian kepada Masyarakat</span>
                    <span class="badge bg-light text-dark rounded-0 font-monospace">{{ $pengabdians->sum('sks_bkd') }} SKS</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 small">
                            <thead class="bg-light text-dark uppercase text-center fw-bold">
                                <tr>
                                    <th style="width: 5%;">NO</th>
                                    <th class="text-start" style="width: 34%;">JUDUL KEGIATAN</th>
                                    <th style="width: 18%;">KATEGORI & PERAN</th>
                                    <th style="width: 18%;">LOKASI / MITRA</th>
                                    <th style="width: 8%;">TAHUN</th>
                                    <th style="width: 7%;">SKS</th>
                                    <th style="width: 10%;">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pengabdians as $index => $item)
                                    <tr>
                                        <td class="text-center font-monospace fw-bold text-muted">{{ $index + 1 }}</td>
                                        <td class="ps-3 fw-bold text-dark uppercase">{{ $item->judul }}</td>
                                        <td class="text-center">
                                            <span class="fw-bold text-dark uppercase d-block" style="font-size: 11px;">{{ $item->kategori }}</span>
                                            <span class="text-muted font-monospace" style="font-size: 10px;">{{ strtoupper($item->peran) }}</span>
                                        </td>
                                        <td class="text-center font-monospace small">{{ $item->nama_jurnal_penerbit_lokasi ?? '-' }}</td>
                                        <td class="text-center font-monospace fw-bold">{{ $item->tahun }} ({{ $item->semester }})</td>
                                        <td class="text-center font-monospace fw-bold">{{ $item->sks_bkd }}</td>
                                        <td class="text-center">
                                            <div class="btn-group rounded-0">
                                                @if($item->file_bukti)
                                                    <a href="{{ asset('storage/' . $item->file_bukti) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2">Berkas</a>
                                                @endif
                                                <form action="{{ route('dosen.tridharma.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data ini?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center py-5 text-muted uppercase fw-bold">Belum ada data pengabdian masyarakat.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-pendidikan">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
                    <span class="uppercase fw-bold small">Pendidikan & Pengajaran</span>
                    <span class="badge bg-light text-dark rounded-0 font-monospace">{{ $ringkasanBkd['sks_pendidikan'] }} SKS</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 small">
                            <thead class="bg-light text-dark uppercase text-center fw-bold">
                                <tr>
                                    <th style="width: 6%;">NO</th>
                                    <th style="width: 14%;">KODE MK</th>
                                    <th class="text-start">MATA KULIAH</th>
                                    <th style="width: 10%;">SMT</th>
                                    <th style="width: 10%;">SKS</th>
                                    <th style="width: 15%;">RPS</th>
                                    <th style="width: 18%;">DOKUMEN KELAS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mataKuliahs as $index => $mk)
                                    <tr>
                                        <td class="text-center font-monospace">{{ $index + 1 }}</td>
                                        <td class="text-center font-monospace fw-bold">{{ $mk->kode_mk }}</td>
                                        <td class="ps-3 fw-bold uppercase text-dark">{{ $mk->nama_mk }}</td>
                                        <td class="text-center font-monospace">{{ $mk->semester }}</td>
                                        <td class="text-center font-monospace fw-bold">{{ $mk->sks }}</td>
                                        <td class="text-center">
                                            @if($mk->file_rps)
                                                <a href="{{ asset('storage/' . $mk->file_rps) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2 font-monospace fw-bold" style="font-size: 10px;">Unduh RPS</a>
                                            @else
                                                <span class="text-muted font-monospace">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('dosen.cetak_absensi', $mk->id) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2 font-monospace fw-bold" style="font-size: 10px;">Presensi</a>
                                            <a href="{{ route('nilai.show', $mk->id) }}" class="btn btn-sm btn-dark rounded-0 py-1 px-2 font-monospace fw-bold" style="font-size: 10px;">Nilai</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center py-4 text-muted uppercase fw-bold">Belum ada mata kuliah.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-penunjang">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
                    <span class="uppercase fw-bold small">Penunjang & Surat Keputusan</span>
                    <span class="badge bg-light text-dark rounded-0 font-monospace">{{ $ringkasanBkd['sks_penunjang'] }} SKS</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 small">
                            <thead class="bg-light text-dark uppercase text-center fw-bold">
                                <tr>
                                    <th style="width: 15%;">JENIS</th>
                                    <th class="text-start">KEGIATAN / PERIHAL SK</th>
                                    <th style="width: 20%;">PERAN</th>
                                    <th style="width: 18%;">NOMOR SURAT</th>
                                    <th style="width: 12%;">BERKAS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($suratKeputusans as $sk)
                                    <tr>
                                        <td class="text-center font-monospace">SK KAMPUS</td>
                                        <td class="ps-3 fw-bold uppercase text-dark">{{ $sk->judul }}</td>
                                        <td class="text-center font-monospace uppercase">{{ $sk->pivot->jabatan_dalam_surat ?? 'ANGGOTA' }}</td>
                                        <td class="text-center font-monospace">{{ $sk->nomor_surat }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('surat-keputusan.download', $sk->id) }}" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2 font-monospace fw-bold" style="font-size: 10px;">Unduh</a>
                                        </td>
                                    </tr>
                                @endforeach
                                @foreach($penunjangs as $item)
                                    <tr>
                                        <td class="text-center font-monospace">PENUNJANG</td>
                                        <td class="ps-3 fw-bold uppercase text-dark">{{ $item->judul }} ({{ $item->tahun }})</td>
                                        <td class="text-center font-monospace uppercase">{{ $item->peran }}</td>
                                        <td class="text-center font-monospace">{{ $item->nomor_identitas ?? '-' }}</td>
                                        <td class="text-center">
                                            <div class="btn-group rounded-0">
                                                @if($item->file_bukti)
                                                    <a href="{{ asset('storage/' . $item->file_bukti) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2">Berkas</a>
                                                @endif
                                                <form action="{{ route('dosen.tridharma.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data ini?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                @if($suratKeputusans->isEmpty() && $penunjangs->isEmpty())
                                    <tr><td colspan="5" class="text-center py-5 text-muted uppercase fw-bold">Belum ada data penunjang.</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH KEGIATAN TRIDHARMA --}}
<div class="modal fade" id="tambahTridharmaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-0 border-0 border-top border-dark border-4">
            <form action="{{ route('dosen.tridharma.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-dark text-white rounded-0 py-3">
                    <h6 class="modal-title fw-bold uppercase small">Tambah Kegiatan Tridharma</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold uppercase text-dark">Bidang <span class="text-danger">*</span></label>
                            <select name="unsur" id="unsurSelect" class="form-select rounded-0 uppercase small fw-bold" required>
                                <option value="penelitian">Penelitian & Publikasi</option>
                                <option value="pengabdian">Pengabdian Masyarakat</option>
                                <option value="penunjang">Penunjang Akademik</option>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label small fw-bold uppercase text-dark">Kategori Kegiatan <span class="text-danger">*</span></label>
                            <select name="kategori" id="kategoriSelect" class="form-select rounded-0 uppercase small fw-bold" required>
                                <option value="Jurnal Nasional Terakreditasi" data-sks="3" data-kum="25">Jurnal Nasional Terakreditasi</option>
                                <option value="Jurnal Internasional" data-sks="4" data-kum="40">Jurnal Internasional</option>
                                <option value="Jurnal Nasional / Prosiding" data-sks="2" data-kum="10">Jurnal Nasional / Prosiding</option>
                                <option value="Buku Ajar / Monograf Ber-ISBN" data-sks="3" data-kum="20">Buku Ajar / Monograf Ber-ISBN</option>
                                <option value="Pelayanan Gerejawi / Pembinaan Jemaat" data-sks="1" data-kum="3">Pelayanan Gerejawi / Pembinaan Jemaat</option>
                                <option value="Pengabdian Masyarakat Terpadu" data-sks="2" data-kum="5">Pengabdian Masyarakat Terpadu</option>
                                <option value="Narasumber / Anggota Profesi" data-sks="1" data-kum="2">Narasumber / Anggota Profesi</option>
                                <option value="Pelatihan / Sertifikasi Dosen" data-sks="1" data-kum="3">Pelatihan / Sertifikasi Dosen</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold uppercase text-dark">Judul Kegiatan / Karya Ilmiah <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control rounded-0" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold uppercase text-dark">Peran <span class="text-danger">*</span></label>
                            <select name="peran" class="form-select rounded-0 uppercase small fw-bold" required>
                                <option value="Penulis Utama / Ketua">Penulis Utama / Ketua</option>
                                <option value="Penulis Anggota">Penulis Anggota</option>
                                <option value="Ketua Pelaksana">Ketua Pelaksana</option>
                                <option value="Narasumber">Narasumber</option>
                                <option value="Anggota">Anggota</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold uppercase text-dark">Tahun <span class="text-danger">*</span></label>
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
                            <label class="form-label small fw-bold uppercase text-dark">Nama Jurnal / Penerbit / Lokasi</label>
                            <input type="text" name="nama_jurnal_penerbit_lokasi" class="form-control rounded-0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold uppercase text-dark">ISSN / ISBN / Nomor Surat Tugas</label>
                            <input type="text" name="nomor_identitas" class="form-control rounded-0 font-monospace">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold uppercase text-dark">Beban SKS <span class="text-danger">*</span></label>
                            <input type="number" step="0.25" name="sks_bkd" id="sksInput" class="form-control rounded-0 font-monospace fw-bold" value="3.00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold uppercase text-dark">Angka Kredit <span class="text-danger">*</span></label>
                            <input type="number" step="0.5" name="angka_kredit" id="kumInput" class="form-control rounded-0 font-monospace fw-bold" value="25.00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold uppercase text-dark">Berkas Bukti (PDF, Maks 5MB)</label>
                            <input type="file" name="file_bukti" class="form-control rounded-0" accept=".pdf,.jpg,.jpeg,.png">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold uppercase text-dark">Tautan Publikasi / Dokumen (Opsional)</label>
                            <input type="url" name="tautan_eksternal" class="form-control rounded-0 font-monospace" placeholder="https://...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top rounded-0">
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small">Simpan</button>
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
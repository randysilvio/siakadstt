@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- 1. HEADER & STATUS UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Arsip Surat & Dokumen</h3>
            <span class="text-muted small uppercase">Pusat Manajemen Dokumen Administrasi Umum</span>
        </div>
        <div class="d-flex align-items-center">
            <span class="badge bg-success text-white rounded-0 uppercase fw-bold font-monospace px-3 py-2 me-2" style="font-size: 11px;">
                <i class="bi bi-check-circle-fill me-1"></i> Arsip Aktif
            </span>
            <a href="{{ route('administrasi.surat-keputusan.create') }}" class="btn btn-sm btn-dark rounded-0 px-3 uppercase fw-bold small shadow-sm">
                <i class="bi bi-envelope-plus-fill me-1"></i> Buat Surat Baru
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 rounded-0 shadow-sm alert-dismissible fade show uppercase small fw-bold font-monospace" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 2. KARTU INDIKATOR KINERJA UTAMA (KPI) --}}
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-body p-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2">Total Arsip Surat</h6>
                    <h2 class="fw-bold text-dark mb-1 font-monospace">{{ number_format($metrics['total']) }}</h2>
                    <span class="small text-muted uppercase font-monospace" style="font-size: 11px;">
                        <i class="bi bi-archive-fill me-1"></i> Seluruh Dokumen
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-body p-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2">Surat Selesai (Final)</h6>
                    <h2 class="fw-bold text-dark mb-1 font-monospace">{{ number_format($metrics['selesai']) }}</h2>
                    <span class="small text-muted uppercase font-monospace" style="font-size: 11px;">
                        <i class="bi bi-check-circle-fill me-1 text-success"></i> Telah Ditandatangani
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-body p-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2">Menunggu Otorisasi</h6>
                    <h2 class="fw-bold text-dark mb-1 font-monospace">{{ number_format($metrics['menunggu']) }}</h2>
                    <span class="small text-muted uppercase font-monospace" style="font-size: 11px;">
                        <i class="bi bi-pen-fill me-1 text-warning"></i> Proses TTD
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-body p-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2">Draf Tersimpan</h6>
                    <h2 class="fw-bold text-dark mb-1 font-monospace">{{ number_format($metrics['draf']) }}</h2>
                    <span class="small text-muted uppercase font-monospace" style="font-size: 11px;">
                        <i class="bi bi-file-earmark-break-fill me-1 text-secondary"></i> Belum Selesai
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. VISUALISASI PENCARIAN (Gaya Header Laporan) --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 h-100 bg-white">
        <div class="card-body p-4">
            <form action="{{ route('administrasi.surat-keputusan.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="uppercase fw-bold small text-muted mb-2">Pencarian Judul / Nomor</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white rounded-0 border-dark border-end-0"><i class="bi bi-search text-dark"></i></span>
                        <input type="text" name="search" class="form-control rounded-0 border-dark border-start-0 font-monospace" placeholder="Cari Surat..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="uppercase fw-bold small text-muted mb-2">Filter Kategori Dokumen</label>
                    <select name="jenis_surat" class="form-select rounded-0 border-dark uppercase small fw-bold text-dark">
                        <option value="">-- SEMUA KATEGORI SURAT --</option>
                        <option value="Surat Keputusan (SK)" {{ request('jenis_surat') == 'Surat Keputusan (SK)' ? 'selected' : '' }}>Surat Keputusan (SK)</option>
                        <option value="Surat Tugas" {{ request('jenis_surat') == 'Surat Tugas' ? 'selected' : '' }}>Surat Tugas</option>
                        <option value="Surat Keterangan" {{ request('jenis_surat') == 'Surat Keterangan' ? 'selected' : '' }}>Surat Keterangan</option>
                        <option value="Surat Undangan" {{ request('jenis_surat') == 'Surat Undangan' ? 'selected' : '' }}>Surat Undangan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-dark rounded-0 fw-bold uppercase w-100 small py-2"><i class="bi bi-funnel-fill me-1"></i> Terapkan</button>
                        @if(request()->has('search') || request()->has('jenis_surat'))
                            <a href="{{ route('administrasi.surat-keputusan.index') }}" class="btn btn-outline-dark rounded-0 fw-bold uppercase small py-2" title="Reset Filter"><i class="bi bi-x-circle"></i></a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- 4. LAPORAN DOKUMEN & TABEL DATA (Gaya Mutu) --}}
    <div class="card border-0 shadow-sm rounded-0 h-100 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
            <span class="uppercase fw-bold small">
                Rekapitulasi Dokumen Aktif
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 15%;">NO. SURAT</th>
                            <th class="text-start" style="width: 40%;">JENIS & JUDUL DOKUMEN</th>
                            <th style="width: 15%;">TANGGAL TERBIT</th>
                            <th style="width: 15%;">STATUS</th>
                            <th style="width: 15%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse($suratKeputusans as $surat)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-dark">
                                    {{ $surat->nomor_surat ?? '- BELUM ADA -' }}
                                </td>
                                <td class="text-start ps-3 py-3">
                                    <span class="badge bg-dark text-white rounded-0 mb-1 font-monospace" style="font-size: 10px;">{{ $surat->jenis_surat }}</span><br>
                                    <strong class="uppercase text-dark lh-sm d-block">{{ $surat->judul }}</strong>
                                    
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        @if($surat->dosens->count() > 0)
                                            <span class="text-muted font-monospace fw-bold" style="font-size: 10px;">
                                                <i class="bi bi-person-check-fill text-dark me-1"></i> {{ $surat->dosens->count() }} DOSEN TERHUBUNG
                                            </span>
                                        @endif
                                        @if(!empty($surat->panitia_lainnya) && count($surat->panitia_lainnya) > 0)
                                            <span class="text-muted font-monospace fw-bold ms-2" style="font-size: 10px;">
                                                <i class="bi bi-people-fill text-dark me-1"></i> {{ count($surat->panitia_lainnya) }} EKSTERNAL
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center font-monospace fw-bold text-dark">
                                    {{ $surat->tanggal_terbit ? \Carbon\Carbon::parse($surat->tanggal_terbit)->locale('id')->isoFormat('D MMMM Y') : '-' }}
                                </td>
                                <td class="text-center">
                                    @if($surat->status == 'Selesai')
                                        <span class="badge bg-dark text-white rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">SELESAI</span>
                                    @elseif($surat->status == 'Menunggu Tanda Tangan')
                                        <span class="badge bg-secondary text-white rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">MENUNGGU</span>
                                    @else
                                        <span class="badge bg-light border text-dark rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">DRAF</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('administrasi.surat-keputusan.show', $surat->id) }}" class="btn btn-sm btn-outline-dark rounded-0" title="Pratinjau / Cetak" target="_blank">
                                            <i class="bi bi-printer-fill"></i>
                                        </a>

                                        @if($surat->status != 'Selesai')
                                            <button type="button" class="btn btn-sm btn-dark rounded-0 fw-bold" data-bs-toggle="modal" data-bs-target="#modalUpload-{{ $surat->id }}" title="Upload PDF Final">
                                                <i class="bi bi-upload"></i>
                                            </button>
                                            <a href="{{ route('administrasi.surat-keputusan.edit', $surat->id) }}" class="btn btn-sm btn-outline-dark rounded-0" title="Edit Surat">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('surat-keputusan.download', $surat->id) }}" class="btn btn-sm btn-dark rounded-0 fw-bold" title="Unduh Dokumen Final">
                                                <i class="bi bi-file-earmark-pdf-fill"></i>
                                            </a>
                                        @endif

                                        <form action="{{ route('administrasi.surat-keputusan.duplicate', $surat->id) }}" method="POST" class="m-0" onsubmit="return confirm('Gunakan dokumen ini sebagai template draf surat baru?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-dark rounded-0" title="Duplikasi Template">
                                                <i class="bi bi-copy"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('administrasi.surat-keputusan.destroy', $surat->id) }}" method="POST" class="m-0" onsubmit="return confirm('Hapus dokumen ini secara permanen?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-0" title="Hapus Permanen">
                                                <i class="bi bi-trash3-fill"></i>
                                            </button>
                                        </form>
                                    </div>

                                    {{-- MODAL UPLOAD FINAL --}}
                                    @if($surat->status != 'Selesai')
                                    <div class="modal fade" id="modalUpload-{{ $surat->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-0 border-dark border-2">
                                                <form action="{{ route('administrasi.surat-keputusan.upload-final', $surat->id) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="modal-header bg-dark text-white rounded-0">
                                                        <h6 class="modal-title uppercase fw-bold small">Unggah Dokumen Final</h6>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4 text-start">
                                                        <p class="small text-muted mb-3 uppercase">Unggah hasil pindaian (scan) dokumen format PDF yang sudah distempel dan ditandatangani.</p>
                                                        <input type="file" name="file_pdf" class="form-control rounded-0 border-dark font-monospace" accept="application/pdf" required>
                                                    </div>
                                                    <div class="modal-footer bg-light rounded-0">
                                                        <button type="button" class="btn btn-sm btn-outline-dark rounded-0 uppercase fw-bold" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-sm btn-dark rounded-0 uppercase fw-bold"><i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan Final</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 uppercase fw-bold text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                    Tidak ada arsip dokumen yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($suratKeputusans->hasPages())
                <div class="px-4 py-3 border-top bg-light">
                    {{ $suratKeputusans->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
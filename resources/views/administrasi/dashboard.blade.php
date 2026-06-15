@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- 1. HEADER & STATUS UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Dashboard Administrasi Umum</h3>
            <span class="text-muted small uppercase">Monitoring Surat Keputusan & Konten Website</span>
        </div>
        <div class="d-flex align-items-center">
            <span class="badge bg-success text-white rounded-0 uppercase fw-bold font-monospace px-3 py-2 me-2" style="font-size: 11px;">
                <i class="bi bi-check-circle-fill me-1"></i> Sistem Aktif
            </span>
            
            <a href="{{ route('administrasi.surat-keputusan.create') }}" class="btn btn-sm btn-dark rounded-0 px-3 uppercase fw-bold small shadow-sm">
                <i class="bi bi-envelope-plus-fill me-1"></i> Buat Surat / SK Baru
            </a>
        </div>
    </div>

    {{-- 2. KARTU INDIKATOR KINERJA UTAMA (KPI) --}}
    <div class="row g-4 mb-5">
        {{-- Surat Selesai --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-body p-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2">Surat Selesai (Final)</h6>
                    <h2 class="fw-bold text-dark mb-1 font-monospace">{{ number_format($suratSelesai) }}</h2>
                    <span class="small text-muted uppercase font-monospace" style="font-size: 11px;">
                        <i class="bi bi-check2-all me-1"></i> Dokumen Terarsip
                    </span>
                </div>
            </div>
        </div>

        {{-- Menunggu TTD --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-body p-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2">Menunggu TTD</h6>
                    <h2 class="fw-bold text-dark mb-1 font-monospace">{{ number_format($suratMenunggu) }}</h2>
                    <span class="small text-muted uppercase font-monospace" style="font-size: 11px;">
                        <i class="bi bi-pen-fill me-1"></i> Proses Otorisasi
                    </span>
                </div>
            </div>
        </div>

        {{-- Draf Tersimpan --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-body p-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2">Draf Tersimpan</h6>
                    <h2 class="fw-bold text-dark mb-1 font-monospace">{{ number_format($suratDraf) }}</h2>
                    <span class="small text-muted uppercase font-monospace" style="font-size: 11px;">
                        <i class="bi bi-file-earmark-break-fill me-1"></i> Dalam Pengerjaan
                    </span>
                </div>
            </div>
        </div>

        {{-- Total Dokumen Publik --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-body p-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2">Dokumen Publik</h6>
                    <h2 class="fw-bold text-dark mb-1 font-monospace">{{ number_format($totalDokumen) }}</h2>
                    <span class="small text-muted uppercase font-monospace" style="font-size: 11px;">
                        <i class="bi bi-globe me-1"></i> Berkas Terpublikasi
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. VISUALISASI DATA AKTIVITAS --}}
    <div class="row g-4 mb-5">
        {{-- Tabel Persuratan (Format Mutu) --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-0 h-100">
                <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
                    <span class="uppercase fw-bold small">
                        Aktivitas Persuratan Terbaru
                    </span>
                    <a href="{{ route('administrasi.surat-keputusan.index') }}" class="badge bg-light text-dark rounded-0 uppercase font-monospace fw-bold text-decoration-none" style="font-size: 10px;">
                        Lihat Arsip Lengkap <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="bg-light text-dark small uppercase text-center fw-bold">
                                <tr>
                                    <th class="text-start" style="width: 50%;">NAMA DOKUMEN</th>
                                    <th style="width: 25%;">TANGGAL TERBIT</th>
                                    <th style="width: 25%;">STATUS</th>
                                </tr>
                            </thead>
                            <tbody class="small text-dark">
                                @forelse($suratTerbaru as $surat)
                                    <tr>
                                        <td class="text-start uppercase fw-bold text-dark ps-3">
                                            {{ Str::limit($surat->judul, 40) }}
                                            <span class="d-block text-muted font-monospace mt-1" style="font-size: 0.7rem;">{{ $surat->nomor_surat ?? 'TANPA NOMOR' }}</span>
                                        </td>
                                        <td class="text-center font-monospace fw-bold text-dark">
                                            {{ $surat->tanggal_terbit ? $surat->tanggal_terbit->format('d/m/Y') : '-' }}
                                        </td>
                                        <td class="text-center">
                                            @if($surat->status == 'Selesai')
                                                <span class="badge bg-dark text-white rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">SELESAI</span>
                                            @elseif($surat->status == 'Menunggu Tanda Tangan')
                                                <span class="badge bg-secondary text-white rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">MENUNGGU TTD</span>
                                            @else
                                                <span class="badge bg-light border text-dark rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">DRAF</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5 uppercase fw-bold text-muted">
                                            <i class="bi bi-envelope-x fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                            Belum ada data persuratan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel Dokumen Publik (Format Mutu) --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-0 h-100">
                <div class="card-header bg-white py-3 border-bottom border-top border-dark border-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark uppercase small">Dokumen Publik</h6>
                        <a href="{{ route('admin.dokumen-publik.index') }}" class="text-decoration-none small font-monospace fw-bold text-muted">KELOLA DOKUMEN <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small uppercase text-center fw-bold">
                            <tr>
                                <th class="text-start ps-4" style="width: 70%;">INFORMASI BERKAS</th>
                                <th style="width: 30%;">KATEGORI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dokumenTerbaru as $doc)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="fw-bold fs-6 font-monospace d-block text-dark lh-sm mb-1">{{ Str::limit($doc->judul_dokumen, 30) }}</div>
                                        <span class="text-muted font-monospace" style="font-size: 0.65rem;">{{ $doc->created_at->format('d M Y') }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-dark rounded-0 px-2 py-1 font-monospace" style="font-size: 0.65rem;">{{ strtoupper($doc->kategori ?? 'UMUM') }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center py-5 text-muted small uppercase fw-bold">
                                        <i class="bi bi-folder-x fs-2 d-block mb-2"></i>
                                        Belum Ada Dokumen Publik Tersimpan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
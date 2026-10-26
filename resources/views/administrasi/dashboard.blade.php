@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4">
    {{-- 1. HEADER & STATUS UTAMA --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 mt-2 gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Dashboard Administrasi Umum</h3>
            <span class="text-muted small uppercase">Manajemen Surat Keputusan (SK), Permohonan Surat Mahasiswa & Konten Website</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.surat-mandiri.index') }}" class="btn btn-sm btn-outline-dark px-3 py-2 uppercase fw-bold small">
                Permohonan Surat
            </a>
            <a href="{{ route('administrasi.surat-keputusan.create') }}" class="btn btn-sm btn-dark px-3 py-2 uppercase fw-bold small shadow-sm">
                <i class="bi bi-envelope-plus-fill me-1"></i> Buat SK Baru
            </a>
        </div>
    </div>

    {{-- 2. KARTU INDIKATOR KINERJA UTAMA (KPI) SERAGAM & RESPONSIF 2x2 DI HP --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card wm-card-exclusive hover-lift border-0 shadow-sm bg-dark text-white h-100">
                <div class="card-body p-3 p-md-4">
                    <h6 class="uppercase fw-bold small text-white-50 mb-1" style="font-size: 11px;">Surat Selesai (Final)</h6>
                    <h2 class="fw-bold text-white mb-1 font-monospace">{{ number_format($suratSelesai) }}</h2>
                    <span class="small text-white-50 uppercase font-monospace" style="font-size: 10px;">Dokumen Terarsip</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card hover-lift border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3 p-md-4">
                    <h6 class="uppercase fw-bold small text-muted mb-1" style="font-size: 11px;">Menunggu TTD</h6>
                    <h2 class="fw-bold text-dark mb-1 font-monospace">{{ number_format($suratMenunggu) }}</h2>
                    <span class="small text-muted uppercase font-monospace" style="font-size: 10px;">Proses Otorisasi</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card hover-lift border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3 p-md-4">
                    <h6 class="uppercase fw-bold small text-muted mb-1" style="font-size: 11px;">Draf Tersimpan</h6>
                    <h2 class="fw-bold text-dark mb-1 font-monospace">{{ number_format($suratDraf) }}</h2>
                    <span class="small text-muted uppercase font-monospace" style="font-size: 10px;">Dalam Pengerjaan</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card hover-lift border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3 p-md-4">
                    <h6 class="uppercase fw-bold small text-muted mb-1" style="font-size: 11px;">Dokumen Publik</h6>
                    <h2 class="fw-bold text-dark mb-1 font-monospace">{{ number_format($totalDokumen) }}</h2>
                    <span class="small text-muted uppercase font-monospace" style="font-size: 10px;">Berkas Terpublikasi</span>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. TABEL PERSURATAN & DOKUMEN PUBLIK --}}
    <div class="row g-4 mb-5">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                    <span class="uppercase fw-bold small">Aktivitas Persuratan & SK Terbaru</span>
                    <a href="{{ route('administrasi.surat-keputusan.index') }}" class="badge bg-light text-dark uppercase font-monospace fw-bold text-decoration-none" style="font-size: 10px;">
                        Lihat Arsip Lengkap &rarr;
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 small">
                            <thead class="bg-light text-dark uppercase text-center fw-bold">
                                <tr>
                                    <th class="text-start ps-3" style="width: 50%;">NAMA DOKUMEN</th>
                                    <th style="width: 25%;">TANGGAL TERBIT</th>
                                    <th style="width: 25%;">STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($suratTerbaru as $surat)
                                    <tr>
                                        <td class="text-start uppercase fw-bold text-dark ps-3">
                                            {{ Str::limit($surat->judul, 45) }}
                                            <span class="d-block text-muted font-monospace mt-1" style="font-size: 0.7rem;">{{ $surat->nomor_surat ?? 'TANPA NOMOR' }}</span>
                                        </td>
                                        <td class="text-center font-monospace fw-bold text-dark">
                                            {{ $surat->tanggal_terbit ? $surat->tanggal_terbit->format('d/m/Y') : '-' }}
                                        </td>
                                        <td class="text-center">
                                            @if($surat->status == 'Selesai')
                                                <span class="badge bg-dark text-white uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">SELESAI</span>
                                            @elseif($surat->status == 'Menunggu Tanda Tangan')
                                                <span class="badge bg-secondary text-white uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">MENUNGGU TTD</span>
                                            @else
                                                <span class="badge bg-light border text-dark uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">DRAF</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5 uppercase fw-bold text-muted">
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

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark uppercase small">Dokumen Publik Terbaru</h6>
                    <a href="{{ route('admin.dokumen-publik.index') }}" class="text-decoration-none small font-monospace fw-bold text-dark">KELOLA &rarr;</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="bg-light text-muted uppercase text-center fw-bold">
                                <tr>
                                    <th class="text-start ps-3" style="width: 70%;">INFORMASI BERKAS</th>
                                    <th style="width: 30%;">KATEGORI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dokumenTerbaru as $doc)
                                    <tr>
                                        <td class="ps-3 py-2.5">
                                            <div class="fw-bold font-monospace d-block text-dark">{{ Str::limit($doc->judul_dokumen ?? $doc->judul, 35) }}</div>
                                            <span class="text-muted font-monospace" style="font-size: 0.65rem;">{{ $doc->created_at->format('d/m/Y') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-dark px-2 py-1 font-monospace" style="font-size: 0.65rem;">{{ strtoupper($doc->kategori ?? 'UMUM') }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-5 text-muted small uppercase fw-bold">
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
</div>
@endsection
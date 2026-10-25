@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4">
    {{-- HEADER UTAMA --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 mt-2 gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Dashboard Keuangan</h3>
            <span class="text-muted font-monospace small uppercase">Petugas Aktif: {{ Auth::user()->name }}</span>
        </div>
        <div>
            <a href="{{ route('pembayaran.index') }}" class="btn btn-sm btn-dark px-4 py-2 uppercase fw-bold small shadow-sm">
                Kelola Semua Tagihan &rarr;
            </a>
        </div>
    </div>

    {{-- KOTAK KPI --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card wm-card-exclusive hover-lift border-0 shadow-sm bg-dark text-white h-100">
                <div class="card-body p-3 p-md-4">
                    <h6 class="uppercase fw-bold small text-white-50 mb-2" style="font-size: 11px;">Total Tagihan Sistem</h6>
                    <h2 class="fw-bold text-white mb-0 font-monospace">{{ $totalTagihan ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card hover-lift border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3 p-md-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2" style="font-size: 11px;">Tagihan Lunas</h6>
                    <h2 class="fw-bold text-dark mb-0 font-monospace">{{ $totalLunas ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card hover-lift border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-body p-3 p-md-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2" style="font-size: 11px;">Belum Lunas / Menunggak</h6>
                    <h2 class="fw-bold text-danger mb-0 font-monospace">{{ $totalBelumLunas ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- DAFTAR TRANSAKSI & PENGUMUMAN (3 PANEL LENGKAP) --}}
    <div class="row g-4 mb-5">
        <div class="col-lg-8">
            {{-- 5 PEMBAYARAN TERBARU (LUNAS) --}}
            <div class="card border-0 shadow-sm border-top border-dark border-4 mb-4 bg-white">
                <div class="card-header bg-dark text-white py-3 uppercase fw-bold small">
                    5 Pembayaran Terbaru (Lunas)
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 small">
                            <thead class="bg-light text-dark uppercase text-center fw-bold">
                                <tr>
                                    <th class="text-start ps-3" style="width: 50%;">MAHASISWA</th>
                                    <th style="width: 25%;">JUMLAH</th>
                                    <th style="width: 25%;">TANGGAL BAYAR</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pembayaranTerbaru as $p)
                                <tr>
                                    <td class="text-start ps-3">
                                        <div class="uppercase fw-bold text-dark">{{ $p->mahasiswa->nama_lengkap ?? 'N/A' }}</div>
                                        <span class="text-muted font-monospace" style="font-size: 11px;">NIM: {{ $p->mahasiswa->nim ?? '-' }}</span>
                                    </td>
                                    <td class="text-center font-monospace fw-bold text-dark">
                                        Rp {{ number_format($p->jumlah, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center font-monospace text-muted">
                                        {{ $p->tanggal_bayar ? $p->tanggal_bayar->isoFormat('D MMM YYYY') : '-' }}
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center py-4 uppercase fw-bold text-muted">Belum ada pembayaran lunas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- 5 TAGIHAN TERLAMA (BELUM LUNAS) --}}
            <div class="card border-0 shadow-sm border-top border-dark border-4 bg-white">
                <div class="card-header bg-white py-3 border-bottom uppercase fw-bold small text-dark">
                    5 Tagihan Terlama (Belum Lunas)
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 small">
                            <thead class="bg-light text-dark uppercase text-center fw-bold">
                                <tr>
                                    <th class="text-start ps-3" style="width: 50%;">MAHASISWA</th>
                                    <th style="width: 25%;">JUMLAH</th>
                                    <th style="width: 25%;">SEMESTER</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tagihanTerlama as $t)
                                <tr>
                                    <td class="text-start ps-3">
                                        <div class="uppercase fw-bold text-dark">{{ $t->mahasiswa->nama_lengkap ?? 'N/A' }}</div>
                                        <span class="text-muted font-monospace" style="font-size: 11px;">NIM: {{ $t->mahasiswa->nim ?? '-' }}</span>
                                    </td>
                                    <td class="text-center font-monospace fw-bold text-danger">
                                        Rp {{ number_format($t->jumlah, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center font-monospace text-muted">
                                        {{ $t->semester }}
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center py-4 uppercase fw-bold text-muted">Tidak ada tagihan yang belum lunas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- PANEL PENGUMUMAN TERBARU (DIKEMBALIKAN PENUH) --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm border-top border-dark border-4 h-100 bg-white">
                <div class="card-header bg-dark text-white py-3 uppercase fw-bold small">
                    Pengumuman Terbaru
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($pengumumans as $pengumuman)
                            <a href="{{ route('pengumuman.public.show', $pengumuman) }}" target="_blank" class="list-group-item list-group-item-action p-3 border-bottom">
                                <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 uppercase fw-bold small text-dark">{{ $pengumuman->judul }}</h6>
                                    <span class="font-monospace text-muted" style="font-size: 10px;">{{ $pengumuman->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="mb-0 small text-muted text-truncate">{{ Str::limit(strip_tags($pengumuman->konten), 80) }}</p>
                            </a>
                        @empty
                            <div class="p-4 text-center small uppercase fw-bold text-muted">Tidak ada pengumuman.</div>
                        @endforelse
                    </div>
                </div>
                <div class="card-footer bg-white border-top text-center py-3">
                    <a href="{{ route('berita.index') }}" target="_blank" class="btn btn-sm btn-outline-dark w-100 uppercase fw-bold small py-2">
                        Lihat Semua Pengumuman
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
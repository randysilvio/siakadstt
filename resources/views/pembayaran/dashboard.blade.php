@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Dasbor Keuangan</h3>
            <span class="text-muted font-monospace small uppercase">USER AKTIF: {{ Auth::user()->name }}</span>
        </div>
        <div>
            <a href="{{ route('pembayaran.index') }}" class="btn btn-sm btn-dark rounded-0 px-4 uppercase fw-bold small shadow-sm">
                Kelola Semua Tagihan <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>

    {{-- KOTAK KPI (Sudut Siku, Monospace, Flat Border) --}}
    <div class="row g-4 mb-4">
        {{-- Total Tagihan --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-body p-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2">Total Tagihan Sistem</h6>
                    <h2 class="fw-bold text-dark mb-0 font-monospace">{{ $totalTagihan ?? 0 }}</h2>
                </div>
            </div>
        </div>
        {{-- Tagihan Lunas --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-body p-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2">Tagihan Lunas</h6>
                    <h2 class="fw-bold text-success mb-0 font-monospace">{{ $totalLunas ?? 0 }}</h2>
                </div>
            </div>
        </div>
        {{-- Belum Lunas --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-body p-4">
                    <h6 class="uppercase fw-bold small text-muted mb-2">Belum Lunas / Menunggak</h6>
                    <h2 class="fw-bold text-danger mb-0 font-monospace">{{ $totalBelumLunas ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- DAFTAR & PENGUMUMAN --}}
    <div class="row g-4 mb-5">
        <div class="col-lg-8">
            
            {{-- PEMBAYARAN TERBARU --}}
            <div class="card border-0 shadow-sm rounded-0 mb-4">
                <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
                    5 Pembayaran Terbaru (Lunas)
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="bg-light text-dark small uppercase text-center fw-bold">
                                <tr>
                                    <th class="text-start" style="width: 50%;">MAHASISWA</th>
                                    <th style="width: 25%;">JUMLAH</th>
                                    <th style="width: 25%;">TGL BAYAR</th>
                                </tr>
                            </thead>
                            <tbody class="small text-dark">
                                @forelse($pembayaranTerbaru as $p)
                                <tr>
                                    <td class="text-start ps-3">
                                        <div class="uppercase fw-bold text-dark">{{ $p->mahasiswa->nama_lengkap ?? 'N/A' }}</div>
                                        <span class="text-muted font-monospace" style="font-size: 11px;">NIM: {{ $p->mahasiswa->nim ?? '-' }}</span>
                                    </td>
                                    <td class="text-center font-monospace fw-bold text-success">
                                        Rp {{ number_format($p->jumlah, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center font-monospace text-muted">
                                        {{ $p->tanggal_bayar->isoFormat('D MMM YYYY') }}
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

            {{-- TAGIHAN TERLAMA --}}
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                    5 Tagihan Terlama (Belum Lunas)
                </div>
                <div class="card-body p-0">
                     <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="bg-light text-dark small uppercase text-center fw-bold">
                                <tr>
                                    <th class="text-start" style="width: 50%;">MAHASISWA</th>
                                    <th style="width: 25%;">JUMLAH</th>
                                    <th style="width: 25%;">SEMESTER</th>
                                </tr>
                            </thead>
                            <tbody class="small text-dark">
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

        {{-- PENGUMUMAN --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                <div class="card-header bg-light py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                    Pengumuman Terbaru
                </div>
                <div class="card-body p-0">
                    <div class="list-group rounded-0">
                        @forelse($pengumumans as $pengumuman)
                            <a href="{{ route('pengumuman.public.show', $pengumuman) }}" class="list-group-item list-group-item-action rounded-0 p-3 bg-light border-bottom border-start-0 border-end-0">
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
                <div class="card-footer bg-light border-top text-center py-3 rounded-0">
                    <a href="{{ route('berita.index') }}" class="btn btn-sm btn-outline-dark rounded-0 w-100 uppercase fw-bold small py-2">Lihat Semua Pengumuman</a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
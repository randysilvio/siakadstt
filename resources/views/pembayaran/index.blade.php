@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Manajemen Pembayaran</h3>
            <span class="text-muted small uppercase">Kelola Data Tagihan & Verifikasi Status Pembayaran Sistem</span>
        </div>
        <div>
            <a href="{{ route('pembayaran.cetak', request()->query()) }}" target="_blank" class="btn btn-sm btn-dark rounded-0 px-3 uppercase fw-bold small me-2">
                <i class="bi bi-printer-fill me-1"></i> Cetak Laporan
            </a>
            <a href="{{ route('pembayaran.generate') }}" class="btn btn-sm btn-success rounded-0 px-3 uppercase fw-bold small text-white me-2 shadow-sm">
                <i class="bi bi-lightning-charge-fill me-1"></i> Generate Massal
            </a>
            <a href="{{ route('pembayaran.create') }}" class="btn btn-sm btn-primary rounded-0 px-3 uppercase fw-bold small shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Tagihan Manual
            </a>
        </div>
    </div>

    {{-- === SMART FILTER SECTION FLAT === --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-light">
        <div class="card-body p-4">
            <form action="{{ route('pembayaran.index') }}" method="GET" id="filterForm">
                <div class="row g-3">
                    {{-- 1. Cari Nama/NIM --}}
                    <div class="col-md-3">
                        <label class="form-label small fw-bold uppercase text-dark">Pencarian Teks</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white rounded-0"><i class="bi bi-search text-dark"></i></span>
                            <input type="text" name="q" class="form-control rounded-0 font-monospace uppercase" placeholder="NAMA / NIM / REG..." value="{{ request('q') }}">
                        </div>
                    </div>

                    {{-- 2. Filter Tipe User --}}
                    <div class="col-md-2">
                        <label class="form-label small fw-bold uppercase text-dark">Tipe Entitas</label>
                        <select name="tipe_user" class="form-select rounded-0 uppercase small fw-bold" onchange="document.getElementById('filterForm').submit()">
                            <option value="">-- SEMUA --</option>
                            <option value="mahasiswa" {{ request('tipe_user') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa Aktif</option>
                            <option value="camaba" {{ request('tipe_user') == 'camaba' ? 'selected' : '' }}>Camaba (PMB)</option>
                        </select>
                    </div>

                    {{-- 3. Filter Semester --}}
                    <div class="col-md-3">
                        <label class="form-label small fw-bold uppercase text-dark">Semester / Periode</label>
                        <input type="text" name="semester" class="form-control rounded-0 font-monospace uppercase" placeholder="GASAL 2024 / PMB..." value="{{ request('semester') }}">
                    </div>

                    {{-- 4. Filter Status --}}
                    <div class="col-md-2">
                        <label class="form-label small fw-bold uppercase text-dark">Status Bayar</label>
                        <select name="status" class="form-select rounded-0 uppercase small fw-bold" onchange="document.getElementById('filterForm').submit()">
                            <option value="">-- SEMUA --</option>
                            <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                            <option value="belum_lunas" {{ request('status') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                            <option value="menunggu_konfirmasi" {{ request('status') == 'menunggu_konfirmasi' ? 'selected' : '' }}>Menunggu Bukti</option>
                        </select>
                    </div>

                    {{-- 5. Tombol Filter --}}
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="d-flex w-100 gap-1">
                            <button type="submit" class="btn btn-primary rounded-0 flex-grow-1 uppercase fw-bold small py-2 shadow-sm">
                                Filter
                            </button>
                            @if(request()->hasAny(['q', 'semester', 'status', 'tipe_user']))
                                <a href="{{ route('pembayaran.index') }}" class="btn btn-outline-dark rounded-0 px-3 py-2" title="Reset Filter">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- === TABEL DATA ENTERPRISE === --}}
    <div class="card border-0 shadow-sm rounded-0 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Rekapitulasi Kewajiban Finansial
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th class="text-start ps-3" style="width: 30%;">IDENTITAS PEMBAYAR</th>
                            <th class="text-start" style="width: 25%;">KETERANGAN / SEMESTER</th>
                            <th style="width: 15%;">JUMLAH TAGIHAN</th>
                            <th style="width: 15%;">STATUS</th>
                            <th style="width: 15%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                    @forelse ($pembayarans as $pembayaran)
                        <tr>
                            <td class="text-start ps-3">
                                @if($pembayaran->mahasiswa)
                                    <div class="uppercase fw-bold text-dark">{{ $pembayaran->mahasiswa->nama_lengkap }}</div>
                                    <span class="text-muted font-monospace" style="font-size: 11px;">NIM: {{ $pembayaran->mahasiswa->nim }}</span>
                                @elseif($pembayaran->user)
                                    <div class="uppercase fw-bold text-dark">{{ $pembayaran->user->name }}</div>
                                    <span class="badge bg-info text-dark rounded-0 font-monospace" style="font-size: 9px;">CAMABA / PMB</span>
                                @else
                                    <span class="text-muted font-monospace uppercase">USER TERHAPUS</span>
                                @endif
                            </td>
                            <td class="text-start">
                                <div class="font-monospace fw-bold text-dark uppercase">{{ $pembayaran->semester }}</div>
                                <span class="text-muted uppercase" style="font-size: 11px;">
                                    {{ $pembayaran->keterangan ?? ucwords(str_replace('_', ' ', $pembayaran->jenis_pembayaran)) }}
                                </span>
                            </td>
                            <td class="text-center font-monospace fw-bold text-primary">
                                Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                @if($pembayaran->status == 'lunas')
                                    <span class="badge bg-success text-white rounded-0 uppercase fw-bold font-monospace" style="font-size: 10px;">LUNAS</span>
                                @elseif($pembayaran->status == 'menunggu_konfirmasi')
                                    <span class="badge bg-warning text-dark rounded-0 uppercase fw-bold font-monospace" style="font-size: 10px;">CEK BUKTI</span>
                                @else
                                    <span class="badge bg-danger text-white rounded-0 uppercase fw-bold font-monospace" style="font-size: 10px;">BELUM LUNAS</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group rounded-0" role="group">
                                    @if($pembayaran->bukti_bayar)
                                        <a href="{{ Storage::url($pembayaran->bukti_bayar) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-0 py-1 px-2" title="Lihat Bukti Transfer">
                                            <i class="bi bi-image"></i>
                                        </a>
                                    @endif

                                    <a href="{{ route('pembayaran.edit', $pembayaran->id) }}" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2" title="Edit Tagihan">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    @if($pembayaran->status != 'lunas')
                                    <form action="{{ route('pembayaran.lunas', $pembayaran->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Verifikasi pembayaran ini? Status akan berubah secara permanen menjadi LUNAS.');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-success rounded-0 py-1 px-2" title="Verifikasi Lunas">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                    @endif

                                    <form action="{{ route('pembayaran.destroy', $pembayaran->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2" onclick="return confirm('Yakin ingin menghapus tagihan ini secara permanen?')" title="Hapus Tagihan">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 uppercase fw-bold text-muted">
                                <i class="bi bi-receipt fs-2 d-block mb-2"></i>
                                Data pembayaran tidak ditemukan untuk kriteria filter ini.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        {{-- Paginasi Flat --}}
        @if($pembayarans->hasPages())
            <div class="card-footer bg-white border-top py-3 rounded-0">
                <div class="d-flex justify-content-center">
                    {{ $pembayarans->withQueryString()->links() }}
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
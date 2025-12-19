@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Manajemen Pembayaran</h1>
            <p class="text-muted small">Kelola data tagihan dan status pembayaran mahasiswa & camaba.</p>
        </div>
        
        {{-- Grup Tombol Aksi --}}
        <div>
            {{-- Tombol Cetak PDF (Otomatis membawa parameter filter yang sedang aktif) --}}
            <a href="{{ route('pembayaran.cetak', request()->query()) }}" target="_blank" class="btn btn-dark me-2">
                <i class="bi bi-printer-fill me-1"></i> Cetak Laporan
            </a>

            <a href="{{ route('pembayaran.generate') }}" class="btn btn-success me-2 text-white fw-bold">
                <i class="bi bi-lightning-charge-fill me-1"></i> Generate Massal
            </a>
            <a href="{{ route('pembayaran.create') }}" class="btn btn-primary fw-bold">
                <i class="bi bi-plus-circle me-1"></i> Tagihan Manual
            </a>
        </div>
    </div>

    {{-- === SMART FILTER SECTION (UPDATED) === --}}
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body bg-light rounded">
            <form action="{{ route('pembayaran.index') }}" method="GET">
                <div class="row g-2">
                    {{-- 1. Cari Nama/NIM --}}
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">Cari Nama/NIM/No.Reg</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Ketik kata kunci..." value="{{ request('q') }}">
                        </div>
                    </div>

                    {{-- 2. Filter Tipe User (BARU - Memisahkan Mahasiswa & Camaba) --}}
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted">Tipe User</label>
                        <select name="tipe_user" class="form-select">
                            <option value="">- Semua -</option>
                            <option value="mahasiswa" {{ request('tipe_user') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa Aktif</option>
                            <option value="camaba" {{ request('tipe_user') == 'camaba' ? 'selected' : '' }}>Camaba (PMB)</option>
                        </select>
                    </div>

                    {{-- 3. Filter Semester --}}
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">Semester / Keterangan</label>
                        <input type="text" name="semester" class="form-control" placeholder="Contoh: Gasal 2024 / PMB..." value="{{ request('semester') }}">
                    </div>

                    {{-- 4. Filter Status --}}
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted">Status Bayar</label>
                        <select name="status" class="form-select">
                            <option value="">- Semua -</option>
                            <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                            <option value="belum_lunas" {{ request('status') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                            <option value="menunggu_konfirmasi" {{ request('status') == 'menunggu_konfirmasi' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                        </select>
                    </div>

                    {{-- 5. Tombol Filter --}}
                    <div class="col-md-2 d-flex align-items-end gap-1">
                        <button type="submit" class="btn btn-primary w-100 fw-bold">
                            <i class="bi bi-funnel-fill me-1"></i> Filter
                        </button>
                        @if(request()->hasAny(['q', 'semester', 'status', 'tipe_user']))
                            <a href="{{ route('pembayaran.index') }}" class="btn btn-outline-secondary w-50" title="Reset">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- === TABEL DATA === --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Identitas Pembayar</th>
                            <th>Keterangan / Semester</th>
                            <th>Jumlah Tagihan</th>
                            <th>Status</th>
                            <th class="text-end pe-4" style="width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($pembayarans as $pembayaran)
                        <tr>
                            <td class="ps-4">
                                @if($pembayaran->mahasiswa)
                                    {{-- Data Mahasiswa --}}
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px;">
                                            <i class="bi bi-mortarboard-fill"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $pembayaran->mahasiswa->nama_lengkap }}</div>
                                            <small class="text-muted">NIM: {{ $pembayaran->mahasiswa->nim }}</small>
                                        </div>
                                    </div>
                                @elseif($pembayaran->user)
                                    {{-- Data User Umum (Camaba) --}}
                                    <div class="d-flex align-items-center">
                                        <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px;">
                                            <i class="bi bi-person-fill"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $pembayaran->user->name }}</div>
                                            <span class="badge bg-info text-dark border border-info border-opacity-25" style="font-size: 0.65rem;">CAMABA / PMB</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted fst-italic">User Terhapus</span>
                                @endif
                            </td>
                            <td>
                                <div class="small fw-bold text-dark">{{ $pembayaran->semester }}</div>
                                <div class="small text-muted text-truncate" style="max-width: 200px;">
                                    {{ $pembayaran->keterangan ?? ucwords(str_replace('_', ' ', $pembayaran->jenis_pembayaran)) }}
                                </div>
                            </td>
                            <td class="fw-bold text-primary">
                                Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}
                            </td>
                            <td>
                                @if($pembayaran->status == 'lunas')
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill border border-success border-opacity-25">
                                        <i class="bi bi-check-circle-fill me-1"></i> Lunas
                                    </span>
                                @elseif($pembayaran->status == 'menunggu_konfirmasi')
                                    <span class="badge bg-warning bg-opacity-10 text-dark px-3 py-2 rounded-pill border border-warning border-opacity-25">
                                        <i class="bi bi-hourglass-split me-1"></i> Cek Bukti
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill border border-danger border-opacity-25">
                                        <i class="bi bi-exclamation-circle-fill me-1"></i> Belum Lunas
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    {{-- Tombol Lihat Bukti --}}
                                    @if($pembayaran->bukti_bayar)
                                        <a href="{{ Storage::url($pembayaran->bukti_bayar) }}" target="_blank" class="btn btn-info text-white" title="Lihat Bukti Transfer">
                                            <i class="bi bi-image"></i>
                                        </a>
                                    @endif

                                    {{-- Tombol Edit --}}
                                    <a href="{{ route('pembayaran.edit', $pembayaran->id) }}" class="btn btn-outline-warning" title="Edit Tagihan">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    {{-- Tombol Lunas --}}
                                    @if($pembayaran->status != 'lunas')
                                    <form action="{{ route('pembayaran.lunas', $pembayaran->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Verifikasi pembayaran ini? Status akan berubah menjadi LUNAS.');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success" title="Verifikasi Lunas">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                    @endif

                                    {{-- Tombol Hapus --}}
                                    <form action="{{ route('pembayaran.destroy', $pembayaran->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Hapus tagihan ini?')" title="Hapus Tagihan">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <div class="mb-2"><i class="bi bi-inbox fs-1 text-secondary opacity-50"></i></div>
                                Data pembayaran tidak ditemukan untuk kriteria filter ini.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $pembayarans->withQueryString()->links() }}
    </div>
</div>
@endsection
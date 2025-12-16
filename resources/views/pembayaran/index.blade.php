@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Manajemen Pembayaran</h1>
            <p class="text-muted small">Kelola data tagihan dan status pembayaran mahasiswa.</p>
        </div>
        
        {{-- Grup Tombol Aksi --}}
        <div>
            {{-- Tombol Cetak PDF (Membawa parameter filter saat ini) --}}
            <a href="{{ route('pembayaran.cetak', request()->query()) }}" target="_blank" class="btn btn-dark me-2">
                <i class="bi bi-printer-fill me-1"></i> Cetak PDF
            </a>

            <a href="{{ route('pembayaran.generate') }}" class="btn btn-success me-2 text-white fw-bold">
                <i class="bi bi-lightning-charge-fill me-1"></i> Generate Massal
            </a>
            <a href="{{ route('pembayaran.create') }}" class="btn btn-primary fw-bold">
                <i class="bi bi-plus-circle me-1"></i> Tagihan Manual
            </a>
        </div>
    </div>

    {{-- === SMART FILTER SECTION === --}}
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body bg-light rounded">
            <form action="{{ route('pembayaran.index') }}" method="GET">
                <div class="row g-3">
                    {{-- Pencarian Nama/NIM --}}
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted">Cari Mahasiswa</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="q" class="form-control" placeholder="Nama atau NIM..." value="{{ request('q') }}">
                        </div>
                    </div>

                    {{-- Filter Semester --}}
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">Semester</label>
                        <input type="text" name="semester" class="form-control" placeholder="Contoh: Gasal 2024..." value="{{ request('semester') }}">
                    </div>

                    {{-- Filter Status --}}
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">Status Pembayaran</label>
                        <select name="status" class="form-select">
                            <option value="">- Semua Status -</option>
                            <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                            <option value="belum_lunas" {{ request('status') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                        </select>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold">Filter</button>
                        @if(request()->hasAny(['q', 'semester', 'status']))
                            <a href="{{ route('pembayaran.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                                <i class="bi bi-x-lg"></i>
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
                            <th class="ps-4">Mahasiswa</th>
                            <th>Semester</th>
                            <th>Jumlah Tagihan</th>
                            <th>Status</th>
                            <th class="text-end pe-4" style="width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($pembayarans as $pembayaran)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold">{{ $pembayaran->mahasiswa->nama_lengkap ?? 'Mahasiswa Dihapus' }}</div>
                                <div class="small text-muted">{{ $pembayaran->mahasiswa->nim ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $pembayaran->semester }}</span>
                                <div class="small text-muted mt-1 fst-italic">{{ Str::limit($pembayaran->keterangan, 25) }}</div>
                            </td>
                            <td class="fw-bold text-primary">
                                Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}
                            </td>
                            <td>
                                @if($pembayaran->status == 'lunas')
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                        <i class="bi bi-check-circle-fill me-1"></i> Lunas
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">
                                        <i class="bi bi-exclamation-circle-fill me-1"></i> Belum Lunas
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    {{-- Tombol Edit / Bayar Sebagian --}}
                                    <a href="{{ route('pembayaran.edit', $pembayaran->id) }}" class="btn btn-outline-warning" title="Edit Tagihan / Bayar Sebagian">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    {{-- Tombol Lunas --}}
                                    @if($pembayaran->status != 'lunas')
                                    <form action="{{ route('pembayaran.lunas', $pembayaran->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tandai tagihan ini LUNAS sepenuhnya?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline-success" title="Tandai Lunas Penuh">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                    @endif

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
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Data pembayaran tidak ditemukan untuk filter ini.
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
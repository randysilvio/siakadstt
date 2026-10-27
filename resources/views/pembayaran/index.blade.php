@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 mt-3 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Manajemen Keuangan & Tagihan</h3>
            <span class="text-muted small uppercase">Verifikasi Pembayaran SPP, Formulir PMB, & Otorisasi Dispensasi Cicilan KRS</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('pembayaran.create') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 py-2 uppercase fw-bold small">
                <i class="bi bi-plus-lg me-1"></i> Tagihan Manual
            </a>
            <a href="{{ route('pembayaran.generate') }}" class="btn btn-sm btn-primary rounded-0 px-3 py-2 uppercase fw-bold small shadow-sm">
                <i class="bi bi-lightning-charge-fill me-1"></i> Generate Tagihan Massal
            </a>
            <a href="{{ route('pembayaran.cetak', request()->query()) }}" target="_blank" class="btn btn-sm btn-dark rounded-0 px-3 py-2 uppercase fw-bold small shadow-sm">
                <i class="bi bi-printer-fill me-1"></i> Cetak Laporan
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- FILTER BAR FLAT --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-light">
        <div class="card-body p-4">
            <form action="{{ route('pembayaran.index') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold uppercase text-dark mb-1">Pencarian Nama / NIM</label>
                        <input type="text" name="q" class="form-control rounded-0 font-monospace uppercase" placeholder="KETIK NAMA / NIM..." value="{{ request('q') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold uppercase text-dark mb-1">Tipe Pembayar</label>
                        <select name="tipe_user" class="form-select rounded-0 uppercase small fw-bold">
                            <option value="">-- SEMUA --</option>
                            <option value="mahasiswa" {{ request('tipe_user') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                            <option value="camaba" {{ request('tipe_user') == 'camaba' ? 'selected' : '' }}>Camaba (PMB)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold uppercase text-dark mb-1">Status Pembayaran</label>
                        <select name="status" class="form-select rounded-0 uppercase small fw-bold">
                            <option value="">-- SEMUA STATUS --</option>
                            <option value="menunggu_konfirmasi" {{ request('status') == 'menunggu_konfirmasi' ? 'selected' : '' }}>Menunggu Konfirmasi Bukti</option>
                            <option value="belum_lunas" {{ request('status') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                            <option value="dispensasi" {{ request('status') == 'dispensasi' ? 'selected' : '' }}>Dispensasi / Cicilan KRS</option>
                            <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold uppercase text-dark mb-1">Semester / Ket.</label>
                        <input type="text" name="semester" class="form-control rounded-0 font-monospace uppercase" placeholder="2025/2026..." value="{{ request('semester') }}">
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary rounded-0 flex-grow-1 uppercase fw-bold small py-2">Filter</button>
                        <a href="{{ route('pembayaran.index') }}" class="btn btn-outline-dark rounded-0 px-3 py-2" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABEL DATA TAGIHAN --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Tagihan & Transaksi Keuangan
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 small">
                    <thead class="bg-light text-dark uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 5%;">NO</th>
                            <th class="text-start" style="width: 22%;">IDENTITAS PEMBAYAR</th>
                            <th class="text-start" style="width: 20%;">RINCIAN TAGIHAN</th>
                            <th style="width: 13%;">NOMINAL</th>
                            <th style="width: 10%;">BUKTI BAYAR</th>
                            <th style="width: 13%;">STATUS</th>
                            <th style="width: 17%;">AKSI VERIFIKASI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pembayarans as $index => $p)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted">{{ $pembayarans->firstItem() + $index }}</td>
                                <td class="ps-3">
                                    @if($p->mahasiswa)
                                        <div class="fw-bold text-dark uppercase">{{ $p->mahasiswa->nama_lengkap }}</div>
                                        <span class="font-monospace text-primary fw-bold" style="font-size: 11px;">NIM: {{ $p->mahasiswa->nim }}</span>
                                    @elseif($p->user)
                                        <div class="fw-bold text-dark uppercase">{{ $p->user->name }}</div>
                                        <span class="badge bg-secondary rounded-0 font-monospace" style="font-size: 9px;">CAMABA / PMB</span>
                                    @else
                                        <span class="text-muted fst-italic">Akun Terhapus</span>
                                    @endif
                                </td>
                                <td class="ps-3">
                                    <span class="fw-bold text-dark font-monospace d-block">{{ $p->semester }}</span>
                                    <span class="text-muted uppercase" style="font-size: 11px;">{{ $p->keterangan ?? strtoupper(str_replace('_', ' ', $p->jenis_pembayaran)) }}</span>
                                </td>
                                <td class="text-end pe-3 font-monospace fw-bold text-dark">
                                    Rp {{ number_format($p->jumlah, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if($p->bukti_bayar)
                                        <a href="{{ asset('storage/' . $p->bukti_bayar) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-0 py-0 px-2 font-monospace fw-bold" style="font-size: 10px;">
                                            <i class="bi bi-image me-1"></i>Lihat Bukti
                                        </a>
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 10px;">BELUM ADA</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($p->status == 'lunas')
                                        <span class="badge bg-success text-white rounded-0 font-monospace uppercase px-2 py-1" style="font-size: 10px;">LUNAS</span>
                                        @if($p->tanggal_bayar)
                                            <div class="text-muted font-monospace mt-1" style="font-size: 9px;">{{ \Carbon\Carbon::parse($p->tanggal_bayar)->format('d/m/Y') }}</div>
                                        @endif
                                    @elseif($p->status == 'dispensasi')
                                        <span class="badge bg-info text-dark border border-dark rounded-0 font-monospace uppercase px-2 py-1" style="font-size: 10px;">DISPENSASI KRS</span>
                                    @elseif($p->status == 'menunggu_konfirmasi')
                                        <span class="badge bg-warning text-dark rounded-0 font-monospace uppercase px-2 py-1" style="font-size: 10px;">CEK BUKTI</span>
                                    @else
                                        <span class="badge bg-danger text-white rounded-0 font-monospace uppercase px-2 py-1" style="font-size: 10px;">BELUM LUNAS</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1 flex-wrap">
                                        @if($p->status != 'lunas')
                                            <form action="{{ route('pembayaran.lunas', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Verifikasi tagihan ini sebagai LUNAS?');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success rounded-0 py-1 px-2 uppercase fw-bold" style="font-size: 10px;" title="Tandai Lunas">
                                                    <i class="bi bi-check-lg"></i> Lunas
                                                </button>
                                            </form>
                                        @endif

                                        @if($p->status == 'menunggu_konfirmasi')
                                            <form action="{{ route('pembayaran.tolakBukti', $p->id) }}" method="POST" class="d-inline" onsubmit="var a = prompt('Alasan penolakan bukti bayar (akan dikirim ke pembayar):'); if (a === null) return false; this.alasan.value = a; return true;">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="alasan" value="">
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2 uppercase fw-bold" style="font-size: 10px;" title="Tolak Bukti Bayar">
                                                    <i class="bi bi-x-lg"></i> Tolak
                                                </button>
                                            </form>
                                        @endif

                                        @if(in_array($p->status, ['belum_lunas', 'belum_bayar', 'menunggu_konfirmasi']) && $p->mahasiswa_id)
                                            <form action="{{ route('pembayaran.dispensasi', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Berikan Dispensasi Cicilan? Mahasiswa akan diizinkan mengisi KRS semester ini.');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-info text-dark border border-dark rounded-0 py-1 px-2 uppercase fw-bold" style="font-size: 10px;" title="Beri Dispensasi Cicilan (Buka Kunci KRS)">
                                                    <i class="bi bi-unlock-fill"></i> Dispensasi
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('pembayaran.edit', $p->id) }}" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2" title="Edit Tagihan">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <form action="{{ route('pembayaran.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data tagihan ini secara permanen?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted uppercase fw-bold">Data tagihan tidak ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($pembayarans->hasPages())
            <div class="card-footer bg-white border-top py-3 rounded-0">
                <div class="d-flex justify-content-center">{{ $pembayarans->links() }}</div>
            </div>
        @endif
    </div>
</div>
@endsection
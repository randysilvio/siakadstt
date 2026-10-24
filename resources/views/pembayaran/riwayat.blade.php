@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Riwayat Pembayaran & Tagihan Akademik</h3>
            <span class="text-muted small uppercase">Daftar Kewajiban Keuangan Semester & Unggah Bukti Pelunasan Mandiri</span>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali ke Dashboard
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

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Tagihan Keuangan Mahasiswa
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 small">
                    <thead class="bg-light text-dark uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 6%;">NO</th>
                            <th style="width: 18%;">PERIODE SEMESTER</th>
                            <th class="text-start">KETERANGAN TAGIHAN</th>
                            <th style="width: 15%;">JUMLAH</th>
                            <th style="width: 15%;">STATUS</th>
                            <th style="width: 22%;">BUKTI PEMBAYARAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pembayarans as $index => $p)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted">{{ $index + 1 }}</td>
                                <td class="text-center font-monospace fw-bold text-dark">{{ $p->semester }}</td>
                                <td class="ps-3 uppercase fw-bold text-dark">{{ $p->keterangan ?? strtoupper(str_replace('_', ' ', $p->jenis_pembayaran)) }}</td>
                                <td class="text-end pe-3 font-monospace fw-bold text-primary">Rp {{ number_format($p->jumlah, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    @if($p->status == 'lunas')
                                        <span class="badge bg-success text-white rounded-0 font-monospace uppercase px-2 py-1">LUNAS</span>
                                    @elseif($p->status == 'dispensasi')
                                        <span class="badge bg-info text-dark border border-dark rounded-0 font-monospace uppercase px-2 py-1">DISPENSASI KRS</span>
                                    @elseif($p->status == 'menunggu_konfirmasi')
                                        <span class="badge bg-warning text-dark rounded-0 font-monospace uppercase px-2 py-1">VERIFIKASI BUKTI</span>
                                    @else
                                        <span class="badge bg-danger text-white rounded-0 font-monospace uppercase px-2 py-1">BELUM LUNAS</span>
                                    @endif
                                </td>
                                <td class="p-2">
                                    @if($p->status == 'lunas')
                                        <div class="text-center font-monospace text-success fw-bold" style="font-size: 11px;">
                                            <i class="bi bi-check-circle-fill me-1"></i> TERVERIFIKASI ({{ $p->tanggal_bayar ? \Carbon\Carbon::parse($p->tanggal_bayar)->format('d/m/Y') : '-' }})
                                        </div>
                                    @else
                                        <form action="{{ route('pembayaran.uploadBukti', $p->id) }}" method="POST" enctype="multipart/form-data" class="d-flex gap-1">
                                            @csrf
                                            <input type="file" name="bukti_bayar" class="form-control form-control-sm rounded-0" accept="image/jpeg,image/png,image/jpg" required>
                                            <button type="submit" class="btn btn-sm btn-dark rounded-0 uppercase fw-bold px-2" style="font-size: 10px;">
                                                Unggah
                                            </button>
                                        </form>
                                        @if($p->bukti_bayar)
                                            <div class="mt-1 text-center">
                                                <a href="{{ asset('storage/' . $p->bukti_bayar) }}" target="_blank" class="small font-monospace text-primary text-decoration-none" style="font-size: 10px;">
                                                    <i class="bi bi-eye me-1"></i>Lihat Bukti Terkirim
                                                </a>
                                            </div>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted uppercase fw-bold">Belum ada data tagihan keuangan untuk akun Anda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
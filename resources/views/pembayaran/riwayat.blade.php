@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Riwayat Pembayaran</h1>
            <p class="text-muted small">Pantau status dan riwayat pembayaran kuliah Anda.</p>
        </div>
    </div>

    {{-- === TABEL DATA === --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Semester</th>
                            <th>Tanggal Bayar</th>
                            <th>Jumlah Tagihan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pembayarans as $pembayaran)
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-light text-dark border">{{ $pembayaran->semester }}</span>
                                </td>
                                <td>
                                    @if($pembayaran->tanggal_bayar)
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-calendar-check me-2 text-muted"></i>
                                            {{ \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->isoFormat('D MMMM YYYY') }}
                                        </div>
                                    @else
                                        <span class="text-muted small fst-italic">-</span>
                                    @endif
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                                    Anda belum memiliki riwayat pembayaran.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
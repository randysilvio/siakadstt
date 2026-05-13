@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Riwayat Pembayaran</h3>
            <span class="text-muted small uppercase">Pantau Mandiri Status & Riwayat Kewajiban Finansial Kuliah</span>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    {{-- === TABEL DATA ENTERPRISE === --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Pembayaran Tersimpan
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 25%;">SEMESTER / PERIODE</th>
                            <th style="width: 25%;">TANGGAL BAYAR</th>
                            <th style="width: 25%;">JUMLAH TAGIHAN</th>
                            <th style="width: 25%;">STATUS</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse ($pembayarans as $pembayaran)
                            <tr>
                                <td class="text-center font-monospace fw-bold uppercase text-dark">
                                    {{ $pembayaran->semester }}
                                </td>
                                <td class="text-center font-monospace text-muted">
                                    @if($pembayaran->tanggal_bayar)
                                        {{ \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->isoFormat('D MMMM YYYY') }}
                                    @else
                                        <span>-</span>
                                    @endif
                                </td>
                                <td class="text-center font-monospace fw-bold text-primary">
                                    Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if($pembayaran->status == 'lunas')
                                        <span class="badge bg-success text-white rounded-0 uppercase fw-bold font-monospace" style="font-size: 10px;">
                                            LUNAS
                                        </span>
                                    @else
                                        <span class="badge bg-danger text-white rounded-0 uppercase fw-bold font-monospace" style="font-size: 10px;">
                                            BELUM LUNAS
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 uppercase fw-bold text-muted">
                                    <i class="bi bi-receipt fs-2 d-block mb-2"></i>
                                    Anda belum memiliki riwayat pembayaran yang terdaftar di sistem.
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
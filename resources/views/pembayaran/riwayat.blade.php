@extends('layouts.app')

@section('content')
    <h1 class="mb-4">Riwayat Pembayaran</h1>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Semester</th>
                <th>Jumlah Tagihan</th>
                <th>Tanggal Bayar</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pembayarans as $pembayaran)
                <tr>
                    <td>{{ $pembayaran->semester }}</td>
                    <td>Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}</td>
                    <td>{{ $pembayaran->tanggal_bayar ? \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->isoFormat('D MMMM YYYY') : '-' }}</td>
                    <td>
                        @if($pembayaran->status == 'lunas')
                            <span class="badge bg-success">Lunas</span>
                        @else
                            <span class="badge bg-danger">Belum Lunas</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Anda belum memiliki riwayat pembayaran.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
@extends('layouts.app')

@section('content')
@php
    $namaBank = \App\Models\Pengaturan::get('nama_bank_kampus', 'Bank Papua / BRI');
    $noRek    = \App\Models\Pengaturan::get('no_rekening_kampus', '100-02-0012345-6');
    $atasNama = \App\Models\Pengaturan::get('atas_nama_rekening', 'STT GPI PAPUA');
@endphp
<div class="container-fluid px-3 px-md-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 mt-2 gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Riwayat Pembayaran & Tagihan</h3>
            <span class="text-muted small uppercase">Daftar Kewajiban Keuangan Semester & Unggah Bukti Transfer</span>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-dark px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    {{-- KARTU INFORMASI REKENING BANK RESMI DARI PENGATURAN SISTEM --}}
    <div class="card wm-card-exclusive bg-dark text-white border-0 shadow-sm mb-4">
        <div class="card-body p-3 p-md-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <span class="text-white-50 font-monospace uppercase d-block" style="font-size: 10px;">REKENING PEMBAYARAN RESMI INSTITUSI</span>
                <div class="fs-5 fw-bold uppercase mt-1">{{ $namaBank }} — <span class="font-monospace" id="rekTextMhs">{{ $noRek }}</span></div>
                <span class="small text-white-50 uppercase font-monospace">A.N. {{ $atasNama }}</span>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-light text-dark fw-bold uppercase small px-3 py-2" onclick="navigator.clipboard.writeText('{{ preg_replace('/[^0-9]/', '', $noRek) }}'); this.textContent='Nomor Tersalin!'; setTimeout(() => this.textContent='Salin No. Rekening', 2000);">
                    Salin No. Rekening
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border shadow-sm mb-4 p-3 uppercase small fw-bold">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border shadow-sm mb-4 p-3 uppercase small fw-bold">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm border-top border-dark border-4 mb-5">
        <div class="card-header bg-dark text-white py-3 uppercase fw-bold small">
            Daftar Tagihan Keuangan Mahasiswa
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 small">
                    <thead class="bg-light text-dark uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 6%;">NO</th>
                            <th style="width: 16%;">SEMESTER</th>
                            <th class="text-start">KETERANGAN TAGIHAN</th>
                            <th style="width: 16%;">NOMINAL</th>
                            <th style="width: 15%;">STATUS</th>
                            <th style="width: 24%;">BUKTI PEMBAYARAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pembayarans as $index => $p)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted">{{ $index + 1 }}</td>
                                <td class="text-center font-monospace fw-bold text-dark">{{ $p->semester }}</td>
                                <td class="ps-3 uppercase fw-bold text-dark">{{ $p->keterangan ?? strtoupper(str_replace('_', ' ', $p->jenis_pembayaran)) }}</td>
                                <td class="text-end pe-3 font-monospace fw-bold text-dark">Rp {{ number_format($p->jumlah, 0, ',', '.') }}</td>
                                <td class="text-center font-monospace fw-bold uppercase">
                                    @if($p->status == 'lunas')
                                        <span class="badge bg-dark text-white px-2 py-1">LUNAS</span>
                                    @elseif($p->status == 'dispensasi')
                                        <span class="badge bg-light text-dark border px-2 py-1">DISPENSASI KRS</span>
                                    @elseif($p->status == 'menunggu_konfirmasi')
                                        <span class="badge bg-secondary text-white px-2 py-1">VERIFIKASI</span>
                                    @else
                                        <span class="badge bg-danger text-white px-2 py-1">BELUM LUNAS</span>
                                    @endif
                                </td>
                                <td class="p-2">
                                    @if($p->status == 'lunas')
                                        <div class="text-center font-monospace text-dark fw-bold" style="font-size: 11px;">
                                            TERVERIFIKASI ({{ $p->tanggal_bayar ? \Carbon\Carbon::parse($p->tanggal_bayar)->format('d/m/Y') : '-' }})
                                        </div>
                                    @else
                                        <form action="{{ route('pembayaran.uploadBukti', $p->id) }}" method="POST" enctype="multipart/form-data" class="d-flex flex-column flex-sm-row gap-1">
                                            @csrf
                                            <input type="file" name="bukti_bayar" class="form-control form-control-sm" accept="image/jpeg,image/png,image/jpg" required>
                                            <button type="submit" class="btn btn-sm btn-dark uppercase fw-bold px-3" style="font-size: 10px;">
                                                Unggah
                                            </button>
                                        </form>
                                        @if($p->bukti_bayar)
                                            <div class="mt-1 text-center">
                                                <a href="{{ route('berkas.unduh', ['jenis' => 'bukti-bayar', 'id' => $p->id]) }}" target="_blank" class="small font-monospace text-dark text-decoration-underline" style="font-size: 10px;">
                                                    Lihat Bukti Terkirim
                                                </a>
                                            </div>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted uppercase fw-bold">Belum ada data tagihan keuangan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
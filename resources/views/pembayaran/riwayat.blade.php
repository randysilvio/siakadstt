@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Riwayat & Konfirmasi Pembayaran</h3>
            <span class="text-muted small uppercase">Pantau Mandiri Status & Unggah Bukti Transfer Kewajiban Finansial Kuliah</span>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    {{-- NOTIFIKASI SUKSES & ERROR --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger border rounded-0 shadow-sm mb-4 p-3 small uppercase fw-bold">
            @foreach ($errors->all() as $err)
                <div><i class="bi bi-x-circle-fill me-2"></i> {{ $err }}</div>
            @endforeach
        </div>
    @endif

    {{-- INFO REKENING KAMPUS --}}
    <div class="alert alert-light border border-dark border-opacity-25 rounded-0 p-3 mb-4 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="small uppercase font-monospace text-dark">
            <i class="bi bi-bank2 me-2 text-primary"></i>
            <strong>INFO PEMBAYARAN:</strong> Transfer tagihan ke rekening resmi <strong>Bank Papua: 123-456-7890</strong> a.n <strong>STT GPI Papua</strong>, lalu unggah bukti transfer pada kolom aksi di bawah.
        </div>
    </div>

    {{-- === TABEL DATA ENTERPRISE === --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Kewajiban & Riwayat Pembayaran Tersimpan
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 18%;">SEMESTER / PERIODE</th>
                            <th class="text-start" style="width: 24%;">JENIS / KETERANGAN</th>
                            <th style="width: 16%;">TANGGAL BAYAR</th>
                            <th style="width: 16%;">JUMLAH TAGIHAN</th>
                            <th style="width: 13%;">STATUS</th>
                            <th style="width: 13%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse ($pembayarans as $pembayaran)
                            <tr>
                                <td class="text-center font-monospace fw-bold uppercase text-dark">
                                    {{ $pembayaran->semester }}
                                </td>
                                <td class="text-start ps-3 uppercase">
                                    <div class="fw-bold text-dark">{{ strtoupper(str_replace('_', ' ', $pembayaran->jenis_pembayaran ?? 'TAGIHAN')) }}</div>
                                    @if($pembayaran->keterangan)
                                        <span class="text-muted font-monospace" style="font-size: 11px;">{{ $pembayaran->keterangan }}</span>
                                    @endif
                                </td>
                                <td class="text-center font-monospace text-muted">
                                    @if($pembayaran->tanggal_bayar)
                                        {{ \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->isoFormat('D MMM YYYY') }}
                                    @else
                                        <span>-</span>
                                    @endif
                                </td>
                                <td class="text-center font-monospace fw-bold text-primary">
                                    Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if($pembayaran->status == 'lunas')
                                        <span class="badge bg-success text-white rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">
                                            LUNAS
                                        </span>
                                    @elseif($pembayaran->status == 'menunggu_konfirmasi')
                                        <span class="badge bg-warning text-dark rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">
                                            MENUNGGU VERIFIKASI
                                        </span>
                                    @else
                                        <span class="badge bg-danger text-white rounded-0 uppercase fw-bold font-monospace px-2 py-1" style="font-size: 10px;">
                                            BELUM LUNAS
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($pembayaran->status == 'lunas')
                                        <span class="text-success font-monospace fw-bold small uppercase">
                                            <i class="bi bi-check-circle-fill me-1"></i> Selesai
                                        </span>
                                    @else
                                        <button type="button" class="btn btn-sm btn-dark rounded-0 px-3 py-1 uppercase fw-bold small" data-bs-toggle="modal" data-bs-target="#uploadModal{{ $pembayaran->id }}">
                                            <i class="bi bi-upload me-1"></i> {{ $pembayaran->bukti_bayar ? 'Ubah Bukti' : 'Upload Bukti' }}
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 uppercase fw-bold text-muted">
                                    <i class="bi bi-receipt fs-2 d-block mb-2 text-secondary opacity-50"></i>
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

{{-- MODAL UPLOAD BUKTI TRANSFER PER TAGIHAN --}}
@foreach ($pembayarans as $pembayaran)
    @if($pembayaran->status != 'lunas')
        <div class="modal fade" id="uploadModal{{ $pembayaran->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog rounded-0">
                <div class="modal-content rounded-0 border-dark border-2">
                    <div class="modal-header bg-dark text-white rounded-0 py-3">
                        <h6 class="modal-title uppercase fw-bold small text-white">
                            Unggah Bukti Pembayaran — {{ $pembayaran->semester }}
                        </h6>
                        <button type="button" class="btn-close btn-close-white rounded-0" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('pembayaran.uploadBukti', $pembayaran->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="p-3 bg-light border rounded-0 text-center mb-3">
                                <span class="small font-monospace text-muted uppercase d-block">NOMINAL TAGIHAN:</span>
                                <h4 class="fw-bold text-primary font-monospace mb-0">Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}</h4>
                                <small class="text-dark uppercase fw-bold">{{ strtoupper(str_replace('_', ' ', $pembayaran->jenis_pembayaran)) }}</small>
                            </div>

                            @if($pembayaran->bukti_bayar)
                                <div class="mb-3 text-center">
                                    <span class="small font-monospace text-muted uppercase d-block mb-1">BUKTI SAAT INI:</span>
                                    <a href="{{ Storage::url($pembayaran->bukti_bayar) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-0 uppercase fw-bold small">
                                        <i class="bi bi-image me-1"></i> Lihat Bukti Terunggah
                                    </a>
                                </div>
                            @endif

                            <div class="mb-2">
                                <label class="form-label small fw-bold uppercase text-dark">Pilih Foto / Struk Transfer <span class="text-danger">*</span></label>
                                <input type="file" name="bukti_bayar" class="form-control rounded-0" accept="image/jpeg,image/png,image/jpg" required>
                                <div class="form-text text-muted small uppercase mt-1">Format: JPG, PNG. Ukuran maksimal: 2MB.</div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light rounded-0">
                            <button type="button" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm">
                                <i class="bi bi-send-fill me-1"></i> Kirim Bukti
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach
@endsection
@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Proses Pengembalian Pustaka</h3>
            <span class="text-muted small uppercase">Penyelesaian Transaksi Sirkulasi & Pemulihan Stok Inventaris Fisik</span>
        </div>
        <div>
            <a href="{{ route('perpustakaan.peminjaman.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-md-8 mx-auto mb-5">
        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
            Formulir Eksekusi Pengembalian Buku
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('perpustakaan.peminjaman.processReturn') }}">
                @csrf
                
                {{-- Panel Informasi Kaku --}}
                <div class="p-3 bg-light border border-dark border-opacity-25 rounded-0 mb-4 d-flex align-items-center">
                    <div class="bg-dark text-white rounded-0 d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                        <i class="bi bi-arrow-return-left fs-5"></i>
                    </div>
                    <div class="small text-muted uppercase">
                        <strong class="text-dark d-block mb-0 font-monospace">OTOMATISASI INVENTARIS:</strong>
                        Pilih data transaksi aktif di bawah ini untuk menandai pengembalian fisik. Stok buku bersangkutan akan dipulihkan secara otomatis pada sistem.
                    </div>
                </div>

                {{-- Pilihan Transaksi --}}
                <div class="mb-4">
                    <label for="peminjaman_id" class="form-label small fw-bold uppercase text-dark">Cari Transaksi Peminjaman Aktif <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light rounded-0"><i class="bi bi-search text-dark"></i></span>
                        <select class="form-select select2 rounded-0 uppercase" id="peminjaman_id" name="peminjaman_id" required>
                            <option value="" disabled selected>-- KETIK NAMA PEMINJAM ATAU JUDUL BUKU... --</option>
                            @foreach($peminjamans as $peminjaman)
                                <option value="{{ $peminjaman->id }}">
                                    {{ str_pad($peminjaman->id, 4, '0', STR_PAD_LEFT) }} | {{ $peminjaman->user->name }} - {{ $peminjaman->koleksi->judul }} (TENGGAT: {{ \Carbon\Carbon::parse($peminjaman->jatuh_tempo)->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end pt-4 mt-4 border-top">
                    <a href="{{ route('perpustakaan.peminjaman.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                        Proses Pengembalian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
        });
    </script>
@endpush
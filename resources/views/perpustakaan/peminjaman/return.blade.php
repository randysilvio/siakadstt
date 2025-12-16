@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0 fw-bold">Proses Pengembalian Buku</h2>
                    <p class="text-muted mb-0">Selesaikan transaksi peminjaman.</p>
                </div>
                <a href="{{ route('perpustakaan.peminjaman.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card shadow-sm border-0 border-top border-5 border-success">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('perpustakaan.peminjaman.processReturn') }}">
                        @csrf
                        
                        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4">
                            <i class="bi bi-info-circle-fill fs-4 me-3 text-info"></i>
                            <div>Pilih transaksi di bawah ini untuk menandai buku telah dikembalikan. Stok buku akan bertambah otomatis.</div>
                        </div>

                        <div class="mb-4">
                            <label for="peminjaman_id" class="form-label fw-bold">Cari Transaksi Peminjaman</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                                <select class="form-select select2" id="peminjaman_id" name="peminjaman_id" required>
                                    <option value="" disabled selected>Ketik nama peminjam atau judul buku...</option>
                                    @foreach($peminjamans as $peminjaman)
                                        <option value="{{ $peminjaman->id }}">
                                            {{ $peminjaman->user->name }} - {{ $peminjaman->koleksi->judul }} (Tenggat: {{ \Carbon\Carbon::parse($peminjaman->jatuh_tempo)->format('d M Y') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end pt-3 border-top mt-2">
                            <button type="submit" class="btn btn-success px-4 shadow-sm">
                                <i class="bi bi-check-lg me-1"></i> Proses Pengembalian
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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
@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Catat Peminjaman Buku</h3>
            <span class="text-muted small uppercase">Input Data Transaksi Peminjaman Pustaka & Alokasi Peminjam</span>
        </div>
        <div>
            <a href="{{ route('perpustakaan.peminjaman.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-md-8 mx-auto mb-5">
        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
            Formulir Parameter Transaksi Sirkulasi
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('perpustakaan.peminjaman.store') }}">
                @csrf
                
                {{-- Data Peminjam --}}
                <div class="mb-4">
                    <label for="user_id" class="form-label small fw-bold uppercase text-dark">Data Peminjam <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light rounded-0"><i class="bi bi-person-badge text-dark"></i></span>
                        <select class="form-select select2 rounded-0 uppercase" id="user_id" name="user_id" required>
                            <option value="" disabled selected>-- CARI NAMA PENGGUNA ATAU EMAIL... --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Data Koleksi Buku --}}
                <div class="mb-4">
                    <label for="koleksi_id" class="form-label small fw-bold uppercase text-dark">Buku yang Dipinjam <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light rounded-0"><i class="bi bi-book text-dark"></i></span>
                        <select class="form-select select2 rounded-0 uppercase" id="koleksi_id" name="koleksi_id" required>
                            <option value="" disabled selected>-- CARI JUDUL BUKU ATAU KODE... --</option>
                            @foreach($koleksi as $buku)
                                <option value="{{ $buku->id }}" {{ $buku->jumlah_tersedia <= 0 ? 'disabled' : '' }}>
                                    {{ $buku->judul }} 
                                    @if($buku->jumlah_tersedia <= 0) 
                                        (STOK HABIS) 
                                    @else 
                                        (SISA STOK: {{ $buku->jumlah_tersedia }}) 
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Tanggal Jatuh Tempo --}}
                <div class="mb-4">
                    <label for="jatuh_tempo" class="form-label small fw-bold uppercase text-dark">Tanggal Jatuh Tempo <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light rounded-0"><i class="bi bi-calendar-event text-dark"></i></span>
                        <input type="date" class="form-control rounded-0 font-monospace text-primary fw-bold text-center" id="jatuh_tempo" name="jatuh_tempo" value="{{ now()->addDays(7)->toDateString() }}" required>
                    </div>
                    <div class="form-text text-muted small uppercase mt-1">
                        * Secara default sistem mengalokasikan durasi peminjaman selama 7 hari kalender.
                    </div>
                </div>

                <div class="d-flex justify-content-end pt-4 mt-4 border-top">
                    <a href="{{ route('perpustakaan.peminjaman.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                        Simpan Transaksi
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
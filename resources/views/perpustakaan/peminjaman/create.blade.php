@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0 fw-bold">Catat Peminjaman Baru</h2>
                    <p class="text-muted mb-0">Input data transaksi peminjaman buku.</p>
                </div>
                <a href="{{ route('perpustakaan.peminjaman.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('perpustakaan.peminjaman.store') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="user_id" class="form-label fw-bold">Data Peminjam</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person-badge"></i></span>
                                <select class="form-select select2" id="user_id" name="user_id" required>
                                    <option value="" disabled selected>Cari nama mahasiswa atau dosen...</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="koleksi_id" class="form-label fw-bold">Buku yang Dipinjam</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-book"></i></span>
                                <select class="form-select select2" id="koleksi_id" name="koleksi_id" required>
                                    <option value="" disabled selected>Cari judul buku...</option>
                                    @foreach($koleksi as $buku)
                                        <option value="{{ $buku->id }}" {{ $buku->jumlah_tersedia <= 0 ? 'disabled' : '' }}>
                                            {{ $buku->judul }} 
                                            @if($buku->jumlah_tersedia <= 0) (Stok Habis) @else (Sisa: {{ $buku->jumlah_tersedia }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="jatuh_tempo" class="form-label fw-bold">Tanggal Jatuh Tempo</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-calendar-event"></i></span>
                                <input type="date" class="form-control" id="jatuh_tempo" name="jatuh_tempo" value="{{ now()->addDays(7)->toDateString() }}" required>
                            </div>
                            <div class="form-text text-muted">Secara default diatur 7 hari dari sekarang.</div>
                        </div>

                        <div class="d-flex justify-content-end pt-3 border-top mt-2">
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="bi bi-save me-1"></i> Simpan Transaksi
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
                width: '100%',
                placeholder: $(this).data('placeholder'),
            });
        });
    </script>
@endpush
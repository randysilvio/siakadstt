@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Form Peminjaman Buku Baru</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('perpustakaan.peminjaman.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Pilih Peminjam</label>
                            <select class="form-control" id="user_id" name="user_id" required>
                                <option value="" disabled selected>Cari berdasarkan nama atau NIM/NIDN...</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="koleksi_id" class="form-label">Pilih Buku</label>
                            <select class="form-control" id="koleksi_id" name="koleksi_id" required>
                                <option value="" disabled selected>Cari berdasarkan judul buku...</option>
                                @foreach($koleksi as $buku)
                                    <option value="{{ $buku->id }}">{{ $buku->judul }} (Sisa: {{ $buku->jumlah_tersedia }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="jatuh_tempo" class="form-label">Tanggal Jatuh Tempo</label>
                            <input type="date" class="form-control" id="jatuh_tempo" name="jatuh_tempo" value="{{ now()->addDays(7)->toDateString() }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
                        <a href="{{ route('perpustakaan.peminjaman.index') }}" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#user_id').select2({ theme: "bootstrap-5" });
        $('#koleksi_id').select2({ theme: "bootstrap-5" });
    });
</script>
@endpush
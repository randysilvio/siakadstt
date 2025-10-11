@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Form Pengembalian Buku</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('perpustakaan.peminjaman.processReturn') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="peminjaman_id" class="form-label">Pilih Transaksi Peminjaman</label>
                            <select class="form-control" id="peminjaman_id" name="peminjaman_id" required>
                                <option value="" disabled selected>Cari buku atau nama peminjam...</option>
                                @foreach($peminjamans as $peminjaman)
                                    <option value="{{ $peminjaman->id }}">
                                        {{ $peminjaman->koleksi->judul }} - (Dipinjam oleh: {{ $peminjaman->user->name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Proses Pengembalian</button>
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
        $('#peminjaman_id').select2({ theme: "bootstrap-5" });
    });
</script>
@endpush
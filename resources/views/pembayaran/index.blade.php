@extends('layouts.app')
@section('content')
    <h1 class="mb-4">Manajemen Pembayaran</h1>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <a href="{{ route('pembayaran.create') }}" class="btn btn-primary mb-3">Buat Tagihan Baru</a>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr><th>NIM</th><th>Nama Mahasiswa</th><th>Semester</th><th>Jumlah</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        @forelse ($pembayarans as $pembayaran)
            <tr>
                <td>{{ $pembayaran->mahasiswa->nim }}</td>
                <td>{{ $pembayaran->mahasiswa->nama_lengkap }}</td>
                <td>{{ $pembayaran->semester }}</td>
                <td>Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}</td>
                <td>
                    @if($pembayaran->status == 'lunas')
                        <span class="badge bg-success">Lunas</span>
                    @else
                        <span class="badge bg-danger">Belum Lunas</span>
                    @endif
                </td>
                <td>
                    @if($pembayaran->status == 'belum_lunas')
                    <form action="{{ route('pembayaran.lunas', $pembayaran->id) }}" method="POST" class="d-inline">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-success btn-sm">Tandai Lunas</button>
                    </form>
                    @endif
                    <form action="{{ route('pembayaran.destroy', $pembayaran->id) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin?')">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center">Belum ada data pembayaran.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="d-flex justify-content-center">{{ $pembayarans->links() }}</div>
@endsection
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manajemen Pembayaran</h1>
        <a href="{{ route('pembayaran.create') }}" class="btn btn-primary">Buat Tagihan Baru</a>
    </div>

    {{-- Tambahkan form filter jika diperlukan di masa depan --}}

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>NIM</th>
                    <th>Nama Mahasiswa</th>
                    <th>Semester</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th style="width: 200px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($pembayarans as $pembayaran)
                <tr>
                    <td>{{ $pembayaran->mahasiswa->nim }}</td>
                    <td>{{ $pembayaran->mahasiswa->nama_lengkap }}</td>
                    <td>{{ $pembayaran->semester }}</td>
                    <td>Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}</td>
                    <td>
                        <span class="badge {{ $pembayaran->status == 'lunas' ? 'bg-success' : 'bg-danger' }}">
                            {{ ucfirst($pembayaran->status) }}
                        </span>
                    </td>
                    <td>
                        @if($pembayaran->status == 'belum_lunas')
                        <form action="{{ route('pembayaran.lunas', $pembayaran->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success btn-sm">Tandai Lunas</button>
                        </form>
                        @endif

                        <form action="{{ route('pembayaran.destroy', $pembayaran->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            {{-- PENYEMPURNAAN: Pesan konfirmasi yang lebih spesifik --}}
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Anda yakin ingin menghapus tagihan untuk {{ $pembayaran->mahasiswa->nama_lengkap }}?')">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Belum ada data pembayaran.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $pembayarans->links() }}
    </div>
</div>
@endsection

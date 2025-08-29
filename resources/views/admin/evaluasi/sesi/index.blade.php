@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Manajemen Sesi Evaluasi</h1>
        {{-- PERBAIKAN: Menambahkan prefix 'admin.' pada nama rute --}}
        <a href="{{ route('admin.evaluasi-sesi.create') }}" class="btn btn-primary">Tambah Sesi Baru</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Nama Sesi</th>
                            <th>Tahun Akademik</th>
                            <th>Periode</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sesi as $item)
                            <tr>
                                <td>{{ $item->nama_sesi }}</td>
                                {{-- PERBAIKAN: Menggunakan relasi yang benar 'tahunAkademik' dan properti 'tahun' & 'semester' --}}
                                <td>{{ $item->tahunAkademik->tahun }} ({{ $item->tahunAkademik->semester }})</td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal_mulai)->isoFormat('D MMM Y') }} - {{ \Carbon\Carbon::parse($item->tanggal_selesai)->isoFormat('D MMM Y') }}</td>
                                <td>
                                    @if ($item->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    {{-- PERBAIKAN: Menambahkan prefix 'admin.' pada nama rute --}}
                                    <a href="{{ route('admin.evaluasi-sesi.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                    {{-- PERBAIKAN: Menambahkan prefix 'admin.' pada nama rute --}}
                                    <form action="{{ route('admin.evaluasi-sesi.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus sesi ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Belum ada sesi evaluasi yang dibuat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($sesi->hasPages())
        <div class="card-footer">
            {{ $sesi->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
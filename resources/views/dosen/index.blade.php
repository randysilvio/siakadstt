@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Manajemen Dosen</h1>
        <a href="{{ route('dosen.create') }}" class="btn btn-primary">Tambah Dosen Baru</a>
    </div>

    {{-- Tombol Export & Import --}}
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('dosen.export') }}" class="btn btn-success me-2">Export Dosen</a>
        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importDosenModal">Import Dosen</button>
    </div>

    {{-- Modal Import --}}
    <div class="modal fade" id="importDosenModal" tabindex="-1" aria-labelledby="importDosenModalLabel" aria-hidden="true">
        {{-- Konten Modal tidak berubah --}}
    </div>

    <div class="card">
        <div class="card-body">
            <!-- ======================================================= -->
            <!-- ===== PERBAIKAN: Menambahkan Formulir Pencarian ===== -->
            <!-- ======================================================= -->
            <form action="{{ route('dosen.index') }}" method="GET" class="mb-4">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan Nama, NIDN, atau Email..." value="{{ request('search') }}">
                    <button class="btn btn-primary" type="submit">Cari</button>
                </div>
            </form>
            <!-- ======================================================= -->

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>NIDN</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dosens as $dosen)
                            <tr>
                                <td>{{ $dosen->nidn }}</td>
                                <td>{{ $dosen->nama_lengkap }}</td>
                                <td>{{ $dosen->user->email ?? '-' }}</td>
                                <td>{{ $dosen->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('dosen.edit', $dosen->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('dosen.destroy', $dosen->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data dosen ini? Ini juga akan menghapus akun user terkait.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">
                                    @if(request('search'))
                                        Data tidak ditemukan untuk pencarian "{{ request('search') }}".
                                    @else
                                        Belum ada data dosen.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($dosens->hasPages())
        <div class="card-footer">
            {{-- Tautan paginasi akan otomatis menyertakan query pencarian --}}
            {{ $dosens->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

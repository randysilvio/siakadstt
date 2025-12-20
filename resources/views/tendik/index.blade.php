@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Daftar Pegawai & Tenaga Kependidikan</h2>
        <a href="{{ route('admin.tendik.create') }}" class="btn btn-primary"><i class="bi bi-plus"></i> Tambah Baru</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('admin.tendik.index') }}" method="GET" class="mb-4">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Cari Nama / NIP / Unit Kerja..." value="{{ request('search') }}">
                    <button class="btn btn-outline-primary" type="submit">Cari</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Lengkap</th>
                            <th>Unit Kerja</th>
                            <th>Jabatan</th>
                            <th>Jenis Tendik</th>
                            <th>Akses (Role)</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tendiks as $tendik)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $tendik->nama_lengkap }}</div>
                                    <small class="text-muted">{{ $tendik->nip_yayasan ?? '-' }}</small>
                                </td>
                                <td>{{ $tendik->unit_kerja }}</td>
                                <td>{{ $tendik->jabatan }}</td>
                                <td>{{ $tendik->jenis_tendik }}</td>
                                <td>
                                    @foreach($tendik->user->roles as $role)
                                        <span class="badge bg-secondary">{{ $role->display_name }}</span>
                                    @endforeach
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.tendik.edit', $tendik->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('admin.tendik.destroy', $tendik->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data ini? User login juga akan terhapus.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">Belum ada data tenaga kependidikan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $tendiks->links() }}
        </div>
    </div>
</div>
@endsection
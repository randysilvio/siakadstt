@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Manajemen Kurikulum</h1>
        <a href="{{ route('kurikulum.create') }}" class="btn btn-primary">Tambah Kurikulum Baru</a>
    </div>
    {{-- ... (kode untuk menampilkan session success/error) ... --}}
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Nama Kurikulum</th>
                        <th>Tahun</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kurikulums as $kurikulum)
                        <tr>
                            <td>{{ $kurikulum->nama_kurikulum }}</td>
                            <td>{{ $kurikulum->tahun }}</td>
                            <td>
                                @if ($kurikulum->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('kurikulum.setActive', $kurikulum->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-info btn-sm" {{ $kurikulum->is_active ? 'disabled' : '' }}>Aktifkan</button>
                                </form>
                                <a href="{{ route('kurikulum.edit', $kurikulum->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('kurikulum.destroy', $kurikulum->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Belum ada data kurikulum.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
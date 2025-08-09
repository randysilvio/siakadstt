@extends('layouts.app')

@section('content')
    <h1 class="mb-4">Manajemen Tahun Akademik</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ route('tahun-akademik.create') }}" class="btn btn-primary mb-3">Tambah Tahun Akademik Baru</a>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Tahun</th>
                <th>Semester</th>
                <th>Status</th>
                <th>Periode KRS</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tahun_akademiks as $ta)
                <tr>
                    <td>{{ $ta->tahun }}</td>
                    <td>{{ $ta->semester }}</td>
                    <td>
                        @if ($ta->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary">Tidak Aktif</span>
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($ta->tanggal_mulai_krs)->isoFormat('D MMM Y') }} - {{ \Carbon\Carbon::parse($ta->tanggal_selesai_krs)->isoFormat('D MMM Y') }}</td>
                    <td>
                        <form action="{{ route('tahun-akademik.set-active', $ta->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-info btn-sm" {{ $ta->is_active ? 'disabled' : '' }}>
                                Aktifkan
                            </button>
                        </form>
                        <a href="{{ route('tahun-akademik.edit', $ta->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('tahun-akademik.destroy', $ta->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin?')">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Belum ada data tahun akademik.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
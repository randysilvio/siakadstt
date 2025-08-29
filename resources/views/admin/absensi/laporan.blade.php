@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Laporan Absensi Pegawai</h1>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.absensi.laporan.index') }}" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label for="search" class="form-label">Cari Nama Pegawai</label>
                        <input type="text" id="search" name="search" class="form-control" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-5">
                        <label for="tanggal" class="form-label">Filter Tanggal</label>
                        <input type="date" id="tanggal" name="tanggal" class="form-control" value="{{ request('tanggal') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                        <a href="{{ route('admin.absensi.laporan.index') }}" class="btn btn-secondary w-100 mt-2">Reset</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Pegawai</th>
                            <th>Tanggal</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($laporan as $item)
                            <tr>
                                <td>{{ $loop->iteration + $laporan->firstItem() - 1 }}</td>
                                <td>{{ $item->user->name ?? 'N/A' }}</td>
                                <td>{{ $item->tanggal_absensi->format('d M Y') }}</td>
                                <td>
                                    @if ($item->waktu_check_in)
                                        {{ $item->waktu_check_in->format('H:i:s') }}
                                        <a href="{{ asset('storage/' . $item->foto_check_in) }}" target="_blank" class="text-primary hover:underline ms-2">[Foto]</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                     @if ($item->waktu_check_out)
                                        {{ $item->waktu_check_out->format('H:i:s') }}
                                        <a href="{{ asset('storage/' . $item->foto_check_out) }}" target="_blank" class="text-primary hover:underline ms-2">[Foto]</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $item->status_kehadiran == 'Hadir' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $item->status_kehadiran }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data absensi ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $laporan->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
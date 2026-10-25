@extends('layouts.app')

@section('content')
@php
    $fakultasAktif = \App\Models\Pengaturan::get('fitur_fakultas_aktif', '0') === '1';
@endphp
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Program Studi</h3>
            <span class="text-muted small uppercase">Daftar Program Studi & Ketua Program Studi</span>
        </div>
        <div class="d-flex gap-2">
            @if($fakultasAktif)
                <a href="{{ route('admin.fakultas.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                    Kelola Fakultas
                </a>
            @endif
            <a href="{{ route('admin.program-studi.create') }}" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm">
                Tambah Program Studi
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold">
            {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold">
            {{ session('error') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Program Studi
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 6%;">NO</th>
                            <th style="width: 12%;">KODE</th>
                            <th style="width: 8%;">JENJANG</th>
                            <th class="text-start">NAMA PROGRAM STUDI</th>
                            @if($fakultasAktif)
                                <th class="text-start">FAKULTAS</th>
                            @endif
                            <th class="text-start" style="width: 26%;">KETUA PROGRAM STUDI</th>
                            <th style="width: 12%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse ($program_studis as $prodi)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted">{{ $loop->iteration }}</td>
                                <td class="text-center font-monospace fw-bold">{{ $prodi->kode_prodi ?? '-' }}</td>
                                <td class="text-center font-monospace">{{ $prodi->jenjang ?? 'S1' }}</td>
                                <td class="ps-3 uppercase fw-bold text-dark">{{ $prodi->nama_prodi }}</td>
                                @if($fakultasAktif)
                                    <td class="ps-3 uppercase text-muted">{{ optional($prodi->fakultas)->nama_fakultas ?? '-' }}</td>
                                @endif
                                <td class="ps-3 uppercase fw-bold text-dark">
                                    {{ optional($prodi->kaprodi)->nama_lengkap ?? '-' }}
                                </td>
                                <td class="text-center">
                                    <div class="btn-group rounded-0">
                                        <a href="{{ route('admin.program-studi.edit', $prodi->id) }}" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2">Edit</a>
                                        <form action="{{ route('admin.program-studi.destroy', $prodi->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus program studi ini?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $fakultasAktif ? 7 : 6 }}" class="text-center py-5 uppercase fw-bold text-muted">
                                    Data program studi belum tersedia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
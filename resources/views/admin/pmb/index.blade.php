@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-teal-700">Data Pendaftar PMB</h3>
        <div>
            <a href="{{ route('admin.pmb.index') }}" class="btn btn-outline-secondary btn-sm me-2">Semua</a>
            <a href="{{ route('admin.pmb.index', ['status' => 'menunggu_verifikasi']) }}" class="btn btn-warning btn-sm text-dark me-2">Perlu Verifikasi</a>
            <a href="{{ route('admin.pmb.index', ['status' => 'lulus']) }}" class="btn btn-success btn-sm">Sudah Lulus</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">No Pendaftaran</th>
                        <th>Nama Lengkap</th>
                        <th>Prodi Pilihan 1</th>
                        <th>Nilai Rapor</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- PERBAIKAN DISINI: Gunakan $pendaftars (ada 's' di belakang) --}}
                    @forelse($pendaftars as $item)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $item->no_pendaftaran }}</td>
                            <td>
                                <div class="fw-bold">{{ $item->user->name }}</div>
                                <small class="text-muted">{{ $item->sekolah_asal }}</small>
                            </td>
                            <td>{{ $item->prodi1->nama_prodi ?? '-' }}</td>
                            <td>{{ $item->nilai_rata_rata_rapor }}</td>
                            <td>
                                @if($item->status_pendaftaran == 'lulus')
                                    <span class="badge bg-success">DITERIMA</span>
                                @elseif($item->status_pendaftaran == 'tidak_lulus')
                                    <span class="badge bg-danger">DITOLAK</span>
                                @elseif($item->status_pendaftaran == 'menunggu_verifikasi')
                                    <span class="badge bg-warning text-dark">BUTUH CEK</span>
                                @else
                                    <span class="badge bg-secondary">DRAFT</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.pmb.show', $item->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Belum ada data pendaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{-- PERBAIKAN DISINI JUGA --}}
            {{ $pendaftars->links() }}
        </div>
    </div>
</div>
@endsection
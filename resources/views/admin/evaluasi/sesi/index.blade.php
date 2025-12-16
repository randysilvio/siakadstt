@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 fw-bold">Manajemen Sesi Evaluasi</h2>
            <p class="text-muted mb-0">Atur periode evaluasi dosen yang aktif.</p>
        </div>
        <a href="{{ route('admin.evaluasi-sesi.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Tambah Sesi Baru
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="ps-4">Nama Sesi</th>
                            <th>Tahun Akademik</th>
                            <th>Periode Aktif</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sesi as $item)
                            <tr>
                                <td class="ps-4 fw-bold text-dark">{{ $item->nama_sesi }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border fw-normal">
                                        {{ $item->tahunAkademik->tahun }}
                                    </span>
                                    <small class="text-muted ms-1">({{ $item->tahunAkademik->semester }})</small>
                                </td>
                                <td>
                                    <div class="d-flex flex-column small">
                                        <span class="text-muted"><i class="bi bi-calendar-event me-1"></i> Mulai: {{ \Carbon\Carbon::parse($item->tanggal_mulai)->isoFormat('D MMM Y') }}</span>
                                        <span class="text-muted"><i class="bi bi-calendar-check me-1"></i> Selesai: {{ \Carbon\Carbon::parse($item->tanggal_selesai)->isoFormat('D MMM Y') }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if ($item->is_active)
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">
                                            <i class="bi bi-check-circle-fill me-1"></i> Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">
                                            <i class="bi bi-dash-circle me-1"></i> Non-Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.evaluasi-sesi.edit', $item->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.evaluasi-sesi.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus sesi evaluasi ini? Data jawaban mungkin ikut terhapus.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-50"></i>
                                    Belum ada sesi evaluasi yang dibuat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($sesi->hasPages())
            <div class="card-footer bg-white border-top">
                {{ $sesi->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
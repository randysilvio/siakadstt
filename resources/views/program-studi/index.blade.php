@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 fw-bold">Manajemen Program Studi</h2>
            <p class="text-muted mb-0">Kelola data program studi dan ketua prodi.</p>
        </div>
        <a href="{{ route('admin.program-studi.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Tambah Prodi
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="ps-4 text-center" style="width: 60px;">No</th>
                            <th>Nama Program Studi</th>
                            <th>Ketua Prodi (Kaprodi)</th>
                            <th class="text-end pe-4" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($program_studis as $prodi)
                            <tr>
                                <td class="text-center fw-bold text-secondary">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                                            <i class="bi bi-building"></i>
                                        </div>
                                        <span class="fw-bold text-dark">{{ $prodi->nama_prodi }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($prodi->kaprodi)
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-person-badge me-2 text-muted"></i>
                                            <span>{{ $prodi->kaprodi->nama_lengkap }}</span>
                                        </div>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal">
                                            <i class="bi bi-dash-circle me-1"></i> Belum Diatur
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.program-studi.edit', $prodi->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.program-studi.destroy', $prodi->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus program studi ini? Data terkait mungkin akan hilang.');">
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
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <div class="mb-2"><i class="bi bi-building-slash fs-1 opacity-50"></i></div>
                                    Data program studi belum tersedia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(method_exists($program_studis, 'hasPages') && $program_studis->hasPages())
            <div class="card-footer bg-white border-top">
                {{ $program_studis->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
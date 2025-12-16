@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 fw-bold">Manajemen Pertanyaan Evaluasi</h2>
            <p class="text-muted mb-0">Kelola daftar pertanyaan untuk kuesioner dosen.</p>
        </div>
        <a href="{{ route('admin.evaluasi-pertanyaan.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Tambah Pertanyaan
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="ps-4 text-center" style="width: 80px;">Urutan</th>
                            <th>Pertanyaan</th>
                            <th style="width: 200px;">Tipe Jawaban</th>
                            <th class="text-center" style="width: 150px;">Status</th>
                            <th class="text-end pe-4" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pertanyaan as $item)
                            <tr>
                                <td class="text-center fw-bold text-secondary">{{ $item->urutan }}</td>
                                <td>
                                    <span class="fw-bold text-dark">{{ $item->pertanyaan }}</span>
                                </td>
                                <td>
                                    @if ($item->tipe_jawaban == 'skala_1_5')
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info">
                                            <i class="bi bi-123 me-1"></i> Skala 1-4
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                                            <i class="bi bi-text-paragraph me-1"></i> Teks / Esai
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($item->is_active)
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Aktif</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Non-Aktif</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.evaluasi-pertanyaan.edit', $item->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.evaluasi-pertanyaan.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pertanyaan ini?');">
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
                                    <i class="bi bi-clipboard-x fs-1 d-block mb-2 opacity-50"></i>
                                    Belum ada pertanyaan evaluasi yang dibuat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($pertanyaan->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $pertanyaan->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Bank Pertanyaan Evaluasi (EDOM)</h3>
            <span class="text-muted small uppercase">Manajemen Instrumen Kuesioner Penilaian Dosen oleh Mahasiswa</span>
        </div>
        <div>
            <a href="{{ route('admin.evaluasi-pertanyaan.create') }}" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Pertanyaan
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Butir Instrumen Evaluasi
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 10%;">NO. URUT</th>
                            <th class="text-start" style="width: 48%;">DESKRIPSI PERTANYAAN</th>
                            <th style="width: 17%;">FORMAT JAWABAN</th>
                            <th style="width: 12%;">STATUS</th>
                            <th style="width: 13%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse ($pertanyaan as $item)
                            <tr>
                                <td class="text-center fw-bold text-dark font-monospace">{{ $item->urutan }}</td>
                                <td class="text-start ps-3 fw-bold text-dark">{{ $item->pertanyaan }}</td>
                                <td class="text-center">
                                    @if ($item->tipe_jawaban == 'skala_1_5')
                                        <span class="badge bg-light text-dark border border-dark rounded-0 font-monospace uppercase">SKALA (1-4)</span>
                                    @else
                                        <span class="badge bg-light text-secondary border rounded-0 font-monospace uppercase">TEKS / ESAI</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($item->is_active)
                                        <span class="badge bg-success text-white rounded-0 font-monospace uppercase px-2 py-1" style="font-size: 10px;">AKTIF</span>
                                    @else
                                        <span class="badge bg-secondary text-white rounded-0 font-monospace uppercase px-2 py-1" style="font-size: 10px;">NON-AKTIF</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group rounded-0" role="group">
                                        <a href="{{ route('admin.evaluasi-pertanyaan.edit', $item->id) }}" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.evaluasi-pertanyaan.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus butir pertanyaan ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 uppercase fw-bold text-muted">
                                    Belum terdapat instrumen pertanyaan evaluasi dalam sistem.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($pertanyaan->hasPages())
        <div class="card-footer bg-white border-top py-3 rounded-0">
            <div class="d-flex justify-content-center">
                {{ $pertanyaan->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
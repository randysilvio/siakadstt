@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0 text-dark fw-bold">Bank Pertanyaan Evaluasi</h3>
            <span class="text-muted small">Manajemen instrumen kuesioner dosen oleh mahasiswa</span>
        </div>
        <a href="{{ route('admin.evaluasi-pertanyaan.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Tambah Instrumen Baru
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 bg-success text-white py-2 px-3 rounded-1 mb-4" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th class="text-center text-muted small" style="width: 80px;">NO. URUT</th>
                            <th class="text-muted small">DESKRIPSI PERTANYAAN</th>
                            <th class="text-muted small" style="width: 200px;">FORMAT JAWABAN</th>
                            <th class="text-center text-muted small" style="width: 120px;">STATUS</th>
                            <th class="text-end text-muted small pe-4" style="width: 150px;">TINDAKAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pertanyaan as $item)
                            <tr>
                                <td class="text-center fw-bold text-dark font-monospace">{{ $item->urutan }}</td>
                                <td>
                                    <span class="fw-bold text-dark">{{ $item->pertanyaan }}</span>
                                </td>
                                <td>
                                    @if ($item->tipe_jawaban == 'skala_1_5')
                                        <span class="badge bg-light text-dark border rounded-1">Skala Numerik (1-4)</span>
                                    @else
                                        <span class="badge bg-light text-secondary border rounded-1">Uraian / Teks</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($item->is_active)
                                        <span class="badge bg-success rounded-1 px-2">AKTIF</span>
                                    @else
                                        <span class="badge bg-secondary rounded-1 px-2">NON-AKTIF</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.evaluasi-pertanyaan.edit', $item->id) }}" class="btn btn-sm btn-light border text-dark me-1" title="Edit Data">Edit</a>
                                    <form action="{{ route('admin.evaluasi-pertanyaan.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus butir pertanyaan ini? Penghapusan dapat memengaruhi rekapitulasi data masa lalu.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">Belum terdapat instrumen pertanyaan evaluasi dalam sistem.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($pertanyaan->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $pertanyaan->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
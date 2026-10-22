@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Manajemen Periode Evaluasi Dosen</h3>
            <span class="text-muted small uppercase">Registrasi Jadwal & Masa Aktif Pengisian Kuesioner EDOM</span>
        </div>
        <div>
            <a href="{{ route('admin.evaluasi-sesi.create') }}" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Buka Sesi Baru
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
            Daftar Periode Evaluasi Terdaftar
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 6%;">NO</th>
                            <th class="text-start" style="width: 32%;">IDENTITAS SESI EVALUASI</th>
                            <th style="width: 20%;">TAHUN AKADEMIK</th>
                            <th style="width: 20%;">RENTANG WAKTU</th>
                            <th style="width: 10%;">STATUS</th>
                            <th style="width: 12%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse ($sesi as $index => $item)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted">{{ $sesi->firstItem() + $index }}</td>
                                <td class="text-start ps-3 fw-bold text-dark uppercase">{{ $item->nama_sesi }}</td>
                                <td class="text-center">
                                    <span class="text-dark font-monospace fw-bold">TA {{ $item->tahunAkademik->tahun ?? '-' }}</span><br>
                                    <span class="text-muted small uppercase">{{ $item->tahunAkademik->semester ?? '-' }}</span>
                                </td>
                                <td class="text-center font-monospace small">
                                    {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') }}
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
                                        <a href="{{ route('admin.evaluasi-sesi.edit', $item->id) }}" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.evaluasi-sesi.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Peringatan: Menghapus sesi evaluasi ini akan menghapus seluruh data kuesioner terkait. Lanjutkan?');">
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
                                <td colspan="6" class="text-center py-5 uppercase fw-bold text-muted">
                                    Belum terdapat catatan periode evaluasi dosen yang didaftarkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($sesi->hasPages())
            <div class="card-footer bg-white border-top py-3 rounded-0">
                <div class="d-flex justify-content-center">
                    {{ $sesi->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Pengaturan Gelombang Admisi (PMB)</h3>
            <span class="text-muted small uppercase">Manajemen Periode Pendaftaran, Biaya Formulir, & Jadwal Ujian Masuk</span>
        </div>
        <div>
            <a href="{{ route('admin.pmb-periods.create') }}" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Buka Gelombang Baru
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Gelombang Pendaftaran PMB
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 6%;">NO</th>
                            <th class="text-start" style="width: 28%;">IDENTITAS GELOMBANG</th>
                            <th style="width: 24%;">PERIODE PENDAFTARAN</th>
                            <th style="width: 15%;">BIAYA FORMULIR</th>
                            <th style="width: 12%;">STATUS</th>
                            <th style="width: 15%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse($periods as $index => $period)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted">{{ $index + 1 }}</td>
                                <td class="text-start ps-3 fw-bold text-dark uppercase">
                                    {{ $period->nama_gelombang }}
                                    <div class="small text-muted font-monospace" style="font-size: 10px;">TAHUN PMB {{ $period->tahun_pmb ?? '-' }}</div>
                                </td>
                                <td class="text-center font-monospace small">
                                    {{ $period->tanggal_buka->format('d/m/Y') }} s/d {{ $period->tanggal_tutup->format('d/m/Y') }}
                                </td>
                                <td class="text-center font-monospace fw-bold text-primary">
                                    Rp {{ number_format($period->biaya_pendaftaran, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if($period->is_active)
                                        <span class="badge bg-success text-white rounded-0 font-monospace uppercase px-2 py-1" style="font-size: 10px;">AKTIF</span>
                                    @else
                                        <span class="badge bg-secondary text-white rounded-0 font-monospace uppercase px-2 py-1" style="font-size: 10px;">NON-AKTIF</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        @if(!$period->is_active)
                                            <form action="{{ route('admin.pmb-periods.set-active', $period->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-success rounded-0 py-1 px-2 uppercase fw-bold" style="font-size: 10px;" title="Aktifkan Gelombang">
                                                    <i class="bi bi-power"></i> Aktifkan
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <div class="btn-group rounded-0" role="group">
                                            <a href="{{ route('admin.pmb-periods.edit', $period->id) }}" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form action="{{ route('admin.pmb-periods.destroy', $period->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Peringatan: Penghapusan data gelombang bersifat permanen. Lanjutkan?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 uppercase fw-bold text-muted">
                                    Belum terdapat data gelombang pendaftaran yang diatur dalam sistem.
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
@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0 text-dark fw-bold">Pengaturan Gelombang Admisi (PMB)</h3>
            <span class="text-muted small">Manajemen periode pendaftaran, biaya, dan jadwal ujian masuk</span>
        </div>
        <a href="{{ route('admin.pmb-periods.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Buka Gelombang Baru
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
                            <th class="text-muted small ps-4">IDENTITAS GELOMBANG</th>
                            <th class="text-muted small text-center">PERIODE PENDAFTARAN</th>
                            <th class="text-muted small text-end">BIAYA REGISTRASI</th>
                            <th class="text-center text-muted small" style="width: 150px;">STATUS PORTAL</th>
                            <th class="text-end text-muted small pe-4" style="width: 180px;">TINDAKAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($periods as $period)
                            <tr>
                                <td class="ps-4 fw-bold text-dark">
                                    {{ $period->nama_gelombang }}
                                </td>
                                <td class="text-center">
                                    <div class="font-monospace text-muted small">
                                        {{ $period->tanggal_buka->translatedFormat('d M Y') }} <i class="bi bi-arrow-right mx-1"></i> {{ $period->tanggal_tutup->translatedFormat('d M Y') }}
                                    </div>
                                </td>
                                <td class="text-end font-monospace text-dark">
                                    Rp {{ number_format($period->biaya_pendaftaran, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if($period->is_active)
                                        <span class="badge bg-success rounded-1 px-2">AKTIF (TERBUKA)</span>
                                    @else
                                        <span class="badge bg-secondary rounded-1 px-2">NON-AKTIF</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    @if(!$period->is_active)
                                        <form action="{{ route('admin.pmb-periods.set-active', $period->id) }}" method="POST" class="d-inline me-1">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-dark rounded-1" title="Buka Portal Pendaftaran Ini">
                                                Aktifkan
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <a href="{{ route('admin.pmb-periods.edit', $period->id) }}" class="btn btn-sm btn-light border text-dark me-1" title="Ubah Data">
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.pmb-periods.destroy', $period->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Peringatan: Penghapusan data gelombang bersifat permanen. Lanjutkan?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger rounded-1" title="Hapus">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">Belum terdapat data gelombang pendaftaran yang diatur dalam sistem.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0 text-dark fw-bold">Manajemen Periode Evaluasi Dosen</h3>
            <span class="text-muted small">Registrasi jadwal dan masa aktif pengisian kuesioner EDOM</span>
        </div>
        <a href="{{ route('admin.evaluasi-sesi.create') }}" class="btn btn-primary">
            <i class="bi bi-calendar-plus me-2"></i>Buka Sesi Baru
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
                            <th class="text-muted small ps-4">IDENTITAS SESI (PERIODE EVALUASI)</th>
                            <th class="text-muted small">TAHUN AKADEMIK TERKAIT</th>
                            <th class="text-muted small">RENTANG WAKTU PELAKSANAAN</th>
                            <th class="text-center text-muted small" style="width: 120px;">STATUS</th>
                            <th class="text-end text-muted small pe-4" style="width: 150px;">TINDAKAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sesi as $item)
                            <tr>
                                <td class="ps-4 fw-bold text-dark">{{ $item->nama_sesi }}</td>
                                <td>
                                    <span class="text-dark fw-semibold">TA {{ $item->tahunAkademik->tahun ?? '-' }}</span><br>
                                    <span class="text-muted small">Semester {{ $item->tahunAkademik->semester ?? '-' }}</span>
                                </td>
                                <td>
                                    <div class="font-monospace text-muted small">
                                        Mulai: {{ \Carbon\Carbon::parse($item->tanggal_mulai)->translatedFormat('d M Y') }}<br>
                                        Akhir: {{ \Carbon\Carbon::parse($item->tanggal_selesai)->translatedFormat('d M Y') }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if ($item->is_active)
                                        <span class="badge bg-success rounded-1 px-2">AKTIF</span>
                                    @else
                                        <span class="badge bg-secondary rounded-1 px-2">NON-AKTIF</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.evaluasi-sesi.edit', $item->id) }}" class="btn btn-sm btn-light border text-dark me-1" title="Edit Jadwal">Edit</a>
                                    <form action="{{ route('admin.evaluasi-sesi.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Peringatan: Menghapus sesi evaluasi ini akan menghapus seluruh data kuesioner yang telah diisi mahasiswa pada periode tersebut secara permanen. Lanjutkan?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus Permanen">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">Belum terdapat catatan periode evaluasi dosen yang didaftarkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($sesi->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $sesi->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
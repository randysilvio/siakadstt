@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Dashboard Program Studi</h3>
            <span class="text-muted small uppercase">Manajemen Validasi & Evaluasi Akademik: {{ $programStudi->nama_prodi ?? 'Program Studi' }}</span>
        </div>
        <div class="badge bg-dark rounded-0 px-3 py-2 fs-6 uppercase shadow-sm">
            <i class="bi bi-building me-1"></i> {{ $programStudi->nama_prodi ?? 'PRODI' }}
        </div>
    </div>

    {{-- TABEL & SMART FILTER DENGAN INDIKATOR ANGKA JUMLAH --}}
    <div class="card shadow-sm border-0 rounded-0">
        <div class="card-header bg-white py-3 border-bottom rounded-0">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('kaprodi.dashboard', ['search' => request('search')]) }}" class="btn btn-sm rounded-0 uppercase fw-bold small {{ !request('status') ? 'btn-dark' : 'btn-outline-dark' }}">
                        Semua <span class="badge bg-white {{ !request('status') ? 'text-dark' : 'text-muted border' }} ms-1 rounded-0" style="font-size: 10px;">{{ $countSemua ?? 0 }}</span>
                    </a>
                    <a href="{{ route('kaprodi.dashboard', ['status' => 'Menunggu Persetujuan', 'search' => request('search')]) }}" class="btn btn-sm rounded-0 uppercase fw-bold small {{ request('status') == 'Menunggu Persetujuan' ? 'btn-warning' : 'btn-outline-warning text-dark' }}">
                        Perlu Validasi <span class="badge bg-white text-dark ms-1 rounded-0" style="font-size: 10px;">{{ $countMenunggu ?? 0 }}</span>
                    </a>
                    <a href="{{ route('kaprodi.dashboard', ['status' => 'Disetujui', 'search' => request('search')]) }}" class="btn btn-sm rounded-0 uppercase fw-bold small {{ request('status') == 'Disetujui' ? 'btn-success' : 'btn-outline-success' }}">
                        Disetujui <span class="badge bg-white {{ request('status') == 'Disetujui' ? 'text-success' : 'text-muted border' }} ms-1 rounded-0" style="font-size: 10px;">{{ $countDisetujui ?? 0 }}</span>
                    </a>
                    <a href="{{ route('kaprodi.dashboard', ['status' => 'Ditolak', 'search' => request('search')]) }}" class="btn btn-sm rounded-0 uppercase fw-bold small {{ request('status') == 'Ditolak' ? 'btn-danger' : 'btn-outline-danger' }}">
                        Ditolak <span class="badge bg-white {{ request('status') == 'Ditolak' ? 'text-danger' : 'text-muted border' }} ms-1 rounded-0" style="font-size: 10px;">{{ $countDitolak ?? 0 }}</span>
                    </a>
                    <a href="{{ route('kaprodi.dashboard', ['status' => 'Belum', 'search' => request('search')]) }}" class="btn btn-sm rounded-0 uppercase fw-bold small {{ request('status') == 'Belum' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                        Belum Mengisi <span class="badge bg-white {{ request('status') == 'Belum' ? 'text-secondary' : 'text-muted border' }} ms-1 rounded-0" style="font-size: 10px;">{{ $countBelum ?? 0 }}</span>
                    </a>
                </div>
                
                <form action="{{ route('kaprodi.dashboard') }}" method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control form-control-sm rounded-0 border-dark uppercase small font-monospace" placeholder="CARI NIM / NAMA..." value="{{ request()->input('search') }}">
                    @if(request()->has('status'))
                        <input type="hidden" name="status" value="{{ request()->input('status') }}">
                    @endif
                    <button type="submit" class="btn btn-sm btn-dark rounded-0 uppercase fw-bold small px-3">Filter</button>
                </form>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr class="uppercase small">
                            <th class="ps-4 py-3">NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th class="text-center">Status KRS</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mahasiswas as $m)
                        <tr class="align-middle border-bottom">
                            <td class="ps-4 font-monospace fw-bold text-primary">{{ $m->nim }}</td>
                            <td class="uppercase fw-bold text-dark">{{ $m->nama_lengkap }}</td>
                            <td class="text-center">
                                @if($m->status_krs == 'Menunggu Persetujuan')
                                    <span class="badge bg-warning text-dark rounded-0 uppercase small fw-bold" style="font-size: 10px;">Perlu Validasi</span>
                                @elseif($m->status_krs == 'Disetujui')
                                    <span class="badge bg-success rounded-0 uppercase small fw-bold" style="font-size: 10px;">Disetujui</span>
                                @else
                                    <span class="badge bg-light text-muted border rounded-0 uppercase small fw-bold" style="font-size: 10px;">{{ $m->status_krs ?? 'DRAFT' }}</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('kaprodi.krs.show', $m->id) }}" class="btn btn-sm rounded-0 uppercase small fw-bold {{ $m->status_krs == 'Menunggu Persetujuan' ? 'btn-primary' : 'btn-light border' }}">
                                    {{ $m->status_krs == 'Menunggu Persetujuan' ? 'Validasi' : 'Detail' }}
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-5 text-muted small uppercase fw-bold">Tidak ditemukan data mahasiswa yang sesuai.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(isset($mahasiswas) && method_exists($mahasiswas, 'hasPages') && $mahasiswas->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $mahasiswas->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
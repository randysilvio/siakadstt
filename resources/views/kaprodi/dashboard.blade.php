@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Dashboard Ketua Program Studi</h3>
            <span class="text-muted small uppercase">Manajemen Validasi KRS & Early Warning System (EWS) Akademik: {{ $programStudi->nama_prodi ?? 'Program Studi' }}</span>
        </div>
        <div class="badge bg-dark rounded-0 px-3 py-2 fs-6 uppercase shadow-sm font-monospace">
            <i class="bi bi-building me-1"></i> {{ $programStudi->nama_prodi ?? 'PRODI' }}
        </div>
    </div>

    {{-- PANEL EARLY WARNING SYSTEM (EWS) MAHASISWA RAWAN DO (IPK < 2.00) --}}
    @if(isset($mahasiswaRawanDo) && $mahasiswaRawanDo->isNotEmpty())
        <div class="card border-0 shadow-sm rounded-0 border-start border-danger border-5 mb-4 bg-white">
            <div class="card-header bg-danger text-white py-2 px-3 rounded-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold uppercase small"><i class="bi bi-exclamation-triangle-fill me-2"></i>Early Warning System (EWS): {{ $mahasiswaRawanDo->count() }} Mahasiswa Rawan Akademik (IPK &lt; 2.00)</span>
                <span class="badge bg-white text-danger rounded-0 font-monospace">PERLU PENDAMPINGAN WALI</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 small">
                        <thead class="bg-light text-dark uppercase text-center fw-bold" style="font-size: 11px;">
                            <tr>
                                <th style="width: 14%;">NIM</th>
                                <th class="text-start">NAMA MAHASISWA</th>
                                <th style="width: 12%;">ANGKATAN</th>
                                <th style="width: 24%;">DOSEN WALI</th>
                                <th style="width: 12%;">SKS LULUS</th>
                                <th style="width: 12%;">IPK SAAT INI</th>
                                <th style="width: 10%;">TINDAKAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mahasiswaRawanDo as $rawan)
                                <tr>
                                    <td class="text-center font-monospace fw-bold text-danger">{{ $rawan->nim }}</td>
                                    <td class="ps-3 fw-bold uppercase text-dark">{{ $rawan->nama_lengkap }}</td>
                                    <td class="text-center font-monospace">{{ $rawan->tahun_masuk }}</td>
                                    <td class="text-center uppercase small">{{ optional($rawan->dosenWali)->nama_lengkap ?? 'BELUM ADA WALI' }}</td>
                                    <td class="text-center font-monospace">{{ $rawan->sks_lulus_hitung }} SKS</td>
                                    <td class="text-center font-monospace fw-bold text-danger">{{ number_format($rawan->ipk_hitung, 2) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('kaprodi.krs.show', $rawan->id) }}" class="btn btn-sm btn-outline-danger rounded-0 py-0 px-2 uppercase fw-bold" style="font-size: 10px;">
                                            Tinjau KRS
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- TABEL & SMART FILTER DENGAN INDIKATOR ANGKA JUMLAH --}}
    <div class="card shadow-sm border-0 rounded-0 border-top border-dark border-4 mb-5">
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
                <table class="table table-bordered align-middle mb-0 small">
                    <thead class="bg-dark text-white uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 14%;">NIM</th>
                            <th class="text-start">NAMA MAHASISWA</th>
                            <th style="width: 22%;">DOSEN WALI</th>
                            <th style="width: 12%;">IPK / SKS</th>
                            <th style="width: 15%;">STATUS KRS</th>
                            <th style="width: 13%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mahasiswas as $m)
                        <tr>
                            <td class="text-center font-monospace fw-bold text-primary">{{ $m->nim }}</td>
                            <td class="ps-3 uppercase fw-bold text-dark">{{ $m->nama_lengkap }}</td>
                            <td class="text-center uppercase text-muted">{{ optional($m->dosenWali)->nama_lengkap ?? '-' }}</td>
                            <td class="text-center font-monospace">
                                <span class="fw-bold {{ ($m->ipk_hitung > 0 && $m->ipk_hitung < 2.00) ? 'text-danger' : 'text-dark' }}">
                                    {{ number_format($m->ipk_hitung ?? 0, 2) }}
                                </span>
                                <span class="text-muted">({{ $m->sks_lulus_hitung ?? 0 }} SKS)</span>
                            </td>
                            <td class="text-center">
                                @if(isset($m->status_krs_display) && $m->status_krs_display == 'Menunggu Persetujuan')
                                    <span class="badge bg-warning text-dark rounded-0 uppercase font-monospace fw-bold" style="font-size: 10px;">PERLU VALIDASI</span>
                                @elseif(isset($m->status_krs_display) && $m->status_krs_display == 'Disetujui')
                                    <span class="badge bg-success rounded-0 uppercase font-monospace fw-bold" style="font-size: 10px;">DISETUJUI</span>
                                @elseif(isset($m->status_krs_display) && $m->status_krs_display == 'Ditolak')
                                    <span class="badge bg-danger rounded-0 uppercase font-monospace fw-bold" style="font-size: 10px;">DITOLAK</span>
                                @else
                                    <span class="badge bg-light text-muted border rounded-0 uppercase font-monospace fw-bold" style="font-size: 10px;">BELUM MENGAJUKAN</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('kaprodi.krs.show', $m->id) }}" class="btn btn-sm rounded-0 uppercase small fw-bold py-1 px-3 {{ (isset($m->status_krs_display) && $m->status_krs_display == 'Menunggu Persetujuan') ? 'btn-primary' : 'btn-outline-dark' }}">
                                    {{ (isset($m->status_krs_display) && $m->status_krs_display == 'Menunggu Persetujuan') ? 'Validasi' : 'Detail' }}
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted small uppercase fw-bold">Tidak ditemukan data mahasiswa yang sesuai.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(isset($mahasiswas) && method_exists($mahasiswas, 'hasPages') && $mahasiswas->hasPages())
            <div class="card-footer bg-white border-top py-3 rounded-0">
                <div class="d-flex justify-content-center">
                    {{ $mahasiswas->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
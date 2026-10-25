@extends('layouts.app')

@section('content')
@php
    $istilahPembimbing = \App\Models\Pengaturan::get('istilah_pembimbing', 'Dosen Tutor');
@endphp
<div class="container-fluid px-3 px-md-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 mt-2 gap-2">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Dashboard Ketua Program Studi</h3>
            <span class="text-muted small uppercase">Program Studi {{ $programStudi->nama_prodi ?? '-' }}</span>
        </div>
        <div>
            <span class="badge bg-dark px-3 py-2 uppercase font-monospace">{{ $programStudi->nama_prodi ?? 'PRODI' }}</span>
        </div>
    </div>

    {{-- DAFTAR MAHASISWA DENGAN IPK DI BAWAH 2.00 --}}
    @if(isset($mahasiswaRawanDo) && $mahasiswaRawanDo->isNotEmpty())
        <div class="card border-0 shadow-sm border-top border-danger border-4 mb-4 bg-white">
            <div class="card-header bg-dark text-white py-2.5 px-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold uppercase small">Perhatian Akademik: {{ $mahasiswaRawanDo->count() }} Mahasiswa dengan IPK &lt; 2.00</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 small">
                        <thead class="bg-light text-dark uppercase text-center fw-bold">
                            <tr>
                                <th style="width: 14%;">NIM</th>
                                <th class="text-start">NAMA MAHASISWA</th>
                                <th style="width: 12%;">ANGKATAN</th>
                                <th style="width: 24%;">{{ strtoupper($istilahPembimbing) }}</th>
                                <th style="width: 12%;">SKS LULUS</th>
                                <th style="width: 12%;">IPK</th>
                                <th style="width: 10%;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mahasiswaRawanDo as $rawan)
                                <tr>
                                    <td class="text-center font-monospace fw-bold text-dark">{{ $rawan->nim }}</td>
                                    <td class="ps-3 fw-bold uppercase text-dark">{{ $rawan->nama_lengkap }}</td>
                                    <td class="text-center font-monospace">{{ $rawan->tahun_masuk }}</td>
                                    <td class="text-center uppercase small">{{ optional($rawan->dosenWali)->nama_lengkap ?? '-' }}</td>
                                    <td class="text-center font-monospace">{{ $rawan->sks_lulus_hitung }} SKS</td>
                                    <td class="text-center font-monospace fw-bold text-danger">{{ number_format($rawan->ipk_hitung, 2) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('kaprodi.krs.show', $rawan->id) }}" class="btn btn-sm btn-outline-dark py-0 px-2 uppercase fw-bold" style="font-size: 10px;">
                                            Lihat KRS
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

    {{-- TABEL VALIDASI KRS PROGRAM STUDI --}}
    <div class="card shadow-sm border-0 border-top border-dark border-4 mb-5">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex flex-wrap gap-1.5">
                    <a href="{{ route('kaprodi.dashboard', ['search' => request('search')]) }}" class="btn btn-sm uppercase fw-bold small {{ !request('status') ? 'btn-dark' : 'btn-outline-dark' }}">
                        Semua ({{ $countSemua ?? 0 }})
                    </a>
                    <a href="{{ route('kaprodi.dashboard', ['status' => 'Menunggu Persetujuan', 'search' => request('search')]) }}" class="btn btn-sm uppercase fw-bold small {{ request('status') == 'Menunggu Persetujuan' ? 'btn-dark' : 'btn-outline-dark' }}">
                        Menunggu Validasi ({{ $countMenunggu ?? 0 }})
                    </a>
                    <a href="{{ route('kaprodi.dashboard', ['status' => 'Disetujui', 'search' => request('search')]) }}" class="btn btn-sm uppercase fw-bold small {{ request('status') == 'Disetujui' ? 'btn-dark' : 'btn-outline-dark' }}">
                        Disetujui ({{ $countDisetujui ?? 0 }})
                    </a>
                    <a href="{{ route('kaprodi.dashboard', ['status' => 'Ditolak', 'search' => request('search')]) }}" class="btn btn-sm uppercase fw-bold small {{ request('status') == 'Ditolak' ? 'btn-dark' : 'btn-outline-dark' }}">
                        Ditolak ({{ $countDitolak ?? 0 }})
                    </a>
                    <a href="{{ route('kaprodi.dashboard', ['status' => 'Belum', 'search' => request('search')]) }}" class="btn btn-sm uppercase fw-bold small {{ request('status') == 'Belum' ? 'btn-dark' : 'btn-outline-dark' }}">
                        Belum Isi ({{ $countBelum ?? 0 }})
                    </a>
                </div>
                
                <form action="{{ route('kaprodi.dashboard') }}" method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control form-control-sm border-dark uppercase small font-monospace" placeholder="Cari NIM / Nama..." value="{{ request()->input('search') }}">
                    @if(request()->has('status'))
                        <input type="hidden" name="status" value="{{ request()->input('status') }}">
                    @endif
                    <button type="submit" class="btn btn-sm btn-dark uppercase fw-bold small px-3">Cari</button>
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
                            <th style="width: 22%;">{{ strtoupper($istilahPembimbing) }}</th>
                            <th style="width: 14%;">IPK / SKS</th>
                            <th style="width: 15%;">STATUS KRS</th>
                            <th style="width: 13%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mahasiswas as $m)
                        <tr>
                            <td class="text-center font-monospace fw-bold text-dark">{{ $m->nim }}</td>
                            <td class="ps-3 uppercase fw-bold text-dark">{{ $m->nama_lengkap }}</td>
                            <td class="text-center uppercase text-muted">{{ optional($m->dosenWali)->nama_lengkap ?? '-' }}</td>
                            <td class="text-center font-monospace">
                                <span class="fw-bold {{ ($m->ipk_hitung > 0 && $m->ipk_hitung < 2.00) ? 'text-danger' : 'text-dark' }}">
                                    {{ number_format($m->ipk_hitung ?? 0, 2) }}
                                </span>
                                <span class="text-muted">({{ $m->sks_lulus_hitung ?? 0 }})</span>
                            </td>
                            <td class="text-center font-monospace uppercase fw-bold">
                                {{ $m->status_krs_display ?? 'BELUM ISI' }}
                            </td>
                            <td class="text-center">
                                <a href="{{ route('kaprodi.krs.show', $m->id) }}" class="btn btn-sm uppercase small fw-bold py-1 px-3 {{ (isset($m->status_krs_display) && $m->status_krs_display == 'Menunggu Persetujuan') ? 'btn-dark' : 'btn-outline-dark' }}">
                                    {{ (isset($m->status_krs_display) && $m->status_krs_display == 'Menunggu Persetujuan') ? 'Validasi' : 'Detail' }}
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted small uppercase fw-bold">Tidak ditemukan data mahasiswa.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(isset($mahasiswas) && method_exists($mahasiswas, 'hasPages') && $mahasiswas->hasPages())
            <div class="card-footer bg-white border-top py-3">
                <div class="d-flex justify-content-center">{{ $mahasiswas->links() }}</div>
            </div>
        @endif
    </div>
</div>
@endsection
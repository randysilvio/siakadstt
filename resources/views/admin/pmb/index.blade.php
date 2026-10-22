@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Manajemen Admisi (PMB)</h3>
            <span class="text-muted small uppercase">Verifikasi Dokumen & Status Kelulusan Calon Mahasiswa Baru</span>
        </div>
        
        <div class="d-flex gap-2">
            <a href="{{ route('admin.pmb.export.excel', request()->all()) }}" class="btn btn-sm btn-outline-dark fw-bold rounded-0 px-3 uppercase small">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </a>
            <a href="{{ route('admin.pmb.export.pdf', request()->all()) }}" target="_blank" class="btn btn-sm btn-dark fw-bold rounded-0 px-3 uppercase small shadow-sm">
                <i class="bi bi-printer-fill me-1"></i> Cetak Rekap PDF
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

    {{-- Filter Panel Flat --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-light">
        <div class="card-body p-4">
            <form action="{{ route('admin.pmb.index') }}" method="GET" id="pmbFilterForm">
                <div class="row g-3 align-items-end">
                    
                    <div class="col-12 mb-1 border-bottom pb-3">
                        <span class="text-dark small fw-bold uppercase d-block mb-2">Klasifikasi Status Pendaftaran</span>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('admin.pmb.index', array_merge(request()->query(), ['status' => null])) }}" class="btn btn-sm rounded-0 uppercase fw-bold small {{ !request('status') ? 'btn-dark' : 'btn-outline-dark bg-white' }}">Semua Data</a>
                            <a href="{{ route('admin.pmb.index', array_merge(request()->query(), ['status' => 'draft'])) }}" class="btn btn-sm rounded-0 uppercase fw-bold small {{ request('status') == 'draft' ? 'btn-dark' : 'btn-outline-dark bg-white' }}">Draft</a>
                            <a href="{{ route('admin.pmb.index', array_merge(request()->query(), ['status' => 'menunggu_verifikasi'])) }}" class="btn btn-sm rounded-0 uppercase fw-bold small {{ request('status') == 'menunggu_verifikasi' ? 'btn-warning text-dark' : 'btn-outline-dark bg-white' }}">Menunggu Verifikasi</a>
                            <a href="{{ route('admin.pmb.index', array_merge(request()->query(), ['status' => 'lulus'])) }}" class="btn btn-sm rounded-0 uppercase fw-bold small {{ request('status') == 'lulus' ? 'btn-success text-white' : 'btn-outline-dark bg-white' }}">Diterima Lulus</a>
                            <a href="{{ route('admin.pmb.index', array_merge(request()->query(), ['status' => 'tidak_lulus'])) }}" class="btn btn-sm rounded-0 uppercase fw-bold small {{ request('status') == 'tidak_lulus' ? 'btn-danger text-white' : 'btn-outline-dark bg-white' }}">Ditolak</a>
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-dark small fw-bold uppercase mb-1">Gelombang</label>
                        <select class="form-select rounded-0 uppercase small fw-bold" name="pmb_period_id" onchange="document.getElementById('pmbFilterForm').submit()">
                            <option value="">-- SEMUA PERIODE --</option>
                            @foreach($periods as $per)
                                <option value="{{ $per->id }}" {{ request('pmb_period_id') == $per->id ? 'selected' : '' }}>{{ $per->nama_gelombang }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-dark small fw-bold uppercase mb-1">Program Studi (Pilihan 1)</label>
                        <select class="form-select rounded-0 uppercase small fw-bold" name="pilihan_prodi_1_id" onchange="document.getElementById('pmbFilterForm').submit()">
                            <option value="">-- SEMUA PRODI --</option>
                            @foreach($prodis as $prd)
                                <option value="{{ $prd->id }}" {{ request('pilihan_prodi_1_id') == $prd->id ? 'selected' : '' }}>{{ $prd->nama_prodi }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-dark small fw-bold uppercase mb-1">Pencarian Kandidat</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white rounded-0"><i class="bi bi-search text-dark"></i></span>
                            <input type="text" name="search" class="form-control rounded-0 font-monospace uppercase" placeholder="NO. DAFTAR / NAMA..." value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="col-md-2 d-flex gap-1">
                        <button class="btn btn-primary rounded-0 flex-grow-1 uppercase fw-bold small py-2 shadow-sm" type="submit">Cari</button>
                        <a href="{{ route('admin.pmb.index') }}" class="btn btn-outline-dark rounded-0 px-3 py-2" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Data Table Standar Enterprise --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Kandidat Pendaftar PMB
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light text-dark small uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 15%;">NO. PENDAFTARAN</th>
                            <th class="text-start" style="width: 27%;">IDENTITAS KANDIDAT</th>
                            <th class="text-start" style="width: 20%;">PILIHAN PRODI 1</th>
                            <th style="width: 15%;">GELOMBANG</th>
                            <th style="width: 11%;">STATUS</th>
                            <th style="width: 12%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="small text-dark">
                        @forelse($pendaftars as $item)
                            <tr>
                                <td class="text-center">
                                    <span class="fw-bold font-monospace text-primary">{{ $item->no_pendaftaran ?? 'N/A' }}</span>
                                    <div class="small text-muted font-monospace" style="font-size: 10px;">{{ $item->created_at->format('d/m/Y') }}</div>
                                </td>
                                <td class="text-start ps-3">
                                    <div class="fw-bold text-dark uppercase">{{ $item->user->name ?? 'DATA KOSONG' }}</div>
                                    <div class="small text-muted font-monospace" style="font-size: 11px;">WA: {{ $item->no_hp ?? '-' }}</div>
                                </td>
                                <td class="text-start ps-3 uppercase fw-bold text-dark">
                                    {{ $item->prodi1->nama_prodi ?? '-' }}
                                </td>
                                <td class="text-center uppercase text-muted font-monospace">
                                    {{ $item->period->nama_gelombang ?? '-' }}
                                </td>
                                <td class="text-center">
                                    @if($item->status_pendaftaran == 'lulus')
                                        <span class="badge bg-success text-white rounded-0 font-monospace uppercase px-2 py-1" style="font-size: 10px;">DITERIMA</span>
                                    @elseif($item->status_pendaftaran == 'tidak_lulus')
                                        <span class="badge bg-danger text-white rounded-0 font-monospace uppercase px-2 py-1" style="font-size: 10px;">DITOLAK</span>
                                    @elseif($item->status_pendaftaran == 'menunggu_verifikasi')
                                        <span class="badge bg-warning text-dark rounded-0 font-monospace uppercase px-2 py-1" style="font-size: 10px;">DIPROSES</span>
                                    @else
                                        <span class="badge bg-secondary text-white rounded-0 font-monospace uppercase px-2 py-1" style="font-size: 10px;">DRAFT</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group rounded-0" role="group">
                                        <a href="{{ route('admin.pmb.show', $item->id) }}" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2 uppercase fw-bold" style="font-size: 10px;" title="Verifikasi Data">
                                            <i class="bi bi-eye me-1"></i> Buka
                                        </a>
                                        <form action="{{ route('admin.pmb.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Peringatan: Penghapusan akan menghilangkan seluruh histori dokumen dan tagihan registrasi pendaftar ini. Lanjutkan?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2" title="Hapus Data">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 uppercase fw-bold text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                    Belum terdapat data kandidat yang memenuhi kriteria penyaringan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($pendaftars->hasPages())
            <div class="card-footer bg-white border-top py-3 rounded-0">
                <div class="d-flex justify-content-center">
                    {{ $pendaftars->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
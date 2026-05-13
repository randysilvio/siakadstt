@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">Manajemen Admisi (PMB)</h3>
            <span class="text-muted small">Verifikasi dokumen dan status kelulusan calon mahasiswa baru</span>
        </div>
        
        <div class="btn-group shadow-sm">
            <a href="{{ route('admin.pmb.export.excel', request()->all()) }}" class="btn btn-outline-dark fw-bold rounded-start-1">
                <i class="bi bi-file-earmark-excel me-1"></i> Unduh Excel
            </a>
            <a href="{{ route('admin.pmb.export.pdf', request()->all()) }}" target="_blank" class="btn btn-dark fw-bold rounded-end-1">
                <i class="bi bi-printer me-1"></i> Cetak Dokumen
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 bg-success text-white py-2 px-3 rounded-1 shadow-sm mb-4" role="alert">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger border-0 bg-danger text-white py-2 px-3 rounded-1 shadow-sm mb-4" role="alert">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter Panel Formal --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.pmb.index') }}" method="GET" id="pmbFilterForm">
                <div class="row g-3 align-items-end">
                    
                    {{-- Navigasi Status --}}
                    <div class="col-12 mb-2 border-bottom pb-3">
                        <span class="text-muted small fw-bold d-block mb-2">KLASIFIKASI STATUS PENDAFTARAN</span>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('admin.pmb.index', array_merge(request()->query(), ['status' => null])) }}" class="btn btn-sm rounded-1 {{ !request('status') ? 'btn-dark' : 'btn-outline-secondary' }}">Semua Data</a>
                            <a href="{{ route('admin.pmb.index', array_merge(request()->query(), ['status' => 'draft'])) }}" class="btn btn-sm rounded-1 {{ request('status') == 'draft' ? 'btn-dark' : 'btn-outline-secondary' }}">Draft / Belum Lengkap</a>
                            <a href="{{ route('admin.pmb.index', array_merge(request()->query(), ['status' => 'menunggu_verifikasi'])) }}" class="btn btn-sm rounded-1 {{ request('status') == 'menunggu_verifikasi' ? 'btn-warning text-dark fw-bold' : 'btn-outline-warning text-dark' }}">Menunggu Verifikasi</a>
                            <a href="{{ route('admin.pmb.index', array_merge(request()->query(), ['status' => 'lulus'])) }}" class="btn btn-sm rounded-1 {{ request('status') == 'lulus' ? 'btn-success text-white fw-bold' : 'btn-outline-success' }}">Diterima Lulus</a>
                            <a href="{{ route('admin.pmb.index', array_merge(request()->query(), ['status' => 'tidak_lulus'])) }}" class="btn btn-sm rounded-1 {{ request('status') == 'tidak_lulus' ? 'btn-danger text-white fw-bold' : 'btn-outline-danger' }}">Ditolak</a>
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        </div>
                    </div>

                    {{-- Dropdown Gelombang --}}
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold mb-1">GELOMBANG</label>
                        <select class="form-select rounded-1" name="pmb_period_id" onchange="document.getElementById('pmbFilterForm').submit()">
                            <option value="">-- Semua Periode --</option>
                            @foreach($periods as $per)
                                <option value="{{ $per->id }}" {{ request('pmb_period_id') == $per->id ? 'selected' : '' }}>{{ $per->nama_gelombang }} ({{ $per->tahun_akademik }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dropdown Program Studi --}}
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold mb-1">PROGRAM STUDI (PILIHAN 1)</label>
                        <select class="form-select rounded-1" name="pilihan_prodi_1_id" onchange="document.getElementById('pmbFilterForm').submit()">
                            <option value="">-- Semua Program Studi --</option>
                            @foreach($prodis as $prd)
                                <option value="{{ $prd->id }}" {{ request('pilihan_prodi_1_id') == $prd->id ? 'selected' : '' }}>{{ $prd->nama_prodi }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Search Text --}}
                    <div class="col-md-5">
                        <label class="form-label text-muted small fw-bold mb-1">PENCARIAN KANDIDAT</label>
                        <div class="input-group rounded-1">
                            <input type="text" name="search" class="form-control rounded-start-1" placeholder="Ketik nomor pendaftaran atau nama lengkap..." value="{{ request('search') }}">
                            <button class="btn btn-dark rounded-end-1" type="submit">Cari Data</button>
                        </div>
                    </div>

                    <div class="col-md-1 d-grid">
                        <a href="{{ route('admin.pmb.index') }}" class="btn btn-light border rounded-1">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th class="ps-4 py-3 text-muted small" style="width: 150px;">NO. PENDAFTARAN</th>
                            <th class="text-muted small">IDENTITAS KANDIDAT</th>
                            <th class="text-muted small">PILIHAN PRIORITAS</th>
                            <th class="text-muted small">GELOMBANG ADMISI</th>
                            <th class="text-center text-muted small" style="width: 130px;">STATUS</th>
                            <th class="text-end text-muted small pe-4" style="width: 120px;">TINDAKAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendaftars as $item)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold font-monospace text-dark">{{ $item->no_pendaftaran ?? 'N/A' }}</span>
                                    <div class="small text-muted mt-1">{{ $item->created_at->format('d M Y') }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item->user->name ?? 'Data Kosong' }}</div>
                                    <div class="small text-muted mt-1 font-monospace">{{ $item->no_hp ?? '-' }}</div>
                                </td>
                                <td>
                                    <span class="fw-semibold text-secondary">{{ $item->prodi1->nama_prodi ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $item->period->nama_gelombang ?? '-' }}</span>
                                </td>
                                <td class="text-center">
                                    @if($item->status_pendaftaran == 'lulus')
                                        <span class="badge bg-success rounded-1 px-2 py-1 w-100">DITERIMA</span>
                                    @elseif($item->status_pendaftaran == 'tidak_lulus')
                                        <span class="badge bg-danger rounded-1 px-2 py-1 w-100">DITOLAK</span>
                                    @elseif($item->status_pendaftaran == 'menunggu_verifikasi')
                                        <span class="badge bg-warning text-dark rounded-1 px-2 py-1 w-100">DIPROSES</span>
                                    @else
                                        <span class="badge bg-secondary rounded-1 px-2 py-1 w-100">DRAFT</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.pmb.show', $item->id) }}" class="btn btn-sm btn-light border text-dark me-1" title="Verifikasi Data">
                                        Buka
                                    </a>
                                    <form action="{{ route('admin.pmb.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Peringatan: Penghapusan akan menghilangkan seluruh histori dokumen dan tagihan registrasi pendaftar ini. Lanjutkan?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger rounded-1" title="Hapus Data">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">Belum terdapat data kandidat yang memenuhi kriteria penyaringan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($pendaftars->hasPages())
            <div class="card-footer bg-white border-top py-3 px-4">
                {{ $pendaftars->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    window.addEventListener('beforeunload', function() {
        sessionStorage.setItem('posisiScrollPMB', window.scrollY);
    });
    window.addEventListener('load', function() {
        let posisiScroll = sessionStorage.getItem('posisiScrollPMB');
        if (posisiScroll !== null) {
            window.scrollTo({ top: parseInt(posisiScroll), behavior: 'instant' });
            sessionStorage.removeItem('posisiScrollPMB');
        }
    });
</script>
@endsection
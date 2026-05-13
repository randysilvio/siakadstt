@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- HEADER HALAMAN --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold">Pangkalan Data Tenaga Pendidik</h3>
            <span class="text-muted small uppercase">Sistem Repositori Data Dosen Terintegrasi</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.dosen.create') }}" class="btn btn-primary rounded-0 px-3">
                <i class="bi bi-plus-lg me-1"></i> REGISTRASI DOSEN
            </a>
            <div class="btn-group">
                <button type="button" class="btn btn-success rounded-0 px-3 dropdown-toggle" data-bs-toggle="dropdown">
                    INSTRUMEN DATA
                </button>
                <ul class="dropdown-menu dropdown-menu-end rounded-0 border-0 shadow-sm">
                    <li><a class="dropdown-item small fw-bold" href="{{ route('admin.dosen.export') }}">EKSPOR DATA EXCEL</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item small fw-bold" href="#" data-bs-toggle="modal" data-bs-target="#importDosenModal">IMPOR DATA EXCEL</a></li>
                </ul>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 bg-success text-white py-2 px-3 rounded-0 mb-4">{{ session('success') }}</div>
    @endif

    {{-- FILTER DINAMIS FORMAL --}}
    <div class="card border-0 shadow-sm mb-4 rounded-0">
        <div class="card-body p-3 bg-light">
            <form action="{{ route('admin.dosen.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label text-muted small fw-bold mb-1 uppercase">Pencarian Identitas (Nama/NIDN)</label>
                    <input type="text" name="search" class="form-control rounded-0 border-secondary-subtle" value="{{ request('search') }}" placeholder="Masukkan kata kunci pencarian...">
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold mb-1 uppercase">Filter Jabatan Akademik</label>
                    <select name="jabatan" class="form-select rounded-0 border-secondary-subtle">
                        <option value="">-- Semua Jabatan --</option>
                        @foreach($jabatans as $jb)
                            <option value="{{ $jb }}" {{ request('jabatan') == $jb ? 'selected' : '' }}>{{ strtoupper($jb) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-grid">
                    <button type="submit" class="btn btn-dark rounded-0 fw-bold">TERAPKAN PENYARINGAN</button>
                </div>
            </form>
        </div>
    </div>

    {{-- TABEL DATA DOSEN --}}
    <div class="card border-0 shadow-sm rounded-0 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th class="ps-4 py-3 text-muted small" style="width: 150px;">NIDN</th>
                            <th class="text-muted small">NAMA LENGKAP & GELAR</th>
                            <th class="text-muted small">BIDANG KEAHLIAN</th>
                            <th class="text-muted small">EMAIL INSTITUSI</th>
                            <th class="text-end text-muted small pe-4" style="width: 150px;">TINDAKAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dosens as $dosen)
                            <tr>
                                <td class="ps-4 font-monospace fw-bold text-dark">{{ $dosen->nidn }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $dosen->foto_url }}" class="rounded-0 border me-3" width="40" height="40" style="object-fit: cover;">
                                        <div>
                                            <div class="fw-bold text-dark uppercase small">{{ $dosen->nama_lengkap }}</div>
                                            @if($dosen->is_keuangan)
                                                <span class="badge bg-success rounded-0" style="font-size: 0.65rem;">STAFF KEUANGAN</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="small text-dark">{{ $dosen->bidang_keahlian ?? '-' }}</td>
                                <td class="font-monospace small text-muted">{{ $dosen->user->email ?? '-' }}</td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.dosen.edit', $dosen->id) }}" class="btn btn-sm btn-light border rounded-0" title="Ubah Data">UBAH</a>
                                        <form action="{{ route('admin.dosen.destroy', $dosen->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Peringatan: Penghapusan data dosen berakibat pada penghapusan akun login terkait secara permanen. Lanjutkan?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger rounded-0">HAPUS</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-5 text-muted small uppercase">Data tenaga pendidik tidak ditemukan dalam basis data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($dosens->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $dosens->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

{{-- MODAL IMPOR DATA --}}
<div class="modal fade" id="importDosenModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-0 border-0">
            <div class="modal-header border-bottom py-3">
                <h6 class="modal-title fw-bold uppercase">Impor Data Tenaga Pendidik</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.dosen.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3 italic">Gunakan template resmi sistem untuk meminimalisir kesalahan sinkronisasi data.</p>
                    <a href="{{ route('admin.dosen.import.template') }}" class="btn btn-outline-dark btn-sm w-100 rounded-0 mb-4 fw-bold">UNDUH TEMPLATE EXCEL</a>
                    
                    <label class="form-label small fw-bold">PILIH BERKAS EXCEL (.XLSX / .XLS)</label>
                    <input class="form-control rounded-0" type="file" name="file" required>
                </div>
                <div class="modal-footer bg-light border-top py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-0" data-bs-dismiss="modal">BATAL</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-4">PROSES IMPOR</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Arsip Surat Keputusan & Tugas</h3>
            <span class="text-muted small uppercase">Pusat Manajemen Dokumen Administrasi Umum</span>
        </div>
        <div>
            <a href="{{ route('administrasi.surat-keputusan.create') }}" class="btn btn-dark btn-sm rounded-0 px-3 uppercase fw-bold shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Buat Surat Baru
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 rounded-0 shadow-sm alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-0 mb-4 bg-white">
        <div class="card-body p-3">
            <form action="{{ route('administrasi.surat-keputusan.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light rounded-0 border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control rounded-0 border-start-0" placeholder="Cari Judul atau Nomor Surat..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="jenis_surat" class="form-select rounded-0">
                        <option value="">-- Semua Kategori Surat --</option>
                        <option value="Surat Keputusan (SK)" {{ request('jenis_surat') == 'Surat Keputusan (SK)' ? 'selected' : '' }}>Surat Keputusan (SK)</option>
                        <option value="Surat Tugas" {{ request('jenis_surat') == 'Surat Tugas' ? 'selected' : '' }}>Surat Tugas</option>
                        <option value="Surat Keterangan" {{ request('jenis_surat') == 'Surat Keterangan' ? 'selected' : '' }}>Surat Keterangan</option>
                        <option value="Surat Undangan" {{ request('jenis_surat') == 'Surat Undangan' ? 'selected' : '' }}>Surat Undangan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-dark rounded-0 fw-bold uppercase w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
                        @if(request()->has('search') || request()->has('jenis_surat'))
                            <a href="{{ route('administrasi.surat-keputusan.index') }}" class="btn btn-outline-danger rounded-0" title="Reset Filter"><i class="bi bi-x-circle"></i></a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-dark text-white uppercase" style="font-size: 0.8rem;">
                        <tr>
                            <th class="py-3 px-4">No. Surat</th>
                            <th class="py-3">Jenis & Judul Dokumen</th>
                            <th class="py-3 text-center">Tanggal Terbit</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="py-3 text-center" style="width: 250px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white" style="font-size: 0.85rem;">
                        @forelse($suratKeputusans as $surat)
                            <tr>
                                <td class="px-4 font-monospace fw-bold">{{ $surat->nomor_surat ?? '- Belum Ada Nomor -' }}</td>
                                <td>
                                    <span class="badge bg-secondary rounded-0 mb-1">{{ $surat->jenis_surat }}</span><br>
                                    <strong class="uppercase text-dark">{{ $surat->judul }}</strong>
                                    @if($surat->dosens->count() > 0)
                                        <div class="small text-primary mt-1"><i class="bi bi-person-check-fill me-1"></i> Terhubung dengan {{ $surat->dosens->count() }} portofolio dosen</div>
                                    @endif
                                </td>
                                <td class="text-center font-monospace">
                                    {{ $surat->tanggal_terbit ? \Carbon\Carbon::parse($surat->tanggal_terbit)->locale('id')->isoFormat('D MMMM Y') : '-' }}
                                </td>
                                <td class="text-center">
                                    @if($surat->status == 'Selesai')
                                        <span class="badge bg-success rounded-0 px-2 py-1"><i class="bi bi-check2-all me-1"></i> Selesai (Final)</span>
                                    @elseif($surat->status == 'Menunggu Tanda Tangan')
                                        <span class="badge bg-warning text-dark rounded-0 px-2 py-1"><i class="bi bi-pen me-1"></i> Menunggu TTD</span>
                                    @else
                                        <span class="badge bg-light text-dark border rounded-0 px-2 py-1">Draf</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('administrasi.surat-keputusan.show', $surat->id) }}" class="btn btn-sm btn-outline-dark rounded-0" title="Pratinjau / Cetak" target="_blank">
                                            <i class="bi bi-printer"></i>
                                        </a>

                                        @if($surat->status != 'Selesai')
                                            <button type="button" class="btn btn-sm btn-primary rounded-0 fw-bold" data-bs-toggle="modal" data-bs-target="#modalUpload-{{ $surat->id }}" title="Upload PDF Final">
                                                <i class="bi bi-upload"></i>
                                            </button>
                                        @else
                                            {{-- [UPDATE]: Rute download bypass symlink sesuai judul surat --}}
                                            <a href="{{ route('surat-keputusan.download', $surat->id) }}" class="btn btn-sm btn-success rounded-0 fw-bold" title="Unduh Dokumen Final">
                                                <i class="bi bi-file-pdf"></i>
                                            </a>
                                        @endif

                                        <form action="{{ route('administrasi.surat-keputusan.duplicate', $surat->id) }}" method="POST" class="m-0" onsubmit="return confirm('Gunakan dokumen ini sebagai template draf surat baru?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-info rounded-0 text-dark" title="Gunakan Sebagai Template Baru">
                                                <i class="bi bi-copy"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('administrasi.surat-keputusan.destroy', $surat->id) }}" method="POST" class="m-0" onsubmit="return confirm('Hapus dokumen ini secara permanen?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-0" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>

                                    @if($surat->status != 'Selesai')
                                    <div class="modal fade" id="modalUpload-{{ $surat->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-0">
                                                <form action="{{ route('administrasi.surat-keputusan.upload-final', $surat->id) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="modal-header bg-dark text-white rounded-0">
                                                        <h6 class="modal-title uppercase fw-bold">Upload Dokumen Final</h6>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="small text-muted mb-3">Upload hasil <i>scan</i> dokumen (PDF) yang sudah distempel dan ditandatangani.</p>
                                                        <input type="file" name="file_pdf" class="form-control rounded-0" accept="application/pdf" required>
                                                    </div>
                                                    <div class="modal-footer bg-light">
                                                        <button type="button" class="btn btn-sm btn-outline-dark rounded-0 uppercase fw-bold" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-sm btn-primary rounded-0 uppercase fw-bold"><i class="bi bi-cloud-arrow-up me-1"></i> Upload & Finalisasi</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
                                    <span class="fw-bold uppercase">Surat tidak ditemukan.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($suratKeputusans->hasPages())
                <div class="px-4 py-3 border-top bg-light">
                    {{ $suratKeputusans->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
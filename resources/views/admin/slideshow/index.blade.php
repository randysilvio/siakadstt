@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0 text-dark fw-bold">Manajemen Slideshow (Spanduk)</h3>
            <span class="text-muted small">Pengaturan materi visual publikasi pada halaman utama</span>
        </div>
        <a href="{{ route('admin.slideshows.create') }}" class="btn btn-primary">
            <i class="bi bi-images me-2"></i>Unggah Slide Baru
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 bg-success text-white py-2 px-3 rounded-1 mb-4" role="alert">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter Bar Formal --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.slideshows.index') }}" method="GET" id="filterForm">
                <div class="row g-3 align-items-center">
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold mb-1">PENCARIAN JUDUL SLIDE</label>
                        <input type="text" class="form-control rounded-1" placeholder="Ketik kata kunci judul..." name="search" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold mb-1">STATUS PUBLIKASI</label>
                        <select class="form-select rounded-1" name="status" onchange="document.getElementById('filterForm').submit()">
                            <option value="" {{ request('status') == '' ? 'selected' : '' }}>-- Semua Status Publikasi --</option>
                            <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Tampil (Aktif)</option>
                            <option value="tidak_aktif" {{ request('status') == 'tidak_aktif' ? 'selected' : '' }}>Disembunyikan (Tidak Aktif)</option>
                        </select>
                    </div>
                    <div class="col-md-2 mt-4 text-end d-grid">
                        <button type="submit" class="btn btn-dark rounded-1 d-none d-md-block">Terapkan</button>
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
                            <th class="text-center text-muted small" style="width: 150px;">PREVIEW MEDIA</th>
                            <th class="text-muted small" style="width: 40%">JUDUL PUBLIKASI</th>
                            <th class="text-center text-muted small" style="width: 100px;">NO. URUT</th>
                            <th class="text-center text-muted small" style="width: 150px;">STATUS</th>
                            <th class="text-end text-muted small pe-4" style="width: 150px;">TINDAKAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($slides as $slide)
                            <tr>
                                <td class="text-center p-2">
                                    <div class="border rounded-1 bg-light p-1 d-inline-block">
                                        <img src="{{ asset('storage/' . $slide->gambar) }}" alt="Thumbnail" style="width: 120px; height: 60px; object-fit: cover;">
                                    </div>
                                </td>
                                <td class="fw-bold text-dark">{{ $slide->judul ?? 'Tanpa Judul / Deskripsi' }}</td>
                                <td class="text-center font-monospace">{{ $slide->urutan }}</td>
                                <td class="text-center">
                                    @if ($slide->is_aktif)
                                        <span class="badge bg-success rounded-1 px-2">AKTIF</span>
                                    @else
                                        <span class="badge bg-secondary rounded-1 px-2">NON-AKTIF</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.slideshows.edit', $slide->id) }}" class="btn btn-sm btn-light border text-dark me-1" title="Ubah Parameter">Edit</a>
                                    <form action="{{ route('admin.slideshows.destroy', $slide->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tindakan ini akan menghapus permanen file gambar dari sistem. Lanjutkan?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus Permanen">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">Belum terdapat data material slideshow yang diunggah ke dalam sistem.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($slides->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $slides->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
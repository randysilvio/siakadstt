@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Manajemen Kalender Akademik</h3>
            <span class="text-muted small uppercase">Pengaturan Agenda & Jadwal Kegiatan Institusi</span>
        </div>
        <a href="{{ route('admin.kalender.create') }}" class="btn btn-primary rounded-0 px-3 fw-bold uppercase small">
            Tambah Agenda
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 bg-success text-white py-2 px-3 rounded-0 mb-4 small shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Statistik & Pencarian --}}
    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-0 bg-dark text-white">
                <div class="card-body p-4 d-flex align-items-center">
                    <div>
                        <h6 class="fw-bold mb-1 uppercase small opacity-75">Total Agenda Terdaftar</h6>
                        <h3 class="fw-bold mb-0 font-monospace text-uppercase">{{ $kegiatans->total() }} Kegiatan</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-0 h-100 border">
                <div class="card-body p-4 bg-light">
                    <form action="{{ route('admin.kalender.index') }}" method="GET">
                        <label class="form-label text-muted small fw-bold uppercase mb-2">Penyaringan Judul</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control rounded-0 border-secondary-subtle" placeholder="Cari kegiatan..." value="{{ request('search') }}">
                            <button class="btn btn-dark rounded-0 px-3 uppercase fw-bold small" type="submit">Cari</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom text-uppercase">
                        <tr>
                            <th class="ps-4 py-3 text-muted small" style="width: 50px;">NO</th>
                            <th class="text-muted small">IDENTITAS KEGIATAN</th>
                            <th class="text-muted small">WAKTU PELAKSANAAN</th>
                            <th class="text-muted small">TARGET PESERTA</th>
                            <th class="text-end text-muted small pe-4" style="width: 150px;">MANAJEMEN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kegiatans as $kegiatan)
                            <tr>
                                <td class="ps-4 font-monospace text-muted">{{ $loop->iteration + ($kegiatans->currentPage() - 1) * $kegiatans->perPage() }}</td>
                                <td>
                                    <div class="fw-bold text-dark uppercase small">{{ $kegiatan->judul_kegiatan }}</div>
                                    <div class="text-muted small text-truncate" style="max-width: 300px;">{{ $kegiatan->deskripsi ?? '-' }}</div>
                                </td>
                                <td>
                                    <div class="font-monospace small text-dark fw-bold">
                                        {{ $kegiatan->tanggal_mulai->translatedFormat('d/m/Y') }} 
                                        @if(!$kegiatan->tanggal_mulai->eq($kegiatan->tanggal_selesai))
                                            - {{ $kegiatan->tanggal_selesai->translatedFormat('d/m/Y') }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @foreach($kegiatan->roles as $role)
                                        <span class="badge bg-light text-dark border rounded-0 font-normal uppercase" style="font-size: 0.6rem;">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.kalender.edit', $kegiatan->id) }}" class="btn btn-sm btn-light border rounded-0 px-3 uppercase small fw-bold">Ubah</a>
                                        <form action="{{ route('admin.kalender.destroy', $kegiatan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Konfirmasi hapus agenda?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger rounded-0 px-2 uppercase small fw-bold">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-5 text-muted small uppercase">Belum terdapat data agenda pada basis data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($kegiatans->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $kegiatans->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
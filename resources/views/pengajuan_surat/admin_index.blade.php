@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Manajemen Layanan Surat Mahasiswa (Ticketing BAAK)</h3>
            <span class="text-muted small uppercase">Verifikasi Permohonan Surat Keterangan Aktif, Izin Penelitian, & Cuti Mahasiswa</span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- FILTER BAR --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-light">
        <div class="card-body p-4">
            <form action="{{ route('admin.surat-mandiri.index') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small fw-bold uppercase text-dark mb-1">Cari Nama / NIM Pemohon</label>
                        <input type="text" name="search" class="form-control rounded-0 font-monospace uppercase" placeholder="KETIK NAMA ATAU NIM..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold uppercase text-dark mb-1">Status Verifikasi</label>
                        <select name="status" class="form-select rounded-0 uppercase small fw-bold">
                            <option value="">-- SEMUA STATUS --</option>
                            <option value="Menunggu Verifikasi" {{ request('status') == 'Menunggu Verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                            <option value="Disetujui" {{ request('status') == 'Disetujui' ? 'selected' : '' }}>Disetujui / Terbit</option>
                            <option value="Ditolak" {{ request('status') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-0 flex-grow-1 uppercase fw-bold small py-2">Filter</button>
                        <a href="{{ route('admin.surat-mandiri.index') }}" class="btn btn-outline-dark rounded-0 px-3 py-2 uppercase fw-bold small">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABEL ANTREAN SURAT (STAY-ON-PAGE WORKFLOW) --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Antrean Permohonan Surat Mahasiswa
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 small">
                    <thead class="bg-light text-dark uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 10%;">TANGGAL</th>
                            <th class="text-start" style="width: 20%;">MAHASISWA PEMOHON</th>
                            <th class="text-start" style="width: 26%;">JENIS SURAT & KEPERLUAN</th>
                            <th style="width: 16%;">NO. SURAT & CATATAN</th>
                            <th style="width: 14%;">STATUS</th>
                            <th style="width: 14%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengajuans as $item)
                            <tr>
                                <td class="text-center font-monospace">{{ $item->created_at->format('d/m/Y') }}</td>
                                <td class="ps-3">
                                    <div class="fw-bold text-dark uppercase">{{ $item->mahasiswa->nama_lengkap ?? '-' }}</div>
                                    <span class="text-primary font-monospace fw-bold" style="font-size: 11px;">{{ $item->mahasiswa->nim ?? '-' }}</span><br>
                                    <span class="text-muted font-monospace" style="font-size: 10px;">{{ optional(optional($item->mahasiswa)->programStudi)->nama_prodi ?? '-' }}</span>
                                </td>
                                <td class="ps-3">
                                    <span class="badge bg-dark text-white rounded-0 font-monospace mb-1" style="font-size: 10px;">{{ strtoupper($item->jenis_surat) }}</span>
                                    <div class="fw-bold text-dark uppercase">{{ $item->keperluan }}</div>
                                    @if($item->tujuan_instansi)
                                        <div class="text-muted font-monospace" style="font-size: 10px;">TUJUAN: {{ $item->tujuan_instansi }}</div>
                                    @endif
                                </td>
                                <td class="p-2">
                                    <form action="{{ route('admin.surat-mandiri.updateStatus', $item->id) }}" method="POST" id="form-surat-{{ $item->id }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="text" name="nomor_surat" class="form-control form-control-sm rounded-0 font-monospace mb-1" value="{{ $item->nomor_surat }}" placeholder="No. Urut (Mis: 042)">
                                        <input type="text" name="catatan_admin" class="form-control form-control-sm rounded-0" value="{{ $item->catatan_admin }}" placeholder="Catatan BAAK...">
                                </td>
                                <td class="p-2 text-center">
                                        <select name="status" class="form-select form-select-sm rounded-0 font-monospace fw-bold">
                                            <option value="Menunggu Verifikasi" {{ $item->status == 'Menunggu Verifikasi' ? 'selected' : '' }}>MENUNGGU</option>
                                            <option value="Disetujui" {{ $item->status == 'Disetujui' ? 'selected' : '' }}>SETUJUI & TERBITKAN</option>
                                            <option value="Ditolak" {{ $item->status == 'Ditolak' ? 'selected' : '' }}>TOLAK</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column gap-1 px-2">
                                        <button type="submit" form="form-surat-{{ $item->id }}" class="btn btn-sm btn-primary rounded-0 py-1 uppercase fw-bold" style="font-size: 10px;">
                                            <i class="bi bi-save me-1"></i> Simpan
                                        </button>
                                        @if($item->status == 'Disetujui')
                                            <a href="{{ route('surat-mandiri.cetak', $item->id) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-0 py-1 uppercase fw-bold" style="font-size: 10px;">
                                                <i class="bi bi-printer me-1"></i> Cetak PDF
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted uppercase fw-bold">Belum ada permohonan surat dari mahasiswa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($pengajuans->hasPages())
            <div class="card-footer bg-white border-top py-3 rounded-0">
                <div class="d-flex justify-content-center">{{ $pengajuans->links() }}</div>
            </div>
        @endif
    </div>
</div>
@endsection
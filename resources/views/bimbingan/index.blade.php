@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- HEADER UTAMA --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 mt-3 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Bimbingan Tugas Akhir (Skripsi) & PPL / Vikariat</h3>
            <span class="text-muted small uppercase">Manajemen Proposal, Penetapan Dosen Pembimbing, & Logbook Konsultasi Digital</span>
        </div>
        @if(Auth::user()->hasRole('mahasiswa'))
            <div>
                <button type="button" class="btn btn-sm btn-primary rounded-0 px-4 py-2 uppercase fw-bold small shadow-sm" data-bs-toggle="modal" data-bs-target="#ajukanBimbinganModal">
                    <i class="bi bi-plus-lg me-1"></i> Ajukan Judul Skripsi / PPL Baru
                </button>
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(!Auth::user()->hasRole('mahasiswa'))
        {{-- FILTER PENCARIAN UNTUK DOSEN, KAPRODI, & ADMIN --}}
        <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-light">
            <div class="card-body p-4">
                <form action="{{ route('bimbingan.index') }}" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold uppercase text-dark mb-1">Cari Mahasiswa / Judul Skripsi</label>
                            <input type="text" name="search" class="form-control rounded-0 font-monospace uppercase" placeholder="KETIK NAMA / NIM / JUDUL..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold uppercase text-dark mb-1">Jenis Bimbingan</label>
                            <select name="jenis" class="form-select rounded-0 uppercase small fw-bold">
                                <option value="">-- SEMUA JENIS --</option>
                                <option value="Skripsi / Tugas Akhir" {{ request('jenis') == 'Skripsi / Tugas Akhir' ? 'selected' : '' }}>Skripsi / Tugas Akhir</option>
                                <option value="PPL / Vikariat" {{ request('jenis') == 'PPL / Vikariat' ? 'selected' : '' }}>PPL / Vikariat</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold uppercase text-dark mb-1">Status</label>
                            <select name="status" class="form-select rounded-0 uppercase small fw-bold">
                                <option value="">-- SEMUA --</option>
                                @foreach(['Diajukan', 'Disetujui / Aktif Bimbingan', 'Siap Sidang', 'Selesai / Lulus', 'Revisi Judul'] as $st)
                                    <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-1">
                            <button type="submit" class="btn btn-primary rounded-0 flex-grow-1 uppercase fw-bold small py-2">Filter</button>
                            <a href="{{ route('bimbingan.index') }}" class="btn btn-outline-dark rounded-0 px-3 py-2"><i class="bi bi-arrow-counterclockwise"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- TABEL DAFTAR BIMBINGAN --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Portofolio Bimbingan Skripsi & PPL Mahasiswa
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 small">
                    <thead class="bg-light text-dark uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 5%;">NO</th>
                            <th class="text-start" style="width: 20%;">MAHASISWA</th>
                            <th style="width: 12%;">JENIS</th>
                            <th class="text-start" style="width: 28%;">JUDUL SKRIPSI / LOKASI PPL</th>
                            <th style="width: 16%;">DOSEN PEMBIMBING</th>
                            <th style="width: 9%;">LOGBOOK</th>
                            <th style="width: 10%;">STATUS & AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bimbingans as $index => $item)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted">
                                    {{ method_exists($bimbingans, 'firstItem') ? $bimbingans->firstItem() + $index : $index + 1 }}
                                </td>
                                <td class="ps-3">
                                    <div class="fw-bold text-dark uppercase">{{ $item->mahasiswa->nama_lengkap ?? '-' }}</div>
                                    <span class="text-primary font-monospace fw-bold" style="font-size: 11px;">{{ $item->mahasiswa->nim ?? '-' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-dark text-white rounded-0 font-monospace" style="font-size: 10px;">{{ strtoupper($item->jenis_bimbingan) }}</span>
                                </td>
                                <td class="ps-3">
                                    <div class="fw-bold text-dark uppercase">{{ $item->judul }}</div>
                                    @if($item->lokasi_pelayanan)
                                        <span class="text-muted font-monospace" style="font-size: 10px;"><i class="bi bi-geo-alt-fill me-1"></i>LOKASI: {{ $item->lokasi_pelayanan }}</span>
                                    @endif
                                </td>
                                <td class="ps-2 font-monospace" style="font-size: 11px;">
                                    <div><strong>P1:</strong> {{ optional($item->pembimbing1)->nama_lengkap ?? 'BELUM DIPLOT' }}</div>
                                    @if($item->pembimbing2)
                                        <div><strong>P2:</strong> {{ $item->pembimbing2->nama_lengkap }}</div>
                                    @endif
                                </td>
                                <td class="text-center font-monospace fw-bold">
                                    {{ $item->logs->count() }} SESI
                                </td>
                                <td class="text-center">
                                    @php
                                        $badgeColor = 'bg-secondary';
                                        if ($item->status == 'Disetujui / Aktif Bimbingan') $badgeColor = 'bg-primary';
                                        elseif ($item->status == 'Siap Sidang') $badgeColor = 'bg-warning text-dark';
                                        elseif ($item->status == 'Selesai / Lulus') $badgeColor = 'bg-success';
                                        elseif ($item->status == 'Revisi Judul') $badgeColor = 'bg-danger';
                                    @endphp
                                    <span class="badge {{ $badgeColor }} rounded-0 font-monospace d-block mb-1" style="font-size: 9px;">{{ strtoupper($item->status) }}</span>
                                    <a href="{{ route('bimbingan.show', $item->id) }}" class="btn btn-sm btn-outline-dark rounded-0 py-0 px-2 uppercase fw-bold" style="font-size: 10px;">
                                        Buka Logbook
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted uppercase fw-bold">Belum ada data pengajuan bimbingan Tugas Akhir atau PPL.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(method_exists($bimbingans, 'hasPages') && $bimbingans->hasPages())
            <div class="card-footer bg-white border-top py-3 rounded-0">
                <div class="d-flex justify-content-center">
                    {{ $bimbingans->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

@if(Auth::user()->hasRole('mahasiswa'))
<div class="modal fade" id="ajukanBimbinganModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-0 border-0 border-top border-dark border-4">
            <form action="{{ route('bimbingan.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-dark text-white rounded-0 py-3">
                    <h6 class="modal-title fw-bold uppercase small">Formulir Pengajuan Judul Skripsi / Tugas Akhir & PPL</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold uppercase text-dark">Jenis Program <span class="text-danger">*</span></label>
                            <select name="jenis_bimbingan" class="form-select rounded-0 uppercase small fw-bold" required>
                                <option value="Skripsi / Tugas Akhir">Skripsi / Tugas Akhir</option>
                                <option value="PPL / Vikariat">Praktik Pelayanan Lapangan (PPL / Vikariat)</option>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label small fw-bold uppercase text-dark">Lokasi Gereja / Sekolah / Instansi (Khusus PPL/Riset)</label>
                            <input type="text" name="lokasi_pelayanan" class="form-control rounded-0 uppercase" placeholder="Contoh: Jemaat GPI Papua Eden Fakfak">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold uppercase text-dark">Judul Skripsi / Laporan PPL yang Diajukan <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control rounded-0" placeholder="Ketik judul lengkap rencana penelitian atau laporan pelayanan..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold uppercase text-dark">Usulan Dosen Pembimbing 1</label>
                            <select name="pembimbing_1_id" class="form-select rounded-0 uppercase small fw-bold">
                                <option value="">-- PILIH USULAN PEMBIMBING 1 --</option>
                                @foreach($dosens as $d)
                                    <option value="{{ $d->id }}">{{ $d->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold uppercase text-dark">Usulan Dosen Pembimbing 2 (Opsional)</label>
                            <select name="pembimbing_2_id" class="form-select rounded-0 uppercase small fw-bold">
                                <option value="">-- PILIH USULAN PEMBIMBING 2 --</option>
                                @foreach($dosens as $d)
                                    <option value="{{ $d->id }}">{{ $d->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold uppercase text-dark">Ringkasan Latar Belakang / Fokus Kajian</label>
                            <textarea name="abstrak_ringkas" rows="3" class="form-control rounded-0" placeholder="Jelaskan secara singkat latar belakang dan tujuan penelitian/pelayanan Anda..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top rounded-0">
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-0 uppercase fw-bold small" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">Kirim Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
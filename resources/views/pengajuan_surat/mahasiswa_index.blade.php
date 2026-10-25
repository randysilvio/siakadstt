@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 mt-3 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Permohonan Surat Akademik</h3>
            <span class="text-muted small uppercase">Pengajuan Surat Keterangan Aktif, Izin Penelitian, & Pengantar PPL</span>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-primary rounded-0 px-4 py-2 uppercase fw-bold small" data-bs-toggle="modal" data-bs-target="#modalAjukanSurat">
                Ajukan Surat
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold">
            {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold">
            {{ session('error') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Riwayat Permohonan Surat
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 small">
                    <thead class="bg-light text-dark uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 6%;">NO</th>
                            <th style="width: 14%;">TANGGAL</th>
                            <th class="text-start" style="width: 24%;">JENIS SURAT</th>
                            <th class="text-start" style="width: 28%;">KEPERLUAN & TUJUAN</th>
                            <th style="width: 14%;">STATUS</th>
                            <th style="width: 14%;">DOKUMEN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengajuans as $index => $item)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted">{{ $pengajuans->firstItem() + $index }}</td>
                                <td class="text-center font-monospace">{{ $item->created_at->format('d/m/Y') }}</td>
                                <td class="ps-3">
                                    <span class="fw-bold text-dark uppercase d-block">{{ $item->jenis_surat }}</span>
                                    @if($item->nomor_surat)
                                        <span class="text-muted font-monospace" style="font-size: 10px;">NO: {{ $item->nomor_surat }}/STT-GPI/AK/{{ $item->created_at->format('Y') }}</span>
                                    @endif
                                </td>
                                <td class="ps-3">
                                    <div class="text-dark uppercase fw-bold">{{ $item->keperluan }}</div>
                                    @if($item->tujuan_instansi)
                                        <span class="text-muted font-monospace" style="font-size: 10px;">TUJUAN: {{ $item->tujuan_instansi }}</span>
                                    @endif
                                    @if($item->catatan_admin)
                                        <div class="text-danger font-monospace mt-1" style="font-size: 10px;">CATATAN: {{ $item->catatan_admin }}</div>
                                    @endif
                                </td>
                                <td class="text-center font-monospace fw-bold uppercase">{{ $item->status }}</td>
                                <td class="text-center">
                                    @if($item->status == 'Disetujui')
                                        <a href="{{ route('surat-mandiri.cetak', $item->id) }}" target="_blank" class="btn btn-sm btn-dark rounded-0 py-1 px-3 uppercase fw-bold" style="font-size: 10px;">
                                            Cetak PDF
                                        </a>
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 10px;">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted uppercase fw-bold">Belum ada permohonan surat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAjukanSurat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-0 border-0 border-top border-dark border-4">
            <form action="{{ route('surat-mandiri.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-dark text-white rounded-0 py-3">
                    <h6 class="modal-title fw-bold uppercase small">Permohonan Surat</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold uppercase text-dark">Jenis Surat <span class="text-danger">*</span></label>
                        <select name="jenis_surat" class="form-select rounded-0 uppercase small fw-bold" required>
                            <option value="Surat Keterangan Aktif Kuliah">Surat Keterangan Aktif Kuliah</option>
                            <option value="Surat Izin Penelitian Tugas Akhir / Skripsi">Surat Izin Penelitian Tugas Akhir / Skripsi</option>
                            <option value="Surat Pengantar Praktik Pelayanan (PPL / Vikariat)">Surat Pengantar Praktik Pelayanan (PPL / Vikariat)</option>
                            <option value="Surat Permohonan Cuti Akademik">Surat Permohonan Cuti Akademik</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold uppercase text-dark">Instansi Tujuan (Opsional)</label>
                        <input type="text" name="tujuan_instansi" class="form-control rounded-0 uppercase">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold uppercase text-dark">Keperluan <span class="text-danger">*</span></label>
                        <textarea name="keperluan" rows="3" class="form-control rounded-0 uppercase" required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top rounded-0">
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-0 uppercase fw-bold small" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small">Kirim</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- Header & Navigasi --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Evaluasi & Rekapitulasi Tugas</h3>
            <span class="text-muted small uppercase">Penilaian Lembar Kerja Mahasiswa Terintegrasi Buku Nilai OBE</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('nilai.show', $verum_tuga->kelas->mata_kuliah_id) }}" class="btn btn-sm btn-dark rounded-0 px-3 uppercase fw-bold small">
                <i class="bi bi-journal-check me-1"></i> Buka Buku Nilai MK
            </a>
            <a href="{{ route('verum.show', $verum_tuga->kelas_id) }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Kelas
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Info Tugas --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-white">
        <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h5 class="fw-bold text-dark uppercase mb-1">{{ $verum_tuga->judul }}</h5>
                <div class="text-muted small uppercase font-monospace">
                    <i class="bi bi-book-half me-1"></i> KELAS: {{ $verum_tuga->kelas->nama_kelas }} | MATA KULIAH: {{ $verum_tuga->kelas->mataKuliah->nama_mk }}
                </div>
            </div>
            <div class="text-md-end">
                <span class="text-muted small font-monospace uppercase d-block">TENGGAT WAKTU PENGUMPULAN</span>
                <span class="badge bg-danger text-white rounded-0 font-monospace px-3 py-2">
                    <i class="bi bi-clock-history me-1"></i> {{ $verum_tuga->tenggat_waktu->format('d M Y, H:i') }} WIT
                </span>
            </div>
        </div>
    </div>

    {{-- Form & Tabel Rekapitulasi Penilaian --}}
    <form action="{{ route('verum.tugas.nilai', $verum_tuga->id) }}" method="POST">
        @csrf
        <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 bg-white mb-4">
            <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 uppercase small"><i class="bi bi-people-fill me-2"></i>Daftar Pengumpulan & Penilaian Tugas Mahasiswa</h6>
                @if($mahasiswas->isNotEmpty())
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm">
                        <i class="bi bi-save me-1"></i> Simpan Semua Nilai Tugas
                    </button>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 text-dark small uppercase">
                        <thead class="bg-light font-monospace text-center fw-bold" style="font-size: 11px;">
                            <tr>
                                <th style="width: 5%;">NO</th>
                                <th style="width: 12%;">NIM</th>
                                <th class="text-start" style="width: 24%;">NAMA MAHASISWA</th>
                                <th style="width: 12%;">STATUS BERKAS</th>
                                <th style="width: 15%;">DOKUMEN JAWABAN</th>
                                <th style="width: 12%;">NILAI (0-100)</th>
                                <th style="width: 20%;">CATATAN / FEEDBACK DOSEN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mahasiswas as $index => $mhs)
                                @php
                                    $kumpul = $pengumpulan->get($mhs->id);
                                @endphp
                                <tr>
                                    <td class="text-center font-monospace fw-bold text-muted">{{ $index + 1 }}</td>
                                    <td class="text-center font-monospace fw-bold text-primary">{{ $mhs->nim }}</td>
                                    <td class="fw-bold ps-3">{{ $mhs->nama_lengkap }}</td>
                                    <td class="text-center">
                                        @if($kumpul && !empty($kumpul->file_path))
                                            <span class="badge bg-success rounded-0 px-2 py-1 font-monospace" style="font-size: 10px;">DISERAHKAN</span>
                                            <div class="font-monospace text-muted mt-1" style="font-size: 10px;">
                                                {{ $kumpul->waktu_pengumpulan->format('d/m/Y H:i') }}
                                            </div>
                                        @else
                                            <span class="badge bg-secondary rounded-0 px-2 py-1 font-monospace" style="font-size: 10px;">BELUM UNGGAH</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($kumpul && !empty($kumpul->file_path))
                                            <div class="btn-group rounded-0" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2 fw-bold uppercase" onclick="lihatPDF('{{ route('berkas.unduh', ['jenis' => 'jawaban-tugas', 'id' => $kumpul->id]) }}', '{{ addslashes($mhs->nama_lengkap) }}')" style="font-size: 10px;">
                                                    <i class="bi bi-eye-fill me-1"></i>Lihat
                                                </button>
                                                <a href="{{ route('berkas.unduh', ['jenis' => 'jawaban-tugas', 'id' => $kumpul->id]) }}" target="_blank" class="btn btn-sm btn-primary rounded-0 py-1 px-2 fw-bold uppercase" style="font-size: 10px;">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            </div>
                                        @else
                                            <span class="text-muted font-monospace">-</span>
                                        @endif
                                    </td>
                                    <td class="px-2">
                                        <input type="number" step="0.01" min="0" max="100" name="nilai[{{ $mhs->id }}]" value="{{ old('nilai.'.$mhs->id, $kumpul->nilai ?? '') }}" class="form-control form-control-sm rounded-0 text-center font-monospace fw-bold border-dark border-opacity-50" placeholder="0 - 100">
                                    </td>
                                    <td class="px-2">
                                        <input type="text" name="catatan_dosen[{{ $mhs->id }}]" value="{{ old('catatan_dosen.'.$mhs->id, $kumpul->catatan_dosen ?? '') }}" class="form-control form-control-sm rounded-0 small" placeholder="Catatan evaluasi...">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted font-monospace fw-bold">Belum ada mahasiswa dengan KRS Disetujui pada mata kuliah ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($mahasiswas->isNotEmpty())
                <div class="card-footer bg-light border-top py-3 rounded-0 text-end">
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 py-2 uppercase fw-bold small shadow-sm">
                        <i class="bi bi-save me-1"></i> Simpan Semua Nilai Tugas
                    </button>
                </div>
            @endif
        </div>
    </form>
</div>

{{-- MODAL PENAMPIL PDF --}}
<div class="modal fade" id="modalLihatPDF" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered rounded-0">
        <div class="modal-content rounded-0 border-dark border-2">
            <div class="modal-header bg-dark text-white rounded-0 py-2">
                <h6 class="modal-title uppercase fw-bold small text-white font-monospace" id="pdfModalTitle">Viewer Dokumen Tugas</h6>
                <button type="button" class="btn-close btn-close-white rounded-0" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-light">
                <iframe id="pdfViewer" src="" width="100%" height="600px" style="border: none;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    function lihatPDF(url, namaMahasiswa) {
        document.getElementById('pdfModalTitle').textContent = "TUGAS MILIK: " + namaMahasiswa;
        document.getElementById('pdfViewer').src = url;
        var modal = new bootstrap.Modal(document.getElementById('modalLihatPDF'));
        modal.show();
    }
</script>
@endsection
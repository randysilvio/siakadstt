@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- Tombol Kembali --}}
    <div class="mb-4 mt-3">
        <a href="{{ route('verum.show', $verum_tuga->kelas_id) }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Kelas
        </a>
    </div>

    {{-- Info Tugas --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-white">
        <div class="card-body p-4">
            <h5 class="fw-bold text-dark uppercase mb-2">Rekapitulasi: {{ $verum_tuga->judul }}</h5>
            <div class="text-muted small uppercase font-monospace mb-2">
                <i class="bi bi-book-half me-1"></i> KELAS: {{ $verum_tuga->kelas->nama_kelas }} | MK: {{ $verum_tuga->kelas->mataKuliah->nama_mk }}
            </div>
            <div class="text-dark small uppercase font-monospace fw-bold">
                <i class="bi bi-clock-history me-1"></i> TENGGAT WAKTU: <span class="text-danger">{{ $verum_tuga->tenggat_waktu->format('d F Y, H:i') }} WIT</span>
            </div>
        </div>
    </div>

    {{-- Tabel Rekapitulasi --}}
    <div class="card border border-dark border-opacity-25 rounded-0 shadow-none bg-white mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3">
            <h6 class="fw-bold mb-0 uppercase small"><i class="bi bi-people-fill me-2"></i>Daftar Pengumpulan Mahasiswa</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0 text-dark small uppercase">
                    <thead class="table-light font-monospace" style="font-size: 11px;">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th width="15%">NIM</th>
                            <th width="30%">Nama Mahasiswa</th>
                            <th class="text-center" width="15%">Status</th>
                            <th class="text-center" width="15%">Waktu Kumpul</th>
                            <th class="text-center" width="20%">Aksi Dokumen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mahasiswas as $index => $mhs)
                            @php
                                $kumpul = $pengumpulan->get($mhs->id);
                            @endphp
                            <tr>
                                <td class="text-center fw-bold">{{ $index + 1 }}</td>
                                <td class="font-monospace">{{ $mhs->nim }}</td>
                                <td class="fw-bold">{{ $mhs->nama_lengkap }}</td>
                                <td class="text-center">
                                    @if($kumpul)
                                        <span class="badge bg-success rounded-0 px-2 py-1"><i class="bi bi-check-lg"></i> Diserahkan</span>
                                    @else
                                        <span class="badge bg-danger rounded-0 px-2 py-1"><i class="bi bi-x-lg"></i> Belum</span>
                                    @endif
                                </td>
                                <td class="text-center font-monospace" style="font-size: 11px;">
                                    @if($kumpul)
                                        {{ $kumpul->waktu_pengumpulan->format('d/m/Y H:i') }}
                                        @if($kumpul->waktu_pengumpulan > $verum_tuga->tenggat_waktu)
                                            <div class="text-danger mt-1 blink">Terlambat</div>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($kumpul && $kumpul->file_path)
                                        <div class="btn-group" role="group">
                                            {{-- [BARU] Tombol Lihat PDF --}}
                                            <button type="button" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2 fw-bold" onclick="lihatPDF('{{ Storage::url($kumpul->file_path) }}', '{{ $mhs->nama_lengkap }}')" style="font-size: 10px;" title="Lihat Dokumen">
                                                <i class="bi bi-eye-fill"></i> Lihat
                                            </button>
                                            
                                            {{-- Tombol Unduh PDF --}}
                                            <a href="{{ Storage::url($kumpul->file_path) }}" target="_blank" class="btn btn-sm btn-primary rounded-0 py-1 px-2 fw-bold border-0" style="font-size: 10px;" title="Unduh Dokumen">
                                                <i class="bi bi-download"></i> Unduh
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted font-monospace">Belum ada mahasiswa yang mengambil mata kuliah ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- [BARU] MODAL PENAMPIL PDF --}}
<div class="modal fade" id="modalLihatPDF" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered rounded-0">
        <div class="modal-content rounded-0 border-dark border-2">
            <div class="modal-header bg-dark text-white rounded-0 py-2">
                <h6 class="modal-title uppercase fw-bold small text-white font-monospace" id="pdfModalTitle">Viewer Dokumen Tugas</h6>
                <button type="button" class="btn-close btn-close-white rounded-0" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-light">
                {{-- iFrame ini akan menampilkan PDF --}}
                <iframe id="pdfViewer" src="" width="100%" height="600px" style="border: none;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    // Fungsi untuk memicu modal dan mengganti sumber PDF
    function lihatPDF(url, namaMahasiswa) {
        document.getElementById('pdfModalTitle').innerText = "TUGAS MILIK: " + namaMahasiswa;
        document.getElementById('pdfViewer').src = url;
        
        var modal = new bootstrap.Modal(document.getElementById('modalLihatPDF'));
        modal.show();
    }
</script>

<style>
    @keyframes blink { 50% { opacity: 0; } }
    .blink { animation: blink 1.5s linear infinite; }
</style>
@endsection
@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.pmb.index') }}" class="btn btn-outline-dark btn-sm rounded-1 me-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <div>
            <h4 class="mb-0 fw-bold text-dark">Panel Verifikasi Pendaftar</h4>
            <p class="text-muted small mb-0">Tinjauan dokumen akademik dan proses persetujuan admisi</p>
        </div>
    </div>

    <div class="row g-4">
        {{-- PANEL KIRI: DATA LENGKAP --}}
        <div class="col-lg-8">
            
            {{-- Header Profil --}}
            <div class="card border-0 shadow-sm mb-4 bg-dark text-white">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-white text-dark rounded-1 d-flex align-items-center justify-content-center fw-bold fs-2 me-4" style="width: 80px; height: 80px;">
                            {{ substr($camaba->user->name, 0, 1) }}
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="fw-bold mb-1">{{ $camaba->user->name }}</h3>
                            <div class="d-flex flex-wrap gap-3 text-white-50 small mb-3 font-monospace">
                                <span><i class="bi bi-envelope me-1"></i> {{ $camaba->user->email }}</span>
                                <span><i class="bi bi-telephone me-1"></i> {{ $camaba->no_hp }}</span>
                            </div>
                            <div class="d-flex gap-2">
                                <span class="badge bg-light text-dark rounded-1 px-2"><i class="bi bi-upc-scan me-1"></i> {{ $camaba->no_pendaftaran }}</span>
                                <span class="badge bg-light text-dark rounded-1 px-2"><i class="bi bi-calendar-check me-1"></i> {{ $camaba->period->nama_gelombang ?? 'Tanpa Gelombang' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabel Informasi Administratif --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">Rincian Data Administratif</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <tbody>
                                <tr class="table-light"><td colspan="2" class="fw-bold text-muted small px-4 py-2">IDENTITAS PERSONAL</td></tr>
                                <tr>
                                    <td width="35%" class="px-4 text-muted small">Tempat, Tanggal Lahir</td>
                                    <td class="fw-semibold text-dark">{{ $camaba->tempat_lahir ?? '-' }}, {{ $camaba->tanggal_lahir ? \Carbon\Carbon::parse($camaba->tanggal_lahir)->translatedFormat('d F Y') : '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 text-muted small">Jenis Kelamin</td>
                                    <td class="text-dark">{{ $camaba->jenis_kelamin == 'L' ? 'Laki-laki' : ($camaba->jenis_kelamin == 'P' ? 'Perempuan' : '-') }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 text-muted small">Agama / Kepercayaan</td>
                                    <td class="text-dark">{{ $camaba->agama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 text-muted small">Alamat Lengkap</td>
                                    <td class="text-dark">{{ $camaba->alamat ?? '-' }}</td>
                                </tr>
                                
                                <tr class="table-light"><td colspan="2" class="fw-bold text-muted small px-4 py-2">HISTORI AKADEMIK</td></tr>
                                <tr>
                                    <td class="px-4 text-muted small">Institusi Pendidikan Asal</td>
                                    <td class="fw-semibold text-dark">{{ $camaba->sekolah_asal ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 text-muted small">Nomor Induk Siswa Nasional (NISN)</td>
                                    <td class="font-monospace text-dark">{{ $camaba->nisn ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 text-muted small">Tahun Kelulusan</td>
                                    <td class="text-dark">{{ $camaba->tahun_lulus ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 text-muted small">Nilai Ekuivalen Rapor</td>
                                    <td><span class="badge bg-dark rounded-1 fs-6">{{ $camaba->nilai_rata_rata_rapor ?? '0.00' }}</span></td>
                                </tr>

                                <tr class="table-light"><td colspan="2" class="fw-bold text-muted small px-4 py-2">MINAT PROGRAM STUDI</td></tr>
                                <tr>
                                    <td class="px-4 text-muted small">Prioritas Pertama</td>
                                    <td class="fw-bold text-dark">{{ $camaba->prodi1->nama_prodi ?? '-' }} <span class="text-muted fw-normal">({{ $camaba->prodi1->jenjang ?? '' }})</span></td>
                                </tr>
                                <tr>
                                    <td class="px-4 text-muted small">Prioritas Kedua (Alternatif)</td>
                                    <td class="text-dark">{{ $camaba->prodi2->nama_prodi ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Lampiran Dokumen --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">Arsip Dokumen Pendukung</h6>
                    <span class="badge bg-light text-dark border rounded-1">{{ $camaba->documents->count() }} Berkas Terlampir</span>
                </div>
                <div class="card-body">
                    @if($camaba->documents->isEmpty())
                        <div class="text-center py-5 text-muted">
                            Kandidat belum mengunggah dokumen administrasi ke dalam sistem.
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach($camaba->documents as $doc)
                                <div class="col-md-6">
                                    <div class="border rounded-1 p-3 d-flex align-items-center bg-light">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <h6 class="mb-1 text-dark text-truncate fw-bold">{{ strtoupper($doc->jenis_dokumen) }}</h6>
                                            <p class="mb-0 small font-monospace text-muted">Log: {{ $doc->created_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                        <a href="{{ Storage::url($doc->path_file) }}" target="_blank" class="btn btn-sm btn-dark rounded-1 ms-3 px-3">
                                            Tinjau
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- PANEL KANAN: KONTROL KEPUTUSAN --}}
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 20px; z-index: 10;">
                
                {{-- Status Board --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body text-center p-4">
                        <span class="text-muted small fw-bold mb-2 d-block">STATUS VERIFIKASI SAAT INI</span>
                        
                        @if($camaba->status_pendaftaran == 'lulus')
                            <h3 class="fw-bold text-success mb-2">DITERIMA</h3>
                            <p class="text-muted small mb-0">Telah diregistrasi sebagai entitas mahasiswa aktif institusi.</p>

                        @elseif($camaba->status_pendaftaran == 'tidak_lulus')
                            <h3 class="fw-bold text-danger mb-2">DITOLAK</h3>
                            <p class="text-muted small mb-0">Kandidat tidak lolos kualifikasi akademik/administrasi.</p>

                        @else
                            @if($tagihan && $tagihan->status == 'lunas')
                                <h3 class="fw-bold text-primary mb-2">DALAM PROSES</h3>
                                <p class="text-muted small mb-0">Administrasi keuangan terselesaikan. Menunggu persetujuan admisi akhir.</p>
                            @else
                                <h3 class="fw-bold text-warning text-dark mb-2">TERTUNDA</h3>
                                <p class="text-muted small mb-0">Menunggu pelunasan kewajiban biaya registrasi pendaftaran.</p>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- Modul Pengambilan Keputusan --}}
                @if($camaba->status_pendaftaran != 'lulus' && $camaba->status_pendaftaran != 'tidak_lulus')
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-dark text-white py-3">
                        <h6 class="fw-bold mb-0">Otoritas Keputusan</h6>
                    </div>
                    <div class="card-body p-4">

                        {{-- Tahap 1: Keuangan --}}
                        @if($tagihan && $tagihan->status != 'lunas')
                            <span class="d-block text-muted small fw-bold mb-2">LANGKAH 1: VALIDASI KEUANGAN</span>
                            
                            @if($tagihan->bukti_bayar)
                                <div class="alert bg-light border text-dark small rounded-1 mb-3">
                                    Dokumen bukti transfer telah dilampirkan oleh kandidat.
                                    <div class="mt-2 text-center">
                                        <a href="{{ Storage::url($tagihan->bukti_bayar) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-1 w-100">Buka Lampiran Bukti</a>
                                    </div>
                                </div>
                                <form action="{{ route('admin.pmb.payment.approve', $tagihan->id) }}" method="POST" onsubmit="return confirm('Pernyataan: Mengesahkan pelunasan ini akan membuka akses seleksi akademik kandidat. Lanjutkan?')">
                                    @csrf
                                    <button type="submit" class="btn btn-success rounded-1 w-100 fw-bold py-2">
                                        Sahkan Pembayaran (Lunas)
                                    </button>
                                </form>
                            @else
                                <div class="alert alert-secondary border-0 small text-center rounded-1 mb-0">
                                    Tertahan: Menunggu unggahan bukti pembayaran dari pendaftar.
                                </div>
                            @endif

                        {{-- Tahap 2: Admisi Akademik --}}
                        @else
                            <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success small rounded-1 mb-4 text-center fw-bold">
                                Tahap 1 Selesai: Pembayaran Lunas
                            </div>

                            <span class="d-block text-muted small fw-bold mb-2">LANGKAH 2: PENETAPAN HASIL SELEKSI</span>
                            
                            @if(!$camaba->pilihan_prodi_1_id)
                                <div class="alert alert-warning border-0 small rounded-1 text-center mb-0">
                                    Tertahan: Formulir biodata akademik (Program Studi) belum dilengkapi oleh kandidat.
                                </div>
                            @else
                                <form action="{{ route('admin.pmb.approve', $camaba->id) }}" method="POST" class="mb-3" onsubmit="return confirm('Sistem akan mendaftarkan pendaftar ini ke dalam entitas Mahasiswa Aktif dan menghasilkan Nomor Induk Mahasiswa (NIM). Proses ini final. Eksekusi sekarang?')">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-dark w-100 rounded-1 fw-bold py-2">
                                        Setujui Penerimaan (Terima)
                                    </button>
                                </form>

                                <button type="button" class="btn btn-outline-danger rounded-1 w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    Tolak Pendaftaran
                                </button>
                            @endif
                        @endif

                    </div>
                </div>
                @endif
                
            </div>
        </div>
    </div>
</div>

{{-- Modal Tolak Formal --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-1">
            <div class="modal-header border-bottom">
                <h6 class="modal-title fw-bold text-dark">Justifikasi Penolakan Admisi</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.pmb.reject', $camaba->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Tindakan ini akan mengakhiri proses pendaftaran kandidat <strong>{{ $camaba->user->name }}</strong>. Harap cantumkan alasan penolakan secara administratif.</p>
                    <label class="form-label text-dark fw-semibold small">Keterangan / Alasan Penolakan</label>
                    <textarea name="alasan" class="form-control rounded-1" rows="3" placeholder="Contoh: Dokumen ijazah terindikasi tidak valid..." required></textarea>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-outline-secondary rounded-1" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-1 px-4">Eksekusi Penolakan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
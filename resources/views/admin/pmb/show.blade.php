@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 fw-bold text-dark uppercase">Panel Verifikasi Pendaftar PMB <x-info-aturan kunci="pmb_nim" /></h3>
            <span class="text-muted small uppercase">Tinjauan Dokumen Akademik & Otorisasi Penerimaan Mahasiswa Baru</span>
        </div>
        <div class="d-flex gap-2">
            @if($camaba->tempat_lahir && $camaba->pilihan_prodi_1_id && $tagihan && $tagihan->status == 'lunas')
                <a href="{{ route('pmb.kartu-ujian.cetak', ['camaba_id' => $camaba->id]) }}" target="_blank" class="btn btn-dark btn-sm rounded-0 px-3 uppercase fw-bold small">
                    <i class="bi bi-printer-fill me-1"></i> Kartu Ujian
                </a>
            @endif
            <a href="{{ route('admin.pmb.index') }}" class="btn btn-outline-dark btn-sm rounded-0 px-3 uppercase fw-bold small">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    {{-- Blok Notifikasi --}}
    @if (session('error'))
        <div class="alert alert-danger border rounded-0 shadow-sm mb-4 p-3 d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>
            <div class="small uppercase fw-bold">{{ session('error') }}</div>
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success border rounded-0 shadow-sm mb-4 p-3 small uppercase fw-bold">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row g-4 mb-5">
        {{-- PANEL KIRI: DATA LENGKAP --}}
        <div class="col-lg-8">
            
            {{-- Header Profil --}}
            <div class="card border-0 shadow-sm rounded-0 mb-4 bg-dark text-white">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-white text-dark rounded-0 d-flex align-items-center justify-content-center fw-bold fs-2 me-4 font-monospace" style="width: 75px; height: 75px;">
                            {{ substr($camaba->user->name ?? 'C', 0, 1) }}
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="fw-bold mb-1 uppercase">{{ $camaba->user->name ?? 'NAMA TIDAK TERSEDIA' }}</h4>
                            <div class="d-flex flex-wrap gap-3 text-white-50 small mb-3 font-monospace">
                                <span><i class="bi bi-envelope me-1"></i> {{ $camaba->user->email ?? '-' }}</span>
                                <span><i class="bi bi-telephone me-1"></i> {{ $camaba->no_hp ?? '-' }}</span>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-light text-dark rounded-0 font-monospace px-2 py-1"><i class="bi bi-upc-scan me-1"></i> {{ $camaba->no_pendaftaran }}</span>
                                <span class="badge bg-secondary text-white rounded-0 font-monospace uppercase px-2 py-1"><i class="bi bi-calendar-check me-1"></i> {{ $camaba->period->nama_gelombang ?? 'TANPA GELOMBANG' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabel Informasi Administratif --}}
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom rounded-0">
                    <h6 class="fw-bold mb-0 text-dark uppercase small">Rincian Data Administratif</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 small">
                            <tbody>
                                <tr class="bg-light"><td colspan="2" class="fw-bold text-dark uppercase px-4 py-2">1. IDENTITAS PERSONAL</td></tr>
                                <tr>
                                    <td width="35%" class="px-4 text-muted uppercase fw-bold">Tempat, Tanggal Lahir</td>
                                    <td class="fw-bold text-dark uppercase">{{ $camaba->tempat_lahir ?? '-' }}, {{ $camaba->tanggal_lahir ? \Carbon\Carbon::parse($camaba->tanggal_lahir)->translatedFormat('d F Y') : '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 text-muted uppercase fw-bold">Jenis Kelamin</td>
                                    <td class="text-dark uppercase">{{ $camaba->jenis_kelamin == 'L' ? 'Laki-laki' : ($camaba->jenis_kelamin == 'P' ? 'Perempuan' : '-') }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 text-muted uppercase fw-bold">Agama / Kepercayaan</td>
                                    <td class="text-dark uppercase">{{ $camaba->agama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 text-muted uppercase fw-bold">Alamat Lengkap</td>
                                    <td class="text-dark uppercase">{{ $camaba->alamat ?? '-' }}</td>
                                </tr>
                                
                                <tr class="bg-light"><td colspan="2" class="fw-bold text-dark uppercase px-4 py-2">2. HISTORI AKADEMIK</td></tr>
                                <tr>
                                    <td class="px-4 text-muted uppercase fw-bold">Institusi Pendidikan Asal</td>
                                    <td class="fw-bold text-dark uppercase">{{ $camaba->sekolah_asal ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 text-muted uppercase fw-bold">Nomor Induk Siswa Nasional (NISN)</td>
                                    <td class="font-monospace fw-bold text-dark">{{ $camaba->nisn ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 text-muted uppercase fw-bold">Tahun Kelulusan</td>
                                    <td class="font-monospace text-dark">{{ $camaba->tahun_lulus ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 text-muted uppercase fw-bold">Nilai Rata-rata Rapor</td>
                                    <td><span class="badge bg-dark rounded-0 font-monospace fs-6">{{ $camaba->nilai_rata_rata_rapor ?? '0.00' }}</span></td>
                                </tr>

                                <tr class="bg-light"><td colspan="2" class="fw-bold text-dark uppercase px-4 py-2">3. MINAT PROGRAM STUDI</td></tr>
                                <tr>
                                    <td class="px-4 text-muted uppercase fw-bold">Prioritas Pertama</td>
                                    <td class="fw-bold text-primary uppercase">{{ $camaba->prodi1->nama_prodi ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 text-muted uppercase fw-bold">Prioritas Kedua (Alternatif)</td>
                                    <td class="text-dark uppercase">{{ $camaba->prodi2->nama_prodi ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Lampiran Dokumen --}}
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4">
                <div class="card-header bg-white py-3 border-bottom rounded-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark uppercase small">Arsip Dokumen Pendukung</h6>
                    <span class="badge bg-dark text-white rounded-0 font-monospace">{{ $camaba->documents->count() }} BERKAS</span>
                </div>
                <div class="card-body p-4">
                    @if($camaba->documents->isEmpty())
                        <div class="text-center py-4 text-muted small uppercase fw-bold">
                            <i class="bi bi-folder-x fs-2 d-block mb-2 text-secondary opacity-50"></i>
                            Kandidat belum mengunggah dokumen administrasi ke dalam sistem.
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach($camaba->documents as $doc)
                                <div class="col-md-6">
                                    <div class="border rounded-0 p-3 d-flex align-items-center bg-light">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <h6 class="mb-1 text-dark text-truncate fw-bold small uppercase">{{ $doc->jenis_dokumen }}</h6>
                                            <p class="mb-0 small font-monospace text-muted" style="font-size: 11px;">Diunggah: {{ $doc->created_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                        <a href="{{ route('berkas.unduh', ['jenis' => 'dokumen-pmb', 'id' => $doc->id]) }}" target="_blank" class="btn btn-sm btn-dark rounded-0 ms-3 px-3 uppercase fw-bold small">
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
            <div class="sticky-top" style="top: 80px; z-index: 10;">
                
                {{-- Status Board --}}
                <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4">
                    <div class="card-body text-center p-4">
                        <span class="text-muted small fw-bold uppercase mb-2 d-block font-monospace">STATUS VERIFIKASI SAAT INI</span>
                        
                        @if($camaba->status_pendaftaran == 'lulus')
                            <span class="badge bg-success text-white rounded-0 px-3 py-2 fs-6 uppercase fw-bold mb-2">DITERIMA / LULUS</span>
                            <p class="text-muted small mb-0 uppercase">Telah diregistrasi sebagai mahasiswa aktif institusi.</p>
                        @elseif($camaba->status_pendaftaran == 'tidak_lulus')
                            <span class="badge bg-danger text-white rounded-0 px-3 py-2 fs-6 uppercase fw-bold mb-2">DITOLAK</span>
                            <p class="text-muted small mb-0 uppercase">Kandidat tidak lolos kualifikasi seleksi.</p>
                            @if($camaba->catatan_seleksi)
                                <p class="text-dark small mt-2 mb-0 uppercase">Alasan: {{ $camaba->catatan_seleksi }}</p>
                            @endif
                        @else
                            @if($tagihan && $tagihan->status == 'lunas')
                                <span class="badge bg-primary text-white rounded-0 px-3 py-2 fs-6 uppercase fw-bold mb-2">MENUNGGU SELEKSI</span>
                                <p class="text-muted small mb-0 uppercase">Biaya formulir lunas. Menunggu persetujuan admisi akhir.</p>
                            @else
                                <span class="badge bg-warning text-dark rounded-0 px-3 py-2 fs-6 uppercase fw-bold mb-2">MENUNGGU PEMBAYARAN</span>
                                <p class="text-muted small mb-0 uppercase">Menunggu pelunasan biaya formulir pendaftaran.</p>
                            @endif
                        @endif

                        {{-- Jejak audit keputusan --}}
                        @if($camaba->pemutus || ($tagihan && $tagihan->verifikator))
                            <div class="border-top mt-3 pt-3 text-start font-monospace text-muted" style="font-size: 10px;">
                                @if($tagihan && $tagihan->verifikator)
                                    <div class="uppercase">BAYAR DIVERIFIKASI: {{ $tagihan->verifikator->name }} — {{ optional($tagihan->diverifikasi_pada)->translatedFormat('d M Y H:i') }}</div>
                                @endif
                                @if($camaba->pemutus)
                                    <div class="uppercase">KEPUTUSAN SELEKSI: {{ $camaba->pemutus->name }} — {{ optional($camaba->diputuskan_pada)->translatedFormat('d M Y H:i') }}</div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Modul Pengambilan Keputusan --}}
                @if($camaba->status_pendaftaran != 'lulus' && $camaba->status_pendaftaran != 'tidak_lulus')
                <div class="card border-0 shadow-sm rounded-0">
                    <div class="card-header bg-dark text-white rounded-0 py-3">
                        <h6 class="fw-bold mb-0 uppercase small">Otoritas Keputusan</h6>
                    </div>
                    <div class="card-body p-4">

                        {{-- Tahap 1: Keuangan --}}
                        @if($tagihan && $tagihan->status != 'lunas')
                            <span class="d-block text-dark small fw-bold uppercase mb-2">LANGKAH 1: VALIDASI KEUANGAN</span>
                            
                            @if($tagihan->bukti_bayar)
                                <div class="alert bg-light border text-dark small rounded-0 mb-3">
                                    <span class="uppercase small d-block mb-2">Bukti transfer telah dilampirkan oleh kandidat:</span>
                                    <a href="{{ route('berkas.unduh', ['jenis' => 'bukti-bayar', 'id' => $tagihan->id]) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-0 w-100 uppercase fw-bold small">
                                        <i class="bi bi-image me-1"></i> Buka Lampiran Bukti
                                    </a>
                                </div>
                                <form action="{{ route('admin.pmb.payment.approve', $tagihan->id) }}" method="POST" onsubmit="return confirm('Sahkan pelunasan biaya formulir pendaftar ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-success rounded-0 w-100 uppercase fw-bold small py-2 shadow-sm">
                                        <i class="bi bi-check-lg me-1"></i> Sahkan Pembayaran (Lunas)
                                    </button>
                                </form>
                            @else
                                <div class="alert alert-secondary border-0 small text-center rounded-0 mb-0 uppercase">
                                    Tertahan: Menunggu unggahan bukti pembayaran dari pendaftar.
                                </div>
                            @endif

                        {{-- Tahap 2: Admisi Akademik --}}
                        @else
                            <div class="alert alert-success border rounded-0 small mb-4 text-center uppercase fw-bold">
                                <i class="bi bi-check-circle-fill me-1"></i> Tahap 1 Selesai: Pembayaran Lunas
                            </div>

                            <span class="d-block text-dark small fw-bold uppercase mb-2">LANGKAH 2: PENETAPAN HASIL SELEKSI</span>
                            
                            @if(!$camaba->pilihan_prodi_1_id || !$camaba->tempat_lahir)
                                <div class="alert alert-warning border rounded-0 small text-center mb-0 uppercase">
                                    Tertahan: Formulir biodata akademik belum dilengkapi oleh kandidat.
                                </div>
                            @else
                                <form action="{{ route('admin.pmb.approve', $camaba->id) }}" method="POST" class="mb-2" onsubmit="return confirm('Sistem akan mendaftarkan kandidat ini menjadi Mahasiswa Aktif dan men-generate NIM secara otomatis. Lanjutkan?')">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-dark w-100 rounded-0 uppercase fw-bold small py-2 shadow-sm">
                                        <i class="bi bi-person-check-fill me-1"></i> Setujui Penerimaan (Terima)
                                    </button>
                                </form>

                                <button type="button" class="btn btn-outline-danger rounded-0 w-100 uppercase fw-bold small py-2" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    <i class="bi bi-x-circle me-1"></i> Tolak Pendaftaran
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
    <div class="modal-dialog modal-dialog-centered rounded-0">
        <div class="modal-content rounded-0 border-dark border-2">
            <div class="modal-header bg-dark text-white rounded-0 py-3">
                <h6 class="modal-title fw-bold text-white uppercase small">Justifikasi Penolakan Admisi</h6>
                <button type="button" class="btn-close btn-close-white rounded-0" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.pmb.reject', $camaba->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Tindakan ini akan menolak pendaftaran kandidat <strong>{{ $camaba->user->name ?? '' }}</strong>.</p>
                    <label class="form-label text-dark fw-bold uppercase small">Keterangan / Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea name="alasan" class="form-control rounded-0 uppercase" rows="3" placeholder="CONTOH: DOKUMEN PERSYARATAN TIDAK VALID..." required></textarea>
                </div>
                <div class="modal-footer bg-light border-top rounded-0">
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-danger rounded-0 px-4 uppercase fw-bold small">Eksekusi Penolakan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
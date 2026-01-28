@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- Breadcrumb & Back Button --}}
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.pmb.index') }}" class="btn btn-light border me-3 rounded-circle" style="width: 40px; height: 40px; padding-top: 8px;">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0 fw-bold text-teal-700">Detail Pendaftar</h4>
            <p class="text-muted small mb-0">Verifikasi data dan berkas calon mahasiswa baru.</p>
        </div>
    </div>

    <div class="row">
        {{-- KOLOM KIRI: DATA UTAMA --}}
        <div class="col-lg-8">
            
            {{-- 1. Kartu Profil Ringkas --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start">
                        <div class="bg-teal-50 text-teal-600 rounded-circle d-flex align-items-center justify-content-center fw-bold fs-2 me-4" style="width: 80px; height: 80px;">
                            {{ substr($camaba->user->name, 0, 1) }}
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="fw-bold mb-1">{{ $camaba->user->name }}</h3>
                            <div class="d-flex flex-wrap gap-3 text-secondary small mb-3">
                                <span><i class="bi bi-envelope me-1"></i> {{ $camaba->user->email }}</span>
                                <span><i class="bi bi-whatsapp me-1"></i> {{ $camaba->no_hp }}</span>
                                <span><i class="bi bi-geo-alt me-1"></i> {{ Str::limit($camaba->alamat, 40) }}</span>
                            </div>
                            <div class="d-flex gap-2">
                                <span class="badge bg-light text-dark border"><i class="bi bi-upc-scan me-1"></i> {{ $camaba->no_pendaftaran }}</span>
                                <span class="badge bg-light text-dark border"><i class="bi bi-calendar-event me-1"></i> Gelombang: {{ $camaba->period->nama_gelombang ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Detail Informasi --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-person-lines-fill me-2"></i>Informasi Lengkap</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <tbody>
                                <tr class="bg-light"><td colspan="2" class="fw-bold text-muted small px-4 py-2">DATA PRIBADI</td></tr>
                                <tr>
                                    <td width="30%" class="px-4 text-muted">Tempat, Tanggal Lahir</td>
                                    <td class="fw-bold">{{ $camaba->tempat_lahir }}, {{ \Carbon\Carbon::parse($camaba->tanggal_lahir)->translatedFormat('d F Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 text-muted">Jenis Kelamin</td>
                                    <td>{{ $camaba->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 text-muted">Agama</td>
                                    <td>{{ $camaba->agama }}</td>
                                </tr>
                                
                                <tr class="bg-light"><td colspan="2" class="fw-bold text-muted small px-4 py-2">DATA SEKOLAH & NILAI</td></tr>
                                <tr>
                                    <td class="px-4 text-muted">Sekolah Asal</td>
                                    <td class="fw-bold">{{ $camaba->sekolah_asal }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 text-muted">NISN</td>
                                    <td>{{ $camaba->nisn }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 text-muted">Tahun Lulus</td>
                                    <td>{{ $camaba->tahun_lulus }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 text-muted">Rata-rata Nilai Rapor</td>
                                    <td><span class="badge bg-info text-dark fs-6">{{ $camaba->nilai_rata_rata_rapor }}</span></td>
                                </tr>

                                <tr class="bg-light"><td colspan="2" class="fw-bold text-muted small px-4 py-2">PILIHAN PROGRAM STUDI</td></tr>
                                <tr>
                                    <td class="px-4 text-muted">Pilihan 1 (Prioritas)</td>
                                    <td class="fw-bold text-primary">{{ $camaba->prodi1->nama_prodi ?? '-' }} ({{ $camaba->prodi1->jenjang ?? '' }})</td>
                                </tr>
                                <tr>
                                    <td class="px-4 text-muted">Pilihan 2</td>
                                    <td>{{ $camaba->prodi2->nama_prodi ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- 3. Dokumen Lampiran --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-folder2-open me-2"></i>Berkas Persyaratan</h6>
                    <span class="badge bg-secondary">{{ $camaba->documents->count() }} Berkas</span>
                </div>
                <div class="card-body">
                    @if($camaba->documents->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-file-earmark-x fs-1 d-block mb-2"></i>
                            Belum ada dokumen yang diupload.
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach($camaba->documents as $doc)
                                <div class="col-md-6">
                                    <div class="border rounded p-3 d-flex align-items-center hover-card">
                                        <div class="bg-light rounded p-2 me-3 text-danger">
                                            <i class="bi bi-file-earmark-pdf-fill fs-2"></i>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <h6 class="mb-1 text-truncate fw-bold">{{ $doc->jenis_dokumen }}</h6>
                                            <p class="mb-0 small text-muted">Diunggah: {{ $doc->created_at->format('d M Y') }}</p>
                                        </div>
                                        <a href="{{ Storage::url($doc->path_file) }}" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- KOLOM KANAN: PANEL AKSI (STICKY) --}}
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 20px; z-index: 10;">
                
                {{-- KARTU STATUS PENDAFTARAN --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body text-center p-4">
                        <h6 class="text-muted text-uppercase small fw-bold mb-3">Status Saat Ini</h6>
                        
                        @if($camaba->status_pendaftaran == 'lulus')
                            <div class="mb-3"><i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i></div>
                            <h4 class="fw-bold text-success mb-1">DITERIMA</h4>
                            <p class="text-muted small">Sudah menjadi Mahasiswa Aktif</p>

                        @elseif($camaba->status_pendaftaran == 'tidak_lulus')
                            <div class="mb-3"><i class="bi bi-x-circle-fill text-danger" style="font-size: 4rem;"></i></div>
                            <h4 class="fw-bold text-danger mb-1">DITOLAK</h4>
                            <p class="text-muted small">Tidak memenuhi syarat.</p>

                        @else
                            {{-- Jika Belum Lulus/Tolak, Cek Status Pembayaran Dulu --}}
                            @if($tagihan && $tagihan->status == 'lunas')
                                <div class="mb-3"><div class="spinner-grow text-primary" role="status" style="width: 3rem; height: 3rem;"></div></div>
                                <h4 class="fw-bold text-primary mb-1">PROSES SELEKSI</h4>
                                <p class="text-muted small">Pembayaran Lunas. Menunggu Biodata & Keputusan.</p>
                            @else
                                <div class="mb-3"><i class="bi bi-wallet2 text-warning" style="font-size: 4rem;"></i></div>
                                <h4 class="fw-bold text-warning text-dark mb-1">BELUM LUNAS</h4>
                                <p class="text-muted small">Menunggu Pembayaran Formulir.</p>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- KARTU AKSI ADMIN (LOGIKA BERTINGKAT) --}}
                @if($camaba->status_pendaftaran != 'lulus' && $camaba->status_pendaftaran != 'tidak_lulus')
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-dark text-white fw-bold py-3">
                        <i class="bi bi-gavel me-2"></i> Tindakan Admin
                    </div>
                    <div class="card-body">

                        {{-- TAHAP 1: VALIDASI PEMBAYARAN --}}
                        @if($tagihan && $tagihan->status != 'lunas')
                            <h6 class="fw-bold border-bottom pb-2 mb-3 text-warning">1. Validasi Pembayaran</h6>
                            
                            @if($tagihan->bukti_bayar)
                                <div class="alert alert-info small">
                                    <i class="bi bi-info-circle me-1"></i> Bukti bayar sudah diupload.
                                    <a href="{{ Storage::url($tagihan->bukti_bayar) }}" target="_blank" class="fw-bold text-decoration-underline">Lihat Bukti</a>
                                </div>
                                <form action="{{ route('admin.pmb.payment.approve', $tagihan->id) }}" method="POST" onsubmit="return confirm('Validasi pembayaran ini LUNAS?')">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100 fw-bold mb-3">
                                        <i class="bi bi-check-circle me-1"></i> VALIDASI LUNAS
                                    </button>
                                </form>
                            @else
                                <div class="alert alert-secondary small text-center">
                                    Menunggu Camaba upload bukti bayar.
                                </div>
                            @endif

                        {{-- TAHAP 2: VALIDASI KELULUSAN (Hanya muncul jika sudah Lunas) --}}
                        @else
                            <div class="alert alert-success small mb-3">
                                <i class="bi bi-check-circle-fill me-1"></i> Pembayaran Lunas.
                            </div>

                            <h6 class="fw-bold border-bottom pb-2 mb-3 text-primary">2. Keputusan Seleksi</h6>
                            
                            @if(!$camaba->pilihan_prodi_1_id)
                                <div class="alert alert-warning small">
                                    <i class="bi bi-exclamation-triangle me-1"></i> Camaba belum melengkapi biodata (Prodi belum dipilih).
                                </div>
                            @else
                                <form action="{{ route('admin.pmb.approve', $camaba->id) }}" method="POST" class="mb-2" onsubmit="return confirm('Yakin TERIMA calon mahasiswa ini? Sistem akan otomatis generate NIM.')">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-success w-100 fw-bold py-3">
                                        <i class="bi bi-person-check-fill me-1"></i> TERIMA MAHASISWA
                                    </button>
                                </form>

                                <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
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

{{-- Modal Tolak --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">Konfirmasi Penolakan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3 text-danger"><i class="bi bi-exclamation-triangle-fill fs-1"></i></div>
                <p class="mb-3">Apakah Anda yakin ingin <strong>MENOLAK</strong> pendaftaran ini?</p>
                <form action="{{ route('admin.pmb.reject', $camaba->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <textarea name="alasan" class="form-control mb-3" placeholder="Tuliskan alasan penolakan..." required></textarea>
                    <button type="submit" class="btn btn-danger px-4 fw-bold w-100">Ya, Tolak</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
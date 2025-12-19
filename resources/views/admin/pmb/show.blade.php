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
        {{-- KOLOM KIRI: DATA UTAMA (8 Kolom) --}}
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

            {{-- 2. Detail Informasi (Tabs / Sections) --}}
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

        {{-- KOLOM KANAN: PANEL AKSI (4 Kolom - Sticky) --}}
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 20px; z-index: 10;">
                
                {{-- Status Saat Ini --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body text-center p-4">
                        <h6 class="text-muted text-uppercase small fw-bold mb-3">Status Pendaftaran</h6>
                        
                        @if($camaba->status_pendaftaran == 'lulus')
                            <div class="mb-3">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                            </div>
                            <h4 class="fw-bold text-success mb-1">DITERIMA</h4>
                            <p class="text-muted small">Mahasiswa Aktif</p>
                        @elseif($camaba->status_pendaftaran == 'tidak_lulus')
                            <div class="mb-3">
                                <i class="bi bi-x-circle-fill text-danger" style="font-size: 4rem;"></i>
                            </div>
                            <h4 class="fw-bold text-danger mb-1">DITOLAK</h4>
                            <p class="text-muted small">Mohon maaf, belum memenuhi kriteria.</p>
                        @elseif($camaba->status_pendaftaran == 'menunggu_verifikasi')
                            <div class="mb-3">
                                <div class="spinner-grow text-warning" role="status" style="width: 3rem; height: 3rem;"></div>
                            </div>
                            <h4 class="fw-bold text-warning text-dark mb-1">MENUNGGU VERIFIKASI</h4>
                            <p class="text-muted small">Silakan periksa data sebelum memutuskan.</p>
                        @else
                            <div class="mb-3">
                                <i class="bi bi-pencil-square text-secondary" style="font-size: 4rem;"></i>
                            </div>
                            <h4 class="fw-bold text-secondary mb-1">DRAFT</h4>
                            <p class="text-muted small">Camaba belum melengkapi biodata.</p>
                        @endif
                    </div>
                </div>

                {{-- Panel Aksi (Hanya muncul jika belum diputuskan) --}}
                @if($camaba->status_pendaftaran == 'menunggu_verifikasi' || $camaba->status_pendaftaran == 'draft')
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-dark text-white fw-bold py-3">
                        <i class="bi bi-gavel me-2"></i> Keputusan Panitia
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info small mb-3">
                            <i class="bi bi-info-circle-fill me-1"></i> 
                            Jika diterima, sistem akan otomatis:
                            <ul class="mb-0 ps-3 mt-1">
                                <li>Membuat Akun Mahasiswa</li>
                                <li>Membuat NIM Otomatis</li>
                                <li>Mengubah status user login</li>
                            </ul>
                        </div>

                        <form action="{{ route('admin.pmb.approve', $camaba->id) }}" method="POST" class="d-grid mb-3">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-success fw-bold py-3" onclick="return confirm('Apakah Anda yakin menerima pendaftar ini? Tindakan ini tidak dapat dibatalkan.')">
                                <i class="bi bi-check-lg me-2"></i> TERIMA MAHASISWA
                            </button>
                        </form>

                        <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="bi bi-x-circle me-2"></i> Tolak Pendaftaran
                        </button>
                    </div>
                </div>
                @endif

                {{-- Info Tambahan --}}
                <div class="mt-4 text-muted small text-center">
                    <p class="mb-1">Terdaftar pada: {{ $camaba->created_at->format('d M Y, H:i') }}</p>
                    <p>Terakhir diupdate: {{ $camaba->updated_at->diffForHumans() }}</p>
                </div>

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
                <div class="mb-3 text-danger">
                    <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                </div>
                <p class="mb-0">Apakah Anda yakin ingin <strong>MENOLAK</strong> pendaftaran calon mahasiswa ini?</p>
                <p class="text-muted small">Status akan berubah menjadi 'Tidak Lulus'.</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center pb-4">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('admin.pmb.reject', $camaba->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger px-4 fw-bold">Ya, Tolak</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- Tombol Navigasi Atas --}}
    <div class="mb-3 mt-3">
        <a href="{{ route('verum.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kelas
        </a>
    </div>

    {{-- HEADER KELAS & KONTROL MEETING VIDEO CONFERENCE --}}
    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4 bg-white">
        <div class="card-body p-4 p-md-5 d-md-flex justify-content-between align-items-center">
            <div class="mb-4 mb-md-0">
                <span class="badge bg-dark text-white rounded-0 font-monospace uppercase fw-bold px-3 py-1 mb-2 shadow-none" style="font-size: 11px;">
                    KODE MK: {{ optional($verum_kela->mataKuliah)->kode_mk ?? 'KODE' }}
                </span>
                <h3 class="fw-bold text-dark mb-2 uppercase">{{ $verum_kela->nama_kelas }}</h3>
                
                <div class="d-flex flex-wrap align-items-center text-dark small uppercase fw-bold mb-3 border-bottom pb-2">
                    <span class="me-3">
                        <i class="bi bi-book-half me-1"></i> {{ optional($verum_kela->mataKuliah)->nama_mk }}
                    </span>
                    <span class="font-monospace text-primary">
                        <i class="bi bi-upc-scan me-1 text-dark"></i> KODE AKSES: <strong class="text-dark">{{ $verum_kela->kode_kelas }}</strong>
                    </span>
                </div>
                
                @if($verum_kela->deskripsi)
                    <p class="text-dark small uppercase mb-0" style="max-width: 800px; line-height: 1.6; text-align: justify;">
                        {{ $verum_kela->deskripsi }}
                    </p>
                @endif
            </div>

            {{-- PANEL KONTROL MEETING (JITSI INTEGRATION) --}}
            <div class="text-start text-md-end border-top border-md-top-0 pt-3 pt-md-0">
                @if($verum_kela->is_meeting_active)
                    <div class="mb-2">
                        <span class="badge bg-danger text-white rounded-0 font-monospace uppercase fw-bold px-3 py-2 blink shadow-none" style="font-size: 11px;">
                            <i class="bi bi-camera-video-fill me-1"></i> SESI KELAS LIVE
                        </span>
                    </div>
                    
                    @php
                        $roomName = "STTGPI-Verum-" . str_replace(' ', '', $verum_kela->kode_kelas);
                        $jitsiUrl = "https://meet.jit.si/" . $roomName;
                    @endphp

                    <div class="d-flex gap-2 justify-content-start justify-content-md-end">
                        <a href="{{ $jitsiUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-success rounded-0 uppercase fw-bold px-4 py-2 shadow-sm text-white">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Gabung Vicon
                        </a>
                        
                        @if(Auth::user()->hasRole('dosen'))
                            <form action="{{ route('verum.meeting.stop', $verum_kela) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-danger rounded-0 px-3 py-2 shadow-sm uppercase fw-bold" onclick="return confirm('Akhiri sesi kelas online secara permanen?')">
                                    <i class="bi bi-stop-circle-fill me-1"></i> Tutup Vicon
                                </button>
                            </form>
                        @endif
                    </div>
                @else
                    @if(Auth::user()->hasRole('dosen'))
                        <form action="{{ route('verum.meeting.start', $verum_kela) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-primary rounded-0 px-4 py-2 uppercase fw-bold small shadow-sm">
                                <i class="bi bi-camera-video-fill me-1"></i> Mulai Vicon Kelas
                            </button>
                        </form>
                    @else
                        <span class="badge border border-dark text-muted rounded-0 font-monospace uppercase fw-bold px-3 py-2 d-block text-center shadow-none" style="font-size: 10px;">
                            <i class="bi bi-camera-video-off me-1"></i> VICON BELUM DIMULAI
                        </span>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- NAVIGASI TAB MODERN FLAT PRESISI 0PX --}}
    <div class="card border-0 shadow-sm rounded-0 mb-5 bg-white">
        <div class="card-header bg-dark text-white rounded-0 p-0 border-bottom-0">
            <ul class="nav nav-tabs rounded-0 border-0 flex-column flex-sm-row" id="classTab" role="tablist">
                <li class="nav-item flex-sm-fill text-center" role="presentation">
                    <button class="nav-link active rounded-0 fw-bold py-3 uppercase small w-100 border-0 text-white" id="forum-tab" data-bs-toggle="tab" data-bs-target="#forum" type="button" role="tab">
                        <i class="bi bi-chat-quote-fill me-2"></i>Forum Diskusi
                    </button>
                </li>
                <li class="nav-item flex-sm-fill text-center" role="presentation">
                    <button class="nav-link rounded-0 fw-bold py-3 uppercase small w-100 border-0 text-white" id="materi-tab" data-bs-toggle="tab" data-bs-target="#materi" type="button" role="tab">
                        <i class="bi bi-journal-richtext me-2"></i>Materi Ajar
                    </button>
                </li>
                <li class="nav-item flex-sm-fill text-center" role="presentation">
                    <button class="nav-link rounded-0 fw-bold py-3 uppercase small w-100 border-0 text-white" id="tugas-tab" data-bs-toggle="tab" data-bs-target="#tugas" type="button" role="tab">
                        <i class="bi bi-clipboard-check-fill me-2"></i>Penugasan
                    </button>
                </li>
                <li class="nav-item flex-sm-fill text-center" role="presentation">
                    <button class="nav-link rounded-0 fw-bold py-3 uppercase small w-100 border-0 text-white" id="presensi-tab" data-bs-toggle="tab" data-bs-target="#presensi" type="button" role="tab">
                        <i class="bi bi-calendar-check-fill me-2"></i>Presensi
                    </button>
                </li>
            </ul>
        </div>
        
        <div class="card-body p-4 p-md-5 bg-white border border-top-0 border-dark border-opacity-25 rounded-0">
            <div class="tab-content" id="classTabContent">
                <div class="tab-pane fade show active" id="forum" role="tabpanel">
                    @include('verum.partials.forum')
                </div>
                <div class="tab-pane fade" id="materi" role="tabpanel">
                    @include('verum.partials.materi')
                </div>
                <div class="tab-pane fade" id="tugas" role="tabpanel">
                    @include('verum.partials.tugas')
                </div>
                <div class="tab-pane fade" id="presensi" role="tabpanel">
                    @include('verum.partials.presensi')
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL KHUSUS DOSEN PENGAMPU --}}
@if(Auth::user()->hasRole('dosen'))
    {{-- MODAL TAMBAH MATERI --}}
    <div class="modal fade" id="tambahMateriModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog rounded-0">
            <div class="modal-content rounded-0 border-dark border-2">
                <div class="modal-header bg-dark text-white rounded-0 py-3">
                    <h6 class="modal-title uppercase fw-bold small text-white">Tambah Materi Baru</h6>
                    <button type="button" class="btn-close btn-close-white rounded-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('verum.materi.store', $verum_kela) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold uppercase text-dark">Judul Materi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-0 uppercase" name="judul" required placeholder="JUDUL MODUL ATAU BAHAN AJAR...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold uppercase text-dark">Deskripsi Ringkas</label>
                            <textarea class="form-control rounded-0 uppercase" name="deskripsi" rows="3" placeholder="URAIAN PENJELASAN..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold uppercase text-dark">Unggah Berkas Dokumen</label>
                            <input class="form-control rounded-0" type="file" name="file_materi">
                            <div class="form-text text-muted font-monospace small">Maksimal ukuran berkas dibatasi 10MB.</div>
                        </div>
                        <div class="text-center my-3 text-muted font-monospace small fw-bold">- ATAU TAUTAN EKSTERNAL -</div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold uppercase text-dark">Tautan Referensi (URL)</label>
                            <input type="url" class="form-control rounded-0 font-monospace" name="link_url" placeholder="https://...">
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-0">
                        <button type="button" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm">Simpan Materi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH TUGAS --}}
    <div class="modal fade" id="tambahTugasModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog rounded-0">
            <div class="modal-content rounded-0 border-dark border-2">
                <div class="modal-header bg-dark text-white rounded-0 py-3">
                    <h6 class="modal-title uppercase fw-bold small text-white">Buat Penugasan Baru</h6>
                    <button type="button" class="btn-close btn-close-white rounded-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('verum.tugas.store', $verum_kela) }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold uppercase text-dark">Judul Penugasan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-0 uppercase" name="judul" required placeholder="CONTOH: TUGAS KELOMPOK 1">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold uppercase text-dark">Instruksi Pengerjaan <span class="text-danger">*</span></label>
                            <textarea class="form-control rounded-0 uppercase" name="instruksi" rows="4" required placeholder="URAIKAN KETENTUAN TUGAS..."></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold uppercase text-dark">Batas Tenggat Waktu <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control rounded-0 font-monospace text-center fw-bold" name="tenggat_waktu" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-0">
                        <button type="button" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm">Simpan Tugas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL BUKA PRESENSI --}}
    <div class="modal fade" id="bukaPresensiModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog rounded-0">
            <div class="modal-content rounded-0 border-dark border-2">
                <div class="modal-header bg-dark text-white rounded-0 py-3">
                    <h6 class="modal-title uppercase fw-bold small text-white">Buka Sesi Presensi Kehadiran</h6>
                    <button type="button" class="btn-close btn-close-white rounded-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('verum.presensi.store', $verum_kela) }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold uppercase text-dark">Judul Pertemuan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-0 uppercase" name="judul_pertemuan" placeholder="CONTOH: PEMBAHASAN BAB 1" required>
                        </div>
                        <div class="row g-3 mb-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase text-dark">Pertemuan Ke- <span class="text-danger">*</span></label>
                                <input type="number" class="form-control rounded-0 font-monospace text-center" name="pertemuan_ke" min="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase text-dark">Durasi Sesi (Menit) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control rounded-0 font-monospace text-center" name="durasi" min="1" value="15" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-0">
                        <button type="button" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm">Buka Sesi Aktif</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<style>
    @keyframes blink { 50% { opacity: 0; } }
    .blink { animation: blink 1.5s linear infinite; }
    
    /* Perataan Bilah Tab Navigasi Enterprise Flat 0px */
    .nav-tabs { border-bottom: 2px solid #212529; }
    .nav-tabs .nav-link { 
        color: #fff; 
        background-color: #212529; 
        border: none; 
        opacity: 0.7; 
        transition: opacity 0.2s;
    }
    .nav-tabs .nav-link:hover { 
        opacity: 1; 
        color: #fff; 
    }
    .nav-tabs .nav-link.active { 
        color: #212529 !important; 
        background-color: #fff !important; 
        border: none; 
        border-top: 4px solid #212529;
        opacity: 1;
        font-weight: bold;
    }
</style>
@endsection
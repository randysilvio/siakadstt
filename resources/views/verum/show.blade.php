@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Header Kelas & Kontrol Meeting --}}
    <div class="card mb-4 shadow-sm border-0 overflow-hidden">
        <div class="card-body p-4 d-md-flex justify-content-between align-items-center bg-white position-relative">
            <div class="position-relative z-1 mb-3 mb-md-0">
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 mb-2">
                    {{ optional($verum_kela->mataKuliah)->kode_mk ?? 'KODE' }}
                </span>
                <h2 class="card-title fw-bold text-dark mb-1">{{ $verum_kela->nama_kelas }}</h2>
                <p class="text-muted mb-2">
                    <i class="bi bi-book-half me-1"></i> {{ optional($verum_kela->mataKuliah)->nama_mk }}
                    <span class="mx-2 text-muted opacity-50">|</span>
                    <i class="bi bi-upc-scan me-1"></i> Kode Akses: <strong class="text-dark">{{ $verum_kela->kode_kelas }}</strong>
                </p>
                @if($verum_kela->deskripsi)
                    <p class="card-text text-secondary small mb-0 mt-2" style="max-width: 600px;">
                        {{ $verum_kela->deskripsi }}
                    </p>
                @endif
            </div>

            {{-- PANEL KONTROL MEETING (VIDEO CONFERENCE) --}}
            <div class="text-md-end position-relative z-1">
                @if($verum_kela->is_meeting_active)
                    <div class="mb-2">
                        <span class="badge bg-danger fs-6 animate-pulse shadow-sm">
                            <i class="bi bi-camera-video-fill me-1"></i> LIVE SEKARANG
                        </span>
                    </div>
                    
                    @php
                        $roomName = "STTGPI-Verum-" . str_replace(' ', '', $verum_kela->kode_kelas);
                        $jitsiUrl = "https://meet.jit.si/" . $roomName;
                    @endphp

                    <div class="d-flex gap-2 justify-content-md-end">
                        <a href="{{ $jitsiUrl }}" target="_blank" class="btn btn-success shadow-sm fw-bold px-4">
                            <i class="bi bi-box-arrow-up-right me-2"></i> Gabung (Tab Baru)
                        </a>
                        
                        @if(Auth::user()->hasRole('dosen'))
                            <form action="{{ route('verum.meeting.stop', $verum_kela) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-outline-danger shadow-sm" onclick="return confirm('Akhiri sesi kelas online?')">
                                    <i class="bi bi-stop-circle-fill"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                @else
                    @if(Auth::user()->hasRole('dosen'))
                        <form action="{{ route('verum.meeting.start', $verum_kela) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-primary shadow-sm fw-bold px-4">
                                <i class="bi bi-camera-video-fill me-2"></i> Mulai Kelas Online
                            </button>
                        </form>
                    @else
                        <button class="btn btn-light text-muted border" disabled>
                            <i class="bi bi-camera-video-off me-2"></i> Kelas Belum Dimulai
                        </button>
                    @endif
                @endif
            </div>
            
            {{-- Dekorasi Latar --}}
            <div class="position-absolute end-0 top-0 h-100 d-none d-lg-block" style="width: 150px; background: linear-gradient(90deg, transparent, rgba(13, 110, 253, 0.05));"></div>
        </div>
    </div>

    {{-- Navigasi Tab Modern --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-3 px-3">
            <ul class="nav nav-tabs card-header-tabs" id="classTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold py-2 px-4" id="forum-tab" data-bs-toggle="tab" data-bs-target="#forum" type="button" role="tab">
                        <i class="bi bi-chat-quote me-2"></i>Forum
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold py-2 px-4" id="materi-tab" data-bs-toggle="tab" data-bs-target="#materi" type="button" role="tab">
                        <i class="bi bi-journal-text me-2"></i>Materi
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold py-2 px-4" id="tugas-tab" data-bs-toggle="tab" data-bs-target="#tugas" type="button" role="tab">
                        <i class="bi bi-clipboard-check me-2"></i>Tugas
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold py-2 px-4" id="presensi-tab" data-bs-toggle="tab" data-bs-target="#presensi" type="button" role="tab">
                        <i class="bi bi-person-check me-2"></i>Presensi
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-4 bg-light bg-opacity-10">
            <div class="tab-content" id="classTabContent">
                <div class="tab-pane fade show active" id="forum" role="tabpanel">@include('verum.partials.forum')</div>
                <div class="tab-pane fade" id="materi" role="tabpanel">@include('verum.partials.materi')</div>
                <div class="tab-pane fade" id="tugas" role="tabpanel">@include('verum.partials.tugas')</div>
                <div class="tab-pane fade" id="presensi" role="tabpanel">@include('verum.partials.presensi')</div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL HANYA DITAMPILKAN JIKA USER ADALAH DOSEN --}}
@if(Auth::user()->hasRole('dosen'))
    {{-- MODAL TAMBAH MATERI --}}
    <div class="modal fade" id="tambahMateriModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog"><div class="modal-content">
          <div class="modal-header"><h5 class="modal-title fw-bold">Tambah Materi Baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
          <form action="{{ route('verum.materi.store', $verum_kela) }}" method="POST" enctype="multipart/form-data">
              @csrf
              <div class="modal-body">
                  <div class="mb-3"><label class="form-label fw-bold">Judul Materi</label><input type="text" class="form-control" name="judul" required></div>
                  <div class="mb-3"><label class="form-label fw-bold">Deskripsi</label><textarea class="form-control" name="deskripsi" rows="2"></textarea></div>
                  <div class="mb-3"><label class="form-label fw-bold">Unggah File</label><input class="form-control" type="file" name="file_materi"></div>
                  <div class="text-center my-2 text-muted small fw-bold">- ATAU -</div>
                  <div class="mb-3"><label class="form-label fw-bold">Link Tautan</label><input type="url" class="form-control" name="link_url" placeholder="https://..."></div>
              </div>
              <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
          </form>
      </div></div>
    </div>

    {{-- MODAL TAMBAH TUGAS --}}
    <div class="modal fade" id="tambahTugasModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog"><div class="modal-content">
          <div class="modal-header"><h5 class="modal-title fw-bold">Buat Tugas Baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
          <form action="{{ route('verum.tugas.store', $verum_kela) }}" method="POST">
              @csrf
              <div class="modal-body">
                  <div class="mb-3"><label class="form-label fw-bold">Judul Tugas</label><input type="text" class="form-control" name="judul" required></div>
                  <div class="mb-3"><label class="form-label fw-bold">Instruksi</label><textarea class="form-control" name="instruksi" rows="4" required></textarea></div>
                  <div class="mb-3"><label class="form-label fw-bold">Tenggat Waktu</label><input type="datetime-local" class="form-control" name="tenggat_waktu" required></div>
              </div>
              <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
          </form>
      </div></div>
    </div>

    {{-- MODAL BUKA PRESENSI --}}
    <div class="modal fade" id="bukaPresensiModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog"><div class="modal-content">
          <div class="modal-header"><h5 class="modal-title fw-bold">Buka Sesi Presensi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
          <form action="{{ route('verum.presensi.store', $verum_kela) }}" method="POST">
              @csrf
              <div class="modal-body">
                  <div class="mb-3"><label class="form-label fw-bold">Judul Pertemuan</label><input type="text" class="form-control" name="judul_pertemuan" placeholder="Contoh: Pembahasan Bab 1" required></div>
                  <div class="row">
                      <div class="col-6 mb-3"><label class="form-label fw-bold">Pertemuan Ke-</label><input type="number" class="form-control" name="pertemuan_ke" min="1" required></div>
                      <div class="col-6 mb-3"><label class="form-label fw-bold">Durasi (menit)</label><input type="number" class="form-control" name="durasi" min="1" value="15" required></div>
                  </div>
              </div>
              <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Buka Sesi</button></div>
          </form>
      </div></div>
    </div>
@endif
@endsection

@push('styles')
<style>
    .animate-pulse { animation: pulse 2s infinite; }
    @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
    .nav-tabs .nav-link { color: #6c757d; border: none; border-bottom: 3px solid transparent; }
    .nav-tabs .nav-link:hover { color: #0d6efd; background-color: transparent; }
    .nav-tabs .nav-link.active { color: #0d6efd; border-bottom: 3px solid #0d6efd; background-color: transparent; }
</style>
@endpush
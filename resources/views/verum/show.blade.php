@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Header Kelas & Kontrol Meeting --}}
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h2 class="card-title fw-bold text-primary">{{ $verum_kela->nama_kelas }}</h2>
                <p class="card-subtitle mb-2 text-muted">
                    <i class="bi bi-book me-1"></i> {{ optional($verum_kela->mataKuliah)->nama_mk }}
                    <span class="mx-2">•</span>
                    <i class="bi bi-key me-1"></i> Kode: <strong>{{ $verum_kela->kode_kelas }}</strong>
                </p>
                <p class="card-text text-secondary mb-0">{{ $verum_kela->deskripsi }}</p>
            </div>

            {{-- [BARU] PANEL KONTROL MEETING (VIDEO CONFERENCE) --}}
            <div class="text-end">
                @if($verum_kela->is_meeting_active)
                    <div class="mb-2">
                        <span class="badge bg-danger fs-6 animate-pulse">
                            <i class="bi bi-camera-video-fill me-1"></i> LIVE SEKARANG
                        </span>
                    </div>
                    {{-- Tombol Gabung untuk Semua User --}}
                    <button type="button" class="btn btn-success btn-lg shadow" onclick="joinMeeting()">
                        <i class="bi bi-camera-video me-2"></i> Gabung Kelas
                    </button>
                    
                    {{-- Tombol Akhiri Khusus Dosen --}}
                    @if(Auth::user()->hasRole('dosen'))
                        <form action="{{ route('verum.meeting.stop', $verum_kela) }}" method="POST" class="d-inline-block ms-2">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Akhiri sesi kelas online?')">
                                <i class="bi bi-stop-circle"></i> Akhiri
                            </button>
                        </form>
                    @endif
                @else
                    {{-- Tombol Mulai Khusus Dosen --}}
                    @if(Auth::user()->hasRole('dosen'))
                        <form action="{{ route('verum.meeting.start', $verum_kela) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-primary shadow">
                                <i class="bi bi-broadcast me-2"></i> Mulai Kelas Online
                            </button>
                        </form>
                    @else
                        {{-- Status Menunggu untuk Mahasiswa --}}
                        <button class="btn btn-secondary" disabled>
                            <i class="bi bi-camera-video-off me-2"></i> Kelas Belum Dimulai
                        </button>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- Navigasi Tab --}}
    <ul class="nav nav-tabs mb-3" id="classTab" role="tablist">
        <li class="nav-item" role="presentation"><button class="nav-link active" id="forum-tab" data-bs-toggle="tab" data-bs-target="#forum" type="button" role="tab" aria-controls="forum" aria-selected="true">Forum</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="materi-tab" data-bs-toggle="tab" data-bs-target="#materi" type="button" role="tab" aria-controls="materi" aria-selected="false">Materi</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="tugas-tab" data-bs-toggle="tab" data-bs-target="#tugas" type="button" role="tab" aria-controls="tugas" aria-selected="false">Tugas</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="presensi-tab" data-bs-toggle="tab" data-bs-target="#presensi" type="button" role="tab" aria-controls="presensi" aria-selected="false">Presensi</button></li>
    </ul>

    {{-- Konten dari setiap Tab --}}
    <div class="tab-content" id="classTabContent">
        <div class="tab-pane fade show active" id="forum" role="tabpanel" aria-labelledby="forum-tab">@include('verum.partials.forum')</div>
        <div class="tab-pane fade" id="materi" role="tabpanel" aria-labelledby="materi-tab">@include('verum.partials.materi')</div>
        <div class="tab-pane fade" id="tugas" role="tabpanel" aria-labelledby="tugas-tab">@include('verum.partials.tugas')</div>
        <div class="tab-pane fade" id="presensi" role="tabpanel" aria-labelledby="presensi-tab">
            @include('verum.partials.presensi')
        </div>
    </div>
</div>

{{-- [BARU] MODAL UNTUK JITSI MEET --}}
<div class="modal fade" id="jitsiModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 95vw;">
        <div class="modal-content" style="height: 90vh;">
            <div class="modal-header bg-dark text-white py-2">
                <h5 class="modal-title fs-6"><i class="bi bi-camera-video-fill me-2 text-danger"></i>Kelas Online: {{ $verum_kela->nama_kelas }}</h5>
                <button type="button" class="btn-close btn-close-white" onclick="closeMeeting()" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-black">
                {{-- Container tempat Jitsi akan di-render --}}
                <div id="jitsi-container" style="width: 100%; height: 100%;"></div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL HANYA DITAMPILKAN JIKA USER ADALAH DOSEN --}}
@if(Auth::user()->hasRole('dosen'))
    {{-- MODAL UNTUK TAMBAH MATERI --}}
    <div class="modal fade" id="tambahMateriModal" tabindex="-1" aria-labelledby="tambahMateriModalLabel" aria-hidden="true">
      <div class="modal-dialog"><div class="modal-content">
          <div class="modal-header"><h5 class="modal-title" id="tambahMateriModalLabel">Tambah Materi Baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
          <form action="{{ route('verum.materi.store', $verum_kela) }}" method="POST" enctype="multipart/form-data">
              @csrf
              <div class="modal-body">
                  <div class="mb-3"><label for="judul" class="form-label">Judul Materi</label><input type="text" class="form-control" id="judul" name="judul" required></div>
                  <div class="mb-3"><label for="deskripsi" class="form-label">Deskripsi (Opsional)</label><textarea class="form-control" id="deskripsi" name="deskripsi" rows="2"></textarea></div>
                  <div class="mb-3"><label for="file_materi" class="form-label">Unggah File</label><input class="form-control" type="file" id="file_materi" name="file_materi"></div>
                  <div class="text-center my-2">ATAU</div>
                  <div class="mb-3"><label for="link_url" class="form-label">Tempelkan Link</label><input type="url" class="form-control" id="link_url" name="link_url" placeholder="https://..."></div>
              </div>
              <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan Materi</button></div>
          </form>
      </div></div>
    </div>

    {{-- MODAL UNTUK TAMBAH TUGAS --}}
    <div class="modal fade" id="tambahTugasModal" tabindex="-1" aria-labelledby="tambahTugasModalLabel" aria-hidden="true">
      <div class="modal-dialog"><div class="modal-content">
          <div class="modal-header"><h5 class="modal-title" id="tambahTugasModalLabel">Buat Tugas Baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
          <form action="{{ route('verum.tugas.store', $verum_kela) }}" method="POST">
              @csrf
              <div class="modal-body">
                  <div class="mb-3"><label for="judul_tugas" class="form-label">Judul Tugas</label><input type="text" class="form-control" id="judul_tugas" name="judul" required></div>
                  <div class="mb-3"><label for="instruksi" class="form-label">Instruksi</label><textarea class="form-control" id="instruksi" name="instruksi" rows="4" required></textarea></div>
                  <div class="mb-3"><label for="tenggat_waktu" class="form-label">Tenggat Waktu</label><input type="datetime-local" class="form-control" id="tenggat_waktu" name="tenggat_waktu" required></div>
              </div>
              <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan Tugas</button></div>
          </form>
      </div></div>
    </div>

    {{-- MODAL UNTUK BUKA SESI PRESENSI --}}
    <div class="modal fade" id="bukaPresensiModal" tabindex="-1" aria-labelledby="bukaPresensiModalLabel" aria-hidden="true">
      <div class="modal-dialog"><div class="modal-content">
          <div class="modal-header"><h5 class="modal-title" id="bukaPresensiModalLabel">Buka Sesi Presensi Baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
          <form action="{{ route('verum.presensi.store', $verum_kela) }}" method="POST">
              @csrf
              <div class="modal-body">
                  <div class="mb-3"><label for="judul_pertemuan" class="form-label">Judul Pertemuan</label><input type="text" class="form-control" id="judul_pertemuan" name="judul_pertemuan" placeholder="Contoh: Pembahasan Doktrin Allah" required></div>
                  <div class="mb-3"><label for="pertemuan_ke" class="form-label">Pertemuan Ke-</label><input type="number" class="form-control" id="pertemuan_ke" name="pertemuan_ke" min="1" required></div>
                  <div class="mb-3"><label for="durasi" class="form-label">Durasi Sesi (menit)</label><input type="number" class="form-control" id="durasi" name="durasi" min="1" value="15" required></div>
              </div>
              <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Buka Sesi</button></div>
          </form>
      </div></div>
    </div>
@endif
@endsection

@push('scripts')
{{-- [BARU] Script Jitsi Meet External API --}}
<script src='https://meet.jit.si/external_api.js'></script>
<script>
    let jitsiApi = null;
    const jitsiModal = new bootstrap.Modal(document.getElementById('jitsiModal'));

    function joinMeeting() {
        jitsiModal.show();

        // Nama Room Unik (STT-GPI-[KODE KELAS]) untuk mencegah tabrakan dengan meeting orang lain
        const roomName = "STTGPI-Verum-{{ $verum_kela->kode_kelas }}";
        const domain = "meet.jit.si";
        const options = {
            roomName: roomName,
            width: '100%',
            height: '100%',
            parentNode: document.querySelector('#jitsi-container'),
            userInfo: {
                displayName: "{{ Auth::user()->name }}" // Menggunakan nama user yang sedang login
            },
            configOverwrite: { 
                startWithAudioMuted: true, 
                startWithVideoMuted: true,
                prejoinPageEnabled: false // Langsung masuk tanpa halaman pre-join
            },
            interfaceConfigOverwrite: {
                TOOLBAR_BUTTONS: [
                    'microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                    'fodeviceselection', 'hangup', 'profile', 'chat', 'recording',
                    'livestreaming', 'etherpad', 'sharedvideo', 'settings', 'raisehand',
                    'videoquality', 'filmstrip', 'invite', 'feedback', 'stats', 'shortcuts',
                    'tileview', 'videobackgroundblur', 'download', 'help', 'mute-everyone'
                ],
            },
            lang: 'id'
        };

        // Bersihkan instance lama jika ada (untuk mencegah duplikasi)
        if(jitsiApi) { jitsiApi.dispose(); }

        // Mulai Jitsi
        jitsiApi = new JitsiMeetExternalAPI(domain, options);

        // Event listener saat user menutup panggilan (tombol merah di dalam Jitsi)
        jitsiApi.addEventListener('videoConferenceLeft', function () {
            closeMeeting();
        });
    }

    function closeMeeting() {
        if(jitsiApi) {
            jitsiApi.dispose();
            jitsiApi = null;
        }
        jitsiModal.hide();
    }
</script>
<style>
    /* Animasi Berdenyut untuk Badge LIVE */
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    .animate-pulse {
        animation: pulse 2s infinite;
    }
</style>
@endpush
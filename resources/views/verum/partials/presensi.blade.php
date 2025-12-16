<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <div class="bg-warning bg-opacity-10 text-warning rounded p-2 me-3">
            <i class="bi bi-calendar-check-fill fs-4"></i>
        </div>
        <div>
            <h5 class="mb-0 fw-bold text-dark">Presensi Kehadiran</h5>
            <small class="text-muted">
                <i class="bi bi-clock me-1"></i> Waktu Server: {{ now()->format('H:i') }} WIT
            </small>
        </div>
    </div>
    @if(Auth::user()->hasRole('dosen'))
        <button class="btn btn-warning text-dark shadow-sm" data-bs-toggle="modal" data-bs-target="#bukaPresensiModal">
            <i class="bi bi-qr-code-scan me-1"></i> Buka Presensi
        </button>
    @endif
</div>

@if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm mb-3"><i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('error') }}</div>
@endif
@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm mb-3"><i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}</div>
@endif

@if($verum_kela->presensi->isEmpty())
    <div class="text-center py-5 border rounded bg-light">
        <i class="bi bi-calendar-x fs-1 text-secondary opacity-50 mb-2"></i>
        <p class="text-muted mb-0">Belum ada sesi presensi yang dibuka.</p>
    </div>
@else
    <div class="list-group shadow-sm">
        @foreach($verum_kela->presensi->sortByDesc('pertemuan_ke') as $sesi)
            @php
                $sekarang = now();
                $isBuka = $sekarang >= $sesi->waktu_buka;
                $isTutup = $sesi->waktu_tutup && $sekarang > $sesi->waktu_tutup;
                $sesiAktif = $isBuka && !$isTutup;

                $sudahAbsen = false;
                if(Auth::user()->hasRole('mahasiswa') && Auth::user()->mahasiswa) {
                    $sudahAbsen = $sesi->kehadiran->where('mahasiswa_id', Auth::user()->mahasiswa->id)->isNotEmpty();
                }
            @endphp
            
            <div class="list-group-item list-group-item-action p-3 {{ $sesiAktif ? 'border-start border-5 border-success bg-success bg-opacity-10' : '' }}">
                <div class="d-flex w-100 justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 fw-bold text-dark">
                            Pertemuan #{{ $sesi->pertemuan_ke }}
                            @if($sesiAktif) <span class="badge bg-danger ms-2 blink">LIVE</span> @endif
                        </h6>
                        <p class="mb-1 text-secondary small">{{ $sesi->judul_pertemuan }}</p>
                        <small class="text-muted">
                            <i class="bi bi-clock me-1"></i> {{ $sesi->waktu_buka->format('H:i') }} - {{ $sesi->waktu_tutup ? $sesi->waktu_tutup->format('H:i') : 'Selesai' }}
                        </small>
                    </div>

                    <div class="text-end">
                        @if(Auth::user()->hasRole('dosen'))
                            <span class="badge {{ $sesiAktif ? 'bg-success' : 'bg-secondary' }} rounded-pill mb-1">
                                {{ $sesiAktif ? 'Sedang Berlangsung' : 'Selesai' }}
                            </span>
                            <div class="small fw-bold text-dark">{{ $sesi->kehadiran->count() }} Hadir</div>
                        
                        @else
                            @if($sudahAbsen)
                                <button class="btn btn-success btn-sm disabled opacity-100 rounded-pill px-3">
                                    <i class="bi bi-check-lg me-1"></i> Hadir
                                </button>
                            @elseif($sesiAktif)
                                <form action="{{ route('verum.presensi.hadir', $sesi->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                                        <i class="bi bi-hand-index-thumb-fill me-1"></i> Absen
                                    </button>
                                </form>
                            @else
                                <span class="badge bg-secondary rounded-pill px-3">Ditutup</span>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<style>
    @keyframes blink { 50% { opacity: 0; } }
    .blink { animation: blink 1.5s linear infinite; }
</style>
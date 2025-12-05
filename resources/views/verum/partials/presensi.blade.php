<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Presensi (Kehadiran)</h4>
    {{-- PERBAIKAN: Gunakan hasRole --}}
    @if(Auth::user()->hasRole('dosen'))
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#bukaPresensiModal">+ Buka Sesi Presensi</button>
    @endif
</div>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

{{-- DEBUG WAKTU (Bisa dihapus nanti jika sudah fix) --}}
<div class="alert alert-info py-2 small">
    <i class="bi bi-clock"></i> Waktu Server Saat Ini: <strong>{{ now()->format('d M Y, H:i') }}</strong>
</div>

@if($verum_kela->presensi->isEmpty())
    <p class="text-center text-muted my-4">
        <img src="https://img.icons8.com/ios/50/cccccc/calendar--v1.png" class="d-block mx-auto mb-2" style="opacity: 0.5;">
        <i>Belum ada sesi presensi yang dibuka.</i>
    </p>
@else
    <ul class="list-group">
        @foreach($verum_kela->presensi->sortByDesc('pertemuan_ke') as $sesi)
            @php
                // Logika Waktu
                $sekarang = now();
                $isBuka = $sekarang >= $sesi->waktu_buka;
                $isTutup = $sesi->waktu_tutup && $sekarang > $sesi->waktu_tutup;
                $sesiAktif = $isBuka && !$isTutup;

                // Logika Sudah Absen
                $sudahAbsen = false;
                if(Auth::user()->hasRole('mahasiswa') && Auth::user()->mahasiswa) {
                    $sudahAbsen = $sesi->kehadiran->where('mahasiswa_id', Auth::user()->mahasiswa->id)->isNotEmpty();
                }
            @endphp
            <li class="list-group-item {{ $sesiAktif ? 'border-primary' : '' }}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Pertemuan {{ $sesi->pertemuan_ke }}: {{ $sesi->judul_pertemuan }}</h5>
                        <p class="mb-0 small text-muted">
                            <i class="bi bi-calendar-check"></i> Dibuka: {{ $sesi->waktu_buka->format('d M, H:i') }}
                            @if($sesi->waktu_tutup)
                                <span class="mx-1">|</span> <i class="bi bi-hourglass-split"></i> Tutup: {{ $sesi->waktu_tutup->format('H:i') }}
                            @endif
                        </p>
                    </div>
                    <div>
                        {{-- TAMPILAN UNTUK DOSEN --}}
                        @if(Auth::user()->hasRole('dosen'))
                            <span class="badge {{ $sesiAktif ? 'bg-success' : 'bg-secondary' }}">
                                {{ $sesiAktif ? 'Sedang Berlangsung' : 'Selesai' }}
                            </span>
                            <small class="d-block text-muted mt-1 text-end">{{ $sesi->kehadiran->count() }} Mhs Hadir</small>
                        
                        {{-- TAMPILAN UNTUK MAHASISWA --}}
                        @else
                            @if($sudahAbsen)
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Hadir</span>
                            @elseif($sesiAktif)
                                <form action="{{ route('verum.presensi.hadir', $sesi->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="bi bi-hand-index-thumb"></i> Klik Hadir
                                    </button>
                                </form>
                            @else
                                <span class="badge bg-danger">Sesi Ditutup</span>
                            @endif
                        @endif
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
@endif
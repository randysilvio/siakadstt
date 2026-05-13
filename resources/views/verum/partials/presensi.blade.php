{{-- HEADER PRESENSI --}}
<div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-dark border-2 pb-3">
    <div class="d-flex align-items-center">
        <div class="bg-dark text-white rounded-0 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
            <i class="bi bi-calendar-check-fill fs-5"></i>
        </div>
        <div>
            <h5 class="mb-0 fw-bold text-dark uppercase">Presensi Kehadiran Mahasiswa</h5>
            <span class="text-muted small uppercase font-monospace">
                WAKTU SERVER AKTIF: <strong class="text-dark">{{ now()->format('H:i') }}</strong> WIT
            </span>
        </div>
    </div>
    
    {{-- Eksekusi Buka Presensi Dosen --}}
    @if(Auth::user()->hasRole('dosen'))
        <button class="btn btn-sm btn-dark rounded-0 px-4 uppercase fw-bold small shadow-sm py-2" data-bs-toggle="modal" data-bs-target="#bukaPresensiModal">
            <i class="bi bi-qr-code-scan me-2 text-white align-middle"></i> Buka Sesi Presensi
        </button>
    @endif
</div>

{{-- NOTIFIKASI STATUS --}}
@if(session('error'))
    <div class="alert alert-danger border rounded-0 p-3 mb-4 font-monospace small uppercase shadow-sm">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
    </div>
@endif
@if(session('success'))
    <div class="alert alert-success border rounded-0 p-3 mb-4 font-monospace small uppercase shadow-sm">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    </div>
@endif

{{-- DAFTAR SESI PERTEMUAN FLAT --}}
@if($verum_kela->presensi->isEmpty())
    <div class="text-center py-5 bg-light border border-dark border-opacity-25 rounded-0 mb-5">
        <i class="bi bi-calendar-x fs-2 d-block mb-2 text-dark opacity-25"></i>
        <p class="text-muted small uppercase fw-bold mb-0">Belum ada pencatatan jadwal pertemuan yang didaftarkan pada kelas ini.</p>
    </div>
@else
    <div class="d-grid gap-2 mb-5">
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
            
            {{-- Blok Baris Pertemuan Bersudut Siku --}}
            <div class="card border border-dark border-opacity-25 rounded-0 shadow-none {{ $sesiAktif ? 'border-start border-success border-5 bg-success bg-opacity-10' : 'bg-white' }}">
                <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <div class="mb-3 mb-md-0">
                        <div class="d-flex align-items-center mb-1">
                            <h6 class="mb-0 fw-bold text-dark uppercase fs-6">
                                PERTEMUAN KE-{{ font_monospace($sesi->pertemuan_ke) }}
                            </h6>
                            @if($sesiAktif)
                                <span class="badge bg-danger text-white rounded-0 font-monospace uppercase fw-bold ms-3 px-2 py-1 blink" style="font-size: 10px;">LIVE SESSION</span>
                            @endif
                        </div>
                        <p class="text-dark small uppercase fw-bold mb-1">
                            {{ $sesi->judul_pertemuan }}
                        </p>
                        <span class="text-muted font-monospace uppercase d-block" style="font-size: 11px;">
                            ALOKASI WAKTU: {{ $sesi->waktu_buka->format('H:i') }} - {{ $sesi->waktu_tutup ? $sesi->waktu_tutup->format('H:i') : 'SELESAI' }} WIT
                        </span>
                    </div>

                    {{-- Komponen Aksi Mandiri / Laporan Dosen --}}
                    <div class="text-start text-md-end w-100 w-md-auto border-top border-md-top-0 pt-3 pt-md-0 mt-2 mt-md-0">
                        @if(Auth::user()->hasRole('dosen'))
                            <span class="badge {{ $sesiAktif ? 'bg-success text-white' : 'border border-dark text-muted' }} rounded-0 font-monospace uppercase fw-bold px-3 py-1 d-block d-md-inline-block mb-2 text-center" style="font-size: 10px;">
                                {{ $sesiAktif ? 'SESI BERLANGSUNG' : 'DITUTUP PERMANEN' }}
                            </span>
                            <div class="font-monospace text-dark fw-bold fs-6">
                                TOTAL KEHADIRAN: <strong class="text-primary">{{ $sesi->kehadiran->count() }}</strong> MHS
                            </div>
                        @else
                            @if($sudahAbsen)
                                <button class="btn btn-success rounded-0 py-2 px-4 uppercase fw-bold small text-white w-100 w-md-auto disabled shadow-none" style="cursor: default;">
                                    <i class="bi bi-check-lg me-2 align-middle"></i> Terverifikasi Hadir
                                </button>
                            @elseif($sesiAktif)
                                <form action="{{ route('verum.presensi.hadir', $sesi->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary rounded-0 py-2 px-5 uppercase fw-bold small shadow-sm w-100 w-md-auto text-center">
                                        <i class="bi bi-hand-index-thumb-fill me-2 align-middle text-white"></i> Konfirmasi Kehadiran
                                    </button>
                                </form>
                            @else
                                <span class="badge bg-secondary text-white rounded-0 font-monospace uppercase fw-bold px-4 py-2 w-100 w-md-auto text-center d-block" style="font-size: 11px;">
                                    SESI DITUTUP
                                </span>
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
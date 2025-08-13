<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Presensi (Kehadiran)</h4>
    @if(Auth::user()->role == 'dosen')
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#bukaPresensiModal">+ Buka Sesi Presensi</button>
    @endif
</div>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($verum_kela->presensi->isEmpty())
    <p class="text-center text-muted"><i>Belum ada sesi presensi yang dibuka.</i></p>
@else
    <ul class="list-group">
        @foreach($verum_kela->presensi->sortByDesc('pertemuan_ke') as $sesi)
            @php
                $sesiAktif = now()->between($sesi->waktu_buka, $sesi->waktu_tutup);
                $sudahAbsen = false;
                if(Auth::user()->role == 'mahasiswa') {
                    $sudahAbsen = $sesi->kehadiran->where('mahasiswa_id', Auth::user()->mahasiswa->id)->isNotEmpty();
                }
            @endphp
            <li class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Pertemuan {{ $sesi->pertemuan_ke }}: {{ $sesi->judul_pertemuan }}</strong>
                        <p class="mb-1 text-muted">
                            Dibuka pada {{ $sesi->waktu_buka->format('d M Y, H:i') }}
                            @if($sesi->waktu_tutup)
                                - Ditutup pada {{ $sesi->waktu_tutup->format('H:i') }}
                            @endif
                        </p>
                    </div>
                    <div>
                        @if(Auth::user()->role == 'dosen')
                            <span class="badge bg-{{ $sesiAktif ? 'success' : 'secondary' }}">
                                {{ $sesiAktif ? 'Sedang Berlangsung' : 'Selesai' }}
                            </span>
                        @else {{-- Tampilan Mahasiswa --}}
                            @if($sudahAbsen)
                                <span class="badge bg-success">Anda Sudah Absen</span>
                            @elseif($sesiAktif)
                                <form action="{{ route('verum.presensi.hadir', $sesi) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">Catat Kehadiran</button>
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

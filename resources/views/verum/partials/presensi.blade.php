<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 border-bottom border-dark border-2 pb-3 gap-3">
    <div class="d-flex align-items-center">
        <div class="bg-dark text-white rounded-0 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
            <i class="bi bi-person-check-fill fs-5"></i>
        </div>
        <div>
            <h5 class="mb-0 fw-bold text-dark uppercase">Presensi & Jurnal Berita Acara Perkuliahan (BAP)</h5>
            <span class="text-muted small uppercase font-monospace">Pencatatan Kehadiran Real-Time & Realisasi Materi RPS Harian</span>
        </div>
    </div>
    
    @if(Auth::user()->hasRole('dosen'))
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('verum.presensi.cetakBap', $verum_kela->id) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-0 px-3 py-2 uppercase fw-bold small shadow-sm">
                <i class="bi bi-journal-text me-1"></i> Cetak BAP (Jurnal)
            </a>
            <a href="{{ route('verum.presensi.cetak', $verum_kela->id) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-0 px-3 py-2 uppercase fw-bold small shadow-sm">
                <i class="bi bi-printer-fill me-1"></i> Cetak Matriks Absen
            </a>
            <button class="btn btn-sm btn-dark rounded-0 px-4 py-2 uppercase fw-bold small shadow-sm" data-bs-toggle="modal" data-bs-target="#bukaPresensiModal">
                <i class="bi bi-plus-lg me-1 text-white"></i> Buka Sesi & BAP
            </button>
        </div>
    @endif
</div>

@if($verum_kela->presensi->isEmpty())
    <div class="text-center py-5 bg-light border border-dark border-opacity-25 rounded-0 mb-5">
        <i class="bi bi-calendar-x fs-2 d-block mb-2 text-dark opacity-25"></i>
        <p class="text-muted small uppercase fw-bold mb-0">Belum ada sesi presensi atau jurnal perkuliahan yang dibuka pada kelas ini.</p>
    </div>
@else
    <div class="row g-3 mb-5">
        @foreach($verum_kela->presensi->sortByDesc('pertemuan_ke') as $sesi)
            @php
                $isOpen = now()->between($sesi->waktu_buka, $sesi->waktu_tutup);
                $sudahHadir = false;
                if (Auth::user()->hasRole('mahasiswa') && Auth::user()->mahasiswa) {
                    $sudahHadir = $sesi->kehadiran->where('mahasiswa_id', Auth::user()->mahasiswa->id)->isNotEmpty();
                }
            @endphp
            <div class="col-12">
                <div class="card border border-dark border-opacity-25 rounded-0 shadow-none bg-white border-start border-4 {{ $isOpen ? 'border-start-success' : 'border-start-dark' }}">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div>
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <span class="badge bg-dark text-white rounded-0 font-monospace">PERTEMUAN KE-{{ $sesi->pertemuan_ke }}</span>
                                    <span class="badge bg-light text-dark border rounded-0 font-monospace">{{ strtoupper($sesi->metode_pembelajaran ?? 'TATAP MUKA / DISKUSI') }}</span>
                                    @if($isOpen)
                                        <span class="badge bg-success text-white rounded-0 font-monospace">SESI SEDANG DIBUKA</span>
                                    @else
                                        <span class="badge bg-secondary text-white rounded-0 font-monospace">SESI DITUTUP</span>
                                    @endif
                                </div>
                                <h6 class="fw-bold text-dark uppercase mb-1">{{ $sesi->judul_pertemuan }}</h6>
                                @if($sesi->materi_aktual)
                                    <p class="text-muted small mb-2"><strong>Realisasi Materi / Sub-CPMK:</strong> {{ $sesi->materi_aktual }}</p>
                                @endif
                                <div class="text-muted font-monospace" style="font-size: 11px;">
                                    <i class="bi bi-clock me-1"></i> {{ $sesi->waktu_buka->translatedFormat('d M Y, H:i') }} s/d {{ $sesi->waktu_tutup->format('H:i') }} WIT |
                                    <strong class="text-dark">HADIR: {{ $sesi->kehadiran->count() }} MAHASISWA</strong>
                                </div>
                            </div>

                            <div class="text-md-end">
                                @if(Auth::user()->hasRole('mahasiswa'))
                                    @if($sudahHadir)
                                        <span class="btn btn-sm btn-success rounded-0 px-4 py-2 uppercase fw-bold small disabled">
                                            <i class="bi bi-check-circle-fill me-1"></i> Kehadiran Tercatat
                                        </span>
                                    @elseif($isOpen)
                                        <form action="{{ route('verum.presensi.hadir', $sesi->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary rounded-0 px-4 py-2 uppercase fw-bold small shadow-sm">
                                                <i class="bi bi-hand-index-thumb-fill me-1"></i> Konfirmasi Hadir Sekarang
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge bg-danger rounded-0 px-3 py-2 font-monospace uppercase">TIDAK HADIR / SESI TUTUP</span>
                                    @endif
                                @elseif(Auth::user()->hasRole('dosen'))
                                    <button type="button" class="btn btn-sm btn-outline-dark rounded-0 px-3 py-1 uppercase fw-bold small" data-bs-toggle="modal" data-bs-target="#editBapModal-{{ $sesi->id }}">
                                        <i class="bi bi-pencil-square me-1"></i> Edit Jurnal BAP
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if(Auth::user()->hasRole('dosen'))
                <div class="modal fade" id="editBapModal-{{ $sesi->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content rounded-0 border-0 border-top border-dark border-4">
                            <form action="{{ route('verum.presensi.updateJurnal', $sesi->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <div class="modal-header bg-dark text-white rounded-0 py-3">
                                    <h6 class="modal-title fw-bold uppercase small">Edit Jurnal BAP Pertemuan Ke-{{ $sesi->pertemuan_ke }}</h6>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold uppercase text-dark">Topik / Pokok Bahasan <span class="text-danger">*</span></label>
                                        <input type="text" name="judul_pertemuan" class="form-control rounded-0" value="{{ $sesi->judul_pertemuan }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold uppercase text-dark">Metode Pembelajaran</label>
                                        <select name="metode_pembelajaran" class="form-select rounded-0 uppercase small fw-bold">
                                            @foreach(['Tatap Muka / Ceramah & Diskusi', 'Daring Sinkronus (Live Video)', 'Diskusi Kelompok / Studi Kasus', ' Seminar / Presentasi Mahasiswa', 'Praktikum / Lapangan'] as $mtd)
                                                <option value="{{ $mtd }}" {{ ($sesi->metode_pembelajaran ?? '') == $mtd ? 'selected' : '' }}>{{ $mtd }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label small fw-bold uppercase text-dark">Uraian Materi Aktual & Capaian Sub-CPMK</label>
                                        <textarea name="materi_aktual" rows="3" class="form-control rounded-0" placeholder="Catat ringkasan materi yang disampaikan dan situasi kelas...">{{ $sesi->materi_aktual }}</textarea>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light border-top rounded-0">
                                    <button type="button" class="btn btn-sm btn-outline-dark rounded-0 uppercase fw-bold small" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm">Simpan Jurnal BAP</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@endif
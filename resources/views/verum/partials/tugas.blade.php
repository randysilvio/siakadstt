<div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-dark border-2 pb-3">
    <div class="d-flex align-items-center">
        <div class="bg-dark text-white rounded-0 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
            <i class="bi bi-clipboard-check-fill fs-5"></i>
        </div>
        <div>
            <h5 class="mb-0 fw-bold text-dark uppercase">Penugasan & Evaluasi Kelas</h5>
            <span class="text-muted small uppercase font-monospace">Pusat Distribusi Instruksi & Pengumpulan Lembar Jawaban</span>
        </div>
    </div>
    
    @if(Auth::user()->hasRole('dosen'))
        <button class="btn btn-sm btn-dark rounded-0 px-4 uppercase fw-bold small shadow-sm py-2" data-bs-toggle="modal" data-bs-target="#tambahTugasModal">
            <i class="bi bi-plus-lg me-2 text-white align-middle"></i> Distribusikan Tugas
        </button>
    @endif
</div>

@if($verum_kela->tugas->isEmpty())
    <div class="text-center py-5 bg-light border border-dark border-opacity-25 rounded-0 mb-5">
        <i class="bi bi-clipboard-x fs-2 d-block mb-2 text-dark opacity-25"></i>
        <p class="text-muted small uppercase fw-bold mb-0">Belum ada modul penugasan yang aktif pada kelas perkuliahan ini.</p>
    </div>
@else
    <div class="accordion shadow-none mb-5" id="accordionTugas">
        @foreach($verum_kela->tugas as $tugas)
            @php
                $isLate = now() > $tugas->tenggat_waktu;
                $sudahKumpul = false;
                $fileKumpul = null;
                if(Auth::user()->hasRole('mahasiswa') && Auth::user()->mahasiswa) {
                    $kumpulan = $tugas->pengumpulan->where('mahasiswa_id', Auth::user()->mahasiswa->id)->first();
                    if($kumpulan) {
                        $sudahKumpul = true;
                        $fileKumpul = $kumpulan;
                    }
                }
            @endphp
            <div class="accordion-item border border-dark border-opacity-25 rounded-0 bg-white mb-3 shadow-none">
                <h2 class="accordion-header" id="heading{{ $tugas->id }}">
                    <button class="accordion-button collapsed bg-light rounded-0 shadow-none p-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $tugas->id }}">
                        <div class="d-flex flex-column flex-md-row w-100 justify-content-between align-items-start align-items-md-center me-3">
                            <span class="fw-bold text-dark uppercase fs-6 mb-1 mb-md-0">
                                {{ $tugas->judul }}
                                @if($sudahKumpul && $fileKumpul->nilai !== null)
                                    <span class="badge bg-success text-white rounded-0 ms-2 font-monospace">NILAI: {{ number_format($fileKumpul->nilai, 1) }}</span>
                                @endif
                            </span>
                            <span class="font-monospace uppercase fw-bold {{ $isLate ? 'text-danger' : 'text-primary' }}" style="font-size: 11px;">
                                BATAS PENGUMPULAN: {{ $tugas->tenggat_waktu->format('d/m/Y - H:i') }} WIT
                            </span>
                        </div>
                    </button>
                </h2>
                <div id="collapse{{ $tugas->id }}" class="accordion-collapse collapse rounded-0" data-bs-parent="#accordionTugas">
                    <div class="accordion-body p-4 border-top border-dark border-opacity-25">
                        
                        <h6 class="fw-bold text-dark uppercase small mb-2">Instruksi Pengerjaan:</h6>
                        <div class="bg-light p-4 border border-dark border-opacity-25 rounded-0 text-dark small uppercase mb-4" style="line-height: 1.75; text-align: justify;">
                            {!! nl2br(e($tugas->instruksi)) !!}
                        </div>
                        
                        @if(Auth::user()->hasRole('mahasiswa'))
                            <div class="card bg-white border border-dark border-opacity-25 rounded-0 shadow-none border-top border-dark border-4">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold text-dark uppercase small mb-3">
                                        <i class="bi bi-cloud-arrow-up-fill me-2 align-middle text-primary fs-5"></i>Status Pengiriman & Evaluasi Jawaban
                                    </h6>
                                    
                                    @if($sudahKumpul)
                                        <div class="alert alert-success border rounded-0 p-3 mb-3 font-monospace small uppercase shadow-sm">
                                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                                                <div>
                                                    <i class="bi bi-check-circle-fill me-2"></i> TERCATAT PADA: {{ $fileKumpul->waktu_pengumpulan->format('d/m/Y H:i') }} WIT
                                                    @if(!empty($fileKumpul->file_path))
                                                        <div class="mt-2">
                                                            <a href="{{ route('berkas.unduh', ['jenis' => 'jawaban-tugas', 'id' => $fileKumpul->id]) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-0 fw-bold">
                                                                <i class="bi bi-download me-1"></i> Unduh Berkas Jawaban Saya
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="text-md-end">
                                                    <span class="d-block text-muted" style="font-size: 10px;">SKOR PENILAIAN DOSEN</span>
                                                    @if($fileKumpul->nilai !== null)
                                                        <span class="fs-4 fw-bold text-dark">{{ number_format($fileKumpul->nilai, 2) }} / 100</span>
                                                    @else
                                                        <span class="badge bg-warning text-dark rounded-0">MENUNGGU PENILAIAN</span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if(!empty($fileKumpul->catatan_dosen))
                                                <div class="mt-3 pt-2 border-top border-success border-opacity-25 text-dark">
                                                    <strong>CATATAN DOSEN:</strong> "{{ $fileKumpul->catatan_dosen }}"
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    @if(!$isLate)
                                        <form action="{{ route('verum.tugas.kumpulkan', $tugas) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <label class="form-label small fw-bold uppercase text-dark">
                                                {{ $sudahKumpul ? 'Perbarui / Unggah Ulang Berkas Jawaban (Sebelum Tenggat)' : 'Unggah Berkas Jawaban' }}
                                            </label>
                                            <div class="input-group rounded-0 mb-2">
                                                <input type="file" class="form-control rounded-0 @error('file_jawaban') is-invalid @enderror" name="file_jawaban" accept=".pdf,.doc,.docx,.zip" required>
                                                <button class="btn btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm" type="submit">
                                                    {{ $sudahKumpul ? 'Perbarui Berkas' : 'Kirim Berkas' }}
                                                </button>
                                            </div>
                                            @error('file_jawaban')
                                                <div class="text-danger small font-monospace fw-bold mb-2">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text text-muted small uppercase font-monospace">
                                                Format diizinkan: PDF, DOCX, ZIP. Batas ukuran maksimal: 10MB.
                                            </div>
                                        </form>
                                    @elseif(!$sudahKumpul)
                                        <div class="alert alert-danger border rounded-0 p-3 mb-0 font-monospace small uppercase">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i> PERINGATAN: Batas waktu pengiriman telah terlewati. Sistem menolak pengiriman baru.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="d-flex justify-content-end pt-3 mt-2 border-top border-dark border-opacity-25">
                                <form action="{{ route('verum.tugas.destroy', $tugas->id) }}" method="POST" class="me-2" onsubmit="return confirm('Hapus tugas ini beserta seluruh file jawaban mahasiswa secara permanen?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger rounded-0 px-4 py-2 uppercase fw-bold small shadow-sm text-center border-2">
                                        <i class="bi bi-trash-fill me-2 align-middle"></i> Hapus Tugas
                                    </button>
                                </form>

                                <a href="{{ route('verum.tugas.rekap', $tugas->id) }}" class="btn btn-dark rounded-0 px-4 py-2 uppercase fw-bold small shadow-sm text-center">
                                    <i class="bi bi-folder-check me-2 align-middle text-white"></i> Rekapitulasi & Beri Nilai ({{ $tugas->pengumpulan->count() }})
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
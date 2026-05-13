{{-- HEADER TUGAS --}}
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
    
    {{-- Buat Tugas Khusus Dosen --}}
    @if(Auth::user()->hasRole('dosen'))
        <button class="btn btn-sm btn-dark rounded-0 px-4 uppercase fw-bold small shadow-sm py-2" data-bs-toggle="modal" data-bs-target="#tambahTugasModal">
            <i class="bi bi-plus-lg me-2 text-white align-middle"></i> Distribusikan Tugas
        </button>
    @endif
</div>

{{-- DAFTAR TUGAS AKORDION FLAT --}}
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
            @endphp
            {{-- Wadah Akordion Siku Tajam --}}
            <div class="accordion-item border border-dark border-opacity-25 rounded-0 bg-white mb-3 shadow-none">
                <h2 class="accordion-header" id="heading{{ $tugas->id }}">
                    <button class="accordion-button collapsed bg-light rounded-0 shadow-none p-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $tugas->id }}">
                        <div class="d-flex flex-column flex-md-row w-100 justify-content-between align-items-start align-items-md-center me-3">
                            <span class="fw-bold text-dark uppercase fs-6 mb-1 mb-md-0">{{ $tugas->judul }}</span>
                            <span class="font-monospace uppercase fw-bold {{ $isLate ? 'text-danger' : 'text-primary' }}" style="font-size: 11px;">
                                BATAS PENGUMPULAN: {{ $tugas->tenggat_waktu->format('d/m/Y - H:i') }} WIT
                            </span>
                        </div>
                    </button>
                </h2>
                <div id="collapse{{ $tugas->id }}" class="accordion-collapse collapse rounded-0" data-bs-parent="#accordionTugas">
                    <div class="accordion-body p-4 border-top border-dark border-opacity-25">
                        
                        {{-- Blok Instruksi --}}
                        <h6 class="fw-bold text-dark uppercase small mb-2">Instruksi Pengerjaan:</h6>
                        <div class="bg-light p-4 border border-dark border-opacity-25 rounded-0 text-dark small uppercase mb-4" style="line-height: 1.75; text-align: justify;">
                            {!! nl2br(e($tugas->instruksi)) !!}
                        </div>
                        
                        {{-- Pengumpulan Mahasiswa --}}
                        @if(Auth::user()->hasRole('mahasiswa'))
                            <div class="card bg-white border border-dark border-opacity-25 rounded-0 shadow-none border-top border-dark border-4">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold text-dark uppercase small mb-3">
                                        <i class="bi bi-cloud-arrow-up-fill me-2 align-middle text-primary fs-5"></i>Formulir Pengiriman Jawaban
                                    </h6>
                                    
                                    @if($isLate)
                                        <div class="alert alert-danger border rounded-0 p-3 mb-3 font-monospace small uppercase">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i> PERINGATAN: Batas waktu pengiriman telah terlewati. Sistem akan menandai berkas sebagai keterlambatan.
                                        </div>
                                    @endif
                                    
                                    <form action="{{ route('verum.tugas.kumpulkan', $tugas) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="input-group rounded-0 mb-2">
                                            <input type="file" class="form-control rounded-0" name="file_jawaban" accept=".pdf,.doc,.docx" required>
                                            <button class="btn btn-primary rounded-0 px-4 uppercase fw-bold small shadow-sm" type="submit">
                                                Kirim Berkas
                                            </button>
                                        </div>
                                        <div class="form-text text-muted small uppercase font-monospace">
                                            Format diizinkan: PDF, DOCX. Batas ukuran maksimal: 5MB.
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @else
                            {{-- Akses Rekapitulasi Dosen --}}
                            <div class="d-flex justify-content-end pt-2 border-top border-dark border-opacity-25">
                                <a href="#" class="btn btn-dark rounded-0 px-4 py-2 uppercase fw-bold small shadow-sm text-center">
                                    <i class="bi bi-folder-check me-2 align-middle text-white"></i> Rekapitulasi Pengumpulan Mahasiswa
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

{{-- Fungsi kustom untuk memastikan angka pertemuan tetap monospace jika dipanggil via fungsi eksternal --}}
@php
function font_monospace($val) {
    return '<span class="font-monospace">' . $val . '</span>';
}
@endphp
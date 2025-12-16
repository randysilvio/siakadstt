<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <div class="bg-danger bg-opacity-10 text-danger rounded p-2 me-3">
            <i class="bi bi-clipboard-check-fill fs-4"></i>
        </div>
        <div>
            <h5 class="mb-0 fw-bold text-dark">Tugas & Evaluasi</h5>
            <small class="text-muted">Pengumpulan tugas kelas.</small>
        </div>
    </div>
    @if(Auth::user()->hasRole('dosen'))
        <button class="btn btn-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#tambahTugasModal">
            <i class="bi bi-plus-lg me-1"></i> Buat Tugas
        </button>
    @endif
</div>

@if($verum_kela->tugas->isEmpty())
    <div class="text-center py-5 border rounded bg-light">
        <i class="bi bi-clipboard-x fs-1 text-secondary opacity-50 mb-2"></i>
        <p class="text-muted mb-0">Belum ada tugas yang diberikan.</p>
    </div>
@else
    <div class="accordion shadow-sm" id="accordionTugas">
        @foreach($verum_kela->tugas as $tugas)
            @php
                $isLate = now() > $tugas->tenggat_waktu;
            @endphp
            <div class="accordion-item border-0 border-bottom mb-2 bg-white rounded overflow-hidden">
                <h2 class="accordion-header" id="heading{{ $tugas->id }}">
                    <button class="accordion-button collapsed bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $tugas->id }}">
                        <div class="d-flex flex-column flex-md-row w-100 justify-content-between align-items-md-center me-3">
                            <span class="fw-bold text-dark">{{ $tugas->judul }}</span>
                            <small class="{{ $isLate ? 'text-danger fw-bold' : 'text-success' }}">
                                <i class="bi bi-clock me-1"></i> Tenggat: {{ $tugas->tenggat_waktu->format('d M, H:i') }}
                            </small>
                        </div>
                    </button>
                </h2>
                <div id="collapse{{ $tugas->id }}" class="accordion-collapse collapse" data-bs-parent="#accordionTugas">
                    <div class="accordion-body">
                        <h6 class="fw-bold text-secondary text-uppercase small ls-1 mb-2">Instruksi Pengerjaan</h6>
                        <div class="bg-light p-3 rounded mb-4 border">
                            {!! nl2br(e($tugas->instruksi)) !!}
                        </div>
                        
                        @if(Auth::user()->hasRole('mahasiswa'))
                            <div class="card bg-white border">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3"><i class="bi bi-upload me-2"></i>Pengumpulan Tugas</h6>
                                    @if($isLate)
                                        <div class="alert alert-warning py-2 small mb-3">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Waktu pengumpulan telah lewat.
                                        </div>
                                    @endif
                                    
                                    <form action="{{ route('verum.tugas.kumpulkan', $tugas) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="input-group mb-3">
                                            <input type="file" class="form-control" name="file_jawaban" required>
                                            <button class="btn btn-primary" type="submit">Kirim Jawaban</button>
                                        </div>
                                        <div class="form-text">Format: PDF, DOCX (Maks. 5MB)</div>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-info text-white shadow-sm">
                                    <i class="bi bi-folder2-open me-1"></i> Lihat Semua Pengumpulan
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
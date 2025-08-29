<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Tugas Mahasiswa</h4>
    @if(Auth::user()->hasRole('dosen'))
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tambahTugasModal">+ Buat Tugas</button>
    @endif
</div>

@if($verum_kela->tugas->isEmpty())
    <p class="text-center text-muted"><i>Belum ada tugas yang diberikan.</i></p>
@else
    <div class="accordion" id="accordionTugas">
        @foreach($verum_kela->tugas as $tugas)
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading{{ $tugas->id }}">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $tugas->id }}" aria-expanded="false" aria-controls="collapse{{ $tugas->id }}">
                        {{ $tugas->judul }} - <span class="ms-2 {{ $tugas->tenggat_waktu < now() ? 'text-danger' : 'text-success' }}">Tenggat: {{ $tugas->tenggat_waktu->format('d M Y, H:i') }}</span>
                    </button>
                </h2>
                <div id="collapse{{ $tugas->id }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $tugas->id }}" data-bs-parent="#accordionTugas">
                    <div class="accordion-body">
                        <p><strong>Instruksi:</strong><br>{!! nl2br(e($tugas->instruksi)) !!}</p>
                        <hr>
                        @if(Auth::user()->hasRole('mahasiswa'))
                            <form action="{{ route('verum.tugas.kumpulkan', $tugas) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="file_jawaban_{{ $tugas->id }}" class="form-label">Unggah Jawaban Anda (PDF, DOC, DOCX)</label>
                                    <input class="form-control" type="file" id="file_jawaban_{{ $tugas->id }}" name="file_jawaban" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Kumpulkan Tugas</button>
                            </form>
                        @else {{-- Tampilan Dosen --}}
                            <a href="#" class="btn btn-info">Lihat Pengumpulan</a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

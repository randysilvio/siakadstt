@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="mb-4 mt-3">
        <a href="{{ route('evaluasi.index') }}" class="btn btn-outline-dark btn-sm rounded-0 mb-3 uppercase small">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        <h3 class="fw-bold text-dark mb-0 uppercase">Instrumen Penilaian Kinerja Dosen</h3>
        <p class="text-muted small">Pastikan Anda mengisi seluruh indikator penilaian dengan jujur dan bertanggung jawab.</p>
    </div>

    {{-- HEADER MATA KULIAH --}}
    <div class="card border-0 shadow-sm mb-4 rounded-0 bg-dark text-white">
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-8">
                    <h5 class="fw-bold mb-1 uppercase">{{ $mataKuliah->nama_mk }}</h5>
                    <span class="font-monospace text-white-50">{{ $mataKuliah->kode_mk }}</span>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0 border-start-md border-white-50 ps-md-4">
                    <span class="text-white-50 small d-block mb-1">DOSEN PENGAMPU:</span>
                    <span class="fw-bold">{{ strtoupper($mataKuliah->dosen->nama_lengkap) }}</span>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('evaluasi.store', $mataKuliah->id) }}" method="POST">
        @csrf
        <input type="hidden" name="sesi_id" value="{{ $sesiAktif->id }}">

        <div class="card border-0 shadow-sm rounded-0">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark uppercase small">Daftar Indikator Evaluasi</h6>
            </div>
            <div class="card-body p-4">
                @foreach ($pertanyaan as $index => $p)
                    <div class="mb-5">
                        <label class="form-label d-block text-dark fw-bold mb-3">
                            {{ $index + 1 }}. {{ $p->pertanyaan }}
                        </label>

                        @if ($p->tipe_jawaban == 'skala_1_5')
                            <div class="d-flex align-items-center gap-4 bg-light p-3 border rounded-0" style="max-width: 600px;">
                                <span class="small fw-bold text-danger uppercase" style="font-size: 0.7rem;">Kurang (1)</span>
                                
                                <div class="d-flex gap-3">
                                    @for ($i = 1; $i <= 4; $i++)
                                    <div class="form-check">
                                        <input class="form-check-input border-secondary" type="radio" name="jawaban[{{ $p->id }}]" id="skala_{{ $p->id }}_{{ $i }}" value="{{ $i }}" required>
                                        <label class="form-check-label fw-bold text-dark" for="skala_{{ $p->id }}_{{ $i }}">{{ $i }}</label>
                                    </div>
                                    @endfor
                                </div>
                                
                                <span class="small fw-bold text-success uppercase" style="font-size: 0.7rem;">Sangat Baik (4)</span>
                            </div>
                        @elseif ($p->tipe_jawaban == 'teks')
                            <textarea name="jawaban[{{ $p->id }}]" class="form-control rounded-0 border-secondary-subtle" rows="3" placeholder="Tuliskan umpan balik kualitatif atau saran konstruktif Anda..." required></textarea>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="card-footer bg-light py-3 text-end">
                <button type="submit" class="btn btn-primary px-5 rounded-0 fw-bold">SUBMIT EVALUASI</button>
            </div>
        </div>
    </form>
</div>
@endsection
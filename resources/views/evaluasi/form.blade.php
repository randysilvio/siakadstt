@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Instrumen Penilaian Kinerja Dosen <x-info-aturan kunci="edom" /></h3>
            <span class="text-muted small uppercase">Pastikan Anda mengisi seluruh indikator penilaian secara objektif</span>
        </div>
        <div>
            <a href="{{ route('evaluasi.index') }}" class="btn btn-outline-dark btn-sm rounded-0 px-3 uppercase fw-bold small">
                Kembali ke Daftar
            </a>
        </div>
    </div>

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- HEADER MATA KULIAH --}}
    <div class="card border-0 shadow-sm mb-4 rounded-0 bg-dark text-white">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge bg-secondary text-white rounded-0 font-monospace uppercase mb-2">{{ $mataKuliah->kode_mk }}</span>
                    <h5 class="fw-bold mb-0 uppercase">{{ $mataKuliah->nama_mk }}</h5>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0 border-start-md border-white-50 ps-md-4">
                    <span class="text-white-50 small font-monospace d-block mb-1">DOSEN PENGAMPU:</span>
                    <span class="fw-bold uppercase">{{ $mataKuliah->dosen->nama_lengkap }}</span>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('evaluasi.store', $mataKuliah->id) }}" method="POST">
        @csrf

        <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
            <div class="card-header bg-white py-3 border-bottom rounded-0">
                <h6 class="fw-bold mb-0 text-dark uppercase small">Daftar Indikator Evaluasi ({{ $sesiAktif->nama_sesi }})</h6>
            </div>
            <div class="card-body p-4">
                @foreach ($pertanyaan as $index => $p)
                    @php
                        $tersimpan = isset($jawabanTersimpan) && $jawabanTersimpan->has($p->id) ? $jawabanTersimpan->get($p->id) : null;
                        $oldVal = old("jawaban.{$p->id}");
                    @endphp
                    <div class="mb-4 pb-3 border-bottom">
                        <label class="form-label d-block text-dark fw-bold mb-3">
                            {{ $index + 1 }}. {{ $p->pertanyaan }} <span class="text-danger">*</span>
                        </label>

                        @if ($p->tipe_jawaban == 'skala_1_5')
                            @php
                                $selectedScale = $oldVal !== null ? (int) $oldVal : ($tersimpan ? (int) $tersimpan->jawaban_skala : null);
                            @endphp
                            <div class="d-flex flex-wrap align-items-center gap-4 bg-light p-3 border rounded-0" style="max-width: 600px;">
                                <span class="small fw-bold text-danger uppercase" style="font-size: 0.7rem;">Kurang (1)</span>
                                
                                <div class="d-flex gap-4">
                                    @for ($i = 1; $i <= 4; $i++)
                                    <div class="form-check mb-0">
                                        <input class="form-check-input border-dark" type="radio" name="jawaban[{{ $p->id }}]" id="skala_{{ $p->id }}_{{ $i }}" value="{{ $i }}" {{ $selectedScale === $i ? 'checked' : '' }} required>
                                        <label class="form-check-label fw-bold font-monospace text-dark" for="skala_{{ $p->id }}_{{ $i }}">{{ $i }}</label>
                                    </div>
                                    @endfor
                                </div>
                                
                                <span class="small fw-bold text-success uppercase" style="font-size: 0.7rem;">Sangat Baik (4)</span>
                            </div>
                        @elseif ($p->tipe_jawaban == 'teks')
                            @php
                                $textVal = $oldVal !== null ? $oldVal : ($tersimpan ? $tersimpan->jawaban_teks : '');
                            @endphp
                            <textarea name="jawaban[{{ $p->id }}]" class="form-control rounded-0 border-secondary-subtle" rows="3" placeholder="Tuliskan umpan balik kualitatif atau saran konstruktif Anda..." required>{{ $textVal }}</textarea>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="card-footer bg-light py-3 text-end rounded-0">
                <a href="{{ route('evaluasi.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 py-2 uppercase fw-bold small me-2">Batal</a>
                <button type="submit" class="btn btn-sm btn-primary px-5 py-2 rounded-0 uppercase fw-bold small shadow-sm">
                    <i class="bi bi-send-fill me-1"></i> Simpan Evaluasi
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
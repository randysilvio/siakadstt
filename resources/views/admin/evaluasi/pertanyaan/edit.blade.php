@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Edit Instrumen Pertanyaan</h3>
            <span class="text-muted small uppercase">Modifikasi Butir Pertanyaan Kuesioner Evaluasi Dosen</span>
        </div>
        <div>
            <a href="{{ route('admin.evaluasi-pertanyaan.index') }}" class="btn btn-outline-dark btn-sm rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-lg-8 mx-auto mb-5">
        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
            Formulir Pembaruan Pertanyaan Evaluasi
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.evaluasi-pertanyaan.update', $pertanyaan->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="pertanyaan" class="form-label text-dark small fw-bold uppercase">Deskripsi Pertanyaan <span class="text-danger">*</span></label>
                    <textarea class="form-control rounded-0 @error('pertanyaan') is-invalid @enderror" id="pertanyaan" name="pertanyaan" rows="3" required autofocus>{{ old('pertanyaan', $pertanyaan->pertanyaan) }}</textarea>
                    @error('pertanyaan')
                        <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row mb-4 g-4">
                    <div class="col-md-8">
                        <label for="tipe_jawaban" class="form-label text-dark small fw-bold uppercase">Format Jawaban <span class="text-danger">*</span></label>
                        <select class="form-select rounded-0 uppercase @error('tipe_jawaban') is-invalid @enderror" id="tipe_jawaban" name="tipe_jawaban" required>
                            <option value="skala_1_5" {{ old('tipe_jawaban', $pertanyaan->tipe_jawaban) == 'skala_1_5' ? 'selected' : '' }}>Skala Penilaian Numerik (1 - 4)</option>
                            <option value="teks" {{ old('tipe_jawaban', $pertanyaan->tipe_jawaban) == 'teks' ? 'selected' : '' }}>Uraian Kualitatif (Teks / Esai)</option>
                        </select>
                        @error('tipe_jawaban')
                            <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="urutan" class="form-label text-dark small fw-bold uppercase">Nomor Urut Tampil <span class="text-danger">*</span></label>
                        <input type="number" class="form-control rounded-0 text-center font-monospace @error('urutan') is-invalid @enderror" id="urutan" name="urutan" value="{{ old('urutan', $pertanyaan->urutan) }}" min="0" required>
                        @error('urutan')
                            <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4 bg-light p-3 border rounded-0">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $pertanyaan->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label text-dark fw-bold uppercase small ms-2" for="is_active">
                            Status Publikasi (Aktif)
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-end pt-3 border-top">
                    <a href="{{ route('admin.evaluasi-pertanyaan.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                        Simpan Pembaruan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
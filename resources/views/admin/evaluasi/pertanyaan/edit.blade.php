@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-0 text-dark fw-bold">Ubah Instrumen Pertanyaan</h3>
                    <span class="text-muted small">Modifikasi butir pertanyaan kuesioner evaluasi dosen</span>
                </div>
                <a href="{{ route('admin.evaluasi-pertanyaan.index') }}" class="btn btn-outline-dark btn-sm rounded-1">Batal</a>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('admin.evaluasi-pertanyaan.update', $pertanyaan->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="pertanyaan" class="form-label text-dark fw-semibold">Deskripsi Pertanyaan <span class="text-danger">*</span></label>
                            <textarea class="form-control rounded-1 @error('pertanyaan') is-invalid @enderror" id="pertanyaan" name="pertanyaan" rows="3" required autofocus>{{ old('pertanyaan', $pertanyaan->pertanyaan) }}</textarea>
                            @error('pertanyaan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-4 g-4">
                            <div class="col-md-8">
                                <label for="tipe_jawaban" class="form-label text-dark fw-semibold">Format Jawaban <span class="text-danger">*</span></label>
                                <select class="form-select rounded-1 @error('tipe_jawaban') is-invalid @enderror" id="tipe_jawaban" name="tipe_jawaban" required>
                                    <option value="skala_1_5" {{ old('tipe_jawaban', $pertanyaan->tipe_jawaban) == 'skala_1_5' ? 'selected' : '' }}>Skala Penilaian Numerik (1 - 4)</option>
                                    <option value="teks" {{ old('tipe_jawaban', $pertanyaan->tipe_jawaban) == 'teks' ? 'selected' : '' }}>Uraian Kualitatif (Teks / Esai)</option>
                                </select>
                                <div class="form-text mt-2 text-muted small">
                                    <i class="bi bi-info-circle me-1"></i> Mengubah format jawaban pada pertanyaan yang sudah berjalan dapat memengaruhi laporan evaluasi.
                                </div>
                                @error('tipe_jawaban')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="urutan" class="form-label text-dark fw-semibold">Nomor Urut Tampil <span class="text-danger">*</span></label>
                                <input type="number" class="form-control rounded-1 text-center font-monospace @error('urutan') is-invalid @enderror" id="urutan" name="urutan" value="{{ old('urutan', $pertanyaan->urutan) }}" min="0" required>
                                @error('urutan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4 bg-light p-3 border rounded-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $pertanyaan->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label text-dark fw-bold ms-2" for="is_active">
                                    Status Publikasi (Aktif)
                                </label>
                            </div>
                            <div class="form-text mt-2">Hapus centang untuk menyembunyikan pertanyaan ini dari kuesioner mahasiswa tanpa menghapus datanya.</div>
                        </div>

                        <hr class="text-muted my-4">
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-5">Simpan Pembaruan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
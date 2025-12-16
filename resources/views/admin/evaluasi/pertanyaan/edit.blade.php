@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0 fw-bold">Edit Pertanyaan</h2>
                <a href="{{ route('admin.evaluasi-pertanyaan.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="{{ route('admin.evaluasi-pertanyaan.update', $pertanyaan->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="pertanyaan" class="form-label fw-bold">Teks Pertanyaan</label>
                            <textarea class="form-control @error('pertanyaan') is-invalid @enderror" id="pertanyaan" name="pertanyaan" rows="3" required placeholder="Contoh: Dosen menyampaikan materi dengan jelas">{{ old('pertanyaan', $pertanyaan->pertanyaan) }}</textarea>
                            @error('pertanyaan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="tipe_jawaban" class="form-label fw-bold">Tipe Jawaban</label>
                                <select class="form-select @error('tipe_jawaban') is-invalid @enderror" id="tipe_jawaban" name="tipe_jawaban" required>
                                    {{-- Value tetap skala_1_5 agar database tidak error, tapi Label diganti --}}
                                    <option value="skala_1_5" {{ old('tipe_jawaban', $pertanyaan->tipe_jawaban) == 'skala_1_5' ? 'selected' : '' }}>Skala 1-4 (Tanpa Nilai Tengah)</option>
                                    <option value="teks" {{ old('tipe_jawaban', $pertanyaan->tipe_jawaban) == 'teks' ? 'selected' : '' }}>Teks (Esai Singkat)</option>
                                </select>
                                <div class="form-text text-muted small">
                                    <i class="bi bi-info-circle me-1"></i> Skala 1-4 menghindari jawaban netral (nilai tengah).
                                </div>
                                @error('tipe_jawaban')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="urutan" class="form-label fw-bold">Urutan Tampil</label>
                                <input type="number" class="form-control @error('urutan') is-invalid @enderror" id="urutan" name="urutan" value="{{ old('urutan', $pertanyaan->urutan) }}" min="0" required>
                                @error('urutan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $pertanyaan->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_active">
                                    Status Aktif
                                </label>
                            </div>
                            <div class="form-text">Jika dimatikan, pertanyaan tidak akan muncul di kuesioner mahasiswa.</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
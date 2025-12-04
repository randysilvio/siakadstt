@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Pertanyaan Evaluasi</h1>

    <div class="card">
        <div class="card-body">
            {{-- PERBAIKAN: Menambahkan 'admin.' pada route --}}
            <form action="{{ route('admin.evaluasi-pertanyaan.update', $pertanyaan->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="pertanyaan" class="form-label">Pertanyaan</label>
                    <textarea class="form-control @error('pertanyaan') is-invalid @enderror" id="pertanyaan" name="pertanyaan" rows="3" required>{{ old('pertanyaan', $pertanyaan->pertanyaan) }}</textarea>
                    @error('pertanyaan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="tipe_jawaban" class="form-label">Tipe Jawaban</label>
                    <select class="form-select @error('tipe_jawaban') is-invalid @enderror" id="tipe_jawaban" name="tipe_jawaban" required>
                        <option value="skala_1_5" {{ old('tipe_jawaban', $pertanyaan->tipe_jawaban) == 'skala_1_5' ? 'selected' : '' }}>Skala 1-5 (Sangat Buruk - Sangat Baik)</option>
                        <option value="teks" {{ old('tipe_jawaban', $pertanyaan->tipe_jawaban) == 'teks' ? 'selected' : '' }}>Teks (Esai Singkat)</option>
                    </select>
                    @error('tipe_jawaban')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="urutan" class="form-label">Urutan Tampil</label>
                    <input type="number" class="form-control @error('urutan') is-invalid @enderror" id="urutan" name="urutan" value="{{ old('urutan', $pertanyaan->urutan) }}" min="0" required>
                    @error('urutan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $pertanyaan->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Aktifkan Pertanyaan Ini?
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">Perbarui</button>
                
                {{-- PERBAIKAN: Menambahkan 'admin.' pada tombol Batal --}}
                <a href="{{ route('admin.evaluasi-pertanyaan.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection
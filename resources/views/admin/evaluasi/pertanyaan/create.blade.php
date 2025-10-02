@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Tambah Pertanyaan Evaluasi Baru</h1>

    <div class="card">
        <div class="card-body">
            {{-- PERBAIKAN: Menggunakan nama rute yang benar --}}
            <form action="{{ route('admin.evaluasi-pertanyaan.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="pertanyaan" class="form-label">Teks Pertanyaan</label>
                    <textarea class="form-control @error('pertanyaan') is-invalid @enderror" id="pertanyaan" name="pertanyaan" rows="3" required>{{ old('pertanyaan') }}</textarea>
                    @error('pertanyaan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tipe_jawaban" class="form-label">Tipe Jawaban</label>
                        <select class="form-select @error('tipe_jawaban') is-invalid @enderror" id="tipe_jawaban" name="tipe_jawaban" required>
                            <option value="skala_1_5" {{ old('tipe_jawaban') == 'skala_1_5' ? 'selected' : '' }}>Skala 1-5</option>
                            <option value="teks" {{ old('tipe_jawaban') == 'teks' ? 'selected' : '' }}>Teks</option>
                        </select>
                        @error('tipe_jawaban')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="urutan" class="form-label">Nomor Urut</label>
                        <input type="number" class="form-control @error('urutan') is-invalid @enderror" id="urutan" name="urutan" value="{{ old('urutan', 0) }}" required>
                        @error('urutan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Aktifkan Pertanyaan Ini?
                    </label>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
                {{-- PERBAIKAN: Menggunakan nama rute yang benar --}}
                <a href="{{ route('admin.evaluasi-pertanyaan.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection
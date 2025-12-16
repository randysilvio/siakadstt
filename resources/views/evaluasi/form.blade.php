@extends('layouts.app')

@section('styles')
<style>
    .rating-group {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    .rating-group .form-check {
        margin-bottom: 0;
    }
    /* Tambahan styling agar label lebih jelas */
    .scale-label {
        font-weight: bold;
        font-size: 0.9em;
        color: #555;
    }
</style>
@endsection

@section('content')
<div class="container">
    <h1 class="mb-2">Formulir Kuesioner Evaluasi Dosen</h1>
    <p class="text-muted">Isi kuesioner untuk mata kuliah di bawah ini.</p>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">{{ $mataKuliah->nama_mk }} ({{ $mataKuliah->kode_mk }})</h5>
            <p class="card-text mb-0"><strong>Dosen Pengampu:</strong> {{ $mataKuliah->dosen->nama_lengkap }}</p>
        </div>
    </div>

    <form action="{{ route('evaluasi.store', $mataKuliah->id) }}" method="POST">
        @csrf
        <input type="hidden" name="sesi_id" value="{{ $sesiAktif->id }}">

        <div class="card">
            <div class="card-header">
                Daftar Pertanyaan
            </div>
            <div class="card-body">
                @foreach ($pertanyaan as $index => $p)
                    <div class="mb-4">
                        <p><strong>{{ $index + 1 }}. {{ $p->pertanyaan }}</strong></p>

                        @if ($p->tipe_jawaban == 'skala_1_5') {{-- Nama tipe di DB tetap skala_1_5 tidak masalah --}}
                            <div class="rating-group">
                                <span class="me-2 scale-label text-danger">Kurang (1)</span>
                                
                                {{-- PERUBAHAN: Loop hanya sampai 4 --}}
                                @for ($i = 1; $i <= 4; $i++)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="jawaban[{{ $p->id }}]" id="skala_{{ $p->id }}_{{ $i }}" value="{{ $i }}" required>
                                    <label class="form-check-label" for="skala_{{ $p->id }}_{{ $i }}">{{ $i }}</label>
                                </div>
                                @endfor
                                
                                <span class="ms-2 scale-label text-success">Sangat Baik (4)</span>
                            </div>
                        @elseif ($p->tipe_jawaban == 'teks')
                            <textarea name="jawaban[{{ $p->id }}]" class="form-control" rows="3" placeholder="Tuliskan masukan atau saran Anda di sini..." required></textarea>
                        @endif
                    </div>
                    @if(!$loop->last) <hr> @endif
                @endforeach
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Kirim Evaluasi</button>
            <a href="{{ route('evaluasi.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
@endsection
@extends('layouts.app')

@section('content')
    <h1 class="mb-4">Tambah Mata Kuliah Baru</h1>

    <form action="/mata-kuliah" method="POST">
        @csrf

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="kode_mk" class="form-label">Kode Mata Kuliah</label>
                <input type="text" class="form-control @error('kode_mk') is-invalid @enderror" id="kode_mk" name="kode_mk" value="{{ old('kode_mk') }}">
                @error('kode_mk')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="nama_mk" class="form-label">Nama Mata Kuliah</label>
                <input type="text" class="form-control @error('nama_mk') is-invalid @enderror" id="nama_mk" name="nama_mk" value="{{ old('nama_mk') }}">
                @error('nama_mk')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="sks" class="form-label">Jumlah SKS</label>
                <input type="number" class="form-control @error('sks') is-invalid @enderror" id="sks" name="sks" value="{{ old('sks') }}">
                @error('sks')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="semester" class="form-label">Semester</label>
                <input type="number" class="form-control @error('semester') is-invalid @enderror" id="semester" name="semester" value="{{ old('semester') }}">
                @error('semester')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="dosen_id" class="form-label">Dosen Pengampu</label>
            <select class="form-select @error('dosen_id') is-invalid @enderror" id="dosen_id" name="dosen_id">
                <option selected disabled>Pilih Dosen</option>
                @foreach ($dosens as $dosen)
                    <option value="{{ $dosen->id }}" {{ old('dosen_id') == $dosen->id ? 'selected' : '' }}>
                        {{ $dosen->nama_lengkap }}
                    </option>
                @endforeach
            </select>
            @error('dosen_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="prasyarat_id" class="form-label">Mata Kuliah Prasyarat (tahan Ctrl untuk memilih lebih dari satu)</label>
            <select multiple class="form-control" id="prasyarat_id" name="prasyarat_id[]" size="5">
                {{-- OPSI INI TIDAK DIPERLUKAN KARENA JIKA TIDAK ADA YANG DIPILIH, SISTEM AKAN MENANGGAPNYA KOSONG --}}
                @foreach ($mata_kuliahs as $mk)
                    <option value="{{ $mk->id }}">{{ $mk->nama_mk }} (Semester {{ $mk->semester }})</option>
                @endforeach
            </select>
        </div>

        <hr>
        <h5>Jadwal Kuliah</h5>
        <div id="jadwal-container">
            <div class="row jadwal-item mb-2">
                <div class="col-md-4">
                    <label class="form-label">Hari</label>
                    <select name="jadwals[0][hari]" class="form-select">
                        <option value="Senin">Senin</option><option value="Selasa">Selasa</option><option value="Rabu">Rabu</option><option value="Kamis">Kamis</option><option value="Jumat">Jumat</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jam Mulai</label>
                    <input type="time" name="jadwals[0][jam_mulai]" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jam Selesai</label>
                    <input type="time" name="jadwals[0][jam_selesai]" class="form-control">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm remove-jadwal-btn" style="display: none;">Hapus</button>
                </div>
            </div>
        </div>
        <button type="button" id="add-jadwal-btn" class="btn btn-success btn-sm mt-2">Tambah Jadwal</button>

        <hr>
        <button type="submit" class="btn btn-primary mt-3">Simpan</button>
        <a href="/mata-kuliah" class="btn btn-secondary mt-3">Batal</a>
    </form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let jadwalIndex = 1;
        document.getElementById('add-jadwal-btn').addEventListener('click', function () {
            const container = document.getElementById('jadwal-container');
            const newJadwal = container.children[0].cloneNode(true);
            
            newJadwal.querySelector('select').name = `jadwals[${jadwalIndex}][hari]`;
            newJadwal.querySelectorAll('input[type="time"]')[0].name = `jadwals[${jadwalIndex}][jam_mulai]`;
            newJadwal.querySelectorAll('input[type="time"]')[1].name = `jadwals[${jadwalIndex}][jam_selesai]`;
            
            newJadwal.querySelector('select').value = 'Senin';
            newJadwal.querySelectorAll('input').forEach(input => input.value = '');
            
            const removeBtn = newJadwal.querySelector('.remove-jadwal-btn');
            removeBtn.style.display = 'block';
            removeBtn.addEventListener('click', function() {
                this.parentElement.parentElement.remove();
            });

            container.appendChild(newJadwal);
            jadwalIndex++;
        });
    });
</script>
@endpush
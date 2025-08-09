@extends('layouts.app')
@section('content')
    <h1 class="mb-4">Input Nilai untuk: {{ $mataKuliah->nama_mk }}</h1>

    <form action="{{ route('nilai.store') }}" method="POST">
        @csrf
        <input type="hidden" name="mata_kuliah_id" value="{{ $mataKuliah->id }}">
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>NIM</th>
                    <th>Nama Mahasiswa</th>
                    <th>Nilai (A, B, C, D, E)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($mataKuliah->mahasiswas as $mahasiswa)
                    <tr>
                        <td>{{ $mahasiswa->nim }}</td>
                        <td>{{ $mahasiswa->nama_lengkap }}</td>
                        <td>
                            <input type="text" name="nilai[{{ $mahasiswa->id }}]" class="form-control" 
                                   value="{{ $mahasiswa->pivot->nilai ?? '' }}">
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">Belum ada mahasiswa yang mengambil mata kuliah ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary">Simpan Nilai</button>
        <a href="{{ route('nilai.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
@endsection
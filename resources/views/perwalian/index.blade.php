@extends('layouts.app')

@section('content')
    <h1 class="mb-4">Manajemen Mahasiswa Perwalian</h1>
    
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            Daftar Mahasiswa Perwalian Anda Saat Ini
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr><th>NIM</th><th>Nama</th><th>Program Studi</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($mahasiswa_wali as $mahasiswa)
                        <tr>
                            <td>{{ $mahasiswa->nim }}</td>
                            <td>{{ $mahasiswa->nama_lengkap }}</td>
                            <td>{{ $mahasiswa->programStudi->nama_prodi }}</td>
                            <td>
                                <form action="{{ route('perwalian.destroy', $mahasiswa->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus mahasiswa ini dari perwalian Anda?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Anda belum memiliki mahasiswa perwalian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Pilih Mahasiswa Perwalian Baru
        </div>
        <div class="card-body">
            <form action="{{ route('perwalian.store') }}" method="POST">
                @csrf
                <table class="table table-hover">
                    <thead>
                        <tr><th>Pilih</th><th>NIM</th><th>Nama</th><th>Program Studi</th></tr>
                    </thead>
                    <tbody>
                    @forelse ($mahasiswa_tersedia as $mahasiswa)
                        <tr>
                            <td>
                                <input class="form-check-input" type="checkbox" name="mahasiswa_ids[]" value="{{ $mahasiswa->id }}">
                            </td>
                            <td>{{ $mahasiswa->nim }}</td>
                            <td>{{ $mahasiswa->nama_lengkap }}</td>
                            <td>{{ $mahasiswa->programStudi->nama_prodi }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada mahasiswa yang tersedia untuk dipilih.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
                @if($mahasiswa_tersedia->isNotEmpty())
                    <button type="submit" class="btn btn-primary">Jadikan Mahasiswa Wali</button>
                @endif
            </form>
        </div>
    </div>
@endsection
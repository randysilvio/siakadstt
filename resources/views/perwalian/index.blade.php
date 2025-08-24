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
            <div class="table-responsive">
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
    </div>

    <div class="card">
        <div class="card-header">
            Pilih Mahasiswa Perwalian Baru
        </div>
        <div class="card-body">
            <!-- ======================================================= -->
            <!-- ===== PERBAIKAN: Menambahkan Formulir Filter & Pencarian ===== -->
            <!-- ======================================================= -->
            <form action="{{ route('perwalian.index') }}" method="GET" class="mb-4">
                <div class="row g-2">
                    <div class="col-md-7">
                        <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan NIM atau Nama Mahasiswa..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <select name="program_studi_id" class="form-select">
                            <option value="">Semua Program Studi</option>
                            @foreach($program_studis as $prodi)
                                <option value="{{ $prodi->id }}" {{ request('program_studi_id') == $prodi->id ? 'selected' : '' }}>
                                    {{ $prodi->nama_prodi }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-primary w-100" type="submit">Cari</button>
                    </div>
                </div>
            </form>
            <!-- ======================================================= -->

            <form action="{{ route('perwalian.store') }}" method="POST">
                @csrf
                <div class="table-responsive">
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
                                <td colspan="4" class="text-center">
                                    @if(request('search') || request('program_studi_id'))
                                        Tidak ada mahasiswa yang cocok dengan kriteria pencarian.
                                    @else
                                        Tidak ada mahasiswa yang tersedia untuk dipilih.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($mahasiswa_tersedia->isNotEmpty())
                    <button type="submit" class="btn btn-primary mt-3">Jadikan Mahasiswa Wali</button>
                @endif
            </form>
        </div>
        
        <!-- ======================================================= -->
        <!-- ===== PERBAIKAN: Menambahkan Tautan Paginasi ===== -->
        <!-- ======================================================= -->
        @if($mahasiswa_tersedia->hasPages())
            <div class="card-footer">
                {{ $mahasiswa_tersedia->links() }}
            </div>
        @endif
        <!-- ======================================================= -->
    </div>
@endsection

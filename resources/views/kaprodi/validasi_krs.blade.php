@extends('layouts.app')

@section('content')
    <h1 class="mb-4">Validasi KRS Mahasiswa</h1>
    <div class="card mb-4">
        <div class="card-header">
            Data Mahasiswa
        </div>
        <div class="card-body">
            <p><strong>NIM:</strong> {{ $mahasiswa->nim }}</p>
            <p><strong>Nama Lengkap:</strong> {{ $mahasiswa->nama_lengkap }}</p>
            <p><strong>Status KRS Saat Ini:</strong> <span class="badge bg-warning text-dark">{{ $mahasiswa->status_krs }}</span></p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Daftar Mata Kuliah yang Diambil
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Kode MK</th>
                        <th>Nama Mata Kuliah</th>
                        <th>SKS</th>
                        <th>Jadwal</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalSks = 0; @endphp
                    @forelse ($mahasiswa->mataKuliahs as $mk)
                        <tr>
                            <td>{{ $mk->kode_mk }}</td>
                            <td>{{ $mk->nama_mk }}</td>
                            <td>{{ $mk->sks }}</td>
                            <td>
                                {{-- BAGIAN YANG HILANG SEBELUMNYA --}}
                                @if($mk->jadwals->isNotEmpty())
                                    <ul class="list-unstyled mb-0">
                                    @foreach($mk->jadwals as $jadwal)
                                        <li><small>{{ $jadwal->hari }}, {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</small></li>
                                    @endforeach
                                    </ul>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                        </tr>
                        @php $totalSks += $mk->sks; @endphp
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Mahasiswa ini belum memilih mata kuliah.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2" class="text-end">Total SKS Diambil</th>
                        <th colspan="2">{{ $totalSks }} SKS</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="mt-4">
        <form action="{{ route('kaprodi.krs.update', $mahasiswa->id) }}" method="POST" class="d-inline">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status_krs" value="Disetujui">
            <button type="submit" class="btn btn-success">Setujui KRS</button>
        </form>
        <form action="{{ route('kaprodi.krs.update', $mahasiswa->id) }}" method="POST" class="d-inline">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status_krs" value="Ditolak">
            <button type="submit" class="btn btn-danger">Tolak KRS</button>
        </form>
        <a href="{{ route('kaprodi.dashboard') }}" class="btn btn-secondary">Kembali ke Dashboard</a>
    </div>
@endsection
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Hasil Evaluasi Dosen</h1>
    </div>

    {{-- Filter Sesi --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.evaluasi-hasil.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="sesi_id" class="col-form-label fw-bold">Pilih Sesi Evaluasi:</label>
                </div>
                <div class="col-auto">
                    <select name="sesi_id" id="sesi_id" class="form-select">
                        @foreach ($sesiList as $s)
                            <option value="{{ $s->id }}" {{ $sesiTerpilih && $sesiTerpilih->id == $s->id ? 'selected' : '' }}>
                                {{ $s->nama_sesi }} ({{ $s->tahunAkademik->tahun ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Tampilkan</button>
                </div>
            </form>
        </div>
    </div>

    @if($sesiTerpilih)
        <div class="card">
            <div class="card-header bg-white">
                Hasil Rekapitulasi: <strong>{{ $sesiTerpilih->nama_sesi }}</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">No</th>
                                <th>Nama Dosen</th>
                                <th>NIDN</th>
                                <th class="text-center">Jumlah Responden</th>
                                <th class="text-center">Nilai Rata-rata</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($hasilEvaluasi as $index => $h)
                                <tr>
                                    <td class="ps-4">{{ $hasilEvaluasi->firstItem() + $index }}</td>
                                    <td class="fw-bold">{{ $h->nama_dosen }}</td>
                                    <td>{{ $h->nidn }}</td>
                                    <td class="text-center">{{ $h->jumlah_responden }} Mhs</td>
                                    <td class="text-center">
                                        {{-- Badge warna berdasarkan nilai --}}
                                        @php
                                            $nilai = number_format($h->nilai_rata_rata, 2);
                                            $badgeClass = $nilai >= 4 ? 'bg-success' : ($nilai >= 3 ? 'bg-warning text-dark' : 'bg-danger');
                                        @endphp
                                        <span class="badge {{ $badgeClass }} fs-6">{{ $nilai }}</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('admin.evaluasi-hasil.show', ['sesi' => $sesiTerpilih->id, 'dosen' => $h->dosen_id]) }}" class="btn btn-sm btn-outline-primary">
                                            Lihat Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        Belum ada data evaluasi yang masuk untuk sesi ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($hasilEvaluasi->count() > 0)
                <div class="card-footer bg-white">
                    {{ $hasilEvaluasi->links() }}
                </div>
            @endif
        </div>
    @else
        <div class="alert alert-info">Silakan pilih sesi evaluasi terlebih dahulu.</div>
    @endif
</div>
@endsection
@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Pengisian Kartu Rencana Studi (KRS)</h1>
            {{-- Menambahkan informasi semester aktif --}}
            <h5 class="text-muted">Tahun Akademik {{ $periodeAktif->tahun }} - Semester {{ $periodeAktif->semester }}</h5>
        </div>
        <a href="{{ route('krs.cetak.final') }}" class="btn btn-info">Cetak KRS (Resmi)</a>
    </div>
    
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @error('mata_kuliahs')
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->get('mata_kuliahs') as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @enderror

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Data Mahasiswa</div>
                <div class="card-body">
                    <p><strong>NIM:</strong> {{ $mahasiswa->nim }}</p>
                    <p><strong>Nama Lengkap:</strong> {{ $mahasiswa->nama_lengkap }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-info">
                <h5>Informasi Akademik</h5>
                <p>IPK Anda saat ini: <strong>{{ $ipk }}</strong></p>
                <p class="mb-0">Batas maksimum pengambilan SKS: <strong>{{ $max_sks }} SKS</strong></p>
            </div>
            <div class="card bg-light">
                <div class="card-body">
                    <h5 class="card-title">Total SKS Diambil: <span id="total-sks" class="fw-bold">0</span> SKS</h5>
                </div>
            </div>
        </div>
    </div>


    <form action="{{ route('krs.store') }}" method="POST">
        @csrf
        
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Ambil</th>
                    <th>Kode MK</th>
                    <th>Nama Mata Kuliah</th>
                    <th>SKS</th>
                    <th>Semester</th>
                    <th>Jadwal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($mata_kuliahs as $mk)
                    @php
                        $prasyaratTerpenuhi = true;
                        $prasyaratList = [];
                        if ($mk->prasyarats->isNotEmpty()) {
                            foreach ($mk->prasyarats as $prasyarat) {
                                if (!in_array($prasyarat->id, $mk_lulus_ids)) {
                                    $prasyaratTerpenuhi = false;
                                    $prasyaratList[] = $prasyarat->nama_mk;
                                }
                            }
                        }
                    @endphp
                    <tr class="krs-row">
                        <td class="text-center align-middle">
                            <input class="form-check-input krs-checkbox" type="checkbox" name="mata_kuliahs[]" value="{{ $mk->id }}"
                                   data-sks="{{ $mk->sks }}"
                                   data-jadwal='@json($mk->jadwals)'
                                   data-prasyarat-ok="{{ $prasyaratTerpenuhi ? 'true' : 'false' }}"
                                   {{ in_array($mk->id, $mk_diambil_ids) ? 'checked' : '' }}
                                   {{ !$prasyaratTerpenuhi ? 'disabled' : '' }}>
                        </td>
                        <td>{{ $mk->kode_mk }}</td>
                        <td>
                            <strong>{{ $mk->nama_mk }}</strong>
                            @if(!$prasyaratTerpenuhi)
                                <small class="d-block text-danger mt-2">Prasyarat: {{ implode(', ', $prasyaratList) }}</small>
                            @endif
                        </td>
                        <td>{{ $mk->sks }}</td>
                        <td>{{ $mk->semester }}</td>
                        <td>
                            @if($mk->jadwals->isNotEmpty())
                                <ul class="list-unstyled mb-0">
                                @foreach($mk->jadwals as $jadwal)
                                    <li><small class="text-muted">{{ $jadwal->hari }}, {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</small></li>
                                @endforeach
                                </ul>
                            @else
                                <small class="text-muted">-</small>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada mata kuliah yang ditawarkan untuk semester ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <button type="submit" id="simpan-krs" class="btn btn-primary">Simpan KRS</button>
    </form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const allCheckboxes = document.querySelectorAll('.krs-checkbox');
    const totalSksElement = document.getElementById('total-sks');
    const simpanButton = document.getElementById('simpan-krs');
    const maxSks = {{ $max_sks }};

    function isTimeOverlap(jadwalA, jadwalB) {
        if (jadwalA.hari !== jadwalB.hari) return false;
        const dummyDate = '1970-01-01T';
        const startA = new Date(dummyDate + jadwalA.jam_mulai);
        const endA = new Date(dummyDate + jadwalA.jam_selesai);
        const startB = new Date(dummyDate + jadwalB.jam_mulai);
        const endB = new Date(dummyDate + jadwalB.jam_selesai);
        return startA < endB && endA > startB;
    }

    function validateKRS() {
        let currentSks = 0;
        const checkedSchedules = [];

        allCheckboxes.forEach(cb => {
            if (cb.checked) {
                currentSks += parseInt(cb.dataset.sks);
                checkedSchedules.push(...JSON.parse(cb.dataset.jadwal));
            }
        });

        totalSksElement.textContent = currentSks;
        const isSksExceeded = currentSks > maxSks;
        simpanButton.disabled = isSksExceeded;
        totalSksElement.classList.toggle('text-danger', isSksExceeded);

        allCheckboxes.forEach(cb => {
            if (cb.dataset.prasyaratOk === 'true') {
                if (!cb.checked) {
                    const jadwalMK = JSON.parse(cb.dataset.jadwal);
                    let isClashing = false;

                    for (const jadwal of jadwalMK) {
                        for (const checkedJadwal of checkedSchedules) {
                            if (isTimeOverlap(jadwal, checkedJadwal)) {
                                isClashing = true;
                                break;
                            }
                        }
                        if (isClashing) break;
                    }
                    
                    cb.disabled = isClashing;
                }
            }
        });
    }

    allCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', validateKRS);
    });

    validateKRS();
});
</script>
@endpush

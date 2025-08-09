@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Pengisian Kartu Rencana Studi (KRS)</h1>
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
                    <th>Jadwal</th> {{-- <-- KOLOM BARU --}}
                </tr>
            </thead>
            <tbody>
                @foreach ($mata_kuliahs as $mk)
                    @php
                        // Cek apakah prasyarat terpenuhi
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
                        <td> {{-- <-- KOLOM BARU --}}
                            @if($mk->jadwals->isNotEmpty())
                                <ul class="list-unstyled mb-0">
                                @foreach($mk->jadwals as $jadwal)
                                    <li><small class="text-muted">{{ $jadwal->hari }}, {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</small></li>
                                @endforeach
                                </ul>
                            @else
                                <small class="text-muted">-</small>
                            @endif
                            <small class="d-block text-danger clash-warning" style="display: none;">Bentrok!</small>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button type="submit" id="simpan-krs" class="btn btn-primary">Simpan KRS</button>
    </form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('.krs-checkbox');
    const totalSksElement = document.getElementById('total-sks');
    const simpanButton = document.getElementById('simpan-krs');
    const maxSks = {{ $max_sks }};

    function isTimeOverlap(jadwalA, jadwalB) {
        if (jadwalA.hari !== jadwalB.hari) return false;
        return (jadwalA.jam_mulai < jadwalB.jam_selesai && jadwalA.jam_selesai > jadwalB.jam_mulai);
    }

    function updateKrsState() {
        let currentSks = 0;
        const checkedSchedules = [];

        // 1. Kumpulkan data dari yang sudah tercentang
        checkboxes.forEach(cb => {
            if (cb.checked) {
                currentSks += parseInt(cb.getAttribute('data-sks'));
                const jadwalData = JSON.parse(cb.getAttribute('data-jadwal'));
                checkedSchedules.push(...jadwalData);
            }
        });

        // 2. Update tampilan SKS dan tombol simpan
        totalSksElement.textContent = currentSks;
        if (currentSks > maxSks) {
            totalSksElement.classList.add('text-danger');
            simpanButton.disabled = true;
        } else {
            totalSksElement.classList.remove('text-danger');
            simpanButton.disabled = false;
        }

        // 3. Periksa setiap checkbox yang belum tercentang
        checkboxes.forEach(cb => {
            const warningElement = cb.closest('tr').querySelector('.clash-warning');
            
            // Hanya proses checkbox yang prasyaratnya terpenuhi
            if (cb.dataset.prasyaratOk === 'true') {
                if (!cb.checked) {
                    const jadwalToCheck = JSON.parse(cb.getAttribute('data-jadwal'));
                    let hasClash = false;
                    for (const jadwal of jadwalToCheck) {
                        for (const checkedJadwal of checkedSchedules) {
                            if (isTimeOverlap(jadwal, checkedJadwal)) {
                                hasClash = true;
                                break;
                            }
                        }
                        if (hasClash) break;
                    }
                    
                    cb.disabled = hasClash;
                    warningElement.style.display = hasClash ? 'block' : 'none';
                } else {
                    // Jika tercentang, pastikan warning disembunyikan
                    warningElement.style.display = 'none';
                }
            }
        });
    }

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateKrsState);
    });

    // Jalankan saat halaman dimuat
    updateKrsState();
});
</script>
@endpush
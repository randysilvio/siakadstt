@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Pengisian Kartu Rencana Studi (KRS)</h1>
            <h5 class="text-muted">Tahun Akademik {{ $periodeAktif->tahun }} - Semester {{ $periodeAktif->semester }}</h5>
        </div>
        <div>
            @if($isLocked)
                <span class="badge bg-success fs-6 me-2"><i class="bi bi-lock-fill"></i> KRS Disetujui</span>
            @endif
            <a href="{{ route('krs.cetak.final') }}" class="btn btn-info">Cetak KRS (Resmi)</a>
        </div>
    </div>
    
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
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
                    <p><strong>Status KRS:</strong> 
                        @if($mahasiswa->status_krs == 'Disetujui')
                            <span class="badge bg-success">Disetujui</span>
                        @elseif($mahasiswa->status_krs == 'Menunggu Persetujuan')
                            <span class="badge bg-warning text-dark">Menunggu Persetujuan</span>
                        @else
                            <span class="badge bg-secondary">{{ $mahasiswa->status_krs ?? 'Draft' }}</span>
                        @endif
                    </p>
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
        
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th class="text-center" style="width: 50px;">Pilih</th>
                    <th>Kode MK</th>
                    <th>Nama Mata Kuliah</th>
                    <th class="text-center">SKS</th>
                    <th class="text-center">Smt</th>
                    <th>Jadwal</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($mata_kuliahs as $mk)
                    @php
                        $isTaken = in_array($mk->id, $mk_diambil_ids);
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
                    <tr class="krs-row {{ $isTaken ? 'table-primary' : '' }}">
                        <td class="text-center">
                            {{-- Checkbox dimatikan jika KRS sudah dilock (Disetujui) --}}
                            <input class="form-check-input krs-checkbox" type="checkbox" name="mata_kuliahs[]" value="{{ $mk->id }}"
                                   data-sks="{{ $mk->sks }}"
                                   data-jadwal='@json($mk->jadwals)'
                                   data-prasyarat-ok="{{ $prasyaratTerpenuhi ? 'true' : 'false' }}"
                                   {{ $isTaken ? 'checked' : '' }}
                                   {{ (!$prasyaratTerpenuhi || $isLocked) ? 'disabled' : '' }}>
                        </td>
                        <td>{{ $mk->kode_mk }}</td>
                        <td>
                            <strong>{{ $mk->nama_mk }}</strong>
                            @if(!$prasyaratTerpenuhi)
                                <small class="d-block text-danger mt-1"><i class="bi bi-exclamation-circle"></i> Prasyarat: {{ implode(', ', $prasyaratList) }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ $mk->sks }}</td>
                        <td class="text-center">{{ $mk->semester }}</td>
                        <td>
                            @if($mk->jadwals->isNotEmpty())
                                <ul class="list-unstyled mb-0 small">
                                @foreach($mk->jadwals as $jadwal)
                                    <li>{{ $jadwal->hari }}, {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}-{{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</li>
                                @endforeach
                                </ul>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            {{-- Tombol Hapus: Hanya muncul jika matkul diambil DAN KRS belum dikunci --}}
                            @if($isTaken && !$isLocked)
                                <button type="submit" form="form-delete-{{ $mk->id }}" class="btn btn-sm btn-danger" onclick="return confirm('Hapus mata kuliah {{ $mk->nama_mk }}?')" title="Hapus MK">
                                    <i class="bi bi-trash"></i>
                                </button>
                            @elseif($isTaken && $isLocked)
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Fix</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">Tidak ada mata kuliah yang ditawarkan untuk semester ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if(!$isLocked)
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" id="simpan-krs" class="btn btn-primary btn-lg"><i class="bi bi-save me-1"></i> Simpan Perubahan KRS</button>
            </div>
        @else
            <div class="alert alert-warning text-center">
                <i class="bi bi-lock-fill"></i> KRS Anda telah disetujui oleh Dosen Wali. Hubungi dosen jika ingin melakukan perubahan.
            </div>
        @endif
    </form>

    {{-- Form Tersembunyi untuk Hapus per Item --}}
    @foreach($mata_kuliahs as $mk)
        @if(in_array($mk->id, $mk_diambil_ids))
            <form id="form-delete-{{ $mk->id }}" action="{{ route('krs.destroy', $mk->id) }}" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        @endif
    @endforeach

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
        
        if(simpanButton) {
            simpanButton.disabled = isSksExceeded;
        }
        totalSksElement.classList.toggle('text-danger', isSksExceeded);

        allCheckboxes.forEach(cb => {
            if (cb.dataset.prasyaratOk === 'true' && !cb.disabled) { // Cek !cb.disabled agar yg dilock tidak berubah
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
                    
                    // Jangan disable jika checkbox memang milik user (kecuali lock global)
                    // Tapi di sini logika bentrok jadwal tetap berlaku
                    if(isClashing) cb.disabled = true;
                    else cb.disabled = false;
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
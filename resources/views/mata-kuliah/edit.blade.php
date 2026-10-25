@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Edit Mata Kuliah</h3>
            <span class="text-muted small uppercase">Perbarui Data Mata Kuliah & Jadwal Kelas</span>
        </div>
        <a href="{{ route('admin.mata-kuliah.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">Kembali</a>
    </div>

    <form action="{{ route('admin.mata-kuliah.update', $mataKuliah->id) }}" method="POST">
        @csrf @method('PUT')
        <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
            <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                Data Mata Kuliah
            </div>
            <div class="card-body p-4">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="kurikulum_id" class="form-label small fw-bold uppercase text-dark">Kurikulum <span class="text-danger">*</span></label>
                        <select class="form-select rounded-0 uppercase @error('kurikulum_id') is-invalid @enderror" id="kurikulum_id" name="kurikulum_id" required>
                            <option value="">-- PILIH KURIKULUM --</option>
                            @foreach ($kurikulums as $kurikulum)
                                <option value="{{ $kurikulum->id }}" {{ old('kurikulum_id', $mataKuliah->kurikulum_id) == $kurikulum->id ? 'selected' : '' }}>
                                    {{ $kurikulum->nama_kurikulum }} ({{ $kurikulum->tahun }})
                                </option>
                            @endforeach
                        </select>
                        @error('kurikulum_id')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="kode_mk" class="form-label small fw-bold uppercase text-dark">Kode Mata Kuliah <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-0 font-monospace uppercase @error('kode_mk') is-invalid @enderror" id="kode_mk" name="kode_mk" value="{{ old('kode_mk', $mataKuliah->kode_mk) }}" required>
                        @error('kode_mk')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="nama_mk" class="form-label small fw-bold uppercase text-dark">Nama Mata Kuliah <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-0 uppercase @error('nama_mk') is-invalid @enderror" id="nama_mk" name="nama_mk" value="{{ old('nama_mk', $mataKuliah->nama_mk) }}" required>
                        @error('nama_mk')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="sks" class="form-label small fw-bold uppercase text-dark">Jumlah SKS <span class="text-danger">*</span></label>
                        <input type="number" class="form-control rounded-0 font-monospace @error('sks') is-invalid @enderror" id="sks" name="sks" value="{{ old('sks', $mataKuliah->sks) }}" required min="1">
                        @error('sks')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="semester" class="form-label small fw-bold uppercase text-dark">Semester <span class="text-danger">*</span></label>
                        <input type="number" class="form-control rounded-0 font-monospace @error('semester') is-invalid @enderror" id="semester" name="semester" value="{{ old('semester', $mataKuliah->semester) }}" required min="1" max="8">
                        @error('semester')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="dosen_id" class="form-label small fw-bold uppercase text-dark">Dosen Pengampu Utama <span class="text-danger">*</span></label>
                    <select class="form-select rounded-0 uppercase @error('dosen_id') is-invalid @enderror" id="dosen_id" name="dosen_id" required>
                        @foreach ($dosens as $dosen)
                            <option value="{{ $dosen->id }}" {{ old('dosen_id', $mataKuliah->dosen_id) == $dosen->id ? 'selected' : '' }}>
                                {{ $dosen->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="team_dosen_ids" class="form-label small fw-bold uppercase text-dark">Dosen Anggota (Team Teaching)</label>
                    <select class="form-control rounded-0 uppercase" id="team_dosen_ids" name="team_dosen_ids[]" multiple>
                        @php $existingTeamIds = $mataKuliah->teamDosens->where('pivot.is_utama', false)->pluck('id')->toArray(); @endphp
                        @foreach ($dosens as $dosen)
                            <option value="{{ $dosen->id }}" {{ in_array($dosen->id, old('team_dosen_ids', $existingTeamIds)) ? 'selected' : '' }}>
                                {{ $dosen->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="prasyarat_id" class="form-label small fw-bold uppercase text-dark">Mata Kuliah Prasyarat</label>
                    <select multiple class="form-control rounded-0 uppercase" id="prasyarat_id" name="prasyarat_id[]">
                        @php $prasyaratIds = $mataKuliah->prasyarats->pluck('id')->toArray(); @endphp
                        @foreach ($mata_kuliahs_options as $mk)
                            <option value="{{ $mk->id }}" {{ in_array($mk->id, $prasyaratIds) ? 'selected' : '' }}>
                                {{ $mk->kode_mk }} - {{ $mk->nama_mk }} (SEM {{ $mk->semester }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <h6 class="uppercase fw-bold small text-dark mb-3 border-bottom pb-2 mt-4">Jadwal & Pembagian Kelas (Rombel)</h6>
                <div id="jadwal-container">
                    @forelse ($mataKuliah->jadwals as $index => $jadwal)
                        <div class="row g-2 jadwal-item mb-3 align-items-end">
                            <div class="col-md-2">
                                @if($loop->first)<label class="form-label small fw-bold uppercase text-dark">Kelas</label>@endif
                                <input type="text" name="jadwals[{{$index}}][nama_kelas]" class="form-control rounded-0 font-monospace text-center uppercase fw-bold" value="{{ $jadwal->nama_kelas ?? 'A' }}">
                            </div>
                            <div class="col-md-2">
                                @if($loop->first)<label class="form-label small fw-bold uppercase text-dark">Hari</label>@endif
                                <select name="jadwals[{{$index}}][hari]" class="form-select rounded-0 uppercase fw-bold">
                                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $hr)
                                        <option value="{{ $hr }}" {{ $jadwal->hari == $hr ? 'selected' : '' }}>{{ $hr }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                @if($loop->first)<label class="form-label small fw-bold uppercase text-dark">Jam Mulai</label>@endif
                                <input type="time" name="jadwals[{{$index}}][jam_mulai]" class="form-control rounded-0 font-monospace text-center" value="{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}">
                            </div>
                            <div class="col-md-2">
                                @if($loop->first)<label class="form-label small fw-bold uppercase text-dark">Jam Selesai</label>@endif
                                <input type="time" name="jadwals[{{$index}}][jam_selesai]" class="form-control rounded-0 font-monospace text-center" value="{{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}">
                            </div>
                            <div class="col-md-2">
                                @if($loop->first)<label class="form-label small fw-bold uppercase text-dark">Ruang</label>@endif
                                <input type="text" name="jadwals[{{$index}}][ruang]" class="form-control rounded-0 font-monospace text-center" value="{{ $jadwal->ruang }}">
                            </div>
                            <div class="col-md-1">
                                @if($loop->first)<label class="form-label small fw-bold uppercase text-dark">Kuota</label>@endif
                                <input type="number" name="jadwals[{{$index}}][kuota]" class="form-control rounded-0 font-monospace text-center" value="{{ $jadwal->kuota ?? 40 }}" min="1">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger rounded-0 w-100 uppercase fw-bold small py-2 remove-jadwal-btn">X</button>
                            </div>
                        </div>
                    @empty
                        <div class="row g-2 jadwal-item mb-3 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label small fw-bold uppercase text-dark">Kelas</label>
                                <input type="text" name="jadwals[0][nama_kelas]" class="form-control rounded-0 font-monospace text-center uppercase fw-bold" value="A">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold uppercase text-dark">Hari</label>
                                <select name="jadwals[0][hari]" class="form-select rounded-0 uppercase fw-bold">
                                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $hr)
                                        <option value="{{ $hr }}">{{ $hr }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold uppercase text-dark">Jam Mulai</label>
                                <input type="time" name="jadwals[0][jam_mulai]" class="form-control rounded-0 font-monospace text-center">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold uppercase text-dark">Jam Selesai</label>
                                <input type="time" name="jadwals[0][jam_selesai]" class="form-control rounded-0 font-monospace text-center">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold uppercase text-dark">Ruang</label>
                                <input type="text" name="jadwals[0][ruang]" class="form-control rounded-0 font-monospace text-center" placeholder="R.101">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label small fw-bold uppercase text-dark">Kuota</label>
                                <input type="number" name="jadwals[0][kuota]" class="form-control rounded-0 font-monospace text-center" value="40" min="1">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger rounded-0 w-100 uppercase fw-bold small py-2 remove-jadwal-btn" style="display: none;">X</button>
                            </div>
                        </div>
                    @endforelse
                </div>
                
                <button type="button" id="add-jadwal-btn" class="btn btn-dark rounded-0 btn-sm uppercase fw-bold px-3 mt-1">
                    Tambah Kelas / Jadwal
                </button>

                <div class="d-flex justify-content-end pt-4 mt-4 border-top">
                    <a href="{{ route('admin.mata-kuliah.index') }}" class="btn btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
                    <button type="submit" class="btn btn-primary rounded-0 px-5 uppercase fw-bold small">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#prasyarat_id').select2({ theme: "bootstrap-5", placeholder: '-- PILIH MATA KULIAH PRASYARAT --', width: '100%' });
        $('#team_dosen_ids').select2({ theme: "bootstrap-5", placeholder: '-- PILIH TIM DOSEN --', width: '100%' });

        let jadwalIndex = {{ $mataKuliah->jadwals->count() > 0 ? $mataKuliah->jadwals->count() : 1 }};

        function addRemoveListener(button) {
            $(button).on('click', function() {
                if ($('.jadwal-item').length > 1) {
                    $(this).closest('.jadwal-item').remove();
                }
            });
        }
        
        $('.remove-jadwal-btn').each(function() { addRemoveListener(this); });

        $('#add-jadwal-btn').on('click', function () {
            const container = $('#jadwal-container');
            const newJadwal = container.find('.jadwal-item:first').clone();
            newJadwal.find('label').remove();
            newJadwal.find('input[type="text"]').eq(0).attr('name', `jadwals[${jadwalIndex}][nama_kelas]`).val('B');
            newJadwal.find('select').attr('name', `jadwals[${jadwalIndex}][hari]`).val('Senin');
            newJadwal.find('input[type="time"]').eq(0).attr('name', `jadwals[${jadwalIndex}][jam_mulai]`).val('');
            newJadwal.find('input[type="time"]').eq(1).attr('name', `jadwals[${jadwalIndex}][jam_selesai]`).val('');
            newJadwal.find('input[type="text"]').eq(1).attr('name', `jadwals[${jadwalIndex}][ruang]`).val('');
            newJadwal.find('input[type="number"]').attr('name', `jadwals[${jadwalIndex}][kuota]`).val('40');
            
            const removeBtn = newJadwal.find('.remove-jadwal-btn').show();
            addRemoveListener(removeBtn);

            container.append(newJadwal);
            jadwalIndex++;
        });
    });
</script>
@endpush
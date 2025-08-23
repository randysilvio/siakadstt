@extends('layouts.app')

@section('content')
    <h1 class="mb-4">Edit Mata Kuliah</h1>

    <form action="{{ route('mata-kuliah.update', $mataKuliah->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="kurikulum_id" class="form-label">Kurikulum <span class="text-danger">*</span></label>
                        <select class="form-select @error('kurikulum_id') is-invalid @enderror" id="kurikulum_id" name="kurikulum_id" required>
                            <option value="">Pilih Kurikulum</option>
                            @foreach ($kurikulums as $kurikulum)
                                <option value="{{ $kurikulum->id }}" {{ old('kurikulum_id', $mataKuliah->kurikulum_id) == $kurikulum->id ? 'selected' : '' }}>
                                    {{ $kurikulum->nama_kurikulum }} ({{ $kurikulum->tahun }})
                                </option>
                            @endforeach
                        </select>
                        @error('kurikulum_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="kode_mk" class="form-label">Kode Mata Kuliah <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('kode_mk') is-invalid @enderror" id="kode_mk" name="kode_mk" value="{{ old('kode_mk', $mataKuliah->kode_mk) }}" required>
                        @error('kode_mk')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="nama_mk" class="form-label">Nama Mata Kuliah <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_mk') is-invalid @enderror" id="nama_mk" name="nama_mk" value="{{ old('nama_mk', $mataKuliah->nama_mk) }}" required>
                        @error('nama_mk')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="sks" class="form-label">Jumlah SKS <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('sks') is-invalid @enderror" id="sks" name="sks" value="{{ old('sks', $mataKuliah->sks) }}" required min="1">
                        @error('sks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('semester') is-invalid @enderror" id="semester" name="semester" value="{{ old('semester', $mataKuliah->semester) }}" required min="1" max="8">
                        @error('semester')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="dosen_id" class="form-label">Dosen Pengampu <span class="text-danger">*</span></label>
                    <select class="form-select @error('dosen_id') is-invalid @enderror" id="dosen_id" name="dosen_id" required>
                        <option value="" selected disabled>Pilih Dosen</option>
                        @foreach ($dosens as $dosen)
                            <option value="{{ $dosen->id }}" {{ old('dosen_id', $mataKuliah->dosen_id) == $dosen->id ? 'selected' : '' }}>
                                {{ $dosen->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                    @error('dosen_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                
                <div class="mb-3">
                    <label for="prasyarat_id" class="form-label">Mata Kuliah Prasyarat</label>
                    <select multiple class="form-control" id="prasyarat_id" name="prasyarat_id[]">
                        @php
                            $prasyaratIds = $mataKuliah->prasyarats->pluck('id')->toArray();
                        @endphp
                        @foreach ($mata_kuliahs_options as $mk)
                            <option value="{{ $mk->id }}" {{ in_array($mk->id, $prasyaratIds) ? 'selected' : '' }}>
                                {{ $mk->nama_mk }} (Semester {{ $mk->semester }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Opsi prasyarat akan otomatis terfilter saat Anda mengubah semester.</div>
                </div>
                
                <hr>
                <h5>Jadwal Kuliah</h5>
                <div id="jadwal-container">
                    @forelse ($mataKuliah->jadwals as $index => $jadwal)
                    <div class="row jadwal-item mb-2">
                        <div class="col-md-4">
                            @if($loop->first)<label class="form-label">Hari</label>@endif
                            <select name="jadwals[{{$index}}][hari]" class="form-select">
                                <option value="Senin" {{ $jadwal->hari == 'Senin' ? 'selected' : '' }}>Senin</option>
                                <option value="Selasa" {{ $jadwal->hari == 'Selasa' ? 'selected' : '' }}>Selasa</option>
                                <option value="Rabu" {{ $jadwal->hari == 'Rabu' ? 'selected' : '' }}>Rabu</option>
                                <option value="Kamis" {{ $jadwal->hari == 'Kamis' ? 'selected' : '' }}>Kamis</option>
                                <option value="Jumat" {{ $jadwal->hari == 'Jumat' ? 'selected' : '' }}>Jumat</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            @if($loop->first)<label class="form-label">Jam Mulai</label>@endif
                            <input type="time" name="jadwals[{{$index}}][jam_mulai]" class="form-control" value="{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}">
                        </div>
                        <div class="col-md-3">
                            @if($loop->first)<label class="form-label">Jam Selesai</label>@endif
                            <input type="time" name="jadwals[{{$index}}][jam_selesai]" class="form-control" value="{{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-sm remove-jadwal-btn">Hapus</button>
                        </div>
                    </div>
                    @empty
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
                    @endforelse
                </div>
                <button type="button" id="add-jadwal-btn" class="btn btn-success btn-sm mt-2">Tambah Jadwal</button>

                <hr>
                <button type="submit" class="btn btn-primary mt-3">Simpan Perubahan</button>
                <a href="{{ route('mata-kuliah.index') }}" class="btn btn-secondary mt-3">Batal</a>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Inisialisasi Select2
        $('#prasyarat_id').select2({
            theme: "bootstrap-5",
            placeholder: 'Pilih mata kuliah prasyarat',
        });

        const semesterInput = $('#semester');
        const prasyaratSelect = $('#prasyarat_id');
        let allCourses = @json($mata_kuliahs_options);

        function filterPrasyarat() {
            const currentSemester = parseInt(semesterInput.val(), 10);
            const selectedValues = prasyaratSelect.val();
            
            prasyaratSelect.empty();

            if (!isNaN(currentSemester) && currentSemester > 1) {
                const filteredCourses = allCourses.filter(course => course.semester < currentSemester);
                
                filteredCourses.forEach(course => {
                    const option = new Option(`${course.nama_mk} (Semester ${course.semester})`, course.id, false, false);
                    prasyaratSelect.append(option);
                });
                
                prasyaratSelect.val(selectedValues);
            }
            prasyaratSelect.trigger('change');
        }

        semesterInput.on('input', filterPrasyarat);
        
        // Logika untuk tambah/hapus jadwal
        let jadwalIndex = {{ $mataKuliah->jadwals->count() > 0 ? $mataKuliah->jadwals->count() : 1 }};

        function addRemoveListener(button) {
            $(button).on('click', function() {
                if ($('.jadwal-item').length > 1) {
                    $(this).closest('.jadwal-item').remove();
                }
            });
        }
        
        $('.remove-jadwal-btn').each(function() {
            addRemoveListener(this);
        });

        $('#add-jadwal-btn').on('click', function () {
            const container = $('#jadwal-container');
            const newJadwal = container.find('.jadwal-item:first').clone();
            
            newJadwal.find('select').attr('name', `jadwals[${jadwalIndex}][hari]`).val('Senin');
            newJadwal.find('input[type="time"]').eq(0).attr('name', `jadwals[${jadwalIndex}][jam_mulai]`).val('');
            newJadwal.find('input[type="time"]').eq(1).attr('name', `jadwals[${jadwalIndex}][jam_selesai]`).val('');
            newJadwal.find('label').remove(); // Hapus label duplikat
            
            const removeBtn = newJadwal.find('.remove-jadwal-btn').show();
            addRemoveListener(removeBtn);

            container.append(newJadwal);
            jadwalIndex++;
        });
    });
</script>
@endpush

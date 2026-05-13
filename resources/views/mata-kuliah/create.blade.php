@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Tambah Mata Kuliah Baru</h3>
            <span class="text-muted small uppercase">Entri Master Data Kurikulum & Prasyarat</span>
        </div>
        <div>
            <a href="{{ route('admin.mata-kuliah.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    <form action="{{ route('admin.mata-kuliah.store') }}" method="POST">
        @csrf
        <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
            <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
                Formulir Parameter Mata Kuliah
            </div>
            <div class="card-body p-4">
                
                {{-- Pemilihan Kurikulum --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="kurikulum_id" class="form-label small fw-bold uppercase text-dark">Kurikulum <span class="text-danger">*</span></label>
                        <select class="form-select rounded-0 uppercase @error('kurikulum_id') is-invalid @enderror" id="kurikulum_id" name="kurikulum_id" required>
                            <option value="">-- PILIH KURIKULUM --</option>
                            @foreach ($kurikulums as $kurikulum)
                                <option value="{{ $kurikulum->id }}" {{ old('kurikulum_id') == $kurikulum->id ? 'selected' : ($kurikulum->is_active ? 'selected' : '') }}>
                                    {{ $kurikulum->nama_kurikulum }} ({{ $kurikulum->tahun }})
                                </option>
                            @endforeach
                        </select>
                        @error('kurikulum_id')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Kode & Nama MK --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="kode_mk" class="form-label small fw-bold uppercase text-dark">Kode Mata Kuliah <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-0 font-monospace uppercase @error('kode_mk') is-invalid @enderror" id="kode_mk" name="kode_mk" value="{{ old('kode_mk') }}" placeholder="CONTOH: STT-101" required>
                        @error('kode_mk')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="nama_mk" class="form-label small fw-bold uppercase text-dark">Nama Mata Kuliah <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-0 uppercase @error('nama_mk') is-invalid @enderror" id="nama_mk" name="nama_mk" value="{{ old('nama_mk') }}" placeholder="URAIAN MATA KULIAH..." required>
                        @error('nama_mk')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- SKS & Semester --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="sks" class="form-label small fw-bold uppercase text-dark">Jumlah SKS <span class="text-danger">*</span></label>
                        <input type="number" class="form-control rounded-0 font-monospace @error('sks') is-invalid @enderror" id="sks" name="sks" value="{{ old('sks') }}" required min="1" placeholder="0">
                        @error('sks')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="semester" class="form-label small fw-bold uppercase text-dark">Semester <span class="text-danger">*</span></label>
                        <input type="number" class="form-control rounded-0 font-monospace @error('semester') is-invalid @enderror" id="semester" name="semester" value="{{ old('semester') }}" required min="1" max="8" placeholder="1 - 8">
                        @error('semester')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Dosen Pengampu --}}
                <div class="mb-3">
                    <label for="dosen_id" class="form-label small fw-bold uppercase text-dark">Dosen Pengampu <span class="text-danger">*</span></label>
                    <select class="form-select rounded-0 uppercase @error('dosen_id') is-invalid @enderror" id="dosen_id" name="dosen_id" required>
                        <option value="" selected disabled>-- PILIH DOSEN --</option>
                        @foreach ($dosens as $dosen)
                            <option value="{{ $dosen->id }}" {{ old('dosen_id') == $dosen->id ? 'selected' : '' }}>
                                {{ $dosen->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                    @error('dosen_id')<div class="invalid-feedback font-monospace small">{{ $message }}</div>@enderror
                </div>

                {{-- Prasyarat --}}
                <div class="mb-4">
                    <label for="prasyarat_id" class="form-label small fw-bold uppercase text-dark">Mata Kuliah Prasyarat</label>
                    <select multiple class="form-control rounded-0 uppercase" id="prasyarat_id" name="prasyarat_id[]">
                        {{-- Opsi akan diisi oleh JavaScript --}}
                    </select>
                    <div class="form-text text-muted small uppercase mt-1">
                        * Opsi prasyarat akan otomatis terfilter setelah mengisi kolom semester.
                    </div>
                </div>

                <h6 class="uppercase fw-bold small text-dark mb-3 border-bottom pb-2 mt-5">Alokasi Jadwal Kuliah</h6>
                <div id="jadwal-container">
                    <div class="row g-2 jadwal-item mb-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold uppercase text-dark">Hari</label>
                            <select name="jadwals[0][hari]" class="form-select rounded-0 uppercase fw-bold">
                                <option value="Senin">Senin</option>
                                <option value="Selasa">Selasa</option>
                                <option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option>
                                <option value="Jumat">Jumat</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold uppercase text-dark">Jam Mulai</label>
                            <input type="time" name="jadwals[0][jam_mulai]" class="form-control rounded-0 font-monospace text-center">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold uppercase text-dark">Jam Selesai</label>
                            <input type="time" name="jadwals[0][jam_selesai]" class="form-control rounded-0 font-monospace text-center">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger rounded-0 w-100 uppercase fw-bold small py-2 remove-jadwal-btn" style="display: none;">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                        </div>
                    </div>
                </div>
                
                <button type="button" id="add-jadwal-btn" class="btn btn-dark rounded-0 btn-sm uppercase fw-bold px-3 mt-1">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Slot Jadwal
                </button>

                <div class="d-flex justify-content-end pt-4 mt-4 border-top">
                    <a href="{{ route('admin.mata-kuliah.index') }}" class="btn btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
                    <button type="submit" class="btn btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                        <i class="bi bi-save me-1"></i> Simpan Data
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#prasyarat_id').select2({
            theme: "bootstrap-5",
            placeholder: '-- PILIH MATA KULIAH PRASYARAT --',
            width: '100%'
        });
        
        const semesterInput = $('#semester');
        const prasyaratSelect = $('#prasyarat_id');
        let allCourses = @json($mata_kuliahs);

        function filterPrasyarat() {
            const currentSemester = parseInt(semesterInput.val(), 10);
            const selectedValues = prasyaratSelect.val(); 
            
            prasyaratSelect.empty(); 

            if (!isNaN(currentSemester) && currentSemester > 1) {
                const filteredCourses = allCourses.filter(course => course.semester < currentSemester);
                
                filteredCourses.forEach(course => {
                    const option = new Option(`${course.kode_mk} - ${course.nama_mk} (SEM ${course.semester})`, course.id, false, false);
                    prasyaratSelect.append(option);
                });

                prasyaratSelect.val(selectedValues);
            }
            prasyaratSelect.trigger('change');
        }

        semesterInput.on('input', filterPrasyarat);
        filterPrasyarat(); 

        let jadwalIndex = 1;

        function addRemoveListener(button) {
            $(button).on('click', function() {
                if ($('.jadwal-item').length > 1) {
                    $(this).closest('.jadwal-item').remove();
                }
            });
        }

        $('#add-jadwal-btn').on('click', function () {
            const container = $('#jadwal-container');
            const newJadwal = container.find('.jadwal-item:first').clone();
            
            newJadwal.find('select').attr('name', `jadwals[${jadwalIndex}][hari]`).val('Senin');
            newJadwal.find('input[type="time"]').eq(0).attr('name', `jadwals[${jadwalIndex}][jam_mulai]`).val('');
            newJadwal.find('input[type="time"]').eq(1).attr('name', `jadwals[${jadwalIndex}][jam_selesai]`).val('');
            
            const removeBtn = newJadwal.find('.remove-jadwal-btn').show();
            addRemoveListener(removeBtn);

            container.append(newJadwal);
            jadwalIndex++;
        });

        $('.remove-jadwal-btn').each(function() {
            addRemoveListener(this);
        });
    });
</script>
@endpush
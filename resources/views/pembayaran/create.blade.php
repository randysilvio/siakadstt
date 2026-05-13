@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Buat Tagihan Baru</h3>
            <span class="text-muted small uppercase">Entri Pembayaran & Kewajiban Finansial Mahasiswa</span>
        </div>
        <div>
            <a href="{{ route('pembayaran.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-md-8 mx-auto mb-5">
        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
            Formulir Parameter Tagihan
        </div>
        <div class="card-body p-4">
            
            {{-- === BAGIAN FILTER CEPAT === --}}
            <div class="p-3 bg-light border rounded-0 mb-4">
                <div class="mb-2"><small class="fw-bold text-dark uppercase font-monospace">Filter Cepat Pencarian Mahasiswa</small></div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <select id="filter-prodi" class="form-select rounded-0 uppercase small fw-bold">
                            <option value="">-- SEMUA PROGRAM STUDI --</option>
                            @foreach ($prodis as $prodi)
                                <option value="{{ $prodi->id }}">
                                    {{ $prodi->nama_prodi ?? $prodi->nama_program_studi ?? 'NAMA PRODI TIDAK DITEMUKAN' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <select id="filter-angkatan" class="form-select rounded-0 font-monospace">
                            <option value="">-- SEMUA ANGKATAN --</option>
                            @foreach ($angkatans as $angkatan)
                                <option value="{{ $angkatan }}">{{ $angkatan }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <form action="{{ route('pembayaran.store') }}" method="POST">
                @csrf
                
                {{-- Input Mahasiswa dengan Select2 --}}
                <div class="mb-4">
                    <label for="select-mahasiswa" class="form-label small fw-bold uppercase text-dark">Pilih Mahasiswa <span class="text-danger">*</span></label>
                    <select class="form-select rounded-0 uppercase @error('mahasiswa_id') is-invalid @enderror" id="select-mahasiswa" name="mahasiswa_id" required>
                        <option></option>
                        @foreach ($mahasiswas as $mahasiswa)
                            <option value="{{ $mahasiswa->id }}" 
                                    data-prodi="{{ $mahasiswa->program_studi_id }}" 
                                    data-angkatan="{{ $mahasiswa->tahun_masuk }}"
                                    {{ old('mahasiswa_id') == $mahasiswa->id ? 'selected' : '' }}>
                                {{ $mahasiswa->nim }} - {{ $mahasiswa->nama_lengkap }} 
                                ({{ $mahasiswa->programStudi->nama_prodi ?? $mahasiswa->programStudi->nama_program_studi ?? '-' }} - {{ $mahasiswa->tahun_masuk }})
                            </option>
                        @endforeach
                    </select>
                    @error('mahasiswa_id')
                        <div class="invalid-feedback font-monospace small">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Input Jumlah --}}
                <div class="mb-3">
                    <label for="jumlah" class="form-label small fw-bold uppercase text-dark">Jumlah Tagihan (Rp) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light rounded-0 font-monospace">Rp</span>
                        <input type="number" class="form-control rounded-0 font-monospace @error('jumlah') is-invalid @enderror" id="jumlah" name="jumlah" value="{{ old('jumlah') }}" placeholder="1500000" required>
                    </div>
                    @error('jumlah')
                        <div class="text-danger font-monospace small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Input Semester --}}
                <div class="mb-4">
                    <label for="semester" class="form-label small fw-bold uppercase text-dark">Untuk Semester <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-0 font-monospace uppercase @error('semester') is-invalid @enderror" id="semester" name="semester" value="{{ old('semester') }}" placeholder="GASAL 2024/2025" required>
                    @error('semester')
                        <div class="text-danger font-monospace small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Input Jenis Pembayaran --}}
                <div class="mb-3">
                    <label for="jenis_pembayaran" class="form-label small fw-bold uppercase text-dark">Jenis Pembayaran <span class="text-danger">*</span></label>
                    <select name="jenis_pembayaran" class="form-select rounded-0 uppercase @error('jenis_pembayaran') is-invalid @enderror" required>
                        <option value="">-- PILIH JENIS --</option>
                        <option value="spp" {{ old('jenis_pembayaran') == 'spp' ? 'selected' : '' }}>SPP</option>
                        <option value="uang_gedung" {{ old('jenis_pembayaran') == 'uang_gedung' ? 'selected' : '' }}>Uang Gedung / Pembangunan</option>
                        <option value="sks" {{ old('jenis_pembayaran') == 'sks' ? 'selected' : '' }}>Biaya SKS</option>
                        <option value="registrasi" {{ old('jenis_pembayaran') == 'registrasi' ? 'selected' : '' }}>Registrasi Ulang / Heregistrasi</option>
                        <option value="biaya_ppl" {{ old('jenis_pembayaran') == 'biaya_ppl' ? 'selected' : '' }}>Biaya PPL</option>
                        <option value="wisuda" {{ old('jenis_pembayaran') == 'wisuda' ? 'selected' : '' }}>Biaya Wisuda</option>
                        <option value="lainnya" {{ old('jenis_pembayaran') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('jenis_pembayaran')
                        <div class="text-danger font-monospace small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="keterangan" class="form-label small fw-bold uppercase text-dark">Keterangan (Opsional)</label>
                    <textarea class="form-control rounded-0 uppercase" name="keterangan" rows="2" placeholder="CATATAN TAMBAHAN...">{{ old('keterangan') }}</textarea>
                </div>

                <div class="d-flex justify-content-end pt-4 mt-4 border-top">
                    <a href="{{ route('pembayaran.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">Simpan Tagihan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var $studentSelect = $('#select-mahasiswa');
        
        $studentSelect.select2({
            theme: 'bootstrap-5',
            placeholder: 'KETIK NAMA ATAU NIM...',
            allowClear: true,
            width: '100%'
        });

        var allOptions = [];
        $studentSelect.find('option').each(function() {
            if($(this).val() !== '') {
                allOptions.push({
                    val: $(this).val(),
                    text: $(this).text(),
                    prodi: $(this).data('prodi'), 
                    angkatan: $(this).data('angkatan') 
                });
            }
        });

        function filterStudents() {
            var selectedProdi = $('#filter-prodi').val();
            var selectedAngkatan = $('#filter-angkatan').val();

            $studentSelect.empty();
            $studentSelect.append('<option></option>');

            var count = 0;
            $.each(allOptions, function(i, student) {
                var studentProdi = String(student.prodi);
                var studentAngkatan = String(student.angkatan);
                
                var matchProdi = (selectedProdi === "" || studentProdi === String(selectedProdi));
                var matchAngkatan = (selectedAngkatan === "" || studentAngkatan === String(selectedAngkatan));

                if (matchProdi && matchAngkatan) {
                    var newOption = new Option(student.text, student.val, false, false);
                    $(newOption).attr('data-prodi', student.prodi);
                    $(newOption).attr('data-angkatan', student.angkatan);
                    $studentSelect.append(newOption);
                    count++;
                }
            });

            $studentSelect.trigger('change');
        }

        $('#filter-prodi, #filter-angkatan').on('change', function() {
            filterStudents();
        });
    });
</script>
@endpush
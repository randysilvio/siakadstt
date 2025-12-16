@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm border-0 col-md-8 mx-auto">
        <div class="card-header bg-white py-3">
            <h4 class="mb-0 fw-bold">Buat Tagihan Baru</h4>
        </div>
        <div class="card-body p-4">
            
            {{-- === BAGIAN FILTER (OPSIONAL) === --}}
            <div class="row g-2 mb-3 p-3 bg-light rounded border">
                <div class="col-12"><small class="fw-bold text-muted text-uppercase">Filter Cepat</small></div>
                
                {{-- Filter Prodi --}}
                <div class="col-md-6">
                    <select id="filter-prodi" class="form-select form-select-sm">
                        <option value="">- Semua Program Studi -</option>
                        @foreach ($prodis as $prodi)
                            <option value="{{ $prodi->id }}">{{ $prodi->nama_program_studi }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Angkatan --}}
                <div class="col-md-6">
                    <select id="filter-angkatan" class="form-select form-select-sm">
                        <option value="">- Semua Angkatan -</option>
                        @foreach ($angkatans as $angkatan)
                            <option value="{{ $angkatan }}">{{ $angkatan }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <hr>

            <form action="{{ route('pembayaran.store') }}" method="POST">
                @csrf
                
                {{-- Input Mahasiswa dengan Select2 --}}
                <div class="mb-4">
                    <label for="select-mahasiswa" class="form-label fw-bold">Pilih Mahasiswa <span class="text-danger">*</span></label>
                    <select class="form-select @error('mahasiswa_id') is-invalid @enderror" id="select-mahasiswa" name="mahasiswa_id" required>
                        <option></option>
                        @foreach ($mahasiswas as $mahasiswa)
                            {{-- Kita simpan data prodi & angkatan di atribut data- --}}
                            <option value="{{ $mahasiswa->id }}" 
                                    data-prodi="{{ $mahasiswa->program_studi_id }}" 
                                    data-angkatan="{{ $mahasiswa->tahun_masuk }}"
                                    {{ old('mahasiswa_id') == $mahasiswa->id ? 'selected' : '' }}>
                                {{ $mahasiswa->nim }} - {{ $mahasiswa->nama_lengkap }} 
                                ({{ $mahasiswa->programStudi->nama_program_studi ?? '-' }} - {{ $mahasiswa->tahun_masuk }})
                            </option>
                        @endforeach
                    </select>
                    @error('mahasiswa_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Input Jumlah --}}
                <div class="mb-3">
                    <label for="jumlah" class="form-label fw-bold">Jumlah Tagihan (Rp) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control @error('jumlah') is-invalid @enderror" id="jumlah" name="jumlah" value="{{ old('jumlah') }}" placeholder="Contoh: 1500000">
                    </div>
                </div>

                {{-- Input Semester --}}
                <div class="mb-4">
                    <label for="semester" class="form-label fw-bold">Untuk Semester <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('semester') is-invalid @enderror" id="semester" name="semester" value="{{ old('semester') }}" placeholder="Contoh: Gasal 2024/2025">
                </div>

                <div class="mb-3">
                    <label for="keterangan" class="form-label fw-bold">Keterangan (Opsional)</label>
                    <textarea class="form-control" name="keterangan" rows="2" placeholder="Catatan tambahan..."></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('pembayaran.index') }}" class="btn btn-light border">Batal</a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Tagihan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // 1. Inisialisasi Select2 Awal
        var $studentSelect = $('#select-mahasiswa');
        
        $studentSelect.select2({
            theme: 'bootstrap-5',
            placeholder: 'Ketik Nama atau NIM...',
            allowClear: true,
            width: '100%'
        });

        // 2. Simpan semua opsi asli ke dalam array memori
        // Ini penting karena Select2 memodifikasi DOM, kita butuh backup data murni
        var allOptions = [];
        $studentSelect.find('option').each(function() {
            if($(this).val() !== '') { // Jangan simpan placeholder
                allOptions.push({
                    val: $(this).val(),
                    text: $(this).text(),
                    prodi: $(this).data('prodi'), // Ambil data-prodi
                    angkatan: $(this).data('angkatan') // Ambil data-angkatan
                });
            }
        });

        // 3. Fungsi Filter
        function filterStudents() {
            var selectedProdi = $('#filter-prodi').val();
            var selectedAngkatan = $('#filter-angkatan').val();

            // Kosongkan select2 (kecuali placeholder)
            $studentSelect.empty();
            $studentSelect.append('<option></option>');

            // Loop data asli dan masukkan kembali hanya yang cocok
            var count = 0;
            $.each(allOptions, function(i, student) {
                var matchProdi = (selectedProdi === "" || student.prodi == selectedProdi);
                var matchAngkatan = (selectedAngkatan === "" || student.angkatan == selectedAngkatan);

                if (matchProdi && matchAngkatan) {
                    var newOption = new Option(student.text, student.val, false, false);
                    // Kita perlu set ulang data attribute agar filter tetap jalan jika user ganti filter lagi
                    $(newOption).attr('data-prodi', student.prodi);
                    $(newOption).attr('data-angkatan', student.angkatan);
                    $studentSelect.append(newOption);
                    count++;
                }
            });

            // Refresh Select2 agar sadar ada perubahan option
            $studentSelect.trigger('change');
            
            // Opsional: Beri feedback jika kosong
            if(count === 0) {
                // Bisa tambahkan alert kecil atau console log
            }
        }

        // 4. Event Listener pada Filter
        $('#filter-prodi, #filter-angkatan').on('change', function() {
            filterStudents();
        });
    });
</script>
@endpush
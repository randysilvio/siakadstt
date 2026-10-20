@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Peminjaman Buku (POS Mode)</h3>
            <span class="text-muted small uppercase">Pindai Barcode Fisik untuk Registrasi Sirkulasi Otomatis</span>
        </div>
        <div>
            <a href="{{ route('perpustakaan.peminjaman.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    {{-- [PERBAIKAN] Tampilkan pesan error dari controller / validasi --}}
    @if(session('error'))
        <div class="alert alert-danger border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger border rounded-0 shadow-sm mb-4 p-3 small">
            @foreach($errors->all() as $err) <div>{{ $err }}</div> @endforeach
        </div>
    @endif

    <div class="row g-4 mb-5">
        {{-- KOLOM KIRI: SCANNER AREA --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-0 border-top border-primary border-4 h-100 bg-dark text-white">
                <div class="card-body p-4 p-md-5 d-flex flex-column justify-content-center align-items-center text-center">
                    <i class="bi bi-upc-scan d-block mb-3 opacity-75" style="font-size: 5rem;"></i>
                    <h5 class="fw-bold uppercase mb-1">Siap Pindai Barcode</h5>
                    <p class="small text-light opacity-75 font-monospace mb-4">Arahkan scanner ke label barcode di belakang buku.</p>
                    
                    {{-- Input Penampung Scanner --}}
                    <div class="w-100 mb-3 px-3">
                        <input type="text" id="scanner_input" class="form-control form-control-lg rounded-0 font-monospace fw-bold text-center bg-dark text-white border-secondary focus-ring-none" placeholder="SCAN BARCODE DI SINI..." style="border-width: 2px;" autocomplete="off" autofocus>
                    </div>
                    <div class="small font-monospace text-warning opacity-75" id="scanner_status">Menunggu input...</div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: FORM DATA TRANSAKSI --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-0 h-100">
                <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark d-flex justify-content-between align-items-center">
                    <span>Detail Transaksi Kasir</span>
                    <span class="badge bg-danger rounded-0 px-2" id="badge_status_buku">BELUM ADA BUKU</span>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('perpustakaan.peminjaman.store') }}" id="formPeminjaman">
                        @csrf
                        
                        {{-- Hidden Input untuk Eksemplar ID --}}
                        <input type="hidden" name="eksemplar_id" id="eksemplar_id" required>
                        <input type="hidden" name="koleksi_id" id="koleksi_id" required> {{-- Dipertahankan untuk fallback database --}}

                        {{-- Panel Info Buku Terpindai (Awalnya tersembunyi/kosong) --}}
                        <div class="bg-light border border-dark border-opacity-25 rounded-0 p-3 mb-4" id="panel_buku" style="display: none;">
                            <span class="text-muted small uppercase fw-bold d-block mb-1">Buku Terpindai:</span>
                            <h5 class="fw-bold text-dark mb-0 uppercase" id="display_judul_buku">JUDUL BUKU</h5>
                            <span class="text-primary font-monospace small fw-bold" id="display_kode_barcode">BK-XXX-XXXX</span>
                        </div>

                        {{-- Data Peminjam --}}
                        <div class="mb-4">
                            <label for="user_id" class="form-label small fw-bold uppercase text-dark">Data Peminjam / Mahasiswa <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light rounded-0"><i class="bi bi-person-badge text-dark"></i></span>
                                <select class="form-select select2 rounded-0 uppercase" id="user_id" name="user_id" required>
                                    <option value="" disabled selected>-- KETIK NAMA / NIM PEMINJAM... --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Tanggal Jatuh Tempo --}}
                        <div class="mb-4">
                            <label for="jatuh_tempo" class="form-label small fw-bold uppercase text-dark">Tanggal Jatuh Tempo <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light rounded-0"><i class="bi bi-calendar-event text-dark"></i></span>
                                <input type="date" class="form-control rounded-0 font-monospace text-primary fw-bold" id="jatuh_tempo" name="jatuh_tempo" value="{{ now()->addDays(7)->toDateString() }}" required>
                            </div>
                            <div class="form-text text-muted small uppercase mt-1">
                                * Durasi default: 7 hari kalender.
                            </div>
                        </div>

                        <div class="d-flex justify-content-end pt-3 mt-4 border-top">
                            <button type="submit" class="btn btn-primary rounded-0 px-5 py-2 uppercase fw-bold shadow-sm w-100" id="btn_simpan" disabled>
                                EKSEKUSI PEMINJAMAN
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- DATA DUMMY SEMENTARA UNTUK SIMULASI SCANNER DI SISI KLIEN (Akan diganti AJAX nanti jika butuh) --}}
@php
    // Kita lempar data semua eksemplar yang tersedia ke Javascript agar pencarian instan tanpa loading
    $eksemplarTersedia = \App\Models\PerpustakaanEksemplar::with('koleksi')->where('status_fisik', 'Tersedia')->get()->map(function($eks) {
        return [
            'id' => $eks->id,
            'koleksi_id' => $eks->koleksi_id,
            'kode_barcode' => strtoupper(trim($eks->kode_barcode)), // [PERBAIKAN] samakan format dengan input scanner
            'judul' => $eks->koleksi->judul ?? 'N/A' // [PERBAIKAN] cegah error jika master buku terhapus
        ];
    });
@endphp
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        .focus-ring-none:focus {
            box-shadow: none !important;
            border-color: #0d6efd !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // 1. Inisialisasi Select2 untuk Pencarian Peminjam
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            // 2. Data Bank Buku dari Server
            const bankBuku = @json($eksemplarTersedia);
            
            // 3. Logika Scanner Barcode
            const inputScanner = $('#scanner_input');
            let typingTimer;
            const doneTypingInterval = 300; // Tunggu 300ms setelah scan selesai

            // Paksa fokus ke input scanner saat halaman dimuat atau diklik di mana saja (Opsional)
            inputScanner.focus();

            inputScanner.on('keyup', function () {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(cekBarcode, doneTypingInterval);
            });

            inputScanner.on('keydown', function () {
                clearTimeout(typingTimer);
            });

            function cekBarcode() {
                let kode = inputScanner.val().trim().toUpperCase();
                if(kode === '') return;

                $('#scanner_status').text('Mencari data...').removeClass('text-warning').addClass('text-info');

                // Cari kode di dalam bank buku (Simulasi pencarian instan)
                let bukuDitemukan = bankBuku.find(b => b.kode_barcode === kode);

                if(bukuDitemukan) {
                    // Bunyi Bip (Simulasi)
                    playBeep(800, 150);

                    // Tampilkan di Panel Kanan
                    $('#eksemplar_id').val(bukuDitemukan.id);
                    $('#koleksi_id').val(bukuDitemukan.koleksi_id);
                    $('#display_judul_buku').text(bukuDitemukan.judul);
                    $('#display_kode_barcode').text(bukuDitemukan.kode_barcode);
                    
                    $('#panel_buku').slideDown();
                    $('#badge_status_buku').removeClass('bg-danger').addClass('bg-success').text('BUKU SIAP DIPINJAM');
                    $('#scanner_status').text('Buku ditemukan!').removeClass('text-info').addClass('text-success');
                    
                    // Aktifkan tombol simpan
                    $('#btn_simpan').prop('disabled', false);
                } else {
                    playBeep(300, 300); // Bunyi Bip Error
                    
                    $('#eksemplar_id').val('');
                    $('#koleksi_id').val('');
                    $('#panel_buku').slideUp();
                    $('#badge_status_buku').removeClass('bg-success').addClass('bg-danger').text('TIDAK DITEMUKAN / SUDAH DIPINJAM');
                    $('#scanner_status').text('Barcode tidak valid atau stok fisik tidak tersedia.').removeClass('text-info text-success').addClass('text-danger');
                    $('#btn_simpan').prop('disabled', true);
                }
                
                // Jangan reset input jika ingin melihat apa yang di-scan, atau uncomment ini untuk reset otomatis:
                // inputScanner.val(''); 
                setTimeout(() => inputScanner.focus(), 500);
            }

            // Fungsi sederhana membunyikan Bip sistem tanpa butuh file audio
            function playBeep(frequency, duration) {
                try {
                    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = audioCtx.createOscillator();
                    oscillator.type = 'sine';
                    oscillator.frequency.setValueAtTime(frequency, audioCtx.currentTime);
                    oscillator.connect(audioCtx.destination);
                    oscillator.start();
                    setTimeout(() => oscillator.stop(), duration);
                } catch(e) {}
            }
        });
    </script>
@endpush
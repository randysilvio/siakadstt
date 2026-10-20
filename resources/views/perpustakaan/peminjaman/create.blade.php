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

    {{-- [MODE KASIR] Pesan sukses (setelah eksekusi, halaman kembali ke sini untuk transaksi berikutnya) --}}
    @if(session('success'))
        <div class="alert alert-success border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif

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
                    <p class="small text-light opacity-75 font-monospace mb-4">Scan buku satu per satu. Semua buku masuk ke keranjang.</p>

                    {{-- Input Penampung Scanner (otomatis dikosongkan setiap selesai scan) --}}
                    <div class="w-100 mb-3 px-3">
                        <input type="text" id="scanner_input" class="form-control form-control-lg rounded-0 font-monospace fw-bold text-center bg-dark text-white border-secondary focus-ring-none" placeholder="SCAN BARCODE DI SINI..." style="border-width: 2px;" autocomplete="off" autofocus>
                    </div>
                    <div class="small font-monospace text-warning opacity-75" id="scanner_status">Menunggu input...</div>

                    {{-- [MODE KASIR] Kode terakhir yang dipindai (selalu tergantikan, tidak menumpuk) --}}
                    <div class="mt-3 small font-monospace text-light opacity-50">
                        Terakhir dipindai: <span id="last_scan" class="fw-bold">-</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: FORM DATA TRANSAKSI --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-0 h-100">
                <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark d-flex justify-content-between align-items-center">
                    <span>Detail Transaksi Kasir</span>
                    <span class="badge bg-danger rounded-0 px-2" id="badge_status_buku">KERANJANG KOSONG</span>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('perpustakaan.peminjaman.store') }}" id="formPeminjaman">
                        @csrf

                        {{-- [MODE KASIR] Hidden input eksemplar_ids[] dibuat otomatis oleh JavaScript --}}
                        <div id="hidden_eksemplar"></div>

                        {{-- [MODE KASIR] Keranjang Buku --}}
                        <div class="mb-4">
                            <label class="form-label small fw-bold uppercase text-dark">Keranjang Buku <span class="text-danger">*</span></label>
                            <div class="table-responsive border">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="bg-light small uppercase fw-bold">
                                        <tr>
                                            <th class="text-center" style="width: 8%;">NO</th>
                                            <th>JUDUL BUKU</th>
                                            <th style="width: 30%;">BARCODE</th>
                                            <th class="text-center" style="width: 10%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="keranjang_body" class="small">
                                        <tr id="keranjang_kosong">
                                            <td colspan="4" class="text-center text-muted py-4 uppercase">
                                                <i class="bi bi-cart d-block fs-4 mb-1 opacity-50"></i>
                                                Belum ada buku dipindai
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Data Peminjam --}}
                        <div class="mb-4">
                            <label for="user_id" class="form-label small fw-bold uppercase text-dark">Data Peminjam / Mahasiswa <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light rounded-0"><i class="bi bi-person-badge text-dark"></i></span>
                                <select class="form-select select2 rounded-0 uppercase" id="user_id" name="user_id" required>
                                    <option value="" disabled {{ old('user_id') ? '' : 'selected' }}>-- KETIK NAMA / NIM PEMINJAM... --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Tanggal Jatuh Tempo --}}
                        <div class="mb-4">
                            <label for="jatuh_tempo" class="form-label small fw-bold uppercase text-dark">Tanggal Jatuh Tempo <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light rounded-0"><i class="bi bi-calendar-event text-dark"></i></span>
                                <input type="date" class="form-control rounded-0 font-monospace text-primary fw-bold" id="jatuh_tempo" name="jatuh_tempo" value="{{ old('jatuh_tempo', now()->addDays(7)->toDateString()) }}" required>
                            </div>
                            <div class="form-text text-muted small uppercase mt-1">
                                * Durasi default: 7 hari kalender. Berlaku untuk semua buku di keranjang.
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 mt-4 border-top">
                            <button type="button" class="btn btn-outline-secondary rounded-0 px-3 py-2 uppercase fw-bold" id="btn_kosongkan" disabled>
                                Kosongkan
                            </button>
                            <button type="submit" class="btn btn-primary rounded-0 px-5 py-2 uppercase fw-bold shadow-sm flex-grow-1" id="btn_simpan" disabled>
                                EKSEKUSI PEMINJAMAN
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Data eksemplar yang tersedia untuk pencarian instan di sisi klien --}}
@php
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
        .row-flash { animation: rowFlash 0.8s ease; }
        @keyframes rowFlash { 0% { background-color: #fff3cd; } 100% { background-color: transparent; } }
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

            // 3. [MODE KASIR] Keranjang buku
            let keranjang = [];

            // 4. Logika Scanner Barcode
            const inputScanner = $('#scanner_input');
            let typingTimer;
            const doneTypingInterval = 300; // fallback jika scanner tidak mengirim Enter

            inputScanner.focus();

            // Kebanyakan scanner mengirim tombol Enter di akhir kode -> proses langsung
            inputScanner.on('keydown', function (e) {
                clearTimeout(typingTimer);
                if (e.key === 'Enter') {
                    e.preventDefault();
                    prosesScan();
                }
            });

            inputScanner.on('keyup', function (e) {
                if (e.key === 'Enter') return;
                clearTimeout(typingTimer);
                typingTimer = setTimeout(prosesScan, doneTypingInterval);
            });

            function setStatus(teks, kelas) {
                $('#scanner_status')
                    .text(teks)
                    .removeClass('text-warning text-info text-success text-danger')
                    .addClass(kelas);
            }

            function prosesScan() {
                let kode = inputScanner.val().trim().toUpperCase();

                // [MODE KASIR] Kosongkan input setiap selesai scan -> scan berikutnya tidak menempel / dobel
                inputScanner.val('');
                if (kode === '') return;

                $('#last_scan').text(kode);

                // Sudah ada di keranjang? -> jangan ditambah dua kali
                let sudahAda = keranjang.find(b => b.kode_barcode === kode);
                if (sudahAda) {
                    playBeep(300, 300);
                    setStatus('Barcode ' + kode + ' sudah ada di keranjang.', 'text-danger');
                    $('#row_' + sudahAda.id).removeClass('row-flash');
                    setTimeout(() => $('#row_' + sudahAda.id).addClass('row-flash'), 10);
                    return fokusUlang();
                }

                let bukuDitemukan = bankBuku.find(b => b.kode_barcode === kode);

                if (!bukuDitemukan) {
                    playBeep(300, 300);
                    setStatus('Barcode ' + kode + ' tidak valid atau sedang dipinjam.', 'text-danger');
                    return fokusUlang();
                }

                // Satu judul hanya boleh satu eksemplar per transaksi
                if (keranjang.find(b => b.koleksi_id === bukuDitemukan.koleksi_id)) {
                    playBeep(300, 300);
                    setStatus('Judul "' + bukuDitemukan.judul + '" sudah ada di keranjang.', 'text-danger');
                    return fokusUlang();
                }

                playBeep(800, 150);
                keranjang.push(bukuDitemukan);
                renderKeranjang();
                setStatus('Ditambahkan: ' + bukuDitemukan.judul, 'text-success');
                fokusUlang();
            }

            function renderKeranjang() {
                const body = $('#keranjang_body');
                const hidden = $('#hidden_eksemplar');
                body.empty();
                hidden.empty();

                if (keranjang.length === 0) {
                    body.append(`
                        <tr id="keranjang_kosong">
                            <td colspan="4" class="text-center text-muted py-4 uppercase">
                                <i class="bi bi-cart d-block fs-4 mb-1 opacity-50"></i>
                                Belum ada buku dipindai
                            </td>
                        </tr>`);
                } else {
                    keranjang.forEach((b, i) => {
                        const row = $('<tr>').attr('id', 'row_' + b.id);
                        row.append($('<td class="text-center font-monospace fw-bold text-muted">').text(i + 1));
                        row.append($('<td class="uppercase fw-bold">').text(b.judul));
                        row.append($('<td class="font-monospace text-primary">').text(b.kode_barcode));
                        row.append($('<td class="text-center">').append(
                            $('<button type="button" class="btn btn-sm btn-outline-danger rounded-0 py-0 px-2" title="Hapus">')
                                .html('<i class="bi bi-x-lg"></i>')
                                .on('click', () => hapusDariKeranjang(b.id))
                        ));
                        body.append(row);

                        hidden.append($('<input type="hidden" name="eksemplar_ids[]">').val(b.id));
                    });
                }

                const jumlah = keranjang.length;
                $('#btn_simpan').prop('disabled', jumlah === 0)
                    .text(jumlah > 0 ? 'EKSEKUSI PEMINJAMAN (' + jumlah + ' BUKU)' : 'EKSEKUSI PEMINJAMAN');
                $('#btn_kosongkan').prop('disabled', jumlah === 0);
                $('#badge_status_buku')
                    .removeClass('bg-danger bg-success')
                    .addClass(jumlah > 0 ? 'bg-success' : 'bg-danger')
                    .text(jumlah > 0 ? jumlah + ' BUKU DI KERANJANG' : 'KERANJANG KOSONG');
            }

            function hapusDariKeranjang(id) {
                keranjang = keranjang.filter(b => b.id !== id);
                renderKeranjang();
                setStatus('Buku dihapus dari keranjang.', 'text-warning');
                fokusUlang();
            }

            $('#btn_kosongkan').on('click', function () {
                keranjang = [];
                renderKeranjang();
                setStatus('Keranjang dikosongkan.', 'text-warning');
                fokusUlang();
            });

            // Cegah form terkirim dua kali (klik ganda)
            $('#formPeminjaman').on('submit', function () {
                $('#btn_simpan').prop('disabled', true).text('MEMPROSES...');
            });

            // Jika validasi gagal, isi ulang keranjang dari data sebelumnya
            const oldIds = @json(old('eksemplar_ids', []));
            oldIds.forEach(id => {
                const b = bankBuku.find(x => String(x.id) === String(id));
                if (b && !keranjang.find(k => k.id === b.id)) keranjang.push(b);
            });
            renderKeranjang();

            function fokusUlang() {
                setTimeout(() => inputScanner.focus(), 100);
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
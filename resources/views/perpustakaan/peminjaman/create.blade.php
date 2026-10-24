@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Peminjaman Buku (Hybrid Barcode & Smart Search)</h3>
            <span class="text-muted small uppercase">Pindai Barcode Fisik atau Cari Judul Buku & NIM Mahasiswa secara Instan</span>
        </div>
        <div>
            <a href="{{ route('perpustakaan.peminjaman.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Daftar Sirkulasi Aktif
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger border rounded-0 shadow-sm mb-4 p-3 small">
            @foreach($errors->all() as $err) <div>{{ $err }}</div> @endforeach
        </div>
    @endif

    @php
        $eksemplarTersedia = \App\Models\PerpustakaanEksemplar::with('koleksi')->where('status_fisik', 'Tersedia')->get()->map(function($eks) {
            return [
                'id' => $eks->id,
                'koleksi_id' => $eks->koleksi_id,
                'kode_barcode' => strtoupper(trim($eks->kode_barcode)),
                'judul' => $eks->koleksi->judul ?? 'N/A',
                'pengarang' => $eks->koleksi->pengarang ?? '-',
                'rak' => $eks->koleksi->lokasi_rak ?? '-'
            ];
        });
    @endphp

    <div class="row g-4 mb-5">
        {{-- KOLOM KIRI: SCANNER BARCODE & SMART SEARCH BUKU --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-0 border-top border-primary border-4 h-100 bg-dark text-white">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="text-center my-auto py-3">
                        <i class="bi bi-upc-scan d-block mb-2 opacity-75" style="font-size: 4rem;"></i>
                        <h5 class="fw-bold uppercase mb-1">1. Pindai Alat Scanner Barcode</h5>
                        <p class="small text-light opacity-75 font-monospace mb-3">Arahkan laser barcode ke label fisik buku.</p>

                        <div class="w-100 mb-2">
                            <input type="text" id="scanner_input" class="form-control form-control-lg rounded-0 font-monospace fw-bold text-center bg-dark text-white border-secondary focus-ring-none" placeholder="SCAN BARCODE DI SINI..." style="border-width: 2px;" autocomplete="off" autofocus>
                        </div>
                        <div class="small font-monospace text-warning" id="scanner_status">Menunggu input...</div>
                        <div class="mt-2 small font-monospace text-light opacity-50">
                            Terakhir dipindai: <span id="last_scan" class="fw-bold">-</span>
                        </div>
                    </div>

                    {{-- FITUR BARU: SMART SEARCH JUDUL BUKU / EKSEMPLAR TANPA SCANNER --}}
                    <div class="border-top border-secondary pt-3 mt-3">
                        <label class="form-label small fw-bold uppercase text-info mb-2">
                            <i class="bi bi-search me-1"></i> 2. Atau Cari Manual Judul Buku / Barcode (Smart Search)
                        </label>
                        <div class="d-flex gap-2">
                            <div class="flex-grow-1 text-dark">
                                <select id="manual_book_select" class="form-select rounded-0">
                                    <option value="">-- KETIK JUDUL BUKU / PENGARANG / BARCODE --</option>
                                    @foreach($eksemplarTersedia as $eks)
                                        <option value="{{ $eks['kode_barcode'] }}">
                                            [{{ $eks['kode_barcode'] }}] {{ strtoupper($eks['judul']) }} — {{ $eks['pengarang'] }} (Rak: {{ $eks['rak'] }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="button" id="btn_add_manual_book" class="btn btn-primary rounded-0 fw-bold uppercase small px-3">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: FORM DATA TRANSAKSI --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100">
                <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark d-flex justify-content-between align-items-center">
                    <span>Detail Transaksi Sirkulasi</span>
                    <span class="badge bg-danger rounded-0 px-2 py-1 font-monospace" id="badge_status_buku">KERANJANG KOSONG</span>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('perpustakaan.peminjaman.store') }}" id="formPeminjaman">
                        @csrf

                        <div id="hidden_eksemplar"></div>

                        {{-- Keranjang Buku --}}
                        <div class="mb-4">
                            <label class="form-label small fw-bold uppercase text-dark">Daftar Buku di Keranjang Pinjam <span class="text-danger">*</span></label>
                            <div class="table-responsive border border-dark border-opacity-25">
                                <table class="table table-sm table-bordered align-middle mb-0">
                                    <thead class="bg-light small uppercase fw-bold text-center">
                                        <tr>
                                            <th style="width: 8%;">NO</th>
                                            <th class="text-start">JUDUL BUKU</th>
                                            <th style="width: 28%;">KODE BARCODE</th>
                                            <th style="width: 10%;">HAPUS</th>
                                        </tr>
                                    </thead>
                                    <tbody id="keranjang_body" class="small">
                                        <tr id="keranjang_kosong">
                                            <td colspan="4" class="text-center text-muted py-4 uppercase fw-bold">
                                                <i class="bi bi-cart d-block fs-4 mb-1 opacity-50"></i>
                                                Belum ada buku di keranjang
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Data Peminjam dengan Smart Search NIM / NIDN / Nama --}}
                        <div class="mb-4">
                            <label for="user_id" class="form-label small fw-bold uppercase text-dark">Pencarian Peminjam (Ketik NIM / NIDN / Nama Mahasiswa atau Dosen) <span class="text-danger">*</span></label>
                            <select class="form-select select2-peminjam rounded-0 uppercase" id="user_id" name="user_id" required>
                                <option value="" disabled {{ old('user_id') ? '' : 'selected' }}>-- KETIK NIM / NIDN / NAMA LENGKAP PEMINJAM... --</option>
                                @foreach($users as $u)
                                    @php
                                        $identitas = $u->email;
                                        if ($u->mahasiswa) {
                                            $identitas = 'NIM: ' . $u->mahasiswa->nim . ' | MAHASISWA';
                                        } elseif ($u->dosen) {
                                            $identitas = 'NIDN: ' . ($u->dosen->nidn ?? '-') . ' | DOSEN';
                                        }
                                    @endphp
                                    <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                        {{ strtoupper($u->name) }} ({{ $identitas }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tanggal Jatuh Tempo --}}
                        <div class="mb-4">
                            <label for="jatuh_tempo" class="form-label small fw-bold uppercase text-dark">Tanggal Jatuh Tempo Pengembalian <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light rounded-0"><i class="bi bi-calendar-event text-dark"></i></span>
                                <input type="date" class="form-control rounded-0 font-monospace text-primary fw-bold" id="jatuh_tempo" name="jatuh_tempo" value="{{ old('jatuh_tempo', now()->addDays(7)->toDateString()) }}" required>
                            </div>
                            <div class="form-text text-muted small uppercase mt-1">
                                * Durasi standar peminjaman: 7 hari kalender.
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 mt-4 border-top">
                            <button type="button" class="btn btn-outline-dark rounded-0 px-3 py-2 uppercase fw-bold small" id="btn_kosongkan" disabled>
                                Kosongkan Keranjang
                            </button>
                            <button type="submit" class="btn btn-primary rounded-0 px-5 py-2 uppercase fw-bold small shadow-sm flex-grow-1" id="btn_simpan" disabled>
                                EKSEKUSI PEMINJAMAN
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
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
    <script>
        $(document).ready(function() {
            $('.select2-peminjam').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: '-- KETIK NIM / NIDN / NAMA PEMINJAM --'
            });

            $('#manual_book_select').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: '-- KETIK JUDUL BUKU / BARCODE --'
            });

            const bankBuku = @json($eksemplarTersedia);
            let keranjang = [];
            const inputScanner = $('#scanner_input');
            let typingTimer;
            const doneTypingInterval = 300;

            inputScanner.focus();

            inputScanner.on('keydown', function (e) {
                clearTimeout(typingTimer);
                if (e.key === 'Enter') {
                    e.preventDefault();
                    prosesKodeBarcode(inputScanner.val());
                    inputScanner.val('');
                }
            });

            inputScanner.on('keyup', function (e) {
                if (e.key === 'Enter') return;
                clearTimeout(typingTimer);
                typingTimer = setTimeout(function() {
                    prosesKodeBarcode(inputScanner.val());
                    inputScanner.val('');
                }, doneTypingInterval);
            });

            // Tambah dari Smart Search Manual
            $('#btn_add_manual_book').on('click', function () {
                const selectedKode = $('#manual_book_select').val();
                if (selectedKode) {
                    prosesKodeBarcode(selectedKode);
                    $('#manual_book_select').val('').trigger('change');
                }
            });

            function setStatus(teks, kelas) {
                $('#scanner_status')
                    .text(teks)
                    .removeClass('text-warning text-info text-success text-danger')
                    .addClass(kelas);
            }

            function prosesKodeBarcode(rawKode) {
                let kode = (rawKode || '').trim().toUpperCase();
                if (kode === '') return;

                $('#last_scan').text(kode);

                let sudahAda = keranjang.find(b => b.kode_barcode === kode);
                if (sudahAda) {
                    playBeep(300, 300);
                    setStatus('Barcode ' + kode + ' sudah ada di keranjang.', 'text-danger');
                    $('#row_' + sudahAda.id).removeClass('row-flash');
                    setTimeout(() => $('#row_' + sudahAda.id).addClass('row-flash'), 10);
                    return;
                }

                let bukuDitemukan = bankBuku.find(b => b.kode_barcode === kode);

                if (!bukuDitemukan) {
                    playBeep(300, 300);
                    setStatus('Barcode ' + kode + ' tidak valid atau sedang dipinjam.', 'text-danger');
                    return;
                }

                if (keranjang.find(b => b.koleksi_id === bukuDitemukan.koleksi_id)) {
                    playBeep(300, 300);
                    setStatus('Judul "' + bukuDitemukan.judul + '" sudah ada di keranjang.', 'text-danger');
                    return;
                }

                playBeep(800, 150);
                keranjang.push(bukuDitemukan);
                renderKeranjang();
                setStatus('Ditambahkan: ' + bukuDitemukan.judul, 'text-success');
            }

            function renderKeranjang() {
                const body = $('#keranjang_body');
                const hidden = $('#hidden_eksemplar');
                body.empty();
                hidden.empty();

                if (keranjang.length === 0) {
                    body.append(`
                        <tr id="keranjang_kosong">
                            <td colspan="4" class="text-center text-muted py-4 uppercase fw-bold">
                                <i class="bi bi-cart d-block fs-4 mb-1 opacity-50"></i>
                                Belum ada buku di keranjang
                            </td>
                        </tr>`);
                } else {
                    keranjang.forEach((b, i) => {
                        const row = $('<tr>').attr('id', 'row_' + b.id);
                        row.append($('<td class="text-center font-monospace fw-bold text-muted">').text(i + 1));
                        row.append($('<td class="uppercase fw-bold ps-2">').text(b.judul));
                        row.append($('<td class="text-center font-monospace text-primary fw-bold">').text(b.kode_barcode));
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
            }

            $('#btn_kosongkan').on('click', function () {
                keranjang = [];
                renderKeranjang();
                setStatus('Keranjang dikosongkan.', 'text-warning');
            });

            $('#formPeminjaman').on('submit', function () {
                $('#btn_simpan').prop('disabled', true).text('MEMPROSES...');
            });

            const oldIds = @json(old('eksemplar_ids', []));
            oldIds.forEach(id => {
                const b = bankBuku.find(x => String(x.id) === String(id));
                if (b && !keranjang.find(k => k.id === b.id)) keranjang.push(b);
            });
            renderKeranjang();

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
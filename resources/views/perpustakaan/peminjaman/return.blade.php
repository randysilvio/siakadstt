@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Pengembalian Pustaka (Hybrid Scanner & Smart Search)</h3>
            <span class="text-muted small uppercase">Pindai Barcode Buku atau Ketik NIM / Nama Peminjam untuk Verifikasi Pengembalian</span>
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

    <div class="row g-4 mb-5">
        {{-- KOLOM KIRI: SCANNER AREA --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-0 border-top border-success border-4 h-100 bg-dark text-white">
                <div class="card-body p-4 p-md-5 d-flex flex-column justify-content-center align-items-center text-center">
                    <i class="bi bi-arrow-return-left d-block mb-3 text-success opacity-75" style="font-size: 4.5rem;"></i>
                    <h5 class="fw-bold uppercase mb-1">1. Pindai Barcode Buku Kembali</h5>
                    <p class="small text-light opacity-75 font-monospace mb-4">Arahkan scanner ke label barcode fisik buku.</p>
                    
                    <div class="w-100 mb-3 px-2">
                        <input type="text" id="scanner_input" class="form-control form-control-lg rounded-0 font-monospace fw-bold text-center bg-dark text-white border-secondary focus-ring-none" placeholder="SCAN BARCODE DI SINI..." style="border-width: 2px;" autocomplete="off" autofocus>
                    </div>
                    <div class="small font-monospace text-warning opacity-75" id="scanner_status">Menunggu alat scanner...</div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: SMART SEARCH & VERIFIKASI PENGEMBALIAN --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100">
                <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark d-flex justify-content-between align-items-center">
                    <span>Verifikasi Data Pengembalian</span>
                    <span class="badge bg-danger rounded-0 px-2 py-1 font-monospace" id="badge_status">MENUNGGU PILIHAN</span>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('perpustakaan.peminjaman.processReturn') }}" id="formPengembalian">
                        @csrf
                        <input type="hidden" name="peminjaman_id" id="peminjaman_id" required>

                        {{-- Smart Search Transaksi Pinjaman Aktif (Select2) --}}
                        <div class="mb-4" id="panel_manual">
                            <label class="form-label small fw-bold uppercase text-dark">
                                <i class="bi bi-search me-1"></i> 2. Atau Cari Cepat via NIM / Nama Peminjam / Judul Buku / Barcode
                            </label>
                            <select class="form-select select2-return rounded-0 uppercase" id="peminjaman_manual">
                                <option value="">-- KETIK NIM / NAMA PEMINJAM / JUDUL BUKU / BARCODE --</option>
                                @foreach($peminjamans as $p)
                                    @php
                                        $nimInfo = optional(optional($p->user)->mahasiswa)->nim ? 'NIM: ' . $p->user->mahasiswa->nim . ' | ' : '';
                                    @endphp
                                    <option value="{{ $p->id }}" data-barcode="{{ $p->eksemplar ? strtoupper(trim($p->eksemplar->kode_barcode)) : '' }}">
                                        [{{ $p->eksemplar->kode_barcode ?? 'TANPA-BARCODE' }}] {{ $nimInfo }}{{ strtoupper($p->user->name ?? 'USER') }} — {{ strtoupper($p->koleksi->judul ?? 'BUKU') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Panel Info Transaksi Terpilih --}}
                        <div class="bg-light border border-dark border-opacity-25 rounded-0 p-4 mb-4 text-center" id="panel_buku" style="display: none;">
                            <div class="mb-3 border-bottom pb-3">
                                <span class="text-muted small uppercase fw-bold d-block mb-1">Judul Buku yang Dikembalikan:</span>
                                <h5 class="fw-bold text-dark mb-0 uppercase" id="display_judul">JUDUL BUKU</h5>
                                <span class="text-primary font-monospace small fw-bold" id="display_barcode">BK-XXX-XXXX</span>
                            </div>
                            
                            <div class="row g-2 text-start">
                                <div class="col-md-6 border-end">
                                    <span class="text-muted small uppercase fw-bold d-block">Data Peminjam</span>
                                    <strong class="text-dark uppercase d-block" id="display_peminjam">NAMA MAHASISWA</strong>
                                </div>
                                <div class="col-md-6 ps-md-3">
                                    <span class="text-muted small uppercase fw-bold d-block">Batas Jatuh Tempo</span>
                                    <strong class="text-dark font-monospace d-block" id="display_tempo">00/00/0000</strong>
                                </div>
                            </div>
                            
                            <div class="mt-3 pt-3 border-top" id="panel_keterlambatan" style="display: none;">
                                <div class="alert alert-danger rounded-0 mb-0 py-2 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <span class="fw-bold small uppercase">Peminjaman ini melewati batas jatuh tempo!</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end pt-3 border-top">
                            <button type="submit" class="btn btn-success rounded-0 px-5 py-2 uppercase fw-bold shadow-sm w-100" id="btn_simpan" disabled>
                                <i class="bi bi-check2-circle me-1"></i> TERIMA & KONFIRMASI PENGEMBALIAN BUKU
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $dataScan = $peminjamans->map(function($p) {
        return [
            'peminjaman_id' => $p->id,
            'barcode' => $p->eksemplar ? strtoupper(trim($p->eksemplar->kode_barcode)) : '',
            'judul' => $p->koleksi->judul ?? 'N/A',
            'peminjam' => ($p->user->name ?? 'N/A') . (optional(optional($p->user)->mahasiswa)->nim ? ' (' . $p->user->mahasiswa->nim . ')' : ''),
            'jatuh_tempo' => \Carbon\Carbon::parse($p->jatuh_tempo)->format('d/m/Y'),
            'is_late' => now()->gt($p->jatuh_tempo)
        ];
    });
@endphp
@endsection

@push('styles')
    <style>
        .focus-ring-none:focus { box-shadow: none !important; border-color: #198754 !important; }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.select2-return').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: '-- KETIK NIM / NAMA PEMINJAM / JUDUL BUKU / BARCODE --'
            });

            const bankTransaksi = @json($dataScan);
            const inputScanner = $('#scanner_input');
            let typingTimer;

            inputScanner.focus();

            inputScanner.on('keydown', function (e) {
                clearTimeout(typingTimer);
                if (e.key === 'Enter') { e.preventDefault(); cekBarcode(); }
            });
            inputScanner.on('keyup', function (e) {
                if (e.key === 'Enter') return;
                clearTimeout(typingTimer);
                typingTimer = setTimeout(cekBarcode, 300);
            });

            function tampilkanDetailTransaksi(transaksi) {
                $('#peminjaman_id').val(transaksi.peminjaman_id);
                $('#display_judul').text(transaksi.judul);
                $('#display_barcode').text(transaksi.barcode || 'TANPA BARCODE');
                $('#display_peminjam').text(transaksi.peminjam);
                $('#display_tempo').text(transaksi.jatuh_tempo);
                
                if (transaksi.is_late) {
                    $('#panel_keterlambatan').show();
                    $('#display_tempo').addClass('text-danger');
                } else {
                    $('#panel_keterlambatan').hide();
                    $('#display_tempo').removeClass('text-danger');
                }
                
                $('#panel_buku').slideDown();
                $('#badge_status').removeClass('bg-danger').addClass('bg-success').text('SIAP DIKEMBALIKAN');
                $('#scanner_status').text('Data ditemukan! Siap diproses.').removeClass('text-warning text-danger').addClass('text-success');
                $('#btn_simpan').prop('disabled', false);
            }

            // Saat memilih dari Smart Search Select2
            $('#peminjaman_manual').on('change', function() {
                const pid = $(this).val();
                if (!pid) {
                    $('#panel_buku').slideUp();
                    $('#btn_simpan').prop('disabled', true);
                    return;
                }
                const transaksi = bankTransaksi.find(b => String(b.peminjaman_id) === String(pid));
                if (transaksi) {
                    tampilkanDetailTransaksi(transaksi);
                }
            });

            function cekBarcode() {
                let kode = inputScanner.val().trim().toUpperCase();
                inputScanner.val('');
                if (kode === '') return;

                let transaksi = bankTransaksi.find(b => b.barcode === kode);

                if (transaksi) {
                    playBeep(900, 100);
                    $('#peminjaman_manual').val(transaksi.peminjaman_id).trigger('change.select2');
                    tampilkanDetailTransaksi(transaksi);
                } else {
                    playBeep(300, 300);
                    $('#panel_buku').slideUp();
                    $('#badge_status').removeClass('bg-success').addClass('bg-danger').text('TIDAK DITEMUKAN');
                    $('#scanner_status').text('Barcode ' + kode + ' tidak ada dalam daftar pinjaman aktif.').removeClass('text-success text-warning').addClass('text-danger');
                    $('#btn_simpan').prop('disabled', true);
                }
                setTimeout(() => inputScanner.focus(), 300);
            }

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
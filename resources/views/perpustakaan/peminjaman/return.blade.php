@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Pengembalian Pustaka (POS Mode)</h3>
            <span class="text-muted small uppercase">Pindai Barcode Buku untuk Verifikasi Pengembalian Otomatis</span>
        </div>
        <div>
            <a href="{{ route('perpustakaan.peminjaman.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Batal / Kembali
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
            <div class="card border-0 shadow-sm rounded-0 border-top border-success border-4 h-100 bg-dark text-white">
                <div class="card-body p-4 p-md-5 d-flex flex-column justify-content-center align-items-center text-center">
                    <i class="bi bi-arrow-return-left d-block mb-3 text-success opacity-75" style="font-size: 5rem;"></i>
                    <h5 class="fw-bold uppercase mb-1">Pindai Buku Kembali</h5>
                    <p class="small text-light opacity-75 font-monospace mb-4">Arahkan scanner ke label barcode fisik buku.</p>
                    
                    <div class="w-100 mb-3 px-3">
                        <input type="text" id="scanner_input" class="form-control form-control-lg rounded-0 font-monospace fw-bold text-center bg-dark text-white border-secondary focus-ring-none" placeholder="SCAN BARCODE DI SINI..." style="border-width: 2px;" autocomplete="off" autofocus>
                    </div>
                    <div class="small font-monospace text-warning opacity-75" id="scanner_status">Menunggu alat scanner...</div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: FORM VERIFIKASI PENGEMBALIAN --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-0 h-100">
                <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark d-flex justify-content-between align-items-center">
                    <span>Verifikasi Data Pengembalian</span>
                    <span class="badge bg-danger rounded-0 px-2" id="badge_status">MENUNGGU SCAN</span>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('perpustakaan.peminjaman.processReturn') }}" id="formPengembalian">
                        @csrf
                        
                        <input type="hidden" name="peminjaman_id" id="peminjaman_id" required>

                        {{-- Panel Info Transaksi Terpindai --}}
                        <div class="bg-light border border-dark border-opacity-25 rounded-0 p-4 mb-4 text-center" id="panel_buku" style="display: none;">
                            <div class="mb-3 border-bottom pb-3">
                                <span class="text-muted small uppercase fw-bold d-block mb-1">Judul Buku Terpindai:</span>
                                <h5 class="fw-bold text-dark mb-0 uppercase" id="display_judul">JUDUL BUKU</h5>
                                <span class="text-primary font-monospace small fw-bold" id="display_barcode">BK-XXX-XXXX</span>
                            </div>
                            
                            <div class="row g-2 text-start">
                                <div class="col-md-6 border-end">
                                    <span class="text-muted small uppercase fw-bold d-block">Data Peminjam</span>
                                    <strong class="text-dark uppercase d-block" id="display_peminjam">NAMA MAHASISWA</strong>
                                </div>
                                <div class="col-md-6 ps-md-3">
                                    <span class="text-muted small uppercase fw-bold d-block">Jatuh Tempo</span>
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

                        {{-- Fallback Manual (Disembunyikan, hanya muncul jika scan gagal terus) --}}
                        <div class="mb-4" id="panel_manual">
                            <label class="form-label small fw-bold uppercase text-dark">Pencarian Manual (Opsional)</label>
                            <select class="form-select rounded-0 uppercase text-muted" id="peminjaman_manual" onchange="if(this.value) { $('#peminjaman_id').val(this.value); $('#btn_simpan').prop('disabled', false); }">
                                <option value="" selected>-- PILIH TRANSAKSI (JIKA SCANNER RUSAK) --</option>
                                @foreach($peminjamans as $p)
                                    <option value="{{ $p->id }}">
                                        {{-- [PERBAIKAN] cegah error jika user / buku sudah dihapus --}}
                                        {{ $p->eksemplar->kode_barcode ?? 'NO-BARCODE' }} | {{ $p->user->name ?? 'USER DIHAPUS' }} - {{ $p->koleksi->judul ?? 'BUKU DIHAPUS' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-flex justify-content-end pt-3 border-top">
                            <button type="submit" class="btn btn-success rounded-0 px-5 py-2 uppercase fw-bold shadow-sm w-100" id="btn_simpan" disabled>
                                TERIMA PENGEMBALIAN BUKU
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
            'barcode' => $p->eksemplar ? strtoupper(trim($p->eksemplar->kode_barcode)) : '', // [PERBAIKAN] tambah trim()
            'judul' => $p->koleksi->judul ?? 'N/A',
            'peminjam' => $p->user->name ?? 'N/A',
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
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            const bankTransaksi = @json($dataScan);
            const inputScanner = $('#scanner_input');
            let typingTimer;

            inputScanner.focus();
            // [PERBAIKAN] Enter dari scanner langsung diproses
            inputScanner.on('keydown', function (e) {
                clearTimeout(typingTimer);
                if (e.key === 'Enter') { e.preventDefault(); cekBarcode(); }
            });
            inputScanner.on('keyup', function (e) {
                if (e.key === 'Enter') return;
                clearTimeout(typingTimer);
                typingTimer = setTimeout(cekBarcode, 300);
            });

            function cekBarcode() {
                let kode = inputScanner.val().trim().toUpperCase();
                inputScanner.val(''); // [PERBAIKAN] kosongkan input agar scan berikutnya menggantikan, tidak menempel
                if(kode === '') return;

                let transaksi = bankTransaksi.find(b => b.barcode === kode);

                if(transaksi) {
                    playBeep(900, 100);
                    $('#peminjaman_id').val(transaksi.peminjaman_id);
                    $('#display_judul').text(transaksi.judul);
                    $('#display_barcode').text(transaksi.barcode);
                    $('#display_peminjam').text(transaksi.peminjam);
                    $('#display_tempo').text(transaksi.jatuh_tempo);
                    
                    if(transaksi.is_late) {
                        $('#panel_keterlambatan').show();
                        $('#display_tempo').addClass('text-danger');
                    } else {
                        $('#panel_keterlambatan').hide();
                        $('#display_tempo').removeClass('text-danger');
                    }
                    
                    $('#panel_manual').hide();
                    $('#panel_buku').slideDown();
                    $('#badge_status').removeClass('bg-danger').addClass('bg-success').text('DATA COCOK');
                    $('#scanner_status').text('Siap diproses!').removeClass('text-warning').addClass('text-success');
                    $('#btn_simpan').prop('disabled', false);
                } else {
                    playBeep(300, 300);
                    $('#panel_buku').slideUp();
                    $('#panel_manual').show();
                    $('#badge_status').removeClass('bg-success').addClass('bg-danger').text('TIDAK DITEMUKAN');
                    $('#scanner_status').text('Barcode tidak ada dalam daftar sirkulasi aktif.').removeClass('text-success text-warning').addClass('text-danger');
                    $('#btn_simpan').prop('disabled', true);
                }
                setTimeout(() => inputScanner.focus(), 500);
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
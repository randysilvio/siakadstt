{{--
    Kartu Tanda Mahasiswa digital (E-KTM) — desain TETAP, tidak mengikuti Theme Engine (tema sentral).
    Latar (logo, judul, label, ornamen geometris & batik Papua) adalah gambar public/images/ktm/latar-ktm.jpg
    yang dibuat oleh resources/ktm/latar.py; komponen ini hanya menempatkan data mahasiswa di atasnya.
    Koordinat memakai satuan kanvas 1712 x 1080 (85,6 x 54 mm) yang sama dengan aplikasi seluler.
    Pemakaian: <x-ktm :mahasiswa="$mahasiswa" />
--}}
@props(['mahasiswa'])
@php
    $k = \App\Support\Ktm::data($mahasiswa);
    $w = $k['warna'];
    // ukuran huruf (px kanvas) mengecil untuk teks panjang — sama dengan aplikasi (KartuKtm.tsx)
    $ukuranTeks = fn (string $t) => \App\Support\Ktm::ukuranTeks($t);
    $nilai = [
        ['y' => 552, 'teks' => $k['nama']],
        ['y' => 640, 'teks' => $k['nim']],
        ['y' => 728, 'teks' => $k['prodi']],
        ['y' => 816, 'teks' => $k['angkatan']],
    ];
@endphp
<div class="ktm-sttgpi" style="background-image: url('{{ $k['latar_url'] }}'); --ktm-ungu: {{ $w['ungu'] }}; --ktm-oranye: {{ $w['oranye'] }}; --ktm-status: {{ $k['aktif'] ? $w['aktif'] : $w['nonaktif'] }};">
    <div class="ktm-foto">
        @if($k['foto_url'])
            <img src="{{ $k['foto_url'] }}" alt="Foto {{ $k['nama'] }}">
        @else
            <span class="ktm-inisial">{{ mb_strtoupper(mb_substr($k['nama'], 0, 1)) }}</span>
        @endif
        <span class="ktm-status">{{ $k['aktif'] ? 'MAHASISWA AKTIF' : strtoupper($k['status']) }}</span>
    </div>

    @foreach($nilai as $n)
        <span class="ktm-nilai" style="top: {{ ($n['y'] - 44) / 10.8 }}%; font-size: {{ $ukuranTeks($n['teks']) / 17.12 }}cqw;">{{ $n['teks'] }}</span>
    @endforeach

    <span class="ktm-qr" data-url="{{ $k['verifikasi_url'] }}" title="Pindai untuk verifikasi"></span>
    <span class="ktm-token">ID {{ $k['token'] }}</span>
    @unless($k['aktif'])
        <span class="ktm-cap">TIDAK AKTIF</span>
    @endunless
</div>

@once
    @push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;700&display=swap">
    <style>
        /* Satuan: 1 px kanvas = 1/17.12 cqw (lebar) = 1/10.8 % (tinggi). Warna ditulis tetap, bukan var(--siakad-*). */
        .ktm-sttgpi { position: relative; container-type: inline-size; width: 100%; max-width: 520px; aspect-ratio: 1712 / 1080; margin: 0 auto;
            border-radius: 2.2cqw; overflow: hidden; background-color: #FDDFC1; background-size: 100% 100%; background-repeat: no-repeat;
            box-shadow: 0 10px 28px rgba(61, 35, 80, .28); font-family: 'Poppins', 'Segoe UI', Arial, sans-serif; color: var(--ktm-ungu); line-height: 1.2; }
        .ktm-sttgpi * { box-sizing: border-box; }
        .ktm-foto { position: absolute; left: 11.974%; top: 23.98%; width: 25.526%; height: 51.94%; border-radius: 1.4cqw; overflow: hidden;
            border: .35cqw solid var(--ktm-oranye); background: #F6C99A; box-shadow: 0 .8cqw 1.6cqw rgba(61, 35, 80, .3); display: flex; align-items: center; justify-content: center; }
        .ktm-foto img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .ktm-inisial { font-weight: 700; font-size: 9cqw; color: var(--ktm-ungu); opacity: .55; }
        .ktm-status { position: absolute; left: 0; right: 0; bottom: 0; background: var(--ktm-status); color: #fff; text-align: center;
            font-weight: 700; font-size: 1.35cqw; letter-spacing: .12cqw; padding: .55cqw 0; }
        .ktm-nilai { position: absolute; left: 55.14%; width: 26.9%; height: 5%; display: flex; align-items: flex-end; font-weight: 700;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1; }
        .ktm-qr { position: absolute; left: 85.98%; top: 61.11%; width: 10.164%; aspect-ratio: 1; display: block; }
        .ktm-qr svg { width: 100%; height: 100%; display: block; }
        .ktm-token { position: absolute; left: 41.82%; top: 79.4%; font-family: 'Courier New', monospace; font-weight: 700; font-size: 1.3cqw; opacity: .75; }
        .ktm-cap { position: absolute; top: 40%; left: 52%; transform: translate(-50%, -50%) rotate(-12deg); border: .35cqw solid #B3402A; color: #B3402A;
            padding: .4cqw 1.6cqw; font-weight: 700; font-size: 4cqw; letter-spacing: .4cqw; border-radius: 1cqw; background: rgba(253, 223, 193, .85); }
    </style>
    @endpush
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <script>
        document.querySelectorAll('.ktm-qr[data-url]').forEach(function (el) {
            if (typeof qrcode !== 'function') return;
            var qr = qrcode(0, 'M');
            qr.addData(el.dataset.url);
            qr.make();
            el.innerHTML = qr.createSvgTag({ cellSize: 4, margin: 0, scalable: true });
            el.querySelectorAll('path, rect').forEach(function (n) {
                var f = (n.getAttribute('fill') || '').toLowerCase();
                if (f === 'black' || f === '#000000') n.setAttribute('fill', '#3D2350');
            });
        });
    </script>
    @endpush
@endonce

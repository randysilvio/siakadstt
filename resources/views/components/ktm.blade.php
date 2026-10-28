{{--
    Kartu Tanda Mahasiswa digital (E-KTM) — desain TETAP, tidak mengikuti Theme Engine (tema sentral).
    Latar (logo, judul, label, ornamen geometris & batik Papua) adalah gambar public/images/ktm/latar-ktm.jpg
    yang dibuat oleh resources/ktm/latar.py; komponen ini hanya menempatkan data di atasnya memakai
    App\Support\Ktm::TATA_LETAK (kanvas 1712 x 1080 = 85,6 x 54 mm, sama dengan aplikasi seluler).
    Pemakaian: <x-ktm :mahasiswa="$mahasiswa" />
--}}
@props(['mahasiswa'])
@php
    $k = \App\Support\Ktm::data($mahasiswa);
    $w = $k['warna'];
    $L = \App\Support\Ktm::TATA_LETAK;
    $f = $L['foto'];
    // Satuan: 1 px kanvas = 1/17.12 cqw (lebar) = 1/10.8 % (tinggi)
    $teks = function (string $kunci, string $isi, string $kelas = '') use ($L) {
        $p = $L[$kunci];
        $u = \App\Support\Ktm::ukuranTeks($isi, $p['w'], $p['maks'], $p['min']);
        return sprintf('<span class="ktm-nilai %s" style="left:%.3f%%;top:%.3f%%;width:%.3f%%;font-size:%.3fcqw;line-height:%.3fcqw;">%s</span>',
            $kelas, $p['x'] / 17.12, ($p['b'] - $u) / 10.8, $p['w'] / 17.12, $u / 17.12, $u * 1.35 / 17.12, e($isi));
    };
@endphp
<div class="ktm-sttgpi" style="background-image: url('{{ $k['latar_url'] }}'); --ktm-ungu: {{ $w['ungu'] }}; --ktm-oranye: {{ $w['oranye'] }}; --ktm-status: {{ $k['aktif'] ? $w['aktif'] : $w['nonaktif'] }};">
    <div class="ktm-foto" style="left: {{ $f['x'] / 17.12 }}%; top: {{ $f['y'] / 10.8 }}%; width: {{ $f['w'] / 17.12 }}%; height: {{ $f['h'] / 10.8 }}%;">
        @if($k['foto_url'])
            <img src="{{ $k['foto_url'] }}" alt="Foto {{ $k['nama'] }}">
        @else
            <span class="ktm-inisial">{{ mb_strtoupper(mb_substr($k['nama'], 0, 1)) }}</span>
        @endif
        <span class="ktm-status">{{ $k['aktif'] ? 'MAHASISWA AKTIF' : strtoupper($k['status']) }}</span>
    </div>

    {!! $teks('nama', $k['nama']) !!}
    {!! $teks('nim', $k['nim'], 'ktm-nim') !!}
    {!! $teks('prodi', $k['prodi']) !!}
    {!! $teks('angkatan', $k['angkatan']) !!}
    {!! $teks('token', 'ID ' . $k['token'], 'ktm-token') !!}

    <span class="ktm-qr" data-url="{{ $k['verifikasi_url'] }}" title="Pindai untuk verifikasi"
          style="left: {{ $L['qr']['x'] / 17.12 }}%; top: {{ $L['qr']['y'] / 10.8 }}%; width: {{ $L['qr']['s'] / 17.12 }}%;"></span>
    @unless($k['aktif'])
        <span class="ktm-cap">TIDAK AKTIF</span>
    @endunless
</div>

@once
    @push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;700&display=swap">
    <style>
        /* Warna ditulis tetap (bukan var(--siakad-*)) agar kartu tidak berubah saat tema sentral diganti. */
        .ktm-sttgpi { position: relative; container-type: inline-size; width: 100%; max-width: 520px; aspect-ratio: 1712 / 1080; margin: 0 auto;
            border-radius: 2.2cqw; overflow: hidden; background-color: #FDDFC1; background-size: 100% 100%; background-repeat: no-repeat;
            box-shadow: 0 10px 28px rgba(61, 35, 80, .28); font-family: 'Poppins', 'Segoe UI', Arial, sans-serif; color: var(--ktm-ungu); }
        .ktm-sttgpi * { box-sizing: border-box; }
        .ktm-foto { position: absolute; border-radius: 1.4cqw; overflow: hidden; border: .35cqw solid var(--ktm-oranye); background: #F6C99A;
            box-shadow: 0 .8cqw 1.6cqw rgba(61, 35, 80, .3); display: flex; align-items: center; justify-content: center; }
        .ktm-foto img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .ktm-inisial { font-weight: 700; font-size: 9cqw; color: var(--ktm-ungu); opacity: .55; }
        .ktm-status { position: absolute; left: 0; right: 0; bottom: 0; background: var(--ktm-status); color: #fff; text-align: center;
            font-weight: 700; font-size: 1.35cqw; letter-spacing: .12cqw; padding: .55cqw 0; }
        .ktm-nilai { position: absolute; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .ktm-nim { letter-spacing: .12cqw; }
        .ktm-token { font-family: 'Courier New', monospace; opacity: .7; }
        .ktm-qr { position: absolute; aspect-ratio: 1; display: block; }
        .ktm-qr svg { width: 100%; height: 100%; display: block; }
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

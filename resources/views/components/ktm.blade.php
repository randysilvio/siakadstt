{{--
    Kartu Tanda Mahasiswa digital (E-KTM).
    Desain TETAP: warna ditulis langsung dari App\Support\Ktm::WARNA dan tidak memakai kelas Bootstrap/variabel
    Theme Engine (--siakad-*), sehingga kartu tidak berubah ketika tema sentral SIAKAD diganti.
    Pemakaian: <x-ktm :mahasiswa="$mahasiswa" />
--}}
@props(['mahasiswa'])
@php
    $k = \App\Support\Ktm::data($mahasiswa);
    $w = $k['warna'];
    $idQr = 'ktm-qr-' . $mahasiswa->id;
@endphp
<div class="ktm-sttgpi" style="--ktm-latar: {{ $w['latar'] }}; --ktm-latar2: {{ $w['latar_ujung'] }}; --ktm-emas: {{ $w['emas'] }}; --ktm-emas-muda: {{ $w['emas_muda'] }}; --ktm-teks: {{ $w['teks'] }}; --ktm-redup: {{ $w['teks_redup'] }}; --ktm-status: {{ $k['aktif'] ? $w['aktif'] : $w['nonaktif'] }};">
    <div class="ktm-kepala">
        <span class="ktm-logo"><img src="{{ $k['kampus']['logo_url'] }}" alt="Logo"></span>
        <span class="ktm-kampus">
            <span class="ktm-kampus-nama">{{ $k['kampus']['nama'] }}</span>
            <span class="ktm-kampus-sub">Kartu Tanda Mahasiswa</span>
        </span>
        <span class="ktm-label">KTM</span>
    </div>

    <div class="ktm-isi">
        <span class="ktm-foto">
            @if($k['foto_url'])
                <img src="{{ $k['foto_url'] }}" alt="Foto">
            @else
                <span class="ktm-inisial">{{ mb_substr($k['nama'], 0, 1) }}</span>
            @endif
        </span>
        <span class="ktm-data">
            <span class="ktm-nama">{{ $k['nama'] }}</span>
            <span class="ktm-nim">{{ $k['nim'] }}</span>
            <span class="ktm-baris"><span>Program Studi</span>{{ $k['prodi'] }}</span>
            <span class="ktm-baris"><span>Angkatan</span>{{ $k['angkatan'] }}</span>
            <span class="ktm-status">{{ strtoupper($k['status']) }}</span>
        </span>
        <span class="ktm-qr" id="{{ $idQr }}" data-url="{{ $k['verifikasi_url'] }}" title="Pindai untuk verifikasi"></span>
    </div>

    <div class="ktm-kaki">
        <span>{{ $k['kampus']['singkatan'] }} · {{ $k['kampus']['kota'] }}</span>
        <span class="ktm-token">{{ $k['token'] }}</span>
    </div>
    @unless($k['aktif'])
        <span class="ktm-cap">TIDAK AKTIF</span>
    @endunless
</div>

@once
    @push('styles')
    <style>
        .ktm-sttgpi { position: relative; overflow: hidden; width: 100%; max-width: 440px; aspect-ratio: 85.6 / 54; margin: 0 auto;
            border-radius: 16px; color: var(--ktm-teks); font-family: 'Segoe UI', Roboto, Arial, sans-serif; line-height: 1.2;
            background: radial-gradient(120% 90% at 100% 0%, var(--ktm-latar2) 0%, var(--ktm-latar) 60%);
            box-shadow: 0 10px 28px rgba(11, 37, 69, .35); display: flex; flex-direction: column; padding: 0; box-sizing: border-box; }
        .ktm-sttgpi::before { content: ''; position: absolute; inset: 0; pointer-events: none; opacity: .07;
            background-image: repeating-linear-gradient(135deg, var(--ktm-emas) 0 2px, transparent 2px 14px); }
        .ktm-sttgpi * { box-sizing: border-box; }
        .ktm-kepala { position: relative; flex-shrink: 0; margin: 3.6% 4.6% 0; display: flex; align-items: center; gap: 3%; padding-bottom: 2.4%; border-bottom: 1px solid var(--ktm-emas); }
        .ktm-logo { flex: 0 0 11%; aspect-ratio: 1; border-radius: 50%; background: #fff; display: flex; align-items: center; justify-content: center; padding: 1.5%; }
        .ktm-logo img { width: 100%; height: 100%; object-fit: contain; }
        .ktm-kampus { flex: 1; min-width: 0; display: flex; flex-direction: column; }
        .ktm-kampus-nama { font-weight: 800; font-size: clamp(8px, 2.6vw, 11px); letter-spacing: .02em; text-transform: uppercase; }
        .ktm-kampus-sub { color: var(--ktm-emas-muda); font-size: clamp(7px, 2.1vw, 9px); letter-spacing: .18em; text-transform: uppercase; margin-top: 2px; }
        .ktm-label { font-weight: 900; font-size: clamp(11px, 3.6vw, 16px); color: var(--ktm-latar); background: var(--ktm-emas); padding: 2px 8px; border-radius: 6px; letter-spacing: .08em; }
        .ktm-isi { position: relative; margin: 0 4.6%; flex: 1; min-height: 0; display: flex; align-items: center; gap: 4%; padding: 2.2% 0; }
        .ktm-foto { flex: 0 0 21%; aspect-ratio: 3 / 4; border-radius: 8px; border: 2px solid var(--ktm-emas); overflow: hidden; background: rgba(255,255,255,.12); display: flex; align-items: center; justify-content: center; }
        .ktm-foto img { width: 100%; height: 100%; object-fit: cover; }
        .ktm-inisial { font-size: clamp(18px, 7vw, 30px); font-weight: 800; color: var(--ktm-emas-muda); }
        .ktm-data { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
        .ktm-nama { font-weight: 800; font-size: clamp(9px, 3vw, 14px); line-height: 1.15; text-transform: uppercase; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
        .ktm-nim { font-family: 'Courier New', monospace; font-weight: 700; font-size: clamp(10px, 3.4vw, 15px); color: var(--ktm-emas-muda); letter-spacing: .06em; }
        .ktm-baris { font-size: clamp(7px, 2.3vw, 10px); color: var(--ktm-teks); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .ktm-baris span { display: inline; margin-right: 4px; color: var(--ktm-redup); font-size: .85em; text-transform: uppercase; letter-spacing: .08em; }
        .ktm-status { align-self: flex-start; margin-top: 2px; font-size: clamp(7px, 2.1vw, 9px); font-weight: 800; letter-spacing: .1em; background: var(--ktm-status); color: #fff; padding: 2px 7px; border-radius: 20px; }
        .ktm-qr { flex: 0 0 23%; aspect-ratio: 1; background: #fff; border-radius: 8px; padding: 2.2%; display: flex; align-items: center; justify-content: center; }
        .ktm-qr svg { width: 100%; height: 100%; display: block; }
        .ktm-kaki { position: relative; flex-shrink: 0; padding: 1.8% 4.6%; background: var(--ktm-emas); color: var(--ktm-latar); display: flex; justify-content: space-between; gap: 8px;
            font-size: clamp(6px, 2vw, 9px); font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
        .ktm-token { font-family: 'Courier New', monospace; letter-spacing: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .ktm-cap { position: absolute; top: 46%; left: 50%; transform: translate(-50%, -50%) rotate(-14deg); border: 3px solid #f87171; color: #f87171; padding: 4px 14px;
            font-weight: 900; font-size: clamp(14px, 5vw, 22px); letter-spacing: .12em; border-radius: 8px; background: rgba(11,37,69,.6); }
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
            var svg = el.querySelector('svg');
            if (svg) { svg.querySelectorAll('path, rect').forEach(function (n) { if ((n.getAttribute('fill') || '').toLowerCase() === 'black' || n.getAttribute('fill') === '#000000') n.setAttribute('fill', '#0B2545'); }); }
        });
    </script>
    @endpush
@endonce

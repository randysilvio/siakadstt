{{--
    Meta pratinjau tautan (Open Graph & Twitter Card) untuk WhatsApp, Facebook, Instagram, X, Telegram, LinkedIn.
    Parameter (semua opsional): $judul, $deskripsi, $gambar (array dari App\Support\GambarBagikan), $tautan, $jenis (website|article),
    $terbit / $diubah (Carbon), $bagian (kategori artikel).
--}}
@php
    $namaSitus  = \App\Models\Pengaturan::get('nama_kampus', 'Sekolah Tinggi Theologia GPI Papua');
    $singkatan  = \App\Models\Pengaturan::get('singkatan_kampus', 'STT GPI Papua');
    $metaJudul  = trim($judul ?? '') ?: $singkatan . ' — ' . $namaSitus;
    $metaDesk   = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($deskripsi ?? '')))) ?: ($namaSitus . ' — informasi akademik, berita, dan penerimaan mahasiswa baru.'), 200);
    $metaGambar = $gambar ?? \App\Support\GambarBagikan::situs();
    $metaTautan = \App\Support\GambarBagikan::absolut($tautan ?? url()->current());
    $metaJenis  = $jenis ?? 'website';
@endphp
<meta name="description" content="{{ $metaDesk }}">
<link rel="canonical" href="{{ $metaTautan }}">
<meta property="og:site_name" content="{{ $singkatan }}">
<meta property="og:locale" content="id_ID">
<meta property="og:type" content="{{ $metaJenis }}">
<meta property="og:title" content="{{ $metaJudul }}">
<meta property="og:description" content="{{ $metaDesk }}">
<meta property="og:url" content="{{ $metaTautan }}">
<meta property="og:image" content="{{ $metaGambar['url'] }}">
@if(str_starts_with($metaGambar['url'], 'https://'))<meta property="og:image:secure_url" content="{{ $metaGambar['url'] }}">@endif
<meta property="og:image:type" content="{{ str_ends_with(strtolower(parse_url($metaGambar['url'], PHP_URL_PATH) ?? ''), '.png') ? 'image/png' : 'image/jpeg' }}">
@if(!empty($metaGambar['lebar']))
<meta property="og:image:width" content="{{ $metaGambar['lebar'] }}">
<meta property="og:image:height" content="{{ $metaGambar['tinggi'] }}">
@endif
<meta property="og:image:alt" content="{{ $metaJudul }}">
@if($metaJenis === 'article')
@isset($terbit)<meta property="article:published_time" content="{{ $terbit->toIso8601String() }}">@endisset
@isset($diubah)<meta property="article:modified_time" content="{{ $diubah->toIso8601String() }}">@endisset
@if(!empty($bagian))<meta property="article:section" content="{{ $bagian }}">@endif
@endif
<meta name="twitter:card" content="{{ !empty($metaGambar['lebar']) ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $metaJudul }}">
<meta name="twitter:description" content="{{ $metaDesk }}">
<meta name="twitter:image" content="{{ $metaGambar['url'] }}">

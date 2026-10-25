@php
    $kopBaris1 = \App\Models\Pengaturan::get('kop_baris_1', 'GEREJA PROTESTAN INDONESIA DI PAPUA');
    $kopBaris2 = \App\Models\Pengaturan::get('kop_baris_2', 'SEKOLAH TINGGI TEOLOGI (STT) GPI PAPUA');
    $kopBaris3 = \App\Models\Pengaturan::get('kop_baris_3', 'TERAKREDITASI BAN-PT');
    $kopAlamat = \App\Models\Pengaturan::get('kop_alamat', 'Jl. Jend. Sudirman, Kotak Pos 116, Fakfak 98611 - Provinsi Papua Barat | Email: info@sttgpipapua.ac.id');
    $logoFile  = \App\Models\Pengaturan::logoPhysicalPath();
    $logoBase64 = '';
    if (file_exists($logoFile)) {
        $ext = pathinfo($logoFile, PATHINFO_EXTENSION) ?: 'png';
        $logoBase64 = 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($logoFile));
    }
@endphp

<table style="width: 100%; border-collapse: collapse; border-bottom: 2.5px double #000; padding-bottom: 6px; margin-bottom: 10px;">
    <tr>
        <td style="width: 13%; text-align: center; vertical-align: middle; border: none; padding: 0;">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo" style="width: 62px; height: 62px; object-fit: contain;">
            @endif
        </td>
        <td style="width: 87%; text-align: center; vertical-align: middle; border: none; padding: 0; line-height: 1.2;">
            <div style="font-size: 10pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">{{ $kopBaris1 }}</div>
            <div style="font-size: 13.5pt; font-weight: bold; text-transform: uppercase; margin: 1px 0;">{{ $kopBaris2 }}</div>
            @if(!empty($kopBaris3))
                <div style="font-size: 8.5pt; font-weight: bold; text-transform: uppercase;">{{ $kopBaris3 }}</div>
            @endif
            <div style="font-size: 8pt; font-weight: normal; margin-top: 2px;">{{ $kopAlamat }}</div>
        </td>
    </tr>
</table>
@php
    $logoPath = public_path('images/logo.png');
    if (!file_exists($logoPath)) {
        $logoPath = public_path('assets/images/logo-stt.png');
    }
    $logoBase64 = '';
    if (file_exists($logoPath)) {
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoBase64 = 'data:image/png;base64,' . $logoData;
    } else {
        $logoBase64 = asset('images/logo.png');
    }
@endphp

<style>
    .kop-surat-wrapper {
        width: 100%;
        margin-bottom: 18px;
        background-color: #ffffff;
        color: #000000;
        font-family: 'Times New Roman', Times, serif;
    }
    .kop-surat-table {
        width: 100%;
        border-collapse: collapse;
        border: none;
        margin-bottom: 0;
    }
    .kop-surat-table td {
        border: none !important;
        padding: 0;
        vertical-align: middle;
    }
    .kop-logo-cell {
        width: 14%;
        text-align: center;
    }
    .kop-logo-img {
        width: 75px;
        height: auto;
    }
    .kop-text-cell {
        width: 86%;
        text-align: center;
        line-height: 1.25;
    }
    .kop-yayasan {
        font-size: 12pt;
        font-weight: bold;
        text-transform: uppercase;
        margin: 0;
        letter-spacing: 0.5px;
    }
    .kop-institusi {
        font-size: 15pt;
        font-weight: bold;
        text-transform: uppercase;
        margin: 2px 0;
        letter-spacing: 0.5px;
    }
    .kop-akreditasi {
        font-size: 10pt;
        font-weight: bold;
        text-transform: uppercase;
        margin: 2px 0;
    }
    .kop-alamat {
        font-size: 9pt;
        margin: 2px 0 0 0;
    }
    .kop-garis-ganda {
        border-top: 3px solid #000000;
        border-bottom: 1px solid #000000;
        height: 2px;
        margin-top: 8px;
    }
</style>

<div class="kop-surat-wrapper">
    <table class="kop-surat-table">
        <tr>
            <td class="kop-logo-cell">
                <img src="{{ $logoBase64 }}" alt="Logo STT GPI Papua" class="kop-logo-img">
            </td>
            <td class="kop-text-cell">
                <div class="kop-yayasan">GEREJA PROTESTAN INDONESIA DI PAPUA</div>
                <div class="kop-institusi">SEKOLAH TINGGI TEOLOGI (STT) GPI PAPUA</div>
                <div class="kop-akreditasi">TERAKREDITASI BAN-PT No. 1599/SK/BAN-PT/Ak.Ppj/PT/X/2022</div>
                <div class="kop-alamat">
                    Jl. Jenderal Sudirman / Jl. Pattimura No. 1, P.O. Box 138, Telp. (0956) 22252, Fakfak - Papua Barat<br>
                    Website: www.sttgpipapua.ac.id | Email: info@sttgpipapua.ac.id
                </div>
            </td>
        </tr>
    </table>
    <div class="kop-garis-ganda"></div>
</div>
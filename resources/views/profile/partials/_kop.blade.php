<style>
    /* Styling khusus untuk Kop Surat */
    .kop-container {
        width: 100%;
        margin-bottom: 20px;
    }
    .kop-table {
        width: 100%;
        border-collapse: collapse;
        border: none;
    }
    .kop-table td {
        border: none;
        vertical-align: middle;
    }
    .logo-cell {
        width: 15%;
        text-align: center;
    }
    .text-cell {
        width: 85%;
        text-align: center;
        line-height: 1.2; /* Mengatur jarak antar baris */
    }
    .kop-org {
        font-size: 14px;
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 2px;
    }
    .kop-inst {
        font-size: 18px; /* Ukuran font nama kampus lebih besar */
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 5px;
        color: #000; /* Atau warna biru tua jika ingin berwarna */
    }
    .kop-address {
        font-size: 11px;
        font-style: normal;
    }
    .kop-contact {
        font-size: 10px;
        font-style: italic;
    }
    /* Garis Ganda di bawah kop (Khas surat resmi Indonesia) */
    .kop-line {
        border-top: 3px solid black;
        border-bottom: 1px solid black;
        height: 2px;
        margin-top: 8px;
        margin-bottom: 5px;
    }
</style>

<div class="kop-container">
    <table class="kop-table">
        <tr>
            <td class="logo-cell">
                {{-- 
                    PENTING UNTUK DOMPDF:
                    Gunakan public_path() agar gambar terbaca saat generate PDF.
                    Pastikan file ada di folder public/assets/images/logo-stt.png 
                --}}
                <img src="{{ public_path('assets/images/logo-stt.png') }}" alt="Logo STT" style="width: 80px; height: auto;">
            </td>

            <td class="text-cell">
                <div class="kop-org">GEREJA PROTESTAN INDONESIA DI PAPUA</div>
                <div class="kop-inst">SEKOLAH TINGGI TEOLOGI (STT) GPI PAPUA</div>
                <div class="kop-address">
                    Jl. Jend. A. Yani, Fakfak, Papua Barat<br>
                    Terakreditasi BAN-PT
                </div>
                <div class="kop-contact">
                    Website: www.sttgpipapua.ac.id | Email: admin@sttgpipapua.ac.id
                </div>
            </td>
        </tr>
    </table>
    
    <div class="kop-line"></div>
</div>
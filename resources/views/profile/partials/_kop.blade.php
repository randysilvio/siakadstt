<style>
    /* STYLING KAKU & FORMAL KOP SURAT (0PX PRESISI) */
    .kop-container {
        width: 100%;
        margin-bottom: 20px;
        background-color: #ffffff;
    }
    .kop-table {
        width: 100%;
        border-collapse: collapse;
        border: none;
    }
    .kop-table td {
        border: none;
        vertical-align: middle;
        padding: 0;
    }
    .logo-cell {
        width: 15%;
        text-align: center;
    }
    .text-cell {
        width: 85%;
        text-align: center;
        line-height: 1.25;
    }
    .kop-org {
        font-size: 14px;
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 2px;
        color: #000000;
    }
    .kop-inst {
        font-size: 18px;
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 4px;
        color: #000000;
        letter-spacing: 0.5px;
    }
    .kop-address {
        font-size: 11px;
        font-style: normal;
        text-transform: uppercase;
        color: #212529;
    }
    .kop-contact {
        font-size: 10px;
        font-family: 'Courier New', Courier, monospace;
        font-weight: bold;
        text-transform: uppercase;
        margin-top: 3px;
        color: #000000;
    }
    /* Pembatas Bawah Standar Dokumen Enterprise Indonesia */
    .kop-line {
        border-top: 3px solid #000000;
        border-bottom: 1px solid #000000;
        height: 2px;
        margin-top: 10px;
        margin-bottom: 6px;
        background-color: transparent;
    }
</style>

<div class="kop-container">
    <table class="kop-table">
        <tr>
            <td class="logo-cell">
                {{-- Penarikan aset fisik gambar mutlak untuk rendering DOMPDF --}}
                <img src="{{ public_path('assets/images/logo-stt.png') }}" alt="Logo Institusi" style="width: 80px; height: auto;">
            </td>

            <td class="text-cell">
                <div class="kop-org">GEREJA PROTESTAN INDONESIA DI PAPUA</div>
                <div class="kop-inst">SEKOLAH TINGGI TEOLOGI (STT) GPI PAPUA</div>
                <div class="kop-address">
                    JL. JEND. A. YANI, FAKFAK, PAPUA BARAT<br>
                    TERAKREDITASI RESMI BAN-PT
                </div>
                <div class="kop-contact">
                    WEBSITE: WWW.STTGPIPAPUA.AC.ID | EMAIL: ADMIN@STTGPIPAPUA.AC.ID
                </div>
            </td>
        </tr>
    </table>
    
    <div class="kop-line"></div>
</div>
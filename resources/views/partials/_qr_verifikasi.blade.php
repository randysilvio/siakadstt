@php
    $jenisDoc = $jenisDoc ?? 'TRANSKRIP';
    $idEntitas = $idEntitas ?? ($mahasiswa->id ?? 0);
    $tokenVerifikasi = $customToken ?? \App\Http\Controllers\VerifikasiDokumenController::buatTokenDokumen($jenisDoc, (int) $idEntitas);
    $urlVerifikasi = route('verifikasi.dokumen', $tokenVerifikasi);
@endphp

<div style="margin-top: 18px; border: 1px solid #000; padding: 8px 10px; background-color: #fafafa; page-break-inside: avoid;">
    <table style="width: 100%; border-collapse: collapse; border: none;">
        <tr>
            <td style="width: 78%; border: none; vertical-align: middle; font-size: 8pt; line-height: 1.35; text-align: left;">
                <strong style="text-transform: uppercase; font-size: 8.5pt;">OTENTIKASI DOKUMEN DIGITAL — SIAKAD STT GPI PAPUA</strong><br>
                Dokumen ini diterbitkan secara elektronik oleh Sistem Informasi Akademik STT GPI Papua.<br>
                Kode Hash Verifikasi: <span style="font-family: 'Courier New', Courier, monospace; font-weight: bold;">{{ $tokenVerifikasi }}</span><br>
                Verifikasi keaslian dokumen dapat diakses secara publik melalui: <span style="font-family: 'Courier New', Courier, monospace; text-decoration: underline;">{{ $urlVerifikasi }}</span>
            </td>
            <td style="width: 22%; border: none; vertical-align: middle; text-align: right;">
                <div style="display: inline-block; border: 2px solid #000; padding: 4px 6px; text-align: center; font-family: 'Courier New', Courier, monospace; font-size: 7.5pt; font-weight: bold; background: #fff;">
                    [ DIGITAL SEAL ]<br>
                    STT GPI PAPUA<br>
                    <span style="font-size: 6.5pt;">{{ substr($tokenVerifikasi, -12) }}</span>
                </div>
            </td>
        </tr>
    </table>
</div>
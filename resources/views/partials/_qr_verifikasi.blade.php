@php
    $jenisDoc = $jenisDoc ?? 'TRANSKRIP';
    $idEntitas = $idEntitas ?? ($mahasiswa->id ?? 0);
    $tokenVerifikasi = $customToken ?? \App\Http\Controllers\VerifikasiDokumenController::buatTokenDokumen($jenisDoc, (int) $idEntitas);
    $urlVerifikasi = route('verifikasi.dokumen', $tokenVerifikasi);
    $singkatanPt = \App\Models\Pengaturan::get('singkatan_kampus', 'STT GPI PAPUA');
@endphp

<div style="margin-top: 8px; border: 1px solid #444; padding: 3px 6px; background-color: #fafafa; page-break-inside: avoid;">
    <table style="width: 100%; border-collapse: collapse; border: none;">
        <tr>
            <td style="width: 80%; border: none; vertical-align: middle; font-size: 6.8pt; line-height: 1.2; text-align: left; color: #222;">
                <strong>VALIDASI DOKUMEN ELEKTRONIK — SIAKAD {{ strtoupper($singkatanPt) }}</strong> |
                Kode Hash: <span style="font-family: 'Courier New', Courier, monospace; font-weight: bold;">{{ $tokenVerifikasi }}</span><br>
                Cek keaslian dokumen secara daring: <span style="font-family: 'Courier New', Courier, monospace;">{{ $urlVerifikasi }}</span>
            </td>
            <td style="width: 20%; border: none; vertical-align: middle; text-align: right;">
                <span style="display: inline-block; border: 1px solid #000; padding: 2px 5px; font-family: 'Courier New', Courier, monospace; font-size: 6.5pt; font-weight: bold; background: #fff;">
                    SEAL: {{ substr($tokenVerifikasi, -10) }}
                </span>
            </td>
        </tr>
    </table>
</div>
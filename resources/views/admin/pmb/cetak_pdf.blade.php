<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pendaftar PMB - STT GPI Papua</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; line-height: 1.4; max-width: 210mm; margin: 0 auto; padding: 20px; color: #000; }
        .doc-title { text-align: center; font-weight: bold; font-size: 13pt; margin: 10px 0 5px 0; text-transform: uppercase; text-decoration: underline; }
        .meta-info { text-align: center; font-size: 9.5pt; margin-bottom: 20px; font-family: 'Courier New', Courier, monospace; font-weight: bold; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 6px 8px; vertical-align: middle; }
        table.data-table th { background-color: #212529; color: #ffffff; text-align: center; font-weight: bold; text-transform: uppercase; font-size: 9pt; }
        table.data-table td { font-size: 9pt; }

        .text-center { text-align: center; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        
        .signature-container { width: 100%; display: table; margin-top: 35px; page-break-inside: avoid; }
        .signature-box { display: table-cell; width: 50%; text-align: center; vertical-align: bottom; }
        .signature-spacer { display: table-cell; width: 50%; }
        .signature-line { margin-top: 65px; font-weight: bold; text-decoration: underline; text-transform: uppercase; }

        .no-print { text-align: right; margin-bottom: 15px; border-bottom: 1px solid #ccc; padding-bottom: 12px; }
        .btn-print { background-color: #212529; color: #fff; border: none; padding: 8px 18px; font-family: Arial, sans-serif; cursor: pointer; text-transform: uppercase; font-size: 11px; font-weight: bold; }

        @media print { 
            @page { size: A4 portrait; margin: 1.5cm; }
            .no-print { display: none !important; } 
            body { padding: 0; margin: 0; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    
    <div class="no-print">
        <button onclick="window.print()" class="btn-print">Cetak Rekapitulasi Fisik</button>
    </div>

    @include('partials._kop')

    <div class="doc-title">DAFTAR KANDIDAT PENERIMAAN MAHASISWA BARU (PMB)</div>
    <div class="meta-info">
        TANGGAL CETAK SISTEM: {{ \Carbon\Carbon::now()->translatedFormat('d F Y - H:i') }} WIT
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 16%;">NO. DAFTAR</th>
                <th>NAMA KANDIDAT</th>
                <th style="width: 6%;">JK</th>
                <th style="width: 15%;">NO. PONSEL</th>
                <th style="width: 24%;">PILIHAN PRODI 1</th>
                <th style="width: 12%;">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pendaftars as $index => $item)
            <tr>
                <td class="text-center font-monospace">{{ $index + 1 }}</td>
                <td class="text-center font-monospace">{{ $item->no_pendaftaran ?? '-' }}</td>
                <td style="font-weight: bold;">{{ strtoupper($item->user->name ?? 'DATA KOSONG') }}</td>
                <td class="text-center">{{ $item->jenis_kelamin ?? '-' }}</td>
                <td class="text-center font-monospace">{{ $item->no_hp ?? '-' }}</td>
                <td>{{ strtoupper($item->prodi1->nama_prodi ?? 'BELUM DITETAPKAN') }}</td>
                <td class="text-center font-monospace">
                    @if($item->status_pendaftaran == 'lulus')
                        DITERIMA
                    @elseif($item->status_pendaftaran == 'tidak_lulus')
                        DITOLAK
                    @elseif($item->status_pendaftaran == 'menunggu_verifikasi')
                        VERIFIKASI
                    @else
                        DRAFT
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 20px;">Tidak terdapat data kandidat pendaftar.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature-container">
        <div class="signature-spacer"></div>
        <div class="signature-box">
            Fakfak, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
            Ketua Panitia PMB
            <div class="signature-line">( ........................................ )</div>
        </div>
    </div>

</body>
</html>
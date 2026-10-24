<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Produktivitas Tridharma Dosen - STT GPI Papua</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; color: #000; line-height: 1.35; }
        @page { size: A4 landscape; margin: 1.2cm; }
        .doc-title { text-align: center; font-weight: bold; font-size: 13pt; text-decoration: underline; text-transform: uppercase; margin: 8px 0 4px 0; }
        .doc-sub { text-align: center; font-size: 9.5pt; font-weight: bold; margin-bottom: 14px; font-family: 'Courier New', Courier, monospace; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 5px 6px; font-size: 9pt; vertical-align: middle; }
        table.data-table th { background-color: #212529; color: #ffffff; text-align: center; font-weight: bold; text-transform: uppercase; }
        .text-center { text-align: center; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        .ttd { margin-top: 25px; width: 100%; border-collapse: collapse; font-size: 10pt; text-transform: uppercase; page-break-inside: avoid; }
        .ttd td { border: none; text-align: center; vertical-align: top; }
    </style>
</head>
<body>

    @include('partials._kop')

    <div class="doc-title">LAPORAN PRODUKTIVITAS PENELITIAN, PUBLIKASI, & PENGABDIAN MASYARAKAT DOSEN</div>
    <div class="doc-sub">PANGKALAN DATA TRIDHARMA PENDIDIK STT GPI PAPUA | TANGGAL CETAK: {{ date('d/m/Y') }}</div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th style="width: 18%;">NAMA DOSEN & NIDN</th>
                <th style="width: 10%;">UNSUR</th>
                <th style="width: 28%;">JUDUL KARYA ILMIAH / KEGIATAN PKM</th>
                <th style="width: 18%;">KATEGORI & PENERBIT/LOKASI</th>
                <th style="width: 8%;">TAHUN</th>
                <th style="width: 7%;">SKS BKD</th>
                <th style="width: 7%;">KUM</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tridharmas as $i => $item)
                <tr>
                    <td class="text-center font-monospace">{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ strtoupper($item->dosen->nama_lengkap ?? '-') }}</strong><br>
                        <span class="font-monospace" style="font-size: 8pt;">NIDN: {{ $item->dosen->nidn ?? '-' }}</span>
                    </td>
                    <td class="text-center font-monospace">{{ strtoupper($item->unsur) }}</td>
                    <td><strong>{{ strtoupper($item->judul) }}</strong> ({{ strtoupper($item->peran) }})</td>
                    <td>
                        {{ strtoupper($item->kategori) }}<br>
                        <small>{{ $item->nama_jurnal_penerbit_lokasi ?? '-' }} {{ $item->nomor_identitas ? '(' . $item->nomor_identitas . ')' : '' }}</small>
                    </td>
                    <td class="text-center font-monospace">{{ $item->tahun }} ({{ $item->semester }})</td>
                    <td class="text-center font-monospace">{{ $item->sks_bkd }}</td>
                    <td class="text-center font-monospace">{{ $item->angka_kredit }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center">Belum ada data portofolio Tridharma dosen yang tercatat.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="ttd">
        <tr>
            <td style="width: 60%;"></td>
            <td style="width: 40%;">
                Fakfak, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                Kepala Unit Penjaminan Mutu (UPM),<br><br><br><br>
                <strong><u>( ................................................. )</u></strong>
            </td>
        </tr>
    </table>

</body>
</html>
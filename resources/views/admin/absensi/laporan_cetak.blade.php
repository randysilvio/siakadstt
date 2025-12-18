<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Absensi</title>
    <style>
        body { 
            font-family: 'Times New Roman', Times, serif; 
            font-size: 12px; 
            max-width: 210mm; /* Lebar A4 */
            margin: 0 auto;
            padding: 20px;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px 8px; text-align: left; }
        th { background-color: #f0f0f0; text-align: center; }
        .text-center { text-align: center; }
        
        /* Area Tanda Tangan */
        .signature-section { margin-top: 50px; width: 100%; display: table; }
        .sig-box { display: table-cell; width: 33%; text-align: center; vertical-align: top; }
        
        /* Tombol Cetak Manual (Hanya tampil di layar) */
        .action-bar {
            text-align: right;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #ccc;
        }
        .btn-print {
            background-color: #0d6efd;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-family: sans-serif;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-print:hover { background-color: #0b5ed7; }

        /* PENTING: Sembunyikan tombol saat dicetak ke kertas/PDF */
        @media print {
            @page { size: A4; margin: 2cm; }
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body> 
    {{-- HAPUS 'onload' DI SINI AGAR TIDAK LANGSUNG PRINT --}}

    <div class="action-bar no-print">
        <button onclick="window.print()" class="btn-print">
            🖨️ Cetak Dokumen
        </button>
    </div>

    {{-- Pastikan path ini sesuai dengan lokasi file kop Anda --}}
    @include('partials._kop')

    <h3 style="text-align: center; margin-top: 10px; text-transform: uppercase;">Laporan Absensi Pegawai</h3>
    
    @if(!empty($filterInfo))
        <div style="text-align: center; margin-bottom: 20px; font-style: italic;">
            {{ implode(' | ', $filterInfo) }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th>Nama Pegawai</th>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 10%;">Masuk</th>
                <th style="width: 10%;">Pulang</th>
                <th style="width: 10%;">Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporan as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $item->user->name ?? '-' }}</td>
                <td class="text-center">{{ $item->tanggal_absensi->translatedFormat('d/m/Y') }}</td>
                <td class="text-center">{{ $item->waktu_check_in ? $item->waktu_check_in->format('H:i') : '-' }}</td>
                <td class="text-center">{{ $item->waktu_check_out ? $item->waktu_check_out->format('H:i') : '-' }}</td>
                <td class="text-center">{{ $item->status_kehadiran }}</td>
                <td>{{ $item->keterangan ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data absensi pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature-section">
        <div class="sig-box">
            <br>Mengetahui,<br>Ketua STT GPI Papua<br><br><br><br>
            <strong>( .................................................... )</strong>
        </div>
        <div class="sig-box"></div>
        <div class="sig-box">
            Fakfak, {{ date('d F Y') }}<br>Kepala BAAK<br><br><br><br>
            <strong>( .................................................... )</strong>
        </div>
    </div>

</body>
</html>
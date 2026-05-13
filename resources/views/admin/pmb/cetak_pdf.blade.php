<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pendaftar PMB - Institusi</title>
    <style>
        body { 
            font-family: 'Times New Roman', Times, serif; 
            font-size: 11pt; 
            line-height: 1.4;
            max-width: 210mm; 
            margin: 0 auto; 
            padding: 20px;
            color: #000;
        }
        
        /* Gaya Khusus Kop Surat Rata Tengah */
        .kop-container {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .kop-container img {
            width: 55px; /* Mengecilkan ukuran logo */
            height: auto;
            display: block;
            margin: 0 auto 10px auto; /* Logo di tengah atas */
        }

        .kop-text {
            text-align: center;
            line-height: 1.2;
        }

        .doc-title { 
            text-align: center; 
            font-weight: bold; 
            font-size: 13pt; 
            margin: 25px 0 10px 0; 
            text-transform: uppercase; 
            text-decoration: underline; 
        }
        
        .meta-info { text-align: center; font-size: 10pt; margin-bottom: 25px; font-style: italic; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 6px 8px; vertical-align: middle; }
        table.data-table th { background-color: #e9ecef; text-align: center; font-weight: bold; text-transform: uppercase; font-size: 9pt; }
        table.data-table td { font-size: 9pt; }

        .text-center { text-align: center; }
        
        .signature-container { width: 100%; display: table; margin-top: 40px; page-break-inside: avoid; }
        .signature-box { display: table-cell; width: 50%; text-align: center; vertical-align: bottom; }
        .signature-spacer { display: table-cell; width: 50%; }
        .signature-line { margin-top: 70px; font-weight: bold; text-decoration: underline; text-transform: uppercase; }

        .no-print { text-align: right; margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 15px; }
        .btn-print { background-color: #000; color: #fff; border: none; padding: 8px 20px; font-family: Arial, sans-serif; cursor: pointer; text-transform: uppercase; font-size: 12px; }

        @media print { 
            @page { size: A4 portrait; margin: 1.5cm; }
            .no-print { display: none !important; } 
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body>
    
    <div class="no-print">
        <button onclick="window.print()" class="btn-print">Cetak Rekapitulasi Fisik</button>
    </div>

    {{-- 
        Struktur Kop Surat Terpusat 
        (Jika Bapak menggunakan @include('partials._kop'), pastikan isinya juga disesuaikan 
        atau gunakan struktur di bawah ini secara langsung) 
    --}}
    <div class="kop-container">
        <img src="{{ asset('images/logo.png') }}" alt="Logo Institusi">
        <div class="kop-text">
            <strong style="font-size: 14pt;">SEKOLAH TINGGI TEOLOGI (STT) GPI PAPUA</strong><br>
            <span style="font-size: 10pt;">Terakreditasi BAN-PT</span><br>
            <small style="font-size: 9pt;">Jl. Ahmad Yani, Fakfak, Papua Barat. Email: info@sttgpipapua.ac.id</small>
        </div>
    </div>

    <div class="doc-title">DAFTAR KANDIDAT PENERIMAAN MAHASISWA BARU (PMB)</div>
    <div class="meta-info">
        Catatan Tarikan Data Sistem (Log): {{ \Carbon\Carbon::now()->translatedFormat('d F Y - H:i:s') }} WIT
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 15%;">No. Daftar</th>
                <th>Nama Kandidat</th>
                <th style="width: 5%;">JK</th>
                <th style="width: 15%;">No. Ponsel</th>
                <th style="width: 25%;">Minat Prodi</th>
                <th style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pendaftars as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $item->no_pendaftaran ?? '-' }}</td>
                <td>{{ strtoupper($item->user->name ?? 'DATA KOSONG') }}</td>
                <td class="text-center">{{ $item->jenis_kelamin ?? '-' }}</td>
                <td class="text-center">{{ $item->no_hp ?? $item->user->no_hp ?? '-' }}</td>
                <td>{{ strtoupper($item->prodi1->nama_prodi ?? 'BELUM DITETAPKAN') }}</td>
                <td class="text-center">
                    @if($item->status_pendaftaran == 'lulus')
                        DITERIMA
                    @elseif($item->status_pendaftaran == 'tidak_lulus')
                        DITOLAK
                    @elseif($item->status_pendaftaran == 'menunggu_verifikasi')
                        PROSES
                    @else
                        DRAFT
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 20px;">Tidak terdapat data kandidat.</td>
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
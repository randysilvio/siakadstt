<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        
        /* Styling Judul Laporan (Di bawah Kop) */
        .report-title {
            text-align: center;
            margin-top: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
        }

        /* Styling Informasi Filter (Meta Info) */
        .meta-info { margin-bottom: 15px; font-size: 11px; }
        .meta-table { width: auto; border: none; }
        .meta-table td { border: none; padding: 2px 10px 2px 0; }

        /* Styling Tabel Data Utama */
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, 
        table.data-table td { border: 1px solid #000; padding: 6px 8px; text-align: left; }
        table.data-table th { background-color: #f0f0f0; font-weight: bold; text-align: center; }
        
        /* Utility Classes */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge-lunas { color: green; font-weight: bold; }
        .badge-belum { color: red; font-weight: bold; }
        
        /* Footer Cetakan */
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 10px; color: #666; font-style: italic; }
    </style>
</head>
<body>

    {{-- 1. INCLUDE KOP SURAT RESMI --}}
    @include('partials._kop')

    {{-- 2. JUDUL LAPORAN --}}
    <div class="report-title">Laporan Status Pembayaran Mahasiswa</div>

    {{-- 3. INFORMASI FILTER (Header Laporan) --}}
    <div class="meta-info">
        <table class="meta-table">
            <tr>
                <td><strong>Periode Semester</strong></td>
                <td>: {{ $filterInfo['semester'] }}</td>
            </tr>
            <tr>
                <td><strong>Status Filter</strong></td>
                <td>: {{ $filterInfo['status'] }}</td>
            </tr>
            <tr>
                <td><strong>Dicetak Oleh</strong></td>
                <td>: {{ $filterInfo['pencetak'] }}</td>
            </tr>
            <tr>
                <td><strong>Tanggal Cetak</strong></td>
                <td>: {{ $filterInfo['tanggal_cetak'] }}</td>
            </tr>
        </table>
    </div>

    {{-- 4. TABEL DATA PEMBAYARAN --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">NIM</th>
                <th style="width: 30%;">Nama Mahasiswa</th>
                <th style="width: 15%;">Semester</th>
                <th style="width: 15%;">Jumlah Tagihan</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 10%;">Tgl Bayar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pembayarans as $index => $p)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $p->mahasiswa->nim ?? '-' }}</td>
                <td>{{ $p->mahasiswa->nama_lengkap ?? 'Data Mahasiswa Terhapus' }}</td>
                <td class="text-center">{{ $p->semester }}</td>
                <td class="text-right">Rp {{ number_format($p->jumlah, 0, ',', '.') }}</td>
                <td class="text-center">
                    @if($p->status == 'lunas')
                        <span class="badge-lunas">LUNAS</span>
                    @else
                        <span class="badge-belum">BELUM</span>
                    @endif
                </td>
                <td class="text-center">{{ $p->tanggal_bayar ? \Carbon\Carbon::parse($p->tanggal_bayar)->format('d/m/Y') : '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 15px;">
                    <em>Data tidak ditemukan untuk kriteria filter ini.</em>
                </td>
            </tr>
            @endforelse
        </tbody>
        
        {{-- Footer Tabel (Total Nominal) --}}
        <tfoot>
            <tr>
                <td colspan="4" class="text-right"><strong>TOTAL NOMINAL TERDATA</strong></td>
                <td class="text-right" style="background-color: #f9f9f9;">
                    <strong>Rp {{ number_format($pembayarans->sum('jumlah'), 0, ',', '.') }}</strong>
                </td>
                <td colspan="2" style="background-color: #f9f9f9;"></td>
            </tr>
        </tfoot>
    </table>

    {{-- 5. FOOTER HALAMAN --}}
    <div class="footer">
        Dicetak melalui Sistem Informasi Akademik (SIAKAD) STT GPI Papua
    </div>

</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan - SIAKAD</title>
    <style>
        /* CSS STANDAR ENTERPRISE (CETAKAN FORMAL 0PX) */
        body { font-family: Arial, sans-serif; font-size: 11px; background-color: #fff; color: #000; }
        
        .report-title {
            text-align: center;
            margin-top: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
        }

        .meta-info { margin-bottom: 15px; font-size: 11px; text-transform: uppercase; }
        .meta-table { width: auto; border: none; }
        .meta-table td { border: none; padding: 3px 10px 3px 0; vertical-align: top; }

        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, 
        table.data-table td { border: 1px solid #000; padding: 6px 8px; vertical-align: middle; font-size: 11px; }
        table.data-table th { background-color: #212529; color: #ffffff; font-weight: bold; text-align: center; text-transform: uppercase; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-start { text-align: left; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        .badge-lunas { font-weight: bold; text-transform: uppercase; }
        .badge-belum { font-weight: bold; text-transform: uppercase; }
        .badge-cek { font-weight: bold; text-transform: uppercase; }
        .badge-camaba { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 10px; font-family: 'Courier New', Courier, monospace; text-transform: uppercase; }
    </style>
</head>
<body onload="window.print()">

    {{-- 1. INCLUDE KOP SURAT --}}
    @include('partials._kop')

    {{-- 2. JUDUL LAPORAN --}}
    <div class="report-title">Laporan Status Pembayaran</div>

    {{-- 3. INFORMASI FILTER --}}
    <div class="meta-info">
        <table class="meta-table">
            <tr>
                <td><strong>Periode Semester</strong></td>
                <td class="font-monospace">: {{ $filterInfo['semester'] ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Status Filter</strong></td>
                <td class="uppercase fw-bold">: {{ $filterInfo['status'] ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Tipe User</strong></td>
                <td class="uppercase fw-bold">: {{ $filterInfo['tipe_user'] ?? 'Semua' }}</td>
            </tr>
            <tr>
                <td><strong>Dicetak Oleh</strong></td>
                <td class="uppercase fw-bold">: {{ $filterInfo['pencetak'] ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Tanggal Cetak</strong></td>
                <td class="font-monospace">: {{ $filterInfo['tanggal_cetak'] ?? date('d M Y') }}</td>
            </tr>
        </table>
    </div>

    {{-- 4. TABEL DATA --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 15%;">NIM / ID</th>
                <th class="text-start" style="width: 25%;">NAMA PEMBAYAR</th>
                <th class="text-start" style="width: 20%;">KETERANGAN</th>
                <th style="width: 15%;">JUMLAH</th>
                <th style="width: 10%;">STATUS</th>
                <th style="width: 10%;">TGL BAYAR</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pembayarans as $index => $p)
            <tr>
                <td class="text-center font-monospace">{{ $index + 1 }}</td>
                
                {{-- KOLOM NIM --}}
                <td class="text-center font-monospace">
                    @if($p->mahasiswa)
                        {{ $p->mahasiswa->nim }}
                    @else
                        <span class="badge-camaba">(CAMABA)</span>
                    @endif
                </td>

                {{-- KOLOM NAMA --}}
                <td class="uppercase fw-bold">
                    @if($p->mahasiswa)
                        {{ $p->mahasiswa->nama_lengkap }}
                    @elseif($p->user)
                        {{ $p->user->name }}
                    @else
                        <span style="font-style: italic;">USER TERHAPUS</span>
                    @endif
                </td>

                {{-- KOLOM KETERANGAN --}}
                <td class="uppercase">
                    <strong class="font-monospace">{{ $p->semester }}</strong>
                    @if($p->jenis_pembayaran != 'spp')
                        <br><span>({{ ucwords(str_replace('_', ' ', $p->jenis_pembayaran)) }})</span>
                    @endif
                </td>

                <td class="text-right font-monospace">Rp {{ number_format($p->jumlah, 0, ',', '.') }}</td>
                
                <td class="text-center">
                    @if($p->status == 'lunas')
                        <span class="badge-lunas">LUNAS</span>
                    @elseif($p->status == 'menunggu_konfirmasi')
                        <span class="badge-cek">CEK BUKTI</span>
                    @else
                        <span class="badge-belum">BELUM LUNAS</span>
                    @endif
                </td>
                
                <td class="text-center font-monospace">
                    {{ $p->tanggal_bayar ? \Carbon\Carbon::parse($p->tanggal_bayar)->format('d/m/Y') : '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center uppercase fw-bold" style="padding: 15px;">
                    Data tidak ditemukan untuk kriteria filter ini.
                </td>
            </tr>
            @endforelse
        </tbody>
        
        {{-- FOOTER TABEL (REKAPITULASI) --}}
        <tfoot>
            <tr>
                <td colspan="4" class="text-right uppercase"><strong>TOTAL PENERIMAAN (SUDAH LUNAS)</strong></td>
                <td class="text-right font-monospace">
                    <strong>Rp {{ number_format($pembayarans->where('status', 'lunas')->sum('jumlah'), 0, ',', '.') }}</strong>
                </td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td colspan="4" class="text-right uppercase"><strong>TOTAL TUNGGAKAN (BELUM LUNAS)</strong></td>
                <td class="text-right font-monospace">
                    <strong>Rp {{ number_format($pembayarans->where('status', '!=', 'lunas')->sum('jumlah'), 0, ',', '.') }}</strong>
                </td>
                <td colspan="2" class="uppercase" style="font-size: 9px;">
                    *TERMASUK MENUNGGU KONFIRMASI
                </td>
            </tr>
            <tr>
                <td colspan="4" class="text-right uppercase"><strong>TOTAL ESTIMASI PENDAPATAN (SEMUA)</strong></td>
                <td class="text-right font-monospace">
                    <strong>Rp {{ number_format($pembayarans->sum('jumlah'), 0, ',', '.') }}</strong>
                </td>
                <td colspan="2" class="uppercase" style="font-size: 9px;">
                    *POTENSI TOTAL
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- 5. FOOTER HALAMAN --}}
    <div class="footer">
        DICETAK MELALUI SISTEM INFORMASI AKADEMIK (SIAKAD) STT GPI PAPUA
    </div>

</body>
</html>
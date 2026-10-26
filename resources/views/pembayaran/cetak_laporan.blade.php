<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    @php
        $singkatanPt = \App\Models\Pengaturan::get('singkatan_kampus', 'STT GPI PAPUA');
    @endphp
    <title>Laporan Keuangan - SIAKAD {{ $singkatanPt }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10.5px; background-color: #fff; color: #000; }
        @page { size: A4 landscape; margin: 1.2cm; }
        
        .report-title {
            text-align: center;
            margin-top: 5px;
            margin-bottom: 14px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
        }

        .meta-info { margin-bottom: 12px; font-size: 10.5px; text-transform: uppercase; }
        .meta-table { width: auto; border: none; }
        .meta-table td { border: none; padding: 2px 10px 2px 0; vertical-align: top; }

        table.data-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.data-table th, 
        table.data-table td { border: 1px solid #000; padding: 5px 7px; vertical-align: middle; font-size: 10.5px; }
        table.data-table th { background-color: #212529; color: #ffffff; font-weight: bold; text-align: center; text-transform: uppercase; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-start { text-align: left; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .fw-bold { font-weight: bold; }
        
        .badge-lunas { font-weight: bold; text-transform: uppercase; color: #065f46; }
        .badge-dispensasi { font-weight: bold; text-transform: uppercase; color: #1e40af; }
        .badge-belum { font-weight: bold; text-transform: uppercase; color: #991b1b; }
        .badge-cek { font-weight: bold; text-transform: uppercase; color: #92400e; }
        .badge-camaba { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 9.5px; font-family: 'Courier New', Courier, monospace; text-transform: uppercase; }
    </style>
</head>
<body>

    @include('partials._kop')

    <div class="report-title">LAPORAN REKAPITULASI TAGIHAN & PEMBAYARAN</div>

    <div class="meta-info">
        <table class="meta-table">
            <tr>
                <td><strong>Periode Semester</strong></td>
                <td class="font-monospace">: {{ $filterInfo['semester'] ?? '-' }}</td>
                <td style="padding-left: 25px;"><strong>Dicetak Oleh</strong></td>
                <td class="uppercase fw-bold">: {{ $filterInfo['pencetak'] ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Status Filter</strong></td>
                <td class="uppercase fw-bold">: {{ $filterInfo['status'] ?? '-' }}</td>
                <td style="padding-left: 25px;"><strong>Tanggal Cetak</strong></td>
                <td class="font-monospace">: {{ $filterInfo['tanggal_cetak'] ?? date('d M Y') }}</td>
            </tr>
            <tr>
                <td><strong>Tipe User</strong></td>
                <td class="uppercase fw-bold" colspan="3">: {{ $filterInfo['tipe_user'] ?? 'Semua' }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 14%;">NIM / ID</th>
                <th class="text-start" style="width: 24%;">NAMA PEMBAYAR</th>
                <th class="text-start" style="width: 22%;">KETERANGAN TAGIHAN</th>
                <th style="width: 14%;">JUMLAH</th>
                <th style="width: 11%;">STATUS</th>
                <th style="width: 10%;">TGL BAYAR</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pembayarans as $index => $p)
            <tr>
                <td class="text-center font-monospace">{{ $index + 1 }}</td>
                
                <td class="text-center font-monospace">
                    @if($p->mahasiswa)
                        {{ $p->mahasiswa->nim }}
                    @else
                        <span class="badge-camaba">(CAMABA)</span>
                    @endif
                </td>

                <td class="uppercase fw-bold">
                    @if($p->mahasiswa)
                        {{ $p->mahasiswa->nama_lengkap }}
                    @elseif($p->user)
                        {{ $p->user->name }}
                    @else
                        <span style="font-style: italic;">USER TERHAPUS</span>
                    @endif
                </td>

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
                    @elseif($p->status == 'dispensasi')
                        <span class="badge-dispensasi">DISPENSASI</span>
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
        
        <tfoot>
            <tr>
                <td colspan="4" class="text-right uppercase"><strong>TOTAL PENERIMAAN (SUDAH LUNAS)</strong></td>
                <td class="text-right font-monospace">
                    <strong>Rp {{ number_format($pembayarans->where('status', 'lunas')->sum('jumlah'), 0, ',', '.') }}</strong>
                </td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td colspan="4" class="text-right uppercase"><strong>TOTAL TUNGGAKAN / BELUM LUNAS</strong></td>
                <td class="text-right font-monospace">
                    <strong>Rp {{ number_format($pembayarans->where('status', '!=', 'lunas')->sum('jumlah'), 0, ',', '.') }}</strong>
                </td>
                <td colspan="2" class="uppercase" style="font-size: 9px;">
                    *TERMASUK DISPENSASI & VERIFIKASI
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

    <div class="footer">
        DICETAK MELALUI SISTEM INFORMASI AKADEMIK (SIAKAD) {{ strtoupper($singkatanPt) }}
    </div>

</body>
</html>
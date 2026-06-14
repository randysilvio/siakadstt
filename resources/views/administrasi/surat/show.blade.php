<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pratinjau Cetak - {{ $suratKeputusan->judul }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Pengaturan Kanvas Latar Belakang (Preview) */
        body { background-color: #525659; padding-top: 30px; padding-bottom: 30px; display: flex; flex-direction: column; align-items: center; font-family: 'Times New Roman', Times, serif; }
        
        /* Pengaturan Ukuran Kertas A4 */
        .a4-page { background: white; width: 210mm; min-height: 297mm; padding: 25mm 20mm 20mm 20mm; margin-bottom: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); position: relative; font-size: 11pt; line-height: 1.3; color: black; }
        
        /* Elemen Cetak Spesifik */
        .kop-surat { border-bottom: 3px solid black; margin-bottom: 2px; padding-bottom: 5px; }
        .kop-surat-2 { border-bottom: 1px solid black; margin-bottom: 20px; }
        .teks-tengah { text-align: center; }
        .huruf-tebal { font-weight: bold; }
        .huruf-kapital { text-transform: uppercase; }
        
        /* Tabel untuk Tata Letak Diktum */
        table.tabel-diktum { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        table.tabel-diktum td { vertical-align: top; padding: 2px 0; }
        
        /* Pengaturan Cetak Asli (Print Media Query) */
        @media print {
            body { background-color: white; padding: 0; margin: 0; }
            .no-print { display: none !important; }
            .a4-page { margin: 0; box-shadow: none; width: 100%; padding: 15mm 20mm 20mm 20mm; page-break-after: always; }
            .a4-page:last-child { page-break-after: auto; }
            /* Memastikan Tanda Tangan tidak terpotong antar halaman */
            .kotak-pengesahan { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

    <!-- Tombol Mengambang (Tidak Ikut Tercetak) -->
    <div class="no-print position-fixed top-0 start-0 m-3 z-3">
        <button onclick="window.print()" class="btn btn-dark rounded-0 px-4 py-2 fw-bold uppercase shadow">
            <i class="bi bi-printer me-2"></i> Cetak Dokumen
        </button>
        <button onclick="window.close()" class="btn btn-outline-light bg-dark rounded-0 px-3 py-2 fw-bold ms-2 shadow">
            Tutup
        </button>
    </div>

    <!-- HALAMAN 1 : DOKUMEN UTAMA -->
    <div class="a4-page">
        <!-- KOP SURAT -->
        <div class="kop-surat d-flex align-items-center">
            <img src="{{ asset('images/logo.png') }}" alt="Logo STT" style="width: 80px; margin-right: 15px;">
            <div class="teks-tengah flex-grow-1">
                <div style="font-size: 16pt; font-family: 'Arial', sans-serif;" class="huruf-tebal huruf-kapital">SEKOLAH TINGGI THEOLOGIA GPI PAPUA</div>
                <div style="font-size: 9pt; font-family: 'Arial', sans-serif;" class="huruf-tebal">TERAKREDITASI BAN-PT NO: 1599/SK/BAN-PT/Ak.Ppj/PT/X/2022</div>
                <div style="font-size: 10pt; font-family: 'Arial', sans-serif;" class="huruf-tebal">JL. PATTIMURA NO. 1 PO.BOX 138 TLP. (0956) 22252</div>
                <div style="font-size: 12pt; font-family: 'Arial', sans-serif; letter-spacing: 2px;" class="huruf-tebal">FAKFAK - PAPUA BARAT</div>
            </div>
        </div>
        <div class="kop-surat-2"></div>

        <!-- JUDUL SURAT -->
        <div class="teks-tengah mb-4 mt-3">
            <div class="huruf-tebal huruf-kapital" style="text-decoration: underline; font-size: 12pt;">
                {{ $suratKeputusan->jenis_surat == 'Surat Keputusan (SK)' ? 'KEPUTUSAN KETUA' : $suratKeputusan->jenis_surat }}
            </div>
            @if($suratKeputusan->nomor_surat)
                <div>Nomor: {{ $suratKeputusan->nomor_surat }}</div>
            @endif
            
            <div class="mt-3">Tentang</div>
            <div class="huruf-tebal huruf-kapital mt-1">{{ $suratKeputusan->judul }}</div>
            <div class="huruf-tebal huruf-kapital mt-3">KETUA SEKOLAH TINGGI THEOLOGIA GPI PAPUA</div>
        </div>

        <!-- BLOK SURAT KEPUTUSAN -->
        @if($suratKeputusan->jenis_surat == 'Surat Keputusan (SK)')
            <table class="tabel-diktum text-justify">
                @if(!empty($suratKeputusan->menimbang))
                <tr>
                    <td width="18%" class="huruf-tebal">Menimbang</td>
                    <td width="2%">:</td>
                    <td width="80%">
                        <ol type="a" style="margin-top:0; padding-left: 20px; text-align: justify;">
                            @foreach($suratKeputusan->menimbang as $item)
                                <li style="margin-bottom: 5px;">{{ $item }}</li>
                            @endforeach
                        </ol>
                    </td>
                </tr>
                @endif

                @if(!empty($suratKeputusan->mengingat))
                <tr>
                    <td class="huruf-tebal">Mengingat</td>
                    <td>:</td>
                    <td>
                        <ol style="margin-top:0; padding-left: 20px; text-align: justify;">
                            @foreach($suratKeputusan->mengingat as $item)
                                <li style="margin-bottom: 5px;">{{ $item }}</li>
                            @endforeach
                        </ol>
                    </td>
                </tr>
                @endif

                @if(!empty($suratKeputusan->memperhatikan))
                <tr>
                    <td class="huruf-tebal">Memperhatikan</td>
                    <td>:</td>
                    <td>
                        <ol style="margin-top:0; padding-left: 20px; text-align: justify;">
                            @foreach($suratKeputusan->memperhatikan as $item)
                                <li style="list-style-type: none; margin-bottom: 5px;">{{ $loop->iteration }}. {{ $item }}</li>
                            @endforeach
                        </ol>
                    </td>
                </tr>
                @endif
            </table>

            <div class="teks-tengah huruf-tebal mt-4 mb-3" style="letter-spacing: 5px;">MEMUTUSKAN</div>

            <table class="tabel-diktum text-justify">
                @if(!empty($suratKeputusan->menetapkan))
                    @php $diktumLabels = ['Pertama', 'Kedua', 'Ketiga', 'Keempat', 'Kelima', 'Keenam', 'Ketujuh', 'Kedelapan']; @endphp
                    <tr>
                        <td width="18%" class="huruf-tebal">Menetapkan</td>
                        <td width="2%">:</td>
                        <td width="80%"></td>
                    </tr>
                    @foreach($suratKeputusan->menetapkan as $index => $item)
                    <tr>
                        <td class="huruf-tebal" style="padding-left: 10px;">{{ $diktumLabels[$index] ?? 'Kedik'.($index+1) }}</td>
                        <td>:</td>
                        <td style="text-align: justify; padding-bottom: 5px;">{{ $item }}</td>
                    </tr>
                    @endforeach
                @endif
            </table>
        @else
            <!-- BLOK SURAT KETERANGAN / TUGAS BIASA -->
            <div style="text-align: justify; white-space: pre-line; margin-top: 30px;">
                {{ $suratKeputusan->isi_surat }}
            </div>
        @endif

        <!-- KOTAK PENGESAHAN (Anti Terpotong) -->
        <div class="kotak-pengesahan" style="margin-top: 40px; width: 100%;">
            <div style="float: right; width: 250px;">
                <p style="margin: 0;">DITETAPKAN DI : F A K F A K</p>
                <!-- [UPDATE] Format Tanggal Indonesia -->
                <p style="margin: 0; border-bottom: 1px solid black; display: inline-block;">PADA TANGGAL : <span class="huruf-tebal">{{ $suratKeputusan->tanggal_terbit ? \Carbon\Carbon::parse($suratKeputusan->tanggal_terbit)->locale('id')->isoFormat('D MMMM Y') : '......................' }}</span></p>
                
                <p class="huruf-tebal mt-3 mb-5" style="letter-spacing: 1px;">K e t u a,</p>
                
                <p class="huruf-tebal mt-5 mb-0" style="text-decoration: underline;">{{ $suratKeputusan->penandatangan_nama }}</p>
            </div>
            <div style="clear: both;"></div>

            <!-- Tembusan -->
            @if(!empty($suratKeputusan->tembusan))
                <div style="margin-top: 30px; font-size: 10pt;">
                    <div class="huruf-tebal">Tembusan disampaikan Kepada Yth.:</div>
                    <ol style="margin-top: 5px; padding-left: 15px;">
                        @foreach($suratKeputusan->tembusan as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ol>
                </div>
            @endif
        </div>
    </div>

    <!-- HALAMAN 2 : LAMPIRAN PANITIA (Hanya Muncul Jika Ada Dosen Ter-Tag) -->
    @if($suratKeputusan->dosens->count() > 0)
    <div class="a4-page">
        <div style="font-size: 10pt; margin-bottom: 30px;">
            <table width="100%">
                <tr>
                    <td width="30%" valign="top">Lampiran SK Nomor</td>
                    <td width="2%" valign="top">:</td>
                    <td width="68%" valign="top">{{ $suratKeputusan->nomor_surat ?? '- Belum Ada Nomor -' }}</td>
                </tr>
            </table>
        </div>

        <div class="teks-tengah huruf-tebal huruf-kapital mb-5">
            {{ $suratKeputusan->judul }}<br>
            TAHUN AKADEMIK {{ date('Y') }}/{{ date('Y', strtotime('+1 year')) }}
        </div>

        <table width="100%" style="font-size: 11pt; border-collapse: separate; border-spacing: 0 10px;">
            @foreach($suratKeputusan->dosens as $dosen)
                <tr>
                    <td width="30%" class="huruf-tebal huruf-kapital" valign="top">{{ $dosen->pivot->jabatan_dalam_surat }}</td>
                    <td width="2%" valign="top">:</td>
                    <td width="68%" valign="top">{{ $dosen->nama_lengkap }}</td>
                </tr>
            @endforeach
        </table>

        <!-- Tanda Tangan di Halaman Lampiran -->
        <div class="kotak-pengesahan" style="margin-top: 50px; width: 100%;">
            <div style="float: right; width: 250px;">
                <p style="margin: 0;">DITETAPKAN DI : F A K F A K</p>
                <!-- [UPDATE] Format Tanggal Indonesia -->
                <p style="margin: 0; border-bottom: 1px solid black; display: inline-block;">PADA TANGGAL : <span class="huruf-tebal">{{ $suratKeputusan->tanggal_terbit ? \Carbon\Carbon::parse($suratKeputusan->tanggal_terbit)->locale('id')->isoFormat('D MMMM Y') : '......................' }}</span></p>
                
                <p class="huruf-tebal mt-3 mb-5" style="letter-spacing: 1px;">K e t u a,</p>
                
                <p class="huruf-tebal mt-5 mb-0" style="text-decoration: underline;">{{ $suratKeputusan->penandatangan_nama }}</p>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
    @endif

</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label Barcode - {{ $koleksi->judul }}</title>
    {{-- CSS Khusus untuk Printer --}}
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: #fff;
            margin: 0;
            padding: 20px;
        }
        
        /* Grid Penataan Kertas A4 (Tiga Kolom) */
        .label-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            width: 100%;
            max-width: 210mm; /* Lebar standar A4 */
            margin: 0 auto;
        }

        /* Desain Kotak Stiker Label */
        .label-box {
            border: 1px dashed #000;
            padding: 10px;
            text-align: center;
            background-color: #fff;
            page-break-inside: avoid; /* Mencegah stiker terpotong di halaman selanjutnya saat ngeprint */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .kampus-name {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .book-title {
            font-size: 9px;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 90%;
        }

        /* Tampilan Khusus Saat Mode Print (Sembunyikan Tombol) */
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
                margin: 0;
            }
            .label-grid {
                max-width: 100%;
            }
        }

        .btn-print {
            display: block;
            width: 200px;
            margin: 0 auto 30px auto;
            padding: 10px;
            background-color: #212529;
            color: #fff;
            text-align: center;
            text-decoration: none;
            font-weight: bold;
            font-family: Arial, sans-serif;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>

    {{-- Tombol Pemicu Cetak (Akan hilang saat kertas di-print) --}}
    <div class="no-print">
        <button onclick="window.print()" class="btn-print">
            CETAK LABEL SEKARANG
        </button>
    </div>

    {{-- Kontainer Label Stiker --}}
    <div class="label-grid">
        @foreach($koleksi->eksemplars as $eks)
            <div class="label-box">
                <div class="kampus-name">PERPUSTAKAAN STT GPI</div>
                <div class="book-title">{{ substr($koleksi->judul, 0, 30) }}...</div>
                
                {{-- Kanvas ini akan digambar oleh JsBarcode --}}
                <svg class="barcode" data-value="{{ $eks->kode_barcode }}"></svg>
            </div>
        @endforeach
    </div>

    {{-- Pemanggilan Library JsBarcode (Sangat Ringan, Murni JS) --}}
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Gambar semua elemen <svg class="barcode"> yang ada di layar
            const barcodes = document.querySelectorAll('.barcode');
            
            barcodes.forEach(function(svg) {
                const value = svg.getAttribute('data-value');
                JsBarcode(svg, value, {
                    format: "CODE128", // Format standar scanner kasir
                    width: 1.5,        // Ketebalan garis
                    height: 40,        // Tinggi garis barcode
                    displayValue: true, // Tampilkan teks angka/huruf di bawah garis
                    fontSize: 12,
                    fontOptions: "bold",
                    margin: 5
                });
            });
            
            // Otomatis Munculkan Jendela Print 1 Detik setelah barcode selesai di-render
            setTimeout(function() {
                window.print();
            }, 1000);
        });
    </script>
</body>
</html>
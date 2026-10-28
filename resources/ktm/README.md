# Latar E-KTM

`public/images/ktm/latar-ktm.jpg` (juga `assets/ktm-latar.jpg` di aplikasi mobilesia2.0) dibuat dari skrip ini.
Desainnya sengaja tetap (tidak mengikuti tema sentral). Untuk mengubah nama kampus/ornamen:

```bash
python3 resources/ktm/latar.py public/images/logo.png "Sekolah Tinggi Theologia|GPI Papua" /tmp/latar.svg
node resources/ktm/render.cjs /tmp/latar.svg public/images/ktm/latar-ktm.jpg   # butuh Playwright + font Poppins
```

Koordinat data (foto, nilai, QR) ada di `resources/views/components/ktm.blade.php` dan `src/ktm/KartuKtm.tsx` (kanvas 1712 x 1080).

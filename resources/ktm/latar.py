"""Latar E-KTM STT GPI Papua (1712x1080 = 85,6x54 mm x 20). Desain tetap: krem-oranye-ungu + ornamen batik Papua."""
import base64, math, sys
W, H = 1712, 1080
KREM, ORANYE, ORANYE_TUA, UNGU, UNGU_MUDA, MERAH, KREM_TUA = '#FDDFC1', '#F9A21B', '#F47B16', '#3D2350', '#5E3F82', '#B3402A', '#F6C99A'
logo = base64.b64encode(open(sys.argv[1], 'rb').read()).decode()
nama_kampus = sys.argv[2].split('|')
el = []
a = el.append

def spiral(cx, cy, r, putaran=2.25, arah=1, langkah=60):
    """Spiral Archimedes (unsur ukiran/batik Papua)."""
    pts = []
    n = int(putaran * langkah)
    for i in range(n + 1):
        t = i / langkah * 2 * math.pi
        rr = r * i / n
        pts.append((cx + arah * rr * math.cos(t), cy + rr * math.sin(t)))
    return 'M' + ' L'.join(f'{x:.1f},{y:.1f}' for x, y in pts)

def ubin(x, y, s, jenis, warna, rot=0):
    """Ubin geometris s x s: kotak, seperempat lingkaran, setengah lingkaran, lingkaran, bingkai, spiral."""
    g = f'<g transform="translate({x},{y}) rotate({rot},{s/2},{s/2})">'
    if jenis == 'kotak':
        g += f'<rect width="{s}" height="{s}" fill="{warna}"/>'
    elif jenis == 'seperempat':
        g += f'<path d="M0,0 H{s} A{s},{s} 0 0 1 0,{s} Z" fill="{warna}"/>'
    elif jenis == 'daun':
        g += f'<path d="M0,{s} A{s},{s} 0 0 1 {s},0 A{s},{s} 0 0 1 0,{s} Z" fill="{warna}"/>'
    elif jenis == 'setengah':
        g += f'<path d="M0,0 H{s} A{s/2},{s/2} 0 0 1 0,0 Z" fill="{warna}"/>'
    elif jenis == 'bulat':
        g += f'<circle cx="{s/2}" cy="{s/2}" r="{s*0.28}" fill="{warna}"/>'
    elif jenis == 'bingkai':
        g += f'<rect x="{s*0.1}" y="{s*0.1}" width="{s*0.8}" height="{s*0.8}" fill="none" stroke="{warna}" stroke-width="{s*0.14}"/>' \
             f'<rect x="{s*0.33}" y="{s*0.33}" width="{s*0.34}" height="{s*0.34}" fill="{warna}"/>'
    elif jenis == 'spiral':
        g += f'<rect width="{s}" height="{s}" fill="{warna[0]}"/><path d="{spiral(s/2, s/2, s*0.4)}" fill="none" stroke="{warna[1]}" stroke-width="{s*0.075}" stroke-linecap="round"/>'
    elif jenis == 'segitiga':
        g += f'<rect width="{s}" height="{s}" fill="{warna[0]}"/>' + ''.join(
            f'<path d="M{i*s/3},{s} L{i*s/3+s/6},{s*0.55} L{(i+1)*s/3},{s} Z" fill="{warna[1]}"/><path d="M{i*s/3},0 L{i*s/3+s/6},{s*0.45} L{(i+1)*s/3},0 Z" fill="{warna[1]}"/>' for i in range(3))
    return g + '</g>'

a(f'<rect width="{W}" height="{H}" fill="{KREM}"/>')

# watermark logo (tipis), sedikit ke kanan seperti contoh
a(f'<image href="data:image/png;base64,{logo}" x="560" y="150" width="700" height="700" opacity="0.13"/>')

# blok oranye kanan atas dengan sudut kiri-bawah membulat
a(f'<path d="M1330,0 H{W} V300 H1450 A120,120 0 0 1 1330,180 Z" fill="{ORANYE}"/>')
# blok oranye kiri bawah
a(f'<path d="M0,700 H330 A120,120 0 0 1 450,820 V{H} H0 Z" fill="{ORANYE}"/>')

# klaster ubin kanan atas (s = 78)
s = 78
for (cx, cy, j, w, r) in [
    (1250, 56, 'bingkai', UNGU, 0), (1328, 56, 'bulat', UNGU_MUDA, 0), (1406, 56, 'seperempat', UNGU, 90),
    (1406, 134, 'kotak', UNGU_MUDA, 0), (1484, 134, 'seperempat', UNGU, 0), (1328, 134, 'seperempat', ORANYE_TUA, 180),
    (1484, 212, 'spiral', (UNGU, KREM_TUA), 0), (1406, 212, 'kotak', UNGU, 0), (1562, 56, 'kotak', ORANYE_TUA, 0),
    (1406, 290, 'daun', ORANYE_TUA, 90), (1562, 212, 'segitiga', (ORANYE_TUA, UNGU), 90), (1328, 212, 'bulat', UNGU, 0),
]:
    a(ubin(cx, cy, s, j, w, r))

# klaster ubin kiri bawah
for (cx, cy, j, w, r) in [
    (170, 600, 'seperempat', ORANYE_TUA, 270), (170, 678, 'kotak', UNGU_MUDA, 0), (170, 756, 'seperempat', UNGU, 270),
    (248, 756, 'kotak', UNGU_MUDA, 0), (248, 834, 'seperempat', UNGU, 180), (326, 834, 'spiral', (UNGU, KREM_TUA), 0),
    (326, 756, 'seperempat', ORANYE_TUA, 0), (404, 912, 'bingkai', UNGU, 0), (326, 912, 'bulat', UNGU_MUDA, 0),
    (170, 834, 'segitiga', (ORANYE_TUA, UNGU), 0), (92, 912, 'kotak', ORANYE_TUA, 0), (248, 912, 'daun', UNGU, 0),
]:
    a(ubin(cx, cy, s, j, w, r))

# titik-titik (dot grid)
for baris in range(2):
    for k in range(5):
        a(f'<circle cx="{985 + k*52}" cy="{56 + baris*50}" r="7.5" fill="{UNGU}"/>')
for baris in range(2):
    for k in range(9):
        a(f'<circle cx="{830 + k*52}" cy="{912 + baris*40}" r="7" fill="{UNGU}"/>')

# pita batik Papua di tepi bawah: ukel "S" (spiral kembar bersambung), garis tegak, wajik bertingkat, gigi segitiga
pt, ptg = 988, 92
a(f'<rect x="0" y="{pt}" width="{W}" height="{ptg}" fill="{UNGU}"/>')
a(f'<rect x="0" y="{pt}" width="{W}" height="6" fill="{ORANYE}"/>')
u = 214

def ukel_s(cx, cy, r):
    """Dua spiral berlawanan yang bersambung membentuk huruf S (motif ukel khas ukiran Papua)."""
    n = 70
    A = [(cx - r + r * (i / n) * math.cos(t), cy + r * (i / n) * math.sin(t)) for i in range(n + 1) for t in [i / n * 3.5 * math.pi]]
    B = [(cx + r - r * (i / n) * math.cos(t), cy - r * (i / n) * math.sin(t)) for i in range(n + 1) for t in [i / n * 3.5 * math.pi]]
    pts = A + list(reversed(B))
    return 'M' + ' L'.join(f'{x:.1f},{y:.1f}' for x, y in pts)

for i in range(-1, W // u + 2):
    x0 = i * u
    cy = pt + 6 + (ptg - 6) / 2
    for k in range(8):
        tx = x0 + k * u / 8
        a(f'<path d="M{tx},{pt+6} l{u/16},13 l{u/16},-13 Z" fill="{ORANYE_TUA}"/>')
        a(f'<path d="M{tx},{pt+ptg} l{u/16},-12 l{u/16},12 Z" fill="{ORANYE_TUA}"/>')
    # ukel S
    a(f'<path d="{ukel_s(x0 + 54, cy, 21)}" fill="none" stroke="{KREM}" stroke-width="4.2" stroke-linecap="round" stroke-linejoin="round"/>')
    # garis tegak
    for k in range(3):
        a(f'<path d="M{x0 + 104 + k*9},{cy-15} V{cy+15}" stroke="{KREM_TUA}" stroke-width="3.5" stroke-linecap="round"/>')
    # wajik bertingkat
    wx = x0 + 160
    a(f'<path d="M{wx},{cy-29} l29,29 l-29,29 l-29,-29 Z" fill="{MERAH}" stroke="{KREM}" stroke-width="3.5"/>')
    a(f'<path d="M{wx},{cy-15} l15,15 l-15,15 l-15,-15 Z" fill="{ORANYE}"/>')
    a(f'<path d="M{wx},{cy-6} l6,6 l-6,6 l-6,-6 Z" fill="{UNGU}"/>')
    for k in range(3):
        a(f'<path d="M{x0 + 196 + k*9},{cy-15} V{cy+15}" stroke="{KREM_TUA}" stroke-width="3.5" stroke-linecap="round"/>')

# garis isian (dengan ujung tegak) — posisi baris data
for y in (572, 660, 748, 836):
    a(f'<path d="M714,{y} H1404" stroke="{ORANYE_TUA}" stroke-width="5" stroke-linecap="round"/>')
    a(f'<path d="M714,{y-11} V{y+11} M1404,{y-11} V{y+11}" stroke="{ORANYE_TUA}" stroke-width="5" stroke-linecap="round"/>')

# teks tetap (Poppins)
a(f'<image href="data:image/png;base64,{logo}" x="88" y="42" width="150" height="150"/>')
for i, baris in enumerate(nama_kampus):
    a(f'<text x="262" y="{106 + i*58}" font-family="Poppins" font-weight="700" font-size="50" fill="{UNGU}">{baris}</text>')
a(f'<text x="712" y="352" font-family="Poppins" font-weight="700" font-size="104" fill="{UNGU}" letter-spacing="-1">Kartu</text>')
a(f'<text x="712" y="462" font-family="Poppins" font-weight="700" font-size="104" fill="{UNGU}" letter-spacing="-1">Mahasiswa</text>')
for y, label in zip((572, 660, 748, 836), ('Nama', 'NIM', 'Prodi', 'Angkatan')):
    a(f'<text x="716" y="{y-20}" font-family="Poppins" font-weight="400" font-size="36" fill="{UNGU}">{label}</text>')
    a(f'<text x="918" y="{y-20}" font-family="Poppins" font-weight="400" font-size="36" fill="{UNGU}">:</text>')

# panel QR (diisi kode QR dinamis) + keterangan
a(f'<rect x="1446" y="640" width="226" height="226" rx="22" fill="#FFFFFF" stroke="{UNGU}" stroke-width="5"/>')
a(f'<rect x="1478" y="852" width="162" height="34" rx="10" fill="{UNGU}"/>')
a(f'<text x="1559" y="877" text-anchor="middle" font-family="Poppins" font-weight="700" font-size="20" fill="#FFFFFF" letter-spacing="2">PINDAI</text>')

svg = f'<svg xmlns="http://www.w3.org/2000/svg" width="{W}" height="{H}" viewBox="0 0 {W} {H}">' + ''.join(el) + '</svg>'
open(sys.argv[3], 'w').write(svg)
print('ok', len(svg))

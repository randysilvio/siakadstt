{{--
    Pengingat kelengkapan data (khusus Admin) — ikon "!" di atas tombol chatbot + modal.
    Tidak ada halaman baru; butir berasal dari App\Services\KelengkapanDataService (cache 10 menit).
    Setiap butir menyebut bidang tujuan dan memiliki tombol "Salin pesan" untuk diteruskan (mis. via WhatsApp).
--}}
@php
    $pengingat = app(\App\Services\KelengkapanDataService::class)->ringkasan();
    $jml = $pengingat['jumlah'];
    $totalUtama = ($jml['kritis'] ?? 0) + ($jml['penting'] ?? 0);
    $perBidang = collect($pengingat['butir'])->groupBy('bidang');
    $warnaTingkat = ['kritis' => 'danger', 'penting' => 'warning', 'saran' => 'info'];
    $labelTingkat = ['kritis' => 'KRITIS', 'penting' => 'PENTING', 'saran' => 'SARAN'];
@endphp
@if(count($pengingat['butir']))
<style>
    #pengingat-admin-btn { position: fixed; right: 1.5rem; bottom: calc(1.5rem + 54px + 12px); z-index: 1049; width: 54px; height: 54px;
        border-radius: var(--siakad-radius, 10px); display: flex; align-items: center; justify-content: center; font-size: 1.6rem; font-weight: 900; }
    #pengingat-admin-btn .badge { position: absolute; top: -6px; right: -6px; font-size: .7rem; }
    @media (max-width: 991.98px) { #pengingat-admin-btn { right: 1rem; bottom: calc(4.8rem + 48px + 10px); width: 48px; height: 48px; font-size: 1.4rem; } }
    .tk-kritis { background: #dc3545 !important; color: #fff !important; }
    .tk-penting { background: #ffc107 !important; color: #212529 !important; }
    .tk-saran { background: #cfe2ff !important; color: #084298 !important; }
    #modalPengingat .butir-pengingat { border-left: 4px solid var(--bs-border-color); }
    #modalPengingat .butir-pengingat.t-kritis { border-left-color: var(--bs-danger); }
    #modalPengingat .butir-pengingat.t-penting { border-left-color: var(--bs-warning); }
    #modalPengingat .butir-pengingat.t-saran { border-left-color: var(--bs-info); }
</style>

<button type="button" id="pengingat-admin-btn" class="btn shadow-lg {{ ($jml['kritis'] ?? 0) ? 'btn-danger' : (($jml['penting'] ?? 0) ? 'btn-warning' : 'btn-light border') }}"
        data-bs-toggle="modal" data-bs-target="#modalPengingat" title="Pengingat kelengkapan data">
    !
    <span class="badge rounded-pill bg-dark">{{ $totalUtama ?: count($pengingat['butir']) }}</span>
</button>

<div class="modal fade" id="modalPengingat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2 px-3">
                <div>
                    <div class="fw-bold uppercase small">Pengingat Kelengkapan Data</div>
                    <div class="text-white-50 font-monospace" style="font-size: 10px;">DIPERIKSA {{ \Carbon\Carbon::parse($pengingat['diperiksa'])->translatedFormat('d M Y H:i') }} WIT{{ $pengingat['agenda'] ? ' · ' . strtoupper($pengingat['agenda']) : '' }}</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 bg-light">
                <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
                    <span class="badge tk-kritis rounded-0 px-3 py-2">{{ $jml['kritis'] ?? 0 }} KRITIS</span>
                    <span class="badge tk-penting rounded-0 px-3 py-2">{{ $jml['penting'] ?? 0 }} PENTING</span>
                    <span class="badge tk-saran rounded-0 px-3 py-2">{{ $jml['saran'] ?? 0 }} SARAN</span>
                    <span class="small text-muted ms-auto">Teruskan ke bidang terkait. Bidang Keuangan, Kaprodi, dan Penjaminan Mutu juga menerima notifikasi mingguan.</span>
                </div>

                @foreach($perBidang as $bidang => $daftar)
                    <div class="card border-0 shadow-sm mb-3 rounded-0" data-bidang="{{ $bidang }}">
                        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <span class="fw-bold small uppercase text-dark"><i class="bi bi-people me-1"></i>{{ $bidang }}</span>
                            <button type="button" class="btn btn-sm btn-outline-dark rounded-0 py-0 px-2 small fw-bold js-salin-bidang">
                                <i class="bi bi-clipboard"></i> SALIN PESAN UNTUK BIDANG INI
                            </button>
                        </div>
                        <div class="card-body p-2">
                            @foreach($daftar as $b)
                                <div class="butir-pengingat t-{{ $b['tingkat'] }} bg-white px-3 py-2 mb-2" data-pesan="{{ $b['judul'] }}: {{ $b['jumlah'] }}{{ $b['contoh'] ? ' (' . implode(', ', $b['contoh']) . ($b['jumlah'] > count($b['contoh']) ? ', dst.' : '') . ')' : '' }}{{ $b['tenggat'] ? ' — ' . $b['tenggat'] : '' }}">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div>
                                            <span class="badge rounded-0 tk-{{ $b['tingkat'] }}" style="font-size: 9px;">{{ $labelTingkat[$b['tingkat']] }}</span>
                                            <span class="fw-bold small text-dark ms-1">{{ $b['judul'] }}</span>
                                            <span class="badge bg-dark rounded-0 font-monospace ms-1">{{ $b['jumlah'] }}</span>
                                            @if($b['tenggat'])<span class="badge bg-light text-danger border border-danger rounded-0 ms-1" style="font-size: 9px;">{{ strtoupper($b['tenggat']) }}</span>@endif
                                        </div>
                                        <div class="d-flex gap-1 flex-shrink-0">
                                            <button type="button" class="btn btn-sm btn-light border rounded-0 py-0 px-2 js-salin-butir" title="Salin pesan"><i class="bi bi-clipboard"></i></button>
                                            @if($b['tautan'])<a href="{{ $b['tautan'] }}" class="btn btn-sm btn-dark rounded-0 py-0 px-2" title="Buka halaman terkait"><i class="bi bi-box-arrow-up-right"></i></a>@endif
                                        </div>
                                    </div>
                                    <div class="text-muted" style="font-size: 12px;">{{ $b['alasan'] }}</div>
                                    @if($b['contoh'])
                                        <div class="font-monospace text-dark mt-1" style="font-size: 11px;">{{ implode(' · ', $b['contoh']) }}{{ $b['jumlah'] > count($b['contoh']) ? ' · +' . ($b['jumlah'] - count($b['contoh'])) . ' lainnya' : '' }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="modal-footer bg-white py-2 px-3 d-flex justify-content-between">
                <form action="{{ route('admin.kelengkapan.segarkan') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-dark rounded-0 fw-bold small"><i class="bi bi-arrow-repeat"></i> PERIKSA ULANG</button>
                </form>
                <button type="button" class="btn btn-sm btn-dark rounded-0 fw-bold small" data-bs-dismiss="modal">TUTUP</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        function salin(teks, tombol) {
            var selesai = function () { if (!tombol) return; var asal = tombol.innerHTML; tombol.innerHTML = '<i class="bi bi-check2"></i> TERSALIN'; setTimeout(function () { tombol.innerHTML = asal; }, 1600); };
            if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(teks).then(selesai); return; }
            var ta = document.createElement('textarea'); ta.value = teks; ta.style.position = 'fixed'; ta.style.opacity = '0';
            document.body.appendChild(ta); ta.select(); try { document.execCommand('copy'); selesai(); } catch (e) {} document.body.removeChild(ta);
        }
        var tanggal = @json(now()->translatedFormat('d F Y'));
        document.querySelectorAll('#modalPengingat .js-salin-bidang').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var kartu = btn.closest('[data-bidang]');
                var baris = Array.from(kartu.querySelectorAll('[data-pesan]')).map(function (el, i) { return (i + 1) + '. ' + el.dataset.pesan; });
                salin('Yth. ' + kartu.dataset.bidang + ',\nberikut data di SIAKAD yang perlu dilengkapi/diperbaiki per ' + tanggal + ':\n' + baris.join('\n') + '\n\nTerima kasih.', btn);
            });
        });
        document.querySelectorAll('#modalPengingat .js-salin-butir').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var el = btn.closest('[data-pesan]');
                var bidang = btn.closest('[data-bidang]').dataset.bidang;
                salin('Yth. ' + bidang + ',\nmohon ditindaklanjuti di SIAKAD: ' + el.dataset.pesan + '.\n\nTerima kasih.', btn);
            });
        });
    })();
</script>
@endif

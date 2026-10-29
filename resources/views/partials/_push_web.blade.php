{{--
    Push notifikasi browser (Firebase Cloud Messaging). Aktif hanya bila FCM_VAPID_KEY diisi di .env
    dan server memiliki kunci service account. Izin diminta lewat tombol (bukan otomatis) agar tidak diblokir browser.
--}}
@php($fcmWeb = config('services.fcm'))
@if(auth()->check() && !empty($fcmWeb['vapid_key']) && !session()->has('impersonate_by') && app(\App\Services\Push\PushFcm::class)->aktif())
<div id="ajakanPush" class="position-fixed start-0 bottom-0 m-3 bg-white border shadow-lg p-3 d-none" style="z-index: 1080; max-width: 330px; border-radius: var(--siakad-radius, 10px);">
    <div class="d-flex gap-2">
        <i class="bi bi-bell-fill text-primary fs-4"></i>
        <div>
            <div class="fw-bold small">Aktifkan notifikasi browser?</div>
            <div class="text-muted" style="font-size: 12px;">Dapatkan pemberitahuan validasi KRS, pengumuman, dan tugas walau tab SIAKAD tertutup.</div>
            <div class="d-flex gap-2 mt-2">
                <button type="button" class="btn btn-sm btn-primary fw-bold" id="ajakanPushYa">Aktifkan</button>
                <button type="button" class="btn btn-sm btn-light border" id="ajakanPushNanti">Nanti saja</button>
            </div>
        </div>
    </div>
</div>
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-messaging-compat.js"></script>
<script>
    (function () {
        if (!('serviceWorker' in navigator) || !('Notification' in window) || !window.isSecureContext || !window.firebase) return;
        var KUNCI = 'siakad-push-nanti';
        var simpanLokal = function (k, v) { try { v === null ? localStorage.removeItem(k) : localStorage.setItem(k, v); } catch (e) {} };
        var bacaLokal = function (k) { try { return localStorage.getItem(k); } catch (e) { return null; } };

        firebase.initializeApp(@json($fcmWeb['web']));
        var messaging = firebase.messaging();

        function daftar() {
            return navigator.serviceWorker.register('/firebase-messaging-sw.js').then(function (reg) {
                return messaging.getToken({ vapidKey: @json($fcmWeb['vapid_key']), serviceWorkerRegistration: reg });
            }).then(function (token) {
                if (!token || bacaLokal('siakad-push-token') === token) return;
                return fetch(@json(route('fcmToken.simpan')), {
                    method: 'POST', credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ token: token })
                }).then(function (r) { if (r.ok) simpanLokal('siakad-push-token', token); });
            }).catch(function (e) { console.warn('Push browser tidak aktif:', e && e.message); });
        }

        // Pesan saat tab terbuka → toast yang sudah ada di layout
        messaging.onMessage(function (payload) {
            var n = payload.notification || {};
            var t = document.getElementById('fcmToast');
            if (!t || !window.bootstrap) return;
            document.getElementById('fcmToastTitle').textContent = n.title || 'Notifikasi Baru';
            document.getElementById('fcmToastBody').textContent = n.body || '';
            bootstrap.Toast.getOrCreateInstance(t).show();
        });

        if (Notification.permission === 'granted') { daftar(); return; }
        if (Notification.permission === 'denied') return;
        var nanti = parseInt(bacaLokal(KUNCI) || '0', 10);
        if (nanti && Date.now() - nanti < 7 * 864e5) return; // ditawarkan lagi seminggu kemudian

        var kotak = document.getElementById('ajakanPush');
        kotak.classList.remove('d-none');
        document.getElementById('ajakanPushNanti').addEventListener('click', function () { simpanLokal(KUNCI, String(Date.now())); kotak.remove(); });
        document.getElementById('ajakanPushYa').addEventListener('click', function () {
            kotak.remove();
            Notification.requestPermission().then(function (izin) { izin === 'granted' ? daftar() : simpanLokal(KUNCI, String(Date.now())); });
        });
    })();
</script>
@endif

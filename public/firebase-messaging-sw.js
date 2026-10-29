// public/firebase-messaging-sw.js — penerima push browser SIAKAD (Firebase Cloud Messaging)

importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "AIzaSyCric2X1hFP4TJ4bghlSnavjQZwmnszehU",
    authDomain: "stt-gpi-papua.firebaseapp.com",
    projectId: "stt-gpi-papua",
    storageBucket: "stt-gpi-papua.firebasestorage.app",
    messagingSenderId: "633336694324",
    appId: "1:633336694324:web:a2cb604ff59a4299b4ec61",
    measurementId: "G-RED9DLVV20"
});
const messaging = firebase.messaging();

// Pesan ber-"notification" sudah ditampilkan otomatis oleh Firebase (klik → fcm_options.link).
// Hanya pesan data-saja yang perlu ditampilkan manual, agar tidak muncul dobel.
messaging.onBackgroundMessage(function (payload) {
    if (payload.notification) return;
    const d = payload.data || {};
    self.registration.showNotification(d.judul || 'SIAKAD STT GPI Papua', {
        body: d.isi || '', icon: '/images/logo.png', data: { tautan: d.tautan || '/notifikasi' }
    });
});

self.addEventListener('notificationclick', function (event) {
    const tautan = event.notification.data && event.notification.data.tautan;
    if (!tautan) return;
    event.notification.close();
    event.waitUntil(clients.openWindow(tautan));
});

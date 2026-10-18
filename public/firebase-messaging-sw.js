// public/firebase-messaging-sw.js

importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-messaging-compat.js');

const firebaseConfig = {
    apiKey: "AIzaSyCric2X1hFP4TJ4bghlSnavjQZwmnszehU",
    authDomain: "stt-gpi-papua.firebaseapp.com",
    projectId: "stt-gpi-papua",
    storageBucket: "stt-gpi-papua.firebasestorage.app",
    messagingSenderId: "633336694324",
    appId: "1:633336694324:web:a2cb604ff59a4299b4ec61",
    measurementId: "G-RED9DLVV20"
};

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

messaging.onBackgroundMessage(function(payload) {
  console.log('[firebase-messaging-sw.js] Menerima pesan background ', payload);
  
  const notificationTitle = payload.notification.title;
  const notificationOptions = {
    body: payload.notification.body,
    icon: '/images/logo.png' 
  };

  self.registration.showNotification(notificationTitle, notificationOptions);
});
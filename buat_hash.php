<?php

// Ganti 'password_anda_disini' dengan password yang ingin Anda hash.
$passwordAsli = 'Asdf1102@';

// Opsi hashing, BCRYPT adalah standar yang kuat dan aman.
$options = [
    'cost' => 12, // Semakin tinggi, semakin kuat tapi semakin lambat prosesnya
];

// Membuat hash
$hashPassword = password_hash($passwordAsli, PASSWORD_BCRYPT, $options);

// Menampilkan hasilnya
echo "Password Asli: " . $passwordAsli . "\n";
echo "Hasil Hash    : " . $hashPassword . "\n";

?>
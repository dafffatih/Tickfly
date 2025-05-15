<?php
// Password yang akan di-hash
$password = "admin";

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Tampilkan hasil hash
echo "Password asli: " . $password . "<br>";
echo "Password yang di-hash: " . $hashedPassword . "<br>";

// Simulasi login: memverifikasi password yang dimasukkan user
$inputPassword = "RahasiaBanget123";

if (password_verify($inputPassword, $hashedPassword)) {
    echo "Password benar. Akses diberikan.";
} else {
    echo "Password salah. Akses ditolak.";
}
?>
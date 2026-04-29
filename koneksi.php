<?php
$host    = 'localhost';
$user    = 'root';
$pass    = '';
$db      = 'kebun_ndesa';

$koneksi = new mysqli($host, $user, $pass, $db);
if ($koneksi->connect_error) {
    die('<div style="font-family:sans-serif;padding:2rem;background:#fff0f0;color:#c0392b;border-left:4px solid #c0392b;">
        <h3>⚠ Koneksi Database Gagal</h3>
        <p>'.$koneksi->connect_error.'</p>
        <p>Pastikan MySQL aktif dan database <b>kebun_ndesa</b> sudah dibuat.</p>
    </div>');
}
$koneksi->set_charset('utf8mb4');
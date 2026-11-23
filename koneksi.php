<?php
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Ubah "sas" jika nama databasemu berbeda
try {
    $koneksi = mysqli_connect("127.0.0.1", "root", "", "sas");
} catch (mysqli_sql_exception $ex) {
    die("Koneksi gagal: " . $ex->getMessage());
}

function wajib_login($role = null) {
    if (!isset($_SESSION['id_user']) || ($role && $_SESSION['role'] !== $role)) {
        header("Location: login.php");
        exit;
    }
}

function e($teks) {
    return htmlspecialchars($teks, ENT_QUOTES, 'UTF-8');
}

function rupiah($angka) {
    return "Rp " . number_format($angka, 0, ",", ".");
}

// Tampilkan berat tanpa nol di belakang koma, contoh 2.50 jadi "2,5 kg"
function kg($berat) {
    return rtrim(rtrim(number_format($berat, 2, ",", "."), "0"), ",") . " kg";
}

// Total bayar = harga per kg x berat
function hitung_total($harga, $berat) {
    return round($harga * $berat);
}

function query($koneksi, $sql, $tipe = "", ...$param) {
    $s = mysqli_prepare($koneksi, $sql);
    if ($tipe !== "") {
        mysqli_stmt_bind_param($s, $tipe, ...$param);
    }
    mysqli_stmt_execute($s);
    return $s;
}

function set_flash($teks, $tipe = "ok") {
    $_SESSION['flash'] = ['teks' => $teks, 'tipe' => $tipe];
}

function tampil_flash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="pesan ' . e($f['tipe']) . '">' . e($f['teks']) . '</div>';
    }
}

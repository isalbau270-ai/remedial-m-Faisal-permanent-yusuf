<?php
require "koneksi.php";
wajib_login('admin');
$judul = "Beranda Admin";

$jml_user = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM tb_user"))[0];
$jml_kategori = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM tb_kategori"))[0];
$jml_transaksi = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM tb_transaksi"))[0];
$pendapatan = (float)mysqli_fetch_row(mysqli_query($koneksi, "SELECT SUM(tr.berat_barang * k.harga)
    FROM tb_transaksi tr
    JOIN tb_barang b ON tr.id_barang = b.id_barang
    JOIN tb_kategori k ON b.id_kategori = k.id_kategori"))[0];

include "header.php";
?>
<h2>Selamat datang, <?= e($_SESSION['nama']) ?></h2>
<div class="ringkas">
  <div class="card"><div>Jumlah user</div><div class="angka"><?= (int)$jml_user ?></div></div>
  <div class="card"><div>Kategori layanan</div><div class="angka"><?= (int)$jml_kategori ?></div></div>
  <div class="card"><div>Transaksi</div><div class="angka"><?= (int)$jml_transaksi ?></div></div>
  <div class="card"><div>Total pendapatan</div><div class="angka"><?= rupiah($pendapatan) ?></div></div>
</div>
<?php include "footer.php"; ?>

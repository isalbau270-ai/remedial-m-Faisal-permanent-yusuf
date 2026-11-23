<?php
require "koneksi.php";
wajib_login('customer');
$judul = "Nota Laundry";
$id = (int)($_GET['id'] ?? 0);
$id_user = (int)$_SESSION['id_user'];

$s = query($koneksi, "SELECT tr.id_transaksi, u.nama, k.nama_kategori, k.harga, tr.berat_barang
    FROM tb_transaksi tr
    JOIN tb_user u ON tr.id_user = u.id_user
    JOIN tb_barang b ON tr.id_barang = b.id_barang
    JOIN tb_kategori k ON b.id_kategori = k.id_kategori
    WHERE tr.id_transaksi=? AND tr.id_user=?", "ii", $id, $id_user);
$d = mysqli_fetch_assoc(mysqli_stmt_get_result($s));

include "header.php";
if (!$d): ?>
  <div class="pesan gagal">Transaksi tidak ditemukan.</div>
  <a class="btn abu" href="customer.php">Kembali</a>
<?php else: ?>
<section class="card bukti">
  <h2>Nota Laundry</h2>
  <dl>
    <dt>No. transaksi</dt><dd><?= (int)$d['id_transaksi'] ?></dd>
    <dt>Nama</dt><dd><?= e($d['nama']) ?></dd>
    <dt>Layanan</dt><dd><?= e($d['nama_kategori']) ?></dd>
    <dt>Harga per kg</dt><dd><?= rupiah($d['harga']) ?></dd>
    <dt>Berat</dt><dd><?= kg($d['berat_barang']) ?></dd>
    <dt>Total bayar</dt><dd><strong><?= rupiah(hitung_total($d['harga'], $d['berat_barang'])) ?></strong></dd>
  </dl>
  <p class="noprint">
    <button class="btn" onclick="window.print()">Cetak</button>
    <a class="btn abu" href="customer.php">Kembali</a>
  </p>
</section>
<?php endif;
include "footer.php"; ?>

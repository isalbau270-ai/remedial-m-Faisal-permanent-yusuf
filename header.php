<?php $halaman = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($judul) ?> - Layanan Laundry</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="bar">
  <div class="wrap">
    <h1>Layanan Laundry</h1>
    <div><?= e($_SESSION['nama']) ?> (<?= e($_SESSION['role']) ?>) &nbsp; <a href="logout.php">Keluar</a></div>
  </div>
</header>
<nav class="menu">
  <div class="wrap">
    <?php if ($_SESSION['role'] == 'admin'): ?>
      <a href="admin.php" class="<?= $halaman == 'admin.php' ? 'aktif' : '' ?>">Beranda</a>
      <a href="kategori.php" class="<?= $halaman == 'kategori.php' ? 'aktif' : '' ?>">Kategori</a>
      <a href="barang.php" class="<?= $halaman == 'barang.php' ? 'aktif' : '' ?>">Barang</a>
      <a href="user.php" class="<?= $halaman == 'user.php' ? 'aktif' : '' ?>">User</a>
      <a href="transaksi.php" class="<?= $halaman == 'transaksi.php' ? 'aktif' : '' ?>">Transaksi</a>
    <?php else: ?>
      <a href="customer.php" class="<?= $halaman == 'customer.php' ? 'aktif' : '' ?>">Pesan Laundry</a>
    <?php endif; ?>
  </div>
</nav>
<main class="wrap">

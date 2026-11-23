<?php
require "koneksi.php";
wajib_login('admin');
$judul = "Transaksi";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $aksi = $_POST['aksi'] ?? 'simpan';
    $id = (int)($_POST['id_transaksi'] ?? 0);

    if ($aksi == 'hapus') {
        query($koneksi, "DELETE FROM tb_transaksi WHERE id_transaksi=?", "i", $id);
        set_flash("Transaksi dihapus.");
    } else {
        $id_user = (int)($_POST['id_user'] ?? 0);
        $id_barang = (int)($_POST['id_barang'] ?? 0);
        $berat = (float)($_POST['berat_barang'] ?? 0);

        $user = mysqli_fetch_assoc(mysqli_stmt_get_result(
            query($koneksi, "SELECT id_user FROM tb_user WHERE id_user=? AND role='customer'", "i", $id_user)
        ));
        $barang = mysqli_fetch_assoc(mysqli_stmt_get_result(
            query($koneksi, "SELECT id_barang FROM tb_barang WHERE id_barang=?", "i", $id_barang)
        ));

        if (!$user || !$barang || $berat <= 0 || $berat > 1000) {
            set_flash("Pilih customer dan barang, lalu isi berat 0,1 sampai 1000 kg.", "gagal");
        } elseif ($id > 0) {
            query($koneksi, "UPDATE tb_transaksi SET id_user=?, id_barang=?, berat_barang=? WHERE id_transaksi=?",
                "iidi", $id_user, $id_barang, $berat, $id);
            set_flash("Transaksi diperbarui.");
        } else {
            query($koneksi, "INSERT INTO tb_transaksi (id_user, id_barang, berat_barang) VALUES (?, ?, ?)",
                "iid", $id_user, $id_barang, $berat);
            set_flash("Transaksi ditambahkan.");
        }
    }
    header("Location: transaksi.php");
    exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $s = query($koneksi, "SELECT * FROM tb_transaksi WHERE id_transaksi=?", "i", (int)$_GET['edit']);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
}

$customer = mysqli_fetch_all(mysqli_query($koneksi, "SELECT id_user, nama FROM tb_user WHERE role='customer' ORDER BY nama"), MYSQLI_ASSOC);
$barang = mysqli_fetch_all(mysqli_query($koneksi, "SELECT b.id_barang, k.nama_kategori, b.berat_barang
    FROM tb_barang b JOIN tb_kategori k ON b.id_kategori = k.id_kategori ORDER BY b.id_barang"), MYSQLI_ASSOC);

// JOIN: transaksi lengkap dengan nama customer, kategori, dan harga
$data = mysqli_query($koneksi, "SELECT tr.id_transaksi, u.nama, k.nama_kategori, k.harga, tr.berat_barang
    FROM tb_transaksi tr
    JOIN tb_user u ON tr.id_user = u.id_user
    JOIN tb_barang b ON tr.id_barang = b.id_barang
    JOIN tb_kategori k ON b.id_kategori = k.id_kategori
    ORDER BY tr.id_transaksi DESC");

include "header.php";
tampil_flash();
?>
<section class="card">
  <h2><?= $edit ? "Ubah transaksi" : "Tambah transaksi" ?></h2>
  <?php if (!$customer || !$barang): ?>
    <p>Isi dulu data customer di menu User dan data di menu Barang.</p>
  <?php else: ?>
  <form method="post">
    <input type="hidden" name="aksi" value="simpan">
    <input type="hidden" name="id_transaksi" value="<?= $edit ? (int)$edit['id_transaksi'] : 0 ?>">
    <label for="id_user">Customer</label>
    <select id="id_user" name="id_user" required>
      <?php foreach ($customer as $c): ?>
        <option value="<?= (int)$c['id_user'] ?>" <?= ($edit && $edit['id_user'] == $c['id_user']) ? "selected" : "" ?>><?= e($c['nama']) ?></option>
      <?php endforeach; ?>
    </select>
    <label for="id_barang">Barang</label>
    <select id="id_barang" name="id_barang" required>
      <?php foreach ($barang as $b): ?>
        <option value="<?= (int)$b['id_barang'] ?>" <?= ($edit && $edit['id_barang'] == $b['id_barang']) ? "selected" : "" ?>>#<?= (int)$b['id_barang'] ?> - <?= e($b['nama_kategori']) ?> (<?= kg($b['berat_barang']) ?>)</option>
      <?php endforeach; ?>
    </select>
    <label for="berat_barang">Berat (kg)</label>
    <input type="number" id="berat_barang" name="berat_barang" step="0.1" min="0.1" max="1000" required value="<?= $edit ? e($edit['berat_barang']) : "" ?>">
    <p>Total bayar dihitung otomatis dari harga per kg x berat.</p>
    <p>
      <button class="btn" type="submit"><?= $edit ? "Simpan perubahan" : "Tambah transaksi" ?></button>
      <?php if ($edit): ?><a class="btn abu" href="transaksi.php">Batal</a><?php endif; ?>
    </p>
  </form>
  <?php endif; ?>
</section>

<section class="card">
  <h2>Daftar transaksi</h2>
  <div class="scroll">
  <table>
    <tr><th>ID</th><th>Customer</th><th>Layanan</th><th>Berat</th><th>Total</th><th>Aksi</th></tr>
    <?php while ($r = mysqli_fetch_assoc($data)): ?>
    <tr>
      <td><?= (int)$r['id_transaksi'] ?></td>
      <td><?= e($r['nama']) ?></td>
      <td><?= e($r['nama_kategori']) ?> (<?= rupiah($r['harga']) ?>/kg)</td>
      <td><?= kg($r['berat_barang']) ?></td>
      <td><?= rupiah(hitung_total($r['harga'], $r['berat_barang'])) ?></td>
      <td class="aksi">
        <a class="btn kecil abu" href="transaksi.php?edit=<?= (int)$r['id_transaksi'] ?>">Ubah</a>
        <form method="post" style="display:inline" onsubmit="return confirm('Hapus transaksi ini?')">
          <input type="hidden" name="aksi" value="hapus">
          <input type="hidden" name="id_transaksi" value="<?= (int)$r['id_transaksi'] ?>">
          <button class="btn kecil merah" type="submit">Hapus</button>
        </form>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
  </div>
</section>
<?php include "footer.php"; ?>

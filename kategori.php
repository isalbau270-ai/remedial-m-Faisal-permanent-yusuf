<?php
require "koneksi.php";
wajib_login('admin');
$judul = "Data Kategori";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $aksi = $_POST['aksi'] ?? 'simpan';
    $id = (int)($_POST['id_kategori'] ?? 0);

    if ($aksi == 'hapus') {
        try {
            query($koneksi, "DELETE FROM tb_kategori WHERE id_kategori=?", "i", $id);
            set_flash("Kategori dihapus.");
        } catch (mysqli_sql_exception $ex) {
            set_flash("Kategori tidak bisa dihapus karena sudah dipakai di data barang.", "gagal");
        }
    } else {
        $nama = trim($_POST['nama_kategori'] ?? '');
        $harga = (int)($_POST['harga'] ?? 0);

        if ($nama === '' || $harga <= 0) {
            set_flash("Isi nama kategori dan harga lebih dari 0.", "gagal");
        } elseif ($id > 0) {
            query($koneksi, "UPDATE tb_kategori SET nama_kategori=?, harga=? WHERE id_kategori=?", "sii", $nama, $harga, $id);
            set_flash("Kategori diperbarui.");
        } else {
            query($koneksi, "INSERT INTO tb_kategori (nama_kategori, harga) VALUES (?, ?)", "si", $nama, $harga);
            set_flash("Kategori ditambahkan.");
        }
    }
    header("Location: kategori.php");
    exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $s = query($koneksi, "SELECT * FROM tb_kategori WHERE id_kategori=?", "i", (int)$_GET['edit']);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
}

$data = mysqli_query($koneksi, "SELECT * FROM tb_kategori ORDER BY id_kategori");
include "header.php";
tampil_flash();
?>
<section class="card">
  <h2><?= $edit ? "Ubah kategori" : "Tambah kategori" ?></h2>
  <form method="post">
    <input type="hidden" name="aksi" value="simpan">
    <input type="hidden" name="id_kategori" value="<?= $edit ? (int)$edit['id_kategori'] : 0 ?>">
    <label for="nama_kategori">Nama kategori</label>
    <input type="text" id="nama_kategori" name="nama_kategori" maxlength="50" required value="<?= $edit ? e($edit['nama_kategori']) : "" ?>">
    <label for="harga">Harga per kg (Rp)</label>
    <input type="number" id="harga" name="harga" min="1" required value="<?= $edit ? (int)$edit['harga'] : "" ?>">
    <p>
      <button class="btn" type="submit"><?= $edit ? "Simpan perubahan" : "Tambah kategori" ?></button>
      <?php if ($edit): ?><a class="btn abu" href="kategori.php">Batal</a><?php endif; ?>
    </p>
  </form>
</section>

<section class="card">
  <h2>Daftar kategori</h2>
  <div class="scroll">
  <table>
    <tr><th>ID</th><th>Nama kategori</th><th>Harga per kg</th><th>Aksi</th></tr>
    <?php while ($r = mysqli_fetch_assoc($data)): ?>
    <tr>
      <td><?= (int)$r['id_kategori'] ?></td>
      <td><?= e($r['nama_kategori']) ?></td>
      <td><?= rupiah($r['harga']) ?></td>
      <td class="aksi">
        <a class="btn kecil abu" href="kategori.php?edit=<?= (int)$r['id_kategori'] ?>">Ubah</a>
        <form method="post" style="display:inline" onsubmit="return confirm('Hapus kategori ini?')">
          <input type="hidden" name="aksi" value="hapus">
          <input type="hidden" name="id_kategori" value="<?= (int)$r['id_kategori'] ?>">
          <button class="btn kecil merah" type="submit">Hapus</button>
        </form>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
  </div>
</section>
<?php include "footer.php"; ?>

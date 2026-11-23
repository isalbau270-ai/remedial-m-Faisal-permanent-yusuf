<?php
require "koneksi.php";
wajib_login('admin');
$judul = "Data Barang";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $aksi = $_POST['aksi'] ?? 'simpan';
    $id = (int)($_POST['id_barang'] ?? 0);

    if ($aksi == 'hapus') {
        try {
            query($koneksi, "DELETE FROM tb_barang WHERE id_barang=?", "i", $id);
            set_flash("Barang dihapus.");
        } catch (mysqli_sql_exception $ex) {
            set_flash("Barang tidak bisa dihapus karena sudah dipakai di transaksi.", "gagal");
        }
    } else {
        $id_kategori = (int)($_POST['id_kategori'] ?? 0);
        $berat = (float)($_POST['berat_barang'] ?? 0);

        if ($id_kategori <= 0 || $berat <= 0 || $berat > 1000) {
            set_flash("Pilih kategori dan isi berat antara 0,01 sampai 1000 kg.", "gagal");
        } else {
            try {
                if ($id > 0) {
                    query($koneksi, "UPDATE tb_barang SET id_kategori=?, berat_barang=? WHERE id_barang=?", "idi", $id_kategori, $berat, $id);
                    set_flash("Barang diperbarui.");
                } else {
                    query($koneksi, "INSERT INTO tb_barang (id_kategori, berat_barang) VALUES (?, ?)", "id", $id_kategori, $berat);
                    set_flash("Barang ditambahkan.");
                }
            } catch (mysqli_sql_exception $ex) {
                set_flash("Kategori tidak valid.", "gagal");
            }
        }
    }
    header("Location: barang.php");
    exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $s = query($koneksi, "SELECT * FROM tb_barang WHERE id_barang=?", "i", (int)$_GET['edit']);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
}

$kategori = mysqli_fetch_all(mysqli_query($koneksi, "SELECT * FROM tb_kategori ORDER BY id_kategori"), MYSQLI_ASSOC);
$data = mysqli_query($koneksi, "SELECT b.id_barang, k.nama_kategori, k.harga, b.berat_barang
    FROM tb_barang b JOIN tb_kategori k ON b.id_kategori = k.id_kategori
    ORDER BY b.id_barang");
include "header.php";
tampil_flash();
?>
<section class="card">
  <h2><?= $edit ? "Ubah barang" : "Tambah barang" ?></h2>
  <?php if (!$kategori): ?>
    <p>Isi dulu data di menu Kategori.</p>
  <?php else: ?>
  <form method="post">
    <input type="hidden" name="aksi" value="simpan">
    <input type="hidden" name="id_barang" value="<?= $edit ? (int)$edit['id_barang'] : 0 ?>">
    <label for="id_kategori">Kategori</label>
    <select id="id_kategori" name="id_kategori" required>
      <?php foreach ($kategori as $k): ?>
        <option value="<?= (int)$k['id_kategori'] ?>" <?= ($edit && $edit['id_kategori'] == $k['id_kategori']) ? "selected" : "" ?>><?= e($k['nama_kategori']) ?> - <?= rupiah($k['harga']) ?>/kg</option>
      <?php endforeach; ?>
    </select>
    <label for="berat_barang">Berat (kg)</label>
    <input type="number" id="berat_barang" name="berat_barang" step="0.1" min="0.1" max="1000" required value="<?= $edit ? e($edit['berat_barang']) : "" ?>">
    <p>
      <button class="btn" type="submit"><?= $edit ? "Simpan perubahan" : "Tambah barang" ?></button>
      <?php if ($edit): ?><a class="btn abu" href="barang.php">Batal</a><?php endif; ?>
    </p>
  </form>
  <?php endif; ?>
</section>

<section class="card">
  <h2>Daftar barang</h2>
  <div class="scroll">
  <table>
    <tr><th>ID</th><th>Kategori</th><th>Berat</th><th>Aksi</th></tr>
    <?php while ($r = mysqli_fetch_assoc($data)): ?>
    <tr>
      <td><?= (int)$r['id_barang'] ?></td>
      <td><?= e($r['nama_kategori']) ?></td>
      <td><?= kg($r['berat_barang']) ?></td>
      <td class="aksi">
        <a class="btn kecil abu" href="barang.php?edit=<?= (int)$r['id_barang'] ?>">Ubah</a>
        <form method="post" style="display:inline" onsubmit="return confirm('Hapus barang ini?')">
          <input type="hidden" name="aksi" value="hapus">
          <input type="hidden" name="id_barang" value="<?= (int)$r['id_barang'] ?>">
          <button class="btn kecil merah" type="submit">Hapus</button>
        </form>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
  </div>
</section>
<?php include "footer.php"; ?>

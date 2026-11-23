<?php
require "koneksi.php";
wajib_login('customer');
$judul = "Pesan Laundry";
$id_user = (int)$_SESSION['id_user'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_kategori = (int)($_POST['id_kategori'] ?? 0);
    $berat = (float)($_POST['berat_barang'] ?? 0);

    $k = mysqli_fetch_assoc(mysqli_stmt_get_result(
        query($koneksi, "SELECT harga FROM tb_kategori WHERE id_kategori=?", "i", $id_kategori)
    ));

    if (!$k || $berat < 0.1 || $berat > 100) {
        set_flash("Pilih layanan dan isi berat 0,1 sampai 100 kg.", "gagal");
    } else {
        // Simpan barang, lalu simpan transaksinya
        mysqli_begin_transaction($koneksi);
        try {
            $s = query($koneksi, "INSERT INTO tb_barang (id_kategori, berat_barang) VALUES (?, ?)", "id", $id_kategori, $berat);
            $id_barang = mysqli_stmt_insert_id($s);
            query($koneksi, "INSERT INTO tb_transaksi (id_user, id_barang, berat_barang) VALUES (?, ?, ?)",
                "iid", $id_user, $id_barang, $berat);
            mysqli_commit($koneksi);
            set_flash("Pesanan dibuat. Total bayar: " . rupiah(hitung_total($k['harga'], $berat)));
        } catch (mysqli_sql_exception $ex) {
            mysqli_rollback($koneksi);
            set_flash("Pesanan gagal disimpan, coba lagi.", "gagal");
        }
    }
    header("Location: customer.php");
    exit;
}

$kategori = mysqli_fetch_all(mysqli_query($koneksi, "SELECT * FROM tb_kategori ORDER BY id_kategori"), MYSQLI_ASSOC);

$riwayat = mysqli_stmt_get_result(query($koneksi, "SELECT tr.id_transaksi, k.nama_kategori, k.harga, tr.berat_barang
    FROM tb_transaksi tr
    JOIN tb_barang b ON tr.id_barang = b.id_barang
    JOIN tb_kategori k ON b.id_kategori = k.id_kategori
    WHERE tr.id_user=? ORDER BY tr.id_transaksi DESC", "i", $id_user));

include "header.php";
tampil_flash();
?>
<section class="card">
  <h2>Pesan laundry</h2>
  <?php if (!$kategori): ?>
    <p>Belum ada layanan. Hubungi admin.</p>
  <?php else: ?>
  <form method="post">
    <label for="id_kategori">Layanan</label>
    <select id="id_kategori" name="id_kategori" required>
      <?php foreach ($kategori as $k): ?>
        <option value="<?= (int)$k['id_kategori'] ?>" data-harga="<?= (int)$k['harga'] ?>"><?= e($k['nama_kategori']) ?> - <?= rupiah($k['harga']) ?>/kg</option>
      <?php endforeach; ?>
    </select>
    <label for="berat_barang">Berat (kg)</label>
    <input type="number" id="berat_barang" name="berat_barang" step="0.1" min="0.1" max="100" value="1" required>
    <p>Total: <span class="total" id="total">Rp 0</span></p>
    <button class="btn" type="submit">Pesan</button>
  </form>
  <?php endif; ?>
</section>

<section class="card">
  <h2>Riwayat pesanan</h2>
  <?php if (mysqli_num_rows($riwayat) == 0): ?>
    <p>Belum ada pesanan. Buat pesanan pertamamu di atas.</p>
  <?php else: ?>
  <div class="scroll">
  <table>
    <tr><th>ID</th><th>Layanan</th><th>Berat</th><th>Total</th><th>Nota</th></tr>
    <?php while ($r = mysqli_fetch_assoc($riwayat)): ?>
    <tr>
      <td><?= (int)$r['id_transaksi'] ?></td>
      <td><?= e($r['nama_kategori']) ?></td>
      <td><?= kg($r['berat_barang']) ?></td>
      <td><?= rupiah(hitung_total($r['harga'], $r['berat_barang'])) ?></td>
      <td><a href="cetak.php?id=<?= (int)$r['id_transaksi'] ?>">Cetak</a></td>
    </tr>
    <?php endwhile; ?>
  </table>
  </div>
  <?php endif; ?>
</section>

<script>
const pilih = document.getElementById('id_kategori');
const berat = document.getElementById('berat_barang');
const out = document.getElementById('total');
function hitung() {
  if (!pilih) return;
  const h = parseInt(pilih.selectedOptions[0].dataset.harga || 0);
  const b = parseFloat(berat.value || 0);
  out.textContent = 'Rp ' + Math.round(h * b).toLocaleString('id-ID');
}
if (pilih) {
  pilih.addEventListener('change', hitung);
  berat.addEventListener('input', hitung);
  hitung();
}
</script>
<?php include "footer.php"; ?>

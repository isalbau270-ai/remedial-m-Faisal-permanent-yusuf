<?php
require "koneksi.php";
wajib_login('admin');
$judul = "Data User";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $aksi = $_POST['aksi'] ?? 'simpan';
    $id = (int)($_POST['id_user'] ?? 0);

    if ($aksi == 'hapus') {
        if ($id == $_SESSION['id_user']) {
            set_flash("Akun yang sedang dipakai tidak bisa dihapus.", "gagal");
        } else {
            try {
                query($koneksi, "DELETE FROM tb_user WHERE id_user=?", "i", $id);
                set_flash("User dihapus.");
            } catch (mysqli_sql_exception $ex) {
                set_flash("User tidak bisa dihapus karena sudah punya transaksi.", "gagal");
            }
        }
    } else {
        $username = trim($_POST['username'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $role = $_POST['role'] ?? '';
        $pass = $_POST['pass'] ?? '';

        if ($username === '' || $nama === '' || !in_array($role, ['admin', 'customer'])) {
            set_flash("Username, nama, dan role wajib diisi.", "gagal");
        } elseif ($id == 0 && $pass === '') {
            set_flash("Password wajib diisi untuk user baru.", "gagal");
        } else {
            try {
                if ($id > 0 && $pass !== '') {
                    query($koneksi, "UPDATE tb_user SET username=?, pass=?, role=?, nama=? WHERE id_user=?",
                        "ssssi", $username, md5($pass), $role, $nama, $id);
                    set_flash("User diperbarui.");
                } elseif ($id > 0) {
                    query($koneksi, "UPDATE tb_user SET username=?, role=?, nama=? WHERE id_user=?",
                        "sssi", $username, $role, $nama, $id);
                    set_flash("User diperbarui.");
                } else {
                    query($koneksi, "INSERT INTO tb_user (username, pass, role, nama) VALUES (?, ?, ?, ?)",
                        "ssss", $username, md5($pass), $role, $nama);
                    set_flash("User ditambahkan.");
                }
            } catch (mysqli_sql_exception $ex) {
                if ($ex->getCode() == 1062) {
                    set_flash("Username sudah dipakai, pilih yang lain.", "gagal");
                } else {
                    throw $ex;
                }
            }
        }
    }
    header("Location: user.php");
    exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $s = query($koneksi, "SELECT * FROM tb_user WHERE id_user=?", "i", (int)$_GET['edit']);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
}

$data = mysqli_query($koneksi, "SELECT * FROM tb_user ORDER BY id_user");
include "header.php";
tampil_flash();
?>
<section class="card">
  <h2><?= $edit ? "Ubah user" : "Tambah user" ?></h2>
  <form method="post">
    <input type="hidden" name="aksi" value="simpan">
    <input type="hidden" name="id_user" value="<?= $edit ? (int)$edit['id_user'] : 0 ?>">
    <label for="username">Username</label>
    <input type="text" id="username" name="username" maxlength="50" required value="<?= $edit ? e($edit['username']) : "" ?>">
    <label for="nama">Nama</label>
    <input type="text" id="nama" name="nama" maxlength="100" required value="<?= $edit ? e($edit['nama']) : "" ?>">
    <label for="pass">Password</label>
    <input type="password" id="pass" name="pass" <?= $edit ? 'placeholder="Kosongkan jika tidak diubah"' : 'required' ?>>
    <label for="role">Role</label>
    <select id="role" name="role" required>
      <?php foreach (['admin', 'customer'] as $r): ?>
        <option value="<?= $r ?>" <?= ($edit && $edit['role'] == $r) ? "selected" : "" ?>><?= $r ?></option>
      <?php endforeach; ?>
    </select>
    <p>
      <button class="btn" type="submit"><?= $edit ? "Simpan perubahan" : "Tambah user" ?></button>
      <?php if ($edit): ?><a class="btn abu" href="user.php">Batal</a><?php endif; ?>
    </p>
  </form>
</section>

<section class="card">
  <h2>Daftar user</h2>
  <div class="scroll">
  <table>
    <tr><th>ID</th><th>Username</th><th>Nama</th><th>Role</th><th>Aksi</th></tr>
    <?php while ($r = mysqli_fetch_assoc($data)): ?>
    <tr>
      <td><?= (int)$r['id_user'] ?></td>
      <td><?= e($r['username']) ?></td>
      <td><?= e($r['nama']) ?></td>
      <td><?= e($r['role']) ?></td>
      <td class="aksi">
        <a class="btn kecil abu" href="user.php?edit=<?= (int)$r['id_user'] ?>">Ubah</a>
        <form method="post" style="display:inline" onsubmit="return confirm('Hapus user ini?')">
          <input type="hidden" name="aksi" value="hapus">
          <input type="hidden" name="id_user" value="<?= (int)$r['id_user'] ?>">
          <button class="btn kecil merah" type="submit">Hapus</button>
        </form>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
  </div>
</section>
<?php include "footer.php"; ?>

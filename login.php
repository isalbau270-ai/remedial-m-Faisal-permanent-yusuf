<?php
require "koneksi.php";

if (isset($_SESSION['id_user'])) {
    header("Location: " . ($_SESSION['role'] == 'admin' ? "admin.php" : "customer.php"));
    exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $pass = md5($_POST['pass'] ?? '');

    $s = query($koneksi, "SELECT id_user, nama, role FROM tb_user WHERE username=? AND pass=?", "ss", $username, $pass);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($s));

    if ($user) {
        session_regenerate_id(true);
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
        header("Location: " . ($user['role'] == 'admin' ? "admin.php" : "customer.php"));
        exit;
    }
    $error = "Username atau password salah.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Masuk - Layanan Laundry</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<main class="login">
  <div class="card">
    <h2>Masuk ke Layanan Laundry</h2>
    <?php if ($error): ?><div class="pesan gagal"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required autofocus>
      <label for="pass">Password</label>
      <input type="password" id="pass" name="pass" required>
      <p><button class="btn" type="submit">Masuk</button></p>
    </form>
  </div>
</main>
</body>
</html>

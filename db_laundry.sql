-- Impor file ini ke database "sas" (atau database laundry milikmu)
DROP TABLE IF EXISTS tb_transaksi;
DROP TABLE IF EXISTS tb_barang;
DROP TABLE IF EXISTS tb_kategori;
DROP TABLE IF EXISTS tb_user;

CREATE TABLE tb_user (
  id_user INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  pass VARCHAR(255) NOT NULL,
  role ENUM('admin','customer') NOT NULL,
  nama VARCHAR(100) NOT NULL
);

CREATE TABLE tb_kategori (
  id_kategori INT AUTO_INCREMENT PRIMARY KEY,
  nama_kategori VARCHAR(50) NOT NULL,
  harga INT NOT NULL
);

CREATE TABLE tb_barang (
  id_barang INT AUTO_INCREMENT PRIMARY KEY,
  id_kategori INT NOT NULL,
  berat_barang DECIMAL(6,2) NOT NULL,
  FOREIGN KEY (id_kategori) REFERENCES tb_kategori(id_kategori)
);

CREATE TABLE tb_transaksi (
  id_transaksi INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT NOT NULL,
  id_barang INT NOT NULL,
  berat_barang DECIMAL(6,2) NOT NULL,
  FOREIGN KEY (id_user) REFERENCES tb_user(id_user),
  FOREIGN KEY (id_barang) REFERENCES tb_barang(id_barang)
);

INSERT INTO tb_user (id_user, username, pass, role, nama) VALUES
(1, 'admin', MD5('admin123'), 'admin', 'Administrator'),
(2, 'budi', MD5('budi123'), 'customer', 'Budi Santoso'),
(3, 'siti', MD5('siti123'), 'customer', 'Siti Aminah'),
(4, 'andi', MD5('andi123'), 'customer', 'Andi Pratama');

INSERT INTO tb_kategori (id_kategori, nama_kategori, harga) VALUES
(1, 'Cuci Kering', 6000),
(2, 'Cuci Setrika', 8000),
(3, 'Setrika Saja', 5000),
(4, 'Dry Cleaning', 15000);

INSERT INTO tb_barang (id_barang, id_kategori, berat_barang) VALUES
(1, 2, 2.5),
(2, 1, 3.0),
(3, 2, 4.0),
(4, 3, 1.5),
(5, 4, 2.0);

INSERT INTO tb_transaksi (id_transaksi, id_user, id_barang, berat_barang) VALUES
(1, 2, 1, 2.5),
(2, 3, 2, 3.0),
(3, 4, 3, 4.0),
(4, 2, 4, 1.5),
(5, 3, 5, 2.0);

-- Query JOIN: data transaksi lengkap
SELECT tr.id_transaksi, u.nama AS nama_customer, k.nama_kategori, k.harga AS harga_per_kg,
       tr.berat_barang, (tr.berat_barang * k.harga) AS total_bayar
FROM tb_transaksi tr
JOIN tb_user u ON tr.id_user = u.id_user
JOIN tb_barang b ON tr.id_barang = b.id_barang
JOIN tb_kategori k ON b.id_kategori = k.id_kategori
ORDER BY tr.id_transaksi;

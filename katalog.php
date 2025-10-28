<?php
require_once 'config.php';
check_role(['customer']);

$success = '';
$error = '';

// Tambah ke keranjang
if (isset($_POST['add_to_cart'])) {
    $id_barang = clean_input($_POST['id_barang']);
    $jumlah = clean_input($_POST['jumlah']);
    
    // Cek stok
    $query = "SELECT stok FROM barang WHERE id = '$id_barang'";
    $result = mysqli_query($conn, $query);
    $barang = mysqli_fetch_assoc($result);
    
    if ($barang['stok'] >= $jumlah) {
        // Simpan ke session keranjang
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$id_barang])) {
            $_SESSION['cart'][$id_barang] += $jumlah;
        } else {
            $_SESSION['cart'][$id_barang] = $jumlah;
        }
        
        $success = "Barang berhasil ditambahkan ke keranjang!";
    } else {
        $error = "Stok tidak mencukupi!";
    }
}

// Ambil data barang
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$kategori = isset($_GET['kategori']) ? clean_input($_GET['kategori']) : '';

$query = "SELECT b.*, k.nama_kategori 
          FROM barang b 
          LEFT JOIN kategori k ON b.id_kategori = k.id 
          WHERE b.stok > 0";

if ($search) {
    $query .= " AND b.nama_barang LIKE '%$search%'";
}

if ($kategori) {
    $query .= " AND b.id_kategori = '$kategori'";
}

$query .= " ORDER BY b.nama_barang";
$result = mysqli_query($conn, $query);

// Ambil kategori untuk filter
$query_kategori = "SELECT * FROM kategori ORDER BY nama_kategori";
$kategori_list = mysqli_query($conn, $query_kategori);

// Hitung item di keranjang
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Produk - Aplikasi Penjualan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Katalog Produk</h1>
            <p>Jelajahi produk kami</p>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Filter -->
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-body">
                <form method="GET" action="" style="display: flex; gap: 10px; align-items: end;">
                    <div class="form-group" style="flex: 1; margin: 0;">
                        <label>Cari Produk</label>
                        <input type="text" name="search" class="form-control" 
                               value="<?php echo $search; ?>" placeholder="Nama produk...">
                    </div>
                    
                    <div class="form-group" style="flex: 1; margin: 0;">
                        <label>Kategori Barang</label>
                        <select name="kategori" class="form-control">
                            <option value="">Semua Kategori</option>
                            <?php while ($kat = mysqli_fetch_assoc($kategori_list)): ?>
                                <option value="<?php echo $kat['id']; ?>" 
                                    <?php echo $kategori == $kat['id'] ? 'selected' : ''; ?>>
                                    <?php echo $kat['nama_kategori']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="katalog.php" class="btn btn-danger">Reset</a>
                </form>
            </div>
        </div>
        
        <!-- Produk Grid -->
        <div class="product-grid">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="product-card">
                <img src="<?php echo $row['foto'] ? 'uploads/'.$row['foto'] : 'https://via.placeholder.com/250x200?text=No+Image'; ?>" 
                     alt="<?php echo $row['nama_barang']; ?>" 
                     class="product-image">
                <div class="product-info">
                    <div class="product-name"><?php echo $row['nama_barang']; ?></div>
                    <div class="product-price"><?php echo format_rupiah($row['harga']); ?></div>
                    <div class="product-stock">Stok: <?php echo $row['stok']; ?> <?php echo $row['tipe_barang']; ?></div>
                    <div style="font-size: 12px; color: #999; margin-bottom: 10px;">
                        <?php echo $row['nama_kategori']; ?>
                    </div>
                    
                    <form method="POST" action="" style="display: flex; gap: 5px;">
                        <input type="hidden" name="id_barang" value="<?php echo $row['id']; ?>">
                        <input type="number" name="jumlah" class="form-control" 
                               value="1" min="1" max="<?php echo $row['stok']; ?>" 
                               style="width: 70px;">
                        <button type="submit" name="add_to_cart" class="btn btn-primary" style="flex: 1;">
                            🛒 Tambah
                        </button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        
        <?php if (mysqli_num_rows($result) == 0): ?>
            <div class="alert alert-info" style="text-align: center;">
                Tidak ada produk yang ditemukan.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
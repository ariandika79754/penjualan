<?php
require_once 'config.php';
check_role(['customer']);

$success = '';
$error = '';

// Hapus dari keranjang
if (isset($_GET['hapus'])) {
    $id_barang = clean_input($_GET['hapus']);
    unset($_SESSION['cart'][$id_barang]);
    $success = "Item berhasil dihapus dari keranjang!";
}

// Update jumlah
if (isset($_POST['update_cart'])) {
    $id_barang = clean_input($_POST['id_barang']);
    $jumlah = clean_input($_POST['jumlah']);
    
    if ($jumlah > 0) {
        $_SESSION['cart'][$id_barang] = $jumlah;
        $success = "Keranjang berhasil diupdate!";
    }
}

// Checkout
if (isset($_POST['checkout'])) {
    if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
        $kode_transaksi = generate_kode_transaksi();
        $id_customer = $_SESSION['user_id'];
        $total_harga = 0;
        
        // Hitung total
        foreach ($_SESSION['cart'] as $id_barang => $qty) {
            $query = "SELECT harga FROM barang WHERE id = '$id_barang'";
            $result = mysqli_query($conn, $query);
            $barang = mysqli_fetch_assoc($result);
            $total_harga += $barang['harga'] * $qty;
        }
        
        // Insert transaksi
        $query_transaksi = "INSERT INTO transaksi (kode_transaksi, id_customer, total_harga, status) 
                           VALUES ('$kode_transaksi', '$id_customer', '$total_harga', 'pending')";
        
        if (mysqli_query($conn, $query_transaksi)) {
            $id_transaksi = mysqli_insert_id($conn);
            
            // Insert detail transaksi dan update stok
            foreach ($_SESSION['cart'] as $id_barang => $qty) {
                $query = "SELECT harga, stok FROM barang WHERE id = '$id_barang'";
                $result = mysqli_query($conn, $query);
                $barang = mysqli_fetch_assoc($result);
                
                $harga_satuan = $barang['harga'];
                $subtotal = $harga_satuan * $qty;
                
                // Insert detail
                $query_detail = "INSERT INTO detail_transaksi (id_transaksi, id_barang, jumlah, harga_satuan, subtotal) 
                                VALUES ('$id_transaksi', '$id_barang', '$qty', '$harga_satuan', '$subtotal')";
                mysqli_query($conn, $query_detail);
                
                // Update stok
                $stok_baru = $barang['stok'] - $qty;
                $query_update = "UPDATE barang SET stok = '$stok_baru' WHERE id = '$id_barang'";
                mysqli_query($conn, $query_update);
            }
            
            // Kosongkan keranjang
            unset($_SESSION['cart']);
            
            header("Location: pesanan.php?success=1");
            exit();
        } else {
            $error = "Gagal memproses pesanan!";
        }
    }
}

// Ambil data keranjang
$cart_items = [];
$total = 0;

if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    $query = "SELECT * FROM barang WHERE id IN ($ids)";
    $result = mysqli_query($conn, $query);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $row['qty'] = $_SESSION['cart'][$row['id']];
        $row['subtotal'] = $row['harga'] * $row['qty'];
        $total += $row['subtotal'];
        $cart_items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Aplikasi Penjualan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Keranjang Belanja</h1>
            <p>Kelola pesanan Anda</p>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (count($cart_items) > 0): ?>
        <div class="card">
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nama Barang</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td>
                                <img src="<?php echo $item['foto'] ? 'uploads/'.$item['foto'] : 'https://via.placeholder.com/60'; ?>" 
                                     alt="<?php echo $item['nama_barang']; ?>"
                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                            </td>
                            <td><?php echo $item['nama_barang']; ?></td>
                            <td><?php echo format_rupiah($item['harga']); ?></td>
                            <td>
                                <form method="POST" action="" style="display: flex; gap: 5px; align-items: center;">
                                    <input type="hidden" name="id_barang" value="<?php echo $item['id']; ?>">
                                    <input type="number" name="jumlah" class="form-control" 
                                           value="<?php echo $item['qty']; ?>" 
                                           min="1" max="<?php echo $item['stok']; ?>"
                                           style="width: 80px;">
                                    <button type="submit" name="update_cart" class="btn btn-info btn-sm">Update</button>
                                </form>
                            </td>
                            <td><?php echo format_rupiah($item['subtotal']); ?></td>
                            <td>
                                <a href="keranjang.php?hapus=<?php echo $item['id']; ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Hapus item ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr style="background: #f8f9fa; font-weight: bold;">
                            <td colspan="4" style="text-align: right;">Total:</td>
                            <td colspan="2"><?php echo format_rupiah($total); ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                    <a href="katalog.php" class="btn btn-info">Lanjut Belanja</a>
                    <form method="POST" action="">
                        <button type="submit" name="checkout" class="btn btn-success" 
                                onclick="return confirm('Proses checkout?')">
                            Checkout
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 50px;">
                <h2>🛒</h2>
                <h3>Keranjang Kosong</h3>
                <p>Belum ada produk di keranjang Anda.</p>
                <a href="katalog.php" class="btn btn-primary">Mulai Belanja</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
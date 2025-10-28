<?php
require_once 'config.php';
check_role(['admin', 'kasir']);

$success = '';
$error = '';

// Hapus barang
if (isset($_GET['hapus']) && $_SESSION['role'] == 'admin') {
    $id = clean_input($_GET['hapus']);
    $query = "DELETE FROM barang WHERE id = '$id'";
    if (mysqli_query($conn, $query)) {
        $success = "Barang berhasil dihapus!";
    } else {
        $error = "Gagal menghapus barang!";
    }
}

// Ambil data barang
$query = "SELECT b.*, k.nama_kategori 
          FROM barang b 
          LEFT JOIN kategori k ON b.id_kategori = k.id 
          ORDER BY b.id DESC";
$result = mysqli_query($conn, $query);

// Ambil kategori untuk form
$query_kategori = "SELECT * FROM kategori ORDER BY nama_kategori";
$kategori_list = mysqli_query($conn, $query_kategori);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Barang - Aplikasi Penjualan Barang</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Data Barang</h1>
            <p>Kelola data barang produk</p>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h2>Daftar Barang</h2>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <a href="barang_form.php" class="btn btn-primary">+ Tambah Barang</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Foto</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Tipe</th>
                            <?php if ($_SESSION['role'] == 'admin'): ?>
                                <th>Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($result)): 
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td>
                                <?php if ($row['foto']): ?>
                                    <img src="uploads/<?php echo $row['foto']; ?>" 
                                         alt="<?php echo $row['nama_barang']; ?>" 
                                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                <?php else: ?>
                                    <div style="width: 60px; height: 60px; background: #ddd; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                        📦
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $row['nama_barang']; ?></td>
                            <td><?php echo $row['nama_kategori'] ?? '-'; ?></td>
                            <td><?php echo format_rupiah($row['harga']); ?></td>
                            <td>
                                <span class="badge <?php echo $row['stok'] > 10 ? 'badge-selesai' : 'badge-pending'; ?>">
                                    <?php echo $row['stok']; ?>
                                </span>
                            </td>
                            <td><?php echo $row['tipe_barang']; ?></td>
                            <?php if ($_SESSION['role'] == 'admin'): ?>
                            <td>
                                <a href="barang_form.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">Edit</a>
                                <a href="barang.php?hapus=<?php echo $row['id']; ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
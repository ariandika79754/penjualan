<?php
require_once 'config.php';
check_role(['customer']);

$id_customer = $_SESSION['user_id'];

// Ambil data pesanan
$query = "SELECT * FROM transaksi WHERE id_customer = '$id_customer' ORDER BY tanggal_transaksi DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Aplikasi Penjualan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Pesanan Saya</h1>
            <p>Riwayat pesanan Anda</p>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                ✓ Pesanan berhasil dibuat! Silakan tunggu konfirmasi dari admin.
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Transaksi</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($result)): 
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo $row['kode_transaksi']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal_transaksi'])); ?></td>
                            <td><?php echo format_rupiah($row['total_harga']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $row['status']; ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="detail_pesanan.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-info btn-sm">Detail</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <?php if (mysqli_num_rows($result) == 0): ?>
                    <div style="text-align: center; padding: 50px;">
                        <h3>Belum Ada Pesanan</h3>
                        <p>Anda belum memiliki riwayat pesanan.</p>
                        <a href="katalog.php" class="btn btn-primary">Mulai Belanja</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
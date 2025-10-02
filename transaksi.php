<?php
require_once 'config.php';
check_role(['kasir']);

$id_kasir = $_SESSION['user_id'];

// Ambil transaksi hari ini
$query = "SELECT t.*, u.nama_lengkap as nama_customer
          FROM transaksi t
          LEFT JOIN users u ON t.id_customer = u.id
          WHERE t.id_kasir = '$id_kasir' AND DATE(t.tanggal_transaksi) = CURDATE()
          ORDER BY t.tanggal_transaksi DESC";
$result = mysqli_query($conn, $query);

// Statistik hari ini
$query_stats = "SELECT COUNT(*) as total, SUM(total_harga) as pendapatan
                FROM transaksi
                WHERE id_kasir = '$id_kasir' AND DATE(tanggal_transaksi) = CURDATE() AND status='selesai'";
$result_stats = mysqli_query($conn, $query_stats);
$stats = mysqli_fetch_assoc($result_stats);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - Aplikasi Penjualan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Riwayat Transaksi</h1>
            <p>Transaksi hari ini</p>
        </div>
        
        <div class="stats-grid" style="grid-template-columns: repeat(2, 1fr);">
            <div class="stat-card">
                <div class="stat-icon">🛒</div>
                <div class="stat-info">
                    <h3><?php echo $stats['total']; ?></h3>
                    <p>Transaksi Hari Ini</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <h3><?php echo format_rupiah($stats['pendapatan'] ?? 0); ?></h3>
                    <p>Pendapatan Hari Ini</p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2>Daftar Transaksi</h2>
                <a href="kasir.php" class="btn btn-primary">Transaksi Baru</a>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Transaksi</th>
                            <th>Waktu</th>
                            <th>Customer</th>
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
                            <td><?php echo date('H:i:s', strtotime($row['tanggal_transaksi'])); ?></td>
                            <td><?php echo $row['nama_customer'] ?? 'Guest'; ?></td>
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
                        <h3>Belum Ada Transaksi</h3>
                        <p>Belum ada transaksi hari ini.</p>
                        <a href="kasir.php" class="btn btn-primary">Mulai Transaksi</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
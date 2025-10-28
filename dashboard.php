<?php
require_once 'config.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

// Statistik untuk Admin
$stats = [];
if ($_SESSION['role'] == 'admin') {
    $stats['total_barang'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM barang"))['total'];
    $stats['total_transaksi'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi"))['total'];
    $stats['total_customer'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='customer'"))['total'];
    $stats['total_pendapatan'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_harga) as total FROM transaksi WHERE status='selesai'"))['total'] ?? 0;
}

// Transaksi terbaru
$query_transaksi = "SELECT t.*, u.nama_lengkap as nama_customer 
                    FROM transaksi t 
                    LEFT JOIN users u ON t.id_customer = u.id 
                    ORDER BY t.tanggal_transaksi DESC LIMIT 10";
$transaksi_terbaru = mysqli_query($conn, $query_transaksi);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Aplikasi Penjualan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Selamat datang, <?php echo $_SESSION['nama_lengkap']; ?> (<?php echo ucfirst($_SESSION['role']); ?>)</p>
        </div>
        
        <?php if ($_SESSION['role'] == 'admin'): ?>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_barang']; ?></h3>
                    <p>Total Barang</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🛒</div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_transaksi']; ?></h3>
                    <p>Total Transaksi</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_customer']; ?></h3>
                    <p>Total Customer</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <h3><?php echo format_rupiah($stats['total_pendapatan']); ?></h3>
                    <p>Total Pendapatan</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h2>Transaksi Terbaru Saat ini</h2>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kode Transaksi</th>
                            <th>Customer</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($transaksi_terbaru)): ?>
                        <tr>
                            <td><?php echo $row['kode_transaksi']; ?></td>
                            <td><?php echo $row['nama_customer'] ?? 'Guest'; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal_transaksi'])); ?></td>
                            <td><?php echo format_rupiah($row['total_harga']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $row['status']; ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
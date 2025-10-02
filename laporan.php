<?php
require_once 'config.php';
check_role(['admin']);

// Filter
$dari = isset($_GET['dari']) ? clean_input($_GET['dari']) : date('Y-m-01');
$sampai = isset($_GET['sampai']) ? clean_input($_GET['sampai']) : date('Y-m-d');

// Statistik
$query_stats = "SELECT 
                COUNT(*) as total_transaksi,
                SUM(CASE WHEN status='selesai' THEN total_harga ELSE 0 END) as total_pendapatan,
                SUM(CASE WHEN status='selesai' THEN 1 ELSE 0 END) as transaksi_selesai,
                SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as transaksi_pending
                FROM transaksi 
                WHERE DATE(tanggal_transaksi) BETWEEN '$dari' AND '$sampai'";
$result_stats = mysqli_query($conn, $query_stats);
$stats = mysqli_fetch_assoc($result_stats);

// Detail transaksi
$query_transaksi = "SELECT t.*, u.nama_lengkap as nama_customer, k.nama_lengkap as nama_kasir
                    FROM transaksi t
                    LEFT JOIN users u ON t.id_customer = u.id
                    LEFT JOIN users k ON t.id_kasir = k.id
                    WHERE DATE(t.tanggal_transaksi) BETWEEN '$dari' AND '$sampai'
                    ORDER BY t.tanggal_transaksi DESC";
$result_transaksi = mysqli_query($conn, $query_transaksi);

// Produk terlaris
$query_terlaris = "SELECT b.nama_barang, SUM(dt.jumlah) as total_terjual, SUM(dt.subtotal) as total_omzet
                   FROM detail_transaksi dt
                   JOIN barang b ON dt.id_barang = b.id
                   JOIN transaksi t ON dt.id_transaksi = t.id
                   WHERE t.status='selesai' AND DATE(t.tanggal_transaksi) BETWEEN '$dari' AND '$sampai'
                   GROUP BY dt.id_barang
                   ORDER BY total_terjual DESC
                   LIMIT 10";
$result_terlaris = mysqli_query($conn, $query_terlaris);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Aplikasi Penjualan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Laporan Penjualan</h1>
            <p>Analisis dan laporan penjualan</p>
        </div>
        
        <!-- Filter -->
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-body">
                <form method="GET" action="" style="display: flex; gap: 10px; align-items: end;">
                    <div class="form-group" style="flex: 1; margin: 0;">
                        <label>Dari Tanggal</label>
                        <input type="date" name="dari" class="form-control" value="<?php echo $dari; ?>">
                    </div>
                    
                    <div class="form-group" style="flex: 1; margin: 0;">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="sampai" class="form-control" value="<?php echo $sampai; ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <button onclick="window.print()" type="button" class="btn btn-info">Cetak</button>
                </form>
            </div>
        </div>
        
        <!-- Statistik -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🛒</div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_transaksi']; ?></h3>
                    <p>Total Transaksi</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">✓</div>
                <div class="stat-info">
                    <h3><?php echo $stats['transaksi_selesai']; ?></h3>
                    <p>Transaksi Selesai</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-info">
                    <h3><?php echo $stats['transaksi_pending']; ?></h3>
                    <p>Transaksi Pending</p>
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
        
        <!-- Produk Terlaris -->
        <div class="card">
            <div class="card-header">
                <h2>Produk Terlaris</h2>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Total Terjual</th>
                            <th>Total Omzet</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($result_terlaris)): 
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo $row['nama_barang']; ?></td>
                            <td><?php echo $row['total_terjual']; ?></td>
                            <td><?php echo format_rupiah($row['total_omzet']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Detail Transaksi -->
        <div class="card">
            <div class="card-header">
                <h2>Detail Transaksi</h2>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Transaksi</th>
                            <th>Tanggal</th>
                            <th>Customer</th>
                            <th>Kasir</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($result_transaksi)): 
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo $row['kode_transaksi']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal_transaksi'])); ?></td>
                            <td><?php echo $row['nama_customer'] ?? 'Guest'; ?></td>
                            <td><?php echo $row['nama_kasir'] ?? '-'; ?></td>
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
            </div>
        </div>
    </div>
    
    <style>
        @media print {
            .navbar, .page-header, .btn, form { display: none; }
            .container { max-width: 100%; }
        }
    </style>
</body>
</html>
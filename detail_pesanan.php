<?php
require_once 'config.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$id_transaksi = clean_input($_GET['id']);

// Ambil data transaksi
$query = "SELECT t.*, u.nama_lengkap as nama_customer, k.nama_lengkap as nama_kasir
          FROM transaksi t
          LEFT JOIN users u ON t.id_customer = u.id
          LEFT JOIN users k ON t.id_kasir = k.id
          WHERE t.id = '$id_transaksi'";
$result = mysqli_query($conn, $query);
$transaksi = mysqli_fetch_assoc($result);

if (!$transaksi) {
    header("Location: dashboard.php");
    exit();
}

// Cek akses
if ($_SESSION['role'] == 'customer' && $transaksi['id_customer'] != $_SESSION['user_id']) {
    header("Location: pesanan.php");
    exit();
}

// Ambil detail transaksi
$query_detail = "SELECT dt.*, b.nama_barang, b.foto, b.tipe_barang
                 FROM detail_transaksi dt
                 JOIN barang b ON dt.id_barang = b.id
                 WHERE dt.id_transaksi = '$id_transaksi'";
$result_detail = mysqli_query($conn, $query_detail);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - Aplikasi Penjualan</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .invoice-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .invoice-header {
            border-bottom: 2px solid #667eea;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .invoice-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        
        .info-box h4 {
            margin-bottom: 10px;
            color: #667eea;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Detail Pesanan</h1>
            <p>Informasi lengkap pesanan</p>
        </div>
        
        <div class="invoice-container">
            <div class="invoice-header">
                <h2 style="color: #667eea;">🛒 Aplikasi Penjualan Barang</h2>
                <h3>Invoice: <?php echo $transaksi['kode_transaksi']; ?></h3>
            </div>
            
            <div class="invoice-info">
                <div class="info-box">
                    <h4>Informasi Transaksi</h4>
                    <p><strong>Tanggal:</strong> <?php echo date('d/m/Y H:i', strtotime($transaksi['tanggal_transaksi'])); ?></p>
                    <p><strong>Status:</strong> 
                        <span class="badge badge-<?php echo $transaksi['status']; ?>">
                            <?php echo ucfirst($transaksi['status']); ?>
                        </span>
                    </p>
                </div>
                
                <div class="info-box">
                    <h4>Informasi Customer</h4>
                    <p><strong>Nama:</strong> <?php echo $transaksi['nama_customer'] ?? 'Guest'; ?></p>
                    <?php if ($transaksi['nama_kasir']): ?>
                        <p><strong>Dilayani oleh:</strong> <?php echo $transaksi['nama_kasir']; ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <h3 style="margin-bottom: 15px;">Detail Barang</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nama Barang</th>
                        <th>Harga Satuan</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($detail = mysqli_fetch_assoc($result_detail)): ?>
                    <tr>
                        <td>
                            <img src="<?php echo $detail['foto'] ? 'uploads/'.$detail['foto'] : 'https://via.placeholder.com/50'; ?>" 
                                 alt="<?php echo $detail['nama_barang']; ?>"
                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                        </td>
                        <td><?php echo $detail['nama_barang']; ?></td>
                        <td><?php echo format_rupiah($detail['harga_satuan']); ?></td>
                        <td><?php echo $detail['jumlah']; ?> <?php echo $detail['tipe_barang']; ?></td>
                        <td><?php echo format_rupiah($detail['subtotal']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <tr style="background: #f8f9fa; font-weight: bold; font-size: 18px;">
                        <td colspan="4" style="text-align: right;">TOTAL:</td>
                        <td><?php echo format_rupiah($transaksi['total_harga']); ?></td>
                    </tr>
                </tbody>
            </table>
            
            <div style="margin-top: 30px; display: flex; gap: 10px;">
                <?php if ($_SESSION['role'] == 'customer'): ?>
                    <a href="pesanan.php" class="btn btn-primary">Kembali</a>
                <?php else: ?>
                    <a href="dashboard.php" class="btn btn-primary">Kembali</a>
                <?php endif; ?>
                
                <button onclick="window.print()" class="btn btn-info">Cetak</button>
            </div>
        </div>
    </div>
    
    <style>
        @media print {
            .navbar, .page-header, .btn { display: none; }
            .container { max-width: 100%; }
        }
    </style>
</body>
</html>
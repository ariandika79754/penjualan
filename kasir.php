<?php
require_once 'config.php';
check_role(['kasir']);

$success = '';
$error = '';

// Proses transaksi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['proses_transaksi'])) {
    $items = $_POST['items'] ?? [];
    $jumlah = $_POST['jumlah'] ?? [];
    
    if (count($items) > 0) {
        $kode_transaksi = generate_kode_transaksi();
        $total_harga = 0;
        
        // Hitung total
        foreach ($items as $index => $id_barang) {
            $qty = $jumlah[$index];
            $query = "SELECT harga FROM barang WHERE id = '$id_barang'";
            $result = mysqli_query($conn, $query);
            $barang = mysqli_fetch_assoc($result);
            $total_harga += $barang['harga'] * $qty;
        }
        
        // Insert transaksi
        $id_kasir = $_SESSION['user_id'];
        $query_transaksi = "INSERT INTO transaksi (kode_transaksi, id_kasir, total_harga, status) 
                           VALUES ('$kode_transaksi', '$id_kasir', '$total_harga', 'selesai')";
        
        if (mysqli_query($conn, $query_transaksi)) {
            $id_transaksi = mysqli_insert_id($conn);
            
            // Insert detail transaksi dan update stok
            foreach ($items as $index => $id_barang) {
                $qty = $jumlah[$index];
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
            
            $success = "Transaksi berhasil! Kode: $kode_transaksi";
        } else {
            $error = "Gagal memproses transaksi!";
        }
    }
}

// Ambil data barang
$query_barang = "SELECT * FROM barang WHERE stok > 0 ORDER BY nama_barang";
$result_barang = mysqli_query($conn, $query_barang);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir - Aplikasi Penjualan</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .kasir-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        
        .item-row {
            display: grid;
            grid-template-columns: 3fr 1fr 1fr 50px;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .total-section {
            background: #667eea;
            color: white;
            padding: 20px;
            border-radius: 10px;
            position: sticky;
            top: 20px;
        }
        
        .total-section h3 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        
        .total-amount {
            font-size: 36px;
            font-weight: bold;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Kasir</h1>
            <p>Proses transaksi penjualan</p>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" id="formKasir">
            <div class="kasir-container">
                <div class="card">
                    <div class="card-header">
                        <h2>Daftar Item</h2>
                        <button type="button" onclick="tambahItem()" class="btn btn-primary btn-sm">+ Tambah Item</button>
                    </div>
                    <div class="card-body" id="itemContainer">
                        <div class="item-row">
                            <select name="items[]" class="form-control item-select" required onchange="updateHarga(this)">
                                <option value="">Pilih Barang</option>
                                <?php while ($barang = mysqli_fetch_assoc($result_barang)): ?>
                                    <option value="<?php echo $barang['id']; ?>" 
                                            data-harga="<?php echo $barang['harga']; ?>"
                                            data-stok="<?php echo $barang['stok']; ?>">
                                        <?php echo $barang['nama_barang']; ?> - <?php echo format_rupiah($barang['harga']); ?> (Stok: <?php echo $barang['stok']; ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <input type="number" name="jumlah[]" class="form-control jumlah-input" 
                                   value="1" min="1" required onchange="hitungTotal()">
                            <input type="text" class="form-control subtotal" readonly placeholder="Subtotal">
                            <button type="button" onclick="hapusItem(this)" class="btn btn-danger btn-sm">×</button>
                        </div>
                    </div>
                </div>
                
                <div class="total-section">
                    <h3>Total Pembayaran</h3>
                    <div class="total-amount" id="totalAmount">Rp 0</div>
                    <button type="submit" name="proses_transaksi" class="btn btn-success" style="width: 100%; font-size: 18px;">
                        Proses Transaksi
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <script>
        function tambahItem() {
            const container = document.getElementById('itemContainer');
            const firstRow = container.querySelector('.item-row');
            const newRow = firstRow.cloneNode(true);
            
            // Reset values
            newRow.querySelector('.item-select').selectedIndex = 0;
            newRow.querySelector('.jumlah-input').value = 1;
            newRow.querySelector('.subtotal').value = '';
            
            container.appendChild(newRow);
        }
        
        function hapusItem(btn) {
            const container = document.getElementById('itemContainer');
            if (container.querySelectorAll('.item-row').length > 1) {
                btn.closest('.item-row').remove();
                hitungTotal();
            }
        }
        
        function updateHarga(select) {
            const row = select.closest('.item-row');
            const option = select.options[select.selectedIndex];
            const harga = parseFloat(option.dataset.harga || 0);
            const jumlah = parseInt(row.querySelector('.jumlah-input').value);
            const subtotal = harga * jumlah;
            
            row.querySelector('.subtotal').value = 'Rp ' + subtotal.toLocaleString('id-ID');
            hitungTotal();
        }
        
        function hitungTotal() {
            let total = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const select = row.querySelector('.item-select');
                const option = select.options[select.selectedIndex];
                const harga = parseFloat(option.dataset.harga || 0);
                const jumlah = parseInt(row.querySelector('.jumlah-input').value);
                const subtotal = harga * jumlah;
                
                row.querySelector('.subtotal').value = 'Rp ' + subtotal.toLocaleString('id-ID');
                total += subtotal;
            });
            
            document.getElementById('totalAmount').textContent = 'Rp ' + total.toLocaleString('id-ID');
        }
        
        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('itemContainer').addEventListener('change', function(e) {
                if (e.target.classList.contains('item-select')) {
                    updateHarga(e.target);
                } else if (e.target.classList.contains('jumlah-input')) {
                    hitungTotal();
                }
            });
        });
    </script>
</body>
</html>
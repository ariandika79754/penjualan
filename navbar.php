<nav class="navbar">
    <div class="nav-container">
        <div class="nav-brand">
            <a href="dashboard.php">🛒 Aplikasi Penjualan</a>
        </div>
        
        <ul class="nav-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <li><a href="barang.php">Data Barang</a></li>
                <li><a href="kategori.php">Kategori</a></li>
                <li><a href="users.php">Data User</a></li>
                <li><a href="laporan.php">Laporan</a></li>
            <?php endif; ?>
            
            <?php if ($_SESSION['role'] == 'kasir'): ?>
                <li><a href="barang.php">Data Barang</a></li>
                <li><a href="kasir.php">Kasir</a></li>
                <li><a href="transaksi.php">Transaksi</a></li>
            <?php endif; ?>
            
            <?php if ($_SESSION['role'] == 'customer'): ?>
                <li><a href="katalog.php">Katalog</a></li>
                <li><a href="keranjang.php">Keranjang</a></li>
                <li><a href="pesanan.php">Pesanan Saya</a></li>
            <?php endif; ?>
            
            <li class="nav-user">
                <span><?php echo $_SESSION['nama_lengkap']; ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </li>
        </ul>
    </div>
</nav>
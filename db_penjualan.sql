-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 02 Okt 2025 pada 10.45
-- Versi server: 10.4.28-MariaDB
-- Versi PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_penjualan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `barang`
--

CREATE TABLE `barang` (
  `id` int(11) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `harga` decimal(10,2) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `tipe_barang` varchar(50) DEFAULT NULL,
  `id_kategori` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `barang`
--

INSERT INTO `barang` (`id`, `nama_barang`, `foto`, `harga`, `stok`, `tipe_barang`, `id_kategori`, `deskripsi`, `created_at`) VALUES
(1, 'Laptop ASUS', 'laptop.jpg', 7500000.00, 10, 'Unit', 1, 'Laptop ASUS Core i5 RAM 8GB', '2025-10-02 07:42:56'),
(2, 'Mouse Wireless', 'mouse.jpg', 150000.00, 50, 'Unit', 1, 'Mouse wireless 2.4GHz', '2025-10-02 07:42:56'),
(3, 'Kemeja Pria', 'kemeja.jpg', 250000.00, 30, 'Pcs', 2, 'Kemeja pria lengan panjang', '2025-10-02 07:42:56'),
(4, 'Snack Coklat', 'snack.jpg', 25000.00, 100, 'Pack', 3, 'Snack coklat import', '2025-10-02 07:42:56'),
(5, 'Mie', '1759392205_foto.png', 100000.00, 83, 'Unit', 3, 'instant', '2025-10-02 08:03:25');

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id` int(11) NOT NULL,
  `id_transaksi` int(11) DEFAULT NULL,
  `id_barang` int(11) DEFAULT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id`, `id_transaksi`, `id_barang`, `jumlah`, `harga_satuan`, `subtotal`) VALUES
(1, 1, 5, 1, 100000.00, 100000.00),
(2, 2, 5, 15, 100000.00, 1500000.00),
(3, 3, 5, 1, 100000.00, 100000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama_kategori` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id`, `nama_kategori`, `deskripsi`) VALUES
(1, 'Elektronik', 'Barang-barang elektronik'),
(2, 'Pakaian', 'Pakaian pria dan wanita'),
(3, 'Makanan', 'Makanan dan minuman'),
(4, 'Alat Tulis', 'Peralatan tulis dan kantor');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `kode_transaksi` varchar(50) NOT NULL,
  `id_customer` int(11) DEFAULT NULL,
  `id_kasir` int(11) DEFAULT NULL,
  `tanggal_transaksi` datetime DEFAULT current_timestamp(),
  `total_harga` decimal(10,2) NOT NULL,
  `status` enum('pending','selesai','batal') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id`, `kode_transaksi`, `id_customer`, `id_kasir`, `tanggal_transaksi`, `total_harga`, `status`) VALUES
(1, 'TRX20251002100503222', 3, NULL, '2025-10-02 15:05:03', 100000.00, 'pending'),
(2, 'TRX20251002100612193', 3, NULL, '2025-10-02 15:06:12', 1500000.00, 'pending'),
(3, 'TRX20251002100807934', NULL, 2, '2025-10-02 15:08:07', 100000.00, 'selesai');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','kasir','customer') NOT NULL,
  `alamat` text DEFAULT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `email`, `role`, `alamat`, `no_telp`, `created_at`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'Administrator', 'admin@toko.com', 'admin', NULL, NULL, '2025-10-02 07:42:56'),
(2, 'kasir1', 'de28f8f7998f23ab4194b51a6029416f', 'Kasir Satu', 'kasir1@toko.com', 'kasir', NULL, NULL, '2025-10-02 07:42:56'),
(3, 'customer1', 'f4ad231214cb99a985dff0f056a36242', 'Customer Satu', 'customer1@email.com', 'customer', NULL, NULL, '2025-10-02 07:42:56');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indeks untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_barang` (`id_barang`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  ADD KEY `id_customer` (`id_customer`),
  ADD KEY `id_kasir` (`id_kasir`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `barang`
--
ALTER TABLE `barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `barang`
--
ALTER TABLE `barang`
  ADD CONSTRAINT `barang_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id`);

--
-- Ketidakleluasaan untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id`),
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id`);

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_customer`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`id_kasir`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

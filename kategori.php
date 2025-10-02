<?php
require_once 'config.php';
check_role(['admin']);

$success = '';
$error = '';

// Hapus kategori
if (isset($_GET['hapus'])) {
    $id = clean_input($_GET['hapus']);
    $query = "DELETE FROM kategori WHERE id = '$id'";
    if (mysqli_query($conn, $query)) {
        $success = "Kategori berhasil dihapus!";
    } else {
        $error = "Gagal menghapus kategori!";
    }
}

// Tambah/Edit kategori
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_kategori = clean_input($_POST['nama_kategori']);
    $deskripsi = clean_input($_POST['deskripsi']);
    
    if (isset($_POST['id']) && $_POST['id']) {
        // Update
        $id = clean_input($_POST['id']);
        $query = "UPDATE kategori SET nama_kategori = '$nama_kategori', deskripsi = '$deskripsi' WHERE id = '$id'";
    } else {
        // Insert
        $query = "INSERT INTO kategori (nama_kategori, deskripsi) VALUES ('$nama_kategori', '$deskripsi')";
    }
    
    if (mysqli_query($conn, $query)) {
        $success = "Kategori berhasil disimpan!";
    } else {
        $error = "Gagal menyimpan kategori!";
    }
}

// Ambil data kategori
$query = "SELECT k.*, COUNT(b.id) as jumlah_barang 
          FROM kategori k 
          LEFT JOIN barang b ON k.id = b.id_kategori 
          GROUP BY k.id 
          ORDER BY k.nama_kategori";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori - Aplikasi Penjualan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Kategori Barang</h1>
            <p>Kelola kategori produk</p>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h2>Daftar Kategori</h2>
                <button onclick="showModal()" class="btn btn-primary">+ Tambah Kategori</button>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th>Jumlah Barang</th>
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
                            <td><?php echo $row['nama_kategori']; ?></td>
                            <td><?php echo $row['deskripsi']; ?></td>
                            <td>
                                <span class="badge badge-selesai"><?php echo $row['jumlah_barang']; ?> item</span>
                            </td>
                            <td>
                                <button onclick='editKategori(<?php echo json_encode($row); ?>)' class="btn btn-info btn-sm">Edit</button>
                                <a href="kategori.php?hapus=<?php echo $row['id']; ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Modal Form Kategori -->
    <div id="kategoriModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Tambah Kategori</h2>
            
            <form method="POST" action="">
                <input type="hidden" name="id" id="kategoriId">
                
                <div class="form-group">
                    <label>Nama Kategori *</label>
                    <input type="text" name="nama_kategori" id="nama_kategori" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3"></textarea>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <button type="button" onclick="closeModal()" class="btn btn-danger">Batal</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function showModal() {
            document.getElementById('kategoriModal').style.display = 'block';
            document.getElementById('modalTitle').textContent = 'Tambah Kategori';
            document.getElementById('kategoriId').value = '';
            document.getElementById('nama_kategori').value = '';
            document.getElementById('deskripsi').value = '';
        }
        
        function editKategori(kategori) {
            document.getElementById('kategoriModal').style.display = 'block';
            document.getElementById('modalTitle').textContent = 'Edit Kategori';
            document.getElementById('kategoriId').value = kategori.id;
            document.getElementById('nama_kategori').value = kategori.nama_kategori;
            document.getElementById('deskripsi').value = kategori.deskripsi || '';
        }
        
        function closeModal() {
            document.getElementById('kategoriModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('kategoriModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
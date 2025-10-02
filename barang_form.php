<?php
require_once 'config.php';
check_role(['admin']);

$success = '';
$error = '';
$edit = false;
$barang = null;

// Cek apakah mode edit
if (isset($_GET['id'])) {
    $edit = true;
    $id = clean_input($_GET['id']);
    $query = "SELECT * FROM barang WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    $barang = mysqli_fetch_assoc($result);
}

// Proses form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_barang = clean_input($_POST['nama_barang']);
    $harga = clean_input($_POST['harga']);
    $stok = clean_input($_POST['stok']);
    $tipe_barang = clean_input($_POST['tipe_barang']);
    $id_kategori = clean_input($_POST['id_kategori']);
    $deskripsi = clean_input($_POST['deskripsi']);
    
    // Upload foto
    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['foto']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            $newname = time() . '_' . $filename;
            $upload_path = 'uploads/' . $newname;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                $foto = $newname;
            }
        }
    }
    
    if ($edit) {
        // Update
        $id = clean_input($_POST['id']);
        if ($foto) {
            $query = "UPDATE barang SET 
                      nama_barang = '$nama_barang',
                      foto = '$foto',
                      harga = '$harga',
                      stok = '$stok',
                      tipe_barang = '$tipe_barang',
                      id_kategori = '$id_kategori',
                      deskripsi = '$deskripsi'
                      WHERE id = '$id'";
        } else {
            $query = "UPDATE barang SET 
                      nama_barang = '$nama_barang',
                      harga = '$harga',
                      stok = '$stok',
                      tipe_barang = '$tipe_barang',
                      id_kategori = '$id_kategori',
                      deskripsi = '$deskripsi'
                      WHERE id = '$id'";
        }
    } else {
        // Insert
        $query = "INSERT INTO barang (nama_barang, foto, harga, stok, tipe_barang, id_kategori, deskripsi) 
                  VALUES ('$nama_barang', '$foto', '$harga', '$stok', '$tipe_barang', '$id_kategori', '$deskripsi')";
    }
    
    if (mysqli_query($conn, $query)) {
        header("Location: barang.php");
        exit();
    } else {
        $error = "Gagal menyimpan data!";
    }
}

// Ambil kategori
$query_kategori = "SELECT * FROM kategori ORDER BY nama_kategori";
$kategori_list = mysqli_query($conn, $query_kategori);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $edit ? 'Edit' : 'Tambah'; ?> Barang - Aplikasi Penjualan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1><?php echo $edit ? 'Edit' : 'Tambah'; ?> Barang</h1>
            <p>Form untuk <?php echo $edit ? 'mengubah' : 'menambah'; ?> data barang</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <form method="POST" action="" enctype="multipart/form-data">
                    <?php if ($edit): ?>
                        <input type="hidden" name="id" value="<?php echo $barang['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Nama Barang *</label>
                        <input type="text" name="nama_barang" class="form-control" 
                               value="<?php echo $barang['nama_barang'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Foto Barang</label>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                        <?php if ($edit && $barang['foto']): ?>
                            <img src="uploads/<?php echo $barang['foto']; ?>" 
                                 style="max-width: 200px; margin-top: 10px;">
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Harga *</label>
                        <input type="number" name="harga" class="form-control" 
                               value="<?php echo $barang['harga'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Stok *</label>
                        <input type="number" name="stok" class="form-control" 
                               value="<?php echo $barang['stok'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Tipe Barang *</label>
                        <input type="text" name="tipe_barang" class="form-control" 
                               value="<?php echo $barang['tipe_barang'] ?? ''; ?>" 
                               placeholder="Unit, Pcs, Pack, dll" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Kategori *</label>
                        <select name="id_kategori" class="form-control" required>
                            <option value="">Pilih Kategori</option>
                            <?php while ($kat = mysqli_fetch_assoc($kategori_list)): ?>
                                <option value="<?php echo $kat['id']; ?>" 
                                    <?php echo ($barang && $barang['id_kategori'] == $kat['id']) ? 'selected' : ''; ?>>
                                    <?php echo $kat['nama_kategori']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="4"><?php echo $barang['deskripsi'] ?? ''; ?></textarea>
                    </div>
                    
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $edit ? 'Update' : 'Simpan'; ?>
                        </button>
                        <a href="barang.php" class="btn btn-danger">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
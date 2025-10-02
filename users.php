<?php
require_once 'config.php';
check_role(['admin']);

$success = '';
$error = '';

// Hapus user
if (isset($_GET['hapus'])) {
    $id = clean_input($_GET['hapus']);
    $query = "DELETE FROM users WHERE id = '$id' AND id != '{$_SESSION['user_id']}'";
    if (mysqli_query($conn, $query)) {
        $success = "User berhasil dihapus!";
    } else {
        $error = "Gagal menghapus user!";
    }
}

// Tambah/Edit user
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean_input($_POST['username']);
    $nama_lengkap = clean_input($_POST['nama_lengkap']);
    $email = clean_input($_POST['email']);
    $role = clean_input($_POST['role']);
    $alamat = clean_input($_POST['alamat']);
    $no_telp = clean_input($_POST['no_telp']);
    
    if (isset($_POST['id']) && $_POST['id']) {
        // Update
        $id = clean_input($_POST['id']);
        $query = "UPDATE users SET 
                  username = '$username',
                  nama_lengkap = '$nama_lengkap',
                  email = '$email',
                  role = '$role',
                  alamat = '$alamat',
                  no_telp = '$no_telp'";
        
        if ($_POST['password']) {
            $password = md5($_POST['password']);
            $query .= ", password = '$password'";
        }
        
        $query .= " WHERE id = '$id'";
    } else {
        // Insert
        $password = md5($_POST['password']);
        $query = "INSERT INTO users (username, password, nama_lengkap, email, role, alamat, no_telp) 
                  VALUES ('$username', '$password', '$nama_lengkap', '$email', '$role', '$alamat', '$no_telp')";
    }
    
    if (mysqli_query($conn, $query)) {
        $success = "User berhasil disimpan!";
    } else {
        $error = "Gagal menyimpan user! " . mysqli_error($conn);
    }
}

// Ambil data user
$query = "SELECT * FROM users ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data User - Aplikasi Penjualan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Data User</h1>
            <p>Kelola pengguna aplikasi</p>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h2>Daftar User</h2>
                <button onclick="showModal()" class="btn btn-primary">+ Tambah User</button>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>No Telp</th>
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
                            <td><?php echo $row['username']; ?></td>
                            <td><?php echo $row['nama_lengkap']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td>
                                <span class="badge badge-<?php echo $row['role'] == 'admin' ? 'selesai' : 'pending'; ?>">
                                    <?php echo ucfirst($row['role']); ?>
                                </span>
                            </td>
                            <td><?php echo $row['no_telp']; ?></td>
                            <td>
                                <button onclick='editUser(<?php echo json_encode($row); ?>)' class="btn btn-info btn-sm">Edit</button>
                                <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                    <a href="users.php?hapus=<?php echo $row['id']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Modal Form User -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Tambah User</h2>
            
            <form method="POST" action="">
                <input type="hidden" name="id" id="userId">
                
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Password <span id="passNote">(Kosongkan jika tidak diubah)</span> *</label>
                    <input type="password" name="password" id="password" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Nama Lengkap *</label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="email" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Role *</label>
                    <select name="role" id="role" class="form-control" required>
                        <option value="admin">Admin</option>
                        <option value="kasir">Kasir</option>
                        <option value="customer">Customer</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="alamat" id="alamat" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>No Telp</label>
                    <input type="text" name="no_telp" id="no_telp" class="form-control">
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
            document.getElementById('userModal').style.display = 'block';
            document.getElementById('modalTitle').textContent = 'Tambah User';
            document.getElementById('userId').value = '';
            document.getElementById('username').value = '';
            document.getElementById('password').value = '';
            document.getElementById('password').required = true;
            document.getElementById('passNote').style.display = 'none';
            document.getElementById('nama_lengkap').value = '';
            document.getElementById('email').value = '';
            document.getElementById('role').value = 'customer';
            document.getElementById('alamat').value = '';
            document.getElementById('no_telp').value = '';
        }
        
        function editUser(user) {
            document.getElementById('userModal').style.display = 'block';
            document.getElementById('modalTitle').textContent = 'Edit User';
            document.getElementById('userId').value = user.id;
            document.getElementById('username').value = user.username;
            document.getElementById('password').value = '';
            document.getElementById('password').required = false;
            document.getElementById('passNote').style.display = 'inline';
            document.getElementById('nama_lengkap').value = user.nama_lengkap;
            document.getElementById('email').value = user.email || '';
            document.getElementById('role').value = user.role;
            document.getElementById('alamat').value = user.alamat || '';
            document.getElementById('no_telp').value = user.no_telp || '';
        }
        
        function closeModal() {
            document.getElementById('userModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('userModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
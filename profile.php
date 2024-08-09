<?php
// Mulai session
session_start();

// Verifikasi apakah pengguna telah login
if (!isset($_SESSION['session_username'])) {
    header('Location: login.php');
    exit;
}

// Ambil nama pengguna dan role dari session
$username = $_SESSION['session_username'];
$role = $_SESSION['session_role'];

// Koneksi ke database
$servername = "localhost";
$db_username = "root";
$password = "";
$dbname = "form_login";

// Buat koneksi
$conn = new mysqli($servername, $db_username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Proses pembaruan username jika formulir dikirim
if (isset($_POST['update_username'])) {
    $user_id = $_POST['user_id'];
    $new_username = $_POST['new_username'];
    $role_id = $_POST['role_id'];

    // Periksa apakah username baru sudah ada
    $sql = "SELECT * FROM users WHERE username = ? AND id != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_username, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Update username dan role
        $sql = "UPDATE users SET username = ?, role_id = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $new_username, $role_id, $user_id);

        if ($stmt->execute()) {
            // Berhasil
            $_SESSION['update_success'] = "Username berhasil diperbarui!";
        } else {
            // Gagal
            $_SESSION['update_error'] = "Terjadi kesalahan saat memperbarui username.";
        }
    } else {
        // Username baru sudah ada
        $_SESSION['update_error'] = "Username baru sudah digunakan. Silakan pilih username lain.";
    }

    // Redirect ke profil
    header('Location: profile.php');
    exit;
}

// Proses pembaruan password jika formulir dikirim
if (isset($_POST['update_password'])) {
    $user_id = $_POST['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi konfirmasi password
    if ($new_password !== $confirm_password) {
        $_SESSION['update_error'] = "Konfirmasi password baru tidak sesuai.";
        header('Location: settings.php');
        exit;
    }

    // Periksa password saat ini
    $sql = "SELECT password FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (password_verify($current_password, $user['password'])) {
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $hashed_password, $user_id);

        if ($stmt->execute()) {
            // Berhasil
            $_SESSION['update_success'] = "Password berhasil diperbarui!";
        } else {
            // Gagal
            $_SESSION['update_error'] = "Terjadi kesalahan saat memperbarui password.";
        }
    } else {
        // Password saat ini tidak valid
        $_SESSION['update_error'] = "Password saat ini salah.";
    }

    // Redirect ke profil
    header('Location: settings.php');
    exit;
}

// Ambil data pengguna
$sql = "SELECT users.id, users.username, roles.role_name, roles.id AS role_id FROM users JOIN roles ON users.role_id = roles.id";
$result = $conn->query($sql);

// Ambil data roles untuk dropdown
$role_sql = "SELECT * FROM roles";
$role_result = $conn->query($role_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Daftar Pengguna - Sistem Informasi Administrasi Biro Pemerintahan Otonomi Daerah Provinsi Sulawesi Utara</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="img/provsulut.png" type="image/x-icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        
        .sidebar { background-color: #E50000; }
        .sidebar .nav-item .nav-link { color: white; }
        .sidebar .nav-item .nav-link:hover { color: #ffcccc; }
        .sidebar .nav-item .nav-link .fas { color: white; }
        .logout-btn { color: #ff6666; font-weight: bold; }
        .logout-btn:hover { color: red; text-decoration: none; }
        .logout-btn .fas { color: #FF8000; }
        .logout-btn:hover .fas { color: #ff3333; }
         /* Tambahkan style khusus untuk tombol Edit */


    .btn-warning:hover {
        background-color: #cb0000; /* Warna kuning gelap saat hover */
        color: #fff; /* Teks putih saat hover */
        text-decoration: none; /* Menghapus underline pada hover */
    }

    .btn-warning:focus, .btn-warning:active {
        box-shadow: none; /* Menghapus efek box-shadow saat tombol diklik */
    }

    /* Style khusus untuk modal */
    .modal-content {
        background-color: #cb0000;
        color: white;
        border-radius: 0.5rem; /* Sudut membulat untuk modal */
    }
      /* Style khusus untuk modal */
      .modal-content .btn-primary {
        background-color: white;
        color: black;
        border-radius: none; /* Sudut membulat untuk modal */
    }

     /* Style khusus untuk modal */
     .modal-content .btn-primary:hover{
        background-color: blue;
        color: white;
        border-radius: none; /* Sudut membulat untuk modal */
    }

    .modal-header {
        background-color: #cb0000; /* Warna header modal */
        color: white; /* Teks putih di header modal */
    }

 
    </style>
</head>
<body id="page-top">
    
    <!-- Page Wrapper -->
<div id="wrapper">

<!-- Sidebar -->
<ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard.php">
        <div class="sidebar-brand-icon">
            <img src="img/provsulut.png" alt="Logo" style="width: 30px; height: 30px;">
        </div>
        <div class="sidebar-brand-text mx-3">ADMINISTRASI</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item">
        <a class="nav-link" href="dashboard.php">
            <i class="fas fa-fw fa-home"></i>
            <span>Beranda</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Menu Utama 
    </div>

    <!-- Nav Item - Surat Masuk -->
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
        aria-expanded="true" aria-controls="collapseTwo">
        <i class="fas fa-fw fa-envelope"></i>
        <span>Surat Masuk</span>
    </a>
    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <!-- Link Input Surat Masuk hanya jika admin -->
            <?php if ($role === 'admin'): ?>
                <a class="collapse-item" href="suratmasuk.php">
                    <i class="fa fa-plus-circle"></i>
                    Input Surat Masuk
                </a>
            <?php endif; ?>
            <a class="collapse-item" href="arsipsuratmasuk.php">
                <i class="fa fa-archive"></i>
                Arsip Surat Masuk
            </a>
        </div>
    </div>
</li>

<!-- Nav Item - Surat Keluar -->
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
        aria-expanded="true" aria-controls="collapseUtilities">
        <i class="fas fa-fw fa-paper-plane"></i>
        <span>Surat Keluar</span>
    </a>
    <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities"
        data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <!-- Link Input Surat Keluar hanya jika admin -->
            <?php if ($role === 'admin'): ?>
                <a class="collapse-item" href="suratkeluar.php">
                    <i class="fa fa-plus-circle"></i>
                    Input Surat Keluar
                </a>
            <?php endif; ?>
            <a class="collapse-item" href="arsipsuratkeluar.php">
                <i class="fa fa-archive"></i>
                Arsip Surat Keluar
            </a>
            </div>
            </div>
        </li>

        <!-- Nav Item - Pages Collapse Menu -->
        <li class="nav-item">
            <a class="nav-link" href="datapegawai.php">
                <i class="fas fa-fw fa-user"></i>
                <span>Data Pegawai</span>
            </a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- Nav Item - Pengaturan Collapse Menu -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePengaturan"
                aria-expanded="true" aria-controls="collapsePengaturan">
                <i class="fas fa-fw fa-cogs"></i>
                <span class="ml-2">Pengaturan</span>
            </a>
            <div id="collapsePengaturan" class="collapse" aria-labelledby="headingPengaturan" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item d-flex align-items-center" href="profile.php">
                <i class="fa fa-address-card nav-icon"></i>
                <span class="ml-2">Daftar Pengguna</span>
            </a>
                    <a class="collapse-item d-flex align-items-center" href="settings.php">
                        <i class="fa fa-key nav-icon"></i>
                        <span class="ml-2">Ubah Password</span>
                    </a>
                    <a class="collapse-item d-flex align-items-center" href="logout.php" onclick="return confirm('Apakah Anda yakin ingin Keluar dari E-Arsip?')">
    <i class="fa fa-sign-out-alt nav-icon"></i>
    <span class="ml-2">Logout</span>
</a>


                </div>
            </div>
        </li>

        

          
            <hr class="sidebar-divider d-none d-md-block">
            <!-- Sidebar Toggler (Sidebar) -->
<div class="text-center d-none d-md-inline">
    <button class="rounded-circle border-0" id="sidebarToggle"></button>
</div>
        </ul>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-envelope fa-fw"></i><span class="badge badge-danger badge-counter">7</span>
                            </a>
                            
                        </li>
                        <li class="nav-item">
                    <a class="nav-link logout-btn" href="logout.php">
                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2"></i>
                    </a>
                </li>
                        
                    </ul>
                </nav>
                <div class="container-fluid">
                    <?php if (isset($_SESSION['update_success'])): ?>
                        <div class="alert alert-success"><?php echo $_SESSION['update_success']; unset($_SESSION['update_success']); ?></div>
                    <?php elseif (isset($_SESSION['update_error'])): ?>
                        <div class="alert alert-danger"><?php echo $_SESSION['update_error']; unset($_SESSION['update_error']); ?></div>
                    <?php endif; ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Daftar Pengguna E-ARSIP :</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Username</th>
                                            <th>Role</th>
                                            <?php if ($role === 'admin'): ?>
                                                <th>Aksi</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                <td><?php echo htmlspecialchars($row['role_name']); ?></td>
                                                <?php if ($role === 'admin'): ?>
                                                    <td>
                                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editUserModal" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-username="<?php echo htmlspecialchars($row['username']); ?>" data-role="<?php echo htmlspecialchars($row['role_id']); ?>">Edit</button>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit Pengguna :</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm" method="post" action="">
                        <input type="hidden" name="user_id" id="user_id">
                        <div class="form-group">
                            <label for="new_username">Username Baru :</label>
                            <input type="text" class="form-control" name="new_username" id="new_username" required>
                        </div>
                        <div class="form-group">
                            <label for="role_id">Role :</label>
                            <select class="form-control" name="role_id" id="role_id" required>
                                <?php while ($role_row = $role_result->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($role_row['id']); ?>"><?php echo htmlspecialchars($role_row['role_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button type="submit" name="update_username" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    
   <!-- Scroll to Top Button-->
   <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
                                    

        <!-- Bootstrap core JavaScript-->
        <script src="vendor/jquery/jquery.min.js"></script>
        <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

        <!-- Core plugin JavaScript-->
        <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

        <!-- Custom scripts for all pages-->
        <script src="js/sb-admin-2.min.js"></script>

        <!-- Page level plugins -->
        <script src="vendor/chart.js/Chart.min.js"></script>

        <!-- Page level custom scripts -->
        <script src="js/demo/chart-area-demo.js"></script>
        <script src="js/demo/chart-pie-demo.js"></script>
        

    
    <script>
        $('#editUserModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var userId = button.data('id');
            var username = button.data('username');
            var roleId = button.data('role');

            var modal = $(this);
            modal.find('#user_id').val(userId);
            modal.find('#new_username').val(username);
            modal.find('#role_id').val(roleId);
        });
    </script>

    
</body>
</html>

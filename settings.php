<?php
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['session_username'])) {
    header("Location: login.php");
    exit();
}

  // Ambil nama pengguna dan role dari session
  $username = $_SESSION['session_username'];
  $role = $_SESSION['session_role'];

// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "form_login";

$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Proses ubah password
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $username = $_SESSION['session_username'];

    // Query untuk mengambil password saat ini dari database
    $sql = "SELECT password FROM users   WHERE username='$username'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if ($row['password'] == $current_password) {
        if ($new_password == $confirm_password) {
            // Update password baru di database
            $sql = "UPDATE users SET password='$new_password' WHERE username='$username'";
            if ($conn->query($sql) === TRUE) {
                echo "<script>alert('Password berhasil diubah.'); window.location.href = 'login.php';</script>";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            echo "<script>alert('Konfirmasi password baru tidak cocok.'); window.location.href = 'settings.php';</script>";
        }
    } else {
        echo "<script>alert('Password saat ini salah.'); window.location.href = 'settings.php';</script>";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Beranda - Sistem Informasi Administrasi Biro Pemerintahan Otonomi Daerah Provinsi Sulawesi Utara</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Favicon -->
    <link rel="icon" href="img/provsulut.png" type="image/x-icon">
    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        /* Sidebar styles */
        .sidebar {
            background-color: #E50000;
        }
        .sidebar .nav-item .nav-link {
            color: white;
        }
        .sidebar .nav-item .nav-link:hover {
            color: #ffcccc;
        }
        .sidebar .nav-item .nav-link .fas {
            color: white;
        }
        
/* Logout button styles */
.logout-btn {
    color: #ff6666;
    font-weight: bold;
}

.logout-btn:hover {
    color: red;
    text-decoration: none;
}

.logout-btn .fas {
    color: #FF8000;
}

.logout-btn:hover .fas {
    color: #ff3333;
}

        /* General text styles */
        .text-gray-600 {
            font-family: 'Arial', sans-serif;
            color: #007bff;
            font-weight: bold;
            font-size: 1.5rem;
            text-align: center;
        }

        /* Navbar styles */
        .navbar {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .navbar p {
            margin: 0;
            padding: 20px 0;
            font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
            font-size: 30px;
            color: #333;
        }

        /* Header and table styles */
        h3, h2 {
            text-align: center;
            margin-top: 40px;
            margin-bottom: 20px;
            font-size: 20px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        table th {
            background-color: #5f9ea0; /* Cadet Blue */
            color: #ffffff;
        }
        table td {
            background-color: #f8f9fc; /* Light gray */
            color: #333333;
        }
        table a {
            text-decoration: none;
            color: #007bff; /* Blue color */
        }
        table a:hover {
            text-decoration: underline;
        }

        /* Header title styles */
        .header-title {
            font-family: 'Nunito', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            text-align: center;
            margin-bottom: 2rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        /* Form container styles */
        .form-container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        .form-group label {
            font-weight: bold;
            color: #333;
        }
        .form-group input {
            width: 100%;
        }

        /* Form table styles */
        .table-form {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .table-form td {
            padding: 15px;
            vertical-align: middle;
        }
        .table-form label {
            font-weight: bold;
            color: #333;
        }

        /* Button styles */
        .btn-primary {
            background-color: #E50000;
            border-color: white;
        }
        .btn-primary:hover {
            background-color: red;
            border-color: white;
        }
        .btn-secondary {
            background-color: #E50000;
            border-color: white;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: white;
        }
        .form-buttons {
            margin-top: 20px;
            text-align: right;
        }
        .form-buttons .btn {
            margin-left: 10px;
        }

        /* Optional: Customize checkbox */
        #showPassword {
            margin-right: 10px;
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

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <div class="row">
                        <div class="col-lg-6 mx-auto">
                            <div class="form-container">
                                <form method="POST" action="settings.php">
                                    <table class="table-form">
                                        <tr>
                                            <td><label for="current_password">Password Saat Ini:</label></td>
                                            <td><input type="password" id="current_password" name="current_password" class="form-control" required></td>
                                        </tr>
                                        <tr>
                                            <td><label for="new_password">Password Baru:</label></td>
                                            <td><input type="password" id="new_password" name="new_password" class="form-control" required></td>
                                        </tr>
                                        <tr>
                                            <td><label for="confirm_password">Konfirmasi Password Baru:</label></td>
                                            <td><input type="password" id="confirm_password" name="confirm_password" class="form-control" required></td>
                                        </tr>
                                    </table>
                                    <div class="form-buttons">
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                        <a href="dashboard.php" class="btn btn-secondary">Batal</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Include jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Start session
session_start();

// Verifikasi apakah pengguna telah login
if (!isset($_SESSION['session_username'])) {
    header('Location: login.php');
    exit;
}

// Ambil nama pengguna dan role dari session
$username = $_SESSION['session_username'];
$role = $_SESSION['session_role']; // Role pengguna: admin atau user

$servername = "localhost";
$db_username = "root";
$password = "";
$dbname = "sistem_informasi";

// Buat koneksi
$conn = new mysqli($servername, $db_username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Handle search
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

$sql = "SELECT * FROM surat_keluar WHERE nomor_surat LIKE '%$search%' OR perihal LIKE '%$search%'";
$result = $conn->query($sql);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $no = $_POST['no'];
    $nomor_surat = $_POST['nomor_surat'];
    $tanggal_surat = $_POST['tanggal_surat'];
    $perihal = $_POST['perihal'];
    $tujuan = $_POST['tujuan'];

    // Validate the file upload
    $berkas = '';
    if (isset($_FILES['berkas']) && $_FILES['berkas']['error'] == 0) {
        $allowed_types = array('jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xlsx');
        $file_extension = pathinfo($_FILES['berkas']['name'], PATHINFO_EXTENSION);

        if (in_array($file_extension, $allowed_types)) {
            $berkas = 'uploads/' . basename($_FILES['berkas']['name']);
            if (!move_uploaded_file($_FILES['berkas']['tmp_name'], $berkas)) {
                die("Error uploading file.");
            }
        } else {
            die("Invalid file type.");
        }
    }

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = $_POST['id'];
        $sql = $conn->prepare("UPDATE surat_keluar SET no=?, nomor_surat=?, tanggal_surat=?, perihal=?, berkas=?, tujuan=? WHERE id=?");
        $sql->bind_param("isssssi", $no, $nomor_surat, $tanggal_surat, $perihal, $berkas, $tujuan, $id);
    } else {
        $sql = $conn->prepare("INSERT INTO surat_keluar (no, nomor_surat, tanggal_surat, perihal, berkas, tujuan) VALUES (?, ?, ?, ?, ?, ?)");
        $sql->bind_param("isssss", $no, $nomor_surat, $tanggal_surat, $perihal, $berkas, $tujuan);
    }

    if ($sql->execute()) {
        // Data berhasil disimpan, redirect dan tampilkan alert
        $_SESSION['message'] = "Surat Telah Di Input";
        echo '<script>alert("Surat Keluar Telah Di Input, Terima Kasih."); window.location.href = "arsipsuratkeluar.php";</script>';
        exit();
    } else {
        echo "Error: " . $sql->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Input Surat Keluar - Biro Pemerintahan Otonomi Daerah Provinsi Sulawesi Utara</title>
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
            background-color: #5f9ea0;
            color: #ffffff;
        }

        table td {
            background-color: #f8f9fc;
            color: #333333;
        }

        table a {
            text-decoration: none;
            color: #007bff;
        }

        table a:hover {
            text-decoration: underline;
        }

        .header-title {
            font-family: 'Nunito', sans-serif;
            font-size: 30px;
            font-weight: 700;
            color: #333;
            text-align: center;
            margin-bottom: 2rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

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

        .btn-primary {
            background-color: #BF6060;
            border-color: white;
        }

        .btn-primary:hover {
            background-color: #E50000;;
            border-color: white;
        }

        .btn-danger {
            background-color: #BF6060;
            border-color: white;
        }

        .btn-danger:hover {
            background-color: #E50000;;
            border-color: white;
        }

        .footer {
            text-align: center;
            padding: 20px 0;
            background-color: #f8f9fc;
            color: #333;
            position: fixed;
            width: 100%;
            bottom: 0;
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


        <!-- Divider -->
        <hr class="sidebar-divider d-none d-md-block">

        <!-- Sidebar Toggler (Sidebar) -->
        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>
    </ul>
    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

        <!-- Main Content -->
        <div id="content">

           

            </nav>
            <!-- End of Topbar -->

            <!-- Begin Page Content -->
            <div class="container-fluid">

                <!-- Page Heading -->
                <br><br>

                <!-- Form Input Surat Keluar -->
                <div class="form-container">
                    <form action="suratkeluar.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="no">No :</label>
                            <input type="text" class="form-control" id="no" name="no" required>
                        </div>
                        <div class="form-group">
                            <label for="nomor_surat">Nomor Surat :</label>
                            <input type="text" class="form-control" id="nomor_surat" name="nomor_surat" required>
                        </div>
                        <div class="form-group">
                            <label for="tanggal_surat">Tanggal Surat :</label>
                            <input type="date" class="form-control" id="tanggal_surat" name="tanggal_surat" required>
                        </div>
                        <div class="form-group">
                            <label for="perihal">Perihal :</label>
                            <input type="text" class="form-control" id="perihal" name="perihal" required>
                        </div>
                        <div class="form-group">
                            <label for="tujuan">Tujuan :</label>
                            <input type="text" class="form-control" id="tujuan" name="tujuan" required>
                        </div>
                        <div class="form-group">
                                <label for="berkas">Upload Berkas : </label>
                                <input type="file" class="form-control-file" id="berkas" name="berkas">
                                <small>Diperbolehkan upload file pdf, docx, jpg, png, jpeg </small>
                            </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="reset" class="btn btn-danger">Reset</button>
                    </form>
                </div>

                

            </div>
            <!-- /.container-fluid -->

        </div>
        <!-- End of Main Content -->

     
    </div>
    <!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<!-- Bootstrap core JavaScript-->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Custom scripts for all pages-->
<script src="js/sb-admin-2.min.js"></script>

</body>
</html>

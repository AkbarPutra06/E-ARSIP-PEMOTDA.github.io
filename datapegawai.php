<?php
// Start session
session_start();

// Verifikasi apakah pengguna telah login
if (!isset($_SESSION['session_username'])) {
    header('Location: login.php');
    exit;
}

// Assuming 'user_role' is set in session when the user logs in
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'admin'; // Default to 'user';

// Ambil nama pengguna dan role dari session
$username = $_SESSION['session_username'];
$role = $_SESSION['session_role'];

// Koneksi ke database
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "sistem_informasi";

// Buat koneksi
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Memproses data dari form jika form disubmit (untuk update atau tambah data)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama'];
    $golongan = $_POST['golongan'];
    $jabatan = $_POST['jabatan'];
    $status = $_POST['status'];

    if (isset($_POST['update'])) {
        $id = $_POST['id'];

        // Query untuk UPDATE data ke dalam database
        $query = "UPDATE pegawai SET nama='$nama', golongan='$golongan', jabatan='$jabatan', status='$status' WHERE id=$id";
    } else {
        // Menentukan ID selanjutnya (memulai dari 1)
        $result = $conn->query("SELECT MAX(id) AS max_id FROM pegawai");
        $row = $result->fetch_assoc();
        $next_id = $row['max_id'] + 1;

        // Query untuk menyimpan data ke dalam database
        $query = "INSERT INTO pegawai (id, nama, golongan, jabatan, status) VALUES ('$next_id', '$nama', '$golongan', '$jabatan', '$status')";
    }

    if ($conn->query($query) === TRUE) {
        echo "<script>alert('Data pegawai berhasil diperbarui.'); window.location.href='datapegawai.php';</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
}

// Menghapus data pegawai
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];

    // Query untuk menghapus data berdasarkan ID
    $delete_query = "DELETE FROM pegawai WHERE id = $delete_id";

    if ($conn->query($delete_query) === TRUE) {
        // Query untuk mengatur ulang nomor urutan (ID)
        $reset_query = "SET @num := 0; UPDATE pegawai SET id = @num := (@num+1); ALTER TABLE pegawai AUTO_INCREMENT = 1";
        if ($conn->multi_query($reset_query) === TRUE) {
            // Pesan sukses jika berhasil
            echo "<script>alert('Data berhasil dihapus, Terimakasih:)'); window.location.href = 'datapegawai.php';</script>";
        } else {
            // Pesan error jika ada masalah dalam mengatur ulang nomor urutan
            echo "<script>alert('Error saat mengatur ulang nomor urutan: " . $conn->error . "');</script>";
        }
    } else {
        // Pesan error jika gagal menghapus data
        echo "<script>alert('Error saat menghapus data: " . $conn->error . "');</script>";
    }
}

// Memproses pencarian jika form pencarian disubmit
$search_query = "";
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])) {
    $search_query = $_GET['search'];
}

// Pagination setup
$results_per_page = 10   ; // Number of records per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$start_from = ($page - 1) * $results_per_page;
?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="">
        <meta name="author" content="">
        <title>Daftar Pegawai ASN dan THL Biro Pemerintahan Otonomi Daerah Provinsi Sulawesi Utara</title>
        <!-- Favicon -->
        <link rel="icon" href="img/provsulut.png" type="image/x-icon">
        <!-- Include Bootstrap CSS -->
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
        <!-- Custom fonts for this template-->
        <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
        <!-- Custom styles for this template-->
        <link href="css/sb-admin-2.min.css" rel="stylesheet">
        <style>
        /* Table Container */
    .table-container {
        margin-top: 50px;
    }

    /* Table Styles */
    table {
        width: 100%;
        border-collapse: collapse;
        background-color: #ffffff;
    }

    table th, table td {
        border: 1px solid #dddddd;
        padding: 12px;
        text-align: center;
        vertical-align: middle;
    }

    table th {
        background-color: #f2f2f2;
        color: #333333;
    }

    table tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    table tr:hover {
        background-color: #f1f1f1;
    }

    /* Heading Styles */
    h1 {
        margin-bottom: 30px;
    }

    /* Form Inline */
    .form-inline {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }

    .form-inline input {
        width: 300px;
        margin-right: 10px;
    }

    /* Sidebar Styles */
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

    /* Custom Heading */
    .custom-heading {
        color: #2c3e50;
        font-weight: bold;
        text-align: center;
    }

    /* Pagination Styles */
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }

    .pagination a {
        margin: 0 5px;
        padding: 10px 15px;
        border: 1px solid #ddd;
        color: #333;
        text-decoration: none;
        transition: background-color 0.3s;
    }

    .pagination a.active, .pagination a:hover {
        background-color: red;
        color: white;
    }

    /* Button Danger Styles */
    .btn-danger {
        background-color: #dc3545; /* Warna latar belakang tombol */
        border-color: #dc3545; /* Warna border tombol */
        color: white; /* Warna teks tombol */
    }

    .btn-danger:hover {
        background-color: #c82333; /* Warna latar belakang tombol saat hover */
        border-color: #bd2130; /* Warna border tombol saat hover */
        color: white; /* Warna teks tombol saat hover */
        text-decoration: none; /* Hapus garis bawah saat hover */
    }

    .btn-danger:focus, .btn-danger:active {
        box-shadow: none; /* Hapus bayangan fokus atau aktif */
        outline: none; /* Hapus outline fokus */
    }

  /* Style for the container that holds the table and buttons */
.table-container {
    position: relative;
    margin-bottom: 20px; /* Space below the table */
}

/* Style for the button container */
.button-container {
    position: absolute;
    bottom: 0;
    right: 0;
    display: flex;
    gap: 10px; /* Space between buttons */
    padding: 10px;
    background-color: #fff; /* Background color for better visibility */
}



/* Print Styles */
@media print {
    .no-print {
        display: none;
    }

    body * {
        visibility: hidden;
    }

    .print-container, .print-container * {
        visibility: visible;
    }

    .print-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: auto;
    }

    .print-container .button-container,
    .print-container .action-column {
        display: none;
    }
}
.btn-warning:hover {
        background-color: #cb0000; /* Warna kuning gelap saat hover */
        color: #fff; /* Teks putih saat hover */
        text-decoration: none; /* Menghapus underline pada hover */
    }

    .btn-warning:focus, .btn-warning:active {
        background-color: red;
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
        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
            <!-- Sidebar Toggle (Topbar) -->
             
            


            <!-- Topbar Navbar -->
            <ul class="navbar-nav ml-auto">
                <!-- Nav Item - Logout -->
                <li class="nav-item">
                    <a class="nav-link logout-btn" href="logout.php">
                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2"></i>
                    </a>
                </li>
                
            </ul>
            
        </nav>
        <!-- End of Topbar -->

            <!-- Begin Page Content -->
            <div class="container-fluid">

                <!-- Page Heading -->
                <br><h1 class="h3 mb-4 text-gray-800 custom-heading">DAFTAR ASN DAN THL BIRO PEMERINTAHAN DAN OTONOMI DAERAH</h1>

                <!-- Form Pencarian -->
                <form class="form-inline d-flex justify-content-center mb-4" action="datapegawai.php" method="GET">
                    <input class="form-control mr-sm-2" type="search" placeholder="Cari Nama / Jabatan" name="search" aria-label="Search" value="<?php echo htmlspecialchars($search_query); ?>">
                    <button class="btn btn-danger my-2 my-sm-0" type="submit">Cari</button>
                </form>
                



<table>
    <!-- Table contents here -->
     
</table>

<div class="table-container print-container">
        <div class="button-container no-print">
           
                <a href="datapegawai_pdf.php" class="btn btn-primary" target="_blank">
                    <i class="fas fa-download"></i> PDF
                </a>
                <?php if ($role === 'admin'): ?>
                <button class="btn btn-success" data-toggle="modal" data-target="#tambahPegawaiModal"><i class="fas fa-plus"></i> Tambah Pegawai </button>
            <?php endif; ?>
        </div>
    </div>



<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Pegawai</th>
                <th>Golongan</th>
                <th>Jabatan</th>
                <th>Status</th>
                <?php if ($role === 'admin'): ?>
                    <th class="action-column">Aksi</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
        <?php
        // Query untuk mengambil data pegawai dengan pagination dan pencarian
        $query = "SELECT * FROM pegawai";
        if ($search_query != "") {
            $query .= " WHERE nama LIKE '%$search_query%' OR jabatan LIKE '%$search_query%'";
        }
        $query .= " LIMIT $start_from, $results_per_page";
        $result = $conn->query($query);

        // Tampilkan data pegawai
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . $row['id'] . "</td>
                        <td>" . htmlspecialchars($row['nama']) . "</td>
                        <td>" . htmlspecialchars($row['golongan']) . "</td>
                        <td>" . htmlspecialchars($row['jabatan']) . "</td>
                        <td>" . htmlspecialchars($row['status']) . "</td>";
                
                if ($role === 'admin') {
                    // Show 'Edit' and 'Delete' buttons for admins
                    echo "<td class='action-column no-print'>
                            <button class='btn btn-primary btn-sm' onclick=\"openEditModal('" . $row['id'] . "', '" . htmlspecialchars($row['nama']) . "', '" . htmlspecialchars($row['golongan']) . "', '" . htmlspecialchars($row['jabatan']) . "', '" . htmlspecialchars($row['status']) . "')\"><i class='fas fa-edit'></i> </button>
                            <a href='datapegawai.php?delete=" . $row['id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Apakah Anda yakin ingin menghapus data ini?\")'><i class='fas fa-trash'></i></a>
                          </td>";
                }
                echo "</tr>";
            }
        } else {
            // Adjust colspan based on the presence of action column
            $colspan = ($role === 'admin') ? '6' : '5';
            echo "<tr><td colspan='$colspan'>Tidak ada data Pegawai.</td></tr>";
        }

// Query untuk menghitung total data
$total_query = "SELECT COUNT(*) FROM pegawai";
if ($search_query != "") {
    $total_query .= " WHERE nama LIKE '%$search_query%' OR jabatan LIKE '%$search_query%'";
}
$total_result = $conn->query($total_query);
$total_rows = $total_result->fetch_array()[0];
$total_pages = ceil($total_rows / $results_per_page);
?>
                </tbody>
            </table>
        </div>
        

        

        <!-- Pagination -->
        <div class="pagination no-print">
            <?php
            for ($i = 1; $i <= $total_pages; $i++) {
                $active = ($i == $page) ? 'active' : '';
                echo "<a href='datapegawai.php?page=$i&search=$search_query' class='$active'>$i</a>";
            }
            ?>
        </div>

                    </div>
                    <!-- /.container-fluid -->
                </div>
                <!-- End of Main Content -->
                <script>
      function printTable() {
    // Hide action column and pagination
    var actionColumns = document.querySelectorAll('.action-column');
    var pagination = document.querySelector('.pagination');

    actionColumns.forEach(function(column) {
        column.style.display = 'none';
    });
    if (pagination) {
        pagination.style.display = 'none';
    }
    // Print the table
    window.print();
};

</script>

    </script>

    <!-- Modal Edit -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel"><b>Data Pegawai :</b></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editForm" method="POST" action="datapegawai.php">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editId">
                        <div class="form-group">
                            <label for="editNama">Nama Pegawai</label>
                            <input type="text" class="form-control" id="editNama" name="nama" required>
                        </div>
                        <div class="form-group">
                            <label for="editGolongan">Golongan</label>
                            <input type="text" class="form-control" id="editGolongan" name="golongan" required>
                        </div>
                        <div class="form-group">
                            <label for="editJabatan">Jabatan</label>
                            <input type="text" class="form-control" id="editJabatan" name="jabatan" required>
                        </div>
                        <div class="form-group">
                            <label for="editStatus">Status</label>
                            <select class="form-control" id="editStatus" name="status" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Non-Aktif">Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" name="update">Simpan Perubahan</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
            // Function to show the edit modal with data
            function openEditModal(id, nama, golongan, jabatan, status) {
                document.getElementById('editId').value = id;
                document.getElementById('editNama').value = nama;
                document.getElementById('editGolongan').value = golongan;
                document.getElementById('editJabatan').value = jabatan;
                document.getElementById('editStatus').value = status;
                $('#editModal').modal('show');
            }
            </script>

    <!-- Body Content -->
    <?php if (isset($alert_message)): ?>
        <div class="alert alert-<?= $alert_type; ?> alert-dismissible fade show" role="alert">
            <?= $alert_message; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Modal -->
    <div class="modal fade" id="tambahPegawaiModal" tabindex="-1" role="dialog" aria-labelledby="tambahPegawaiModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahPegawaiModalLabel"><b>Tambah Pegawai :</b></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="pegawaiForm" action="datapegawai.php" method="post">
                        <div class="form-group">
                            <label for="nama">Nama Pegawai:</label>
                            <input type="text" id="nama" name="nama" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="golongan">Golongan:</label>
                            <input type="text" id="golongan" name="golongan" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="jabatan">Jabatan:</label>
                            <input type="text" id="jabatan" name="jabatan" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Status:</label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Non-Aktif">Non-Aktif</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- End of Content Wrapper -->
        <!-- Footer -->
<br><br><footer class="footer mt-auto py-3 bg-light">
    <div class="container text-center">
        <span class="text-muted">&copy; 2024 E-ARSIP PEMOTDA SULUT</span>
    </div>
</footer>

    </div>
    
        </div>
        
                    <!-- /.container-fluid -->

                </div>
                <!-- End of Main Content -->
                 

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
    </body>
    </html>

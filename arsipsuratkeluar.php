<?php
// Mulai sesi
session_start();

// Verifikasi apakah pengguna telah login
if (!isset($_SESSION['session_username'])) {
    header('Location: login.php');
    exit;
}

// Assuming 'user_role' is set in session when the user logs in
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'admin'; // Default to 'user'

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

// Menangani query pencarian
$search_query = isset($_GET['search_query']) ? '%' . $conn->real_escape_string($_GET['search_query']) . '%' : '%';

// Variabel pagination
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Hitung total record
$count_sql = "SELECT COUNT(*) as total FROM surat_keluar WHERE nomor_surat LIKE ?";
$stmt = $conn->prepare($count_sql);
$stmt->bind_param('s', $search_query);
$stmt->execute();
$result = $stmt->get_result();
$total_records = $result->fetch_assoc()['total'];
$stmt->close();

// Ambil record
$sql = "SELECT * FROM surat_keluar WHERE nomor_surat LIKE ? LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sii', $search_query, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Memproses data dari form jika form disubmit (untuk update data)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = $_POST['id'];
    $tanggal_surat = $_POST['tanggal_surat'];
    $nomor_surat = $_POST['nomor_surat'];
    $tujuan = $_POST['tujuan'];
    $perihal = $_POST['perihal'];
    $berkas = $_POST['berkas']; 

    // Menangani upload berkas
    if ($_FILES['berkas']['error'] == UPLOAD_ERR_OK) {
        $berkas_tmp = $_FILES['berkas']['tmp_name'];
        $berkas_name = $_FILES['berkas']['name'];
        $berkas_destination = 'uploads/' . $berkas_name;

        // Pindahkan berkas ke direktori tujuan
        if (move_uploaded_file($berkas_tmp, $berkas_destination)) {
            $berkas_display = $berkas_name;
        } else {
            // Tangani kesalahan upload jika perlu
            $berkas_display = '';
        }
    } else {
        // Jika tidak ada berkas baru, gunakan nilai yang ada sebelumnya
        $berkas_display = $_POST['berkas_display'];
    }

    // Query untuk memperbarui data di database
    $query = "UPDATE surat_keluar SET 
                tanggal_surat = ?, 
                nomor_surat = ?, 
                tujuan = ?, 
                perihal = ?, 
                berkas = ? 
              WHERE id = ?";

    // Persiapkan dan eksekusi query
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssi", $tanggal_surat, $nomor_surat, $tujuan, $perihal, $berkas_display, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Data surat keluar berhasil diperbarui.'); window.location.href='arsipsuratkeluar.php';</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}

// Menghapus data surat keluar
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];

    // Query untuk menghapus data berdasarkan ID
    $delete_query = "DELETE FROM surat_keluar WHERE id = $delete_id";

    if ($conn->query($delete_query) === TRUE) {
        // Query untuk mengatur ulang nomor urutan (ID)
        $reset_query = "SET @num := 0; UPDATE surat_keluar SET id = @num := (@num+1); ALTER TABLE surat_keluar AUTO_INCREMENT = 1";
        if ($conn->multi_query($reset_query) === TRUE) {
            // Pesan sukses jika berhasil
            echo "<script>alert('Data berhasil dihapus, Terimakasih:)'); window.location.href = 'arsipsuratkeluar.php';</script>";
        } else {
            // Pesan error jika ada masalah dalam mengatur ulang nomor urutan
            echo "<script>alert('Error saat mengatur ulang nomor urutan: " . $conn->error . "');</script>";
        }
    } else {
        // Pesan error jika gagal menghapus data
        echo "<script>alert('Error saat menghapus data: " . $conn->error . "');</script>";
    }
}

// Tutup koneksi
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

    <title>E-Arsip Surat Keluar - Biro Pemerintahan Otonomi Daerah Provinsi Sulawesi Utara</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Favicon -->
    <link rel="icon" href="img/provsulut.png" type="image/x-icon">
    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css" rel="stylesheet">
    <style>
          /* Custom styles */
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
        

      /* Card container styling */
    .card {
        border: 1px solid #ddd; /* Border color */
        border-radius: 0.5rem; /* Rounded corners */
        background-color: #fff; /* Background color */
        box-shadow: 0 0 1rem rgba(0,0,0,0.1); /* Box shadow for card effect */
        margin-bottom: 1.5rem; /* Space below the card */
        overflow: hidden; /* Ensure content stays inside card */
    }

    /* Card header styling */
    .card-header {
        background-color: #cb0000; /* Header background color */
        color: #fff; /* Text color */
        padding: 1rem; /* Padding */
        border-bottom: 1px solid #ddd; /* Border bottom */
        font-size: 1.25rem; /* Font size */
        font-weight: 600; /* Font weight */
    }

    /* Card body styling */
    .card-body {
        padding: 1rem; /* Padding */
    }

    /* Card shadow styling */
    .shadow {
        box-shadow: 0 0 1rem rgba(0,0,0,0.1); /* Box shadow for card effect */
    }

    /* Margin bottom for card */
    .mb-4 {
        margin-bottom: 1.5rem; /* Space below the card */
    }
    
    /* Text primary color */
    .text-primary {
        color: #007bff; /* Primary text color */
    }
    
    /* Font weight bold */
    .font-weight-bold {
        font-weight: 700; /* Bold font weight */
    }
    
    /* Heading styling */
    h6 {
        margin: 0; /* Remove default margin */
    }
    
    /* Margin for card header heading */
    .card-header h6 {
        margin: 0; /* Remove default margin */
    }
    
    /* Padding for card header */
    .py-3 {
        padding-top: 1rem; /* Padding top */
        padding-bottom: 1rem; /* Padding bottom */
    }
    /* Table styling */
    .table {
        margin-bottom: 0; /* Remove default margin */
        border-collapse: collapse; /* Collapsed borders */
    }

    .table thead th {
        background-color: #cb0000; /* Header background color */
        color: #fff; /* Header text color */
        padding: 0.75rem; /* Padding */
        text-align: center; /* Center align text */
    }

    .table tbody td {
        padding: 0.75rem; /* Padding */
        vertical-align: middle; /* Vertical align center */
        text-align: center; /* Center align text */
    }

    .table tbody tr:nth-child(odd) {
        background-color: #f2f2f2; /* Alternating row color */
    }

    .table tbody tr:hover {
        background-color: #e2e6ea; /* Hover row color */
    }

    
    /* Action buttons styling */
    .btn-action-edit {
        margin-bottom: 5px;
        background-color: #28a745; /* Edit button color */
        border-color: #28a745; /* Edit button border color */
    }

    .btn-action-edit:hover {
        background-color: #cb0000; /* Darker green on hover */
        border-color: #cb0000; /* Darker border color on hover */
    }

    .btn-action {
        margin-top: 5px;
        background-color: #ffc107; /* Delete button color */
        border-color: #ffc107; /* Delete button border color */
    }

    .btn-action:hover {
        background-color: gray; /* Darker yellow on hover */
        border-color: gray; /* Darker border color on hover */
    }

    /* Pagination controls styling */
    .pagination-controls {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }

    /* Pagination controls styling */
    .pagination-controls {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        /* Styling untuk link pagination */
        .pagination-controls a {
            display: inline-block;
            padding: 5px 10px;
            margin: 0 2px;
            border: 1px solid #ddd;
            border-radius: 3px;
            text-decoration: none;
            color: black; /* Default text color */
            background-color: #fff; /* Default background color */
        }
        /* Styling untuk link pagination saat hover */
        .pagination-controls a:hover {
            background-color: #cb0000; /* Background color on hover */
            color: white; /* Text color on hover */
            border-color: #ddd; /* Border color on hover */
        }
        /* Styling untuk link pagination yang aktif */
        .pagination-controls a.active {
            background-color: #cb0000; /* Active page color */
            color: #fff; /* Active page text color */
            border-color: #cb0000; /* Active page border color */
        }
         /* Styling untuk tombol download PDF */
         .btn-download-pdf {
            position: fixed;
            right: 20px;
            bottom: 20px;
            background-color: red;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            z-index: 1000;
        }
        .btn-download-pdf:hover {
            background-color:#990000;
          
            color: white;
        }

    .action-container {
    display: flex;
    justify-content: center; /* Untuk menempatkan tombol di tengah */
    gap: 10px; /* Jarak antar tombol */
}

.action-btn {
    margin: 5px 0; /* Margin atas dan bawah */
    margin-right: 5px;
}

.no-file-msg {
    margin: 5px 0; /* Margin atas dan bawah */
    font-size: 0.875em; /* Ukuran font agar sesuai dengan tombol */
    color: #6c757d; /* Warna teks */
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
                    <!-- Topbar content (same as before) -->
 <!-- Show message if any -->
 <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-info">
                            <?php
                            echo $_SESSION['message'];
                            unset($_SESSION['message']);
                            ?>
                        </div>
                    <?php endif; ?>



                    <!-- Search Form -->
 <form class="form-inline mb-4" method="GET" action="">
                        <input class="form-control mr-sm-2" type="search" name="search_query" placeholder="Cari Tgl / Nomor Surat" aria-label="Search" value="<?php echo htmlspecialchars($_GET['search_query'] ?? '', ENT_QUOTES); ?>">
                        <button class="btn btn-danger my-2 my-sm-0" type="submit">Cari</button>
                    </form>


<!-- DataTables Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-white">Arsip Surat Keluar</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal Surat</th>
                        <th>Nomor Surat</th>
                        <th>Tujuan</th>
                        <th>Perihal</th>
                        <th>Nama Berkas</th>
                        <?php if ($role === 'admin'): ?>
                        <th class="action-column">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php
                // Koneksi ke database
                $conn = new mysqli("localhost", "root", "", "sistem_informasi");
                if ($conn->connect_error) {
                    die("Koneksi gagal: " . $conn->connect_error);
                }

                // Menangani query pencarian
                $search_query = isset($_GET['search_query']) ? '%' . $conn->real_escape_string($_GET['search_query']) . '%' : '%';

                // Variabel pagination
                $limit = 5;
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $offset = ($page - 1) * $limit;

                // Hitung total record
                $count_sql = "SELECT COUNT(*) as total FROM surat_keluar WHERE tanggal_surat LIKE ? OR nomor_surat LIKE ? OR tujuan LIKE ?";
                $stmt = $conn->prepare($count_sql);
                $stmt->bind_param('sss', $search_query, $search_query, $search_query);
                $stmt->execute();
                $result = $stmt->get_result();
                $total_records = $result->fetch_assoc()['total'];
                $stmt->close();

                // Ambil record
                $sql = "SELECT * FROM surat_keluar WHERE tanggal_surat LIKE ? OR nomor_surat LIKE ? OR tujuan LIKE ? LIMIT ? OFFSET ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('sssii', $search_query, $search_query, $search_query, $limit, $offset);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $no = $offset + 1;
                    while ($row = $result->fetch_assoc()) {
                        // Menentukan nama berkas dan URL
                        $nama_berkas = !empty($row['berkas']) ? basename($row['berkas']) : 'Tidak ada lampiran';
                        $file_url = 'download.php?file=' . urlencode($nama_berkas);

                        echo "<tr>
                                <td>{$no}</td>
                                <td>{$row['tanggal_surat']}</td>
                                <td>{$row['nomor_surat']}</td>
                                <td>{$row['tujuan']}</td>
                                <td>{$row['perihal']}</td>
                                <td>{$nama_berkas}</td>";
                                if ($role === 'admin') {
                                    echo "<td>
                                            <button class='btn btn-success btn-sm action-btn' onclick='openEditModal({$row['id']}, \"{$row['tanggal_surat']}\", \"{$row['nomor_surat']}\", \"{$row['tujuan']}\", \"{$row['perihal']}\", \"{$row['berkas']}\")'>
                                                <i class='fas fa-edit'></i>
                                            </button>";
                                    if (!empty($row['berkas'])) {
                                        echo "<a href='{$file_url}' class='btn btn-info btn-sm action-btn'>
                                                <i class='fas fa-download'></i>
                                              </a>";
                                    }
                                    echo "<form action='deleted.php' method='POST' style='display:inline;' onsubmit='return confirmDelete()'>
                                            <input type='hidden' name='id' value='{$row['id']}'>
                                            <button type='submit' name='delete' class='btn btn-warning btn-sm action-btn'>
                                                <i class='fas fa-trash'></i>
                                            </button>
                                          </form>
                                          </td>";
                                }
                                
                        echo "</tr>";
                        $no++;
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>Tidak ada surat keluar</td></tr>";
                }

                // Tutup koneksi
                $conn->close();
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


                
<!-- Pagination Controls -->
<div class="pagination-controls">
    <?php
    // Pagination logic
    $search_query = isset($_GET['search_query']) ? htmlspecialchars($_GET['search_query']) : '';
    $total_pages = ceil($total_records / $limit);

    // Previous Page Link
    if ($page > 1) {
        echo "<a href='?page=" . ($page - 1) . "&search_query=" . urlencode($search_query) . "'>Sebelumnya</a>";
    }

    // Page Number Links
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $page) {
            echo "<a href='#' class='active'>$i</a>";
        } else {
            echo "<a href='?page=$i&search_query=" . urlencode($search_query) . "'>$i</a>";
        }
    }

    // Next Page Link
    if ($page < $total_pages) {
        echo "<a href='?page=" . ($page + 1) . "&search_query=" . urlencode($search_query) . "'>Selanjutnya</a>";
    }
    ?>
    </div>


<!-- Tambahkan tombol Download -->
<?php if ($role === 'admin'): ?>
            <!-- Tambahkan tombol Download -->
            <a href="arsipsuratkeluar_pdf.php" class="btn-download-pdf" target="_blank"><i class="fas fa-download"></i> Download Arsip</a>
        <?php endif; ?>
    </div>
</div>



<br><br>



                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

           
<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Surat Keluar :</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" action="arsipsuratkeluar.php" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="editId">
                    <div class="form-group">
                        <label for="editTanggalSurat">Tanggal Surat</label>
                        <input type="date" class="form-control" id="editTanggalSurat" name="tanggal_surat" required>
                    </div>
                    <div class="form-group">
                        <label for="editNomorSurat">Nomor Surat</label>
                        <input type="text" class="form-control" id="editNomorSurat" name="nomor_surat" required>
                    </div>
                    <div class="form-group">
                        <label for="editTujuan">Tujuan</label>
                        <input type="text" class="form-control" id="editTujuan" name="tujuan" required>
                    </div>
                    <div class="form-group">
                        <label for="editPerihal">Perihal</label>
                        <input type="text" class="form-control" id="editPerihal" name="perihal" required>
                    </div>
                    <div class="form-group">
                        <label for="editBerkas">Nama Berkas</label>
                        <input type="text" class="form-control" id="editBerkas_display" name="berkas_display" readonly>
                        <input type="file" class="form-control-file" id="editBerkas" name="berkas">
                        <small class="form-text text-muted">Jika Anda ingin mengganti berkas, pilih berkas baru di sini.</small>
                    </div>
                    <button type="submit" name="update" class="btn btn-danger">Simpan</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openEditModal(id, tanggal_surat, nomor_surat, tujuan, perihal, berkas) {
    document.getElementById('editId').value = id;
    document.getElementById('editTanggalSurat').value = tanggal_surat;
    document.getElementById('editNomorSurat').value = nomor_surat;
    document.getElementById('editTujuan').value = tujuan;
    document.getElementById('editPerihal').value = perihal;
    document.getElementById('editBerkas_display').value = berkas || 'Tidak ada lampiran berkas';

    $('#editModal').modal('show');
}

function confirmDelete() {
    return confirm("Apakah Anda yakin ingin menghapus surat ini?");
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    if (message === 'delete_success') {
        alert('Data surat keluar telah berhasil dihapus!');
    } else if (message === 'delete_error') {
        alert('Terjadi kesalahan saat menghapus data surat keluar!');
    }
});
</script>


    <!-- End of Input Form -->
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



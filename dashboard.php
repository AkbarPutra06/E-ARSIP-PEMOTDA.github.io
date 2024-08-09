<?php
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
    $dbname = "sistem_informasi";

    // Buat koneksi
    $conn = new mysqli($servername, $db_username, $password, $dbname);

    // Periksa koneksi
    if ($conn->connect_error) { 
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Query untuk menghitung jumlah surat masuk
    $query_surat_masuk = "SELECT COUNT(*) as total FROM surat_masuk";
    $result_surat_masuk = $conn->query($query_surat_masuk);
    $total_surat_masuk = 0;

    if ($result_surat_masuk->num_rows > 0) {
        $row = $result_surat_masuk->fetch_assoc();
        $total_surat_masuk = $row['total'];
    }

    // Query untuk menghitung jumlah surat keluar
    $query_surat_keluar = "SELECT COUNT(*) as total FROM surat_keluar";
    $result_surat_keluar = $conn->query($query_surat_keluar);
    $total_surat_keluar = 0;

    if ($result_surat_keluar->num_rows > 0) {
        $row = $result_surat_keluar->fetch_assoc();
        $total_surat_keluar = $row['total'];
    }

    // Query untuk mengambil jumlah pegawai
    $sql = "SELECT COUNT(*) AS total_pegawai FROM pegawai";
    $result = $conn->query($sql);
    $total_pegawai = 0;

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $total_pegawai = $row['total_pegawai'];
    }

    if (isset($_SESSION['update_error'])) {
        echo '<div class="alert alert-danger">' . $_SESSION['update_error'] . '</div>';
        unset($_SESSION['update_error']);
    }

    if (isset($_SESSION['update_success'])) {
        echo '<div class="alert alert-success">' . $_SESSION['update_success'] . '</div>';
        unset($_SESSION['update_success']);
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

            <title>Beranda - Sistem Informasi Administrasi Biro Pemerintahan Otonomi Daerah Provinsi Sulawesi Utara</title>
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

            <!-- Custom styles for the sidebar -->
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

/* Welcome Box Styles */
.welcome-box {
    background-color: red; /* Light background color for contrast */
    border: 1px solid #dcdcdc; /* Border for definition */
    border-radius: 5px; /* Rounded corners */
    padding: 10px 40px; /* Padding for spacing, extra space on the right for the close button */
    margin: 10px 0; /* Margin for spacing around the box */
    font-size: 14px; /* Font size for readability */
    color: white; /* Text color */
    font-weight: 500; /* Slightly bold text */
    display: flex; /* Flexbox for alignment */
    align-items: center; /* Center items vertically */
    position: relative; /* Position relative for positioning the close button */
}

/* Close Button Styles */
.welcome-box .close-btn {
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    font-size: 16px;
    cursor: pointer;
    color: white; /* Close button color */
}

.welcome-box .close-btn:hover {
    color: #333; /* Close button color on hover */
}

/* Optional: Responsive adjustments */
@media (max-width: 768px) {
    .welcome-box {
        font-size: 12px; /* Smaller font size on smaller screens */
        padding: 8px 30px; /* Adjust padding for smaller screens */
    }
}

/* Layout Styles for Chart and Image Card */
.row {
    display: flex;
    flex-wrap: nowrap; /* Prevent wrapping */
    margin-top: 20px; /* Space above the row */
}

/* Chart Container Styles */
.chart-container {
    position: relative;
    height: 400px; /* Adjust height as needed */
    width: 100%; /* Full width of its parent container */
    background: #ffffff; /* White background for contrast */
    border-radius: 10px; /* Rounded corners for a modern look */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    padding: 20px; /* Padding around the chart */
    border: 1px solid #e3e6f0; /* Light border for definition */
}

/* Wrapper to ensure full size of the chart */
.chart-wrapper {
    position: relative;
    width: 100%;
    height: 100%;
}

/* Ensures that the chart fills the container */
.chart-area {
    height: 100%;
    width: 100%;
}

#myChart {
    height: 100% !important;
    width: 100% !important;
    border-radius: 5px; /* Rounded corners for the chart */
}

/* Image Styles */
.card-body img {
    max-width: 100%; /* Ensure the image fits within the card */
    height: auto; /* Maintain aspect ratio */
    border-radius: 5px; /* Rounded corners for the image */
    margin-bottom: 15px; /* Space below the image */
}

/* Description Text Styles */
.card-body p {
    font-size: 14px; /* Font size for the description */
    color: #333; /* Dark color for better readability */
    line-height: 1.6; /* Line height for readability */
    margin-bottom: 15px; /* Space below the paragraph */
    text-align: left; /* Align text to the left */
}

/* Link Styles */
.card-body a {
    color: #007bff; /* Bootstrap primary color for the link */
    text-decoration: none; /* Remove underline from the link */
    font-weight: bold; /* Bold text for emphasis */
}

.card-body a:hover {
    text-decoration: underline; /* Underline on hover for better visibility */
    color: #0056b3; /* Darker shade on hover */
}

/* Optional: Responsive Adjustments */
@media (max-width: 768px) {
    .card-body p {
        font-size: 12px; /* Smaller font size on smaller screens */
        margin-bottom: 10px; /* Adjust spacing for smaller screens */
    }
}


/* Table Styles */
.table-container {
    width: 100%;
    overflow-x: auto;
    margin-top: 20px;
}

.table {
    width: 100%;
    margin-bottom: 1rem;
    color: #858796;
    border-collapse: collapse;
}

.table th, .table td {
    padding: 12px;
    text-align: center;
    border-top: 1px solid #e3e6f0;
}

.table thead th {
    background-color: #4e73df;
    color: white;
    border-bottom: 2px solid #e3e6f0;
}

.table tbody tr:nth-child(even) {
    background-color: #f8f9fc;
}

.table tbody tr:hover {
    background-color: #e9ecef;
}

.table-responsive {
    display: block;
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
/* Footer Styles */
.footer {
    background-color: #f8f9fc; /* Light background color for contrast */
    border-top: 1px solid #e3e6f0; /* Light border on top */
    padding: 10px 0; /* Padding for spacing */
    margin-top: 20px; /* Spacing above the footer */
    text-align: center; /* Center the text */
    color: #6c757d; /* Text color */
    font-size: 14px; /* Font size */
}

/* Optional: Responsive adjustments */
@media (max-width: 768px) {
    .footer {
        font-size: 12px; /* Smaller font size on smaller screens */
        padding: 8px 0; /* Adjust padding for smaller screens */
    }
}
/* Gaya untuk header utama */
.page-header {
    font-family: 'Arial', sans-serif; /* Font yang digunakan untuk header */
    color: black; /* Warna teks utama */
    font-size: 2.0rem; /* Ukuran font utama */
    margin-bottom: 0.5rem; /* Jarak bawah dari elemen lain */
}

/* Gaya untuk sub-header atau small text */
.page-header small {
    font-family: 'Arial', sans-serif; /* Font yang digunakan untuk sub-header */
    font-weight: normal; /* Menjadikan teks normal */
    color: #858796; /* Warna teks sub-header */
    font-size: 1rem; /* Ukuran font sub-header */
 
    margin-top: 0.5rem; /* Jarak atas dari elemen lain */
}
/* Current Date Styles */
.current-date {
    font-size: 14px; /* Font size for the date */
    color: #333; /* Color for the date text */
    font-weight: 500; /* Slightly bold text */
}

/* Adjust styles if needed for responsiveness */
@media (max-width: 768px) {
    .current-date {
        font-size: 12px; /* Smaller font size on smaller screens */
    }
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
             
             <!-- Welcome Message -->
             <div class="welcome-box">
                <button class="close-btn" onclick="closeWelcomeBox()">&times;</button>
                Welcome To E-ARSIP Pemotda Version Beta . Aplikasi Pengelolaan Arsip Digital. Dikembangkan oleh Akbar Putra  | Politeknik Negeri Manado |  +62 858-2451-8618!
            </div>
            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>


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


            <h1 class="page-header">Menu Dashboard <small>Overview & Statistic</small></h1>
            <div id="current-date" class="current-date"></div>
            <script>
                function closeWelcomeBox() {
        var welcomeBox = document.querySelector('.welcome-box');
        if (welcomeBox) {
            welcomeBox.style.display = 'none'; // Hide the welcome box
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        // Get current date
        const today = new Date();
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        const formattedDate = today.toLocaleDateString('id-ID', options);
        
        // Set date in the container
        document.getElementById('current-date').textContent = formattedDate;
    });
</script>


            
           
                <!-- Content Row -->
                <div class="row">
                    
                    <!-- Surat Masuk Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Jumlah Surat Masuk :</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_surat_masuk; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-envelope fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Surat Keluar Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Jumlah Surat Keluar :</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_surat_keluar; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-paper-plane fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Pegawai Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Jumlah Pegawai :</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_pegawai; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Users Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Nama Pengguna :</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $username; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users-cog fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
       
             <!-- Area Chart and Image Card -->
<div class="row">
    <!-- Area Chart for Surat Masuk dan Keluar -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Statistik Surat Masuk & Surat Keluar</h6>
            </div>
            <div class="card-body">
                <div class="chart-container" id="chartContainer">
                    <div class="chart-wrapper">
                        <div class="chart-area">
                            <canvas id="myChart"></canvas>
                        </div>
                    </div>
                </div>
                <div id="noDataMessage" style="display: none;">Data tidak cukup untuk menampilkan grafik.</div>
            </div>
        </div>
    </div>

    <!-- Image Card -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Tentang Kami</h6>
            </div>
            <div class="card-body text-center">
                <img src="img/prof.png" alt="Descriptive Image Alt Text" class="img-fluid rounded">
                <p class="mt-3">Visi kami adalah menjadi pusat 
                    pengarsipan surat menyurat yang terpercaya dan efisien, sementara misi kami adalah mengelola arsip dengan standar desentralisasi, mendorong partisipasi 
                    masyarakat, memastikan keteraturan dalam pengarsipan, dan meningkatkan akses informasi serta kerja sama lintas instansi dan dengan masyarakat.
.</p>
<a target="_blank" rel="nofollow" href="https://humasprovsulut.wordpress.com/biro-pemerintahan-dan-humas-setda-prov-sulut/">Profil Kami di sini&rarr;</a>
            </div>
        </div>
    </div>
</div>

    


                    
                    

 <!-- Logout Modal-->
 <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Yakin ingin keluar?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Apakah Anda yakin ingin keluar dari E-Arsip?</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                    <a class="btn btn-primary" href="login/logout.php">Keluar</a>
                </div>
            </div>
        </div>
    </div>

          
    <!-- Content Row -->
    <div class="row">

   

    <!-- /.container-fluid -->
    </div>
                    <!-- End of Main Content -->
                </div>
    
                <!-- End of Content Wrapper -->
            </div>
            
            <!-- End of Page Wrapper -->
             <!-- Footer -->
<footer class="footer mt-auto py-3 bg-light">
    <div class="container text-center">
        <span class="text-muted">&copy; 2024 E-ARSIP PEMOTDA SULUT</span>
    </div>
</footer>
            <div class="text-center mt-3">
    
    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    
   
                    <!-- /.container-fluid -->

                </div>
                <!-- End of Main Content -->
                 

            

            </div>
            <!-- End of Content Wrapper -->

        </div>
        <!-- End of Page Wrapper -->

         <!-- Include Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Chart Script -->
    <script>
        var totalSuratMasuk = <?php echo $total_surat_masuk; ?>;
        var totalSuratKeluar = <?php echo $total_surat_keluar; ?>;
        
        // Check if total count is more than 2
        if (totalSuratMasuk > 1 || totalSuratKeluar > 2) {
            document.getElementById('chartContainer').style.display = 'block'; // Show chart
            var ctx = document.getElementById('myChart').getContext('2d');
            var myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Surat Masuk', 'Surat Keluar'],
                    datasets: [{
                        label: 'Jumlah',
                        data: [totalSuratMasuk, totalSuratKeluar],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(75, 192, 192, 0.2)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(75, 192, 192, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 50 // Set maximum limit to 100
                        }
                    }
                }
            });
        } else {
            document.getElementById('noDataMessage').style.display = 'block'; // Show no data message
        }
    </script>


        <!-- Scroll to Top Button-->
        <a class="scroll-to-top rounded" href="#page-top">
            <i class="fas fa-angle-up"></i>
        </a>

        

  
  

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
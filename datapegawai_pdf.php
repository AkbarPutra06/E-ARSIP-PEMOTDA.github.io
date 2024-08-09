<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Start session
session_start();

// Verifikasi apakah pengguna telah login
if (!isset($_SESSION['session_username'])) {
    header('Location: login.php');
    exit;
}

// Assuming 'user_role' is set in session when the user logs in
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'admin'; // Default to 'user';

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

// Query untuk mengambil data pegawai
$query = "SELECT * FROM pegawai";
$result = $conn->query($query);

// Buat HTML untuk PDF
$html = '<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #dddddd; text-align: left; padding: 8px; }
        th { background-color: #f2f2f2; }
        h1 { text-align: center; }
    </style>
</head>
<body>
    <h1>Daftar Pegawai ASN dan THL Biro Pemerintahan Otonomi Daerah Provinsi Sulawesi Utara</h1><br>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Golongan</th>
                <th>Jabatan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>';

// Tampilkan data pegawai
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $html .= '<tr>
                    <td>' . $row['id'] . '</td>
                    <td>' . htmlspecialchars($row['nama']) . '</td>
                    <td>' . htmlspecialchars($row['golongan']) . '</td>
                    <td>' . htmlspecialchars($row['jabatan']) . '</td>
                    <td>' . htmlspecialchars($row['status']) . '</td>
                  </tr>';
    }
} else {
    $html .= '<tr><td colspan="5">Tidak ada data pegawai.</td></tr>';
}

$html .= '   </tbody>
        </table>
    </body>
</html>';

// Create a new Dompdf instance
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Load HTML to Dompdf
$dompdf->loadHtml($html);

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$dompdf->stream("Daftar_Pegawai.pdf", array("Attachment" => 0));

// Close the database connection
$conn->close();
?>

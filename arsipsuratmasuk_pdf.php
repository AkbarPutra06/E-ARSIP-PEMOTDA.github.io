<?php
require 'vendor/autoload.php'; // Pastikan Anda sudah menginstal Dompdf via Composer

use Dompdf\Dompdf;
use Dompdf\Options;

// Buat koneksi ke database
function getDBConnection() {
    $conn = new mysqli("localhost", "root", "", "sistem_informasi");
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
    return $conn;
}

// Ambil data dari database
$search_query = '%';
$conn = getDBConnection();
$sql = "SELECT * FROM surat_masuk WHERE nomor_surat LIKE ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $search_query);
$stmt->execute();
$result = $stmt->get_result();

// Buat konten HTML untuk PDF
$html = '
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Surat Masuk Biro Pemerintah Otonomi Daerah Provinsi Sulawesi Utara</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        @page {
            margin: 20mm;
        }
        h2 {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: center; /* Menyejajarkan teks di tengah */
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
        }
        /* Optional print-specific styles */
        @media print {
            body {
                margin: 0;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>
    <h2>Daftar Surat Masuk Biro Pemerintahan Otonomi Daerah</h2><br>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Surat</th>
                <th>Nomor Surat</th>
                <th>Tanggal Diterima</th>
                <th>Nama Pengirim</th>
                <th>Perihal</th>
                <th>Disposisi</th>
                <th>Nama Berkas</th>
                <th>Tanda Terima</th>
            </tr>
        </thead>
        <tbody>';

if ($result->num_rows > 0) {
    $no = 1;
    while ($row = $result->fetch_assoc()) {
        $berkas = pathinfo($row['berkas'], PATHINFO_FILENAME);
        $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . htmlspecialchars($row['tanggal_surat']) . '</td>
                    <td>' . htmlspecialchars($row['nomor_surat']) . '</td>
                    <td>' . htmlspecialchars($row['tanggal_diterima']) . '</td>
                    <td>' . htmlspecialchars($row['nama_pengirim']) . '</td>
                    <td>' . htmlspecialchars($row['perihal']) . '</td>
                    <td>' . htmlspecialchars($row['disposisi']) . '</td>
                    <td>' . htmlspecialchars($berkas) . '</td>
                    <td>' . htmlspecialchars($row['tanda_tangan']) . '</td>
                </tr>';
        $no++;
    }
} else {
    $html .= '<tr><td colspan="9" class="text-center">Tidak ada surat masuk</td></tr>';
}

$html .= '</tbody></table></body></html>';

// Inisialisasi Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);

// (Opsional) Atur ukuran kertas dan orientasi
$dompdf->setPaper('A4', 'landscape');

// Render PDF
$dompdf->render();

// Output PDF ke browser
$dompdf->stream('arsipsuratmasuk.pdf', array('Attachment' => 0));
?>

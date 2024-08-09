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
$sql = "SELECT * FROM surat_keluar WHERE nomor_surat LIKE ?";
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
    <title>Daftar Surat Keluar Biro Pemerintah Otonomi Daerah Provinsi Sulawesi Utara</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        @page {
            margin: 20mm;
        }
        h1 {
            text-align: center;
            margin-top : auto;
            margin-bottom: 10px;
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
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        td {
            vertical-align: top;
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
    <h1>Daftar Surat Keluar Biro Pemerintahan Otonomi Daerah</h1><br>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Surat</th>
                <th>Nomor Surat</th>
                <th>Tujuan</th>
                <th>Perihal</th>
                <th>Nama Berkas</th>
            </tr>
        </thead>
        <tbody>';

if ($result->num_rows > 0) {
    $no = 1;
    while ($row = $result->fetch_assoc()) {
        $berkas = pathinfo($row['berkas'], PATHINFO_FILENAME);
        $html .= '<tr>
                    <td class="text-center">' . $no . '</td>
                    <td>' . htmlspecialchars($row['tanggal_surat']) . '</td>
                    <td>' . htmlspecialchars($row['nomor_surat']) . '</td>
                    <td>' . htmlspecialchars($row['tujuan']) . '</td>
                    <td>' . htmlspecialchars($row['perihal']) . '</td>
                    <td>' . htmlspecialchars($berkas) . '</td>
                </tr>';
        $no++;
    }
} else {
    $html .= '<tr><td colspan="6" class="text-center">Tidak ada surat keluar</td></tr>';
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
$dompdf->stream('daftar_surat_keluar.pdf', array('Attachment' => 0));
?>

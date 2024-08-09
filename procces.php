<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sistem_informasi";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $no = $_POST['no'];
    $tanggal_surat = $_POST['tanggal_surat'];
    $nomor_surat = $_POST['nomor_surat'];
    $tanggal_diterima = $_POST['tanggal_diterima'];
    $nama_pengirim = $_POST['nama_pengirim'];
    $perihal = $_POST['perihal'];
    $disposisi = $_POST['disposisi'];
    $tanda_tangan = $_POST['tanda_tangan'];

    $berkas = '';
    if (isset($_FILES['berkas']) && $_FILES['berkas']['error'] == 0) {
        $berkas = 'uploads/' . basename($_FILES['berkas']['name']);
        move_uploaded_file($_FILES['berkas']['tmp_name'], $berkas);
    }

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = $_POST['id'];
        $sql = "UPDATE surat_masuk SET no='$no', tanggal_surat='$tanggal_surat', nomor_surat='$nomor_surat', tanggal_diterima='$tanggal_diterima', nama_pengirim='$nama_pengirim', perihal='$perihal', disposisi='$disposisi', berkas='$berkas', tanda_tangan='$tanda_tangan' WHERE id=$id";
    } else {
        $sql = "INSERT INTO surat_masuk (no, tanggal_surat, nomor_surat, tanggal_diterima, nama_pengirim, perihal, disposisi, berkas, tanda_tangan) VALUES ('$no', '$tanggal_surat', '$nomor_surat', '$tanggal_diterima', '$nama_pengirim', '$perihal', '$disposisi', '$berkas', '$tanda_tangan')";
    }

    if ($conn->query($sql) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>


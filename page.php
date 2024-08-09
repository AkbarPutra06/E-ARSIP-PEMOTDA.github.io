<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "sistem_informasi");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Pagination variables
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search query
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Query for data with pagination
$query_masuk = "SELECT * FROM surat_masuk WHERE nomor_surat LIKE '%$search%' OR tanggal_surat LIKE '%$search%' LIMIT $limit OFFSET $offset";
$result_masuk = $conn->query($query_masuk);

// Query for data with pagination
$query_keluar = "SELECT * FROM surat_keluar WHERE nomor_surat LIKE '%$search%' OR tanggal_surat LIKE '%$search%' LIMIT $limit OFFSET $offset";
$result_keluar = $conn->query($query_keluar);

// Get total number of records for pagination
$total_query = "SELECT COUNT(*) AS total FROM surat_masuk WHERE nomor_surat LIKE '%$search%' OR tanggal_surat LIKE '%$search%'";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);
?>
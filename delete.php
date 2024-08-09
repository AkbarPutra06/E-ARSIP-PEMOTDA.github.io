<?php
// Start session
session_start();

// Verifikasi apakah pengguna telah login
if (!isset($_SESSION['session_username'])) {
    header('Location: login.php');
    exit;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "sistem_informasi");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the delete button is pressed for surat_masuk
if (isset($_POST['delete']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    // Prepare and execute the delete query
    $delete_sql = "DELETE FROM surat_masuk WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        // Redirect to the previous page or any other page after successful deletion
        header('Location: arsipsuratmasuk.php?message=delete_success');
    } else {
        // Handle the error, perhaps redirect with an error message
        header('Location: arsipsuratmasuk.php?message=delete_error');
    }

    $stmt->close();
}

// Close database connection
$conn->close();
?>

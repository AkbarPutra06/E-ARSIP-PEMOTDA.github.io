<?php
session_start();

// Hapus semua data session
session_unset();
session_destroy();

// Redirect ke halaman login dengan alert
echo '<script>alert("Anda telah berhasil logout. Sampai jumpa!"); window.location.href = "login.php";</script>';
exit();
?>

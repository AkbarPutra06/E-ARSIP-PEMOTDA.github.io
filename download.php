<?php
if (isset($_GET['file'])) {
    $file = basename($_GET['file']);
    $filepath = 'uploads/' . $file;

    // Periksa apakah file ada di direktori uploads
    if (file_exists($filepath)) {
        // Mendapatkan informasi tipe konten file
        $fileinfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($fileinfo, $filepath);
        finfo_close($fileinfo);

        // Menentukan header yang sesuai
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    } else {
        // Tampilkan pesan kesalahan yang lebih rinci
        echo "File tidak ditemukan di direktori: " . htmlspecialchars($filepath);
    }
} else {
    echo "File tidak ditentukan.";
}
?>

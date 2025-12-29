<?php
session_start();

// Cek apakah sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo 'Unauthorized';
    exit();
}

if ($_FILES && isset($_FILES['restore_file'])) {
    $uploadedFile = $_FILES['restore_file'];
    
    if ($uploadedFile['error'] === UPLOAD_ERR_OK) {
        $fileContent = file_get_contents($uploadedFile['tmp_name']);
        $data = json_decode($fileContent, true);
        
        if ($data && isset($data['stok']) && isset($data['riwayat'])) {
            // Simpan data yang di-restore
            file_put_contents('../data_inventaris.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            echo 'Data berhasil di-restore!';
        } else {
            echo 'Format file tidak valid!';
        }
    } else {
        echo 'Error upload file!';
    }
} else {
    echo 'Tidak ada file yang di-upload!';
}
?>
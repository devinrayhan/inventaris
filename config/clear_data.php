<?php
session_start();

// Cek apakah sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

// Hapus semua data dari database MySQL
require_once 'database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Hapus semua transaksi
    $conn->exec("DELETE FROM transaksi");
    
    // Hapus semua barang
    $conn->exec("DELETE FROM barang");
    
    // Reset auto increment
    $conn->exec("ALTER TABLE transaksi AUTO_INCREMENT = 1");
    $conn->exec("ALTER TABLE barang AUTO_INCREMENT = 1");
    
    // Simpan pesan sukses di session
    $_SESSION['notification'] = [
        'message' => 'Semua data berhasil dihapus dari database',
        'type' => 'success'
    ];
    
} catch (Exception $e) {
    // Simpan pesan error di session
    $_SESSION['notification'] = [
        'message' => 'Error: ' . $e->getMessage(),
        'type' => 'error'
    ];
}

// Redirect kembali
header('Location: ../pengaturan/pengaturan.php');
exit();
?>
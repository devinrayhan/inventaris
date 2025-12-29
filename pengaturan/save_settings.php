<?php
session_start();

// Redirect ke login jika belum login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    // Save to session (you can also save to database if you have a settings table)
    $_SESSION['settings'] = [
        'app_name' => $data['app_name'] ?? 'Sistem Manajemen Inventaris',
        'low_stock' => intval($data['low_stock'] ?? 5),
        'auto_backup' => $data['auto_backup'] ?? 'daily',
        'notify_low_stock' => intval($data['notify_low_stock'] ?? 1),
        'auto_save' => intval($data['auto_save'] ?? 1)
    ];
    
    echo json_encode(['success' => true, 'message' => 'Pengaturan berhasil disimpan']);
} else {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
}
?>

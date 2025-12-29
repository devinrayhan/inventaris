<?php
session_start();

// Cek apakah sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

require_once 'config.php';

$inventaris = new ManajemenInventaris();

// Set header untuk download
header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="backup_inventaris_' . date('Y-m-d_H-i-s') . '.json"');

// Output data sebagai JSON
$dataBackup = [
    'stok' => $inventaris->getStokBarang(),
    'riwayat' => $inventaris->getRiwayatTransaksi(),
    'backup_date' => date('Y-m-d H:i:s'),
    'version' => '1.0.0'
];

echo json_encode($dataBackup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
exit();
?>
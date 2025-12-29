<?php
session_start();

// Redirect ke login jika belum login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

$type = isset($_GET['type']) ? $_GET['type'] : 'barang';

// CSV format
header('Content-Type: text/csv; charset=UTF-8');

if ($type == 'barang') {
    header('Content-Disposition: attachment; filename="Template_Data_Barang.csv"');
    
    // UTF-8 BOM
    echo chr(0xEF).chr(0xBB).chr(0xBF);
    
    // Header
    echo "Nama Barang,Stok Awal,Satuan,Harga,Keterangan\n";
    
    // Sample data
    echo "Contoh: Laptop HP,10,unit,5000000,Laptop untuk kantor\n";
    echo "Contoh: Printer Canon,5,unit,2000000,Printer laser\n";
    
} else {
    header('Content-Disposition: attachment; filename="Template_Data_Transaksi.csv"');
    
    // UTF-8 BOM
    echo chr(0xEF).chr(0xBB).chr(0xBF);
    
    // Header
    echo "No,Jenis,Nama Barang,Jumlah,Satuan,Harga,Tanggal,Bagian,Penanggung Jawab\n";
    
    // Sample data
    echo "1,MASUK,Laptop HP,5,unit,5000000,15/12/2025,IT,Admin\n";
    echo "2,KELUAR,Mouse Wireless,10,unit,50000,15/12/2025,Keuangan,Budi\n";
}
?>

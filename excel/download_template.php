<?php
session_start();

// Redirect ke login jika belum login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

$type = isset($_GET['type']) ? $_GET['type'] : 'barang';

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="Template_' . ucfirst($type) . '.xls"');

echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
echo '<head>';
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
echo '<style>
    .header { background-color: #4472C4; color: white; font-weight: bold; text-align: center; }
    .info { background-color: #FFF2CC; border: 1px solid #FFC000; }
    .required { color: red; }
</style>';
echo '</head>';
echo '<body>';

if ($type == 'barang') {
    echo '<table border="1">';
    echo '<tr class="info"><td colspan="5">';
    echo '<b>TEMPLATE IMPORT DATA BARANG</b><br>';
    echo 'Petunjuk Pengisian:<br>';
    echo '1. Jangan hapus baris header (baris pertama)<br>';
    echo '2. Isi data mulai dari baris ke-3<br>';
    echo '3. Kolom dengan tanda <span class="required">(*)</span> wajib diisi<br>';
    echo '4. Satuan: buah, lusin, kodi, gros, rim<br>';
    echo '5. Stok awal: angka positif<br>';
    echo '6. Jika barang sudah ada, stok akan ditambahkan';
    echo '</td></tr>';
    
    echo '<tr class="header">';
    echo '<th>Nama Barang <span class="required">*</span></th>';
    echo '<th>Stok Awal <span class="required">*</span></th>';
    echo '<th>Satuan <span class="required">*</span></th>';
    echo '<th>Harga <span class="required">*</span></th>';
    echo '<th>Keterangan</th>';
    echo '</tr>';
    
    // Contoh data
    echo '<tr>';
    echo '<td>Sapu Ijuk</td>';
    echo '<td>50</td>';
    echo '<td>buah</td>';
    echo '<td>15000</td>';
    echo '<td>Stok awal bulan Desember</td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<td>Sapu Lidi</td>';
    echo '<td>10</td>';
    echo '<td>lusin</td>';
    echo '<td>120000</td>';
    echo '<td>1 lusin = 12 buah</td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<td>Kertas HVS A4</td>';
    echo '<td>5</td>';
    echo '<td>rim</td>';
    echo '<td>45000</td>';
    echo '<td>1 rim = 500 lembar</td>';
    echo '</tr>';
    
    echo '</table>';
    
} else if ($type == 'transaksi') {
    echo '<table border="1">';
    echo '<tr class="info"><td colspan="9">';
    echo '<b>TEMPLATE IMPORT TRANSAKSI</b><br>';
    echo 'Petunjuk Pengisian:<br>';
    echo '1. Jangan hapus baris header (baris pertama)<br>';
    echo '2. Isi data mulai dari baris ke-3<br>';
    echo '3. Kolom dengan tanda <span class="required">(*)</span> wajib diisi<br>';
    echo '4. Jenis: MASUK atau KELUAR (huruf besar)<br>';
    echo '5. Tanggal format: DD/MM/YYYY atau YYYY-MM-DD<br>';
    echo '6. Nama barang harus sudah ada di database (untuk transaksi KELUAR)';
    echo '</td></tr>';
    
    echo '<tr class="header">';
    echo '<th>No</th>';
    echo '<th>Jenis <span class="required">*</span></th>';
    echo '<th>Nama Barang <span class="required">*</span></th>';
    echo '<th>Jumlah <span class="required">*</span></th>';
    echo '<th>Satuan <span class="required">*</span></th>';
    echo '<th>Harga <span class="required">*</span></th>';
    echo '<th>Tanggal <span class="required">*</span></th>';
    echo '<th>Bagian</th>';
    echo '<th>Penanggung Jawab</th>';
    echo '</tr>';
    
    // Contoh data transaksi masuk
    echo '<tr>';
    echo '<td>1</td>';
    echo '<td>MASUK</td>';
    echo '<td>Sapu Ijuk</td>';
    echo '<td>20</td>';
    echo '<td>buah</td>';
    echo '<td>15000</td>';
    echo '<td>15/12/2025</td>';
    echo '<td></td>';
    echo '<td></td>';
    echo '</tr>';
    
    // Contoh data transaksi keluar
    echo '<tr>';
    echo '<td>2</td>';
    echo '<td>KELUAR</td>';
    echo '<td>Sapu Ijuk</td>';
    echo '<td>5</td>';
    echo '<td>buah</td>';
    echo '<td>15000</td>';
    echo '<td>15/12/2025</td>';
    echo '<td>Kantor Pusat</td>';
    echo '<td>Budi Santoso</td>';
    echo '</tr>';
    
    echo '</table>';
}

echo '</body>';
echo '</html>';
?>

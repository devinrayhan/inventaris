<?php
session_start();

// Redirect ke login jika belum login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

require_once '../config/config.php';

$inventaris = new ManajemenInventaris();

// Get parameters
$exportBarang = isset($_GET['barang']) && $_GET['barang'] == '1';
$exportTransaksi = isset($_GET['transaksi']) && $_GET['transaksi'] == '1';
$exportUsers = isset($_GET['users']) && $_GET['users'] == '1';
$format = isset($_GET['format']) ? $_GET['format'] : 'xlsx';

// Filter parameters
$filterMonth = isset($_GET['month']) ? $_GET['month'] : '';
$filterYear = isset($_GET['year']) ? $_GET['year'] : '';

// Prepare filename
$timestamp = date('Y-m-d_His');
$filename = "Inventaris_Export_" . $timestamp;

// Add month/year to filename if filtered
if ($filterMonth && $filterYear) {
    $monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $filename = "Inventaris_" . $monthNames[intval($filterMonth)] . "_" . $filterYear . "_" . $timestamp;
}

if ($format == 'csv') {
    // CSV Export
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // UTF-8 BOM for Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    if ($exportBarang) {
        // Header untuk Data Barang
        fputcsv($output, ['=== DATA BARANG ===']);
        fputcsv($output, ['No', 'Nama Barang', 'Stok', 'Satuan', 'Terakhir Update']);
        
        $stokBarang = $inventaris->getStokBarang();
        $no = 1;
        foreach ($stokBarang as $nama => $jumlah) {
            $satuan = $inventaris->getSatuanBarang($nama);
            fputcsv($output, [$no++, $nama, $jumlah, $satuan, date('Y-m-d H:i:s')]);
        }
        fputcsv($output, []);
    }
    
    if ($exportTransaksi) {
        // Header untuk Transaksi
        fputcsv($output, ['=== RIWAYAT TRANSAKSI ===']);
        if ($filterMonth && $filterYear) {
            $monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            fputcsv($output, ['Periode: ' . $monthNames[intval($filterMonth)] . ' ' . $filterYear]);
        }
        fputcsv($output, ['No', 'Jenis', 'Nama Barang', 'Jumlah', 'Satuan', 'Harga', 'Total', 'Tanggal', 'Bagian', 'Penanggung Jawab']);
        
        $riwayat = $inventaris->getRiwayatTransaksi();
        $no = 1;
        foreach ($riwayat as $transaksi) {
            // Filter by month and year if specified
            if ($filterMonth && $filterYear) {
                $transaksiDate = date('Y-m', strtotime($transaksi['tanggal']));
                $filterDate = $filterYear . '-' . str_pad($filterMonth, 2, '0', STR_PAD_LEFT);
                if ($transaksiDate != $filterDate) {
                    continue;
                }
            }
            
            $total = $transaksi['jumlah'] * $transaksi['harga'];
            fputcsv($output, [
                $no++,
                $transaksi['jenis'],
                $transaksi['nama'],
                $transaksi['jumlah'],
                $transaksi['satuan'] ?? 'buah',
                $transaksi['harga'],
                $total,
                $transaksi['tanggal'],
                $transaksi['bagian'] ?? '-',
                $transaksi['penanggung_jawab'] ?? '-'
            ]);
        }
        fputcsv($output, []);
    }
    
    if ($exportUsers) {
        // Export users (optional, jika diperlukan)
        fputcsv($output, ['=== DATA USERS ===']);
        fputcsv($output, ['Username', 'Nama Lengkap', 'Role']);
        // Tambahkan query users jika diperlukan
    }
    
    fclose($output);
    exit();
    
} else {
    // XLSX Export (using HTML table method for compatibility)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
    
    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head>';
    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    echo '<style>
        .header { background-color: #4472C4; color: white; font-weight: bold; }
        .section { background-color: #D9E1F2; font-weight: bold; font-size: 14px; }
        .money { mso-number-format:"\#\,\#\#0"; }
    </style>';
    echo '</head>';
    echo '<body>';
    
    if ($exportBarang) {
        echo '<table border="1">';
        echo '<tr class="section"><td colspan="5">DATA BARANG & STOK</td></tr>';
        echo '<tr class="header">';
        echo '<th>No</th><th>Nama Barang</th><th>Stok</th><th>Satuan</th><th>Terakhir Update</th>';
        echo '</tr>';
        
        $stokBarang = $inventaris->getStokBarang();
        $no = 1;
        foreach ($stokBarang as $nama => $jumlah) {
            $satuan = $inventaris->getSatuanBarang($nama);
            echo '<tr>';
            echo '<td>' . $no++ . '</td>';
            echo '<td>' . htmlspecialchars($nama) . '</td>';
            echo '<td>' . $jumlah . '</td>';
            echo '<td>' . htmlspecialchars($satuan) . '</td>';
            echo '<td>' . date('d/m/Y H:i') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '<br><br>';
    }
    
    if ($exportTransaksi) {
        echo '<table border="1">';
        echo '<tr class="section"><td colspan="10">RIWAYAT TRANSAKSI';
        if ($filterMonth && $filterYear) {
            $monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            echo ' - ' . $monthNames[intval($filterMonth)] . ' ' . $filterYear;
        }
        echo '</td></tr>';
        echo '<tr class="header">';
        echo '<th>No</th><th>Jenis</th><th>Nama Barang</th><th>Jumlah</th><th>Satuan</th><th>Harga</th><th>Total Nilai</th><th>Tanggal</th><th>Bagian</th><th>Penanggung Jawab</th>';
        echo '</tr>';
        
        $riwayat = $inventaris->getRiwayatTransaksi();
        $no = 1;
        foreach ($riwayat as $transaksi) {
            // Filter by month and year if specified
            if ($filterMonth && $filterYear) {
                $transaksiDate = date('Y-m', strtotime($transaksi['tanggal']));
                $filterDate = $filterYear . '-' . str_pad($filterMonth, 2, '0', STR_PAD_LEFT);
                if ($transaksiDate != $filterDate) {
                    continue;
                }
            }
            
            $total = $transaksi['jumlah'] * $transaksi['harga'];
            echo '<tr>';
            echo '<td>' . $no++ . '</td>';
            echo '<td>' . htmlspecialchars($transaksi['jenis']) . '</td>';
            echo '<td>' . htmlspecialchars($transaksi['nama']) . '</td>';
            echo '<td>' . $transaksi['jumlah'] . '</td>';
            echo '<td>' . htmlspecialchars($transaksi['satuan'] ?? 'buah') . '</td>';
            echo '<td class="money">' . $transaksi['harga'] . '</td>';
            echo '<td class="money">' . $total . '</td>';
            echo '<td>' . date('d/m/Y', strtotime($transaksi['tanggal'])) . '</td>';
            echo '<td>' . htmlspecialchars($transaksi['bagian'] ?? '-') . '</td>';
            echo '<td>' . htmlspecialchars($transaksi['penanggung_jawab'] ?? '-') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
    
    echo '</body>';
    echo '</html>';
    exit();
}
?>

<?php
session_start();

// Redirect ke login jika belum login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

// Include config
require_once '../config/config.php';

// Set current page
$current_page = 'dashboard';
$title = 'Dashboard - Aplikasi Inventaris';
$message = '';
$message_type = '';

// Include header
include '../layout/header.php';
include '../layout/sidebar.php';

// Inisialisasi inventaris
$inventaris = new ManajemenInventaris();
$stokBarang = $inventaris->getStokBarang();
$riwayatTransaksi = $inventaris->getRiwayatTransaksi();

// Statistik
$totalBarang = count($stokBarang);
$totalStok = array_sum($stokBarang);
$transaksiHariIni = 0;
$totalNilaiStok = 0;

foreach ($riwayatTransaksi as $trx) {
    if ($trx['tanggal'] == date('Y-m-d')) {
        $transaksiHariIni++;
    }
    if ($trx['jenis'] == 'MASUK') {
        $totalNilaiStok += $trx['jumlah'] * $trx['harga'];
    }
}
?>

<div class="page-header">
    <div class="page-title">
        <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
        <p>Selamat datang di sistem manajemen inventaris</p>
    </div>
    <div class="page-actions">
        <span class="current-date"><?php echo date('d F Y'); ?></span>
    </div>
</div>

<div class="dashboard-content">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-purple">
            <div class="stat-icon">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $totalBarang; ?></h3>
                <p>Jenis Barang</p>
            </div>
        </div>
        
        <div class="stat-card stat-green">
            <div class="stat-icon">
                <i class="fas fa-cubes"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $totalStok; ?></h3>
                <p>Total Stok</p>
            </div>
        </div>
        
        <div class="stat-card stat-blue">
            <div class="stat-icon">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $transaksiHariIni; ?></h3>
                <p>Transaksi Hari Ini</p>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="recent-section">
        <div class="section-header">
            <h3><i class="fas fa-clock"></i> Transaksi Terbaru</h3>
        </div>
        
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Barang</th>
                        <th>Jumlah</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $recentTransactions = array_slice(array_reverse($riwayatTransaksi), 0, 5);
                    foreach ($recentTransactions as $trx): 
                        $total = $trx['jumlah'] * $trx['harga'];
                        $jenisClass = $trx['jenis'] == 'MASUK' ? 'success' : 'danger';
                        $jenisIcon = $trx['jenis'] == 'MASUK' ? 'arrow-down' : 'arrow-up';
                    ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($trx['tanggal'])); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $jenisClass; ?>">
                                <i class="fas fa-<?php echo $jenisIcon; ?>"></i>
                                <?php echo $trx['jenis']; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($trx['nama']); ?></td>
                        <td><?php echo $trx['jumlah']; ?></td>
                        <td>Rp <?php echo number_format($total, 0, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($recentTransactions)): ?>
                    <tr>
                        <td colspan="5" class="no-data">Belum ada transaksi</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <?php 
    $lowStockItems = [];
    foreach ($stokBarang as $nama => $jumlah) {
        if ($jumlah <= 5) { // Alert jika stok <= 5
            $lowStockItems[] = ['nama' => $nama, 'stok' => $jumlah];
        }
    }
    ?>
    
    <?php if (!empty($lowStockItems)): ?>
    <div class="alert-section">
        <div class="section-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Peringatan Stok Rendah</h3>
        </div>
        
        <div class="alert-grid">
            <?php foreach ($lowStockItems as $item): ?>
            <div class="alert-item">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="alert-content">
                    <h4><?php echo htmlspecialchars($item['nama']); ?></h4>
                    <p>Stok tersisa: <?php echo $item['stok']; ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include '../layout/footer.php'; ?>
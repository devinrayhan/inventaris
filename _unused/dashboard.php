<?php
session_start();

// Cek apakah sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

include 'config.php';

// Inisialisasi kelas inventaris
$inventaris = new ManajemenInventaris();

// Proses form jika ada data POST
$message = '';
$message_type = '';

if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'catat_masuk':
                $result = $inventaris->catatBarangMasuk(
                    $_POST['nama_barang'],
                    (int)$_POST['jumlah'],
                    (int)str_replace('.', '', $_POST['harga']),
                    $_POST['tanggal']
                );
                $message = $result['message'];
                $message_type = $result['success'] ? 'success' : 'error';
                break;
                
            case 'catat_keluar':
                $result = $inventaris->catatBarangKeluar(
                    $_POST['nama_barang'],
                    (int)$_POST['jumlah'],
                    (int)str_replace('.', '', $_POST['harga']),
                    $_POST['tanggal']
                );
                $message = $result['message'];
                $message_type = $result['success'] ? 'success' : 'error';
                break;
                
            case 'hapus_transaksi':
                $result = $inventaris->hapusTransaksi((int)$_POST['index']);
                $message = $result['message'];
                $message_type = $result['success'] ? 'success' : 'error';
                break;
        }
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Aplikasi Inventaris</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="header-left">
                <i class="fas fa-boxes"></i>
                <h1>Manajemen Inventaris</h1>
            </div>
            <div class="header-right">
                <span>Selamat datang, <?php echo $_SESSION['user']; ?>!</span>
                <a href="?logout=1" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Keluar
                </a>
            </div>
        </div>
    </header>

    <!-- Message Alert -->
    <?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>" id="alert">
        <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
        <?php echo $message; ?>
        <button onclick="closeAlert()" class="alert-close">&times;</button>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Navigation Tabs -->
        <div class="tab-navigation">
            <button class="tab-btn active" onclick="showTab('transaksi')">
                <i class="fas fa-edit"></i> Transaksi
            </button>
            <button class="tab-btn" onclick="showTab('stok')">
                <i class="fas fa-chart-bar"></i> Laporan Stok
            </button>
            <button class="tab-btn" onclick="showTab('riwayat-masuk')">
                <i class="fas fa-arrow-down"></i> Riwayat Masuk
            </button>
            <button class="tab-btn" onclick="showTab('riwayat-keluar')">
                <i class="fas fa-arrow-up"></i> Riwayat Keluar
            </button>
        </div>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Tab Transaksi -->
            <div id="transaksi" class="tab-panel active">
                <div class="panel-header">
                    <h2><i class="fas fa-briefcase"></i> Formulir Transaksi</h2>
                    <p>Gunakan formulir ini untuk mencatat barang masuk dan keluar. Pastikan semua data diisi dengan benar sebelum menyimpan transaksi.</p>
                </div>
                
                <form method="POST" class="transaction-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nama_barang">Nama Barang</label>
                            <input type="text" id="nama_barang" name="nama_barang" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="jumlah">Jumlah</label>
                            <input type="number" id="jumlah" name="jumlah" min="1" required>
                        </div>
                        <div class="form-group">
                            <label for="harga">Harga Satuan</label>
                            <input type="text" id="harga" name="harga" placeholder="0" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="tanggal">Tanggal Transaksi</label>
                            <input type="date" id="tanggal" name="tanggal" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="action" value="catat_masuk" class="btn btn-success">
                            <i class="fas fa-plus"></i> Catat Barang Masuk
                        </button>
                        <button type="submit" name="action" value="catat_keluar" class="btn btn-warning">
                            <i class="fas fa-minus"></i> Catat Barang Keluar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tab Stok -->
            <div id="stok" class="tab-panel">
                <div class="panel-header">
                    <h2><i class="fas fa-chart-line"></i> Laporan Stok Barang</h2>
                    <p>Berikut adalah ringkasan stok barang yang tersedia saat ini. Data akan otomatis terupdate setiap kali ada transaksi masuk atau keluar.</p>
                </div>
                
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-box"></i> Nama Barang</th>
                                <th><i class="fas fa-chart-bar"></i> Jumlah Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventaris->getStokBarang() as $nama => $jumlah): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($nama); ?></td>
                                <td><?php echo $jumlah; ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($inventaris->getStokBarang())): ?>
                            <tr>
                                <td colspan="2" class="no-data">Belum ada data stok</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Riwayat Masuk -->
            <div id="riwayat-masuk" class="tab-panel">
                <div class="panel-header">
                    <h2><i class="fas fa-box"></i> Riwayat Barang Masuk</h2>
                    <p>Berikut adalah riwayat semua transaksi barang masuk.</p>
                </div>
                
                <?php $riwayatMasuk = $inventaris->getRiwayatByJenis('MASUK'); ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-calendar"></i> Tanggal</th>
                                <th><i class="fas fa-box"></i> Nama Barang</th>
                                <th><i class="fas fa-hashtag"></i> Jumlah</th>
                                <th><i class="fas fa-money-bill"></i> Harga Beli</th>
                                <th><i class="fas fa-calculator"></i> Total</th>
                                <th><i class="fas fa-trash"></i> Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalKeseluruhan = 0;
                            foreach (array_reverse($riwayatMasuk) as $index => $trx): 
                                $total = $trx['jumlah'] * $trx['harga'];
                                $totalKeseluruhan += $total;
                                $realIndex = count($inventaris->getRiwayatTransaksi()) - 1 - array_search($trx, array_reverse($inventaris->getRiwayatTransaksi()));
                            ?>
                            <tr>
                                <td><?php echo date('d-m-Y', strtotime($trx['tanggal'])); ?></td>
                                <td><?php echo htmlspecialchars($trx['nama']); ?></td>
                                <td><?php echo $trx['jumlah']; ?></td>
                                <td>Rp <?php echo number_format($trx['harga'], 0, ',', '.'); ?></td>
                                <td><strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong></td>
                                <td>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')">
                                        <input type="hidden" name="action" value="hapus_transaksi">
                                        <input type="hidden" name="index" value="<?php echo $realIndex; ?>">
                                        <button type="submit" class="btn btn-danger btn-small">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($riwayatMasuk)): ?>
                            <tr>
                                <td colspan="6" class="no-data">Belum ada transaksi masuk</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if (!empty($riwayatMasuk)): ?>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="4"><strong>TOTAL KESELURUHAN:</strong></td>
                                <td colspan="2"><strong>Rp <?php echo number_format($totalKeseluruhan, 0, ',', '.'); ?></strong></td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <!-- Tab Riwayat Keluar -->
            <div id="riwayat-keluar" class="tab-panel">
                <div class="panel-header">
                    <h2><i class="fas fa-shipping-fast"></i> Riwayat Barang Keluar</h2>
                    <p>Berikut adalah riwayat semua transaksi barang keluar.</p>
                </div>
                
                <?php $riwayatKeluar = $inventaris->getRiwayatByJenis('KELUAR'); ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-calendar"></i> Tanggal</th>
                                <th><i class="fas fa-shipping-fast"></i> Nama Barang</th>
                                <th><i class="fas fa-hashtag"></i> Jumlah</th>
                                <th><i class="fas fa-money-bill"></i> Harga Jual</th>
                                <th><i class="fas fa-calculator"></i> Total</th>
                                <th><i class="fas fa-trash"></i> Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalKeseluruhan = 0;
                            foreach (array_reverse($riwayatKeluar) as $index => $trx): 
                                $total = $trx['jumlah'] * $trx['harga'];
                                $totalKeseluruhan += $total;
                                $realIndex = count($inventaris->getRiwayatTransaksi()) - 1 - array_search($trx, array_reverse($inventaris->getRiwayatTransaksi()));
                            ?>
                            <tr>
                                <td><?php echo date('d-m-Y', strtotime($trx['tanggal'])); ?></td>
                                <td><?php echo htmlspecialchars($trx['nama']); ?></td>
                                <td><?php echo $trx['jumlah']; ?></td>
                                <td>Rp <?php echo number_format($trx['harga'], 0, ',', '.'); ?></td>
                                <td><strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong></td>
                                <td>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')">
                                        <input type="hidden" name="action" value="hapus_transaksi">
                                        <input type="hidden" name="index" value="<?php echo $realIndex; ?>">
                                        <button type="submit" class="btn btn-danger btn-small">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($riwayatKeluar)): ?>
                            <tr>
                                <td colspan="6" class="no-data">Belum ada transaksi keluar</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if (!empty($riwayatKeluar)): ?>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="4"><strong>TOTAL KESELURUHAN:</strong></td>
                                <td colspan="2"><strong>Rp <?php echo number_format($totalKeseluruhan, 0, ',', '.'); ?></strong></td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="script.js"></script>
</body>
</html>
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
$current_page = 'transaksi';
$title = 'Transaksi - Aplikasi Inventaris';
$message = '';
$message_type = '';

// Inisialisasi inventaris
$inventaris = new ManajemenInventaris();

// Proses form
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
        }
    }
}

// Include header
include '../layout/header.php';
include '../layout/sidebar.php';
?>

<div class="page-header">
    <div class="page-title">
        <h2><i class="fas fa-exchange-alt"></i> Transaksi Barang</h2>
        <p>Catat transaksi barang masuk dan keluar</p>
    </div>
</div>

<div class="page-content">
    <div class="form-container">
        <div class="form-card">
            <div class="card-header">
                <h3><i class="fas fa-edit"></i> Formulir Transaksi</h3>
                <p>Gunakan formulir ini untuk mencatat barang masuk dan keluar. Pastikan semua data diisi dengan benar.</p>
            </div>
            
            <form method="POST" class="transaction-form" id="transactionForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nama_barang">
                            <i class="fas fa-box"></i>
                            Nama Barang *
                        </label>
                        <input type="text" id="nama_barang" name="nama_barang" required 
                               placeholder="Masukkan nama barang">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="jumlah">
                            <i class="fas fa-sort-numeric-up"></i>
                            Jumlah *
                        </label>
                        <input type="number" id="jumlah" name="jumlah" min="1" required 
                               placeholder="0">
                    </div>
                    <div class="form-group">
                        <label for="harga">
                            <i class="fas fa-money-bill"></i>
                            Harga Satuan *
                        </label>
                        <input type="text" id="harga" name="harga" required 
                               placeholder="0">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="tanggal">
                            <i class="fas fa-calendar"></i>
                            Tanggal Transaksi *
                        </label>
                        <input type="date" id="tanggal" name="tanggal" 
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="action" value="catat_masuk" class="btn btn-success">
                        <i class="fas fa-arrow-down"></i>
                        Catat Barang Masuk
                    </button>
                    <button type="submit" name="action" value="catat_keluar" class="btn btn-danger">
                        <i class="fas fa-arrow-up"></i>
                        Catat Barang Keluar
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-redo"></i>
                        Reset Form
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Quick Stock Info -->
    <div class="stock-info">
        <div class="info-card">
            <div class="card-header">
                <h3><i class="fas fa-info-circle"></i> Informasi Stok</h3>
            </div>
            
            <div class="stock-list">
                <?php 
                $stokBarang = $inventaris->getStokBarang();
                if (!empty($stokBarang)): 
                ?>
                    <?php foreach ($stokBarang as $nama => $jumlah): ?>
                    <div class="stock-item">
                        <span class="item-name"><?php echo htmlspecialchars($nama); ?></span>
                        <span class="item-stock <?php echo $jumlah <= 5 ? 'low-stock' : ''; ?>">
                            <?php echo $jumlah; ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-data">Belum ada data stok</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../layout/footer.php'; ?>
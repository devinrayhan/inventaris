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
$current_page = 'stok';
$title = 'Data Stok - Aplikasi Inventaris';
$message = '';
$message_type = '';

// Include header
include '../layout/header.php';
include '../layout/sidebar.php';

// Inisialisasi inventaris
$inventaris = new ManajemenInventaris();
$stokBarang = $inventaris->getStokBarang();
$detailedStok = $inventaris->getDetailedStokBarang();
?>

<div class="page-header">
    <div class="page-title">
        <h2><i class="fas fa-boxes"></i> Data Stok Barang</h2>
        <p>Informasi lengkap stok barang yang tersedia</p>
    </div>
</div>

<div class="page-content">
    <!-- Summary Cards -->
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-icon bg-blue">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo count($stokBarang); ?></h3>
                <p>Jenis Barang</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon bg-green">
                <i class="fas fa-cubes"></i>
            </div>
            <div class="summary-content">
                <h3><?php echo array_sum($stokBarang); ?></h3>
                <p>Total Stok</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon bg-blue">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="summary-content">
                <?php 
                $stokAda = 0;
                foreach ($stokBarang as $jumlah) {
                    if ($jumlah > 0) $stokAda++;
                }
                ?>
                <h3><?php echo $stokAda; ?></h3>
                <p>Stok Ada</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon bg-red">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="summary-content">
                <?php 
                $outOfStock = 0;
                foreach ($stokBarang as $jumlah) {
                    if ($jumlah == 0) $outOfStock++;
                }
                ?>
                <h3><?php echo $outOfStock; ?></h3>
                <p>Stok Habis</p>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="filter-section">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Cari nama barang..." 
                   onkeyup="searchTable()">
        </div>
        <div class="filter-buttons">
            <button class="filter-btn active" onclick="filterStock('all')">Semua</button>
            <button class="filter-btn" onclick="filterStock('available')">Stok Ada</button>
            <button class="filter-btn" onclick="filterStock('empty')">Stok Habis</button>
        </div>
    </div>

    <!-- Stock Table -->
    <div class="table-section">
        <div class="table-container">
            <table class="data-table" id="stockTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th><i class="fas fa-hashtag"></i> ID Barang</th>
                        <th><i class="fas fa-box"></i> Nama Barang</th>
                        <th><i class="fas fa-ruler"></i> Satuan</th>
                        <th><i class="fas fa-sort-numeric-up"></i> Jumlah Stok</th>
                        <th><i class="fas fa-tag"></i> Harga</th>
                        <th><i class="fas fa-money-bill-wave"></i> Total Nilai</th>
                        <th><i class="fas fa-calendar"></i> Terakhir Update</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    
                    foreach ($detailedStok as $item): 
                        $idBarang = $item['id'];
                        $nama = $item['nama_barang'];
                        $jumlah = $item['stok'];
                        $satuan = $item['satuan'];
                        $harga = $item['harga'];
                        $totalNilai = $item['total_nilai'];
                        $lastUpdate = $item['tanggal_update'];
                        
                        // Tentukan status stok
                        if ($jumlah == 0) {
                            $status = '<span class="badge badge-danger">Habis</span>';
                            $statusClass = 'empty';
                        } else {
                            $status = '<span class="badge badge-success">Ada</span>';
                            $statusClass = 'available';
                        }
                    ?>
                    <tr class="stock-row" data-status="<?php echo $statusClass; ?>">
                        <td><?php echo $no++; ?></td>
                        <td><strong><?php echo $idBarang; ?></strong></td>
                        <td class="item-name"><?php echo htmlspecialchars($nama); ?></td>
                        <td><?php echo htmlspecialchars($satuan); ?></td>
                        <td>
                            <span class="stock-amount <?php echo $statusClass; ?>">
                                <?php echo $jumlah; ?>
                            </span>
                        </td>
                        <td>Rp <?php echo number_format($harga, 0, ',', '.'); ?></td>
                        <td><strong>Rp <?php echo number_format($totalNilai, 0, ',', '.'); ?></strong></td>
                        <td>
                            <?php echo $lastUpdate ? date('d/m/Y', strtotime($lastUpdate)) : '-'; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($detailedStok)): ?>
                    <tr>
                        <td colspan="8" class="no-data">
                            <i class="fas fa-inbox"></i>
                            <p>Belum ada data stok barang</p>
                            <a href="../transaksi_masuk/transaksi_masuk.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Transaksi
                            </a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal untuk History -->
<div id="historyModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-history"></i> Riwayat Transaksi</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="historyContent">
            <!-- Content akan diisi via JavaScript -->
        </div>
    </div>
</div>

<!-- Custom Confirmation Modal -->
<div id="addStockModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 15px; max-width: 450px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.3); animation: modalSlideIn 0.3s ease-out;">
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-plus" style="font-size: 40px; color: white;"></i>
            </div>
            <h3 style="color: #2c3e50; margin: 0; font-size: 24px;">Tambah Stok Barang</h3>
        </div>
        
        <div id="addStockContent" style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 25px; border-left: 4px solid #667eea;">
            <!-- Content akan diisi oleh JavaScript -->
        </div>
        
        <p style="text-align: center; color: #7f8c8d; margin-bottom: 25px; font-size: 15px;">
            <i class="fas fa-arrow-right"></i> Anda akan diarahkan ke halaman Transaksi Masuk dengan form yang sudah terisi otomatis
        </p>
        
        <div style="display: flex; gap: 15px; justify-content: center;">
            <button type="button" onclick="closeAddStockModal()" style="padding: 12px 30px; background: #95a5a6; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 500; transition: all 0.3s;">
                <i class="fas fa-times"></i> Batal
            </button>
            <button type="button" id="confirmAddStockBtn" onclick="proceedAddStock()" style="padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 500; transition: all 0.3s; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
                <i class="fas fa-check"></i> Ya, Lanjutkan
            </button>
        </div>
    </div>
</div>

<style>
@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
</style>

<script>
// Search functionality
function searchTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('stockTable');
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        const nameCell = rows[i].getElementsByClassName('item-name')[0];
        if (nameCell) {
            const txtValue = nameCell.textContent || nameCell.innerText;
            if (txtValue.toLowerCase().indexOf(filter) > -1) {
                rows[i].style.display = '';
            } else {
                rows[i].style.display = 'none';
            }
        }
    }
}

// Filter by stock status
function filterStock(type) {
    const rows = document.querySelectorAll('.stock-row');
    const buttons = document.querySelectorAll('.filter-btn');
    
    // Update active button
    buttons.forEach(btn => btn.classList.remove('active'));
    
    // Cari tombol yang diklik dan set active
    buttons.forEach(btn => {
        if (btn.textContent.toLowerCase().includes(type) || 
            (type === 'all' && btn.textContent === 'Semua')) {
            btn.classList.add('active');
        }
    });
    
    rows.forEach(row => {
        const status = row.getAttribute('data-status');
        if (type === 'all' || status === type) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Close modal dengan ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});

// View item history
function viewHistory(itemName) {
    document.getElementById('historyModal').style.display = 'block';
    document.getElementById('historyContent').innerHTML = 
        '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Memuat...</div>';
    
    // Simulate loading (dalam implementasi nyata, ini akan fetch data via AJAX)
    setTimeout(() => {
        document.getElementById('historyContent').innerHTML = 
            '<p>Riwayat untuk: <strong>' + itemName + '</strong></p>' +
            '<p>Fitur ini akan menampilkan riwayat lengkap transaksi barang ini.</p>';
    }, 1000);
}

// Close modal
function closeModal() {
    document.getElementById('historyModal').style.display = 'none';
}

// Export data
function exportData() {
    alert('Fitur export akan segera tersedia');
}

// Print data
function printData() {
    window.print();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('historyModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php include '../layout/footer.php'; ?>
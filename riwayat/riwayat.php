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
$current_page = 'riwayat';
$title = 'Riwayat Transaksi - Aplikasi Inventaris';
$message = '';
$message_type = '';

// Inisialisasi inventaris
$inventaris = new ManajemenInventaris();

// Proses hapus transaksi
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'hapus_transaksi') {
    $result = $inventaris->hapusTransaksi((int)$_POST['id']);
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'error';
}

// Include header
include '../layout/header.php';
include '../layout/sidebar.php';

$riwayatTransaksi = $inventaris->getRiwayatTransaksi();
$riwayatMasuk = $inventaris->getRiwayatByJenis('MASUK');
$riwayatKeluar = $inventaris->getRiwayatByJenis('KELUAR');
?>

<div class="page-header">
    <div class="page-title">
        <h2><i class="fas fa-history"></i> Riwayat Transaksi</h2>
        <p>Catatan lengkap semua transaksi barang masuk dan keluar</p>
    </div>
    <div class="page-actions">
        <div class="date-filter">
            <input type="date" id="startDate" value="<?php echo date('Y-m-01'); ?>">
            <span>s/d</span>
            <input type="date" id="endDate" value="<?php echo date('Y-m-d'); ?>">
            <button onclick="filterByDate()" class="btn btn-primary">
                <i class="fas fa-filter"></i> Filter
            </button>
        </div>
    </div>
</div>

<div class="page-content">
    <!-- Tab Navigation -->
    <div class="tab-navigation">
        <button class="tab-btn active" onclick="showTab('semua', this)">
            <i class="fas fa-list"></i> Semua Transaksi
        </button>
        <button class="tab-btn" onclick="showTab('masuk', this)">
            <i class="fas fa-arrow-down"></i> Barang Masuk
        </button>
        <button class="tab-btn" onclick="showTab('keluar', this)">
            <i class="fas fa-arrow-up"></i> Barang Keluar
        </button>
    </div>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- Semua Transaksi -->
        <div id="semua" class="tab-panel active">
            <div class="table-section">
                <div class="section-header">
                    <h3>Semua Transaksi</h3>
                    <div class="summary-info">
                        <span class="info-item">
                            <i class="fas fa-list"></i>
                            Total: <?php echo count($riwayatTransaksi); ?> transaksi
                        </span>
                    </div>
                </div>
                
                <div class="table-container">
                    <table class="data-table" id="allTransactionTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th><i class="fas fa-calendar"></i> Tanggal</th>
                                <th><i class="fas fa-exchange-alt"></i> Jenis</th>
                                <th><i class="fas fa-box"></i> Nama Barang</th>
                                <th><i class="fas fa-sort-numeric-up"></i> Jumlah</th>
                                <th><i class="fas fa-money-bill"></i> Harga</th>
                                <th><i class="fas fa-calculator"></i> Total</th>
                                <th><i class="fas fa-building"></i> Bagian</th>
                                <th><i class="fas fa-user"></i> PJ</th>
                                <th><i class="fas fa-cogs"></i> Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            $reversedTransaksi = array_reverse($riwayatTransaksi);
                            foreach ($reversedTransaksi as $index => $trx): 
                                $total = $trx['jumlah'] * $trx['harga'];
                                // Cari index asli di array original
                                $realIndex = array_search($trx, $riwayatTransaksi, true);
                                $jenisClass = $trx['jenis'] == 'MASUK' ? 'success' : 'danger';
                                $jenisIcon = $trx['jenis'] == 'MASUK' ? 'arrow-down' : 'arrow-up';
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($trx['tanggal'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $jenisClass; ?>">
                                        <i class="fas fa-<?php echo $jenisIcon; ?>"></i>
                                        <?php echo $trx['jenis']; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($trx['nama']); ?></td>
                                <td><?php echo $trx['jumlah']; ?></td>
                                <td>Rp <?php echo number_format($trx['harga'], 0, ',', '.'); ?></td>
                                <td><strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong></td>
                                <td><?php echo isset($trx['bagian']) ? htmlspecialchars($trx['bagian']) : '-'; ?></td>
                                <td><?php echo isset($trx['penanggung_jawab']) ? htmlspecialchars($trx['penanggung_jawab']) : '-'; ?></td>
                                <td>
                                    <form method="POST" style="display: inline;" id="deleteForm<?php echo $trx['id']; ?>" 
                                          onsubmit="return false;">
                                        <input type="hidden" name="action" value="hapus_transaksi">
                                        <input type="hidden" name="id" value="<?php echo $trx['id']; ?>">
                                        <button type="button" onclick="showDeleteConfirm(<?php echo $trx['id']; ?>, '<?php echo htmlspecialchars($trx['nama']); ?>', '<?php echo $trx['jenis']; ?>', <?php echo $trx['jumlah']; ?>)" class="btn btn-small btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($riwayatTransaksi)): ?>
                            <tr>
                                <td colspan="8" class="no-data">
                                    <i class="fas fa-inbox"></i>
                                    <p>Belum ada transaksi</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Barang Masuk -->
        <div id="masuk" class="tab-panel">
            <div class="table-section">
                <div class="section-header">
                    <h3>Transaksi Barang Masuk</h3>
                    <div class="summary-info">
                        <span class="info-item">
                            <i class="fas fa-arrow-down"></i>
                            Total: <?php echo count($riwayatMasuk); ?> transaksi
                        </span>
                        <?php 
                        $totalNilaiMasuk = 0;
                        foreach ($riwayatMasuk as $trx) {
                            $totalNilaiMasuk += $trx['jumlah'] * $trx['harga'];
                        }
                        ?>
                        <span class="info-item">
                            <i class="fas fa-money-bill"></i>
                            Nilai: Rp <?php echo number_format($totalNilaiMasuk, 0, ',', '.'); ?>
                        </span>
                    </div>
                </div>
                
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th><i class="fas fa-calendar"></i> Tanggal</th>
                                <th><i class="fas fa-box"></i> Nama Barang</th>
                                <th><i class="fas fa-sort-numeric-up"></i> Jumlah</th>
                                <th><i class="fas fa-money-bill"></i> Harga Beli</th>
                                <th><i class="fas fa-calculator"></i> Total</th>
                                <th><i class="fas fa-cogs"></i> Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            foreach (array_reverse($riwayatMasuk) as $trx): 
                                $total = $trx['jumlah'] * $trx['harga'];
                                $realIndex = array_search($trx, $riwayatTransaksi);
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($trx['tanggal'])); ?></td>
                                <td><?php echo htmlspecialchars($trx['nama']); ?></td>
                                <td><?php echo $trx['jumlah']; ?></td>
                                <td>Rp <?php echo number_format($trx['harga'], 0, ',', '.'); ?></td>
                                <td><strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong></td>
                                <td>
                                    <form method="POST" style="display: inline;" id="deleteForm<?php echo $trx['id']; ?>" 
                                          onsubmit="return false;">
                                        <input type="hidden" name="action" value="hapus_transaksi">
                                        <input type="hidden" name="id" value="<?php echo $trx['id']; ?>">
                                        <button type="button" onclick="showDeleteConfirm(<?php echo $trx['id']; ?>, '<?php echo addslashes(htmlspecialchars($trx['nama'])); ?>', '<?php echo $trx['jenis']; ?>', <?php echo $trx['jumlah']; ?>)" class="btn btn-small btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($riwayatMasuk)): ?>
                            <tr>
                                <td colspan="7" class="no-data">Belum ada transaksi barang masuk</td>
                            </tr>
                            <?php else: ?>
                            <tr class="total-row">
                                <td colspan="5"><strong>TOTAL KESELURUHAN:</strong></td>
                                <td colspan="2"><strong>Rp <?php echo number_format($totalNilaiMasuk, 0, ',', '.'); ?></strong></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Barang Keluar -->
        <div id="keluar" class="tab-panel">
            <div class="table-section">
                <div class="section-header">
                    <h3>Transaksi Barang Keluar</h3>
                    <div class="summary-info">
                        <span class="info-item">
                            <i class="fas fa-arrow-up"></i>
                            Total: <?php echo count($riwayatKeluar); ?> transaksi
                        </span>
                        <?php 
                        $totalNilaiKeluar = 0;
                        foreach ($riwayatKeluar as $trx) {
                            $totalNilaiKeluar += $trx['jumlah'] * $trx['harga'];
                        }
                        ?>
                        <span class="info-item">
                            <i class="fas fa-money-bill"></i>
                            Nilai: Rp <?php echo number_format($totalNilaiKeluar, 0, ',', '.'); ?>
                        </span>
                    </div>
                </div>
                
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th><i class="fas fa-calendar"></i> Tanggal</th>
                                <th><i class="fas fa-box"></i> Nama Barang</th>
                                <th><i class="fas fa-sort-numeric-up"></i> Jumlah</th>
                                <th><i class="fas fa-building"></i> Bagian Tujuan</th>
                                <th><i class="fas fa-user"></i> Penanggung Jawab</th>
                                <th><i class="fas fa-money-bill"></i> Harga</th>
                                <th><i class="fas fa-calculator"></i> Total</th>
                                <th><i class="fas fa-cogs"></i> Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            foreach (array_reverse($riwayatKeluar) as $trx): 
                                $total = $trx['jumlah'] * $trx['harga'];
                                $realIndex = array_search($trx, $riwayatTransaksi);
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($trx['tanggal'])); ?></td>
                                <td><?php echo htmlspecialchars($trx['nama']); ?></td>
                                <td><?php echo $trx['jumlah']; ?></td>
                                <td><?php echo isset($trx['bagian']) ? htmlspecialchars($trx['bagian']) : '-'; ?></td>
                                <td><?php echo isset($trx['penanggung_jawab']) ? htmlspecialchars($trx['penanggung_jawab']) : '-'; ?></td>
                                <td>Rp <?php echo number_format($trx['harga'], 0, ',', '.'); ?></td>
                                <td><strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong></td>
                                <td>
                                    <form method="POST" style="display: inline;" id="deleteForm<?php echo $trx['id']; ?>" 
                                          onsubmit="return false;">
                                        <input type="hidden" name="action" value="hapus_transaksi">
                                        <input type="hidden" name="id" value="<?php echo $trx['id']; ?>">
                                        <button type="button" onclick="showDeleteConfirm(<?php echo $trx['id']; ?>, '<?php echo addslashes(htmlspecialchars($trx['nama'])); ?>', '<?php echo $trx['jenis']; ?>', <?php echo $trx['jumlah']; ?>)" class="btn btn-small btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($riwayatKeluar)): ?>
                            <tr>
                                <td colspan="7" class="no-data">Belum ada transaksi barang keluar</td>
                            </tr>
                            <?php else: ?>
                            <tr class="total-row">
                                <td colspan="5"><strong>TOTAL KESELURUHAN:</strong></td>
                                <td colspan="2"><strong>Rp <?php echo number_format($totalNilaiKeluar, 0, ',', '.'); ?></strong></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Tab management
function showTab(tabName, element) {
    // Hide all tab panels
    const tabPanels = document.querySelectorAll('.tab-panel');
    tabPanels.forEach(panel => panel.classList.remove('active'));
    
    // Remove active class from all tab buttons
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(btn => btn.classList.remove('active'));
    
    // Show selected tab panel
    document.getElementById(tabName).classList.add('active');
    
    // Add active class to clicked tab button
    if (element) {
        element.classList.add('active');
    }
}

// Filter by date range
function filterByDate() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    if (!startDate || !endDate) {
        alert('Pilih tanggal mulai dan akhir');
        return;
    }
    
    const start = new Date(startDate);
    const end = new Date(endDate);
    
    // Filter semua tabel
    const tables = document.querySelectorAll('.data-table tbody');
    
    tables.forEach(tbody => {
        const rows = tbody.querySelectorAll('tr');
        
        rows.forEach(row => {
            // Skip jika row adalah "no data" row
            if (row.querySelector('.no-data')) {
                return;
            }
            
            // Ambil kolom tanggal (biasanya kolom ke-2)
            const dateCell = row.cells[1];
            if (!dateCell) return;
            
            const dateText = dateCell.textContent.trim();
            
            // Parse tanggal dari format DD/MM/YYYY
            const parts = dateText.split('/');
            if (parts.length === 3) {
                const rowDate = new Date(parts[2], parts[1] - 1, parts[0]);
                
                // Tampilkan atau sembunyikan row berdasarkan range tanggal
                if (rowDate >= start && rowDate <= end) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    });
    
    // Tampilkan notifikasi
    const startFormatted = start.toLocaleDateString('id-ID');
    const endFormatted = end.toLocaleDateString('id-ID');
    
    // Buat dan tampilkan alert sukses
    const existingAlert = document.querySelector('.filter-alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    const alert = document.createElement('div');
    alert.className = 'filter-alert';
    alert.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #28a745; color: white; padding: 15px 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); z-index: 9999;';
    alert.innerHTML = `<i class="fas fa-check-circle"></i> Filter diterapkan: ${startFormatted} s/d ${endFormatted}`;
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 3000);
}

let deleteFormId = null;

function showDeleteConfirm(index, namaBarang, jenis, jumlah) {
    deleteFormId = 'deleteForm' + index;
    
    const jenisText = jenis === 'MASUK' ? 'Barang Masuk' : 'Barang Keluar';
    const jenisIcon = jenis === 'MASUK' ? 'fa-arrow-down' : 'fa-arrow-up';
    const jenisColor = jenis === 'MASUK' ? '#27ae60' : '#e74c3c';
    
    document.getElementById('deleteContent').innerHTML = `
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #7f8c8d; width: 40%;"><i class="fas fa-box"></i> Nama Barang:</td>
                <td style="padding: 8px 0; font-weight: bold; color: #2c3e50;">${namaBarang}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #7f8c8d;"><i class="fas ${jenisIcon}"></i> Jenis:</td>
                <td style="padding: 8px 0; font-weight: bold; color: ${jenisColor};">${jenisText}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #7f8c8d;"><i class="fas fa-sort-numeric-up"></i> Jumlah:</td>
                <td style="padding: 8px 0; font-weight: bold; color: #2c3e50;">${jumlah}</td>
            </tr>
        </table>
    `;
    
    document.getElementById('deleteModal').style.display = 'flex';
}

function proceedDelete() {
    if (deleteFormId) {
        document.getElementById('deleteModal').style.display = 'none';
        document.getElementById(deleteFormId).submit();
    }
}

function cancelDelete() {
    document.getElementById('deleteModal').style.display = 'none';
    deleteFormId = null;
}
</script>

<!-- Custom Delete Confirmation Modal -->
<div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 10px; max-width: 500px; width: 90%; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
        <div style="text-align: center; margin-bottom: 20px;">
            <i class="fas fa-exclamation-triangle" style="font-size: 60px; color: #e74c3c;"></i>
        </div>
        <h3 style="text-align: center; color: #2c3e50; margin-bottom: 15px;">Hapus Transaksi</h3>
        <div id="deleteContent" style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <!-- Content akan diisi oleh JavaScript -->
        </div>
        <p style="text-align: center; color: #7f8c8d; margin-bottom: 20px;">Yakin ingin menghapus transaksi ini?</p>
        <div style="display: flex; gap: 10px; justify-content: center;">
            <button type="button" onclick="cancelDelete()" style="padding: 10px 30px; background: #95a5a6; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                <i class="fas fa-times"></i> Batal
            </button>
            <button type="button" onclick="proceedDelete()" style="padding: 10px 30px; background: #e74c3c; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                <i class="fas fa-trash"></i> Ya, Hapus
            </button>
        </div>
    </div>
</div>

<?php include '../layout/footer.php'; ?>
<?php
session_start();

// Redirect ke login jika belum login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

// Set current page
$current_page = 'pengaturan';
$title = 'Pengaturan - Aplikasi Inventaris';
$message = '';
$message_type = '';

// Cek pesan dari session
if (isset($_SESSION['notification'])) {
    $message = $_SESSION['notification']['message'];
    $message_type = $_SESSION['notification']['type'];
    // Hapus dari session setelah diambil
    unset($_SESSION['notification']);
}

// Get database info
require_once '../config/config.php';
$inventaris = new ManajemenInventaris();

try {
    $riwayatTransaksi = $inventaris->getRiwayatTransaksi();
    $stokBarang = $inventaris->getStokBarang();
} catch (Exception $e) {
    // Jika error (misal setelah hapus data), set ke array kosong
    $riwayatTransaksi = [];
    $stokBarang = [];
}

// Load settings from session
$settings = $_SESSION['settings'] ?? [
    'app_name' => 'Sistem Manajemen Inventaris',
    'low_stock' => 5,
    'auto_backup' => 'daily',
    'notify_low_stock' => 1,
    'auto_save' => 1
];

// Include header
include '../layout/header.php';
include '../layout/sidebar.php';
?>

<div class="page-header">
    <div class="page-title">
        <h2><i class="fas fa-cog"></i> Pengaturan Sistem</h2>
        <p>Konfigurasi dan pengaturan aplikasi inventaris</p>
    </div>
</div>

<div class="page-content">
    <div class="settings-grid">
        <!-- General Settings -->
        <div class="settings-card">
            <div class="card-header">
                <h3><i class="fas fa-cogs"></i> Pengaturan Umum</h3>
            </div>
            <div class="settings-content">
                <div class="setting-item">
                    <label>Nama Aplikasi</label>
                    <input type="text" id="app_name" value="<?php echo htmlspecialchars($settings['app_name']); ?>" class="form-control">
                    <small>Nama ini akan muncul di header aplikasi</small>
                </div>
                
                <div class="setting-item">
                    <label>Batas Stok Rendah</label>
                    <input type="number" id="low_stock" value="<?php echo $settings['low_stock']; ?>" class="form-control" min="1">
                    <small>Barang dengan stok di bawah angka ini akan mendapat peringatan</small>
                </div>
                
                <div class="setting-item">
                    <label>Auto Backup</label>
                    <select id="auto_backup" class="form-control">
                        <option value="daily" <?php echo $settings['auto_backup'] == 'daily' ? 'selected' : ''; ?>>Harian</option>
                        <option value="weekly" <?php echo $settings['auto_backup'] == 'weekly' ? 'selected' : ''; ?>>Mingguan</option>
                        <option value="monthly" <?php echo $settings['auto_backup'] == 'monthly' ? 'selected' : ''; ?>>Bulanan</option>
                        <option value="never" <?php echo $settings['auto_backup'] == 'never' ? 'selected' : ''; ?>>Tidak Pernah</option>
                    </select>
                </div>
                
                <div class="setting-item">
                    <label class="checkbox-label">
                        <input type="checkbox" id="notify_low_stock" <?php echo $settings['notify_low_stock'] ? 'checked' : ''; ?>>
                        <span class="checkmark"></span>
                        Notifikasi Stok Rendah
                    </label>
                </div>
                
                <div class="setting-item">
                    <label class="checkbox-label">
                        <input type="checkbox" id="auto_save" <?php echo $settings['auto_save'] ? 'checked' : ''; ?>>
                        <span class="checkmark"></span>
                        Auto Save Transaksi
                    </label>
                </div>
            </div>
        </div>

        <!-- Data Management -->
        <div class="settings-card">
            <div class="card-header">
                <h3><i class="fas fa-database"></i> Manajemen Database</h3>
            </div>
            <div class="settings-content">
                <div class="setting-item">
                    <button class="btn btn-info" onclick="exportDatabase()">
                        <i class="fas fa-file-export"></i>
                        Export Database (SQL)
                    </button>
                    <small>Download database dalam format SQL</small>
                </div>
                
                <div class="setting-item">
                    <button class="btn btn-warning" onclick="clearDatabase()">
                        <i class="fas fa-trash-alt"></i>
                        Hapus Semua Data
                    </button>
                    <small>Hapus semua transaksi dan stok (tidak dapat dibatalkan)</small>
                </div>
            </div>
        </div>

      
    <!-- Save Button -->
    <div class="settings-actions">
        <button onclick="saveSettings()" class="btn btn-primary btn-large">
            <i class="fas fa-save"></i>
            Simpan Pengaturan
        </button>
    </div>
</div>

<script>
function showNotification(message, type) {
    // Buat elemen notifikasi
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: ${type === 'success' ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' : 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)'};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
        min-width: 300px;
        max-width: 400px;
    `;
    
    notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 12px;">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}" style="font-size: 24px;"></i>
            <div style="flex: 1;">
                <strong style="display: block; margin-bottom: 3px;">${type === 'success' ? 'Berhasil!' : 'Gagal!'}</strong>
                <span style="font-size: 14px; opacity: 0.95;">${message}</span>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer; opacity: 0.8; padding: 0; width: 24px; height: 24px;">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Hapus notifikasi setelah 5 detik
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// Tambahkan CSS animasi
if (!document.getElementById('notification-styles')) {
    const style = document.createElement('style');
    style.id = 'notification-styles';
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
}

function saveSettings() {
    // Collect all settings
    const settings = {
        app_name: document.getElementById('app_name').value,
        low_stock: document.getElementById('low_stock').value,
        auto_backup: document.getElementById('auto_backup').value,
        notify_low_stock: document.getElementById('notify_low_stock').checked ? 1 : 0,
        auto_save: document.getElementById('auto_save').checked ? 1 : 0
    };
    
    // Send to server
    fetch('save_settings.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(settings)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Pengaturan berhasil disimpan!', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Gagal menyimpan pengaturan: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Terjadi kesalahan: ' + error, 'error');
    });
}

function exportDatabase() {
    window.location.href = '../config/export_database.php';
}

function clearDatabase() {
    document.getElementById('clearModal').style.display = 'flex';
}

function proceedClear() {
    document.getElementById('clearModal').style.display = 'none';
    window.location.href = '../config/clear_data.php';
}

function cancelClear() {
    document.getElementById('clearModal').style.display = 'none';
}
</script>

<!-- Custom Clear Database Confirmation Modal -->
<div id="clearModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 10px; max-width: 500px; width: 90%; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
        <div style="text-align: center; margin-bottom: 20px;">
            <i class="fas fa-exclamation-triangle" style="font-size: 60px; color: #e74c3c;"></i>
        </div>
        <h3 style="text-align: center; color: #2c3e50; margin-bottom: 15px;">PERINGATAN!</h3>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <p style="color: #e74c3c; font-weight: bold; margin-bottom: 10px; text-align: center;">
                <i class="fas fa-trash-alt"></i> Hapus Semua Data
            </p>
            <p style="color: #7f8c8d; margin-bottom: 8px; text-align: center;">
                Anda akan menghapus <strong>SEMUA</strong> data transaksi dan stok dari database MySQL.
            </p>
            <p style="color: #e74c3c; font-weight: bold; text-align: center;">
                Tindakan ini TIDAK dapat dibatalkan!
            </p>
        </div>
        <p style="text-align: center; color: #7f8c8d; margin-bottom: 20px; font-weight: bold;">Yakin ingin menghapus semua data?</p>
        <div style="display: flex; gap: 10px; justify-content: center;">
            <button type="button" onclick="cancelClear()" style="padding: 10px 30px; background: #95a5a6; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                <i class="fas fa-times"></i> Batal
            </button>
            <button type="button" onclick="proceedClear()" style="padding: 10px 30px; background: #e74c3c; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                <i class="fas fa-trash"></i> Ya, Hapus
            </button>
        </div>
    </div>
</div>

<script>
// Tampilkan notifikasi jika ada pesan dari URL
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($message): ?>
        showNotification(<?php echo json_encode($message); ?>, <?php echo json_encode($message_type); ?>);
    <?php endif; ?>
});
</script>

<?php include '../layout/footer.php'; ?>
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
$current_page = 'transaksi_keluar';
$title = 'Transaksi Keluar - Aplikasi Inventaris';
$message = '';
$message_type = '';

// Inisialisasi inventaris
$inventaris = new ManajemenInventaris();

// Proses form
if ($_POST) {
    if (isset($_POST['action']) && $_POST['action'] === 'catat_keluar') {
        // Ambil harga dari database berdasarkan nama barang
        $harga = $inventaris->getHargaBarang($_POST['nama_barang']);
        
        $result = $inventaris->catatBarangKeluar(
            $_POST['nama_barang'],
            (int)$_POST['jumlah'],
            $harga,
            $_POST['tanggal'],
            $_POST['bagian'],
            $_POST['penanggung_jawab']
        );
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'error';
    }
}

// Get daftar barang yang tersedia
$daftarBarang = $inventaris->getDaftarBarang();

// Debug - hapus setelah selesai
// echo '<pre>'; print_r($daftarBarang); echo '</pre>'; die();

// Include header
include '../layout/header.php';
include '../layout/sidebar.php';
?>

<style>
/* Style untuk autocomplete dropdown */
.suggestions-box {
    position: absolute;
    background-color: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    max-height: 200px;
    overflow-y: auto;
    width: 100%;
    z-index: 1000;
    display: none;
    margin-top: 2px;
}

.suggestion-item {
    padding: 10px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    color: #333;
    background-color: white;
    transition: background-color 0.2s ease;
}

.suggestion-item:hover {
    background-color: #e3f2fd;
}

.suggestion-item.active {
    background-color: #bbdefb;
}

.suggestion-item:last-child {
    border-bottom: none;
}

.suggestion-name {
    font-weight: 500;
    color: #2c3e50;
}

.suggestion-stock {
    font-size: 0.9em;
    color: #7f8c8d;
    margin-left: 8px;
}
</style>

<div class="page-header">
    <div class="page-title">
        <h2><i class="fas fa-arrow-up"></i> Transaksi Barang Keluar</h2>
        <p>Catat transaksi barang keluar dari inventaris</p>
    </div>
</div>

<div class="page-content">
    <div class="form-container">
        <div class="form-card">
            <div class="card-header">
                <h3><i class="fas fa-edit"></i> Formulir Transaksi Keluar</h3>
                <p>Gunakan formulir ini untuk mencatat barang keluar. Pastikan semua data diisi dengan benar.</p>
            </div>
            
            <form method="POST" class="transaction-form" id="transactionForm" onsubmit="return confirmTransaction()">
                <input type="hidden" name="action" value="catat_keluar">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nama_barang">
                            <i class="fas fa-box"></i>
                            Nama Barang *
                        </label>
                        <div style="position: relative;">
                            <input type="text" 
                                   id="nama_barang" 
                                   name="nama_barang" 
                                   required 
                                   autocomplete="off"
                                   placeholder="Ketik nama barang..."
                                   oninput="showSuggestions(this.value)"
                                   onfocus="showSuggestions(this.value)"
                                   style="width: 100%;">
                            <div id="suggestions" class="suggestions-box"></div>
                        </div>
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
                        <small id="stok_info" style="color: #666; display: block; margin-top: 5px;"></small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="bagian">
                            <i class="fas fa-building"></i>
                            Bagian/Unit Tujuan *
                        </label>
                        <input type="text" id="bagian" name="bagian" required 
                               placeholder="Contoh: Aptika Kominfo">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="penanggung_jawab">
                            <i class="fas fa-user"></i>
                            Penanggung Jawab *
                        </label>
                        <input type="text" id="penanggung_jawab" name="penanggung_jawab" required 
                               placeholder="Nama penanggung jawab">
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
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-arrow-up"></i>
                        Catat Barang Keluar
                    </button>
                    <button type="reset" class="btn btn-secondary" onclick="resetForm()">
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

<!-- Custom Confirmation Modal -->
<div id="confirmModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 10px; max-width: 500px; width: 90%; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
        <div style="text-align: center; margin-bottom: 20px;">
            <i class="fas fa-exclamation-triangle" style="font-size: 60px; color: #FF9800;"></i>
        </div>
        <h3 style="text-align: center; color: #2c3e50; margin-bottom: 15px;">Konfirmasi Transaksi Keluar</h3>
        <div id="confirmContent" style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <!-- Content akan diisi oleh JavaScript -->
        </div>
        <p style="text-align: center; color: #7f8c8d; margin-bottom: 20px;">Apakah data sudah benar?</p>
        <div style="display: flex; gap: 10px; justify-content: center;">
            <button type="button" onclick="cancelTransaction()" style="padding: 10px 30px; background: #95a5a6; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                <i class="fas fa-times"></i> Batal
            </button>
            <button type="button" onclick="proceedTransaction()" style="padding: 10px 30px; background: #f44336; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                <i class="fas fa-check"></i> Ya, Catat
            </button>
        </div>
    </div>
</div>

<script>
// Data barang untuk pencarian
const dataBarang = [
    <?php 
    $total = count($daftarBarang);
    foreach ($daftarBarang as $index => $barang): 
    ?>{
        nama: <?php echo json_encode($barang['nama']); ?>,
        stok: <?php echo $barang['stok']; ?>,
        satuan: <?php echo json_encode($barang['satuan']); ?>,
        harga: <?php echo $barang['harga']; ?>
    }<?php if ($index < $total - 1) echo ','; ?>
    <?php endforeach; ?>
];

let selectedIndex = -1;
let formSubmissionAllowed = false;

// Konfirmasi sebelum submit
function confirmTransaction() {
    if (formSubmissionAllowed) {
        return true;
    }
    
    const namaBarang = document.getElementById('nama_barang').value;
    const jumlah = document.getElementById('jumlah').value;
    const bagian = document.getElementById('bagian').value;
    const penanggungJawab = document.getElementById('penanggung_jawab').value;
    const tanggal = document.getElementById('tanggal').value;
    
    if (!namaBarang || !jumlah || !bagian || !penanggungJawab || !tanggal) {
        alert('Mohon lengkapi semua field!');
        return false;
    }
    
    // Cari data barang
    const barang = dataBarang.find(b => b.nama.toLowerCase() === namaBarang.toLowerCase());
    
    if (!barang) {
        alert('Barang tidak ditemukan dalam stok!');
        return false;
    }
    
    // Format harga untuk tampilan
    const hargaFormatted = 'Rp ' + parseInt(barang.harga).toLocaleString('id-ID');
    const totalFormatted = 'Rp ' + (parseInt(barang.harga) * parseInt(jumlah)).toLocaleString('id-ID');
    
    // Format tanggal
    const tanggalObj = new Date(tanggal);
    const tanggalFormatted = tanggalObj.toLocaleDateString('id-ID', { 
        day: 'numeric', 
        month: 'long', 
        year: 'numeric' 
    });
    
    // Hitung sisa stok
    const sisaStok = barang.stok - parseInt(jumlah);
    const satuan = barang.satuan || window.currentSatuan || 'buah';
    const warningStok = sisaStok <= 5 
        ? `<div style="color: #f44336; margin-top: 10px;"><i class="fas fa-exclamation-triangle"></i> Peringatan: Stok akan menjadi rendah!</div>`
        : '';
    
    const statusStok = sisaStok === 0
        ? `<div style="color: #f44336;"><i class="fas fa-times-circle"></i> Stok setelah transaksi: <strong>HABIS</strong></div>`
        : `<div style="color: ${sisaStok <= 5 ? '#FF9800' : '#27ae60'};"><i class="fas fa-arrow-down"></i> Stok setelah transaksi: <strong>${sisaStok} ${satuan}</strong></div>`;
    
    // Isi konten modal
    document.getElementById('confirmContent').innerHTML = `
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #7f8c8d; width: 40%;"><i class="fas fa-box"></i> Nama Barang:</td>
                <td style="padding: 8px 0; font-weight: bold; color: #2c3e50;">${namaBarang}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #7f8c8d;"><i class="fas fa-cubes"></i> Stok Saat Ini:</td>
                <td style="padding: 8px 0; font-weight: bold; color: #2196F3;">${barang.stok} ${satuan}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #7f8c8d;"><i class="fas fa-sort-numeric-down"></i> Jumlah Keluar:</td>
                <td style="padding: 8px 0; font-weight: bold; color: #e74c3c;">${jumlah} ${satuan}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #7f8c8d;"><i class="fas fa-building"></i> Bagian Tujuan:</td>
                <td style="padding: 8px 0; font-weight: bold; color: #2c3e50;">${bagian}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #7f8c8d;"><i class="fas fa-user"></i> Penanggung Jawab:</td>
                <td style="padding: 8px 0; font-weight: bold; color: #2c3e50;">${penanggungJawab}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #7f8c8d;"><i class="fas fa-money-bill"></i> Harga per ${satuan}:</td>
                <td style="padding: 8px 0; font-weight: bold; color: #2c3e50;">${hargaFormatted}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #7f8c8d;"><i class="fas fa-calculator"></i> Total Nilai:</td>
                <td style="padding: 8px 0; font-weight: bold; color: #e74c3c; font-size: 18px;">${totalFormatted}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #7f8c8d;"><i class="fas fa-calendar"></i> Tanggal:</td>
                <td style="padding: 8px 0; font-weight: bold; color: #2c3e50;">${tanggalFormatted}</td>
            </tr>
        </table>
        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
            ${statusStok}
            ${warningStok}
        </div>
    `;
    
    // Tampilkan modal
    document.getElementById('confirmModal').style.display = 'flex';
    
    return false;
}

function proceedTransaction() {
    formSubmissionAllowed = true;
    document.getElementById('confirmModal').style.display = 'none';
    document.getElementById('transactionForm').submit();
}

function cancelTransaction() {
    document.getElementById('confirmModal').style.display = 'none';
}

function showSuggestions(value) {
    const suggestionsBox = document.getElementById('suggestions');
    const input = document.getElementById('nama_barang');
    
    // Jika input kosong, sembunyikan suggestions
    if (value.trim() === '') {
        suggestionsBox.style.display = 'none';
        updateStokInfo(null);
        return;
    }
    
    // Filter barang berdasarkan input
    const filtered = dataBarang.filter(barang => 
        barang.nama.toLowerCase().includes(value.toLowerCase())
    );
    
    // Jika tidak ada hasil, sembunyikan suggestions
    if (filtered.length === 0) {
        suggestionsBox.style.display = 'none';
        updateStokInfo(null);
        return;
    }
    
    // Tampilkan suggestions
    suggestionsBox.innerHTML = '';
    filtered.forEach((barang, index) => {
        const div = document.createElement('div');
        div.className = 'suggestion-item';
        div.innerHTML = `
            <span class="suggestion-name">${barang.nama}</span>
            <span class="suggestion-stock">Stok: ${barang.stok} ${barang.satuan}</span>
        `;
        div.onclick = function() {
            selectSuggestion(barang);
        };
        suggestionsBox.appendChild(div);
    });
    
    suggestionsBox.style.display = 'block';
    selectedIndex = -1;
}

function selectSuggestion(barang) {
    const input = document.getElementById('nama_barang');
    const suggestionsBox = document.getElementById('suggestions');
    
    input.value = barang.nama;
    suggestionsBox.style.display = 'none';
    updateStokInfo(barang);
}

function updateStokInfo(barang) {
    const stokInfo = document.getElementById('stok_info');
    const jumlahInput = document.getElementById('jumlah');
    
    if (barang) {
        stokInfo.textContent = `Stok tersedia: ${barang.stok} ${barang.satuan}`;
        stokInfo.style.color = barang.stok <= 5 ? '#dc3545' : '#28a745';
        jumlahInput.setAttribute('max', barang.stok);
        
        // Simpan satuan di window global
        window.currentSatuan = barang.satuan;
    } else {
        stokInfo.textContent = '';
        jumlahInput.removeAttribute('max');
    }
}

// Tutup suggestions ketika klik di luar
document.addEventListener('click', function(e) {
    const suggestionsBox = document.getElementById('suggestions');
    const input = document.getElementById('nama_barang');
    
    if (e.target !== input && e.target !== suggestionsBox) {
        suggestionsBox.style.display = 'none';
    }
});

// Keyboard navigation
document.getElementById('nama_barang').addEventListener('keydown', function(e) {
    const suggestionsBox = document.getElementById('suggestions');
    const items = suggestionsBox.getElementsByClassName('suggestion-item');
    
    if (suggestionsBox.style.display === 'none') return;
    
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
        highlightItem(items);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        selectedIndex = Math.max(selectedIndex - 1, 0);
        highlightItem(items);
    } else if (e.key === 'Enter' && selectedIndex >= 0) {
        e.preventDefault();
        items[selectedIndex].click();
    } else if (e.key === 'Escape') {
        suggestionsBox.style.display = 'none';
    }
});

function highlightItem(items) {
    for (let i = 0; i < items.length; i++) {
        if (i === selectedIndex) {
            items[i].style.backgroundColor = '#bbdefb';
            items[i].classList.add('active');
        } else {
            items[i].style.backgroundColor = 'white';
            items[i].classList.remove('active');
        }
    }
    if (selectedIndex >= 0 && items[selectedIndex]) {
        items[selectedIndex].scrollIntoView({ block: 'nearest' });
    }
}

function resetForm() {
    document.getElementById('stok_info').textContent = '';
    document.getElementById('jumlah').removeAttribute('max');
    document.getElementById('suggestions').style.display = 'none';
}

// Validasi jumlah tidak melebihi stok
document.getElementById('jumlah').addEventListener('input', function() {
    const max = this.getAttribute('max');
    if (max && parseInt(this.value) > parseInt(max)) {
        this.value = max;
    }
});
</script>

<?php include '../layout/footer.php'; ?>

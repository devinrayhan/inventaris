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
$current_page = 'laporan';
$title = 'Laporan - Aplikasi Inventaris';
$message = '';
$message_type = '';

// Include header
include '../layout/header.php';
include '../layout/sidebar.php';

// Inisialisasi inventaris
$inventaris = new ManajemenInventaris();
$stokBarang = $inventaris->getStokBarang();
$riwayatTransaksi = $inventaris->getRiwayatTransaksi();
?>

<div class="page-header">
    <div class="page-title">
        <h2><i class="fas fa-chart-line"></i> Laporan Inventaris</h2>
        <p>Analisis dan statistik data inventaris</p>
    </div>
    <div class="page-actions">
        <div class="date-filter" style="display: flex; gap: 10px; align-items: center; margin-right: 15px;">
            <input type="date" id="startDate" class="form-control" value="<?php echo date('Y-01-01'); ?>" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            <span>s/d</span>
            <input type="date" id="endDate" class="form-control" value="<?php echo date('Y-m-d'); ?>" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            <button onclick="updateChart()" class="btn btn-primary" style="padding: 8px 15px;">
                <i class="fas fa-sync"></i> Update
            </button>
        </div>
    </div>
</div>

<div class="page-content">
    <!-- Chart Section -->
    <div class="chart-section">
        <div class="chart-container" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <div class="chart-header" style="margin-bottom: 20px;">
                <h3><i class="fas fa-chart-bar"></i> Grafik Transaksi Inventaris</h3>
                <p style="color: #7f8c8d; font-size: 14px; margin-top: 5px;">Visualisasi transaksi barang masuk dan keluar berdasarkan periode <span style="color: #2ecc71;">● Masuk</span> <span style="color: #e74c3c;">● Keluar</span></p>
            </div>
            <div style="position: relative; height: 400px;">
                <canvas id="chartPengeluaran"></canvas>
            </div>
        </div>
    </div>

    <!-- Report Cards -->
    <div class="report-grid">
        <div class="report-card">
            <div class="report-header">
                <h3><i class="fas fa-arrow-down"></i> Barang Masuk</h3>
            </div>
            <div class="report-content">
                <?php 
                $riwayatMasuk = $inventaris->getRiwayatByJenis('MASUK');
                $totalMasuk = 0;
                $nilaiMasuk = 0;
                foreach ($riwayatMasuk as $trx) {
                    $totalMasuk += $trx['jumlah'];
                    $nilaiMasuk += $trx['jumlah'] * $trx['harga'];
                }
                ?>
                <div class="stat">
                    <span class="stat-label">Total Transaksi:</span>
                    <span class="stat-value"><?php echo count($riwayatMasuk); ?></span>
                </div>
                <div class="stat">
                    <span class="stat-label">Total Barang:</span>
                    <span class="stat-value"><?php echo $totalMasuk; ?></span>
                </div>
                <div class="stat">
                    <span class="stat-label">Total Nilai:</span>
                    <span class="stat-value">Rp <?php echo number_format($nilaiMasuk, 0, ',', '.'); ?></span>
                </div>
            </div>
        </div>
        
        <div class="report-card">
            <div class="report-header">
                <h3><i class="fas fa-arrow-up"></i> Barang Keluar</h3>
            </div>
            <div class="report-content">
                <?php 
                $riwayatKeluar = $inventaris->getRiwayatByJenis('KELUAR');
                $totalKeluar = 0;
                $nilaiKeluar = 0;
                foreach ($riwayatKeluar as $trx) {
                    $totalKeluar += $trx['jumlah'];
                    $nilaiKeluar += $trx['jumlah'] * $trx['harga'];
                }
                ?>
                <div class="stat">
                    <span class="stat-label">Total Transaksi:</span>
                    <span class="stat-value"><?php echo count($riwayatKeluar); ?></span>
                </div>
                <div class="stat">
                    <span class="stat-label">Total Barang:</span>
                    <span class="stat-value"><?php echo $totalKeluar; ?></span>
                </div>
                <div class="stat">
                    <span class="stat-label">Total Nilai:</span>
                    <span class="stat-value">Rp <?php echo number_format($nilaiKeluar, 0, ',', '.'); ?></span>
                </div>
            </div>
        </div>
        
        <div class="report-card">
            <div class="report-header">
                <h3><i class="fas fa-warehouse"></i> Stok Saat Ini</h3>
            </div>
            <div class="report-content">
                <div class="stat">
                    <span class="stat-label">Jenis Barang:</span>
                    <span class="stat-value"><?php echo count($stokBarang); ?></span>
                </div>
                <div class="stat">
                    <span class="stat-label">Total Stok:</span>
                    <span class="stat-value"><?php echo array_sum($stokBarang); ?></span>
                </div>
                <div class="stat">
                    <span class="stat-label">Stok Rendah:</span>
                    <span class="stat-value">
                        <?php 
                        $lowStock = 0;
                        foreach ($stokBarang as $jumlah) {
                            if ($jumlah <= 5) $lowStock++;
                        }
                        echo $lowStock;
                        ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Items -->
    <div class="top-items-section">
        <div class="section-header">
            <h3><i class="fas fa-star"></i> Barang Terpopuler</h3>
        </div>
        
        <div class="top-items-grid">
            <div class="top-items-card">
                <h4>Paling Sering Masuk</h4>
                <div class="item-list">
                    <?php 
                    $itemMasuk = [];
                    foreach ($riwayatMasuk as $trx) {
                        $itemMasuk[$trx['nama']] = ($itemMasuk[$trx['nama']] ?? 0) + 1;
                    }
                    arsort($itemMasuk);
                    $topMasuk = array_slice($itemMasuk, 0, 5, true);
                    
                    foreach ($topMasuk as $nama => $count):
                    ?>
                    <div class="item">
                        <span class="item-name"><?php echo htmlspecialchars($nama); ?></span>
                        <span class="item-count"><?php echo $count; ?>x</span>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($topMasuk)): ?>
                    <p class="no-data">Belum ada data</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="top-items-card">
                <h4>Paling Sering Keluar</h4>
                <div class="item-list">
                    <?php 
                    $itemKeluar = [];
                    foreach ($riwayatKeluar as $trx) {
                        $itemKeluar[$trx['nama']] = ($itemKeluar[$trx['nama']] ?? 0) + 1;
                    }
                    arsort($itemKeluar);
                    $topKeluar = array_slice($itemKeluar, 0, 5, true);
                    
                    foreach ($topKeluar as $nama => $count):
                    ?>
                    <div class="item">
                        <span class="item-name"><?php echo htmlspecialchars($nama); ?></span>
                        <span class="item-count"><?php echo $count; ?>x</span>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($topKeluar)): ?>
                    <p class="no-data">Belum ada data</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Data transaksi dari PHP
const riwayatTransaksi = <?php echo json_encode($riwayatTransaksi); ?>;

let chartInstance = null;

function filterTransaksiByDate(startDate, endDate) {
    const start = new Date(startDate);
    start.setHours(0, 0, 0, 0);
    
    const end = new Date(endDate);
    end.setHours(23, 59, 59, 999);
    
    return riwayatTransaksi.filter(trx => {
        const trxDate = new Date(trx.tanggal);
        // Show all transactions (both MASUK and KELUAR)
        return trxDate >= start && trxDate <= end;
    });
}

function groupByDate(transaksi) {
    const grouped = {};
    
    transaksi.forEach(trx => {
        // Group by date only
        const key = trx.tanggal;
        if (!grouped[key]) {
            grouped[key] = {
                tanggal: trx.tanggal,
                jumlah: 0,
                nilai: 0,
                nilaiMasuk: 0,
                nilaiKeluar: 0,
                items: []
            };
        }
        const nilai = parseInt(trx.jumlah) * parseInt(trx.harga);
        grouped[key].jumlah += parseInt(trx.jumlah);
        grouped[key].nilai += nilai;
        
        if (trx.jenis === 'MASUK') {
            grouped[key].nilaiMasuk += nilai;
        } else {
            grouped[key].nilaiKeluar += nilai;
        }
        
        grouped[key].items.push({
            nama: trx.nama,
            jenis: trx.jenis,
            jumlah: trx.jumlah,
            nilai: nilai
        });
    });
    
    return grouped;
}

function updateChart() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    if (!startDate || !endDate) {
        alert('Pilih tanggal mulai dan akhir');
        return;
    }
    
    // Filter transaksi
    const filteredTransaksi = filterTransaksiByDate(startDate, endDate);
    const groupedData = groupByDate(filteredTransaksi);
    
    // Siapkan data untuk chart
    const sortedKeys = Object.keys(groupedData).sort();
    
    const dataValues = sortedKeys.map(key => groupedData[key].nilai);
    const dataJumlah = sortedKeys.map(key => groupedData[key].jumlah);
    
    // Format label dengan tanggal saja
    const formattedLabels = sortedKeys.map(key => {
        const d = new Date(key);
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    });
    
    // Destroy chart lama jika ada
    if (chartInstance) {
        chartInstance.destroy();
    }
    
    // Prepare colors: green if more MASUK, red if more KELUAR, blue if equal
    const backgroundColors = sortedKeys.map(key => {
        const data = groupedData[key];
        if (data.nilaiMasuk > data.nilaiKeluar) {
            return 'rgba(46, 204, 113, 0.7)'; // Green for MASUK dominant
        } else if (data.nilaiKeluar > data.nilaiMasuk) {
            return 'rgba(231, 76, 60, 0.7)'; // Red for KELUAR dominant
        } else {
            return 'rgba(52, 152, 219, 0.7)'; // Blue for balanced
        }
    });
    const borderColors = sortedKeys.map(key => {
        const data = groupedData[key];
        if (data.nilaiMasuk > data.nilaiKeluar) {
            return 'rgba(46, 204, 113, 1)';
        } else if (data.nilaiKeluar > data.nilaiMasuk) {
            return 'rgba(231, 76, 60, 1)';
        } else {
            return 'rgba(52, 152, 219, 1)';
        }
    });
    
    // Buat chart baru
    const ctx = document.getElementById('chartPengeluaran').getContext('2d');
    chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: formattedLabels,
            datasets: [{
                label: 'Nilai Transaksi (Rp)',
                data: dataValues,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 2,
                yAxisID: 'y'
            }, {
                label: 'Jumlah Barang',
                data: dataJumlah,
                backgroundColor: 'rgba(52, 152, 219, 0.7)',
                borderColor: 'rgba(52, 152, 219, 1)',
                borderWidth: 2,
                type: 'line',
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                title: {
                    display: false
                },
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                if (context.datasetIndex === 0) {
                                    label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                } else {
                                    label += context.parsed.y + ' unit';
                                }
                            }
                            return label;
                        },
                        afterLabel: function(context) {
                            const key = sortedKeys[context.dataIndex];
                            const data = groupedData[key];
                            let details = [];
                            
                            if (data.nilaiMasuk > 0) {
                                details.push('Masuk: Rp ' + data.nilaiMasuk.toLocaleString('id-ID'));
                            }
                            if (data.nilaiKeluar > 0) {
                                details.push('Keluar: Rp ' + data.nilaiKeluar.toLocaleString('id-ID'));
                            }
                            
                            details.push('---');
                            data.items.forEach(item => {
                                const icon = item.jenis === 'MASUK' ? '↓' : '↑';
                                details.push(icon + ' ' + item.nama + ' (' + item.jumlah + ')');
                            });
                            
                            return details;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Nilai Transaksi (Rp)'
                    },
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Jumlah Barang (unit)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
    
    // Update info
    const totalNilai = dataValues.reduce((a, b) => a + b, 0);
    const totalBarang = dataJumlah.reduce((a, b) => a + b, 0);
    
    console.log('Total Pengeluaran: Rp ' + totalNilai.toLocaleString('id-ID'));
    console.log('Total Barang Keluar: ' + totalBarang + ' unit');
}

// Load chart pertama kali
document.addEventListener('DOMContentLoaded', function() {
    updateChart();
});

function generateReport() {
    alert('Fitur generate PDF akan segera tersedia');
}
</script>

<?php include '../layout/footer.php'; ?>
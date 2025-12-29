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
$current_page = 'excel';
$title = 'Excel - Aplikasi Inventaris';
$message = '';
$message_type = '';

// Inisialisasi inventaris
$inventaris = new ManajemenInventaris();

// Include header
include '../layout/header.php';
include '../layout/sidebar.php';
?>

<div class="page-header">
    <div class="page-title">
        <h2><i class="fas fa-file-excel"></i> Manajemen Excel</h2>
        <p>Export dan Import data inventaris dalam format Excel</p>
    </div>
</div>

<div class="page-content">
    <?php if (isset($_SESSION['import_message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['import_type']; ?>">
        <i class="fas fa-<?php echo $_SESSION['import_type'] == 'success' ? 'check-circle' : ($_SESSION['import_type'] == 'warning' ? 'exclamation-triangle' : 'times-circle'); ?>"></i>
        <div class="alert-message">
            <?php 
            echo $_SESSION['import_message'];
            unset($_SESSION['import_message']);
            unset($_SESSION['import_type']);
            ?>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="excel-grid">
        <!-- Export Section -->
        <div class="excel-card">
            <div class="card-header">
                <div class="card-icon export-icon">
                    <i class="fas fa-download"></i>
                </div>
                <h3>Export Data ke Excel</h3>
                <p>Download data inventaris dalam format Excel (.xlsx atau .csv)</p>
            </div>
            
            <div class="card-content">
                <div class="export-options">
                    <div class="option-group">
                        <label class="option-title">
                            <i class="fas fa-table"></i>
                            Pilih Data yang Akan Di-export:
                        </label>
                        
                        <div class="selectable-cards">
                            <div class="select-card active" onclick="toggleCard(this, 'export_barang')">
                                <input type="checkbox" id="export_barang" checked style="display: none;">
                                <div class="card-icon-wrapper">
                                    <i class="fas fa-boxes"></i>
                                </div>
                                <div class="card-text">
                                    <h4>Data Barang</h4>
                                    <p>Stok inventaris</p>
                                </div>
                                <div class="card-check">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                            
                            <div class="select-card active" onclick="toggleCard(this, 'export_transaksi')">
                                <input type="checkbox" id="export_transaksi" checked style="display: none;">
                                <div class="card-icon-wrapper">
                                    <i class="fas fa-exchange-alt"></i>
                                </div>
                                <div class="card-text">
                                    <h4>Riwayat Transaksi</h4>
                                    <p>Masuk & Keluar</p>
                                </div>
                                <div class="card-check">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="option-group">
                        <label class="option-title">
                            <i class="fas fa-file-alt"></i>
                            Format File:
                        </label>
                        
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="format" value="xlsx" checked>
                                <span class="radio-dot"></span>
                                <span class="label-text">Excel (.xlsx) - Recommended</span>
                            </label>
                            
                            <label class="radio-label">
                                <input type="radio" name="format" value="csv">
                                <span class="radio-dot"></span>
                                <span class="label-text">CSV (.csv) - Universal</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="option-group">
                        <label class="option-title">
                            <i class="fas fa-calendar-alt"></i>
                            Filter Periode:
                        </label>
                        
                        <div style="display: flex; gap: 15px; align-items: center;">
                            <div style="flex: 1;">
                                <select id="filterMonth" class="form-control" style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                                    <option value="">Semua Bulan</option>
                                    <option value="1">Januari</option>
                                    <option value="2">Februari</option>
                                    <option value="3">Maret</option>
                                    <option value="4">April</option>
                                    <option value="5">Mei</option>
                                    <option value="6">Juni</option>
                                    <option value="7">Juli</option>
                                    <option value="8">Agustus</option>
                                    <option value="9">September</option>
                                    <option value="10">Oktober</option>
                                    <option value="11">November</option>
                                    <option value="12">Desember</option>
                                </select>
                            </div>
                            
                            <div style="flex: 1;">
                                <select id="filterYear" class="form-control" style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                                    <option value="">Semua Tahun</option>
                                    <?php 
                                    $currentYear = date('Y');
                                    for ($year = $currentYear - 2; $year <= $currentYear + 1; $year++) {
                                        $selected = ($year == $currentYear) ? 'selected' : '';
                                        echo "<option value='$year' $selected>$year</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div style="margin-top: 10px; padding: 8px 12px; background: #f0fdf4; border-left: 3px solid #10b981; border-radius: 4px; font-size: 13px; color: #065f46;">
                            <i class="fas fa-info-circle"></i> Pilih bulan dan tahun untuk export data periode tertentu, atau biarkan kosong untuk export semua data
                        </div>
                    </div>
                    
                    <button type="button" onclick="exportData()" class="btn btn-success btn-large">
                        <i class="fas fa-download"></i>
                        Export ke Excel
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Import Section -->
        <div class="excel-card">
            <div class="card-header">
                <div class="card-icon import-icon">
                    <i class="fas fa-upload"></i>
                </div>
                <h3>Import Data dari Excel</h3>
                <p>Upload file Excel untuk menambahkan data ke inventaris</p>
            </div>
            
            <div class="card-content">
                <!-- Template Section Inside Import -->
                <div class="import-template-section">
                    <h4><i class="fas fa-file-download"></i> 1. Download Template CSV</h4>
                    <p>Download template CSV sesuai jenis data yang ingin diimport:</p>
                    <div class="template-buttons-inline">
                        <a href="download_csv_template.php?type=transaksi" class="btn-template">
                            <i class="fas fa-exchange-alt"></i> Template Transaksi (CSV)
                        </a>
                    </div>
                    <div style="margin-top: 12px; padding: 10px; background: #eff6ff; border-left: 3px solid #3b82f6; border-radius: 4px; font-size: 13px; color: #1e40af;">
                        <i class="fas fa-info-circle"></i> <strong>Edit dengan Excel:</strong> Buka file CSV → Edit data → Save (tetap format CSV)
                    </div>
                </div>
                
                <div class="section-divider"></div>
                
                <!-- Upload Section -->
                <div class="import-upload-section">
                    <h4><i class="fas fa-cloud-upload-alt"></i> 2. Upload File</h4>
                
                <div class="import-area">
                    <form id="importForm" enctype="multipart/form-data" method="POST" action="process_import.php">
                        <div class="upload-zone" id="uploadZone" onclick="document.getElementById('fileInput').click()">
                            <input type="file" id="fileInput" name="excel_file" accept=".csv" style="display: none;" onchange="handleFileSelect(this)">
                            
                            <div class="upload-content" id="uploadContent">
                                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                <h4>Drag & Drop File Excel Di Sini</h4>
                                <p>atau klik untuk browse file</p>
                                <small>Format: .csv (Max 5MB)</small>
                            </div>
                            
                            <div class="file-preview" id="filePreview" style="display: none;">
                                <i class="fas fa-file-excel file-icon"></i>
                                <div class="file-info">
                                    <span class="file-name" id="fileName"></span>
                                    <span class="file-size" id="fileSize"></span>
                                </div>
                                <button type="button" onclick="clearFile(event)" class="btn-remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="import-options">
                            <label class="checkbox-label">
                                <input type="checkbox" name="update_existing" id="update_existing">
                                <span class="checkmark"></span>
                                <span class="label-text">Update data jika sudah ada (berdasarkan nama barang)</span>
                            </label>
                            
                            <label class="checkbox-label">
                                <input type="checkbox" name="skip_errors" id="skip_errors" checked>
                                <span class="checkmark"></span>
                                <span class="label-text">Lewati baris yang error dan lanjutkan import</span>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-large" id="importBtn" disabled>
                            <i class="fas fa-upload"></i>
                            Import Data
                        </button>
                    </form>
                </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Info Section -->
    <div class="info-section">
        <div class="info-card">
            <h4><i class="fas fa-info-circle"></i> Panduan Import Excel</h4>
            <ol>
                <li>Download template Excel yang sesuai dengan jenis data</li>
                <li>Isi data sesuai dengan format di template</li>
                <li>Pastikan tidak ada baris kosong di tengah data</li>
                <li>Upload file Excel yang sudah diisi</li>
                <li>Klik tombol Import untuk memproses data</li>
            </ol>
        </div>
        
        <div class="info-card">
            <h4><i class="fas fa-exclamation-triangle"></i> Perhatian</h4>
            <ul>
                <li>File maksimal 5MB</li>
                <li>Format yang didukung: .csv</li>
                <li>Kolom wajib harus diisi (tidak boleh kosong)</li>
                <li>Untuk tanggal gunakan format: DD/MM/YYYY atau YYYY-MM-DD</li>
                <li>Backup data sebelum melakukan import besar</li>
            </ul>
        </div>
    </div>
</div>

<style>
body {
    background: #F3F4F6;
}

.page-content {
    background: #F3F4F6;
}

.excel-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 30px;
    margin-bottom: 30px;
}

.alert {
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    display: flex;
    align-items: flex-start;
    gap: 15px;
    animation: slideDown 0.3s ease-out;
}

.alert i {
    font-size: 24px;
    margin-top: 2px;
}

.alert-message {
    flex: 1;
    line-height: 1.6;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-success i {
    color: #28a745;
}

.alert-warning {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeeba;
}

.alert-warning i {
    color: #ffc107;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-error i {
    color: #dc3545;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.excel-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.3s ease;
}

.excel-card:hover {
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

.excel-card .card-header {
    padding: 30px;
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    color: white;
    text-align: center;
}

.card-icon {
    width: 80px;
    height: 80px;
    background: rgba(255,255,255,0.15);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 36px;
    backdrop-filter: blur(10px);
}

.excel-card .card-header h3 {
    margin: 0 0 10px 0;
    font-size: 22px;
    color: white;
}

.excel-card .card-header p {
    margin: 0;
    color: white;
    opacity: 0.95;
    font-size: 14px;
}

.card-content {
    padding: 30px;
}

/* Import Template Section */
.import-template-section {
    margin-bottom: 25px;
}

.import-template-section h4,
.import-upload-section h4 {
    color: #1e3a8a;
    font-size: 16px;
    margin: 0 0 12px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.import-template-section h4 i {
    color: #10b981;
}

.import-upload-section h4 i {
    color: #3b82f6;
}

.import-template-section p {
    color: #6b7280;
    font-size: 13px;
    margin: 0 0 15px 0;
}

.template-buttons-inline {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-template {
    flex: 1;
    min-width: 180px;
    padding: 12px 20px;
    border: 2px solid #10b981;
    background: white;
    color: #059669;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-template:hover {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
}

.section-divider {
    height: 1px;
    background: linear-gradient(to right, transparent, #e5e7eb, transparent);
    margin: 25px 0;
    position: relative;
}

.section-divider::after {
    content: '';
    position: absolute;
    top: -4px;
    left: 50%;
    transform: translateX(-50%);
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
}

.import-upload-section {
    margin-bottom: 0;
}

.export-options, .import-area {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.option-group {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.option-title {
    font-weight: 600;
    color: #2c3e50;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.option-group {
    margin-bottom: 30px;
}

/* Selectable Cards Style */
.selectable-cards {
    display: grid;
    gap: 15px;
    margin-top: 15px;
}

.select-card {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.select-card:hover {
    border-color: #3b82f6;
    background: #eff6ff;
    transform: translateX(5px);
}

.select-card.active {
    border-color: #3b82f6;
    background: #eff6ff;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.select-card .card-icon-wrapper {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    flex-shrink: 0;
}

.select-card .card-text {
    flex: 1;
}

.select-card .card-text h4 {
    margin: 0 0 5px 0;
    color: #1f2937;
    font-size: 16px;
    font-weight: 600;
}

.select-card .card-text p {
    margin: 0;
    color: #6b7280;
    font-size: 13px;
}

.select-card .card-check {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #d1d5db;
    transition: all 0.3s ease;
}

.select-card.active .card-check {
    color: #10b981;
}

.checkbox-group, .radio-group {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding-left: 10px;
}

.checkbox-label, .radio-label {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    padding: 10px;
    border-radius: 6px;
    transition: background 0.2s;
}

.checkbox-label:hover, .radio-label:hover {
    background: #f8f9fa;
}

.checkbox-label input[type="checkbox"],
.radio-label input[type="radio"] {
    display: none;
}

.checkmark {
    width: 20px;
    height: 20px;
    border: 2px solid #ddd;
    border-radius: 4px;
    position: relative;
    transition: all 0.2s;
}

.checkbox-label input:checked ~ .checkmark {
    background: #667eea;
    border-color: #667eea;
}

.checkbox-label input:checked ~ .checkmark:after {
    content: '\f00c';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 12px;
}

.radio-dot {
    width: 20px;
    height: 20px;
    border: 2px solid #ddd;
    border-radius: 50%;
    position: relative;
    transition: all 0.2s;
}

.radio-label input:checked ~ .radio-dot {
    border-color: #1e3a8a;
}

.radio-label input:checked ~ .radio-dot:after {
    content: '';
    position: absolute;
    width: 10px;
    height: 10px;
    background: #1e3a8a;
    border-radius: 50%;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.label-text {
    color: #2c3e50;
    font-size: 14px;
}

.upload-zone {
    border: 2px dashed #3b82f6;
    border-radius: 16px;
    padding: 60px 40px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #EFF6FF;
    position: relative;
    overflow: hidden;
}

.upload-zone::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.05) 0%, transparent 70%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.upload-zone:hover {
    border-color: #2563eb;
    background: #dbeafe;
    transform: scale(1.01);
}

.upload-zone:hover::before {
    opacity: 1;
}

.upload-zone.dragover {
    border-color: #10b981;
    background: #d1fae5;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
}

.upload-icon {
    font-size: 64px;
    color: #3b82f6;
    margin-bottom: 20px;
    animation: float 3s ease-in-out infinite;
    text-align: center;
    display: block;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

.upload-content {
    text-align: center;
    width: 100%;
}

.upload-content * {
    text-align: center !important;
    margin-left: auto;
    margin-right: auto;
}

.upload-content h4 {
    color: #1e3a8a;
    margin: 0 auto 10px auto;
    font-size: 18px;
    font-weight: 600;
    display: block;
}

.upload-content p {
    color: #3b82f6;
    margin: 0 auto 15px auto;
    font-size: 14px;
    display: block;
}

.upload-content small {
    color: #6b7280;
    font-size: 13px;
    background: white;
    padding: 6px 12px;
    border-radius: 20px;
    display: block;
    width: fit-content;
    margin: 0 auto;
}

.file-preview {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: white;
    border-radius: 8px;
}

.file-icon {
    font-size: 40px;
    color: #27ae60;
}

.file-info {
    flex: 1;
    text-align: left;
}

.file-name {
    display: block;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 5px;
}

.file-size {
    display: block;
    font-size: 13px;
    color: #7f8c8d;
}

.btn-remove {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: #e74c3c;
    color: white;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-remove:hover {
    background: #c0392b;
    transform: scale(1.1);
}

.import-options {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

/* Button Styles */
.btn {
    padding: 14px 28px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 15px;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
}

.btn-large {
    width: 100%;
    justify-content: center;
    padding: 16px 32px;
    font-size: 16px;
}

.btn-success {
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(30, 58, 138, 0.3);
}

.btn-success:hover {
    background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(30, 58, 138, 0.4);
}

.btn-primary {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
}

.btn-primary:hover:not(:disabled) {
    background: linear-gradient(135deg, #047857 0%, #059669 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none !important;
}

.template-section {
    margin: 30px 0;
}

.template-card {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    text-align: center;
}

.template-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-bottom: 15px;
}

.template-header i {
    font-size: 28px;
    color: #10b981;
}

.template-header h3 {
    margin: 0;
    color: #1e3a8a;
    font-size: 22px;
}

.template-card p {
    color: #6b7280;
    margin-bottom: 25px;
    font-size: 14px;
}

.template-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-outline {
    padding: 12px 24px;
    border: 2px solid #3b82f6;
    background: white;
    color: #1e3a8a;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.btn-outline:hover {
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
}

.info-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin-top: 30px;
}

.info-card {
    background: white;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #e5e7eb;
}

.info-card h4 {
    color: #1e3a8a;
    margin: 0 0 15px 0;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 18px;
}

.info-card h4 i {
    color: #3b82f6;
    font-size: 22px;
}

.info-card ol, .info-card ul {
    margin: 0;
    padding-left: 25px;
    color: #555;
    line-height: 1.8;
}

.info-card li {
    margin-bottom: 8px;
}

.btn-large {
    width: 100%;
    padding: 15px;
    font-size: 16px;
    font-weight: 600;
}

@media (max-width: 768px) {
    .excel-grid {
        grid-template-columns: 1fr;
    }
    
    .info-section {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function toggleCard(card, checkboxId) {
    const checkbox = document.getElementById(checkboxId);
    checkbox.checked = !checkbox.checked;
    card.classList.toggle('active', checkbox.checked);
}

// Update export button text based on filter selection
function updateExportButtonText() {
    const filterMonth = document.getElementById('filterMonth').value;
    const filterYear = document.getElementById('filterYear').value;
    const exportBtn = document.querySelector('.btn-success.btn-large');
    
    if (filterMonth && filterYear) {
        const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        exportBtn.innerHTML = '<i class="fas fa-download"></i> Export ke Excel (' + monthNames[parseInt(filterMonth)] + ' ' + filterYear + ')';
    } else if (filterYear) {
        exportBtn.innerHTML = '<i class="fas fa-download"></i> Export ke Excel (Tahun ' + filterYear + ')';
    } else {
        exportBtn.innerHTML = '<i class="fas fa-download"></i> Export ke Excel';
    }
}

// Add event listeners when page loads
document.addEventListener('DOMContentLoaded', function() {
    const filterMonth = document.getElementById('filterMonth');
    const filterYear = document.getElementById('filterYear');
    
    if (filterMonth && filterYear) {
        filterMonth.addEventListener('change', updateExportButtonText);
        filterYear.addEventListener('change', updateExportButtonText);
        updateExportButtonText(); // Initialize on load
    }
});

function exportData() {
    console.log('exportData() called'); // Debug log
    
    try {
        const exportBarangEl = document.getElementById('export_barang');
        const exportTransaksiEl = document.getElementById('export_transaksi');
        const formatEl = document.querySelector('input[name="format"]:checked');
        const filterMonthEl = document.getElementById('filterMonth');
        const filterYearEl = document.getElementById('filterYear');
        
        if (!exportBarangEl || !exportTransaksiEl || !formatEl) {
            console.error('Element not found:', {exportBarangEl, exportTransaksiEl, formatEl});
            alert('Error: Form elements not found. Please refresh the page.');
            return;
        }
        
        const exportBarang = exportBarangEl.checked;
        const exportTransaksi = exportTransaksiEl.checked;
        const format = formatEl.value;
        const filterMonth = filterMonthEl ? filterMonthEl.value : '';
        const filterYear = filterYearEl ? filterYearEl.value : '';
        
        console.log('Export values:', {exportBarang, exportTransaksi, format, filterMonth, filterYear}); // Debug log
        
        if (!exportBarang && !exportTransaksi) {
            alert('Pilih minimal satu jenis data untuk di-export!');
            return;
        }
        
        // Build URL with parameters
        const params = new URLSearchParams({
            barang: exportBarang ? '1' : '0',
            transaksi: exportTransaksi ? '1' : '0',
            format: format
        });
        
        // Add filter parameters if selected
        if (filterMonth) {
            params.append('month', filterMonth);
        }
        if (filterYear) {
            params.append('year', filterYear);
        }
        
        const url = 'export_excel.php?' + params.toString();
        console.log('Redirecting to:', url); // Debug log
        
        // Redirect to export script
        window.location.href = url;
    } catch (error) {
        console.error('Error in exportData:', error);
        alert('Terjadi error: ' + error.message);
    }
}

function handleFileSelect(input) {
    const file = input.files[0];
    if (file) {
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('File terlalu besar! Maksimal 5MB.');
            input.value = '';
            return;
        }
        
        // Validate file extension - ONLY CSV
        const ext = file.name.split('.').pop().toLowerCase();
        if (ext !== 'csv') {
            alert('❌ Format file tidak didukung!\n\n✅ Gunakan format CSV (.csv)\n\n📝 Cara convert dari Excel:\n1. Buka file Excel Anda\n2. Klik File → Save As\n3. Pilih format: CSV (Comma delimited)\n4. Save dan upload file CSV');
            input.value = '';
            return;
        }
        
        // Show file preview
        document.getElementById('uploadContent').style.display = 'none';
        document.getElementById('filePreview').style.display = 'flex';
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = formatFileSize(file.size);
        document.getElementById('importBtn').disabled = false;
    }
}

function clearFile(event) {
    event.stopPropagation();
    document.getElementById('fileInput').value = '';
    document.getElementById('uploadContent').style.display = 'block';
    document.getElementById('filePreview').style.display = 'none';
    document.getElementById('importBtn').disabled = true;
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Drag and drop functionality
const uploadZone = document.getElementById('uploadZone');

uploadZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadZone.classList.add('dragover');
});

uploadZone.addEventListener('dragleave', () => {
    uploadZone.classList.remove('dragover');
});

uploadZone.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        document.getElementById('fileInput').files = files;
        handleFileSelect(document.getElementById('fileInput'));
    }
});
</script>

<?php include '../layout/footer.php'; ?>

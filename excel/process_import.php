<?php
session_start();

// Redirect ke login jika belum login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['excel_file'])) {
    header('Location: excel.php');
    exit();
}

$inventaris = new ManajemenInventaris();
$updateExisting = isset($_POST['update_existing']);
$skipErrors = isset($_POST['skip_errors']);

$file = $_FILES['excel_file'];
$fileName = $file['name'];
$fileTmp = $file['tmp_name'];
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Validate file
if ($file['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['import_message'] = 'Error upload file!';
    $_SESSION['import_type'] = 'error';
    header('Location: excel.php');
    exit();
}

if (!in_array($fileExt, ['csv'])) {
    $_SESSION['import_message'] = 'Format file tidak didukung! Gunakan format CSV (.csv)';
    $_SESSION['import_type'] = 'error';
    header('Location: excel.php');
    exit();
}

$successCount = 0;
$errorCount = 0;
$errors = [];

// Create debug log file
$debugLog = fopen(__DIR__ . '/import_debug.log', 'w');
fwrite($debugLog, "=== IMPORT DEBUG LOG - " . date('Y-m-d H:i:s') . " ===\n");
fwrite($debugLog, "File: $fileName\n");
fwrite($debugLog, "Extension: $fileExt\n\n");

try {
    // Process CSV only
    $handle = fopen($fileTmp, 'r');
    
    // Skip UTF-8 BOM if present
    $bom = fread($handle, 3);
    if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
        rewind($handle);
    }
    
    $headers = fgetcsv($handle, 1000, ',');
    $rowNumber = 1;
    
    // Clean headers (remove asterisks and extra spaces)
    $cleanHeaders = array_map(function($header) {
        return trim(str_replace('*', '', $header));
    }, $headers);
    
    // Debug log headers
    fwrite($debugLog, "Raw Headers: " . print_r($headers, true) . "\n");
    fwrite($debugLog, "Clean Headers: " . print_r($cleanHeaders, true) . "\n");
    
    // Detect type based on headers
    $isBarang = in_array('Nama Barang', $cleanHeaders) && (in_array('Stok', $cleanHeaders) || in_array('Stok Awal', $cleanHeaders));
    $isTransaksi = in_array('Jenis', $cleanHeaders) && in_array('Nama Barang', $cleanHeaders);
    
    // Debug log detection
    fwrite($debugLog, "isBarang: " . ($isBarang ? 'YES' : 'NO') . "\n");
    fwrite($debugLog, "isTransaksi: " . ($isTransaksi ? 'YES' : 'NO') . "\n\n");
    
    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
        $rowNumber++;
        
        fwrite($debugLog, "Row $rowNumber: " . print_r($data, true) . "\n");
        
        // Skip empty rows
        if (empty(array_filter($data))) {
            fwrite($debugLog, "Row $rowNumber: SKIPPED (empty)\n");
            continue;
        }
        
        try {
            if ($isBarang) {
                // Import barang
                // Expected: Nama Barang, Stok Awal, Satuan, Harga, Keterangan
                if (count($data) < 4) {
                    throw new Exception("Data tidak lengkap");
                }
                
                $namaBarang = trim($data[0]);
                $stok = intval($data[1]);
                $satuan = trim($data[2]);
                $harga = intval(str_replace(['.', ',', ' '], '', $data[3]));
                
                if (empty($namaBarang) || $stok <= 0 || $harga <= 0) {
                    throw new Exception("Nama barang, stok, dan harga wajib diisi dengan nilai valid");
                }
                
                if (empty($satuan)) $satuan = 'buah';
                    
                    // Insert as transaksi masuk
                    $result = $inventaris->catatBarangMasuk(
                        $namaBarang,
                        $stok,
                        $harga,
                        date('Y-m-d'),
                        $satuan
                    );
                    
                    if ($result['success']) {
                        $successCount++;
                    } else {
                        throw new Exception($result['message']);
                    }
                    
                } else if ($isTransaksi) {
                    // Import transaksi
                    // Expected: No, Jenis, Nama Barang, Jumlah, Satuan, Harga, Tanggal, Bagian, Penanggung Jawab
                    // Or: Jenis, Nama Barang, Jumlah, Satuan, Harga, Tanggal, Bagian, Penanggung Jawab (backward compatible)
                    
                    // Check if first column is number (No column exists)
                    $hasNoColumn = is_numeric(trim($data[0]));
                    
                    // Debug log detection
                    fwrite($debugLog, "Row $rowNumber: hasNoColumn = " . ($hasNoColumn ? 'YES' : 'NO') . ", first data = '" . $data[0] . "'\n");
                    
                    if ($hasNoColumn) {
                        // New format with No column
                        if (count($data) < 7) {
                            throw new Exception("Data tidak lengkap");
                        }
                        $jenis = strtoupper(trim($data[1]));
                        $namaBarang = trim($data[2]);
                        $jumlah = intval($data[3]);
                        $satuan = trim($data[4]);
                        $harga = intval(str_replace(['.', ',', ' '], '', $data[5]));
                        $tanggal = trim($data[6]);
                        $bagian = isset($data[7]) ? trim($data[7]) : '';
                        $penanggungJawab = isset($data[8]) ? trim($data[8]) : '';
                    } else {
                        // Old format without No column
                        if (count($data) < 6) {
                            throw new Exception("Data tidak lengkap");
                        }
                        $jenis = strtoupper(trim($data[0]));
                        $namaBarang = trim($data[1]);
                        $jumlah = intval($data[2]);
                        $satuan = trim($data[3]);
                        $harga = intval(str_replace(['.', ',', ' '], '', $data[4]));
                        $tanggal = trim($data[5]);
                        $bagian = isset($data[6]) ? trim($data[6]) : '';
                        $penanggungJawab = isset($data[7]) ? trim($data[7]) : '';
                    }
                    
                    // Normalize jenis to uppercase
                    $jenis = strtoupper($jenis);
                    
                    // Debug log values
                    fwrite($debugLog, "Row $rowNumber: jenis='$jenis', namaBarang='$namaBarang', jumlah=$jumlah, harga=$harga, tanggal='$tanggal'\n");
                    
                    if (!in_array($jenis, ['MASUK', 'KELUAR'])) {
                        fwrite($debugLog, "Row $rowNumber: ERROR - Invalid jenis: $jenis\n");
                        throw new Exception("Jenis harus MASUK atau KELUAR, bukan: " . $jenis);
                    }
                    
                    if (empty($namaBarang) || $jumlah <= 0 || $harga <= 0) {
                        throw new Exception("Nama barang, jumlah, dan harga wajib diisi");
                    }
                    
                    if (empty($satuan)) $satuan = 'buah';
                    
                    // Parse tanggal
                    $tanggalParsed = parseTanggal($tanggal);
                    if (!$tanggalParsed) {
                        fwrite($debugLog, "Row $rowNumber: ERROR - Invalid date format: $tanggal\n");
                        throw new Exception("Format tanggal tidak valid");
                    }
                    
                    fwrite($debugLog, "Row $rowNumber: tanggalParsed = $tanggalParsed\n");
                    
                    if ($jenis == 'MASUK') {
                        $result = $inventaris->catatBarangMasuk(
                            $namaBarang,
                            $jumlah,
                            $harga,
                            $tanggalParsed,
                            $satuan
                        );
                    } else {
                        $result = $inventaris->catatBarangKeluar(
                            $namaBarang,
                            $jumlah,
                            $harga,
                            $tanggalParsed,
                            $bagian,
                            $penanggungJawab
                        );
                    }
                    
                    if ($result['success']) {
                        $successCount++;
                        fwrite($debugLog, "Row $rowNumber: SUCCESS - Inserted\n");
                    } else {
                        fwrite($debugLog, "Row $rowNumber: ERROR - " . $result['message'] . "\n");
                        throw new Exception($result['message']);
                    }
                }
                
            } catch (Exception $e) {
                $errorCount++;
                $errors[] = "Baris $rowNumber: " . $e->getMessage();
                fwrite($debugLog, "Row $rowNumber: EXCEPTION - " . $e->getMessage() . "\n");
                
                if (!$skipErrors) {
                    break;
                }
            }
        }
        
        fclose($handle);
        
        // Debug log final results
        fwrite($debugLog, "\nFinal Results:\n");
        fwrite($debugLog, "successCount = $successCount\n");
        fwrite($debugLog, "errorCount = $errorCount\n");
        fwrite($debugLog, "Errors: " . print_r($errors, true) . "\n");
    
    // Set result message
    $message = "Import selesai! Berhasil: $successCount, Gagal: $errorCount";
    if ($errorCount > 0 && count($errors) > 0) {
        $message .= "<br><br>Error:<br>" . implode('<br>', array_slice($errors, 0, 10));
        if (count($errors) > 10) {
            $message .= "<br>... dan " . (count($errors) - 10) . " error lainnya";
        }
    }
    
    $_SESSION['import_message'] = $message;
    $_SESSION['import_type'] = $errorCount > 0 ? 'warning' : 'success';
    
} catch (Exception $e) {
    $_SESSION['import_message'] = 'Error: ' . $e->getMessage();
    $_SESSION['import_type'] = 'error';
    fwrite($debugLog, "\nFATAL ERROR: " . $e->getMessage() . "\n");
}

// Close debug log
fwrite($debugLog, "\n=== END DEBUG LOG ===\n");
fclose($debugLog);

header('Location: excel.php');
exit();

function parseTanggal($tanggal) {
    // Try different date formats
    $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'm/d/Y'];
    
    foreach ($formats as $format) {
        $date = DateTime::createFromFormat($format, $tanggal);
        if ($date !== false) {
            return $date->format('Y-m-d');
        }
    }
    
    // Try strtotime
    $timestamp = strtotime($tanggal);
    if ($timestamp !== false) {
        return date('Y-m-d', $timestamp);
    }
    
    return false;
}
?>
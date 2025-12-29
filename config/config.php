<?php
/**
 * Konfigurasi dan Class Manajemen Inventaris
 * Menggunakan MySQL Database
 */

require_once 'database.php';

class ManajemenInventaris {
    private $db;
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Catat barang masuk
     */
    public function catatBarangMasuk($namaBarang, $jumlah, $harga, $tanggal, $satuan = 'buah') {
        try {
            // Validasi
            if (empty(trim($namaBarang))) {
                return ['success' => false, 'message' => 'Nama barang tidak boleh kosong!'];
            }
            
            if ($jumlah <= 0 || $harga <= 0) {
                return ['success' => false, 'message' => 'Jumlah dan Harga harus lebih dari 0.'];
            }
            
            // Begin transaction
            $this->conn->beginTransaction();
            
            // Insert transaksi
            $query = "INSERT INTO transaksi (jenis, nama_barang, jumlah, satuan, harga, tanggal) 
                      VALUES ('MASUK', :nama_barang, :jumlah, :satuan, :harga, :tanggal)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nama_barang', $namaBarang);
            $stmt->bindParam(':jumlah', $jumlah);
            $stmt->bindParam(':satuan', $satuan);
            $stmt->bindParam(':harga', $harga);
            $stmt->bindParam(':tanggal', $tanggal);
            $stmt->execute();
            
            // Update atau insert stok barang
            $queryCheck = "SELECT id, stok FROM barang WHERE nama_barang = :nama_barang";
            $stmtCheck = $this->conn->prepare($queryCheck);
            $stmtCheck->bindParam(':nama_barang', $namaBarang);
            $stmtCheck->execute();
            
            if ($stmtCheck->rowCount() > 0) {
                // Update stok dan satuan
                $queryUpdate = "UPDATE barang SET stok = stok + :jumlah, satuan = :satuan WHERE nama_barang = :nama_barang";
                $stmtUpdate = $this->conn->prepare($queryUpdate);
                $stmtUpdate->bindParam(':jumlah', $jumlah);
                $stmtUpdate->bindParam(':satuan', $satuan);
                $stmtUpdate->bindParam(':nama_barang', $namaBarang);
                $stmtUpdate->execute();
            } else {
                // Insert barang baru
                $queryInsert = "INSERT INTO barang (nama_barang, stok, satuan) VALUES (:nama_barang, :stok, :satuan)";
                $stmtInsert = $this->conn->prepare($queryInsert);
                $stmtInsert->bindParam(':nama_barang', $namaBarang);
                $stmtInsert->bindParam(':stok', $jumlah);
                $stmtInsert->bindParam(':satuan', $satuan);
                $stmtInsert->execute();
            }
            
            // Commit transaction
            $this->conn->commit();
            
            // Get stok sekarang
            $stokSekarang = $this->getStokBarangByName($namaBarang);
            return ['success' => true, 'message' => "Berhasil! Stok '$namaBarang' sekarang: $stokSekarang."];
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => "Terjadi kesalahan: " . $e->getMessage()];
        }
    }
    
    /**
     * Catat barang keluar
     */
    public function catatBarangKeluar($namaBarang, $jumlah, $harga, $tanggal, $bagian = '', $penanggungJawab = '') {
        try {
            // Validasi
            if (empty(trim($namaBarang))) {
                return ['success' => false, 'message' => 'Nama barang tidak boleh kosong!'];
            }
            
            if ($jumlah <= 0 || $harga <= 0) {
                return ['success' => false, 'message' => 'Jumlah dan Harga harus lebih dari 0.'];
            }
            
            // Cek stok dan ambil satuan
            $stokSaatIni = $this->getStokBarangByName($namaBarang);
            $satuan = $this->getSatuanBarang($namaBarang);
            
            if ($stokSaatIni == 0) {
                return ['success' => false, 'message' => "Barang '$namaBarang' tidak ada atau stok habis."];
            }
            
            if ($jumlah > $stokSaatIni) {
                return ['success' => false, 'message' => "Stok tidak mencukupi. Sisa $stokSaatIni."];
            }
            
            // Begin transaction
            $this->conn->beginTransaction();
            
            // Insert transaksi dengan satuan dari barang
            $query = "INSERT INTO transaksi (jenis, nama_barang, jumlah, satuan, harga, tanggal, bagian, penanggung_jawab) 
                      VALUES ('KELUAR', :nama_barang, :jumlah, :satuan, :harga, :tanggal, :bagian, :penanggung_jawab)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nama_barang', $namaBarang);
            $stmt->bindParam(':jumlah', $jumlah);
            $stmt->bindParam(':satuan', $satuan);
            $stmt->bindParam(':harga', $harga);
            $stmt->bindParam(':tanggal', $tanggal);
            $stmt->bindParam(':bagian', $bagian);
            $stmt->bindParam(':penanggung_jawab', $penanggungJawab);
            $stmt->execute();
            
            // Update stok
            $queryUpdate = "UPDATE barang SET stok = stok - :jumlah WHERE nama_barang = :nama_barang";
            $stmtUpdate = $this->conn->prepare($queryUpdate);
            $stmtUpdate->bindParam(':jumlah', $jumlah);
            $stmtUpdate->bindParam(':nama_barang', $namaBarang);
            $stmtUpdate->execute();
            
            // Commit transaction
            $this->conn->commit();
            
            $stokSekarang = $this->getStokBarangByName($namaBarang);
            return ['success' => true, 'message' => "Berhasil! Stok '$namaBarang' sekarang: $stokSekarang."];
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => "Terjadi kesalahan: " . $e->getMessage()];
        }
    }
    
    /**
     * Get stok barang by name
     */
    private function getStokBarangByName($namaBarang) {
        $query = "SELECT stok FROM barang WHERE nama_barang = :nama_barang";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nama_barang', $namaBarang);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['stok'];
        }
        return 0;
    }
    
    /**
     * Get satuan barang by name
     */
    public function getSatuanBarang($namaBarang) {
        $query = "SELECT satuan FROM barang WHERE nama_barang = :nama_barang";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nama_barang', $namaBarang);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['satuan'] ?? 'buah';
        }
        return 'buah';
    }
    
    /**
     * Get harga terakhir barang dari riwayat transaksi masuk
     */
    public function getHargaBarang($namaBarang) {
        $query = "SELECT harga FROM transaksi 
                  WHERE nama_barang = :nama_barang AND jenis = 'MASUK' 
                  ORDER BY id DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nama_barang', $namaBarang);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['harga'];
        }
        return 0;
    }
    
    /**
     * Get daftar barang beserta stok, satuan, dan harga
     */
    public function getDaftarBarang() {
        $query = "SELECT b.nama_barang, b.stok, b.satuan, 
                  (SELECT t.harga FROM transaksi t 
                   WHERE t.nama_barang = b.nama_barang AND t.jenis = 'MASUK' 
                   ORDER BY t.id DESC LIMIT 1) as harga
                  FROM barang b 
                  WHERE b.stok > 0
                  ORDER BY b.nama_barang";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $daftar = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $daftar[] = [
                'nama' => $row['nama_barang'],
                'stok' => $row['stok'],
                'satuan' => $row['satuan'] ?? 'buah',
                'harga' => $row['harga'] ?? 0
            ];
        }
        return $daftar;
    }
    
    /**
     * Getter untuk stok barang
     */
    public function getStokBarang() {
        $query = "SELECT nama_barang, stok FROM barang ORDER BY nama_barang";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $stok = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stok[$row['nama_barang']] = $row['stok'];
        }
        return $stok;
    }
    
    /**
     * Get detailed stock information with price and unit
     */
    public function getDetailedStokBarang() {
        $query = "SELECT b.id, b.nama_barang, b.stok, b.satuan,
                  (SELECT t.harga FROM transaksi t 
                   WHERE t.nama_barang = b.nama_barang 
                   ORDER BY t.tanggal DESC, t.id DESC LIMIT 1) as harga_terakhir,
                  (SELECT t.tanggal FROM transaksi t 
                   WHERE t.nama_barang = b.nama_barang 
                   ORDER BY t.tanggal DESC, t.id DESC LIMIT 1) as tanggal_update
                  FROM barang b 
                  ORDER BY b.nama_barang";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = [
                'id' => $row['id'],
                'nama_barang' => $row['nama_barang'],
                'stok' => $row['stok'],
                'satuan' => $row['satuan'] ?? 'buah',
                'harga' => $row['harga_terakhir'] ?? 0,
                'total_nilai' => ($row['stok'] * ($row['harga_terakhir'] ?? 0)),
                'tanggal_update' => $row['tanggal_update']
            ];
        }
        return $result;
    }
    
    /**
     * Getter untuk riwayat transaksi
     */
    public function getRiwayatTransaksi() {
        $query = "SELECT * FROM transaksi ORDER BY tanggal DESC, id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $riwayat = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $riwayat[] = [
                'id' => $row['id'],
                'jenis' => $row['jenis'],
                'nama' => $row['nama_barang'],
                'jumlah' => $row['jumlah'],
                'satuan' => $row['satuan'] ?? 'buah',
                'harga' => $row['harga'],
                'tanggal' => $row['tanggal'],
                'bagian' => $row['bagian'],
                'penanggung_jawab' => $row['penanggung_jawab']
            ];
        }
        return $riwayat;
    }
    
    /**
     * Get riwayat berdasarkan jenis (MASUK/KELUAR)
     */
    public function getRiwayatByJenis($jenis) {
        $query = "SELECT * FROM transaksi WHERE jenis = :jenis ORDER BY tanggal DESC, id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':jenis', $jenis);
        $stmt->execute();
        
        $riwayat = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $riwayat[] = [
                'id' => $row['id'],
                'jenis' => $row['jenis'],
                'nama' => $row['nama_barang'],
                'jumlah' => $row['jumlah'],
                'harga' => $row['harga'],
                'tanggal' => $row['tanggal'],
                'bagian' => $row['bagian'],
                'penanggung_jawab' => $row['penanggung_jawab']
            ];
        }
        return $riwayat;
    }
    
    /**
     * Hapus transaksi berdasarkan ID
     */
    public function hapusTransaksi($transactionId) {
        try {
            // Get transaksi data
            $query = "SELECT * FROM transaksi WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $transactionId);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                return ['success' => false, 'message' => 'Transaksi tidak ditemukan.'];
            }
            
            $transaksi = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Begin transaction
            $this->conn->beginTransaction();
            
            // Sesuaikan stok
            if ($transaksi['jenis'] == 'MASUK') {
                // Jika menghapus transaksi masuk, kurangi stok
                $stokSaatIni = $this->getStokBarangByName($transaksi['nama_barang']);
                
                if ($stokSaatIni < $transaksi['jumlah']) {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => "Tidak dapat menghapus. Stok '" . $transaksi['nama_barang'] . "' akan menjadi negatif."];
                }
                
                $queryUpdate = "UPDATE barang SET stok = stok - :jumlah WHERE nama_barang = :nama_barang";
            } else {
                // Jika menghapus transaksi keluar, tambah stok kembali
                $queryUpdate = "UPDATE barang SET stok = stok + :jumlah WHERE nama_barang = :nama_barang";
            }
            
            $stmtUpdate = $this->conn->prepare($queryUpdate);
            $stmtUpdate->bindParam(':jumlah', $transaksi['jumlah']);
            $stmtUpdate->bindParam(':nama_barang', $transaksi['nama_barang']);
            $stmtUpdate->execute();
            
            // Delete transaksi
            $queryDelete = "DELETE FROM transaksi WHERE id = :id";
            $stmtDelete = $this->conn->prepare($queryDelete);
            $stmtDelete->bindParam(':id', $transactionId);
            $stmtDelete->execute();
            
            // Commit
            $this->conn->commit();
            
            $stokSekarang = $this->getStokBarangByName($transaksi['nama_barang']);
            return ['success' => true, 'message' => "Transaksi berhasil dihapus. Stok '" . $transaksi['nama_barang'] . "' sekarang: $stokSekarang."];
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => "Terjadi kesalahan: " . $e->getMessage()];
        }
    }
    
    /**
     * Get periode transaksi (bulan-tahun)
     */
    public function getPeriodeTransaksi() {
        $query = "SELECT DISTINCT DATE_FORMAT(tanggal, '%m-%Y') as periode 
                  FROM transaksi 
                  ORDER BY tanggal DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $periode = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $periode[] = $row['periode'];
        }
        return $periode;
    }
    
    /**
     * Konversi periode ke nama bulan
     */
    public function getNamaPeriode($periodeStr) {
        list($bulan, $tahun) = explode('-', $periodeStr);
        $namaBulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        
        return $namaBulan[$bulan] . ' ' . $tahun;
    }
}

/**
 * Fungsi helper untuk format rupiah
 */
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

/**
 * Fungsi helper untuk format tanggal Indonesia
 */
function formatTanggalIndonesia($tanggal) {
    $bulan = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
        '04' => 'April', '05' => 'Mei', '06' => 'Juni',
        '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
        '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];
    
    $timestamp = strtotime($tanggal);
    $hari = date('d', $timestamp);
    $bulanNum = date('m', $timestamp);
    $tahun = date('Y', $timestamp);
    
    return $hari . ' ' . $bulan[$bulanNum] . ' ' . $tahun;
}
?>

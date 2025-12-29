<?php
/**
 * Konfigurasi dan Class Manajemen Inventaris
 * Konversi dari Python ke PHP
 */

class ManajemenInventaris {
    private $stokBarang = [];
    private $riwayatTransaksi = [];
    private $namaFile = '../data_inventaris.json';
    
    public function __construct() {
        $this->muatData();
    }
    
    /**
     * Mencatat transaksi barang masuk/keluar
     */
    private function catatTransaksi($namaBarang, $jumlah, $harga, $tanggal, $jenis, $bagian = '', $penanggungJawab = '') {
        try {
            if (empty(trim($namaBarang))) {
                return ['success' => false, 'message' => 'Nama barang tidak boleh kosong!'];
            }
            
            if ($jumlah <= 0 || $harga <= 0) {
                return ['success' => false, 'message' => 'Jumlah dan Harga harus lebih dari 0.'];
            }
            
            if ($jenis == 'KELUAR') {
                $stokSaatIni = isset($this->stokBarang[$namaBarang]) ? $this->stokBarang[$namaBarang] : 0;
                
                if ($stokSaatIni == 0) {
                    return ['success' => false, 'message' => "Barang '$namaBarang' tidak ada atau stok habis."];
                }
                
                if ($jumlah > $stokSaatIni) {
                    return ['success' => false, 'message' => "Stok tidak mencukupi. Sisa $stokSaatIni."];
                }
                
                $this->stokBarang[$namaBarang] -= $jumlah;
                
                // Hapus barang dari stok jika sudah 0
                if ($this->stokBarang[$namaBarang] == 0) {
                    unset($this->stokBarang[$namaBarang]);
                }
            } else { // MASUK
                if (!isset($this->stokBarang[$namaBarang])) {
                    $this->stokBarang[$namaBarang] = 0;
                }
                $this->stokBarang[$namaBarang] += $jumlah;
            }
            
            $transaksi = [
                'jenis' => $jenis,
                'nama' => $namaBarang,
                'jumlah' => $jumlah,
                'harga' => $harga,
                'tanggal' => $tanggal
            ];
            
            // Tambahkan bagian dan penanggung jawab untuk transaksi keluar
            if ($jenis == 'KELUAR') {
                $transaksi['bagian'] = $bagian;
                $transaksi['penanggung_jawab'] = $penanggungJawab;
            }
            
            $this->riwayatTransaksi[] = $transaksi;
            
            $this->simpanData();
            
            $stokSekarang = isset($this->stokBarang[$namaBarang]) ? $this->stokBarang[$namaBarang] : 0;
            return ['success' => true, 'message' => "Berhasil! Stok '$namaBarang' sekarang: $stokSekarang."];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => "Terjadi kesalahan: " . $e->getMessage()];
        }
    }
    
    /**
     * Catat barang masuk
     */
    public function catatBarangMasuk($namaBarang, $jumlah, $harga, $tanggal) {
        return $this->catatTransaksi($namaBarang, $jumlah, $harga, $tanggal, 'MASUK');
    }
    
    /**
     * Catat barang keluar
     */
    public function catatBarangKeluar($namaBarang, $jumlah, $harga, $tanggal, $bagian = '', $penanggungJawab = '') {
        return $this->catatTransaksi($namaBarang, $jumlah, $harga, $tanggal, 'KELUAR', $bagian, $penanggungJawab);
    }
    
    /**
     * Get harga terakhir barang dari riwayat transaksi masuk
     */
    public function getHargaBarang($namaBarang) {
        $harga = 0;
        // Cari harga terakhir dari transaksi masuk
        for ($i = count($this->riwayatTransaksi) - 1; $i >= 0; $i--) {
            if ($this->riwayatTransaksi[$i]['nama'] == $namaBarang && 
                $this->riwayatTransaksi[$i]['jenis'] == 'MASUK') {
                $harga = $this->riwayatTransaksi[$i]['harga'];
                break;
            }
        }
        return $harga;
    }
    
    /**
     * Get daftar barang beserta stok dan harga
     */
    public function getDaftarBarang() {
        $daftar = [];
        foreach ($this->stokBarang as $nama => $stok) {
            $daftar[] = [
                'nama' => $nama,
                'stok' => $stok,
                'harga' => $this->getHargaBarang($nama)
            ];
        }
        return $daftar;
    }
    
    /**
     * Hapus transaksi berdasarkan index
     */
    public function hapusTransaksi($indexTransaksi) {
        try {
            if ($indexTransaksi < 0 || $indexTransaksi >= count($this->riwayatTransaksi)) {
                return ['success' => false, 'message' => 'Transaksi tidak ditemukan.'];
            }
            
            $transaksi = $this->riwayatTransaksi[$indexTransaksi];
            $namaBarang = $transaksi['nama'];
            $jumlah = $transaksi['jumlah'];
            $jenis = $transaksi['jenis'];
            
            // Sesuaikan stok berdasarkan jenis transaksi yang dihapus
            if ($jenis == 'MASUK') {
                // Jika menghapus transaksi masuk, kurangi stok
                $stokSaatIni = isset($this->stokBarang[$namaBarang]) ? $this->stokBarang[$namaBarang] : 0;
                
                if ($stokSaatIni < $jumlah) {
                    return ['success' => false, 'message' => "Tidak dapat menghapus. Stok '$namaBarang' akan menjadi negatif."];
                }
                
                $this->stokBarang[$namaBarang] -= $jumlah;
                
                if ($this->stokBarang[$namaBarang] == 0) {
                    unset($this->stokBarang[$namaBarang]);
                }
            } else { // KELUAR
                // Jika menghapus transaksi keluar, tambah stok kembali
                if (!isset($this->stokBarang[$namaBarang])) {
                    $this->stokBarang[$namaBarang] = 0;
                }
                $this->stokBarang[$namaBarang] += $jumlah;
            }
            
            // Hapus transaksi dari riwayat
            array_splice($this->riwayatTransaksi, $indexTransaksi, 1);
            $this->simpanData();
            
            $stokSekarang = isset($this->stokBarang[$namaBarang]) ? $this->stokBarang[$namaBarang] : 0;
            return ['success' => true, 'message' => "Transaksi berhasil dihapus. Stok '$namaBarang' sekarang: $stokSekarang."];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => "Terjadi kesalahan: " . $e->getMessage()];
        }
    }
    
    /**
     * Simpan data ke file JSON
     */
    private function simpanData() {
        try {
            $dataUntukDisimpan = [
                'stok' => $this->stokBarang,
                'riwayat' => $this->riwayatTransaksi
            ];
            
            $json = json_encode($dataUntukDisimpan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            file_put_contents($this->namaFile, $json);
            
        } catch (Exception $e) {
            error_log("Gagal menyimpan data: " . $e->getMessage());
        }
    }
    
    /**
     * Muat data dari file JSON
     */
    private function muatData() {
        try {
            if (file_exists($this->namaFile)) {
                $json = file_get_contents($this->namaFile);
                $dataDariFile = json_decode($json, true);
                
                if ($dataDariFile) {
                    $this->stokBarang = isset($dataDariFile['stok']) ? $dataDariFile['stok'] : [];
                    $this->riwayatTransaksi = isset($dataDariFile['riwayat']) ? $dataDariFile['riwayat'] : [];
                }
            }
        } catch (Exception $e) {
            error_log("Gagal memuat data: " . $e->getMessage());
        }
    }
    
    /**
     * Getter untuk stok barang
     */
    public function getStokBarang() {
        return $this->stokBarang;
    }
    
    /**
     * Getter untuk riwayat transaksi
     */
    public function getRiwayatTransaksi() {
        return $this->riwayatTransaksi;
    }
    
    /**
     * Get riwayat berdasarkan jenis (MASUK/KELUAR)
     */
    public function getRiwayatByJenis($jenis) {
        $hasil = [];
        foreach ($this->riwayatTransaksi as $transaksi) {
            if ($transaksi['jenis'] == $jenis) {
                $hasil[] = $transaksi;
            }
        }
        return $hasil;
    }
    
    /**
     * Get periode transaksi (bulan-tahun)
     */
    public function getPeriodeTransaksi() {
        $periode = [];
        foreach ($this->riwayatTransaksi as $transaksi) {
            $bulanTahun = date('m-Y', strtotime($transaksi['tanggal']));
            if (!in_array($bulanTahun, $periode)) {
                $periode[] = $bulanTahun;
            }
        }
        rsort($periode); // Urutkan terbaru dulu
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
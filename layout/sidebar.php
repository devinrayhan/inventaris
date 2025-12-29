    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <nav class="sidebar-nav">
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="../dashboard/dashboard.php" class="nav-link <?php echo $current_page == 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt nav-icon"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="../transaksi_masuk/transaksi_masuk.php" class="nav-link <?php echo $current_page == 'transaksi_masuk' ? 'active' : ''; ?>">
                        <i class="fas fa-arrow-down nav-icon"></i>
                        <span class="nav-text">Transaksi Masuk</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="../transaksi_keluar/transaksi_keluar.php" class="nav-link <?php echo $current_page == 'transaksi_keluar' ? 'active' : ''; ?>">
                        <i class="fas fa-arrow-up nav-icon"></i>
                        <span class="nav-text">Transaksi Keluar</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="../stok/stok.php" class="nav-link <?php echo $current_page == 'stok' ? 'active' : ''; ?>">
                        <i class="fas fa-boxes nav-icon"></i>
                        <span class="nav-text">Data Stok</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="../riwayat/riwayat.php" class="nav-link <?php echo $current_page == 'riwayat' ? 'active' : ''; ?>">
                        <i class="fas fa-history nav-icon"></i>
                        <span class="nav-text">Riwayat</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="../laporan/laporan.php" class="nav-link <?php echo $current_page == 'laporan' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-bar nav-icon"></i>
                        <span class="nav-text">Laporan</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="../excel/excel.php" class="nav-link <?php echo $current_page == 'excel' ? 'active' : ''; ?>">
                        <i class="fas fa-file-excel nav-icon"></i>
                        <span class="nav-text">Excel</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="../profil/profil.php" class="nav-link <?php echo $current_page == 'profil' ? 'active' : ''; ?>">
                        <i class="fas fa-user-circle nav-icon"></i>
                        <span class="nav-text">Profil</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="../pengaturan/pengaturan.php" class="nav-link <?php echo $current_page == 'pengaturan' ? 'active' : ''; ?>">
                        <i class="fas fa-cog nav-icon"></i>
                        <span class="nav-text">Pengaturan</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content" id="mainContent">
        <div class="content-wrapper">
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Aplikasi Inventaris'; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet" />
</head>
<body>
    <!-- Top Header -->
    <header class="top-header">
        <div class="header-content">
            <div class="header-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1><?php echo isset($_SESSION['settings']['app_name']) ? $_SESSION['settings']['app_name'] : 'Sistem Manajemen Inventaris'; ?></h1>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <div class="user-avatar"></div>
                    <div class="user-details">
                        <span class="user-name"><?php echo isset($_SESSION['user']) ? $_SESSION['user'] : 'Admin Inventaris'; ?></span>
                        <span class="user-role"><?php echo isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'Admin'; ?></span>
                    </div>
                </div>
                <a href="../logout.php" class="logout-btn" title="Keluar">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </header>
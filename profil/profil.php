<?php
session_start();

// Redirect ke login jika belum login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

require_once '../config/database.php';

// Set current page
$current_page = 'profil';
$title = 'Profil Admin - Aplikasi Inventaris';
$message = '';
$message_type = '';

// Data admin dari session
$current_username = isset($_SESSION['username']) ? $_SESSION['username'] : 'admin';

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'update_profil') {
        $new_username = trim($_POST['username']);
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        try {
            $database = new Database();
            $conn = $database->getConnection();
            
            // Get user dari database
            $query = "SELECT * FROM users WHERE username = :username LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':username', $current_username);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Validasi password lama
                if (!password_verify($current_password, $user['password'])) {
                    $message = 'Password lama tidak sesuai!';
                    $message_type = 'error';
                } elseif (!empty($new_password) && $new_password !== $confirm_password) {
                    $message = 'Password baru dan konfirmasi password tidak cocok!';
                    $message_type = 'error';
                } elseif (!empty($new_password) && strlen($new_password) < 4) {
                    $message = 'Password baru minimal 4 karakter!';
                    $message_type = 'error';
                } else {
                    // Update username dan password jika ada
                    if (!empty($new_password)) {
                        // Update username dan password
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $updateQuery = "UPDATE users SET username = :new_username, password = :password WHERE username = :old_username";
                        $updateStmt = $conn->prepare($updateQuery);
                        $updateStmt->bindParam(':new_username', $new_username);
                        $updateStmt->bindParam(':password', $hashed_password);
                        $updateStmt->bindParam(':old_username', $current_username);
                        $updateStmt->execute();
                        
                        $message = 'Profil dan password berhasil diperbarui!';
                    } else {
                        // Update username saja
                        $updateQuery = "UPDATE users SET username = :new_username WHERE username = :old_username";
                        $updateStmt = $conn->prepare($updateQuery);
                        $updateStmt->bindParam(':new_username', $new_username);
                        $updateStmt->bindParam(':old_username', $current_username);
                        $updateStmt->execute();
                        
                        $message = 'Username berhasil diperbarui!';
                    }
                    
                    // Update session
                    $_SESSION['username'] = $new_username;
                    $_SESSION['profile_message'] = $message;
                    $_SESSION['profile_message_type'] = $message_type;
                    
                    // Redirect untuk mencegah resubmit dan notifikasi duplikat
                    header('Location: profil.php');
                    exit();
                }
            } else {
                $message = 'User tidak ditemukan!';
                $message_type = 'error';
            }
        } catch (Exception $e) {
            $message = 'Terjadi kesalahan: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Ambil message dari session jika ada
if (isset($_SESSION['profile_message'])) {
    $message = $_SESSION['profile_message'];
    $message_type = $_SESSION['profile_message_type'];
    unset($_SESSION['profile_message']);
    unset($_SESSION['profile_message_type']);
}

// Include header
include '../layout/header.php';
include '../layout/sidebar.php';
?>

<div class="page-header">
    <div class="page-title">
        <h2><i class="fas fa-user-circle"></i> Profil Admin</h2>
        <p>Kelola informasi akun administrator Anda</p>
    </div>
    
    <?php if (!empty($message)): ?>
    <div class="profile-alert profile-alert-<?php echo $message_type; ?>">
        <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
        <span><?php echo htmlspecialchars($message); ?></span>
        <button onclick="this.parentElement.remove()" class="alert-close">&times;</button>
    </div>
    <?php endif; ?>
</div>

<div class="page-content">
    <div class="profile-container">
        <div class="profile-card">
            <form method="POST" class="profile-form" id="profileForm">
                <input type="hidden" name="action" value="update_profil">
                
                <div class="form-section">
                    <h4><i class="fas fa-user"></i> Informasi Login</h4>
                    
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user-tag"></i>
                            Username *
                        </label>
                        <input type="text" id="username" name="username" 
                               value="<?php echo htmlspecialchars($current_username); ?>" 
                               required placeholder="Masukkan username">
                        <small class="form-hint">Username untuk login ke sistem</small>
                    </div>
                </div>
                
                <div class="form-divider"></div>
                
                <div class="form-section">
                    <h4><i class="fas fa-lock"></i> Ubah Password</h4>
                    <p class="section-description">Kosongkan jika tidak ingin mengubah password</p>
                    
                    <div class="form-group">
                        <label for="current_password">
                            <i class="fas fa-key"></i>
                            Password Lama *
                        </label>
                        <div class="password-input-wrapper">
                            <input type="password" id="current_password" name="current_password" 
                                   required placeholder="Masukkan password lama">
                            <button type="button" class="toggle-password" onclick="togglePassword('current_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">
                            <i class="fas fa-lock"></i>
                            Password Baru
                        </label>
                        <div class="password-input-wrapper">
                            <input type="password" id="new_password" name="new_password" 
                                   placeholder="Masukkan password baru (opsional)">
                            <button type="button" class="toggle-password" onclick="togglePassword('new_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="form-hint">Minimal 4 karakter</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i>
                            Konfirmasi Password Baru
                        </label>
                        <div class="password-input-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   placeholder="Ketik ulang password baru">
                            <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-large">
                        <i class="fas fa-save"></i>
                        Simpan Perubahan
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-undo"></i>
                        Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.profile-alert {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px 20px;
    border-radius: 8px;
    margin-top: 20px;
    font-size: 14px;
    position: relative;
}

.profile-alert-success {
    background: #d1fae5;
    color: #065f46;
    border-left: 4px solid #10b981;
}

.profile-alert-error {
    background: #fee2e2;
    color: #991b1b;
    border-left: 4px solid #ef4444;
}

.profile-alert i {
    font-size: 18px;
}

.profile-alert span {
    flex: 1;
}

.alert-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: inherit;
    opacity: 0.5;
    transition: opacity 0.3s;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.alert-close:hover {
    opacity: 1;
}

.profile-container {
    max-width: 700px;
}

.profile-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    padding: 30px;
}

.profile-form {
    /* No extra padding needed */
}

.form-section {
    margin-bottom: 30px;
}

.form-section h4 {
    color: #2c3e50;
    margin-bottom: 15px;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-description {
    color: #7f8c8d;
    font-size: 14px;
    margin-bottom: 20px;
}

.form-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 30px 0;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    color: #2c3e50;
    font-weight: 500;
    margin-bottom: 8px;
    font-size: 14px;
}

.form-group input[type="text"],
.form-group input[type="password"] {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.password-input-wrapper {
    position: relative;
}

.password-input-wrapper input {
    padding-right: 45px;
}

.toggle-password {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #7f8c8d;
    cursor: pointer;
    padding: 5px;
    font-size: 14px;
    transition: color 0.3s;
}

.toggle-password:hover {
    color: #3498db;
}

.form-group input:focus {
    outline: none;
    border-color: #3498db;
}

.form-hint {
    display: block;
    color: #7f8c8d;
    font-size: 12px;
    margin-top: 5px;
}

.form-actions {
    display: flex;
    gap: 10px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}

.btn-large {
    padding: 12px 24px;
    font-size: 14px;
}
</style>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.parentElement.querySelector('.toggle-password i');
    
    if (input.type === 'password') {
        input.type = 'text';
        button.classList.remove('fa-eye');
        button.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        button.classList.remove('fa-eye-slash');
        button.classList.add('fa-eye');
    }
}

// Validasi form
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword && newPassword !== confirmPassword) {
        e.preventDefault();
        alert('Password baru dan konfirmasi password tidak cocok!');
        return false;
    }
    
    if (newPassword && newPassword.length < 4) {
        e.preventDefault();
        alert('Password baru minimal 4 karakter!');
        return false;
    }
});
</script>

<?php include '../layout/footer.php'; ?>

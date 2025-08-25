<?php
session_start();
require_once 'includes/functions.php';

// Kiểm tra đăng nhập
checkLogin();

$user = getUserById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ - Quản Lý Chi Tiêu</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="main-content">
    <div class="container">
        <div class="page-header">
            <div class="page-title">
                <h1><i class="fas fa-user"></i> Hồ sơ cá nhân</h1>
                <p>Thông tin tài khoản của bạn</p>
            </div>
        </div>

        <div class="form-container">
            <div class="profile-grid">
                <div class="profile-card" style="display:flex; align-items:center; gap:1rem;">
                    <div class="profile-avatar">
                        <?php if ($avatar): ?>
                            <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" style="width:80px;height:80px;border-radius:50%;object-fit:cover;">
                        <?php else: ?>
                            <i class="fas fa-user-circle" style="font-size:80px;color:#9e9e9e;"></i>
                        <?php endif; ?>
                    </div>
                    <div class="profile-info">
                        <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($user['name'] ?? ($_SESSION['user_name'] ?? '')); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? ($_SESSION['user_email'] ?? '')); ?></p>
                        <p><strong>Ngày tạo:</strong> <?php echo htmlspecialchars($user['created_at'] ?? ''); ?></p>
                        <p><strong>Cập nhật gần nhất:</strong> <?php echo htmlspecialchars($user['updated_at'] ?? ''); ?></p>
                    </div>
                </div>
                <div class="profile-actions">
                    <a href="settings.php" class="btn btn-primary"><i class="fas fa-cog"></i> Cập nhật hồ sơ</a>
                    <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                </div>
            </div>
        </div>

    </div>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>

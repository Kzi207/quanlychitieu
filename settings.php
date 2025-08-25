<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Kiểm tra đăng nhập
checkLogin();

$user = getUserById($_SESSION['user_id']);

// Xử lý cập nhật hồ sơ
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        
        if (empty($name) || empty($email)) {
            $message = 'Vui lòng điền đầy đủ thông tin!';
            $message_type = 'danger';
        } else {
            $result = updateUserProfile($_SESSION['user_id'], $name, $email);
            if ($result['success']) {
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $message = $result['message'];
                $message_type = 'success';
                $user = getUserById($_SESSION['user_id']); // Cập nhật lại thông tin user
            } else {
                $message = $result['message'];
                $message_type = 'danger';
            }
        }
    }
    
    // Xử lý đổi mật khẩu
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $message = 'Vui lòng điền đầy đủ thông tin mật khẩu!';
            $message_type = 'danger';
        } elseif ($new_password !== $confirm_password) {
            $message = 'Mật khẩu mới không khớp!';
            $message_type = 'danger';
        } else {
            $result = changeUserPassword($_SESSION['user_id'], $current_password, $new_password);
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cài đặt - Quản Lý Chi Tiêu</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="main-content">
    <div class="container">
        <div class="page-header">
            <div class="page-title">
                <h1><i class="fas fa-cog"></i> Cài đặt tài khoản</h1>
                <p>Cập nhật thông tin cá nhân và đổi mật khẩu</p>
            </div>
        </div>

        <?php if ($message): ?><div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div><?php endif; ?>

        <div class="form-container">
            <h3><i class="fas fa-user"></i> Thông tin cá nhân</h3>
            <form method="POST" class="settings-form">
                <input type="hidden" name="update_profile" value="1">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Họ tên</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu thay đổi</button>
                </div>
            </form>
        </div>

        <hr style="margin:2rem 0; opacity:0.2;">

        <div class="form-container">
            <h3><i class="fas fa-lock"></i> Đổi mật khẩu</h3>
            <form method="POST" class="settings-form">
                <input type="hidden" name="change_password" value="1">
                <div class="form-row">
                    <div class="form-group">
                        <label for="current_password">Mật khẩu hiện tại</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">Mật khẩu mới</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Xác nhận mật khẩu mới</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Đổi mật khẩu</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>

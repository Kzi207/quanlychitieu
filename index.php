<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Kiểm tra đăng nhập
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
if ($_POST) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    if (empty($email) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    } else {
        $user = loginUser($email, $password);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            
            if ($remember) {
                createRememberToken($user['id']);
            }
            
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Email hoặc mật khẩu không đúng!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Chi Tiêu - Đăng Nhập</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <i class="fas fa-wallet"></i>
                <h1>Quản Lý Chi Tiêu</h1>
                <p>Kiểm soát tài chính, xây dựng tương lai</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Mật khẩu</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group" style="display:flex; align-items:center; gap:.5rem;">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember" style="margin:0;">Ghi nhớ đăng nhập</label>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Đăng Nhập
                </button>
            </form>
            
            <div class="login-footer">
                <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
                <a href="forgot-password.php">Quên mật khẩu?</a>
            </div>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
</body>
</html>

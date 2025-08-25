<?php
session_start();
require_once 'config/database.php';

echo "<h2>🔍 Kiểm tra Session</h2>";

echo "<h3>📋 Thông tin Session hiện tại:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>🌐 Thông tin Server:</h3>";
echo "<p>REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "</p>";
echo "<p>PHP_SELF: " . $_SERVER['PHP_SELF'] . "</p>";

echo "<h3>🔐 Trạng thái đăng nhập:</h3>";
if (isset($_SESSION['user_id'])) {
    echo "<p style='color: green;'>✅ Đã đăng nhập</p>";
    echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
    echo "<p>User Name: " . ($_SESSION['user_name'] ?? 'N/A') . "</p>";
    echo "<p>User Email: " . ($_SESSION['user_email'] ?? 'N/A') . "</p>";
    
    // Kiểm tra user trong database
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "<p style='color: green;'>✅ User tồn tại trong database</p>";
            echo "<p>DB Name: " . $user['name'] . "</p>";
            echo "<p>DB Email: " . $user['email'] . "</p>";
        } else {
            echo "<p style='color: red;'>❌ User không tồn tại trong database!</p>";
        }
    } catch(Exception $e) {
        echo "<p style='color: red;'>❌ Lỗi kiểm tra database: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Chưa đăng nhập</p>";
}

echo "<hr>";
echo "<h3>🚀 Hành động:</h3>";
if (isset($_SESSION['user_id'])) {
    echo "<p><a href='dashboard.php'>→ Vào Dashboard</a></p>";
    echo "<p><a href='logout.php'>→ Đăng xuất</a></p>";
} else {
    echo "<p><a href='index.php'>→ Đăng nhập</a></p>";
    echo "<p><a href='register.php'>→ Đăng ký</a></p>";
}

echo "<p><a href='test_db.php'>→ Kiểm tra Database</a></p>";
?>

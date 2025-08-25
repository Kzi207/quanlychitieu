<?php
// File test database
require_once 'config/database.php';

echo "<h2>🔍 Kiểm tra Database</h2>";

// Kiểm tra kết nối
try {
    echo "<p>✅ Kết nối database thành công</p>";
} catch(Exception $e) {
    echo "<p>❌ Lỗi kết nối: " . $e->getMessage() . "</p>";
    exit;
}

// Kiểm tra bảng users
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $user_count = $stmt->fetch()['count'];
    echo "<p>👥 Số lượng users: <strong>$user_count</strong></p>";
    
    if ($user_count > 0) {
        $stmt = $pdo->prepare("SELECT id, name, email, created_at FROM users");
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        echo "<h3>📋 Danh sách users:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Tên</th><th>Email</th><th>Ngày tạo</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['name']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch(Exception $e) {
    echo "<p>❌ Lỗi kiểm tra users: " . $e->getMessage() . "</p>";
}

// Kiểm tra bảng categories
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM categories");
    $stmt->execute();
    $cat_count = $stmt->fetch()['count'];
    echo "<p>🏷️ Số lượng categories: <strong>$cat_count</strong></p>";
} catch(Exception $e) {
    echo "<p>❌ Lỗi kiểm tra categories: " . $e->getMessage() . "</p>";
}

// Kiểm tra bảng transactions
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM transactions");
    $stmt->execute();
    $trans_count = $stmt->fetch()['count'];
    echo "<p>💰 Số lượng transactions: <strong>$trans_count</strong></p>";
} catch(Exception $e) {
    echo "<p>❌ Lỗi kiểm tra transactions: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🚀 Hướng dẫn sử dụng:</h3>";
echo "<ol>";
echo "<li>Nếu chưa có user nào, hãy truy cập <a href='register.php'>đăng ký</a> hoặc dùng tài khoản mẫu</li>";
echo "<li>Đăng nhập tại <a href='index.php'>trang chủ</a></li>";
echo "<li>Vào <a href='dashboard.php'>dashboard</a> để thêm giao dịch</li>";
echo "</ol>";

if ($user_count == 0) {
    echo "<div style='background: #fff3cd; color: #856404; padding: 15px; margin: 20px; border-radius: 5px; border: 1px solid #ffeaa7;'>";
    echo "<strong>⚠️ Chưa có tài khoản nào!</strong><br>";
    echo "Hãy <a href='register.php'>đăng ký tài khoản mới</a> hoặc refresh trang này để tạo tài khoản mẫu.";
    echo "</div>";
}
?>

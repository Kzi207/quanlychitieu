<?php
echo "<h1>🔍 DEBUG VẤN ĐỀ ĐĂNG NHẬP</h1>";

// Kiểm tra PHP và extensions
echo "<h2>📋 Kiểm tra PHP</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>PDO MySQL:</strong> " . (extension_loaded('pdo_mysql') ? '✓ Có' : '✗ Không') . "</p>";
echo "<p><strong>Password Hash:</strong> " . (function_exists('password_hash') ? '✓ Có' : '✗ Không') . "</p>";

// Kiểm tra kết nối database
echo "<h2>🔌 Kiểm tra kết nối Database</h2>";

try {
    // Thử kết nối MySQL
    $pdo = new PDO("mysql:host=localhost;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✓ Kết nối MySQL thành công!</p>";
    
    // Kiểm tra database
    $databases = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('quanlychitieu', $databases)) {
        echo "<p style='color: green;'>✓ Database 'quanlychitieu' tồn tại</p>";
        
        // Chọn database
        $pdo->exec("USE quanlychitieu");
        echo "<p style='color: green;'>✓ Đã chọn database 'quanlychitieu'</p>";
        
        // Kiểm tra bảng
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "<p><strong>Bảng hiện có:</strong></p><ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
        
        // Kiểm tra bảng master_accounts
        if (in_array('master_accounts', $tables)) {
            echo "<p style='color: green;'>✓ Bảng 'master_accounts' tồn tại</p>";
            
            // Kiểm tra cấu trúc bảng
            $columns = $pdo->query("DESCRIBE master_accounts")->fetchAll(PDO::FETCH_ASSOC);
            echo "<p><strong>Cấu trúc bảng master_accounts:</strong></p><ul>";
            foreach ($columns as $column) {
                echo "<li>{$column['Field']} - {$column['Type']} - {$column['Null']} - {$column['Key']}</li>";
            }
            echo "</ul>";
            
            // Kiểm tra dữ liệu
            $count = $pdo->query("SELECT COUNT(*) FROM master_accounts")->fetchColumn();
            echo "<p><strong>Số tài khoản:</strong> $count</p>";
            
            if ($count > 0) {
                $accounts = $pdo->query("SELECT id, username, email, role, status FROM master_accounts")->fetchAll(PDO::FETCH_ASSOC);
                echo "<p><strong>Danh sách tài khoản:</strong></p><ul>";
                foreach ($accounts as $account) {
                    echo "<li>ID: {$account['id']}, Username: {$account['username']}, Email: {$account['email']}, Role: {$account['role']}, Status: {$account['status']}</li>";
                }
                echo "</ul>";
                
                // Test tài khoản admin
                $stmt = $pdo->prepare("SELECT * FROM master_accounts WHERE username = ?");
                $stmt->execute(['admin']);
                $admin = $stmt->fetch();
                
                if ($admin) {
                    echo "<p style='color: green;'>✓ Tìm thấy tài khoản admin</p>";
                    echo "<p><strong>ID:</strong> {$admin['id']}</p>";
                    echo "<p><strong>Username:</strong> {$admin['username']}</p>";
                    echo "<p><strong>Email:</strong> {$admin['email']}</p>";
                    echo "<p><strong>Role:</strong> {$admin['role']}</p>";
                    echo "<p><strong>Status:</strong> {$admin['status']}</p>";
                    
                    // Test password
                    if (password_verify('password', $admin['password'])) {
                        echo "<p style='color: green;'>✓ Mật khẩu 'password' khớp!</p>";
                    } else {
                        echo "<p style='color: red;'>✗ Mật khẩu 'password' KHÔNG khớp!</p>";
                        echo "<p><strong>Hash trong DB:</strong> " . substr($admin['password'], 0, 50) . "...</p>";
                        
                        // Tạo hash mới để so sánh
                        $newHash = password_hash('password', PASSWORD_DEFAULT);
                        echo "<p><strong>Hash mới cho 'password':</strong> " . substr($newHash, 0, 50) . "...</p>";
                    }
                } else {
                    echo "<p style='color: red;'>✗ KHÔNG tìm thấy tài khoản admin</p>";
                }
            } else {
                echo "<p style='color: red;'>✗ Bảng master_accounts trống!</p>";
            }
            
        } else {
            echo "<p style='color: red;'>✗ Bảng 'master_accounts' KHÔNG tồn tại!</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Database 'quanlychitieu' KHÔNG tồn tại!</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Lỗi database: " . $e->getMessage() . "</p>";
}

// Kiểm tra file config
echo "<h2>📁 Kiểm tra File Config</h2>";
if (file_exists('config/database.php')) {
    echo "<p style='color: green;'>✓ File config/database.php tồn tại</p>";
    
    // Đọc nội dung file config
    $configContent = file_get_contents('config/database.php');
    if (strpos($configContent, 'localhost') !== false) {
        echo "<p style='color: green;'>✓ Config có host localhost</p>";
    } else {
        echo "<p style='color: red;'>✗ Config KHÔNG có host localhost</p>";
    }
    
    if (strpos($configContent, 'quanlychitieu') !== false) {
        echo "<p style='color: green;'>✓ Config có database quanlychitieu</p>";
    } else {
        echo "<p style='color: red;'>✗ Config KHÔNG có database quanlychitieu</p>";
    }
    
} else {
    echo "<p style='color: red;'>✗ File config/database.php KHÔNG tồn tại!</p>";
}

// Kiểm tra file functions
echo "<h2>🔧 Kiểm tra File Functions</h2>";
if (file_exists('includes/functions.php')) {
    echo "<p style='color: green;'>✓ File includes/functions.php tồn tại</p>";
    
    // Kiểm tra function authenticateUser
    $functionsContent = file_get_contents('includes/functions.php');
    if (strpos($functionsContent, 'function authenticateUser') !== false) {
        echo "<p style='color: green;'>✓ Function authenticateUser tồn tại</p>";
    } else {
        echo "<p style='color: red;'>✗ Function authenticateUser KHÔNG tồn tại!</p>";
    }
    
} else {
    echo "<p style='color: red;'>✗ File includes/functions.php KHÔNG tồn tại!</p>";
}

// Test tạo tài khoản admin mới
echo "<h2>🆕 Tạo Tài Khoản Admin Mới</h2>";

try {
    if (isset($pdo)) {
        // Xóa tài khoản admin cũ
        $pdo->exec("DELETE FROM master_accounts WHERE username = 'admin'");
        echo "<p style='color: orange;'>⚠ Đã xóa tài khoản admin cũ</p>";
        
        // Tạo tài khoản admin mới
        $adminPassword = 'password';
        $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO master_accounts (username, email, password, full_name, role, status) 
            VALUES (?, ?, ?, ?, 'admin', 'active')
        ");
        
        if ($stmt->execute(['admin', 'admin@example.com', $hashedPassword, 'Administrator'])) {
            $adminId = $pdo->lastInsertId();
            echo "<p style='color: green;'>✓ Đã tạo tài khoản admin mới!</p>";
            echo "<p><strong>ID:</strong> $adminId</p>";
            echo "<p><strong>Username:</strong> admin</p>";
            echo "<p><strong>Password:</strong> password</p>";
            echo "<p><strong>Hash:</strong> " . substr($hashedPassword, 0, 50) . "...</p>";
            
            // Test password verify
            if (password_verify('password', $hashedPassword)) {
                echo "<p style='color: green;'>✓ Password verify thành công!</p>";
            } else {
                echo "<p style='color: red;'>✗ Password verify thất bại!</p>";
            }
            
        } else {
            echo "<p style='color: red;'>✗ Lỗi khi tạo tài khoản admin!</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Lỗi: " . $e->getMessage() . "</p>";
}

echo "<hr><h2>🔑 HƯỚNG DẪN ĐĂNG NHẬP</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>1. Đảm bảo XAMPP đang chạy (Apache + MySQL)</strong></p>";
echo "<p><strong>2. Truy cập:</strong> <a href='http://localhost/quanlychitieu/login.php' target='_blank'>http://localhost/quanlychitieu/login.php</a></p>";
echo "<p><strong>3. Thông tin đăng nhập:</strong></p>";
echo "<ul>";
echo "<li><strong>Username:</strong> admin</li>";
echo "<li><strong>Password:</strong> password</li>";
echo "<li><strong>Email:</strong> admin@example.com</li>";
echo "</ul>";
echo "</div>";

echo "<hr><h2>🚨 Nếu vẫn lỗi</h2>";
echo "<p>Hãy kiểm tra:</p>";
echo "<ul>";
echo "<li>XAMPP Control Panel - Apache và MySQL có status 'Running' không?</li>";
echo "<li>Trình duyệt có hiển thị lỗi gì không?</li>";
echo "<li>Console trình duyệt có lỗi JavaScript không?</li>";
echo "<li>File error log của Apache có ghi lỗi gì không?</li>";
echo "</ul>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
h1, h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
p { margin: 10px 0; }
ul { margin: 10px 0; padding-left: 20px; }
li { margin: 5px 0; }
code { background: #e9ecef; padding: 2px 4px; border-radius: 3px; }
</style>

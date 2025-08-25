<?php
// Cấu hình cơ sở dữ liệu
define('DB_HOST', 'localhost');
define('DB_NAME', 'quanlychitieu');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());
}

// Tạo bảng nếu chưa tồn tại
function createTables() {
    global $pdo;
    
    // Bảng người dùng
    $sql_users = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    // Bảng danh mục
    $sql_categories = "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        type ENUM('income', 'expense') NOT NULL,
        color VARCHAR(7) DEFAULT '#007bff',
        icon VARCHAR(50) DEFAULT 'fas fa-tag',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    // Bảng giao dịch
    $sql_transactions = "CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type ENUM('income', 'expense') NOT NULL,
        amount DECIMAL(15,2) NOT NULL,
        description TEXT,
        category_id INT NOT NULL,
        transaction_date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    )";
    
    // Bảng ngân sách
    $sql_budgets = "CREATE TABLE IF NOT EXISTS budgets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        category_id INT,
        amount DECIMAL(15,2) NOT NULL,
        period ENUM('monthly', 'yearly') DEFAULT 'monthly',
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    )";
    
    // Bảng mục tiêu tiết kiệm
    $sql_goals = "CREATE TABLE IF NOT EXISTS savings_goals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(200) NOT NULL,
        target_amount DECIMAL(15,2) NOT NULL,
        current_amount DECIMAL(15,2) DEFAULT 0,
        target_date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    // Bảng token ghi nhớ đăng nhập
    $sql_auth_tokens = "CREATE TABLE IF NOT EXISTS auth_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        selector CHAR(24) NOT NULL UNIQUE,
        validator_hash CHAR(64) NOT NULL,
        expires DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX (selector),
        INDEX (expires)
    )";
    
    try {
        $pdo->exec($sql_users);
        $pdo->exec($sql_categories);
        $pdo->exec($sql_transactions);
        $pdo->exec($sql_budgets);
        $pdo->exec($sql_goals);
        $pdo->exec($sql_auth_tokens);
        
        // Bổ sung cột avatar nếu chưa có
        $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) NULL AFTER password");
        
        // Thêm danh mục mặc định
        insertDefaultCategories();
        
    } catch(PDOException $e) {
        die("Lỗi tạo bảng: " . $e->getMessage());
    }
}

// Thêm danh mục mặc định
function insertDefaultCategories() {
    global $pdo;
    
    $default_categories = [
        ['name' => 'Lương', 'type' => 'income', 'color' => '#28a745', 'icon' => 'fas fa-money-bill-wave'],
        ['name' => 'Thưởng', 'type' => 'income', 'color' => '#ffc107', 'icon' => 'fas fa-gift'],
        ['name' => 'Đầu tư', 'type' => 'income', 'color' => '#17a2b8', 'icon' => 'fas fa-chart-line'],
        ['name' => 'Khác', 'type' => 'income', 'color' => '#6c757d', 'icon' => 'fas fa-plus'],
        ['name' => 'Ăn uống', 'type' => 'expense', 'color' => '#dc3545', 'icon' => 'fas fa-utensils'],
        ['name' => 'Mua sắm', 'type' => 'expense', 'color' => '#e83e8c', 'icon' => 'fas fa-shopping-cart'],
        ['name' => 'Đi lại', 'type' => 'expense', 'color' => '#fd7e14', 'icon' => 'fas fa-car'],
        ['name' => 'Học tập', 'type' => 'expense', 'color' => '#6f42c1', 'icon' => 'fas fa-graduation-cap'],
        ['name' => 'Giải trí', 'type' => 'expense', 'color' => '#20c997', 'icon' => 'fas fa-gamepad'],
        ['name' => 'Tiết kiệm', 'type' => 'expense', 'color' => '#28a745', 'icon' => 'fas fa-piggy-bank'],
        ['name' => 'Nợ', 'type' => 'expense', 'color' => '#dc3545', 'icon' => 'fas fa-credit-card'],
        ['name' => 'Y tế', 'type' => 'expense', 'color' => '#6f42c1', 'icon' => 'fas fa-heartbeat'],
        ['name' => 'Nhà ở', 'type' => 'expense', 'color' => '#fd7e14', 'icon' => 'fas fa-home']
    ];
    
    foreach ($default_categories as $category) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name, type, color, icon) VALUES (?, ?, ?, ?)");
        $stmt->execute([$category['name'], $category['type'], $category['color'], $category['icon']]);
    }
}

// Tạo tài khoản mẫu để test
function createSampleUser() {
    global $pdo;
    
    // Kiểm tra xem đã có tài khoản nào chưa
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $user_count = $stmt->fetch()['count'];
    
    if ($user_count == 0) {
        // Tạo tài khoản mẫu
        $sample_password = password_hash('123456', PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute(['Người dùng mẫu', 'demo@example.com', $sample_password]);
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; margin: 20px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
            echo "<strong>🎉 Tài khoản mẫu đã được tạo!</strong><br>";
            echo "Email: <strong>demo@example.com</strong><br>";
            echo "Mật khẩu: <strong>123456</strong><br>";
            echo "Bạn có thể dùng tài khoản này để đăng nhập và test hệ thống.";
            echo "</div>";
        } catch(PDOException $e) {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; margin: 20px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
            echo "<strong>❌ Lỗi tạo tài khoản mẫu:</strong> " . $e->getMessage();
            echo "</div>";
        }
    }
}

// Tạo bảng khi khởi tạo
createTables();

// Tạo tài khoản mẫu nếu cần
createSampleUser();
?>

<?php
require_once 'config/database.php';

// Hàm đăng nhập
function loginUser($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

// Hàm đăng ký
function registerUser($name, $email, $password) {
    global $pdo;
    
    // Kiểm tra email đã tồn tại
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email đã được sử dụng!'];
    }
    
    // Mã hóa mật khẩu
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $hashed_password]);
        return ['success' => true, 'message' => 'Đăng ký thành công!'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi đăng ký: ' . $e->getMessage()];
    }
}

// Hàm lấy thống kê tổng quan
function getDashboardStats($user_id, $period = 'month') {
    global $pdo;
    
    $date_condition = '';
    switch($period) {
        case 'week':
            $date_condition = "AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $date_condition = "AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            break;
        case 'year':
            $date_condition = "AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
            break;
    }
    
    // Tổng thu nhập
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND type = 'income' $date_condition");
    $stmt->execute([$user_id]);
    $total_income = $stmt->fetch()['total'];
    
    // Tổng chi tiêu
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND type = 'expense' $date_condition");
    $stmt->execute([$user_id]);
    $total_expense = $stmt->fetch()['total'];
    
    // Số dư
    $balance = $total_income - $total_expense;
    
    // Chi tiêu theo danh mục
    $stmt = $pdo->prepare("
        SELECT c.name, c.color, c.icon, COALESCE(SUM(t.amount), 0) as total
        FROM categories c
        LEFT JOIN transactions t ON c.id = t.category_id AND t.user_id = ? AND t.type = 'expense' $date_condition
        WHERE c.type = 'expense'
        GROUP BY c.id
        ORDER BY total DESC
    ");
    $stmt->execute([$user_id]);
    $expense_by_category = $stmt->fetchAll();
    
    // Giao dịch gần đây
    $stmt = $pdo->prepare("
        SELECT t.*, c.name as category_name, c.color, c.icon
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ?
        ORDER BY t.transaction_date DESC, t.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    $recent_transactions = $stmt->fetchAll();
    
    return [
        'total_income' => $total_income,
        'total_expense' => $total_expense,
        'balance' => $balance,
        'expense_by_category' => $expense_by_category,
        'recent_transactions' => $recent_transactions
    ];
}

// Hàm thêm giao dịch mới
function addTransaction($user_id, $type, $amount, $description, $category_id, $transaction_date) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, category_id, transaction_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $type, $amount, $description, $category_id, $transaction_date]);
        return ['success' => true, 'message' => 'Thêm giao dịch thành công!'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi thêm giao dịch: ' . $e->getMessage()];
    }
}

// Hàm cập nhật giao dịch
function updateTransaction($transaction_id, $user_id, $type, $amount, $description, $category_id, $transaction_date) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE transactions SET type = ?, amount = ?, description = ?, category_id = ?, transaction_date = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$type, $amount, $description, $category_id, $transaction_date, $transaction_id, $user_id]);
        return ['success' => true, 'message' => 'Cập nhật giao dịch thành công!'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi cập nhật giao dịch: ' . $e->getMessage()];
    }
}

// Hàm xóa giao dịch
function deleteTransaction($transaction_id, $user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
        $stmt->execute([$transaction_id, $user_id]);
        return ['success' => true, 'message' => 'Xóa giao dịch thành công!'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi xóa giao dịch: ' . $e->getMessage()];
    }
}

// Hàm lấy danh sách giao dịch
function getTransactions($user_id, $filters = []) {
    global $pdo;
    
    $where_conditions = ["t.user_id = ?"];
    $params = [$user_id];
    
    if (!empty($filters['type'])) {
        $where_conditions[] = "t.type = ?";
        $params[] = $filters['type'];
    }
    
    if (!empty($filters['category_id'])) {
        $where_conditions[] = "t.category_id = ?";
        $params[] = $filters['category_id'];
    }
    
    if (!empty($filters['start_date'])) {
        $where_conditions[] = "t.transaction_date >= ?";
        $params[] = $filters['start_date'];
    }
    
    if (!empty($filters['end_date'])) {
        $where_conditions[] = "t.transaction_date <= ?";
        $params[] = $filters['end_date'];
    }
    
    if (!empty($filters['search'])) {
        $where_conditions[] = "(t.description LIKE ? OR c.name LIKE ?)";
        $params[] = "%{$filters['search']}%";
        $params[] = "%{$filters['search']}%";
    }
    
    $where_clause = implode(" AND ", $where_conditions);
    
    $sql = "
        SELECT t.*, c.name as category_name, c.color, c.icon
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE $where_clause
        ORDER BY t.transaction_date DESC, t.created_at DESC
    ";
    
    if (!empty($filters['limit'])) {
        $sql .= " LIMIT " . (int)$filters['limit'];
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Hàm lấy danh mục
function getCategories($type = null) {
    global $pdo;
    
    $sql = "SELECT * FROM categories";
    $params = [];
    
    if ($type) {
        $sql .= " WHERE type = ?";
        $params[] = $type;
    }
    
    $sql .= " ORDER BY name";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Hàm lấy thống kê theo thời gian
function getTimeSeriesStats($user_id, $period = 'month', $months = 12) {
    global $pdo;
    
    $sql = "
        SELECT 
            DATE_FORMAT(transaction_date, '%Y-%m') as month,
            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
        FROM transactions 
        WHERE user_id = ? 
        AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
        GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
        ORDER BY month
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $months]);
    return $stmt->fetchAll();
}

// Hàm thêm ngân sách
function addBudget($user_id, $category_id, $amount, $period, $start_date, $end_date) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO budgets (user_id, category_id, amount, period, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $category_id, $amount, $period, $start_date, $end_date]);
        return ['success' => true, 'message' => 'Thêm ngân sách thành công!'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi thêm ngân sách: ' . $e->getMessage()];
    }
}

// Lấy danh sách ngân sách của người dùng
function getBudgets($user_id) {
    global $pdo;
    
    $sql = "
        SELECT b.*, c.name AS category_name
        FROM budgets b
        LEFT JOIN categories c ON b.category_id = c.id
        WHERE b.user_id = ?
        ORDER BY b.start_date DESC, b.created_at DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Xóa ngân sách theo id và user
function deleteBudget($budget_id, $user_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("DELETE FROM budgets WHERE id = ? AND user_id = ?");
        $stmt->execute([$budget_id, $user_id]);
        return ['success' => true, 'message' => 'Xóa ngân sách thành công!'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi xóa ngân sách: ' . $e->getMessage()];
    }
}

// Hàm thêm mục tiêu tiết kiệm
function addSavingsGoal($user_id, $name, $target_amount, $target_date) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO savings_goals (user_id, name, target_amount, target_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $name, $target_amount, $target_date]);
        return ['success' => true, 'message' => 'Thêm mục tiêu tiết kiệm thành công!'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi thêm mục tiêu: ' . $e->getMessage()];
    }
}

// Lấy danh sách mục tiêu tiết kiệm của người dùng
function getSavingsGoals($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM savings_goals WHERE user_id = ? ORDER BY target_date ASC, created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Xóa mục tiêu tiết kiệm theo id và user
function deleteSavingsGoal($goal_id, $user_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("DELETE FROM savings_goals WHERE id = ? AND user_id = ?");
        $stmt->execute([$goal_id, $user_id]);
        return ['success' => true, 'message' => 'Xóa mục tiêu thành công!'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi xóa mục tiêu: ' . $e->getMessage()];
    }
}

// Hàm định dạng tiền tệ
function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . ' ₫';
}

// Cập nhật hồ sơ người dùng (tên, email)
function updateUserProfile($user_id, $name, $email) {
    global $pdo;
    try {
        // Kiểm tra email đã tồn tại bởi người khác
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email đã được sử dụng bởi tài khoản khác!'];
        }
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->execute([$name, $email, $user_id]);
        return ['success' => true, 'message' => 'Cập nhật hồ sơ thành công!'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi cập nhật hồ sơ: ' . $e->getMessage()];
    }
}

// Đổi mật khẩu người dùng
function changeUserPassword($user_id, $current_password, $new_password) {
    global $pdo;
    try {
        // Lấy mật khẩu hiện tại
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch();
        if (!$row) {
            return ['success' => false, 'message' => 'Không tìm thấy người dùng!'];
        }
        if (!password_verify($current_password, $row['password'])) {
            return ['success' => false, 'message' => 'Mật khẩu hiện tại không đúng!'];
        }
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $user_id]);
        return ['success' => true, 'message' => 'Đổi mật khẩu thành công!'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi đổi mật khẩu: ' . $e->getMessage()];
    }
}

// Lấy thông tin người dùng theo ID
function getUserById($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, name, email, created_at, updated_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// Tạo token ghi nhớ đăng nhập
function createRememberToken($user_id) {
    global $pdo;
    $selector = bin2hex(random_bytes(12));
    $validator = bin2hex(random_bytes(32));
    $validatorHash = hash('sha256', $validator);
    $expires = date('Y-m-d H:i:s', time() + 60*60*24*30); // 30 ngày

    $stmt = $pdo->prepare("INSERT INTO auth_tokens (user_id, selector, validator_hash, expires) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $selector, $validatorHash, $expires]);

    // Set cookie: selector|validator
    setcookie('remember', $selector . ':' . $validator, time() + 60*60*24*30, '/', '', false, true);
}

// Xoá token ghi nhớ
function clearRememberToken($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM auth_tokens WHERE user_id = ?");
    $stmt->execute([$user_id]);
    setcookie('remember', '', time() - 3600, '/', '', false, true);
}

// Thử đăng nhập bằng cookie
function tryAutoLoginByCookie() {
    global $pdo;
    if (isset($_SESSION['user_id'])) { return; }
    if (empty($_COOKIE['remember'])) { return; }

    $parts = explode(':', $_COOKIE['remember']);
    if (count($parts) !== 2) { return; }
    list($selector, $validator) = $parts;
    $stmt = $pdo->prepare("SELECT * FROM auth_tokens WHERE selector = ? AND expires > NOW()");
    $stmt->execute([$selector]);
    $row = $stmt->fetch();
    if (!$row) { return; }
    if (!hash_equals($row['validator_hash'], hash('sha256', $validator))) { return; }

    // Lấy user và set session
    $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id = ?");
    $stmt->execute([$row['user_id']]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
    }
}

// Hàm kiểm tra đăng nhập
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        tryAutoLoginByCookie();
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php');
            exit();
        }
    }
}

// Hàm đăng xuất
function logout() {
    if (isset($_SESSION['user_id'])) {
        clearRememberToken($_SESSION['user_id']);
    }
    session_destroy();
    header('Location: index.php');
    exit();
}
?>

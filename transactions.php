<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

checkLogin();

// Xử lý xóa giao dịch
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $transaction_id = intval($_GET['delete']);
    $result = deleteTransaction($transaction_id, $_SESSION['user_id']);
    if ($result['success']) {
        $success_message = $result['message'];
    } else {
        $error_message = $result['message'];
    }
}

// Xử lý tìm kiếm và lọc
$filters = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filters['search'] = trim($_GET['search']);
}
if (isset($_GET['type']) && !empty($_GET['type'])) {
    $filters['type'] = $_GET['type'];
}
if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
    $filters['category_id'] = intval($_GET['category_id']);
}
if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $filters['start_date'] = $_GET['start_date'];
}
if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $filters['end_date'] = $_GET['end_date'];
}

// Lấy danh sách giao dịch
$transactions = getTransactions($_SESSION['user_id'], $filters);
$categories = getCategories();

// Tính tổng thu chi
$total_income = 0;
$total_expense = 0;
foreach ($transactions as $transaction) {
    if ($transaction['type'] == 'income') {
        $total_income += $transaction['amount'];
    } else {
        $total_expense += $transaction['amount'];
    }
}
$balance = $total_income - $total_expense;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Giao Dịch - Quản Lý Chi Tiêu</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="main-content">
        <div class="container">
            <!-- Header trang -->
            <div class="page-header">
                <div class="page-title">
                    <h1><i class="fas fa-exchange-alt"></i> Quản Lý Giao Dịch</h1>
                    <p>Xem và quản lý tất cả giao dịch thu chi</p>
                </div>
                <a href="add-transaction.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Thêm Giao Dịch
                </a>
            </div>
            
            <!-- Thông báo -->
            <?php if (isset($success_message) || isset($_GET['success'])): ?>
                <div class="alert alert-success"><?php echo $success_message ?? $_GET['success']; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message) || isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?php echo $error_message ?? $_GET['error']; ?></div>
            <?php endif; ?>
            
            <!-- Thống kê nhanh -->
            <div class="stats-grid">
                <div class="stat-card income">
                    <div class="stat-icon">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Tổng Thu Nhập</h3>
                        <p class="stat-amount"><?php echo formatCurrency($total_income); ?></p>
                    </div>
                </div>
                
                <div class="stat-card expense">
                    <div class="stat-icon">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Tổng Chi Tiêu</h3>
                        <p class="stat-amount"><?php echo formatCurrency($total_expense); ?></p>
                    </div>
                </div>
                
                <div class="stat-card balance <?php echo $balance >= 0 ? 'positive' : 'negative'; ?>">
                    <div class="stat-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Số Dư</h3>
                        <p class="stat-amount"><?php echo formatCurrency($balance); ?></p>
                    </div>
                </div>
                
                <div class="stat-card savings">
                    <div class="stat-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Tổng Giao Dịch</h3>
                        <p class="stat-amount"><?php echo count($transactions); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Form tìm kiếm và lọc -->
            <div class="form-container">
                <form method="GET" class="search-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="search"><i class="fas fa-search"></i> Tìm kiếm</label>
                            <input type="text" id="search" name="search" 
                                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" 
                                   placeholder="Tìm theo mô tả hoặc danh mục...">
                        </div>
                        
                        <div class="form-group">
                            <label for="type"><i class="fas fa-filter"></i> Loại giao dịch</label>
                            <select id="type" name="type">
                                <option value="">Tất cả</option>
                                <option value="income" <?php echo (isset($_GET['type']) && $_GET['type'] == 'income') ? 'selected' : ''; ?>>Thu nhập</option>
                                <option value="expense" <?php echo (isset($_GET['type']) && $_GET['type'] == 'expense') ? 'selected' : ''; ?>>Chi tiêu</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id"><i class="fas fa-tag"></i> Danh mục</label>
                            <select id="category_id" name="category_id">
                                <option value="">Tất cả danh mục</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="start_date"><i class="fas fa-calendar"></i> Từ ngày</label>
                            <input type="date" id="start_date" name="start_date" 
                                   value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="end_date"><i class="fas fa-calendar"></i> Đến ngày</label>
                            <input type="date" id="end_date" name="end_date" 
                                   value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>">
                        </div>
                        
                        <div class="form-group" style="display: flex; align-items: end;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Tìm Kiếm
                            </button>
                            <a href="transactions.php" class="btn btn-outline" style="margin-left: 10px;">
                                <i class="fas fa-times"></i> Xóa Lọc
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Danh sách giao dịch -->
            <div class="transactions-section">
                <div class="section-header">
                    <h3>Danh Sách Giao Dịch</h3>
                    <div class="section-actions">
                        <span class="transaction-count"><?php echo count($transactions); ?> giao dịch</span>
                    </div>
                </div>
                
                <?php if (empty($transactions)): ?>
                    <div class="empty-state">
                        <i class="fas fa-receipt"></i>
                        <p>Không tìm thấy giao dịch nào</p>
                        <a href="add-transaction.php" class="btn btn-primary">Thêm giao dịch đầu tiên</a>
                    </div>
                <?php else: ?>
                    <div class="transactions-list">
                        <?php foreach ($transactions as $transaction): ?>
                            <div class="transaction-item">
                                <div class="transaction-icon" style="background-color: <?php echo $transaction['color']; ?>">
                                    <i class="<?php echo $transaction['icon']; ?>"></i>
                                </div>
                                
                                <div class="transaction-details">
                                    <h4><?php echo htmlspecialchars($transaction['description']); ?></h4>
                                    <p class="transaction-category"><?php echo $transaction['category_name']; ?></p>
                                    <p class="transaction-date"><?php echo date('d/m/Y', strtotime($transaction['transaction_date'])); ?></p>
                                </div>
                                
                                <div class="transaction-amount <?php echo $transaction['type']; ?>">
                                    <?php echo ($transaction['type'] == 'income' ? '+' : '-') . formatCurrency($transaction['amount']); ?>
                                </div>
                                
                                <div class="transaction-actions">
                                    <a href="edit-transaction.php?id=<?php echo $transaction['id']; ?>" class="btn-icon" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="transactions.php?delete=<?php echo $transaction['id']; ?>" 
                                       class="btn-icon delete" title="Xóa" 
                                       onclick="return confirm('Bạn có chắc muốn xóa giao dịch này?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Tự động điền ngày hôm nay nếu chưa có
        document.addEventListener('DOMContentLoaded', function() {
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');
            
            if (!startDate.value) {
                const today = new Date();
                const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                startDate.value = firstDay.toISOString().split('T')[0];
            }
            
            if (!endDate.value) {
                const today = new Date();
                endDate.value = today.toISOString().split('T')[0];
            }
        });
    </script>
</body>
</html>

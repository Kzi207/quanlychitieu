<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

checkLogin();

// Handle quick add transaction
$quick_message = '';
$quick_type = '';

// Debug session
if (!isset($_SESSION['user_id'])) {
    $quick_message = 'Lỗi: Không tìm thấy user_id trong session. Vui lòng đăng nhập lại!';
    $quick_type = 'danger';
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_add'])) {
        $type = $_POST['type'] ?? '';
        $amount = isset($_POST['amount']) ? floatval(str_replace([',', '.'], ['', ''], preg_replace('/[^0-9.,]/', '', $_POST['amount']))) : 0;
        $description = trim($_POST['description'] ?? '');
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        $transaction_date = $_POST['transaction_date'] ?? date('Y-m-d');

        if ($type && $amount > 0 && $description && $category_id > 0) {
            $result = addTransaction($_SESSION['user_id'], $type, $amount, $description, $category_id, $transaction_date);
            if ($result['success']) {
                $quick_message = $result['message'];
                $quick_type = 'success';
            } else {
                $quick_message = $result['message'];
                $quick_type = 'danger';
            }
        } else {
            $quick_message = 'Vui lòng nhập đầy đủ thông tin hợp lệ!';
            $quick_type = 'danger';
        }
    }
}

$period = isset($_GET['period']) ? $_GET['period'] : 'month';
$stats = getDashboardStats($_SESSION['user_id'], $period);
$time_series = getTimeSeriesStats($_SESSION['user_id'], $period);
$categories = getCategories();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Quản Lý Chi Tiêu</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="main-content">
        <div class="container">
            <!-- Header Dashboard -->
            <div class="dashboard-header">
                <div class="welcome-section">
                    <h1>Xin chào, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Người dùng'); ?>! 👋</h1>
                    <p>Chào mừng bạn trở lại với hệ thống quản lý chi tiêu</p>
                </div>
                
                <div class="period-selector">
                    <select id="period-select" onchange="changePeriod(this.value)">
                        <option value="week" <?php echo $period == 'week' ? 'selected' : ''; ?>>Tuần này</option>
                        <option value="month" <?php echo $period == 'month' ? 'selected' : ''; ?>>Tháng này</option>
                        <option value="year" <?php echo $period == 'year' ? 'selected' : ''; ?>>Năm nay</option>
                    </select>
                </div>
            </div>
            
            <!-- Thống kê tổng quan -->
            <div class="stats-grid">
                <div class="stat-card income">
                    <div class="stat-icon">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Tổng Thu Nhập</h3>
                        <p class="stat-amount"><?php echo formatCurrency($stats['total_income']); ?></p>
                    </div>
                </div>
                
                <div class="stat-card expense">
                    <div class="stat-icon">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Tổng Chi Tiêu</h3>
                        <p class="stat-amount"><?php echo formatCurrency($stats['total_expense']); ?></p>
                    </div>
                </div>
                
                <div class="stat-card balance <?php echo $stats['balance'] >= 0 ? 'positive' : 'negative'; ?>">
                    <div class="stat-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Số Dư</h3>
                        <p class="stat-amount"><?php echo formatCurrency($stats['balance']); ?></p>
                    </div>
                </div>
                
                <div class="stat-card savings">
                    <div class="stat-icon">
                        <i class="fas fa-piggy-bank"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Tỷ Lệ Tiết Kiệm</h3>
                        <p class="stat-amount">
                            <?php 
                            if ($stats['total_income'] > 0) {
                                $savings_rate = ($stats['balance'] / $stats['total_income']) * 100;
                                echo number_format($savings_rate, 1) . '%';
                            } else {
                                echo '0%';
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Quick Add Transaction -->
            <div class="form-container">
                <?php if (!empty($quick_message)): ?>
                    <div class="alert alert-<?php echo $quick_type; ?>"><?php echo $quick_message; ?></div>
                <?php endif; ?>
                <form method="POST" class="transaction-form" id="quickAddForm">
                    <input type="hidden" name="quick_add" value="1">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="quick_type"><i class="fas fa-exchange-alt"></i> Loại giao dịch</label>
                            <select id="quick_type" name="type" required>
                                <option value="">Chọn loại</option>
                                <option value="income">Thu nhập (+)</option>
                                <option value="expense">Chi tiêu (-)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="quick_amount"><i class="fas fa-money-bill"></i> Số tiền</label>
                            <input type="text" id="quick_amount" name="amount" placeholder="Ví dụ: 100000" required>
                        </div>
                        <div class="form-group">
                            <label for="quick_category"><i class="fas fa-tag"></i> Danh mục</label>
                            <select id="quick_category" name="category_id" required>
                                <option value="">Chọn danh mục</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" data-type="<?php echo $cat['type']; ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="quick_description"><i class="fas fa-edit"></i> Mô tả</label>
                            <input type="text" id="quick_description" name="description" placeholder="Ví dụ: Mẹ gửi, ăn trưa, mua sách..." required>
                        </div>
                        <div class="form-group">
                            <label for="quick_date"><i class="fas fa-calendar"></i> Ngày</label>
                            <input type="date" id="quick_date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Thêm nhanh
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Biểu đồ và phân tích -->
            <div class="charts-section">
                <div class="chart-container">
                    <div class="chart-header">
                        <h3>Xu Hướng Thu Chi</h3>
                        <p>Biểu đồ thu nhập và chi tiêu theo thời gian</p>
                    </div>
                    <canvas id="trendChart"></canvas>
                </div>
                
                <div class="chart-container">
                    <div class="chart-header">
                        <h3>Chi Tiêu Theo Danh Mục</h3>
                        <p>Phân bổ chi tiêu theo các danh mục</p>
                    </div>
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
            
            <!-- Giao dịch gần đây -->
            <div class="recent-transactions">
                <div class="section-header">
                    <h3>Giao Dịch Gần Đây</h3>
                    <a href="transactions.php" class="btn btn-outline">Xem tất cả</a>
                </div>
                
                <div class="transactions-list">
                    <?php if (empty($stats['recent_transactions'])): ?>
                        <div class="empty-state">
                            <i class="fas fa-receipt"></i>
                            <p>Chưa có giao dịch nào</p>
                            <a href="add-transaction.php" class="btn btn-primary">Thêm giao dịch đầu tiên</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($stats['recent_transactions'] as $transaction): ?>
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
                                    <a href="delete-transaction.php?id=<?php echo $transaction['id']; ?>" class="btn-icon delete" title="Xóa" onclick="return confirm('Bạn có chắc muốn xóa giao dịch này?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Hành động nhanh -->
            <div class="quick-actions">
                <div class="section-header">
                    <h3>Hành Động Nhanh</h3>
                </div>
                
                <div class="actions-grid">
                    <a href="add-transaction.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h4>Thêm Giao Dịch</h4>
                        <p>Ghi nhận thu chi mới</p>
                    </a>
                    
                    <a href="budgets.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <h4>Quản Lý Ngân Sách</h4>
                        <p>Thiết lập hạn mức chi tiêu</p>
                    </a>
                    
                    <a href="goals.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h4>Mục Tiêu Tiết Kiệm</h4>
                        <p>Đặt và theo dõi mục tiêu</p>
                    </a>
                    
                    <a href="reports.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h4>Báo Cáo</h4>
                        <p>Xem báo cáo chi tiết</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Biểu đồ xu hướng
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        const trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($time_series, 'month')); ?>,
                datasets: [{
                    label: 'Thu nhập',
                    data: <?php echo json_encode(array_column($time_series, 'income')); ?>,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Chi tiêu',
                    data: <?php echo json_encode(array_column($time_series, 'expense')); ?>,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN').format(value) + ' ₫';
                            }
                        }
                    }
                }
            }
        });
        
        // Biểu đồ danh mục
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryChart = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($stats['expense_by_category'], 'name')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($stats['expense_by_category'], 'total')); ?>,
                    backgroundColor: <?php echo json_encode(array_column($stats['expense_by_category'], 'color')); ?>,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
        
        // Thay đổi khoảng thời gian
        function changePeriod(period) {
            window.location.href = 'dashboard.php?period=' + period;
        }

        // Quick form helpers
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('quick_type');
            const categorySelect = document.getElementById('quick_category');
            const amountInput = document.getElementById('quick_amount');

            function filterCategories() {
                const type = typeSelect.value;
                const options = Array.from(categorySelect.querySelectorAll('option'));
                options.forEach((opt, idx) => {
                    if (idx === 0) return; // skip placeholder
                    const t = opt.getAttribute('data-type');
                    opt.style.display = !type || t === type ? 'block' : 'none';
                });
                // reset selection if hidden
                if (categorySelect.selectedIndex > 0) {
                    const sel = categorySelect.options[categorySelect.selectedIndex];
                    if (sel.style.display === 'none') categorySelect.selectedIndex = 0;
                }
            }

            typeSelect.addEventListener('change', filterCategories);
            filterCategories();

            // format amount (allow digits only while typing, show thousands visually)
            amountInput.addEventListener('input', function() {
                const digits = this.value.replace(/[^\d]/g, '');
                if (!digits) { this.value = ''; return; }
                this.value = Number(digits).toLocaleString('vi-VN');
            });
        });
    </script>
</body>
</html>

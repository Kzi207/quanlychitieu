<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

checkLogin();

$categories = getCategories();
$message = '';
$message_type = '';

if ($_POST) {
    $type = $_POST['type'];
    $amount = floatval($_POST['amount']);
    $description = trim($_POST['description']);
    $category_id = intval($_POST['category_id']);
    $transaction_date = $_POST['transaction_date'];
    
    if (empty($description) || $amount <= 0 || $category_id <= 0) {
        $message = 'Vui lòng nhập đầy đủ thông tin!';
        $message_type = 'danger';
    } else {
        $result = addTransaction($_SESSION['user_id'], $type, $amount, $description, $category_id, $transaction_date);
        if ($result['success']) {
            $message = $result['message'];
            $message_type = 'success';
            // Reset form
            $_POST = [];
        } else {
            $message = $result['message'];
            $message_type = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Giao Dịch - Quản Lý Chi Tiêu</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="main-content">
        <div class="container">
            <div class="page-header">
                <div class="page-title">
                    <h1><i class="fas fa-plus"></i> Thêm Giao Dịch Mới</h1>
                    <p>Ghi nhận thu nhập hoặc chi tiêu mới</p>
                </div>
                <a href="dashboard.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Quay lại Dashboard
                </a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST" class="transaction-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="type"><i class="fas fa-exchange-alt"></i> Loại giao dịch</label>
                            <select id="type" name="type" required onchange="updateCategories()">
                                <option value="">Chọn loại giao dịch</option>
                                <option value="income" <?php echo (isset($_POST['type']) && $_POST['type'] == 'income') ? 'selected' : ''; ?>>Thu nhập</option>
                                <option value="expense" <?php echo (isset($_POST['type']) && $_POST['type'] == 'expense') ? 'selected' : ''; ?>>Chi tiêu</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="amount"><i class="fas fa-money-bill-wave"></i> Số tiền</label>
                            <input type="number" id="amount" name="amount" step="1000" min="0" 
                                   value="<?php echo isset($_POST['amount']) ? $_POST['amount'] : ''; ?>" 
                                   placeholder="Nhập số tiền" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description"><i class="fas fa-edit"></i> Mô tả</label>
                        <input type="text" id="description" name="description" 
                               value="<?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?>" 
                               placeholder="Mô tả giao dịch (ví dụ: Mẹ gửi tiền, Ăn trưa, Mua sắm...)" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category_id"><i class="fas fa-tag"></i> Danh mục</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">Chọn danh mục</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="transaction_date"><i class="fas fa-calendar"></i> Ngày giao dịch</label>
                            <input type="date" id="transaction_date" name="transaction_date" 
                                   value="<?php echo isset($_POST['transaction_date']) ? $_POST['transaction_date'] : date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu Giao Dịch
                        </button>
                        <a href="transactions.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Hướng dẫn sử dụng -->
            <div class="help-section">
                <div class="help-header">
                    <h3><i class="fas fa-lightbulb"></i> Hướng Dẫn Sử Dụng</h3>
                </div>
                
                <div class="help-content">
                    <div class="help-item">
                        <h4><i class="fas fa-arrow-up text-success"></i> Thu nhập</h4>
                        <p>Ghi nhận các khoản tiền bạn nhận được như: lương, thưởng, tiền từ người khác gửi, lợi nhuận đầu tư...</p>
                        <ul>
                            <li><strong>Lương:</strong> Tiền lương hàng tháng</li>
                            <li><strong>Thưởng:</strong> Tiền thưởng, hoa hồng</li>
                            <li><strong>Đầu tư:</strong> Lợi nhuận từ đầu tư</li>
                            <li><strong>Khác:</strong> Các khoản thu nhập khác</li>
                        </ul>
                    </div>
                    
                    <div class="help-item">
                        <h4><i class="fas fa-arrow-down text-danger"></i> Chi tiêu</h4>
                        <p>Ghi nhận các khoản tiền bạn chi ra cho các nhu cầu sinh hoạt, mua sắm...</p>
                        <ul>
                            <li><strong>Ăn uống:</strong> Tiền ăn, uống, cà phê...</li>
                            <li><strong>Mua sắm:</strong> Quần áo, đồ dùng, mỹ phẩm...</li>
                            <li><strong>Đi lại:</strong> Xăng xe, taxi, xe buýt...</li>
                            <li><strong>Học tập:</strong> Sách vở, khóa học, học phí...</li>
                            <li><strong>Giải trí:</strong> Xem phim, chơi game, du lịch...</li>
                            <li><strong>Tiết kiệm:</strong> Gửi tiết kiệm, đầu tư</li>
                            <li><strong>Nợ:</strong> Trả nợ, vay mượn</li>
                            <li><strong>Y tế:</strong> Khám bệnh, thuốc men</li>
                            <li><strong>Nhà ở:</strong> Tiền nhà, điện nước, internet...</li>
                        </ul>
                    </div>
                    
                    <div class="help-item">
                        <h4><i class="fas fa-tips"></i> Mẹo ghi chú</h4>
                        <p>Để dễ dàng theo dõi và tìm kiếm sau này, hãy ghi chú rõ ràng:</p>
                        <ul>
                            <li><strong>Thu nhập:</strong> "Lương tháng 12/2024", "Mẹ gửi tiền", "Thưởng cuối năm"</li>
                            <li><strong>Chi tiêu:</strong> "Ăn trưa tại nhà hàng ABC", "Mua áo khoác mùa đông", "Tiền xăng xe tháng 12"</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Dữ liệu danh mục
        const categories = <?php echo json_encode($categories); ?>;
        
        // Cập nhật danh mục theo loại giao dịch
        function updateCategories() {
            const type = document.getElementById('type').value;
            const categorySelect = document.getElementById('category_id');
            
            // Xóa tất cả options cũ
            categorySelect.innerHTML = '<option value="">Chọn danh mục</option>';
            
            if (type) {
                // Lọc danh mục theo loại
                const filteredCategories = categories.filter(cat => cat.type === type);
                
                filteredCategories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    option.style.color = category.color;
                    categorySelect.appendChild(option);
                });
            }
        }
        
        // Khởi tạo danh mục khi trang load
        document.addEventListener('DOMContentLoaded', function() {
            updateCategories();
            
            // Tự động điền ngày hôm nay nếu chưa có
            if (!document.getElementById('transaction_date').value) {
                document.getElementById('transaction_date').value = new Date().toISOString().split('T')[0];
            }
        });
        
        // Format số tiền khi nhập
        document.getElementById('amount').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^\d]/g, '');
            if (value) {
                value = parseInt(value).toLocaleString('vi-VN');
                e.target.value = value;
            }
        });
        
        // Format số tiền trước khi submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const amountField = document.getElementById('amount');
            const rawValue = amountField.value.replace(/[^\d]/g, '');
            amountField.value = rawValue;
        });
    </script>
</body>
</html>

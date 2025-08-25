<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

checkLogin();

$transaction_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$transaction_id) {
    header('Location: transactions.php');
    exit();
}

// Lấy thông tin giao dịch
$stmt = $pdo->prepare("
    SELECT t.*, c.name as category_name, c.type as category_type 
    FROM transactions t 
    JOIN categories c ON t.category_id = c.id 
    WHERE t.id = ? AND t.user_id = ?
");
$stmt->execute([$transaction_id, $_SESSION['user_id']]);
$transaction = $stmt->fetch();

if (!$transaction) {
    header('Location: transactions.php');
    exit();
}

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
        $result = updateTransaction($transaction_id, $_SESSION['user_id'], $type, $amount, $description, $category_id, $transaction_date);
        if ($result['success']) {
            $message = $result['message'];
            $message_type = 'success';
            // Cập nhật thông tin giao dịch
            $transaction['type'] = $type;
            $transaction['amount'] = $amount;
            $transaction['description'] = $description;
            $transaction['category_id'] = $category_id;
            $transaction['transaction_date'] = $transaction_date;
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
    <title>Chỉnh Sửa Giao Dịch - Quản Lý Chi Tiêu</title>
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
                    <h1><i class="fas fa-edit"></i> Chỉnh Sửa Giao Dịch</h1>
                    <p>Cập nhật thông tin giao dịch</p>
                </div>
                <a href="transactions.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
            
            <!-- Thông báo -->
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Form chỉnh sửa -->
            <div class="form-container">
                <form method="POST" class="transaction-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="type"><i class="fas fa-exchange-alt"></i> Loại giao dịch</label>
                            <select id="type" name="type" required onchange="updateCategories()">
                                <option value="">Chọn loại giao dịch</option>
                                <option value="income" <?php echo $transaction['type'] == 'income' ? 'selected' : ''; ?>>Thu nhập</option>
                                <option value="expense" <?php echo $transaction['type'] == 'expense' ? 'selected' : ''; ?>>Chi tiêu</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="amount"><i class="fas fa-money-bill-wave"></i> Số tiền</label>
                            <input type="number" id="amount" name="amount" step="1000" min="0" 
                                   value="<?php echo $transaction['amount']; ?>" 
                                   placeholder="Nhập số tiền" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description"><i class="fas fa-edit"></i> Mô tả</label>
                        <input type="text" id="description" name="description" 
                               value="<?php echo htmlspecialchars($transaction['description']); ?>" 
                               placeholder="Mô tả giao dịch" required>
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
                                   value="<?php echo $transaction['transaction_date']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cập Nhật
                        </button>
                        <a href="transactions.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Thông tin giao dịch hiện tại -->
            <div class="help-section">
                <div class="help-header">
                    <h3><i class="fas fa-info-circle"></i> Thông Tin Giao Dịch Hiện Tại</h3>
                </div>
                
                <div class="help-content">
                    <div class="help-item">
                        <h4><i class="fas fa-receipt"></i> Chi tiết giao dịch</h4>
                        <ul>
                            <li><strong>Loại:</strong> <?php echo $transaction['type'] == 'income' ? 'Thu nhập' : 'Chi tiêu'; ?></li>
                            <li><strong>Số tiền:</strong> <?php echo formatCurrency($transaction['amount']); ?></li>
                            <li><strong>Mô tả:</strong> <?php echo htmlspecialchars($transaction['description']); ?></li>
                            <li><strong>Danh mục:</strong> <?php echo htmlspecialchars($transaction['category_name']); ?></li>
                            <li><strong>Ngày:</strong> <?php echo date('d/m/Y', strtotime($transaction['transaction_date'])); ?></li>
                            <li><strong>Ngày tạo:</strong> <?php echo date('d/m/Y H:i', strtotime($transaction['created_at'])); ?></li>
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
        const currentCategoryId = <?php echo $transaction['category_id']; ?>;
        const currentType = '<?php echo $transaction['type']; ?>';
        
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
                    
                    // Chọn danh mục hiện tại nếu phù hợp
                    if (category.id == currentCategoryId && type == currentType) {
                        option.selected = true;
                    }
                    
                    categorySelect.appendChild(option);
                });
            }
        }
        
        // Khởi tạo khi trang load
        document.addEventListener('DOMContentLoaded', function() {
            updateCategories();
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

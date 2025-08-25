<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

checkLogin();

$success = '';
$error = '';

// Xử lý thêm ngân sách
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_budget'])) {
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
    $period = $_POST['period'] ?? 'monthly';
    $start_date = $_POST['start_date'] ?? date('Y-m-01');
    $end_date = $_POST['end_date'] ?? date('Y-m-t');

    if ($amount > 0 && $start_date && $end_date) {
        $result = addBudget($_SESSION['user_id'], $category_id, $amount, $period, $start_date, $end_date);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    } else {
        $error = 'Vui lòng nhập đầy đủ thông tin hợp lệ!';
    }
}

// Xử lý xóa ngân sách
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $res = deleteBudget($delId, $_SESSION['user_id']);
    if ($res['success']) {
        $success = $res['message'];
    } else {
        $error = $res['message'];
    }
}

$categories = getCategories('expense');
$budgets = getBudgets($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ngân sách - Quản Lý Chi Tiêu</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="main-content">
    <div class="container">
        <div class="page-header">
            <div class="page-title">
                <h1><i class="fas fa-chart-pie"></i> Quản lý Ngân sách</h1>
                <p>Đặt hạn mức chi tiêu theo khoảng thời gian</p>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" class="budget-form">
                <input type="hidden" name="add_budget" value="1">
                <div class="form-row">
                    <div class="form-group">
                        <label for="category_id"><i class="fas fa-tags"></i> Danh mục (tuỳ chọn)</label>
                        <select id="category_id" name="category_id">
                            <option value="">Tất cả danh mục</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="amount"><i class="fas fa-coins"></i> Hạn mức (₫)</label>
                        <input type="number" id="amount" name="amount" step="0.01" min="0" placeholder="Ví dụ: 3000000" required>
                    </div>
                    <div class="form-group">
                        <label for="period"><i class="fas fa-calendar-alt"></i> Chu kỳ</label>
                        <select id="period" name="period">
                            <option value="monthly">Hàng tháng</option>
                            <option value="yearly">Hàng năm</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date"><i class="fas fa-play"></i> Từ ngày</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo date('Y-m-01'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date"><i class="fas fa-stop"></i> Đến ngày</label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo date('Y-m-t'); ?>" required>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Thêm ngân sách</button>
                </div>
            </form>
        </div>

        <div class="transactions-section">
            <div class="section-header">
                <h3><i class="fas fa-list"></i> Danh sách ngân sách</h3>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Danh mục</th>
                            <th>Hạn mức</th>
                            <th>Chu kỳ</th>
                            <th>Từ ngày</th>
                            <th>Đến ngày</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($budgets)): ?>
                        <tr><td colspan="7" style="text-align:center;">Chưa có ngân sách nào</td></tr>
                    <?php else: ?>
                        <?php foreach ($budgets as $i => $b): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><?php echo htmlspecialchars($b['category_name'] ?? 'Tất cả'); ?></td>
                                <td><?php echo formatCurrency($b['amount']); ?></td>
                                <td><?php echo $b['period'] === 'yearly' ? 'Hàng năm' : 'Hàng tháng'; ?></td>
                                <td><?php echo htmlspecialchars($b['start_date']); ?></td>
                                <td><?php echo htmlspecialchars($b['end_date']); ?></td>
                                <td>
                                    <a class="btn btn-danger btn-sm" onclick="return confirm('Xóa ngân sách này?');" href="budgets.php?delete=<?php echo $b['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>

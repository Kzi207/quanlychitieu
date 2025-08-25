<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

checkLogin();

$success = '';
$error = '';

// Xử lý thêm mục tiêu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_goal'])) {
    $name = trim($_POST['name'] ?? '');
    $target_amount = isset($_POST['target_amount']) ? (float)$_POST['target_amount'] : 0;
    $target_date = $_POST['target_date'] ?? '';

    if ($name && $target_amount > 0 && $target_date) {
        $result = addSavingsGoal($_SESSION['user_id'], $name, $target_amount, $target_date);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    } else {
        $error = 'Vui lòng nhập đầy đủ thông tin hợp lệ!';
    }
}

// Xử lý xóa mục tiêu
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $res = deleteSavingsGoal($delId, $_SESSION['user_id']);
    if ($res['success']) {
        $success = $res['message'];
    } else {
        $error = $res['message'];
    }
}

$goals = getSavingsGoals($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mục tiêu tiết kiệm - Quản Lý Chi Tiêu</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="main-content">
    <div class="container">
        <div class="page-header">
            <div class="page-title">
                <h1><i class="fas fa-bullseye"></i> Mục tiêu tiết kiệm</h1>
                <p>Đặt mục tiêu và theo dõi tiến độ tiết kiệm</p>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" class="goal-form">
                <input type="hidden" name="add_goal" value="1">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name"><i class="fas fa-flag"></i> Tên mục tiêu</label>
                        <input type="text" id="name" name="name" placeholder="Ví dụ: Tiết kiệm 10 triệu trong 6 tháng" required>
                    </div>
                    <div class="form-group">
                        <label for="target_amount"><i class="fas fa-coins"></i> Số tiền mục tiêu (₫)</label>
                        <input type="number" id="target_amount" name="target_amount" step="0.01" min="0" placeholder="10000000" required>
                    </div>
                    <div class="form-group">
                        <label for="target_date"><i class="fas fa-calendar-alt"></i> Deadline</label>
                        <input type="date" id="target_date" name="target_date" required>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Thêm mục tiêu</button>
                </div>
            </form>
        </div>

        <div class="transactions-section">
            <div class="section-header">
                <h3><i class="fas fa-list"></i> Danh sách mục tiêu</h3>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tên mục tiêu</th>
                            <th>Số tiền mục tiêu</th>
                            <th>Deadline</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($goals)): ?>
                        <tr><td colspan="5" style="text-align:center;">Chưa có mục tiêu nào</td></tr>
                    <?php else: ?>
                        <?php foreach ($goals as $i => $g): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><?php echo htmlspecialchars($g['name']); ?></td>
                                <td><?php echo formatCurrency($g['target_amount']); ?></td>
                                <td><?php echo htmlspecialchars($g['target_date']); ?></td>
                                <td>
                                    <a class="btn btn-danger btn-sm" onclick="return confirm('Xóa mục tiêu này?');" href="goals.php?delete=<?php echo $g['id']; ?>">
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

<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

checkLogin();

// Lọc
$type = isset($_GET['type']) && in_array($_GET['type'], ['income','expense']) ? $_GET['type'] : '';
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$filters = [];
if ($type) $filters['type'] = $type;
if ($category_id) $filters['category_id'] = $category_id;
if ($start_date) $filters['start_date'] = $start_date;
if ($end_date) $filters['end_date'] = $end_date;

$transactions = getTransactions($_SESSION['user_id'], $filters);
$categories = getCategories();

// Tính tổng thu/chi theo bộ lọc
$total_income = 0; $total_expense = 0;
foreach ($transactions as $t) {
    if ($t['type'] === 'income') $total_income += (float)$t['amount'];
    if ($t['type'] === 'expense') $total_expense += (float)$t['amount'];
}
$balance = $total_income - $total_expense;

// Chuẩn bị dữ liệu biểu đồ: xu hướng theo ngày
$trendMap = [];
foreach ($transactions as $t) {
    $d = $t['transaction_date'];
    if (!isset($trendMap[$d])) $trendMap[$d] = ['income'=>0,'expense'=>0];
    $trendMap[$d][$t['type']] += (float)$t['amount'];
}
ksort($trendMap);
$trendLabels = array_keys($trendMap);
$trendIncome = array_map(function($d){return $d['income'];}, array_values($trendMap));
$trendExpense = array_map(function($d){return $d['expense'];}, array_values($trendMap));

// Biểu đồ theo danh mục (chỉ tính các giao dịch hiện có)
$byCategory = [];
foreach ($transactions as $t) {
    $key = $t['category_name'];
    if (!isset($byCategory[$key])) $byCategory[$key] = 0;
    if ($t['type'] === 'expense') {
        $byCategory[$key] += (float)$t['amount'];
    }
}
$catLabels = array_keys($byCategory);
$catValues = array_values($byCategory);
$catColors = [];
$colorMap = [];
foreach ($transactions as $t) {
    if (!isset($colorMap[$t['category_name']])) {
        $colorMap[$t['category_name']] = $t['color'] ?: '#1e88e5';
    }
}
foreach ($catLabels as $label) {
    $catColors[] = isset($colorMap[$label]) ? $colorMap[$label] : '#1e88e5';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo - Quản Lý Chi Tiêu</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="main-content">
    <div class="container">
        <div class="page-header">
            <div class="page-title">
                <h1><i class="fas fa-file-alt"></i> Báo cáo & Phân tích</h1>
                <p>Lọc, xem biểu đồ xu hướng và phân bổ theo danh mục</p>
            </div>
        </div>

        <div class="form-container">
            <form method="GET" class="search-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="type"><i class="fas fa-exchange-alt"></i> Loại</label>
                        <select id="type" name="type">
                            <option value="">Tất cả</option>
                            <option value="income" <?php echo $type==='income'?'selected':''; ?>>Thu nhập</option>
                            <option value="expense" <?php echo $type==='expense'?'selected':''; ?>>Chi tiêu</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="category_id"><i class="fas fa-tags"></i> Danh mục</label>
                        <select id="category_id" name="category_id">
                            <option value="0">Tất cả</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo $category_id===$c['id']?'selected':''; ?>>
                                    <?php echo htmlspecialchars($c['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="start_date"><i class="fas fa-play"></i> Từ ngày</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                    </div>
                    <div class="form-group">
                        <label for="end_date"><i class="fas fa-stop"></i> Đến ngày</label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Lọc</button>
                    <a href="reports.php" class="btn btn-secondary"><i class="fas fa-undo"></i> Reset</a>
                </div>
            </form>
        </div>

        <div class="stats-grid">
            <div class="stat-card income">
                <div class="stat-icon"><i class="fas fa-arrow-up"></i></div>
                <div class="stat-content">
                    <h3>Tổng Thu nhập</h3>
                    <p class="stat-amount"><?php echo formatCurrency($total_income); ?></p>
                </div>
            </div>
            <div class="stat-card expense">
                <div class="stat-icon"><i class="fas fa-arrow-down"></i></div>
                <div class="stat-content">
                    <h3>Tổng Chi tiêu</h3>
                    <p class="stat-amount"><?php echo formatCurrency($total_expense); ?></p>
                </div>
            </div>
            <div class="stat-card balance <?php echo $balance>=0?'positive':'negative'; ?>">
                <div class="stat-icon"><i class="fas fa-wallet"></i></div>
                <div class="stat-content">
                    <h3>Số dư</h3>
                    <p class="stat-amount"><?php echo formatCurrency($balance); ?></p>
                </div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <h3><i class="fas fa-chart-line"></i> Xu hướng theo ngày</h3>
                <canvas id="trendChart"></canvas>
            </div>
            <div class="chart-card">
                <h3><i class="fas fa-chart-pie"></i> Phân bổ chi tiêu theo danh mục</h3>
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <div class="transactions-section" style="margin-top: 2rem;">
            <div class="section-header">
                <h3><i class="fas fa-table"></i> Bảng giao dịch</h3>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Ngày</th>
                            <th>Loại</th>
                            <th>Danh mục</th>
                            <th>Mô tả</th>
                            <th>Số tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr><td colspan="6" style="text-align:center;">Không có dữ liệu</td></tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $i => $t): ?>
                            <tr>
                                <td><?php echo $i+1; ?></td>
                                <td><?php echo htmlspecialchars($t['transaction_date']); ?></td>
                                <td><?php echo $t['type'] === 'income' ? 'Thu' : 'Chi'; ?></td>
                                <td><?php echo htmlspecialchars($t['category_name']); ?></td>
                                <td><?php echo htmlspecialchars($t['description']); ?></td>
                                <td><?php echo formatCurrency($t['amount']); ?></td>
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

<script>
const trendLabels = <?php echo json_encode($trendLabels); ?>;
const trendIncome = <?php echo json_encode($trendIncome); ?>;
const trendExpense = <?php echo json_encode($trendExpense); ?>;
const catLabels = <?php echo json_encode($catLabels); ?>;
const catValues = <?php echo json_encode($catValues); ?>;
const catColors = <?php echo json_encode($catColors); ?>;

if (document.getElementById('trendChart')) {
    const ctx = document.getElementById('trendChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [
                {
                    label: 'Thu nhập',
                    data: trendIncome,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40,167,69,0.1)',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Chi tiêu',
                    data: trendExpense,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220,53,69,0.1)',
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: { x: { title: { display: true, text: 'Ngày' } }, y: { beginAtZero: true } }
        }
    });
}

if (document.getElementById('categoryChart')) {
    const ctx2 = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: catLabels,
            datasets: [{ data: catValues, backgroundColor: catColors }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'right' } }
        }
    });
}
</script>
</body>
</html>

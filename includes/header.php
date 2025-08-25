<header class="main-header">
    <div class="header-container">
        <div class="header-left">
            <a href="dashboard.php" class="logo">
                <i class="fas fa-wallet"></i>
                <span>Quản Lý Chi Tiêu</span>
            </a>
        </div>
        
        <nav class="main-nav">
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Tổng quan</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="transactions.php" class="nav-link">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Giao dịch</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="budgets.php" class="nav-link">
                        <i class="fas fa-chart-pie"></i>
                        <span>Ngân sách</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="goals.php" class="nav-link">
                        <i class="fas fa-bullseye"></i>
                        <span>Mục tiêu</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="reports.php" class="nav-link">
                        <i class="fas fa-file-alt"></i>
                        <span>Báo cáo</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="header-right">
            <div class="user-menu">
                <button class="user-toggle" onclick="toggleUserMenu()">
                    <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Người dùng'); ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="user-dropdown" id="userDropdown">
                    <a href="profile.php" class="dropdown-item">
                        <i class="fas fa-user"></i>
                        Hồ sơ
                    </a>
                    <a href="settings.php" class="dropdown-item">
                        <i class="fas fa-cog"></i>
                        Cài đặt
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i>
                        Đăng xuất
                    </a>
                </div>
            </div>
            
            <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
</header>

<!-- Mobile Bottom Navigation -->
<nav class="mobile-bottom-nav">
    <div class="nav-inner">
        <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF'])==='dashboard.php'?'active':''; ?>">
            <i class="fas fa-home"></i>
            <span>Trang chủ</span>
        </a>
        <a href="transactions.php" class="<?php echo basename($_SERVER['PHP_SELF'])==='transactions.php'?'active':''; ?>">
            <i class="fas fa-exchange-alt"></i>
            <span>Giao dịch</span>
        </a>
        <a href="add-transaction.php">
            <i class="fas fa-plus-circle"></i>
            <span>Thêm</span>
        </a>
        <a href="budgets.php" class="<?php echo basename($_SERVER['PHP_SELF'])==='budgets.php'?'active':''; ?>">
            <i class="fas fa-chart-pie"></i>
            <span>Ngân sách</span>
        </a>
        <a href="reports.php" class="<?php echo basename($_SERVER['PHP_SELF'])==='reports.php'?'active':''; ?>">
            <i class="fas fa-file-alt"></i>
            <span>Báo cáo</span>
        </a>
    </div>
</nav>

<!-- Floating Action Button -->
<a class="fab-add" href="add-transaction.php" aria-label="Thêm giao dịch nhanh">
    <i class="fas fa-plus"></i>
</a>

<script>
function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('show');
}

function toggleMobileMenu() {
    const nav = document.querySelector('.main-nav');
    nav.classList.toggle('show');
}

// Đóng dropdown khi click bên ngoài
window.onclick = function(event) {
    if (!event.target.matches('.user-toggle')) {
        const dropdowns = document.getElementsByClassName('user-dropdown');
        for (let dropdown of dropdowns) {
            if (dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            }
        }
    }
}
</script>

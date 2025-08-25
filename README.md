# 🏠 Quản Lý Chi Tiêu - Hệ Thống Quản Lý Tài Chính Cá Nhân

## 📋 Mô Tả Dự Án

Hệ thống quản lý chi tiêu cá nhân được xây dựng bằng PHP thuần, giúp người dùng theo dõi thu chi, thiết lập ngân sách và đạt được mục tiêu tài chính. Giao diện được thiết kế đẹp mắt với chủ đề màu xanh, responsive và dễ sử dụng trên mọi thiết bị.

## ✨ Tính Năng Chính

### 🏠 Trang Tổng Quan (Dashboard)
- **Thống kê tổng quan**: Thu nhập, chi tiêu, số dư, tỷ lệ tiết kiệm
- **Biểu đồ trực quan**: 
  - Biểu đồ đường xu hướng thu chi theo thời gian
  - Biểu đồ tròn phân bổ chi tiêu theo danh mục
- **Giao dịch gần đây**: Hiển thị 10 giao dịch mới nhất
- **Hành động nhanh**: Truy cập nhanh các chức năng chính

### 💰 Quản Lý Thu Nhập & Chi Tiêu
- **Thêm giao dịch mới**: 
  - Chọn loại (thu nhập/chi tiêu)
  - Nhập số tiền, mô tả, danh mục, ngày
  - Hỗ trợ ghi chú chi tiết (ví dụ: "Mẹ gửi tiền", "Ăn trưa")
- **Danh mục đa dạng**:
  - Thu nhập: Lương, thưởng, đầu tư, khác
  - Chi tiêu: Ăn uống, mua sắm, đi lại, học tập, giải trí, tiết kiệm, nợ, y tế, nhà ở
- **Tìm kiếm & lọc**: Theo ngày, danh mục, số tiền, từ khóa
- **Chỉnh sửa/Xóa**: Quản lý giao dịch đã nhập

### 📊 Báo Cáo & Phân Tích
- **Thống kê theo danh mục**: Phân bổ chi tiêu chi tiết
- **Biểu đồ xu hướng**: Theo dõi thu chi theo thời gian
- **So sánh thu chi**: Tính toán tỷ lệ tiết kiệm
- **Xuất dữ liệu**: Hỗ trợ xuất Excel/PDF (sẽ phát triển thêm)

### 🎯 Ngân Sách & Mục Tiêu Tài Chính
- **Thiết lập ngân sách**: Theo tháng hoặc danh mục cụ thể
- **Cảnh báo vượt ngân sách**: Thông báo khi gần vượt hạn mức
- **Mục tiêu tiết kiệm**: Đặt và theo dõi mục tiêu tài chính

### 🔔 Thông Báo & Nhắc Nhở
- **Nhắc ghi chi tiêu**: Hướng dẫn ghi chú chi tiết
- **Cảnh báo ngân sách**: Thông báo khi vượt mức
- **Gợi ý thói quen**: Hướng dẫn quản lý tài chính hiệu quả

### 👥 Tính Năng Nâng Cao
- **Đa người dùng**: Hệ thống tài khoản riêng biệt
- **Bảo mật cao**: Mã hóa mật khẩu, session quản lý
- **Giao diện responsive**: Tối ưu cho PC, tablet, điện thoại
- **Chế độ tối**: Hỗ trợ dark mode (sẽ phát triển thêm)

## 🎨 Giao Diện & Thiết Kế

### Màu Sắc Chủ Đạo
- **Primary**: #1e88e5 (Xanh dương)
- **Secondary**: #26a69a (Xanh lá)
- **Success**: #4caf50 (Xanh lá cây)
- **Warning**: #ff9800 (Cam)
- **Danger**: #f44336 (Đỏ)

### Responsive Design
- **Desktop**: Giao diện đầy đủ với sidebar và grid layout
- **Tablet**: Tối ưu cho màn hình trung bình
- **Mobile**: Menu hamburger, layout dọc, touch-friendly

### Icon & Typography
- **Font Awesome 6.0**: Icon đẹp mắt và nhất quán
- **Segoe UI**: Font chữ dễ đọc, hiện đại
- **Material Design**: Nguyên tắc thiết kế Google

## 🚀 Cài Đặt & Sử Dụng

### Yêu Cầu Hệ Thống
- **Web Server**: Apache/Nginx
- **PHP**: 7.4+ (khuyến nghị 8.0+)
- **Database**: MySQL 5.7+ hoặc MariaDB 10.2+
- **Browser**: Chrome, Firefox, Safari, Edge (hiện đại)

### Cài Đặt

1. **Clone dự án**:
```bash
git clone [repository-url]
cd quanlychitieu
```

2. **Tạo cơ sở dữ liệu**:
```sql
CREATE DATABASE quanlychitieu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. **Cấu hình database**:
- Chỉnh sửa file `config/database.php`
- Cập nhật thông tin kết nối database

4. **Upload lên web server**:
- Upload tất cả file lên thư mục web
- Đảm bảo quyền ghi cho thư mục `uploads/` (nếu có)

5. **Truy cập website**:
- Mở trình duyệt và truy cập domain
- Hệ thống sẽ tự động tạo bảng và dữ liệu mẫu

### Cấu Trúc Thư Mục
```
quanlychitieu/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
├── config/
│   └── database.php
├── includes/
│   ├── functions.php
│   ├── header.php
│   └── footer.php
├── index.php
├── register.php
├── dashboard.php
├── add-transaction.php
├── logout.php
└── README.md
```

## 📱 Hướng Dẫn Sử Dụng

### Đăng Ký & Đăng Nhập
1. **Đăng ký tài khoản**: Điền họ tên, email, mật khẩu
2. **Đăng nhập**: Sử dụng email và mật khẩu đã đăng ký
3. **Bảo mật**: Mật khẩu được mã hóa an toàn

### Thêm Giao Dịch
1. **Chọn loại**: Thu nhập hoặc chi tiêu
2. **Nhập số tiền**: Số tiền giao dịch
3. **Mô tả**: Ghi chú chi tiết (ví dụ: "Mẹ gửi tiền", "Ăn trưa")
4. **Danh mục**: Chọn danh mục phù hợp
5. **Ngày**: Chọn ngày giao dịch (mặc định hôm nay)

### Quản Lý Ngân Sách
1. **Thiết lập hạn mức**: Đặt giới hạn chi tiêu theo tháng/danh mục
2. **Theo dõi**: Xem tỷ lệ sử dụng ngân sách
3. **Cảnh báo**: Nhận thông báo khi gần vượt ngân sách

### Mục Tiêu Tiết Kiệm
1. **Đặt mục tiêu**: Tên, số tiền mục tiêu, thời hạn
2. **Theo dõi tiến độ**: Xem tỷ lệ hoàn thành
3. **Cập nhật**: Ghi nhận tiền tiết kiệm thêm

## 🔧 Tùy Chỉnh & Phát Triển

### Thêm Danh Mục Mới
1. Chỉnh sửa file `config/database.php`
2. Thêm danh mục vào mảng `$default_categories`
3. Refresh trang để áp dụng thay đổi

### Thay Đổi Giao Diện
1. **CSS**: Chỉnh sửa file `assets/css/style.css`
2. **JavaScript**: Cập nhật file `assets/js/main.js`
3. **Layout**: Sửa đổi file trong thư mục `includes/`

### Thêm Tính Năng Mới
1. **Backend**: Thêm hàm vào `includes/functions.php`
2. **Frontend**: Tạo file PHP mới với giao diện
3. **Database**: Thêm bảng mới nếu cần

## 📊 Cơ Sở Dữ Liệu

### Bảng Chính
- **users**: Thông tin người dùng
- **categories**: Danh mục thu chi
- **transactions**: Giao dịch thu chi
- **budgets**: Ngân sách chi tiêu
- **savings_goals**: Mục tiêu tiết kiệm

### Quan Hệ
- Mỗi user có nhiều transactions
- Mỗi transaction thuộc một category
- User có thể có nhiều budgets và goals

## 🛡️ Bảo Mật

### Mã Hóa
- **Mật khẩu**: Sử dụng `password_hash()` và `password_verify()`
- **Session**: Quản lý session an toàn
- **SQL Injection**: Sử dụng prepared statements

### Xác Thực
- **Đăng nhập bắt buộc**: Kiểm tra session cho các trang bảo vệ
- **Quyền truy cập**: Mỗi user chỉ thấy dữ liệu của mình
- **CSRF Protection**: Sẽ phát triển thêm

## 🚀 Tính Năng Sắp Tới

### Phiên Bản 2.0
- [ ] Dark mode hoàn chỉnh
- [ ] Xuất báo cáo Excel/PDF
- [ ] Thông báo push notification
- [ ] Đồng bộ đa thiết bị
- [ ] API RESTful

### Phiên Bản 3.0
- [ ] Ứng dụng mobile (React Native)
- [ ] Tích hợp ngân hàng
- [ ] AI phân tích chi tiêu
- [ ] Chia sẻ nhóm gia đình

## 🤝 Đóng Góp

Chúng tôi hoan nghênh mọi đóng góp từ cộng đồng:

1. **Fork** dự án
2. **Tạo branch** mới (`git checkout -b feature/AmazingFeature`)
3. **Commit** thay đổi (`git commit -m 'Add some AmazingFeature'`)
4. **Push** lên branch (`git push origin feature/AmazingFeature`)
5. **Tạo Pull Request**

## 📄 Giấy Phép

Dự án này được phát hành dưới giấy phép MIT. Xem file `LICENSE` để biết thêm chi tiết.

## 📞 Liên Hệ & Hỗ Trợ

- **Email**:
- **Website**: 
- **GitHub**: https://github.com/username/quanlychitieu

## 🙏 Lời Cảm Ơn

Cảm ơn tất cả những người đã đóng góp và sử dụng hệ thống quản lý chi tiêu này. Chúng tôi hy vọng nó sẽ giúp bạn quản lý tài chính hiệu quả hơn!

---

**⭐ Nếu dự án này hữu ích, hãy cho chúng tôi một ngôi sao trên GitHub!**

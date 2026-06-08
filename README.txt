WEBBANHANG - BẢN MỞ RỘNG TÀI KHOẢN / PHÂN QUYỀN

Giữ nguyên cấu trúc project gốc, đã phát triển thêm:
- Đăng ký / đăng nhập nâng cấp.
- Remember Me bằng cookie + token hash trong database.
- Quên mật khẩu và đặt lại mật khẩu bằng link token demo.
- Xác thực email bằng link token demo.
- Trang hồ sơ cá nhân, cập nhật họ tên, email, SĐT, địa chỉ.
- Upload / thay đổi ảnh đại diện.
- Đổi mật khẩu.
- Phân quyền Admin / User.
- Chỉ Admin được vào khu vực quản trị danh mục, quản lý người dùng và CRUD sản phẩm.
- Trang quản lý người dùng cho Admin.
- Admin khóa / mở khóa tài khoản, cấp / hạ quyền người dùng.

Tài khoản admin mặc định khi database chưa có account:
username: admin
password: 123456

Lưu ý local:
- Chức năng xác thực email và quên mật khẩu đang dùng link demo hiển thị trực tiếp trên giao diện vì project chưa cấu hình SMTP.
- Khi cấu hình mail thật, có thể thay phần hiển thị link bằng hàm mail()/PHPMailer.
- BASE_URL đang để rỗng theo project gốc. Nếu đặt trong thư mục con, chỉnh BASE_URL trong index.php.

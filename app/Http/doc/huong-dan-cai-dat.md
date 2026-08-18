# HƯỚNG DẪN CÀI ĐẶT MÔI TRƯỜNG VÀ CHẠY DỰ ÁN
# XÂY DỰNG WEBSITE BÁN NƯỚC HOA

---

## 1. Yêu cầu

- Máy tính Windows 10/11, có kết nối Internet.
- IDE: VS Code hoặc PhpStorm.
- Đã có sẵn source code dự án Laravel.

---

## 2. Cài đặt WampServer (PHP 8.2 & MySQL)

1. Tải và cài đặt WampServer (chọn phiên bản hỗ trợ PHP 8.2 trở lên) từ trang chủ [wampserver.com](https://www.wampserver.com).
2. Khởi chạy WampServer, chờ biểu tượng Wamp ở thanh Taskbar chuyển sang **màu xanh lá**.

---

## 3. Cài đặt Composer

1. Tải Composer từ https://getcomposer.org/Composer-Setup.exe và tiến hành cài đặt.
2. Trong quá trình cài đặt, chọn đường dẫn tới file PHP của WAMP (thường là `C:\wamp64\bin\php\php8.x.x\php.exe`).
3. Kiểm tra cài đặt thành công bằng Terminal:

```cmd
composer -v
```

---

## 4. Cài đặt Node.js & npm

1. Tải Node.js LTS (phiên bản 18 hoặc 20) từ https://nodejs.org/ và cài đặt.
2. Kiểm tra phiên bản:

```cmd
node -v
npm -v
```

---

## 5. Mở dự án trong VS Code

Mở thư mục chứa source code Laravel bằng Visual Studio Code (hoặc mở Command Prompt tại thư mục dự án).

---

## 6. Cài đặt các gói phụ thuộc (Dependencies)

1. Cài đặt thư viện PHP:

```cmd
composer install
```

2. Cài đặt thư viện JavaScript:

```cmd
npm install
```

---

## 7. Cấu hình file môi trường (.env)

1. Tạo file `.env` từ file mẫu:

```cmd
copy .env.example .env
```

2. Sinh mã khóa bảo mật cho ứng dụng:

```cmd
php artisan key:generate
```

---

## 8. Tạo Cơ sở dữ liệu (Database)

1. Bấm chuột trái vào biểu tượng WAMP ở góc màn hình → chọn **phpMyAdmin** (hoặc truy cập http://localhost/phpmyadmin trên trình duyệt).
2. Đăng nhập (Mặc định: Username: `root`, Mật khẩu để trống) và tạo một Database mới tên là `laravel_db` (bảng mã `utf8mb4_unicode_ci`).
3. Mở file `.env` trong dự án và cập nhật cấu hình:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=root
DB_PASSWORD=
```

---

## 9. Chạy Migration

Tạo toàn bộ cấu trúc bảng trong database:

```cmd
php artisan migrate
```

> **Lưu ý:** Không cần thêm `--seed`. Seeder mặc định chỉ tạo 1 user test thông thường, không có dữ liệu sản phẩm hay admin. Tài khoản admin được tạo ở bước tiếp theo.

---

## 10. Tạo tài khoản Admin

Khởi chạy Tinker để tạo tài khoản đăng nhập trang quản trị:

```cmd
php artisan tinker
```

Dán đoạn mã sau rồi nhấn Enter:

```php
App\Models\User::create([
    'name'              => 'Admin',
    'email'             => 'admin@example.com',
    'password'          => bcrypt('password123'),
    'phone'             => '0900000000',
    'address'           => 'TP. Hồ Chí Minh',
    'role'              => 'admin',
    'email_verified_at' => now(),
    'is_active'         => true,
]);
```

Gõ `exit` để thoát Tinker.

### Các role trong hệ thống

| Role | Mô tả |
|---|---|
| `root` | Toàn quyền hệ thống |
| `admin` | Quản trị toàn bộ (trừ xem doanh thu) |
| `director` | Chỉ xem báo cáo / doanh thu |
| `warehouse` | Quản lý kho hàng |
| `manufacturer` | Nhà sản xuất |
| `customer` | Khách hàng mua hàng |

---

## 11. Build Frontend

Biên dịch CSS/JS cho giao diện:

```cmd
npm run build
```

---

## 12. Chạy ứng dụng

Khởi động máy chủ Laravel:

```cmd
php artisan serve
```

Mở trình duyệt và truy cập:
- **Trang chủ:** http://127.0.0.1:8000
- **Trang admin:** http://127.0.0.1:8000/admin

---

## 13. Lỗi thường gặp

| Lỗi | Cách khắc phục |
|---|---|
| WampServer có biểu tượng màu Vàng/Đỏ | Kiểm tra cổng 80 hoặc 3306 có bị chiếm dụng không, hoặc bấm chuột trái vào WAMP → Restart All Services. |
| `php` hoặc `composer` command not found | Thêm đường dẫn PHP trong thư mục WAMP (`C:\wamp64\bin\php\php8.x.x`) vào biến môi trường PATH. |
| SQLSTATE[HY000] [1049] Unknown database | Chưa tạo Database `laravel_db` trong phpMyAdmin hoặc gõ sai tên DB trong file `.env`. |
| Access denied for user 'root'@'localhost' | Sai `DB_USERNAME` hoặc `DB_PASSWORD` trong file `.env` so với cấu hình MySQL của WAMP. |
| Class "..." not found | Chạy lệnh `composer dump-autoload` trong Terminal. |
| Trang bị vỡ giao diện / không nhận CSS | Chạy lệnh `npm run build` để biên dịch lại file giao diện. |
| Lỗi Cache / Không nhận file `.env` mới | Chạy lệnh `php artisan optimize:clear`. |
| Trang trắng sau khi đăng nhập | Email chưa được xác minh. Dùng Tinker: `App\Models\User::where('email','admin@example.com')->update(['email_verified_at'=>now()]);` |

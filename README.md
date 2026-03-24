# TGS Login Customizer

Plugin tùy chỉnh giao diện trang đăng nhập WordPress cho hệ thống TGS Shop Management.

## Tính năng

### 🎨 Giao diện trang đăng nhập
- **Logo**: Tải lên logo tùy chỉnh, thiết lập kích thước, bo góc, link URL
- **Background**: Màu đơn sắc, hình ảnh, gradient - tùy chọn lớp phủ mờ
- **Form đăng nhập**: Tùy chỉnh màu nền, viền, bo góc, bóng đổ, padding, chiều rộng
- **Input**: Màu nền, chữ, viền, focus, placeholder, bo góc
- **Button**: Màu nền, chữ, viền, hover, bo góc, kiểu chữ, chiều rộng
- **Labels**: Màu chữ, cỡ chữ, font weight
- **Links**: Màu chữ, hover, ẩn/hiện từng link
- **Error**: Tùy chỉnh màu sắc thông báo lỗi

### 🔐 Tự động đăng nhập
- Cài đặt tài khoản/mật khẩu mặc định (demo)
- Tự động điền sẵn vào form
- Tùy chọn tự động submit với đếm ngược
- Hiển thị thông tin credentials cho user
- Nút hủy tự động đăng nhập

### 🔄 Chuyển hướng
- Chuyển hướng sau đăng nhập về trang đang truy cập (redirect_to)
- Hoặc chuyển hướng đến URL tùy chỉnh (ví dụ: trang quản lý kho TGS)
- URL mặc định khi không có redirect_to

### 📐 Bố cục
- Vị trí form: trái / giữa / phải
- Vị trí dọc: trên / giữa / dưới
- Font family (Google Fonts): Inter, Roboto, Poppins, v.v.

### 💻 Tùy chỉnh nâng cao
- Custom CSS
- Custom JavaScript
- Tiêu đề / mô tả tùy chỉnh trên form
- Footer text (copyright)

### 💾 Import / Export
- Export cài đặt ra file JSON
- Import cài đặt từ file JSON hoặc clipboard
- Khôi phục cài đặt mặc định

## Lưu trữ dữ liệu

- **Multisite**: Lưu vào bảng `sitemeta` dưới dạng JSON (`tgs_login_customizer_settings`)
- **Single site**: Lưu vào bảng `options`
- **Không tạo bảng database mới**

## Cài đặt

1. Copy thư mục `tgs_login_customizer` vào `/wp-content/plugins/`
2. Kích hoạt plugin trong WordPress Admin
3. Vào **Settings > Login Customizer** để cài đặt
4. Hoặc Network Admin > Settings > Login Customizer (multisite)

## Cấu trúc Plugin

```
tgs_login_customizer/
├── tgs-login-customizer.php      # File chính plugin
├── index.php                      # Security
├── README.md                      # Tài liệu
├── includes/
│   ├── class-settings.php         # Quản lý cài đặt (JSON/sitemeta)
│   ├── class-login-customizer.php # Can thiệp trang đăng nhập
│   └── class-admin.php            # Trang quản trị cài đặt
├── templates/
│   └── admin-settings.php         # Template giao diện admin
└── assets/
    ├── css/
    │   └── admin.css              # CSS trang admin
    └── js/
        └── admin.js               # JS trang admin
```

## Sử dụng cho Developer

### Lấy settings
```php
$settings = TGS_Login_Settings::get_instance();
$all = $settings->get_all();
$logo_url = $settings->get('logo.image_url');
$bg_type = $settings->get('background.type', 'color');
```

### Lưu settings
```php
$settings->update('logo.image_url', 'https://example.com/logo.png');
$settings->save_all($new_settings_array);
```

### Export/Import
```php
$json = $settings->export();    // Export ra JSON string
$settings->import($json);       // Import từ JSON string
$settings->reset();              // Reset về mặc định
```

## Yêu cầu
- WordPress 5.0+
- PHP 7.4+
- Multisite supported

## Tác giả
BIZGPT_AI - https://bizgpt.vn/

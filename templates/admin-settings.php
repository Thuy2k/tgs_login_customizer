<?php
/**
 * Admin Settings Template
 * 
 * Giao diện trang cài đặt tùy chỉnh trang đăng nhập
 * 
 * @package TGS_Login_Customizer
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = TGS_Login_Settings::get_instance();
$s = $settings->get_all();

// Helper function to get nested value
function tgs_login_val($s, $path, $default = '') {
    $keys = explode('.', $path);
    $val = $s;
    foreach ($keys as $k) {
        if (is_array($val) && isset($val[$k])) {
            $val = $val[$k];
        } else {
            return $default;
        }
    }
    return $val;
}

// Helper for checked attribute
function tgs_login_checked($s, $path) {
    return tgs_login_val($s, $path, false) ? 'checked' : '';
}

// Helper for selected attribute  
function tgs_login_selected($s, $path, $value) {
    return tgs_login_val($s, $path, '') === $value ? 'selected' : '';
}

// Decode password for display
$auto_password = $settings->get_auto_login_password();
?>
<div class="wrap tgs-login-admin-wrap">

    <!-- HEADER -->
    <div class="tgs-login-admin-header">
        <h1>🎨 TGS Login Customizer</h1>
        <div class="header-actions">
            <button class="button tgs-btn-preview">
                <span class="dashicons dashicons-visibility" style="vertical-align:text-bottom;"></span> Xem trước
            </button>
            <a href="<?php echo esc_url(wp_login_url()); ?>" target="_blank" class="button">
                <span class="dashicons dashicons-external" style="vertical-align:text-bottom;"></span> Mở trang Login
            </a>
            <button class="button button-primary tgs-btn-save">💾 Lưu cài đặt</button>
        </div>
    </div>

    <!-- TABS -->
    <div class="tgs-login-tabs">
        <div class="tgs-login-tab active" data-tab="general">
            <span class="dashicons dashicons-admin-generic"></span> Chung
        </div>
        <div class="tgs-login-tab" data-tab="auto-login">
            <span class="dashicons dashicons-unlock"></span> Tự động đăng nhập
        </div>
        <div class="tgs-login-tab" data-tab="logo">
            <span class="dashicons dashicons-format-image"></span> Logo
        </div>
        <div class="tgs-login-tab" data-tab="background">
            <span class="dashicons dashicons-art"></span> Nền trang
        </div>
        <div class="tgs-login-tab" data-tab="form">
            <span class="dashicons dashicons-editor-table"></span> Form đăng nhập
        </div>
        <div class="tgs-login-tab" data-tab="colors">
            <span class="dashicons dashicons-admin-appearance"></span> Màu sắc & Chữ
        </div>
        <div class="tgs-login-tab" data-tab="text">
            <span class="dashicons dashicons-editor-textcolor"></span> Nội dung văn bản
        </div>
        <div class="tgs-login-tab" data-tab="layout">
            <span class="dashicons dashicons-layout"></span> Bố cục
        </div>
        <div class="tgs-login-tab" data-tab="custom-code">
            <span class="dashicons dashicons-editor-code"></span> CSS/JS tùy chỉnh
        </div>
        <div class="tgs-login-tab" data-tab="import-export">
            <span class="dashicons dashicons-database"></span> Import / Export
        </div>
    </div>

    <!-- ============================== -->
    <!-- TAB: GENERAL -->
    <!-- ============================== -->
    <div id="tab-general" class="tgs-login-tab-content active">
        <div class="tgs-section">
            <h3 class="tgs-section-title">⚙️ Cài đặt chung</h3>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Bật tùy chỉnh trang đăng nhập</div>
                <div class="tgs-field-control">
                    <label class="tgs-toggle">
                        <input type="checkbox" data-key="general.enabled" <?php echo tgs_login_checked($s, 'general.enabled'); ?>>
                        <span class="tgs-toggle-slider"></span>
                    </label>
                    <div class="tgs-field-desc">Bật/tắt toàn bộ tùy chỉnh trang đăng nhập</div>
                </div>
            </div>
        </div>

        <div class="tgs-section">
            <h3 class="tgs-section-title">🔄 Chuyển hướng sau đăng nhập</h3>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Ưu tiên chuyển hướng</div>
                <div class="tgs-field-control">
                    <select class="tgs-setting-field" data-key="general.redirect_priority">
                        <option value="requested_url" <?php echo tgs_login_selected($s, 'general.redirect_priority', 'requested_url'); ?>>Trang đang truy cập (redirect_to)</option>
                        <option value="custom_url" <?php echo tgs_login_selected($s, 'general.redirect_priority', 'custom_url'); ?>>URL tùy chỉnh bên dưới</option>
                    </select>
                    <div class="tgs-field-desc">
                        <strong>Trang đang truy cập:</strong> Nếu user bị đẩy ra đăng nhập từ trang nào thì sau khi login sẽ quay lại trang đó.<br>
                        <strong>URL tùy chỉnh:</strong> Luôn chuyển đến URL cài đặt bên dưới.
                    </div>
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">URL chuyển hướng mặc định</div>
                <div class="tgs-field-control">
                    <input type="url" class="tgs-setting-field regular-text" 
                           data-key="general.default_redirect_url"
                           value="<?php echo esc_attr(tgs_login_val($s, 'general.default_redirect_url')); ?>"
                           placeholder="Ví dụ: /wp-admin/admin.php?page=tgs-shop-management">
                    <div class="tgs-field-desc">URL mặc định khi không có redirect_to. Để trống = vào wp-admin mặc định.</div>
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">URL chuyển hướng cố định</div>
                <div class="tgs-field-control">
                    <input type="url" class="tgs-setting-field regular-text" 
                           data-key="general.redirect_after_login"
                           value="<?php echo esc_attr(tgs_login_val($s, 'general.redirect_after_login')); ?>"
                           placeholder="Ví dụ: /wp-admin/admin.php?page=tgs-shop-management&view=dashboard">
                    <div class="tgs-field-desc">Nếu chọn ưu tiên "URL tùy chỉnh", user sẽ luôn được chuyển đến URL này sau đăng nhập.</div>
                </div>
            </div>
        </div>

        <div class="tgs-info-box">
            💡 <strong>Mẹo:</strong> Cài đặt lưu vào bảng <code>sitemeta</code> dưới dạng JSON, dùng chung cho cả hệ thống multisite. Không tạo thêm bảng database.
        </div>
    </div>

    <!-- ============================== -->
    <!-- TAB: AUTO LOGIN -->
    <!-- ============================== -->
    <div id="tab-auto-login" class="tgs-login-tab-content">
        <div class="tgs-section">
            <h3 class="tgs-section-title">🔐 Tài khoản mặc định & Tự động đăng nhập</h3>

            <div class="tgs-info-box warning">
                ⚠️ <strong>Lưu ý bảo mật:</strong> Chỉ sử dụng tính năng này cho môi trường demo hoặc phát triển. Không nên bật trên production.
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Bật tự động đăng nhập</div>
                <div class="tgs-field-control">
                    <label class="tgs-toggle">
                        <input type="checkbox" data-key="auto_login.enabled" <?php echo tgs_login_checked($s, 'auto_login.enabled'); ?>>
                        <span class="tgs-toggle-slider"></span>
                    </label>
                    <div class="tgs-field-desc">Khi bật, sẽ tự động điền tài khoản/mật khẩu vào form đăng nhập</div>
                </div>
            </div>

            <div class="tgs-auto-login-fields tgs-conditional <?php echo empty($s['auto_login']['enabled']) ? 'hidden' : ''; ?>">
                
                <div class="tgs-field-row">
                    <div class="tgs-field-label">Tên đăng nhập mặc định</div>
                    <div class="tgs-field-control">
                        <input type="text" class="tgs-setting-field regular-text" 
                               data-key="auto_login.username"
                               value="<?php echo esc_attr(tgs_login_val($s, 'auto_login.username')); ?>"
                               placeholder="demo">
                    </div>
                </div>

                <div class="tgs-field-row">
                    <div class="tgs-field-label">Mật khẩu mặc định</div>
                    <div class="tgs-field-control">
                        <input type="text" class="tgs-setting-field regular-text" 
                               data-key="auto_login.password"
                               value="<?php echo esc_attr($auto_password); ?>"
                               placeholder="123456">
                        <div class="tgs-field-desc">Mật khẩu sẽ được mã hóa base64 khi lưu</div>
                    </div>
                </div>

                <div class="tgs-field-row">
                    <div class="tgs-field-label">Hiển thị thông tin đăng nhập</div>
                    <div class="tgs-field-control">
                        <label class="tgs-toggle">
                            <input type="checkbox" data-key="auto_login.show_credentials" <?php echo tgs_login_checked($s, 'auto_login.show_credentials'); ?>>
                            <span class="tgs-toggle-slider"></span>
                        </label>
                        <div class="tgs-field-desc">Hiển thị ô thông tin "Tài khoản demo: user / pass" trên form đăng nhập</div>
                    </div>
                </div>

                <div class="tgs-field-row">
                    <div class="tgs-field-label">Chỉ điền sẵn (không tự động submit)</div>
                    <div class="tgs-field-control">
                        <label class="tgs-toggle">
                            <input type="checkbox" data-key="auto_login.prefill_only" <?php echo tgs_login_checked($s, 'auto_login.prefill_only'); ?>>
                            <span class="tgs-toggle-slider"></span>
                        </label>
                        <div class="tgs-field-desc">BẬT = Chỉ điền sẵn user/pass, user tự bấm đăng nhập. TẮT = Tự động submit form.</div>
                    </div>
                </div>

                <div class="tgs-auto-submit-fields tgs-conditional <?php echo !empty($s['auto_login']['prefill_only']) ? 'hidden' : ''; ?>">
                    <div class="tgs-field-row">
                        <div class="tgs-field-label">Thời gian chờ trước khi submit</div>
                        <div class="tgs-field-control">
                            <div class="tgs-field-inline">
                                <input type="number" class="tgs-setting-field" 
                                       data-key="auto_login.auto_submit_delay"
                                       value="<?php echo esc_attr(tgs_login_val($s, 'auto_login.auto_submit_delay', 3)); ?>"
                                       min="0" max="30" step="1">
                                <span class="unit">giây</span>
                            </div>
                            <div class="tgs-field-desc">0 = submit ngay lập tức, hoặc đặt từ 1-30 giây</div>
                        </div>
                    </div>

                    <div class="tgs-field-row">
                        <div class="tgs-field-label">Hiển thị đếm ngược</div>
                        <div class="tgs-field-control">
                            <label class="tgs-toggle">
                                <input type="checkbox" data-key="auto_login.show_countdown" <?php echo tgs_login_checked($s, 'auto_login.show_countdown'); ?>>
                                <span class="tgs-toggle-slider"></span>
                            </label>
                            <div class="tgs-field-desc">Hiển thị thanh đếm ngược "Tự động đăng nhập sau X giây" + nút Hủy</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================== -->
    <!-- TAB: LOGO -->
    <!-- ============================== -->
    <div id="tab-logo" class="tgs-login-tab-content">
        <div class="tgs-section">
            <h3 class="tgs-section-title">🖼️ Logo trang đăng nhập</h3>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Hình ảnh Logo</div>
                <div class="tgs-field-control">
                    <div class="tgs-image-upload">
                        <div class="tgs-image-preview" id="logo-preview">
                            <?php
                            $logo_url = tgs_login_val($s, 'logo.image_url');
                            if (!empty($logo_url)) {
                                echo '<img src="' . esc_url($logo_url) . '" alt="Logo" />';
                            } else {
                                echo '<span class="no-image">Chưa có hình ảnh<br>Nhấn để tải lên</span>';
                            }
                            ?>
                        </div>
                        <div class="tgs-image-actions">
                            <button class="button tgs-upload-btn" data-target="logo_image_url" data-key="logo.image_url">📤 Tải lên</button>
                            <button class="button tgs-remove-image-btn" data-target="logo_image_url" data-key="logo.image_url">🗑️ Xóa</button>
                        </div>
                    </div>
                    <input type="hidden" id="logo_image_url" class="tgs-setting-field" data-key="logo.image_url" value="<?php echo esc_attr($logo_url); ?>">
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Chiều rộng Logo</div>
                <div class="tgs-field-control">
                    <div class="tgs-field-inline">
                        <input type="number" class="tgs-setting-field" data-key="logo.width" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'logo.width', '320')); ?>" min="50" max="800">
                        <span class="unit">px</span>
                    </div>
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Chiều cao Logo</div>
                <div class="tgs-field-control">
                    <div class="tgs-field-inline">
                        <input type="number" class="tgs-setting-field" data-key="logo.height" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'logo.height', '120')); ?>" min="20" max="500">
                        <span class="unit">px</span>
                    </div>
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Bo góc Logo</div>
                <div class="tgs-field-control">
                    <div class="tgs-field-inline">
                        <input type="number" class="tgs-setting-field" data-key="logo.border_radius" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'logo.border_radius', '0')); ?>" min="0" max="200">
                        <span class="unit">px</span>
                    </div>
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Khoảng cách dưới Logo</div>
                <div class="tgs-field-control">
                    <div class="tgs-field-inline">
                        <input type="number" class="tgs-setting-field" data-key="logo.margin_bottom" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'logo.margin_bottom', '25')); ?>" min="0" max="100">
                        <span class="unit">px</span>
                    </div>
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">URL khi click Logo</div>
                <div class="tgs-field-control">
                    <input type="url" class="tgs-setting-field regular-text" data-key="logo.link_url" 
                           value="<?php echo esc_attr(tgs_login_val($s, 'logo.link_url')); ?>"
                           placeholder="<?php echo esc_attr(home_url('/')); ?>">
                    <div class="tgs-field-desc">Để trống = về trang chủ</div>
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Title khi hover Logo</div>
                <div class="tgs-field-control">
                    <input type="text" class="tgs-setting-field regular-text" data-key="logo.link_title" 
                           value="<?php echo esc_attr(tgs_login_val($s, 'logo.link_title')); ?>"
                           placeholder="<?php echo esc_attr(get_bloginfo('name')); ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- ============================== -->
    <!-- TAB: BACKGROUND -->
    <!-- ============================== -->
    <div id="tab-background" class="tgs-login-tab-content">
        <div class="tgs-section">
            <h3 class="tgs-section-title">🎨 Nền trang đăng nhập</h3>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Loại nền</div>
                <div class="tgs-field-control">
                    <select class="tgs-setting-field" data-key="background.type">
                        <option value="color" <?php echo tgs_login_selected($s, 'background.type', 'color'); ?>>Màu đơn sắc</option>
                        <option value="image" <?php echo tgs_login_selected($s, 'background.type', 'image'); ?>>Hình ảnh</option>
                        <option value="gradient" <?php echo tgs_login_selected($s, 'background.type', 'gradient'); ?>>Gradient</option>
                    </select>
                </div>
            </div>

            <!-- COLOR BG -->
            <div class="tgs-bg-color-fields tgs-conditional <?php echo tgs_login_val($s, 'background.type', 'color') !== 'color' ? 'hidden' : ''; ?>">
                <div class="tgs-field-row">
                    <div class="tgs-field-label">Màu nền</div>
                    <div class="tgs-field-control">
                        <input type="text" class="tgs-color-picker" data-key="background.color" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'background.color', '#f0f0f1')); ?>">
                    </div>
                </div>
            </div>

            <!-- IMAGE BG -->
            <div class="tgs-bg-image-fields tgs-conditional <?php echo tgs_login_val($s, 'background.type', 'color') !== 'image' ? 'hidden' : ''; ?>">
                <div class="tgs-field-row">
                    <div class="tgs-field-label">Ảnh nền</div>
                    <div class="tgs-field-control">
                        <div class="tgs-image-upload">
                            <div class="tgs-image-preview" id="bg-image-preview">
                                <?php
                                $bg_url = tgs_login_val($s, 'background.image_url');
                                if (!empty($bg_url)) {
                                    echo '<img src="' . esc_url($bg_url) . '" alt="Background" />';
                                } else {
                                    echo '<span class="no-image">Chưa có ảnh nền</span>';
                                }
                                ?>
                            </div>
                            <div class="tgs-image-actions">
                                <button class="button tgs-upload-btn" data-target="bg_image_url" data-key="background.image_url">📤 Tải lên</button>
                                <button class="button tgs-remove-image-btn" data-target="bg_image_url" data-key="background.image_url">🗑️ Xóa</button>
                            </div>
                        </div>
                        <input type="hidden" id="bg_image_url" class="tgs-setting-field" data-key="background.image_url" value="<?php echo esc_attr($bg_url); ?>">
                    </div>
                </div>

                <div class="tgs-field-row">
                    <div class="tgs-field-label">Kích thước ảnh</div>
                    <div class="tgs-field-control">
                        <select class="tgs-setting-field" data-key="background.image_size">
                            <option value="cover" <?php echo tgs_login_selected($s, 'background.image_size', 'cover'); ?>>Cover (phủ toàn trang)</option>
                            <option value="contain" <?php echo tgs_login_selected($s, 'background.image_size', 'contain'); ?>>Contain (hiện trọn ảnh)</option>
                            <option value="auto" <?php echo tgs_login_selected($s, 'background.image_size', 'auto'); ?>>Auto (kích thước gốc)</option>
                        </select>
                    </div>
                </div>

                <div class="tgs-field-row">
                    <div class="tgs-field-label">Vị trí ảnh</div>
                    <div class="tgs-field-control">
                        <select class="tgs-setting-field" data-key="background.image_position">
                            <option value="center center" <?php echo tgs_login_selected($s, 'background.image_position', 'center center'); ?>>Giữa</option>
                            <option value="top center" <?php echo tgs_login_selected($s, 'background.image_position', 'top center'); ?>>Trên giữa</option>
                            <option value="bottom center" <?php echo tgs_login_selected($s, 'background.image_position', 'bottom center'); ?>>Dưới giữa</option>
                            <option value="left center" <?php echo tgs_login_selected($s, 'background.image_position', 'left center'); ?>>Trái giữa</option>
                            <option value="right center" <?php echo tgs_login_selected($s, 'background.image_position', 'right center'); ?>>Phải giữa</option>
                        </select>
                    </div>
                </div>

                <div class="tgs-field-row">
                    <div class="tgs-field-label">Lặp ảnh</div>
                    <div class="tgs-field-control">
                        <select class="tgs-setting-field" data-key="background.image_repeat">
                            <option value="no-repeat" <?php echo tgs_login_selected($s, 'background.image_repeat', 'no-repeat'); ?>>Không lặp</option>
                            <option value="repeat" <?php echo tgs_login_selected($s, 'background.image_repeat', 'repeat'); ?>>Lặp</option>
                        </select>
                    </div>
                </div>

                <div class="tgs-field-row">
                    <div class="tgs-field-label">Cố định ảnh khi cuộn</div>
                    <div class="tgs-field-control">
                        <select class="tgs-setting-field" data-key="background.image_attachment">
                            <option value="fixed" <?php echo tgs_login_selected($s, 'background.image_attachment', 'fixed'); ?>>Fixed (cố định)</option>
                            <option value="scroll" <?php echo tgs_login_selected($s, 'background.image_attachment', 'scroll'); ?>>Scroll (cuộn theo)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- OVERLAY (only for image bg) -->
            <div class="tgs-bg-overlay-fields tgs-conditional <?php echo tgs_login_val($s, 'background.type', 'color') !== 'image' ? 'hidden' : ''; ?>">
                <div class="tgs-field-row">
                    <div class="tgs-field-label">Bật lớp phủ mờ</div>
                    <div class="tgs-field-control">
                        <label class="tgs-toggle">
                            <input type="checkbox" data-key="background.overlay_enabled" <?php echo tgs_login_checked($s, 'background.overlay_enabled'); ?>>
                            <span class="tgs-toggle-slider"></span>
                        </label>
                        <div class="tgs-field-desc">Thêm lớp phủ màu mờ lên trên ảnh nền để chữ dễ đọc hơn</div>
                    </div>
                </div>

                <div class="tgs-field-row">
                    <div class="tgs-field-label">Màu lớp phủ</div>
                    <div class="tgs-field-control">
                        <input type="text" class="tgs-setting-field regular-text" data-key="background.overlay_color" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'background.overlay_color', 'rgba(0,0,0,0.3)')); ?>"
                               placeholder="rgba(0,0,0,0.3)">
                        <div class="tgs-field-desc">Dùng định dạng rgba, ví dụ: rgba(0,0,0,0.5) cho đen 50% trong suốt</div>
                    </div>
                </div>
            </div>

            <!-- GRADIENT BG -->
            <div class="tgs-bg-gradient-fields tgs-conditional <?php echo tgs_login_val($s, 'background.type', 'color') !== 'gradient' ? 'hidden' : ''; ?>">
                <div class="tgs-field-row">
                    <div class="tgs-field-label">Màu bắt đầu</div>
                    <div class="tgs-field-control">
                        <input type="text" class="tgs-color-picker" data-key="background.gradient_start" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'background.gradient_start', '#667eea')); ?>">
                    </div>
                </div>

                <div class="tgs-field-row">
                    <div class="tgs-field-label">Màu kết thúc</div>
                    <div class="tgs-field-control">
                        <input type="text" class="tgs-color-picker" data-key="background.gradient_end" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'background.gradient_end', '#764ba2')); ?>">
                    </div>
                </div>

                <div class="tgs-field-row">
                    <div class="tgs-field-label">Hướng gradient</div>
                    <div class="tgs-field-control">
                        <select class="tgs-setting-field" data-key="background.gradient_direction">
                            <option value="135deg" <?php echo tgs_login_selected($s, 'background.gradient_direction', '135deg'); ?>>Chéo ↘ (135°)</option>
                            <option value="to right" <?php echo tgs_login_selected($s, 'background.gradient_direction', 'to right'); ?>>Ngang →</option>
                            <option value="to bottom" <?php echo tgs_login_selected($s, 'background.gradient_direction', 'to bottom'); ?>>Dọc ↓</option>
                            <option value="45deg" <?php echo tgs_login_selected($s, 'background.gradient_direction', '45deg'); ?>>Chéo ↗ (45°)</option>
                            <option value="to bottom right" <?php echo tgs_login_selected($s, 'background.gradient_direction', 'to bottom right'); ?>>Góc ↘</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================== -->
    <!-- TAB: FORM -->
    <!-- ============================== -->
    <div id="tab-form" class="tgs-login-tab-content">
        <div class="tgs-section">
            <h3 class="tgs-section-title">📋 Khung form đăng nhập</h3>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Màu nền form</div>
                <div class="tgs-field-control">
                    <input type="text" class="tgs-color-picker" data-key="form.background_color" 
                           value="<?php echo esc_attr(tgs_login_val($s, 'form.background_color', '#ffffff')); ?>">
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Bo góc form</div>
                <div class="tgs-field-control">
                    <div class="tgs-field-inline">
                        <input type="number" class="tgs-setting-field" data-key="form.border_radius" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'form.border_radius', '8')); ?>" min="0" max="50">
                        <span class="unit">px</span>
                    </div>
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Màu viền form</div>
                <div class="tgs-field-control">
                    <input type="text" class="tgs-color-picker" data-key="form.border_color" 
                           value="<?php echo esc_attr(tgs_login_val($s, 'form.border_color', '#dddddd')); ?>">
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Độ dày viền</div>
                <div class="tgs-field-control">
                    <div class="tgs-field-inline">
                        <input type="number" class="tgs-setting-field" data-key="form.border_width" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'form.border_width', '1')); ?>" min="0" max="10">
                        <span class="unit">px</span>
                    </div>
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Bóng đổ form</div>
                <div class="tgs-field-control">
                    <input type="text" class="tgs-setting-field regular-text" data-key="form.box_shadow" 
                           value="<?php echo esc_attr(tgs_login_val($s, 'form.box_shadow', '0 4px 20px rgba(0,0,0,0.1)')); ?>"
                           placeholder="0 4px 20px rgba(0,0,0,0.1)">
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Padding bên trong</div>
                <div class="tgs-field-control">
                    <div class="tgs-field-inline">
                        <input type="number" class="tgs-setting-field" data-key="form.padding" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'form.padding', '26')); ?>" min="0" max="80">
                        <span class="unit">px</span>
                    </div>
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Chiều rộng form</div>
                <div class="tgs-field-control">
                    <div class="tgs-field-inline">
                        <input type="number" class="tgs-setting-field" data-key="form.width" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'form.width', '350')); ?>" min="250" max="800">
                        <span class="unit">px</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="tgs-section">
            <h3 class="tgs-section-title">🔘 Ghi nhớ đăng nhập</h3>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Tự động check "Ghi nhớ"</div>
                <div class="tgs-field-control">
                    <label class="tgs-toggle">
                        <input type="checkbox" data-key="remember_me.checked_by_default" <?php echo tgs_login_checked($s, 'remember_me.checked_by_default'); ?>>
                        <span class="tgs-toggle-slider"></span>
                    </label>
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Ẩn checkbox "Ghi nhớ"</div>
                <div class="tgs-field-control">
                    <label class="tgs-toggle">
                        <input type="checkbox" data-key="remember_me.hide" <?php echo tgs_login_checked($s, 'remember_me.hide'); ?>>
                        <span class="tgs-toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================== -->
    <!-- TAB: COLORS -->
    <!-- ============================== -->
    <div id="tab-colors" class="tgs-login-tab-content">
        
        <!-- INPUT FIELDS -->
        <div class="tgs-section">
            <h3 class="tgs-section-title">📝 Ô nhập liệu (Input)</h3>
            <div class="tgs-color-grid">
                <div class="tgs-field-row">
                    <div class="tgs-field-label">Màu nền input</div>
                    <div class="tgs-field-control">
                        <input type="text" class="tgs-color-picker" data-key="input.background_color" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'input.background_color', '#ffffff')); ?>">
                    </div>
                </div>
                <div class="tgs-field-row">
                    <div class="tgs-field-label">Màu chữ input</div>
                    <div class="tgs-field-control">
                        <input type="text" class="tgs-color-picker" data-key="input.text_color" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'input.text_color', '#333333')); ?>">
                    </div>
                </div>
                <div class="tgs-field-row">
                    <div class="tgs-field-label">Màu viền input</div>
                    <div class="tgs-field-control">
                        <input type="text" class="tgs-color-picker" data-key="input.border_color" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'input.border_color', '#dddddd')); ?>">
                    </div>
                </div>
                <div class="tgs-field-row">
                    <div class="tgs-field-label">Màu viền khi focus</div>
                    <div class="tgs-field-control">
                        <input type="text" class="tgs-color-picker" data-key="input.focus_border_color" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'input.focus_border_color', '#2271b1')); ?>">
                    </div>
                </div>
                <div class="tgs-field-row">
                    <div class="tgs-field-label">Màu placeholder</div>
                    <div class="tgs-field-control">
                        <input type="text" class="tgs-color-picker" data-key="input.placeholder_color" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'input.placeholder_color', '#a0a5aa')); ?>">
                    </div>
                </div>
            </div>

            <div class="tgs-field-row" style="margin-top:15px;">
                <div class="tgs-field-label">Bo góc input</div>
                <div class="tgs-field-control">
                    <div class="tgs-field-inline">
                        <input type="number" class="tgs-setting-field" data-key="input.border_radius" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'input.border_radius', '4')); ?>" min="0" max="30">
                        <span class="unit">px</span>
                    </div>
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Cỡ chữ input</div>
                <div class="tgs-field-control">
                    <div class="tgs-field-inline">
                        <input type="number" class="tgs-setting-field" data-key="input.font_size" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'input.font_size', '14')); ?>" min="10" max="24">
                        <span class="unit">px</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- LABELS -->
        <div class="tgs-section">
            <h3 class="tgs-section-title">🏷️ Nhãn (Label)</h3>
            <div class="tgs-color-grid">
                <div class="tgs-field-row">
                    <div class="tgs-field-label">Màu chữ label</div>
                    <div class="tgs-field-control">
                        <input type="text" class="tgs-color-picker" data-key="label.text_color" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'label.text_color', '#1e1e1e')); ?>">
                    </div>
                </div>
            </div>
            <div class="tgs-field-row" style="margin-top:15px;">
                <div class="tgs-field-label">Cỡ chữ label</div>
                <div class="tgs-field-control">
                    <div class="tgs-field-inline">
                        <input type="number" class="tgs-setting-field" data-key="label.font_size" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'label.font_size', '14')); ?>" min="10" max="24">
                        <span class="unit">px</span>
                    </div>
                </div>
            </div>
            <div class="tgs-field-row">
                <div class="tgs-field-label">Độ đậm chữ label</div>
                <div class="tgs-field-control">
                    <select class="tgs-setting-field" data-key="label.font_weight">
                        <option value="400" <?php echo tgs_login_selected($s, 'label.font_weight', '400'); ?>>Normal (400)</option>
                        <option value="500" <?php echo tgs_login_selected($s, 'label.font_weight', '500'); ?>>Medium (500)</option>
                        <option value="600" <?php echo tgs_login_selected($s, 'label.font_weight', '600'); ?>>Semi-Bold (600)</option>
                        <option value="700" <?php echo tgs_login_selected($s, 'label.font_weight', '700'); ?>>Bold (700)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- BUTTON -->
        <div class="tgs-section">
            <h3 class="tgs-section-title">🔵 Nút Đăng nhập</h3>
            <div class="tgs-color-grid">
                <div class="tgs-field-row">
                    <div class="tgs-field-label">Màu nền nút</div>
                    <div class="tgs-field-control">
                        <input type="text" class="tgs-color-picker" data-key="button.background_color" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'button.background_color', '#2271b1')); ?>">
                    </div>
                </div>
                <div class="tgs-field-row">
                    <div class="tgs-field-label">Màu chữ nút</div>
                    <div class="tgs-field-control">
                        <input type="text" class="tgs-color-picker" data-key="button.text_color" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'button.text_color', '#ffffff')); ?>">
                    </div>
                </div>
                <div class="tgs-field-row">
                    <div class="tgs-field-label">Màu nền hover</div>
                    <div class="tgs-field-control">
                        <input type="text" class="tgs-color-picker" data-key="button.hover_background_color" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'button.hover_background_color', '#135e96')); ?>">
                    </div>
                </div>
                <div class="tgs-field-row">
                    <div class="tgs-field-label">Màu viền nút</div>
                    <div class="tgs-field-control">
                        <input type="text" class="tgs-color-picker" data-key="button.border_color" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'button.border_color', '#2271b1')); ?>">
                    </div>
                </div>
            </div>

            <div class="tgs-field-row" style="margin-top:15px;">
                <div class="tgs-field-label">Bo góc nút</div>
                <div class="tgs-field-control">
                    <div class="tgs-field-inline">
                        <input type="number" class="tgs-setting-field" data-key="button.border_radius" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'button.border_radius', '4')); ?>" min="0" max="50">
                        <span class="unit">px</span>
                    </div>
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Chiều rộng nút</div>
                <div class="tgs-field-control">
                    <select class="tgs-setting-field" data-key="button.width">
                        <option value="auto" <?php echo tgs_login_selected($s, 'button.width', 'auto'); ?>>Auto</option>
                        <option value="100%" <?php echo tgs_login_selected($s, 'button.width', '100%'); ?>>Full width (100%)</option>
                    </select>
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Kiểu chữ nút</div>
                <div class="tgs-field-control">
                    <select class="tgs-setting-field" data-key="button.text_transform">
                        <option value="none" <?php echo tgs_login_selected($s, 'button.text_transform', 'none'); ?>>Bình thường</option>
                        <option value="uppercase" <?php echo tgs_login_selected($s, 'button.text_transform', 'uppercase'); ?>>IN HOA</option>
                        <option value="capitalize" <?php echo tgs_login_selected($s, 'button.text_transform', 'capitalize'); ?>>Viết Hoa Đầu</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- LINKS -->
        <div class="tgs-section">
            <h3 class="tgs-section-title">🔗 Liên kết</h3>
            <div class="tgs-color-grid">
                <div class="tgs-field-row">
                    <div class="tgs-field-label">Màu link</div>
                    <div class="tgs-field-control">
                        <input type="text" class="tgs-color-picker" data-key="links.text_color" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'links.text_color', '#50575e')); ?>">
                    </div>
                </div>
                <div class="tgs-field-row">
                    <div class="tgs-field-label">Màu link hover</div>
                    <div class="tgs-field-control">
                        <input type="text" class="tgs-color-picker" data-key="links.hover_color" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'links.hover_color', '#135e96')); ?>">
                    </div>
                </div>
            </div>

            <div class="tgs-field-row" style="margin-top:15px;">
                <div class="tgs-field-label">Ẩn link "Quay lại trang chính"</div>
                <div class="tgs-field-control">
                    <label class="tgs-toggle">
                        <input type="checkbox" data-key="links.hide_back_to_blog" <?php echo tgs_login_checked($s, 'links.hide_back_to_blog'); ?>>
                        <span class="tgs-toggle-slider"></span>
                    </label>
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Ẩn link "Quên mật khẩu?"</div>
                <div class="tgs-field-control">
                    <label class="tgs-toggle">
                        <input type="checkbox" data-key="links.hide_lost_password" <?php echo tgs_login_checked($s, 'links.hide_lost_password'); ?>>
                        <span class="tgs-toggle-slider"></span>
                    </label>
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Ẩn link "Đăng ký"</div>
                <div class="tgs-field-control">
                    <label class="tgs-toggle">
                        <input type="checkbox" data-key="links.hide_register" <?php echo tgs_login_checked($s, 'links.hide_register'); ?>>
                        <span class="tgs-toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- ERROR MESSAGES -->
        <div class="tgs-section">
            <h3 class="tgs-section-title">⚠️ Thông báo lỗi</h3>
            <div class="tgs-color-grid">
                <div class="tgs-field-row">
                    <div class="tgs-field-label">Màu nền lỗi</div>
                    <div class="tgs-field-control">
                        <input type="text" class="tgs-color-picker" data-key="error.background_color" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'error.background_color', '#fcf0f1')); ?>">
                    </div>
                </div>
                <div class="tgs-field-row">
                    <div class="tgs-field-label">Màu viền lỗi</div>
                    <div class="tgs-field-control">
                        <input type="text" class="tgs-color-picker" data-key="error.border_color" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'error.border_color', '#d63638')); ?>">
                    </div>
                </div>
                <div class="tgs-field-row">
                    <div class="tgs-field-label">Màu chữ lỗi</div>
                    <div class="tgs-field-control">
                        <input type="text" class="tgs-color-picker" data-key="error.text_color" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'error.text_color', '#d63638')); ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================== -->
    <!-- TAB: TEXT -->
    <!-- ============================== -->
    <div id="tab-text" class="tgs-login-tab-content">
        <div class="tgs-section">
            <h3 class="tgs-section-title">📄 Tiêu đề & Mô tả (trên form)</h3>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Tiêu đề</div>
                <div class="tgs-field-control">
                    <input type="text" class="tgs-setting-field regular-text" data-key="text.login_title" 
                           value="<?php echo esc_attr(tgs_login_val($s, 'text.login_title')); ?>"
                           placeholder="Ví dụ: Chào mừng bạn đến TGS">
                    <div class="tgs-field-desc">Để trống = không hiển thị</div>
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Màu tiêu đề</div>
                <div class="tgs-field-control">
                    <input type="text" class="tgs-color-picker" data-key="text.title_color" 
                           value="<?php echo esc_attr(tgs_login_val($s, 'text.title_color', '#333333')); ?>">
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Cỡ chữ tiêu đề</div>
                <div class="tgs-field-control">
                    <div class="tgs-field-inline">
                        <input type="number" class="tgs-setting-field" data-key="text.title_font_size" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'text.title_font_size', '22')); ?>" min="12" max="48">
                        <span class="unit">px</span>
                    </div>
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Mô tả phụ</div>
                <div class="tgs-field-control">
                    <input type="text" class="tgs-setting-field regular-text" data-key="text.login_subtitle" 
                           value="<?php echo esc_attr(tgs_login_val($s, 'text.login_subtitle')); ?>"
                           placeholder="Ví dụ: Đăng nhập để quản lý kho hàng">
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Màu mô tả</div>
                <div class="tgs-field-control">
                    <input type="text" class="tgs-color-picker" data-key="text.subtitle_color" 
                           value="<?php echo esc_attr(tgs_login_val($s, 'text.subtitle_color', '#666666')); ?>">
                </div>
            </div>
        </div>

        <div class="tgs-section">
            <h3 class="tgs-section-title">📌 Footer (dưới cùng)</h3>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Nội dung footer</div>
                <div class="tgs-field-control">
                    <textarea class="tgs-setting-textarea regular-text" data-key="text.footer_text" rows="3"
                              placeholder="Ví dụ: © 2025 Thế Giới Sữa. Powered by TGS Shop Management"><?php echo esc_textarea(tgs_login_val($s, 'text.footer_text')); ?></textarea>
                    <div class="tgs-field-desc">Hỗ trợ HTML cơ bản (a, strong, em, br). Để trống = không hiển thị.</div>
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Màu footer</div>
                <div class="tgs-field-control">
                    <input type="text" class="tgs-color-picker" data-key="text.footer_color" 
                           value="<?php echo esc_attr(tgs_login_val($s, 'text.footer_color', '#999999')); ?>">
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Cỡ chữ footer</div>
                <div class="tgs-field-control">
                    <div class="tgs-field-inline">
                        <input type="number" class="tgs-setting-field" data-key="text.footer_font_size" 
                               value="<?php echo esc_attr(tgs_login_val($s, 'text.footer_font_size', '12')); ?>" min="8" max="20">
                        <span class="unit">px</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================== -->
    <!-- TAB: LAYOUT -->
    <!-- ============================== -->
    <div id="tab-layout" class="tgs-login-tab-content">
        <div class="tgs-section">
            <h3 class="tgs-section-title">📐 Bố cục trang</h3>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Vị trí form (ngang)</div>
                <div class="tgs-field-control">
                    <select class="tgs-setting-field" data-key="layout.form_position">
                        <option value="center" <?php echo tgs_login_selected($s, 'layout.form_position', 'center'); ?>>Giữa</option>
                        <option value="left" <?php echo tgs_login_selected($s, 'layout.form_position', 'left'); ?>>Trái</option>
                        <option value="right" <?php echo tgs_login_selected($s, 'layout.form_position', 'right'); ?>>Phải</option>
                    </select>
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Vị trí form (dọc)</div>
                <div class="tgs-field-control">
                    <select class="tgs-setting-field" data-key="layout.form_vertical_position">
                        <option value="top" <?php echo tgs_login_selected($s, 'layout.form_vertical_position', 'top'); ?>>Trên</option>
                        <option value="center" <?php echo tgs_login_selected($s, 'layout.form_vertical_position', 'center'); ?>>Giữa</option>
                        <option value="bottom" <?php echo tgs_login_selected($s, 'layout.form_vertical_position', 'bottom'); ?>>Dưới</option>
                    </select>
                </div>
            </div>

            <div class="tgs-field-row">
                <div class="tgs-field-label">Font family</div>
                <div class="tgs-field-control">
                    <select class="tgs-setting-field" data-key="layout.page_font_family">
                        <option value="" <?php echo tgs_login_selected($s, 'layout.page_font_family', ''); ?>>Mặc định (hệ thống)</option>
                        <option value="Inter" <?php echo tgs_login_selected($s, 'layout.page_font_family', 'Inter'); ?>>Inter</option>
                        <option value="Roboto" <?php echo tgs_login_selected($s, 'layout.page_font_family', 'Roboto'); ?>>Roboto</option>
                        <option value="Open Sans" <?php echo tgs_login_selected($s, 'layout.page_font_family', 'Open Sans'); ?>>Open Sans</option>
                        <option value="Lato" <?php echo tgs_login_selected($s, 'layout.page_font_family', 'Lato'); ?>>Lato</option>
                        <option value="Montserrat" <?php echo tgs_login_selected($s, 'layout.page_font_family', 'Montserrat'); ?>>Montserrat</option>
                        <option value="Nunito" <?php echo tgs_login_selected($s, 'layout.page_font_family', 'Nunito'); ?>>Nunito</option>
                        <option value="Poppins" <?php echo tgs_login_selected($s, 'layout.page_font_family', 'Poppins'); ?>>Poppins</option>
                        <option value="Source Sans Pro" <?php echo tgs_login_selected($s, 'layout.page_font_family', 'Source Sans Pro'); ?>>Source Sans Pro</option>
                    </select>
                    <div class="tgs-field-desc">Google Fonts sẽ được tải tự động</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================== -->
    <!-- TAB: CUSTOM CODE -->
    <!-- ============================== -->
    <div id="tab-custom-code" class="tgs-login-tab-content">
        <div class="tgs-section">
            <h3 class="tgs-section-title">🎨 CSS tùy chỉnh</h3>
            <div class="tgs-field-row">
                <div class="tgs-field-label">Custom CSS</div>
                <div class="tgs-field-control">
                    <textarea class="tgs-setting-textarea" data-key="custom_css" rows="12" style="width:100%;max-width:100%;font-family:monospace;font-size:12px;"
                              placeholder="/* CSS tùy chỉnh thêm */
body.login {
    /* ... */
}"><?php echo esc_textarea(tgs_login_val($s, 'custom_css')); ?></textarea>
                    <div class="tgs-field-desc">CSS sẽ được thêm sau tất cả CSS được sinh tự động</div>
                </div>
            </div>
        </div>

        <div class="tgs-section">
            <h3 class="tgs-section-title">⚡ JavaScript tùy chỉnh</h3>
            <div class="tgs-field-row">
                <div class="tgs-field-label">Custom JS</div>
                <div class="tgs-field-control">
                    <textarea class="tgs-setting-textarea" data-key="custom_js" rows="12" style="width:100%;max-width:100%;font-family:monospace;font-size:12px;"
                              placeholder="// JavaScript tùy chỉnh
document.addEventListener('DOMContentLoaded', function() {
    // ...
});"><?php echo esc_textarea(tgs_login_val($s, 'custom_js')); ?></textarea>
                    <div class="tgs-field-desc">JS sẽ được chạy sau khi trang đăng nhập load xong</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================== -->
    <!-- TAB: IMPORT/EXPORT -->
    <!-- ============================== -->
    <div id="tab-import-export" class="tgs-login-tab-content">
        <div class="tgs-section">
            <h3 class="tgs-section-title">💾 Import / Export cài đặt</h3>
            <div class="tgs-info-box">
                💡 Export cài đặt để backup hoặc copy sang site khác. Import để khôi phục hoặc áp dụng cài đặt từ file JSON.
            </div>

            <div class="tgs-import-export-area">
                <!-- EXPORT -->
                <div class="tgs-export-box">
                    <h3>📤 Export cài đặt</h3>
                    <p style="font-size:13px;color:#666;">Nhấn nút bên dưới để xuất toàn bộ cài đặt ra JSON.</p>
                    <div class="tgs-btn-group">
                        <button class="button button-primary" id="tgs-export-btn">📤 Export</button>
                        <button class="button" id="tgs-copy-export">📋 Copy</button>
                        <button class="button" id="tgs-download-export">⬇️ Tải file</button>
                    </div>
                    <textarea id="tgs-export-data" rows="10" style="width:100%;margin-top:10px;font-family:monospace;font-size:11px;" readonly placeholder="Nhấn Export để xuất dữ liệu..."></textarea>
                </div>

                <!-- IMPORT -->
                <div class="tgs-import-box">
                    <h3>📥 Import cài đặt</h3>
                    <p style="font-size:13px;color:#666;">Dán JSON vào ô bên dưới hoặc chọn file .json để import.</p>
                    <textarea id="tgs-import-data" rows="10" style="width:100%;font-family:monospace;font-size:11px;" placeholder="Dán JSON cài đặt vào đây..."></textarea>
                    <div class="tgs-btn-group" style="margin-top:10px;">
                        <button class="button button-primary" id="tgs-import-btn">📥 Import từ ô trên</button>
                        <button class="button" id="tgs-import-file-btn">📁 Chọn file .json</button>
                    </div>
                    <input type="file" id="tgs-import-file-input" accept=".json" style="display:none;">
                </div>
            </div>
        </div>

        <!-- DANGER ZONE -->
        <div class="tgs-section" style="margin-top:30px;">
            <h3 class="tgs-section-title" style="border-bottom-color:#d63638;color:#d63638;">🗑️ Vùng nguy hiểm</h3>
            <div class="tgs-info-box danger">
                ⚠️ Khôi phục mặc định sẽ XÓA tất cả cài đặt hiện tại và trả về giá trị ban đầu.
            </div>
            <button class="button tgs-btn-reset">🔄 Khôi phục cài đặt mặc định</button>
        </div>
    </div>

    <!-- STICKY FOOTER BAR -->
    <div class="tgs-sticky-footer">
        <div class="tgs-save-status"><span style="color:#00a32a;">✓ Đã lưu</span></div>
        <div class="tgs-btn-group" style="margin-top:0;">
            <button class="button tgs-btn-preview">👁️ Xem trước</button>
            <button class="button button-primary tgs-btn-save">💾 Lưu cài đặt</button>
        </div>
    </div>

</div><!-- .wrap -->

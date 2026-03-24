<?php
/**
 * Settings Manager
 * 
 * Quản lý tất cả cài đặt, lưu JSON vào sitemeta (multisite) hoặc options (single site)
 * Hỗ trợ import/export settings
 * 
 * @package TGS_Login_Customizer
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TGS_Login_Settings {

    /**
     * Singleton instance
     * @var TGS_Login_Settings|null
     */
    private static $instance = null;

    /**
     * Cached settings
     * @var array|null
     */
    private $settings = null;

    /**
     * Meta key for sitemeta
     * @var string
     */
    const META_KEY = 'tgs_login_customizer_settings';

    /**
     * Get singleton instance
     * @return TGS_Login_Settings
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Load settings on init
    }

    /**
     * Get default settings
     * 
     * @return array
     */
    public function get_defaults() {
        return array(
            // === GENERAL ===
            'general' => array(
                'enabled' => true,                          // Bật/tắt plugin
                'redirect_after_login' => '',               // URL chuyển hướng sau đăng nhập (trống = dùng redirect_to mặc định)
                'redirect_priority' => 'requested_url',     // 'requested_url' | 'custom_url' - Ưu tiên URL yêu cầu hay URL tùy chỉnh
                'default_redirect_url' => '',               // URL mặc định nếu không có redirect_to (ví dụ: admin.php?page=tgs-shop-management)
            ),

            // === AUTO LOGIN ===
            'auto_login' => array(
                'enabled' => false,                         // Bật/tắt tự động đăng nhập
                'username' => '',                           // Tài khoản mặc định
                'password' => '',                           // Mật khẩu mặc định (sẽ được mã hóa base64)
                'prefill_only' => true,                     // Chỉ điền sẵn, không tự động submit
                'show_credentials' => true,                 // Hiển thị thông tin đăng nhập cho user thấy
                'auto_submit_delay' => 3,                   // Giây chờ trước khi tự động submit (0 = submit ngay)
                'show_countdown' => true,                   // Hiển thị đếm ngược
            ),

            // === LOGO ===
            'logo' => array(
                'image_url' => '',                          // URL hình ảnh logo
                'width' => '320',                           // Chiều rộng logo (px)
                'height' => '120',                          // Chiều cao logo (px)
                'link_url' => '',                           // URL khi click logo (trống = home_url)
                'link_title' => '',                         // Title khi hover logo
                'border_radius' => '0',                     // Bo góc logo (px)
                'margin_bottom' => '25',                    // Khoảng cách dưới logo (px)
            ),

            // === BACKGROUND ===
            'background' => array(
                'type' => 'color',                          // 'color' | 'image' | 'gradient'
                'color' => '#f0f0f1',                       // Màu nền
                'image_url' => '',                          // URL ảnh nền
                'image_size' => 'cover',                    // 'cover' | 'contain' | 'auto'
                'image_position' => 'center center',        // Vị trí ảnh nền
                'image_repeat' => 'no-repeat',              // 'no-repeat' | 'repeat' | 'repeat-x' | 'repeat-y'
                'image_attachment' => 'fixed',              // 'fixed' | 'scroll'
                'overlay_enabled' => false,                 // Bật lớp phủ mờ trên ảnh nền
                'overlay_color' => 'rgba(0,0,0,0.3)',       // Màu lớp phủ
                'gradient_start' => '#667eea',              // Gradient bắt đầu
                'gradient_end' => '#764ba2',                // Gradient kết thúc
                'gradient_direction' => '135deg',           // Hướng gradient
            ),

            // === FORM BOX ===
            'form' => array(
                'background_color' => '#ffffff',            // Màu nền form
                'border_radius' => '8',                     // Bo góc form (px)
                'border_color' => '#dddddd',                // Màu viền form
                'border_width' => '1',                      // Độ dày viền (px)
                'box_shadow' => '0 4px 20px rgba(0,0,0,0.1)', // Bóng đổ
                'padding' => '26',                          // Padding bên trong (px)
                'width' => '350',                           // Chiều rộng form (px)
                'max_width' => '100%',                      // Max-width
            ),

            // === INPUT FIELDS ===
            'input' => array(
                'background_color' => '#ffffff',            // Màu nền input
                'text_color' => '#333333',                  // Màu chữ input
                'border_color' => '#dddddd',                // Màu viền input
                'border_radius' => '4',                     // Bo góc input (px)
                'border_width' => '1',                      // Độ dày viền (px)
                'focus_border_color' => '#2271b1',          // Màu viền khi focus
                'font_size' => '14',                        // Cỡ chữ (px)
                'padding' => '8',                           // Padding (px)
                'placeholder_color' => '#a0a5aa',           // Màu placeholder
            ),

            // === LABELS ===
            'label' => array(
                'text_color' => '#1e1e1e',                  // Màu chữ label
                'font_size' => '14',                        // Cỡ chữ (px)
                'font_weight' => '600',                     // Độ đậm chữ
            ),

            // === BUTTON ===
            'button' => array(
                'background_color' => '#2271b1',            // Màu nền button
                'text_color' => '#ffffff',                  // Màu chữ button
                'border_color' => '#2271b1',                // Màu viền button
                'border_radius' => '4',                     // Bo góc (px)
                'hover_background_color' => '#135e96',      // Màu nền hover
                'hover_text_color' => '#ffffff',            // Màu chữ hover
                'font_size' => '14',                        // Cỡ chữ (px)
                'font_weight' => '600',                     // Độ đậm
                'padding' => '8px 20px',                    // Padding
                'width' => 'auto',                          // 'auto' | '100%'
                'text_transform' => 'none',                 // 'none' | 'uppercase' | 'capitalize'
            ),

            // === LINKS ===
            'links' => array(
                'text_color' => '#50575e',                  // Màu chữ link
                'hover_color' => '#135e96',                 // Màu hover link
                'font_size' => '13',                        // Cỡ chữ
                'hide_back_to_blog' => false,               // Ẩn link "Quay lại trang chính"
                'hide_lost_password' => false,              // Ẩn link "Quên mật khẩu?"
                'hide_register' => false,                   // Ẩn link đăng ký
            ),

            // === CUSTOM TEXT ===
            'text' => array(
                'login_title' => '',                        // Tiêu đề tùy chỉnh trên form (trống = không hiển thị)
                'login_subtitle' => '',                     // Mô tả phụ dưới tiêu đề
                'title_color' => '#333333',                 // Màu tiêu đề
                'title_font_size' => '22',                  // Cỡ chữ tiêu đề (px)
                'subtitle_color' => '#666666',              // Màu mô tả
                'subtitle_font_size' => '14',               // Cỡ chữ mô tả (px)
                'footer_text' => '',                        // Text dưới cùng (copyright, etc.)
                'footer_color' => '#999999',                // Màu footer text
                'footer_font_size' => '12',                 // Cỡ chữ footer
            ),

            // === CUSTOM CSS ===
            'custom_css' => '',                             // CSS tùy chỉnh thêm

            // === CUSTOM JS ===
            'custom_js' => '',                              // JS tùy chỉnh thêm

            // === REMEMBER ME ===
            'remember_me' => array(
                'checked_by_default' => true,               // Tự động check "Ghi nhớ đăng nhập"
                'hide' => false,                            // Ẩn checkbox nhớ mật khẩu
            ),

            // === ERROR MESSAGES ===
            'error' => array(
                'background_color' => '#fcf0f1',            // Màu nền thông báo lỗi
                'border_color' => '#d63638',                // Màu viền lỗi
                'text_color' => '#d63638',                  // Màu chữ lỗi
            ),

            // === LAYOUT ===
            'layout' => array(
                'form_position' => 'center',                // 'center' | 'left' | 'right'
                'form_vertical_position' => 'center',       // 'top' | 'center' | 'bottom'
                'page_font_family' => '',                   // Font family cho trang (trống = mặc định)
            ),
        );
    }

    /**
     * Get all settings
     * 
     * @param bool $force_reload Force reload from database
     * @return array
     */
    public function get_all($force_reload = false) {
        if (null !== $this->settings && !$force_reload) {
            return $this->settings;
        }

        $json = $this->get_from_storage();

        if (empty($json)) {
            $this->settings = $this->get_defaults();
        } else {
            $saved = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($saved)) {
                // Deep merge with defaults to ensure all keys exist
                $this->settings = $this->deep_merge($this->get_defaults(), $saved);
            } else {
                $this->settings = $this->get_defaults();
            }
        }

        return $this->settings;
    }

    /**
     * Get a specific setting group or value
     * 
     * @param string $key Setting key (e.g., 'logo', 'background', 'logo.image_url')
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function get($key, $default = null) {
        $settings = $this->get_all();

        // Support dot notation: 'logo.image_url'
        $keys = explode('.', $key);
        $value = $settings;

        foreach ($keys as $k) {
            if (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * Save all settings
     * 
     * @param array $settings
     * @return bool
     */
    public function save_all($settings) {
        $json = wp_json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $result = $this->save_to_storage($json);

        if ($result) {
            $this->settings = $settings;
        }

        return $result;
    }

    /**
     * Update a specific setting group
     * 
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function update($key, $value) {
        $settings = $this->get_all();

        $keys = explode('.', $key);
        $ref = &$settings;

        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                $ref[$k] = $value;
            } else {
                if (!isset($ref[$k]) || !is_array($ref[$k])) {
                    $ref[$k] = array();
                }
                $ref = &$ref[$k];
            }
        }

        return $this->save_all($settings);
    }

    /**
     * Export settings as JSON string
     * 
     * @return string
     */
    public function export() {
        $settings = $this->get_all();
        return wp_json_encode(array(
            'plugin' => 'tgs_login_customizer',
            'version' => TGS_LOGIN_CUSTOMIZER_VERSION,
            'exported_at' => current_time('mysql'),
            'site_url' => get_site_url(),
            'settings' => $settings
        ), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Import settings from JSON string
     * 
     * @param string $json JSON string
     * @return bool|WP_Error
     */
    public function import($json) {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('invalid_json', 'JSON không hợp lệ: ' . json_last_error_msg());
        }

        if (!is_array($data)) {
            return new WP_Error('invalid_format', 'Định dạng dữ liệu không hợp lệ');
        }

        // Check if it's an export file (has 'settings' key)
        if (isset($data['plugin']) && $data['plugin'] === 'tgs_login_customizer' && isset($data['settings'])) {
            $settings = $data['settings'];
        } else {
            // Assume direct settings format
            $settings = $data;
        }

        // Validate and merge with defaults
        $merged = $this->deep_merge($this->get_defaults(), $settings);

        return $this->save_all($merged);
    }

    /**
     * Reset to defaults
     * 
     * @return bool
     */
    public function reset() {
        $this->settings = null;
        return $this->save_all($this->get_defaults());
    }

    /**
     * Delete all settings
     * 
     * @return bool
     */
    public function delete() {
        $this->settings = null;
        return $this->delete_from_storage();
    }

    // ========================================
    // STORAGE METHODS (sitemeta / options)
    // ========================================

    /**
     * Get JSON from storage
     * 
     * @return string|false
     */
    private function get_from_storage() {
        if (is_multisite()) {
            // Lưu vào sitemeta dùng chung cho cả network
            $network_id = get_current_network_id();
            return get_network_option($network_id, self::META_KEY, '');
        } else {
            return get_option(self::META_KEY, '');
        }
    }

    /**
     * Save JSON to storage
     * 
     * @param string $json
     * @return bool
     */
    private function save_to_storage($json) {
        if (is_multisite()) {
            $network_id = get_current_network_id();
            return update_network_option($network_id, self::META_KEY, $json);
        } else {
            return update_option(self::META_KEY, $json, false);
        }
    }

    /**
     * Delete from storage
     * 
     * @return bool
     */
    private function delete_from_storage() {
        if (is_multisite()) {
            $network_id = get_current_network_id();
            return delete_network_option($network_id, self::META_KEY);
        } else {
            return delete_option(self::META_KEY);
        }
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Deep merge two arrays (defaults + saved)
     * 
     * @param array $defaults
     * @param array $saved
     * @return array
     */
    private function deep_merge($defaults, $saved) {
        $merged = $defaults;

        foreach ($saved as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->deep_merge($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Sanitize settings before saving
     * 
     * @param array $settings
     * @return array
     */
    public function sanitize($settings) {
        // Auto login password - encode to base64
        if (isset($settings['auto_login']['password']) && !empty($settings['auto_login']['password'])) {
            // Don't re-encode if already encoded
            $decoded = base64_decode($settings['auto_login']['password'], true);
            if ($decoded === false || base64_encode($decoded) !== $settings['auto_login']['password']) {
                $settings['auto_login']['password'] = base64_encode($settings['auto_login']['password']);
            }
        }

        // Sanitize URLs
        $url_fields = array(
            array('general', 'redirect_after_login'),
            array('general', 'default_redirect_url'),
            array('logo', 'image_url'),
            array('logo', 'link_url'),
            array('background', 'image_url'),
        );

        foreach ($url_fields as $path) {
            $ref = &$settings;
            $valid = true;
            foreach ($path as $i => $k) {
                if ($i === count($path) - 1) {
                    if (isset($ref[$k]) && !empty($ref[$k])) {
                        $ref[$k] = esc_url_raw($ref[$k]);
                    }
                } else {
                    if (isset($ref[$k]) && is_array($ref[$k])) {
                        $ref = &$ref[$k];
                    } else {
                        $valid = false;
                        break;
                    }
                }
            }
        }

        // Sanitize CSS
        if (isset($settings['custom_css'])) {
            $settings['custom_css'] = wp_strip_all_tags($settings['custom_css']);
        }

        return $settings;
    }

    /**
     * Get decoded auto login password
     * 
     * @return string
     */
    public function get_auto_login_password() {
        $encoded = $this->get('auto_login.password', '');
        if (empty($encoded)) {
            return '';
        }
        $decoded = base64_decode($encoded, true);
        return ($decoded !== false) ? $decoded : $encoded;
    }
}

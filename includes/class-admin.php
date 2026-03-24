<?php
/**
 * Admin Settings Page
 * 
 * Trang quản trị cài đặt tùy chỉnh giao diện đăng nhập
 * Menu trong WP Admin + AJAX handlers
 * 
 * @package TGS_Login_Customizer
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TGS_Login_Admin {

    /**
     * Singleton instance
     * @var TGS_Login_Admin|null
     */
    private static $instance = null;

    /**
     * Settings instance
     * @var TGS_Login_Settings
     */
    private $settings;

    /**
     * Page slug
     * @var string
     */
    const PAGE_SLUG = 'tgs-login-customizer';

    /**
     * Get singleton instance
     * @return TGS_Login_Admin
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
        $this->settings = TGS_Login_Settings::get_instance();
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Admin menu
        if (is_multisite()) {
            add_action('network_admin_menu', array($this, 'add_network_menu'));
        }
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // AJAX handlers
        add_action('wp_ajax_tgs_login_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_tgs_login_reset_settings', array($this, 'ajax_reset_settings'));
        add_action('wp_ajax_tgs_login_export_settings', array($this, 'ajax_export_settings'));
        add_action('wp_ajax_tgs_login_import_settings', array($this, 'ajax_import_settings'));
        add_action('wp_ajax_tgs_login_preview_data', array($this, 'ajax_preview_data'));

        // Enqueue admin scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Add network admin menu
     */
    public function add_network_menu() {
        add_submenu_page(
            'settings.php',
            'TGS Login Customizer',
            'Login Customizer',
            'manage_network_options',
            self::PAGE_SLUG,
            array($this, 'render_admin_page')
        );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            'TGS Login Customizer',
            'Login Customizer',
            'manage_options',
            self::PAGE_SLUG,
            array($this, 'render_admin_page')
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only on our admin page
        if (strpos($hook, self::PAGE_SLUG) === false) {
            return;
        }

        // WordPress media uploader
        wp_enqueue_media();

        // Color picker
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        // Admin CSS
        wp_enqueue_style(
            'tgs-login-admin-css',
            TGS_LOGIN_CUSTOMIZER_PLUGIN_URL . 'assets/css/admin.css',
            array('wp-color-picker'),
            TGS_LOGIN_CUSTOMIZER_VERSION
        );

        // Admin JS
        wp_enqueue_script(
            'tgs-login-admin-js',
            TGS_LOGIN_CUSTOMIZER_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'wp-color-picker', 'wp-util'),
            TGS_LOGIN_CUSTOMIZER_VERSION,
            true
        );

        // Localize script data
        wp_localize_script('tgs-login-admin-js', 'tgsLoginAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tgs_login_customizer_nonce'),
            'settings' => $this->settings->get_all(),
            'defaults' => $this->settings->get_defaults(),
            'loginUrl' => wp_login_url(),
            'homeUrl' => home_url('/'),
            'i18n' => array(
                'save_success' => 'Đã lưu cài đặt thành công!',
                'save_error' => 'Có lỗi khi lưu cài đặt.',
                'reset_confirm' => 'Bạn có chắc chắn muốn khôi phục cài đặt mặc định? Tất cả cài đặt hiện tại sẽ bị mất.',
                'reset_success' => 'Đã khôi phục cài đặt mặc định.',
                'import_confirm' => 'Import cài đặt sẽ ghi đè lên cài đặt hiện tại. Tiếp tục?',
                'import_success' => 'Đã import cài đặt thành công!',
                'import_error' => 'Có lỗi khi import cài đặt.',
                'export_success' => 'Đã export cài đặt.',
                'choose_image' => 'Chọn hình ảnh',
                'use_image' => 'Sử dụng hình ảnh này',
                'uploading' => 'Đang tải...',
                'saving' => 'Đang lưu...',
            )
        ));
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        // Check permissions
        if (is_multisite() && is_network_admin()) {
            if (!current_user_can('manage_network_options')) {
                wp_die('Bạn không có quyền truy cập trang này.');
            }
        } else {
            if (!current_user_can('manage_options')) {
                wp_die('Bạn không có quyền truy cập trang này.');
            }
        }

        // Load template
        include TGS_LOGIN_CUSTOMIZER_PLUGIN_DIR . 'templates/admin-settings.php';
    }

    // ========================================
    // AJAX HANDLERS
    // ========================================

    /**
     * AJAX: Save settings
     */
    public function ajax_save_settings() {
        check_ajax_referer('tgs_login_customizer_nonce', 'nonce');

        if (!current_user_can('manage_options') && !(is_multisite() && current_user_can('manage_network_options'))) {
            wp_send_json_error(array('message' => 'Không có quyền.'));
        }

        $settings_json = isset($_POST['settings']) ? wp_unslash($_POST['settings']) : '';
        $settings = json_decode($settings_json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error(array('message' => 'Dữ liệu JSON không hợp lệ.'));
        }

        // Sanitize
        $settings = $this->settings->sanitize($settings);

        // Save
        $result = $this->settings->save_all($settings);

        if ($result) {
            wp_send_json_success(array(
                'message' => 'Đã lưu cài đặt thành công!',
                'settings' => $this->settings->get_all(true)
            ));
        } else {
            wp_send_json_error(array('message' => 'Có lỗi khi lưu cài đặt.'));
        }
    }

    /**
     * AJAX: Reset settings
     */
    public function ajax_reset_settings() {
        check_ajax_referer('tgs_login_customizer_nonce', 'nonce');

        if (!current_user_can('manage_options') && !(is_multisite() && current_user_can('manage_network_options'))) {
            wp_send_json_error(array('message' => 'Không có quyền.'));
        }

        $result = $this->settings->reset();

        if ($result) {
            wp_send_json_success(array(
                'message' => 'Đã khôi phục cài đặt mặc định.',
                'settings' => $this->settings->get_all(true)
            ));
        } else {
            wp_send_json_error(array('message' => 'Có lỗi khi reset.'));
        }
    }

    /**
     * AJAX: Export settings
     */
    public function ajax_export_settings() {
        check_ajax_referer('tgs_login_customizer_nonce', 'nonce');

        if (!current_user_can('manage_options') && !(is_multisite() && current_user_can('manage_network_options'))) {
            wp_send_json_error(array('message' => 'Không có quyền.'));
        }

        $json = $this->settings->export();

        wp_send_json_success(array(
            'json' => $json,
            'filename' => 'tgs-login-settings-' . date('Y-m-d-His') . '.json'
        ));
    }

    /**
     * AJAX: Import settings
     */
    public function ajax_import_settings() {
        check_ajax_referer('tgs_login_customizer_nonce', 'nonce');

        if (!current_user_can('manage_options') && !(is_multisite() && current_user_can('manage_network_options'))) {
            wp_send_json_error(array('message' => 'Không có quyền.'));
        }

        $json = isset($_POST['import_data']) ? wp_unslash($_POST['import_data']) : '';

        if (empty($json)) {
            wp_send_json_error(array('message' => 'Dữ liệu import trống.'));
        }

        $result = $this->settings->import($json);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array(
            'message' => 'Đã import cài đặt thành công!',
            'settings' => $this->settings->get_all(true)
        ));
    }

    /**
     * AJAX: Get preview data (current settings for live preview)
     */
    public function ajax_preview_data() {
        check_ajax_referer('tgs_login_customizer_nonce', 'nonce');

        wp_send_json_success(array(
            'settings' => $this->settings->get_all()
        ));
    }
}

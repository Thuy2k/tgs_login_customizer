<?php
/**
 * Plugin Name: TGS Login Customizer
 * Plugin URI: https://bizgpt.vn/
 * Description: Tùy chỉnh giao diện trang đăng nhập WordPress - Logo, màu sắc, background, tự động đăng nhập, chuyển hướng sau đăng nhập
 * Version: 1.0.0
 * Author: BIZGPT_AI
 * Author URI: https://bizgpt.vn/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: tgs-login-customizer
 * Domain Path: /languages
 * Network: true
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin Constants
define('TGS_LOGIN_CUSTOMIZER_VERSION', '1.0.0');
define('TGS_LOGIN_CUSTOMIZER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TGS_LOGIN_CUSTOMIZER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TGS_LOGIN_CUSTOMIZER_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 * 
 * @since 1.0.0
 */
final class TGS_Login_Customizer {

    /**
     * Singleton instance
     * @var TGS_Login_Customizer|null
     */
    private static $instance = null;

    /**
     * Sitemeta key for storing settings as JSON
     * @var string
     */
    const META_KEY = 'tgs_login_customizer_settings';

    /**
     * Get singleton instance
     * 
     * @return TGS_Login_Customizer
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
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load required files
     */
    private function load_dependencies() {
        require_once TGS_LOGIN_CUSTOMIZER_PLUGIN_DIR . 'includes/class-settings.php';
        require_once TGS_LOGIN_CUSTOMIZER_PLUGIN_DIR . 'includes/class-login-customizer.php';
        require_once TGS_LOGIN_CUSTOMIZER_PLUGIN_DIR . 'includes/class-admin.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Initialize components
        add_action('init', array($this, 'init_components'));

        // Register activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    /**
     * Init components
     */
    public function init_components() {
        // Initialize settings
        TGS_Login_Settings::get_instance();

        // Initialize login page customizer (always active - even when not logged in)
        TGS_Login_Page_Customizer::get_instance();

        // Initialize admin page (only when logged in and in admin)
        if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
            TGS_Login_Admin::get_instance();
        }
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Set default settings if not exists
        $settings = TGS_Login_Settings::get_instance();
        if (empty($settings->get_all())) {
            $settings->save_all($settings->get_defaults());
        }
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clean up if needed
    }
}

// Initialize plugin
add_action('plugins_loaded', function() {
    TGS_Login_Customizer::get_instance();
}, 5);

<?php
/**
 * Login Page Customizer
 * 
 * Can thiệp vào trang đăng nhập WordPress để tùy chỉnh giao diện
 * Xử lý auto-login, redirect, custom styles
 * 
 * @package TGS_Login_Customizer
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TGS_Login_Page_Customizer {

    /**
     * Singleton instance
     * @var TGS_Login_Page_Customizer|null
     */
    private static $instance = null;

    /**
     * Settings instance
     * @var TGS_Login_Settings
     */
    private $settings;

    /**
     * Get singleton instance
     * @return TGS_Login_Page_Customizer
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

        // Chỉ chạy trên trang login
        add_action('login_enqueue_scripts', array($this, 'enqueue_login_styles'), 999);
        add_action('login_enqueue_scripts', array($this, 'enqueue_login_scripts'), 999);

        // Custom login header
        add_action('login_head', array($this, 'login_head_custom'));

        // Logo URL & title
        add_filter('login_headerurl', array($this, 'login_logo_url'));
        add_filter('login_headertext', array($this, 'login_logo_title'));

        // Custom message above form
        add_filter('login_message', array($this, 'login_custom_message'));

        // Footer text
        add_action('login_footer', array($this, 'login_footer_content'));

        // Remember me default
        add_action('login_form', array($this, 'modify_remember_me'));

        // Redirect after login
        add_filter('login_redirect', array($this, 'custom_login_redirect'), 10, 3);

        // Auto login handler
        add_action('login_form', array($this, 'auto_login_form_fields'));

        // Background overlay
        add_action('login_header', array($this, 'add_background_overlay'));

        // Hide elements
        add_action('login_head', array($this, 'hide_login_elements'));
    }

    /**
     * Check if customizer is enabled
     * @return bool
     */
    private function is_enabled() {
        return (bool) $this->settings->get('general.enabled', true);
    }

    /**
     * Check if BizCity Twin AI plugin is active and modifying login page
     * @return bool
     */
    private function is_bizcity_login_active() {
        return class_exists('BizCity_Login_Page');
    }

    /**
     * Enqueue custom login styles
     */
    public function enqueue_login_styles() {
        if (!$this->is_enabled()) return;

        // Inline dynamic CSS
        $css = $this->generate_login_css();
        if (!empty($css)) {
            echo '<style id="tgs-login-customizer-css">' . "\n" . $css . "\n" . '</style>' . "\n";
        }
    }

    /**
     * Enqueue login scripts
     */
    public function enqueue_login_scripts() {
        if (!$this->is_enabled()) return;

        $auto_login = $this->settings->get('auto_login', array());

        if (!empty($auto_login['enabled'])) {
            wp_enqueue_script('jquery');

            $script_data = array(
                'prefill_only' => !empty($auto_login['prefill_only']),
                'auto_submit_delay' => intval($auto_login['auto_submit_delay'] ?? 3),
                'show_countdown' => !empty($auto_login['show_countdown']),
                'username' => $auto_login['username'] ?? '',
                'password' => $this->settings->get_auto_login_password(),
            );

            echo '<script id="tgs-login-customizer-data">' . "\n";
            echo 'var tgsLoginData = ' . wp_json_encode($script_data) . ';' . "\n";
            echo '</script>' . "\n";
        }

        // Auto login JS
        echo '<script id="tgs-login-customizer-js">' . "\n";
        echo $this->get_login_js();
        echo "\n" . '</script>' . "\n";

        // Custom JS
        $custom_js = $this->settings->get('custom_js', '');
        if (!empty($custom_js)) {
            echo '<script id="tgs-login-custom-js">' . "\n" . $custom_js . "\n" . '</script>' . "\n";
        }
    }

    /**
     * Login head custom additions
     */
    public function login_head_custom() {
        if (!$this->is_enabled()) return;

        // Google Fonts if custom font family is set
        $font_family = $this->settings->get('layout.page_font_family', '');
        if (!empty($font_family)) {
            $font_name = str_replace(' ', '+', $font_family);
            echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
            echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
            echo '<link href="https://fonts.googleapis.com/css2?family=' . esc_attr($font_name) . ':wght@300;400;500;600;700&display=swap" rel="stylesheet">' . "\n";
        }
    }

    /**
     * Custom login logo URL
     */
    public function login_logo_url($url) {
        if (!$this->is_enabled()) return $url;

        $custom_url = $this->settings->get('logo.link_url', '');
        return !empty($custom_url) ? esc_url($custom_url) : home_url('/');
    }

    /**
     * Custom login logo title
     */
    public function login_logo_title($title) {
        if (!$this->is_enabled()) return $title;

        $custom_title = $this->settings->get('logo.link_title', '');
        return !empty($custom_title) ? esc_html($custom_title) : get_bloginfo('name');
    }

    /**
     * Custom message above login form
     */
    public function login_custom_message($message) {
        if (!$this->is_enabled()) return $message;

        $title = $this->settings->get('text.login_title', '');
        $subtitle = $this->settings->get('text.login_subtitle', '');
        $auto_login = $this->settings->get('auto_login', array());
        $bizcity = $this->is_bizcity_login_active();

        $html = '';

        // Custom title — skip when BizCity active (it has its own welcome text)
        if (!empty($title) && !$bizcity) {
            $title_color = $this->settings->get('text.title_color', '#333333');
            $title_size = $this->settings->get('text.title_font_size', '22');
            $html .= '<h2 class="tgs-login-title" style="text-align:center;color:' . esc_attr($title_color) . ';font-size:' . esc_attr($title_size) . 'px;margin:0 0 8px 0;font-weight:600;">' . esc_html($title) . '</h2>';
        }

        // Custom subtitle — skip when BizCity active
        if (!empty($subtitle) && !$bizcity) {
            $sub_color = $this->settings->get('text.subtitle_color', '#666666');
            $sub_size = $this->settings->get('text.subtitle_font_size', '14');
            $html .= '<p class="tgs-login-subtitle" style="text-align:center;color:' . esc_attr($sub_color) . ';font-size:' . esc_attr($sub_size) . 'px;margin:0 0 15px 0;">' . esc_html($subtitle) . '</p>';
        }

        // Show credentials info
        if (!empty($auto_login['enabled']) && !empty($auto_login['show_credentials'])) {
            $username = $auto_login['username'] ?? '';
            $password = $this->settings->get_auto_login_password();
            if (!empty($username)) {
                $html .= '<div class="tgs-login-credentials" style="background:#f0f6fc;border:1px solid #c3c4c7;border-radius:4px;padding:12px 16px;margin:0 0 16px 0;text-align:center;">';
                $html .= '<p style="margin:0 0 5px 0;font-size:13px;color:#50575e;">🔑 <strong>Tài khoản demo:</strong></p>';
                $html .= '<p style="margin:0;font-size:13px;color:#1d2327;"><strong>User:</strong> <code>' . esc_html($username) . '</code>';
                if (!empty($password)) {
                    $html .= ' &nbsp;|&nbsp; <strong>Pass:</strong> <code>' . esc_html($password) . '</code>';
                }
                $html .= '</p>';
                $html .= '</div>';
            }
        }

        return $html . $message;
    }

    /**
     * Add auto-login countdown and form fields
     */
    public function auto_login_form_fields() {
        if (!$this->is_enabled()) return;

        $auto_login = $this->settings->get('auto_login', array());

        if (empty($auto_login['enabled'])) return;

        // Countdown display
        if (!empty($auto_login['show_countdown']) && empty($auto_login['prefill_only'])) {
            $delay = intval($auto_login['auto_submit_delay'] ?? 3);
            if ($delay > 0) {
                echo '<div id="tgs-auto-login-countdown" style="text-align:center;margin:10px 0;padding:8px;background:#dff0d8;border:1px solid #3c763d;border-radius:4px;color:#3c763d;font-size:13px;">';
                echo '⏱ Tự động đăng nhập sau <strong><span id="tgs-countdown-number">' . $delay . '</span></strong> giây... ';
                echo '<a href="#" id="tgs-cancel-auto-login" style="color:#d63638;text-decoration:underline;">Hủy</a>';
                echo '</div>';
            }
        }
    }

    /**
     * Footer content
     */
    public function login_footer_content() {
        if (!$this->is_enabled()) return;

        // BizCity has its own footer — skip to avoid duplicate
        if ($this->is_bizcity_login_active()) return;

        $footer_text = $this->settings->get('text.footer_text', '');
        if (!empty($footer_text)) {
            $footer_color = $this->settings->get('text.footer_color', '#999999');
            $footer_size = $this->settings->get('text.footer_font_size', '12');
            echo '<div class="tgs-login-footer" style="text-align:center;padding:20px 0;color:' . esc_attr($footer_color) . ';font-size:' . esc_attr($footer_size) . 'px;">';
            echo wp_kses_post($footer_text);
            echo '</div>';
        }
    }

    /**
     * Modify "Remember Me" checkbox
     */
    public function modify_remember_me() {
        if (!$this->is_enabled()) return;

        $checked = $this->settings->get('remember_me.checked_by_default', true);
        if ($checked) {
            echo '<script>document.addEventListener("DOMContentLoaded",function(){var r=document.getElementById("rememberme");if(r)r.checked=true;});</script>';
        }
    }

    /**
     * Custom redirect after login
     */
    public function custom_login_redirect($redirect_to, $requested_redirect_to, $user) {
        if (!$this->is_enabled()) return $redirect_to;
        if (is_wp_error($user)) return $redirect_to;

        $priority = $this->settings->get('general.redirect_priority', 'requested_url');
        $custom_url = $this->settings->get('general.default_redirect_url', '');

        // If priority is requested_url and user came from a specific page, use that
        if ($priority === 'requested_url' && !empty($requested_redirect_to) && $requested_redirect_to !== admin_url()) {
            return $requested_redirect_to;
        }

        // If custom URL is set, use it
        if (!empty($custom_url)) {
            return $custom_url;
        }

        // Fallback to redirect_after_login
        $redirect_after = $this->settings->get('general.redirect_after_login', '');
        if (!empty($redirect_after)) {
            return $redirect_after;
        }

        return $redirect_to;
    }

    /**
     * Add background overlay div
     */
    public function add_background_overlay() {
        if (!$this->is_enabled()) return;

        // BizCity handles its own background — skip overlay
        if ($this->is_bizcity_login_active()) return;

        $overlay = $this->settings->get('background.overlay_enabled', false);
        if ($overlay) {
            $overlay_color = $this->settings->get('background.overlay_color', 'rgba(0,0,0,0.3)');
            echo '<div id="tgs-login-overlay" style="position:fixed;top:0;left:0;right:0;bottom:0;background:' . esc_attr($overlay_color) . ';z-index:0;pointer-events:none;"></div>';
        }
    }

    /**
     * Hide login elements via CSS
     */
    public function hide_login_elements() {
        if (!$this->is_enabled()) return;

        $hide_css = '';

        if ($this->settings->get('links.hide_back_to_blog', false)) {
            $hide_css .= '#backtoblog { display: none !important; }' . "\n";
        }

        if ($this->settings->get('links.hide_lost_password', false)) {
            $hide_css .= '#nav a[href*="lostpassword"] { display: none !important; }' . "\n";
            $hide_css .= '.login #nav { text-align: center; }' . "\n";
        }

        if ($this->settings->get('links.hide_register', false)) {
            $hide_css .= '#nav a[href*="register"] { display: none !important; }' . "\n";
        }

        if ($this->settings->get('remember_me.hide', false)) {
            $hide_css .= '.forgetmenot { display: none !important; }' . "\n";
        }

        if (!empty($hide_css)) {
            echo '<style>' . $hide_css . '</style>';
        }
    }

    /**
     * Generate complete login CSS from settings
     * 
     * @return string
     */
    private function generate_login_css() {
        $s = $this->settings;

        // BizCity Twin AI compatibility: return adapted CSS
        if ($this->is_bizcity_login_active()) {
            return $this->generate_bizcity_compat_css();
        }

        $css = '';

        // === FONT FAMILY ===
        $font = $s->get('layout.page_font_family', '');
        if (!empty($font)) {
            $css .= "body.login, .login form, .login label, .login input, .login #nav, .login #backtoblog {\n";
            $css .= "  font-family: '{$font}', sans-serif !important;\n";
            $css .= "}\n";
        }

        // === BACKGROUND ===
        $bg_type = $s->get('background.type', 'color');
        $css .= "body.login {\n";

        if ($bg_type === 'image') {
            $bg_image = $s->get('background.image_url', '');
            if (!empty($bg_image)) {
                $css .= "  background-image: url('" . esc_url($bg_image) . "') !important;\n";
                $css .= "  background-size: " . esc_attr($s->get('background.image_size', 'cover')) . " !important;\n";
                $css .= "  background-position: " . esc_attr($s->get('background.image_position', 'center center')) . " !important;\n";
                $css .= "  background-repeat: " . esc_attr($s->get('background.image_repeat', 'no-repeat')) . " !important;\n";
                $css .= "  background-attachment: " . esc_attr($s->get('background.image_attachment', 'fixed')) . " !important;\n";
            }
        } elseif ($bg_type === 'gradient') {
            $grad_start = $s->get('background.gradient_start', '#667eea');
            $grad_end = $s->get('background.gradient_end', '#764ba2');
            $grad_dir = $s->get('background.gradient_direction', '135deg');
            $css .= "  background: linear-gradient({$grad_dir}, {$grad_start}, {$grad_end}) !important;\n";
        } else {
            $bg_color = $s->get('background.color', '#f0f0f1');
            $css .= "  background-color: {$bg_color} !important;\n";
        }

        $css .= "}\n";

        // === LOGO ===
        $logo_url = $s->get('logo.image_url', '');
        $logo_w = $s->get('logo.width', '320');
        $logo_h = $s->get('logo.height', '120');
        $logo_radius = $s->get('logo.border_radius', '0');
        $logo_margin = $s->get('logo.margin_bottom', '25');

        $css .= "#login h1 a, .login h1 a {\n";
        if (!empty($logo_url)) {
            $css .= "  background-image: url('" . esc_url($logo_url) . "') !important;\n";
        }
        $css .= "  width: {$logo_w}px !important;\n";
        $css .= "  height: {$logo_h}px !important;\n";
        $css .= "  background-size: contain !important;\n";
        $css .= "  background-repeat: no-repeat !important;\n";
        $css .= "  background-position: center !important;\n";
        $css .= "  border-radius: {$logo_radius}px !important;\n";
        $css .= "  margin-bottom: {$logo_margin}px !important;\n";
        $css .= "  max-width: 100% !important;\n";
        $css .= "}\n";

        // === FORM BOX ===
        $form_bg = $s->get('form.background_color', '#ffffff');
        $form_radius = $s->get('form.border_radius', '8');
        $form_border_color = $s->get('form.border_color', '#dddddd');
        $form_border_w = $s->get('form.border_width', '1');
        $form_shadow = $s->get('form.box_shadow', '0 4px 20px rgba(0,0,0,0.1)');
        $form_padding = $s->get('form.padding', '26');
        $form_width = $s->get('form.width', '350');

        $css .= ".login form {\n";
        $css .= "  background: {$form_bg} !important;\n";
        $css .= "  border-radius: {$form_radius}px !important;\n";
        $css .= "  border: {$form_border_w}px solid {$form_border_color} !important;\n";
        $css .= "  box-shadow: {$form_shadow} !important;\n";
        $css .= "  padding: {$form_padding}px !important;\n";
        $css .= "}\n";

        $css .= "#login {\n";
        $css .= "  width: {$form_width}px !important;\n";
        $css .= "  max-width: " . esc_attr($s->get('form.max_width', '100%')) . " !important;\n";
        $css .= "  padding: 5% 0 0 !important;\n";
        $css .= "}\n";

        // === FORM POSITION ===
        $form_pos = $s->get('layout.form_position', 'center');
        $form_v_pos = $s->get('layout.form_vertical_position', 'center');

        if ($form_pos !== 'center' || $form_v_pos !== 'top') {
            $css .= "body.login {\n";
            $css .= "  display: flex !important;\n";

            if ($form_pos === 'left') {
                $css .= "  justify-content: flex-start !important;\n";
            } elseif ($form_pos === 'right') {
                $css .= "  justify-content: flex-end !important;\n";
            } else {
                $css .= "  justify-content: center !important;\n";
            }

            if ($form_v_pos === 'center') {
                $css .= "  align-items: center !important;\n";
                $css .= "  min-height: 100vh !important;\n";
            } elseif ($form_v_pos === 'bottom') {
                $css .= "  align-items: flex-end !important;\n";
                $css .= "  min-height: 100vh !important;\n";
            }

            $css .= "  flex-direction: column !important;\n";
            $css .= "}\n";

            if ($form_pos === 'left' || $form_pos === 'right') {
                $pad_side = ($form_pos === 'left') ? 'left' : 'right';
                $css .= "#login {\n";
                $css .= "  margin-{$pad_side}: 80px !important;\n";
                $css .= "}\n";
            }
        }

        // === INPUT FIELDS ===
        $css .= ".login form .input, .login input[type='text'], .login input[type='password'], .login input[type='email'] {\n";
        $css .= "  background-color: " . esc_attr($s->get('input.background_color', '#ffffff')) . " !important;\n";
        $css .= "  color: " . esc_attr($s->get('input.text_color', '#333333')) . " !important;\n";
        $css .= "  border-color: " . esc_attr($s->get('input.border_color', '#dddddd')) . " !important;\n";
        $css .= "  border-radius: " . esc_attr($s->get('input.border_radius', '4')) . "px !important;\n";
        $css .= "  border-width: " . esc_attr($s->get('input.border_width', '1')) . "px !important;\n";
        $css .= "  font-size: " . esc_attr($s->get('input.font_size', '14')) . "px !important;\n";
        $css .= "  padding: " . esc_attr($s->get('input.padding', '8')) . "px !important;\n";
        $css .= "}\n";

        $css .= ".login form .input:focus, .login input[type='text']:focus, .login input[type='password']:focus, .login input[type='email']:focus {\n";
        $css .= "  border-color: " . esc_attr($s->get('input.focus_border_color', '#2271b1')) . " !important;\n";
        $css .= "  box-shadow: 0 0 0 1px " . esc_attr($s->get('input.focus_border_color', '#2271b1')) . " !important;\n";
        $css .= "}\n";

        $css .= ".login form .input::placeholder {\n";
        $css .= "  color: " . esc_attr($s->get('input.placeholder_color', '#a0a5aa')) . " !important;\n";
        $css .= "}\n";

        // === LABELS ===
        $css .= ".login label {\n";
        $css .= "  color: " . esc_attr($s->get('label.text_color', '#1e1e1e')) . " !important;\n";
        $css .= "  font-size: " . esc_attr($s->get('label.font_size', '14')) . "px !important;\n";
        $css .= "  font-weight: " . esc_attr($s->get('label.font_weight', '600')) . " !important;\n";
        $css .= "}\n";

        // === BUTTON ===
        $css .= ".wp-core-ui .button-primary, .login .button-primary {\n";
        $css .= "  background-color: " . esc_attr($s->get('button.background_color', '#2271b1')) . " !important;\n";
        $css .= "  color: " . esc_attr($s->get('button.text_color', '#ffffff')) . " !important;\n";
        $css .= "  border-color: " . esc_attr($s->get('button.border_color', '#2271b1')) . " !important;\n";
        $css .= "  border-radius: " . esc_attr($s->get('button.border_radius', '4')) . "px !important;\n";
        $css .= "  font-size: " . esc_attr($s->get('button.font_size', '14')) . "px !important;\n";
        $css .= "  font-weight: " . esc_attr($s->get('button.font_weight', '600')) . " !important;\n";
        $css .= "  padding: " . esc_attr($s->get('button.padding', '8px 20px')) . " !important;\n";
        $css .= "  text-transform: " . esc_attr($s->get('button.text_transform', 'none')) . " !important;\n";

        $btn_width = $s->get('button.width', 'auto');
        if ($btn_width === '100%') {
            $css .= "  width: 100% !important;\n";
            $css .= "  text-align: center !important;\n";
        }

        $css .= "}\n";

        $css .= ".wp-core-ui .button-primary:hover, .login .button-primary:hover, .wp-core-ui .button-primary:focus, .login .button-primary:focus {\n";
        $css .= "  background-color: " . esc_attr($s->get('button.hover_background_color', '#135e96')) . " !important;\n";
        $css .= "  color: " . esc_attr($s->get('button.hover_text_color', '#ffffff')) . " !important;\n";
        $css .= "}\n";

        // === LINKS ===
        $css .= ".login #nav a, .login #backtoblog a {\n";
        $css .= "  color: " . esc_attr($s->get('links.text_color', '#50575e')) . " !important;\n";
        $css .= "  font-size: " . esc_attr($s->get('links.font_size', '13')) . "px !important;\n";
        $css .= "}\n";

        $css .= ".login #nav a:hover, .login #backtoblog a:hover {\n";
        $css .= "  color: " . esc_attr($s->get('links.hover_color', '#135e96')) . " !important;\n";
        $css .= "}\n";

        // === ERROR MESSAGES ===
        $css .= ".login .message, .login #login_error {\n";
        $css .= "  border-radius: 4px !important;\n";
        $css .= "}\n";

        $css .= ".login #login_error {\n";
        $css .= "  background-color: " . esc_attr($s->get('error.background_color', '#fcf0f1')) . " !important;\n";
        $css .= "  border-left-color: " . esc_attr($s->get('error.border_color', '#d63638')) . " !important;\n";
        $css .= "  color: " . esc_attr($s->get('error.text_color', '#d63638')) . " !important;\n";
        $css .= "}\n";

        // === CUSTOM CSS ===
        $custom_css = $s->get('custom_css', '');
        if (!empty($custom_css)) {
            $css .= "\n/* Custom CSS */\n" . $custom_css . "\n";
        }

        return $css;
    }

    /**
     * Generate CSS compatible with BizCity Twin AI login layout
     * 
     * BizCity uses a two-column layout (form left, hero image right).
     * This method outputs CSS that works WITHIN that layout instead of
     * conflicting with it. Only colors, fonts, and minor tweaks are applied.
     * 
     * @return string
     */
    private function generate_bizcity_compat_css() {
        $s = $this->settings;
        $css = "/* TGS Login Customizer — BizCity Twin AI Compatibility Mode */\n\n";

        // ── Font family (non-conflicting, enhances) ──
        $font = $s->get('layout.page_font_family', '');
        if (!empty($font)) {
            $css .= "body.login, .login form, .login label, .login input,\n";
            $css .= ".aiquill-welcome h2, .aiquill-welcome p, .aiquill-footer {\n";
            $css .= "  font-family: '{$font}', sans-serif !important;\n";
            $css .= "}\n\n";
        }

        // ── Input field focus color ──
        $css .= "#loginform input[type='text']:focus,\n";
        $css .= "#loginform input[type='password']:focus,\n";
        $css .= "#loginform input[type='email']:focus {\n";
        $css .= "  border-color: " . esc_attr($s->get('input.focus_border_color', 'rgb(77,107,254)')) . " !important;\n";
        $css .= "  box-shadow: 0 0 0 1px " . esc_attr($s->get('input.focus_border_color', 'rgb(77,107,254)')) . " !important;\n";
        $css .= "}\n\n";

        // ── Labels & input text: let BizCity dark mode handle colors ──
        // Only set font-size/weight, NOT color (so dark mode works)
        $css .= ".login label {\n";
        $css .= "  font-size: " . esc_attr($s->get('label.font_size', '14')) . "px !important;\n";
        $css .= "  font-weight: " . esc_attr($s->get('label.font_weight', '500')) . " !important;\n";
        $css .= "}\n\n";

        // ── Button colors (keep BizCity pill shape + full-width) ──
        $css .= "#wp-submit,\n";
        $css .= ".login .button-primary {\n";
        $css .= "  background: " . esc_attr($s->get('button.background_color', 'rgb(77,107,254)')) . " !important;\n";
        $css .= "  color: " . esc_attr($s->get('button.text_color', '#ffffff')) . " !important;\n";
        $css .= "  font-size: " . esc_attr($s->get('button.font_size', '14')) . "px !important;\n";
        $css .= "  font-weight: " . esc_attr($s->get('button.font_weight', '500')) . " !important;\n";
        $css .= "  text-transform: " . esc_attr($s->get('button.text_transform', 'none')) . " !important;\n";
        $css .= "}\n";

        $css .= "#wp-submit:hover,\n";
        $css .= ".login .button-primary:hover {\n";
        $css .= "  background: " . esc_attr($s->get('button.hover_background_color', 'rgb(77,107,254)')) . " !important;\n";
        $css .= "  color: " . esc_attr($s->get('button.hover_text_color', '#ffffff')) . " !important;\n";
        $css .= "  opacity: 0.9;\n";
        $css .= "}\n\n";

        // ── Error messages (use BizCity rounded style, allow color customization) ──
        $css .= ".login #login_error {\n";
        $css .= "  background: " . esc_attr($s->get('error.background_color', 'rgba(255,86,48,.08)')) . " !important;\n";
        $css .= "  color: " . esc_attr($s->get('error.text_color', 'rgb(255,86,48)')) . " !important;\n";
        $css .= "}\n\n";

        // ── TGS injected elements — styled to fit BizCity layout ──
        $css .= ".tgs-login-credentials {\n";
        $css .= "  max-width: 480px;\n";
        $css .= "  border-radius: 12px !important;\n";
        $css .= "  border: 1px solid #EBECED !important;\n";
        $css .= "  background: rgba(77,107,254,.05) !important;\n";
        $css .= "  margin: 12px 0 !important;\n";
        $css .= "}\n\n";

        $css .= "#tgs-auto-login-countdown {\n";
        $css .= "  max-width: 480px;\n";
        $css .= "  border-radius: 12px !important;\n";
        $css .= "}\n\n";

        // ── Custom CSS (always applied last) ──
        $custom_css = $s->get('custom_css', '');
        if (!empty($custom_css)) {
            $css .= "\n/* Custom CSS */\n" . $custom_css . "\n";
        }

        return $css;
    }

    /**
     * Get auto login JavaScript
     * 
     * @return string
     */
    private function get_login_js() {
        return <<<'JS'
(function() {
    'use strict';

    if (typeof tgsLoginData === 'undefined') return;

    document.addEventListener('DOMContentLoaded', function() {
        var userField = document.getElementById('user_login');
        var passField = document.getElementById('user_pass');
        var loginForm = document.getElementById('loginform');

        if (!userField || !passField || !loginForm) return;

        // Pre-fill credentials
        if (tgsLoginData.username) {
            userField.value = tgsLoginData.username;
        }
        if (tgsLoginData.password) {
            passField.value = tgsLoginData.password;
        }

        // If prefill only, stop here
        if (tgsLoginData.prefill_only) return;

        // Auto submit
        var delay = tgsLoginData.auto_submit_delay || 0;
        var countdownEl = document.getElementById('tgs-countdown-number');
        var cancelBtn = document.getElementById('tgs-cancel-auto-login');
        var cancelled = false;

        if (cancelBtn) {
            cancelBtn.addEventListener('click', function(e) {
                e.preventDefault();
                cancelled = true;
                var countdownDiv = document.getElementById('tgs-auto-login-countdown');
                if (countdownDiv) {
                    countdownDiv.innerHTML = '✋ Đã hủy tự động đăng nhập.';
                    countdownDiv.style.background = '#fcf0f1';
                    countdownDiv.style.borderColor = '#d63638';
                    countdownDiv.style.color = '#d63638';
                }
            });
        }

        if (delay > 0 && tgsLoginData.show_countdown) {
            var remaining = delay;
            var interval = setInterval(function() {
                if (cancelled) {
                    clearInterval(interval);
                    return;
                }
                remaining--;
                if (countdownEl) countdownEl.textContent = remaining;
                if (remaining <= 0) {
                    clearInterval(interval);
                    loginForm.submit();
                }
            }, 1000);
        } else if (delay === 0) {
            // Submit immediately
            setTimeout(function() {
                if (!cancelled) loginForm.submit();
            }, 500);
        }
    });
})();
JS;
    }
}

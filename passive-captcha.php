<?php
/*
Plugin Name: Passive CAPTCHA for Gravity Forms
Description: Adds a passive CAPTCHA mechanism to Gravity Forms.
Version:     3.6.0
Author:      Rich Hamilton
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// -----------------------------------------------------------------------------
// 1) Settings Page
// -----------------------------------------------------------------------------

require_once plugin_dir_path( __FILE__ ) . 'includes/settings-page.php';

add_action( 'admin_init', 'pch_register_settings' );
function pch_register_settings() {
    register_setting( 'pch_settings_group', 'pch_settings' );
}

add_action( 'admin_menu', 'pch_add_settings_page' );
function pch_add_settings_page() {
    add_options_page(
        'Passive CAPTCHA Settings',
        'Passive CAPTCHA',
        'manage_options',
        'pch-settings',
        'pch_render_settings_page'
    );
}


// -----------------------------------------------------------------------------
// 2) Enqueue our ES-module bundle
// -----------------------------------------------------------------------------

add_action( 'wp_enqueue_scripts', 'pch_enqueue_passive_captcha_script' );
function pch_enqueue_passive_captcha_script() {
    $script_path = 'js/index.js';
    $full_path   = plugin_dir_path( __FILE__ ) . $script_path;
    $url_base    = plugin_dir_url( __FILE__ );

    if ( file_exists( $full_path ) ) {
        wp_register_script(
            'passive-captcha',
            $url_base . $script_path,
            [],           // no deps
            '3.6.0',
            true          // in footer
        );
        wp_enqueue_script( 'passive-captcha' );

        wp_localize_script( 'passive-captcha', 'pchData', [
            'debug'          => (bool) ( get_option( 'pch_settings' )['enable_debug'] ?? false ),
            'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
            'ja3Fingerprint' => isset( $_SERVER['HTTP_X_JA3_FINGERPRINT'] )
                                  ? sanitize_text_field( $_SERVER['HTTP_X_JA3_FINGERPRINT'] )
                                  : '',
            // legacy fields for backward compatibility
            'nonce'          => wp_create_nonce( 'pch_nonce' ),
            'sessionToken'   => hash( 'sha256', uniqid( '', true ) ),
            'ipHash'         => hash( 'sha1', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1' ),
            'enableWebGL'    => '1',
            'enableMath'     => '1',
        ] );
    } else {
        // if index.js is missing, warning in the console
        add_action( 'wp_footer', function() {
            echo "<script>console.warn('PCH WARN: js/index.js not found — please verify plugin folder.');</script>";
        } );
    }
}


// -----------------------------------------------------------------------------
// 3) Post-footer injection & fallback
// -----------------------------------------------------------------------------

add_action( 'wp_footer', 'pch_inject_post_footer_check', 100 );
function pch_inject_post_footer_check() {
    ?>
    <script>
    // if our module never sets this, we know it didn't load
    if ( typeof window.pchScriptLoaded === 'undefined' ) {
        console.warn("PCH WARN: passive-captcha module failed to load.");
        // fire off an AJAX warning
        fetch('<?php echo esc_url( admin_url( 'admin-ajax.php?action=pch_log_warning' ) ); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                level: 'warn',
                message: 'js/index.js did not load'
            })
        });
    }
    </script>
    <noscript>
        <div style="background: #fee; color: #900; padding: 1rem; text-align: center;">
            JavaScript is disabled or passive-captcha failed to load. Bot protection is degraded.
        </div>
    </noscript>
    <?php
}

// fallback if wp_footer hook never fired
add_action( 'shutdown', 'pch_fallback_footer_injection' );
function pch_fallback_footer_injection() {
    if ( ! did_action( 'wp_footer' ) ) {
        if ( function_exists( 'ob_get_level' ) && ob_get_level() ) {
            @ob_end_flush();
        }
        echo "\n<!-- Passive CAPTCHA fallback -->\n";
        echo "<script>\n";
        echo "if (typeof window.pchScriptLoaded === 'undefined') {\n";
        echo "  console.warn('PCH WARN: passive-captcha missing, fallback injection triggered.');\n";
        echo "  fetch('" . esc_url( admin_url( 'admin-ajax.php?action=pch_log_warning' ) ) . "', {\n";
        echo "    method: 'POST',\n";
        echo "    headers: { 'Content-Type': 'application/json' },\n";
        echo "    body: JSON.stringify({ level: 'warn', message: 'fallback injection' })\n";
        echo "  });\n";
        echo "}\n";
        echo "</script>\n";
    }
}


// -----------------------------------------------------------------------------
// 4) Admin notice if theme is missing footer.php
// -----------------------------------------------------------------------------

add_action( 'admin_notices', 'pch_warn_missing_footer_template' );
function pch_warn_missing_footer_template() {
    $theme       = wp_get_theme();
    $footer_file = get_theme_file_path( 'footer.php' );

    if ( ! file_exists( $footer_file ) ) {
        printf(
            '<div class="notice notice-error"><p><strong>Passive CAPTCHA:</strong> Current theme (<code>%s</code>) does not include <code>footer.php</code>. Please ensure <code>wp_footer()</code> is present.</p></div>',
            esc_html( $theme->get( 'Name' ) )
        );
    }
}


// -----------------------------------------------------------------------------
// 5) AJAX endpoint for JS-side warnings
// -----------------------------------------------------------------------------

add_action( 'wp_ajax_pch_log_warning', 'pch_log_warning' );
add_action( 'wp_ajax_nopriv_pch_log_warning', 'pch_log_warning' );
function pch_log_warning() {
    $data = json_decode( file_get_contents( 'php://input' ), true );
    if ( ! empty( $data['message'] ) ) {
        error_log( '[Passive CAPTCHA] ' . sanitize_text_field( $data['message'] ) );
    }
    wp_send_json_success();
}

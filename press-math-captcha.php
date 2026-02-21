<?php
/**
 * Plugin Name: Press Math Captcha
 * Description: Adds a lightweight math CAPTCHA to WordPress login, Contact Form 7, and WooCommerce forms.
 * Version: 1.0.0
 * Author: Press Math Captcha
 * Text Domain: press-math-captcha
 * Domain Path: /languages
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PMC_VERSION', '1.0.0' );
define( 'PMC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PMC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PMC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once PMC_PLUGIN_DIR . 'includes/class-pmc-loader.php';
require_once PMC_PLUGIN_DIR . 'includes/class-pmc-captcha.php';
require_once PMC_PLUGIN_DIR . 'includes/class-pmc-admin.php';
require_once PMC_PLUGIN_DIR . 'includes/class-pmc-login.php';
require_once PMC_PLUGIN_DIR . 'includes/class-pmc-cf7.php';
require_once PMC_PLUGIN_DIR . 'includes/class-pmc-woocommerce.php';
require_once PMC_PLUGIN_DIR . 'includes/class-pmc-security.php';

function pmc_activate_plugin() {
    $defaults = array(
        'enable_login'       => 1,
        'enable_cf7'         => 1,
        'enable_woocommerce' => 1,
        'difficulty'         => 'easy',
        'operation'          => 'random',
        'hide_logged_in'     => 1,
        'error_message'      => __( 'Incorrect captcha answer. Please try again.', 'press-math-captcha' ),
        'enable_rate_limit'  => 1,
        'max_attempts'       => 5,
        'block_duration'     => 15,
    );

    $current = get_option( 'pmc_settings', array() );
    $merged  = wp_parse_args( $current, $defaults );
    update_option( 'pmc_settings', $merged );
}
register_activation_hook( __FILE__, 'pmc_activate_plugin' );

function pmc_deactivate_plugin() {
    // Reserved for future cleanup tasks.
}
register_deactivation_hook( __FILE__, 'pmc_deactivate_plugin' );

function pmc_run() {
    $loader = new PMC_Loader();
    $loader->run();
}
pmc_run();

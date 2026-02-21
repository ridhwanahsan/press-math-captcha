<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PMC_Loader {
    private $actions;
    private $filters;

    public function __construct() {
        $this->actions = array();
        $this->filters = array();

        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->actions[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args,
        );
    }

    public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->filters[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args,
        );
    }

    public function run() {
        foreach ( $this->filters as $hook ) {
            add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
        }

        foreach ( $this->actions as $hook ) {
            add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
        }
    }

    private function define_admin_hooks() {
        $admin = new PMC_Admin();
        $this->add_action( 'admin_menu', $admin, 'add_menu' );
        $this->add_action( 'admin_init', $admin, 'register_settings' );
        $this->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_assets' );
        $this->add_action( 'wp_login', $admin, 'log_login', 10, 2 );
        $this->add_action( 'wp_login_failed', $admin, 'log_failed_login', 10, 1 );
    }

    private function define_public_hooks() {
        $settings = get_option( 'pmc_settings', array() );

        $security = new PMC_Security( $settings );

        if ( ! empty( $settings['enable_login'] ) ) {
            $login = new PMC_Login( $settings, $security );
            $this->add_action( 'login_form', $login, 'render_captcha' );
            $this->add_filter( 'authenticate', $login, 'validate_captcha', 30, 3 );
        }

        if ( ! empty( $settings['enable_cf7'] ) && $this->is_cf7_active() ) {
            $cf7 = new PMC_CF7( $settings, $security );
            $this->add_action( 'wpcf7_init', $cf7, 'register_tag' );
            $this->add_filter( 'wpcf7_validate_mathcaptcha', $cf7, 'validate', 10, 2 );
            $this->add_filter( 'wpcf7_validate_mathcaptcha*', $cf7, 'validate', 10, 2 );
        }

        if ( ! empty( $settings['enable_woocommerce'] ) && $this->is_woocommerce_active() ) {
            $wc = new PMC_Woocommerce( $settings, $security );
            $this->add_action( 'woocommerce_login_form', $wc, 'render_login_captcha' );
            $this->add_action( 'woocommerce_register_form', $wc, 'render_register_captcha' );
            $this->add_filter( 'woocommerce_process_login_errors', $wc, 'validate_login', 10, 3 );
            $this->add_filter( 'woocommerce_registration_errors', $wc, 'validate_register', 10, 3 );
        }
    }

    private function is_cf7_active() {
        return class_exists( 'WPCF7' );
    }

    private function is_woocommerce_active() {
        return class_exists( 'WooCommerce' );
    }
}

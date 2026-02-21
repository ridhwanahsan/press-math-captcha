<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PMC_Security {
    private $settings;

    public function __construct( $settings = array() ) {
        $this->settings = $settings;
    }

    public function is_honeypot_filled() {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce is verified at form handlers before validation.
        if ( empty( $_POST['pmc_website_url'] ) ) {
            return false;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce is verified at form handlers before validation.
        $value = sanitize_text_field( wp_unslash( $_POST['pmc_website_url'] ) );
        return '' !== $value;
    }

    public function is_ip_blocked() {
        if ( empty( $this->settings['enable_rate_limit'] ) ) {
            return false;
        }

        $key = $this->get_attempts_key();
        $data = get_transient( $key );

        if ( empty( $data['blocked_until'] ) ) {
            return false;
        }

        return time() < (int) $data['blocked_until'];
    }

    public function record_failure() {
        if ( empty( $this->settings['enable_rate_limit'] ) ) {
            return;
        }

        $max_attempts   = ! empty( $this->settings['max_attempts'] ) ? (int) $this->settings['max_attempts'] : 5;
        $block_duration = ! empty( $this->settings['block_duration'] ) ? (int) $this->settings['block_duration'] : 15;

        $key  = $this->get_attempts_key();
        $data = get_transient( $key );

        if ( ! is_array( $data ) ) {
            $data = array( 'attempts' => 0, 'blocked_until' => 0 );
        }

        $data['attempts']++;
        if ( $data['attempts'] >= $max_attempts ) {
            $data['blocked_until'] = time() + ( $block_duration * MINUTE_IN_SECONDS );
        }

        set_transient( $key, $data, $block_duration * MINUTE_IN_SECONDS );
    }

    public function reset_attempts() {
        if ( empty( $this->settings['enable_rate_limit'] ) ) {
            return;
        }

        delete_transient( $this->get_attempts_key() );
    }

    public function get_block_message() {
        return __( 'Too many failed attempts. Please try again later.', 'press-math-captcha' );
    }

    private function get_attempts_key() {
        $ip     = $this->get_ip();
        $hash   = hash( 'sha256', $ip );
        return 'pmc_attempts_' . $hash;
    }

    private function get_ip() {
        $keys = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        );

        foreach ( $keys as $key ) {
            if ( ! empty( $_SERVER[ $key ] ) ) {
                $ip_list = explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) );
                return trim( $ip_list[0] );
            }
        }

        return '0.0.0.0';
    }
}

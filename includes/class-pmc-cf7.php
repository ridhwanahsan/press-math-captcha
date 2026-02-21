<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PMC_CF7 {
    private $settings;
    private $security;
    private $captcha;

    public function __construct( $settings, PMC_Security $security ) {
        $this->settings = $settings;
        $this->security = $security;
        $this->captcha  = new PMC_Captcha( $settings );
    }

    public function register_tag() {
        if ( function_exists( 'wpcf7_add_form_tag' ) ) {
            wpcf7_add_form_tag( array( 'mathcaptcha', 'mathcaptcha*' ), array( $this, 'render_tag' ), true );
        }
    }

    public function render_tag( $tag ) {
        if ( $this->should_hide() ) {
            return '';
        }

        if ( $this->security->is_ip_blocked() ) {
            return '<span class="wpcf7-not-valid-tip">' . esc_html( $this->security->get_block_message() ) . '</span>';
        }

        return wp_kses_post( $this->captcha->render_field( 'pmc_cf7_captcha' ) );
    }

    public function validate( $result, $tag ) {
        if ( $this->should_hide() ) {
            return $result;
        }

        $nonce = isset( $_POST['pmc_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['pmc_nonce'] ) ) : '';
        if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'pmc_captcha_nonce' ) ) {
            $result->invalidate( $tag, __( 'Invalid submission.', 'press-math-captcha' ) );
            return $result;
        }

        if ( $this->security->is_honeypot_filled() ) {
            $result->invalidate( $tag, __( 'Invalid submission.', 'press-math-captcha' ) );
            return $result;
        }

        if ( $this->security->is_ip_blocked() ) {
            $result->invalidate( $tag, $this->security->get_block_message() );
            return $result;
        }

        $answer = isset( $_POST['pmc_cf7_captcha'] ) ? sanitize_text_field( wp_unslash( $_POST['pmc_cf7_captcha'] ) ) : '';
        $token  = isset( $_POST['pmc_token'] ) ? sanitize_text_field( wp_unslash( $_POST['pmc_token'] ) ) : '';

        if ( ! $this->captcha->validate( $answer, $token ) ) {
            $this->security->record_failure();
            $result->invalidate( $tag, $this->get_error_message() );
            return $result;
        }

        $this->security->reset_attempts();
        return $result;
    }

    private function should_hide() {
        return ! empty( $this->settings['hide_logged_in'] ) && is_user_logged_in();
    }

    private function get_error_message() {
        if ( ! empty( $this->settings['error_message'] ) ) {
            return $this->settings['error_message'];
        }

        return __( 'Incorrect captcha answer. Please try again.', 'press-math-captcha' );
    }
}

<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PMC_Login {
    private $settings;
    private $security;
    private $captcha;

    public function __construct( $settings, PMC_Security $security ) {
        $this->settings = $settings;
        $this->security = $security;
        $this->captcha  = new PMC_Captcha( $settings );
    }

    public function render_captcha() {
        if ( $this->should_hide() ) {
            return;
        }

        if ( $this->security->is_ip_blocked() ) {
            echo '<p class="message" style="color:#b32d2e">' . esc_html( $this->security->get_block_message() ) . '</p>';
            return;
        }

        echo wp_kses_post( $this->captcha->render_field( 'pmc_login_captcha' ) );
    }

    public function validate_captcha( $user, $username, $password ) {
        if ( $this->should_hide() ) {
            return $user;
        }

        if ( $this->security->is_honeypot_filled() ) {
            return new WP_Error( 'pmc_honeypot', __( 'Invalid submission.', 'press-math-captcha' ) );
        }

        if ( $this->security->is_ip_blocked() ) {
            return new WP_Error( 'pmc_blocked', $this->security->get_block_message() );
        }

        $nonce = isset( $_POST['pmc_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['pmc_nonce'] ) ) : '';
        if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'pmc_captcha_nonce' ) ) {
            return new WP_Error( 'pmc_nonce', __( 'Invalid submission.', 'press-math-captcha' ) );
        }

        $answer = isset( $_POST['pmc_login_captcha'] ) ? sanitize_text_field( wp_unslash( $_POST['pmc_login_captcha'] ) ) : '';
        $token  = isset( $_POST['pmc_token'] ) ? sanitize_text_field( wp_unslash( $_POST['pmc_token'] ) ) : '';

        if ( ! $this->captcha->validate( $answer, $token ) ) {
            $this->security->record_failure();
            return new WP_Error( 'pmc_failed', $this->get_error_message() );
        }

        $this->security->reset_attempts();
        return $user;
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

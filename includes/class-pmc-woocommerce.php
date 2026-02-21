<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PMC_Woocommerce {
    private $settings;
    private $security;
    private $captcha;

    public function __construct( $settings, PMC_Security $security ) {
        $this->settings = $settings;
        $this->security = $security;
        $this->captcha  = new PMC_Captcha( $settings );
    }

    public function render_login_captcha() {
        if ( $this->should_hide() ) {
            return;
        }

        if ( $this->security->is_ip_blocked() ) {
            echo '<p class="form-row form-row-wide"><span class="woocommerce-error">' . esc_html( $this->security->get_block_message() ) . '</span></p>';
            return;
        }

        echo wp_kses_post( $this->captcha->render_field( 'pmc_wc_login_captcha' ) );
    }

    public function render_register_captcha() {
        if ( $this->should_hide() ) {
            return;
        }

        if ( $this->security->is_ip_blocked() ) {
            echo '<p class="form-row form-row-wide"><span class="woocommerce-error">' . esc_html( $this->security->get_block_message() ) . '</span></p>';
            return;
        }

        echo wp_kses_post( $this->captcha->render_field( 'pmc_wc_register_captcha' ) );
    }

    public function validate_login( $errors, $username, $password ) {
        if ( $this->should_hide() ) {
            return $errors;
        }

        if ( $this->security->is_honeypot_filled() ) {
            $errors->add( 'pmc_honeypot', __( 'Invalid submission.', 'press-math-captcha' ) );
            return $errors;
        }

        if ( $this->security->is_ip_blocked() ) {
            $errors->add( 'pmc_blocked', $this->security->get_block_message() );
            return $errors;
        }

        $nonce = isset( $_POST['pmc_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['pmc_nonce'] ) ) : '';
        if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'pmc_captcha_nonce' ) ) {
            $errors->add( 'pmc_nonce', __( 'Invalid submission.', 'press-math-captcha' ) );
            return $errors;
        }

        $answer = isset( $_POST['pmc_wc_login_captcha'] ) ? sanitize_text_field( wp_unslash( $_POST['pmc_wc_login_captcha'] ) ) : '';
        $token  = isset( $_POST['pmc_token'] ) ? sanitize_text_field( wp_unslash( $_POST['pmc_token'] ) ) : '';

        if ( ! $this->captcha->validate( $answer, $token ) ) {
            $this->security->record_failure();
            $errors->add( 'pmc_failed', $this->get_error_message() );
            return $errors;
        }

        $this->security->reset_attempts();
        return $errors;
    }

    public function validate_register( $errors, $username, $email ) {
        if ( $this->should_hide() ) {
            return $errors;
        }

        if ( $this->security->is_honeypot_filled() ) {
            $errors->add( 'pmc_honeypot', __( 'Invalid submission.', 'press-math-captcha' ) );
            return $errors;
        }

        if ( $this->security->is_ip_blocked() ) {
            $errors->add( 'pmc_blocked', $this->security->get_block_message() );
            return $errors;
        }

        $nonce = isset( $_POST['pmc_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['pmc_nonce'] ) ) : '';
        if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'pmc_captcha_nonce' ) ) {
            $errors->add( 'pmc_nonce', __( 'Invalid submission.', 'press-math-captcha' ) );
            return $errors;
        }

        $answer = isset( $_POST['pmc_wc_register_captcha'] ) ? sanitize_text_field( wp_unslash( $_POST['pmc_wc_register_captcha'] ) ) : '';
        $token  = isset( $_POST['pmc_token'] ) ? sanitize_text_field( wp_unslash( $_POST['pmc_token'] ) ) : '';

        if ( ! $this->captcha->validate( $answer, $token ) ) {
            $this->security->record_failure();
            $errors->add( 'pmc_failed', $this->get_error_message() );
            return $errors;
        }

        $this->security->reset_attempts();
        return $errors;
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

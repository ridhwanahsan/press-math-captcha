<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PMC_Captcha {
    private $settings;

    public function __construct( $settings = array() ) {
        $this->settings = $settings;
    }

    public function generate() {
        $range     = $this->get_difficulty_range();
        $operation = $this->get_operation();

        $a = wp_rand( $range['min'], $range['max'] );
        $b = wp_rand( $range['min'], $range['max'] );

        switch ( $operation ) {
            case 'subtraction':
                $question = sprintf( '%d - %d', $a, $b );
                $answer   = $a - $b;
                break;
            case 'multiplication':
                $question = sprintf( '%d × %d', $a, $b );
                $answer   = $a * $b;
                break;
            case 'addition':
            default:
                $question = sprintf( '%d + %d', $a, $b );
                $answer   = $a + $b;
                break;
        }

        return array(
            'question'  => $question,
            'answer'    => $answer,
            'operation' => $operation,
        );
    }

    public function render_field( $field_id ) {
        $problem = $this->generate();
        $token   = wp_generate_password( 32, false );
        $this->store_answer( $token, $problem['answer'] );

        ob_start();
        ?>
        <div class="pmc-captcha-field">
            <label for="<?php echo esc_attr( $field_id ); ?>">
                <?php echo esc_html( $problem['question'] ); ?>
            </label>
            <input type="number" name="<?php echo esc_attr( $field_id ); ?>" id="<?php echo esc_attr( $field_id ); ?>" value="" />
            <input type="hidden" name="pmc_token" value="<?php echo esc_attr( $token ); ?>" />
            <input type="text" name="pmc_website_url" value="" style="display:none" autocomplete="off" />
            <?php wp_nonce_field( 'pmc_captcha_nonce', 'pmc_nonce' ); ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public function validate( $user_answer, $token ) {
        $token       = is_scalar( $token ) ? (string) $token : '';
        $user_answer = is_scalar( $user_answer ) ? trim( (string) $user_answer ) : '';

        if ( '' === $token || '' === $user_answer ) {
            return false;
        }

        if ( empty( $_POST['pmc_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pmc_nonce'] ) ), 'pmc_captcha_nonce' ) ) {
            return false;
        }

        $stored_answer = $this->get_stored_answer( $token );
        if ( null === $stored_answer ) {
            return false;
        }

        return (int) $stored_answer === (int) $user_answer;
    }

    public function get_difficulty_range() {
        $difficulty = isset( $this->settings['difficulty'] ) ? $this->settings['difficulty'] : 'easy';

        switch ( $difficulty ) {
            case 'medium':
                return array( 'min' => 5, 'max' => 25 );
            case 'hard':
                return array( 'min' => 10, 'max' => 50 );
            case 'easy':
            default:
                return array( 'min' => 1, 'max' => 10 );
        }
    }

    public function get_operation() {
        $operation = isset( $this->settings['operation'] ) ? $this->settings['operation'] : 'random';
        if ( 'random' !== $operation ) {
            return $operation;
        }

        $options = array( 'addition', 'subtraction' );
        if ( 'medium' === ( isset( $this->settings['difficulty'] ) ? $this->settings['difficulty'] : 'easy' ) ) {
            $options[] = 'multiplication';
        }
        if ( 'hard' === ( isset( $this->settings['difficulty'] ) ? $this->settings['difficulty'] : 'easy' ) ) {
            $options[] = 'multiplication';
        }

        return $options[ array_rand( $options ) ];
    }

    public function store_answer( $token, $answer ) {
        set_transient( 'pmc_captcha_' . $token, (int) $answer, 5 * MINUTE_IN_SECONDS );
    }

    public function get_stored_answer( $token ) {
        $key    = 'pmc_captcha_' . $token;
        $answer = get_transient( $key );
        delete_transient( $key );

        if ( false === $answer ) {
            return null;
        }

        return $answer;
    }
}

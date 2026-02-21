<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PMC_Admin {
    private $page_hook;
    private $log_page_hook;
    private $failed_log_page_hook;

    public function add_menu() {
        $this->page_hook = add_menu_page(
            __( 'Press Math Captcha', 'press-math-captcha' ),
            __( 'Press Math Captcha', 'press-math-captcha' ),
            'manage_options',
            'press-math-captcha',
            array( $this, 'render_page' ),
            'dashicons-shield',
            81
        );

        $this->log_page_hook = add_submenu_page(
            'press-math-captcha',
            __( 'Logged User List', 'press-math-captcha' ),
            __( 'Logged User List', 'press-math-captcha' ),
            'manage_options',
            'press-math-captcha-logged-list',
            array( $this, 'render_logged_list' )
        );

        $this->failed_log_page_hook = add_submenu_page(
            'press-math-captcha',
            __( 'Failed Login Log', 'press-math-captcha' ),
            __( 'Failed Login Log', 'press-math-captcha' ),
            'manage_options',
            'press-math-captcha-failed-log',
            array( $this, 'render_failed_log' )
        );
    }

    public function register_settings() {
        register_setting( 'pmc_settings_group', 'pmc_settings', array( $this, 'sanitize_settings' ) );

        add_settings_section(
            'pmc_main_section',
            __( 'General Settings', 'press-math-captcha' ),
            '__return_false',
            'press-math-captcha'
        );

        $fields = array(
            'enable_login'       => __( 'Enable on WordPress Login', 'press-math-captcha' ),
            'enable_cf7'         => __( 'Enable on Contact Form 7', 'press-math-captcha' ),
            'enable_woocommerce' => __( 'Enable on WooCommerce', 'press-math-captcha' ),
            'hide_logged_in'     => __( 'Hide for Logged-in Users', 'press-math-captcha' ),
            'enable_rate_limit'  => __( 'Enable Rate Limiting', 'press-math-captcha' ),
        );

        foreach ( $fields as $key => $label ) {
            add_settings_field(
                $key,
                esc_html( $label ),
                array( $this, 'render_checkbox' ),
                'press-math-captcha',
                'pmc_main_section',
                array( 'key' => $key )
            );
        }

        add_settings_field(
            'difficulty',
            esc_html__( 'Difficulty', 'press-math-captcha' ),
            array( $this, 'render_select' ),
            'press-math-captcha',
            'pmc_main_section',
            array(
                'key'     => 'difficulty',
                'options' => array(
                    'easy'   => __( 'Easy', 'press-math-captcha' ),
                    'medium' => __( 'Medium', 'press-math-captcha' ),
                    'hard'   => __( 'Hard', 'press-math-captcha' ),
                ),
            )
        );

        add_settings_field(
            'operation',
            esc_html__( 'Operation', 'press-math-captcha' ),
            array( $this, 'render_select' ),
            'press-math-captcha',
            'pmc_main_section',
            array(
                'key'     => 'operation',
                'options' => array(
                    'random'        => __( 'Random', 'press-math-captcha' ),
                    'addition'      => __( 'Addition', 'press-math-captcha' ),
                    'subtraction'   => __( 'Subtraction', 'press-math-captcha' ),
                    'multiplication'=> __( 'Multiplication', 'press-math-captcha' ),
                ),
            )
        );

        add_settings_field(
            'error_message',
            esc_html__( 'Error Message', 'press-math-captcha' ),
            array( $this, 'render_text' ),
            'press-math-captcha',
            'pmc_main_section',
            array( 'key' => 'error_message' )
        );

        add_settings_field(
            'max_attempts',
            esc_html__( 'Max Attempts', 'press-math-captcha' ),
            array( $this, 'render_number' ),
            'press-math-captcha',
            'pmc_main_section',
            array( 'key' => 'max_attempts', 'min' => 1 )
        );

        add_settings_field(
            'block_duration',
            esc_html__( 'Block Duration (minutes)', 'press-math-captcha' ),
            array( $this, 'render_number' ),
            'press-math-captcha',
            'pmc_main_section',
            array( 'key' => 'block_duration', 'min' => 1 )
        );
    }

    public function enqueue_assets( $hook ) {
        if ( $hook !== $this->page_hook && $hook !== $this->log_page_hook && $hook !== $this->failed_log_page_hook ) {
            return;
        }

        wp_enqueue_style( 'pmc-admin', PMC_PLUGIN_URL . 'assets/css/admin.css', array(), PMC_VERSION );
        wp_enqueue_script( 'pmc-admin', PMC_PLUGIN_URL . 'assets/js/admin.js', array(), PMC_VERSION, true );
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        ?>
        <div class="wrap pmc-settings">
            <h1><?php echo esc_html__( 'Press Math Captcha', 'press-math-captcha' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'pmc_settings_group' );
                do_settings_sections( 'press-math-captcha' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function render_logged_list() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $cleared = false;
        if ( isset( $_POST['pmc_clear_login_logs'] ) ) {
            check_admin_referer( 'pmc_clear_login_logs_action', 'pmc_clear_login_logs_nonce' );
            delete_option( 'pmc_login_logs' );
            $cleared = true;
        }

        $logs = get_option( 'pmc_login_logs', array() );
        if ( ! is_array( $logs ) ) {
            $logs = array();
        }

        ?>
        <div class="wrap pmc-settings">
            <h1><?php echo esc_html__( 'Logged User List', 'press-math-captcha' ); ?></h1>
            <?php if ( $cleared ) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html__( 'Logged user list cleared.', 'press-math-captcha' ); ?></p>
                </div>
            <?php endif; ?>
            <form method="post" style="margin: 0 0 12px;">
                <?php wp_nonce_field( 'pmc_clear_login_logs_action', 'pmc_clear_login_logs_nonce' ); ?>
                <?php submit_button( __( 'Clear Logged User List', 'press-math-captcha' ), 'delete', 'pmc_clear_login_logs', false ); ?>
            </form>
            <table class="widefat striped">
                <thead>
                <tr>
                    <th><?php echo esc_html__( 'User', 'press-math-captcha' ); ?></th>
                    <th><?php echo esc_html__( 'Email', 'press-math-captcha' ); ?></th>
                    <th><?php echo esc_html__( 'IP', 'press-math-captcha' ); ?></th>
                    <th><?php echo esc_html__( 'Time', 'press-math-captcha' ); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php if ( empty( $logs ) ) : ?>
                    <tr>
                        <td colspan="4"><?php echo esc_html__( 'No login records yet.', 'press-math-captcha' ); ?></td>
                    </tr>
                <?php else : ?>
                    <?php foreach ( $logs as $log ) : ?>
                        <tr>
                            <td><?php echo esc_html( isset( $log['user_login'] ) ? $log['user_login'] : '' ); ?></td>
                            <td><?php echo esc_html( isset( $log['user_email'] ) ? $log['user_email'] : '' ); ?></td>
                            <td><?php echo esc_html( isset( $log['ip'] ) ? $log['ip'] : '' ); ?></td>
                            <td><?php echo esc_html( isset( $log['time'] ) ? $log['time'] : '' ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function log_login( $user_login, $user ) {
        if ( ! ( $user instanceof WP_User ) ) {
            return;
        }

        $logs = get_option( 'pmc_login_logs', array() );
        if ( ! is_array( $logs ) ) {
            $logs = array();
        }

        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

        $logs[] = array(
            'user_login' => $user->user_login,
            'user_email' => $user->user_email,
            'ip'         => $ip,
            'time'       => gmdate( 'Y-m-d H:i:s' ),
        );

        if ( count( $logs ) > 200 ) {
            $logs = array_slice( $logs, -200 );
        }

        update_option( 'pmc_login_logs', $logs );
    }

    public function render_failed_log() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $cleared = false;
        if ( isset( $_POST['pmc_clear_failed_logs'] ) ) {
            check_admin_referer( 'pmc_clear_failed_logs_action', 'pmc_clear_failed_logs_nonce' );
            delete_option( 'pmc_failed_logs' );
            $cleared = true;
        }

        $logs = get_option( 'pmc_failed_logs', array() );
        if ( ! is_array( $logs ) ) {
            $logs = array();
        }

        ?>
        <div class="wrap pmc-settings">
            <h1><?php echo esc_html__( 'Failed Login Log', 'press-math-captcha' ); ?></h1>
            <?php if ( $cleared ) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html__( 'Failed login log cleared.', 'press-math-captcha' ); ?></p>
                </div>
            <?php endif; ?>
            <form method="post" style="margin: 0 0 12px;">
                <?php wp_nonce_field( 'pmc_clear_failed_logs_action', 'pmc_clear_failed_logs_nonce' ); ?>
                <?php submit_button( __( 'Clear Failed Login Log', 'press-math-captcha' ), 'delete', 'pmc_clear_failed_logs', false ); ?>
            </form>
            <table class="widefat striped">
                <thead>
                <tr>
                    <th><?php echo esc_html__( 'User', 'press-math-captcha' ); ?></th>
                    <th><?php echo esc_html__( 'Email', 'press-math-captcha' ); ?></th>
                    <th><?php echo esc_html__( 'IP', 'press-math-captcha' ); ?></th>
                    <th><?php echo esc_html__( 'Time', 'press-math-captcha' ); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php if ( empty( $logs ) ) : ?>
                    <tr>
                        <td colspan="4"><?php echo esc_html__( 'No failed login records yet.', 'press-math-captcha' ); ?></td>
                    </tr>
                <?php else : ?>
                    <?php foreach ( $logs as $log ) : ?>
                        <tr>
                            <td><?php echo esc_html( isset( $log['user_login'] ) ? $log['user_login'] : '' ); ?></td>
                            <td><?php echo esc_html( isset( $log['user_email'] ) ? $log['user_email'] : '' ); ?></td>
                            <td><?php echo esc_html( isset( $log['ip'] ) ? $log['ip'] : '' ); ?></td>
                            <td><?php echo esc_html( isset( $log['time'] ) ? $log['time'] : '' ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function log_failed_login( $username ) {
        $logs = get_option( 'pmc_failed_logs', array() );
        if ( ! is_array( $logs ) ) {
            $logs = array();
        }

        $user = get_user_by( 'login', $username );
        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

        $logs[] = array(
            'user_login' => ( $user instanceof WP_User ) ? $user->user_login : sanitize_text_field( $username ),
            'user_email' => ( $user instanceof WP_User ) ? $user->user_email : '',
            'ip'         => $ip,
            'time'       => gmdate( 'Y-m-d H:i:s' ),
        );

        if ( count( $logs ) > 200 ) {
            $logs = array_slice( $logs, -200 );
        }

        update_option( 'pmc_failed_logs', $logs );
    }

    public function render_checkbox( $args ) {
        $settings = get_option( 'pmc_settings', array() );
        $key      = $args['key'];
        $value    = ! empty( $settings[ $key ] ) ? 1 : 0;
        ?>
        <label>
            <input type="checkbox" name="pmc_settings[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( 1, $value ); ?> />
        </label>
        <?php
    }

    public function render_select( $args ) {
        $settings = get_option( 'pmc_settings', array() );
        $key      = $args['key'];
        $current  = isset( $settings[ $key ] ) ? $settings[ $key ] : '';
        ?>
        <select name="pmc_settings[<?php echo esc_attr( $key ); ?>]">
            <?php foreach ( $args['options'] as $value => $label ) : ?>
                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current, $value ); ?>>
                    <?php echo esc_html( $label ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    public function render_text( $args ) {
        $settings = get_option( 'pmc_settings', array() );
        $key      = $args['key'];
        $value    = isset( $settings[ $key ] ) ? $settings[ $key ] : '';
        ?>
        <input type="text" class="regular-text" name="pmc_settings[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>" />
        <?php
    }

    public function render_number( $args ) {
        $settings = get_option( 'pmc_settings', array() );
        $key      = $args['key'];
        $value    = isset( $settings[ $key ] ) ? (int) $settings[ $key ] : 0;
        $min      = isset( $args['min'] ) ? (int) $args['min'] : 0;
        ?>
        <input type="number" class="small-text" min="<?php echo esc_attr( $min ); ?>" name="pmc_settings[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>" />
        <?php
    }

    public function sanitize_settings( $input ) {
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

        $sanitized = array();
        $input     = is_array( $input ) ? $input : array();

        $sanitized['enable_login']       = ! empty( $input['enable_login'] ) ? 1 : 0;
        $sanitized['enable_cf7']         = ! empty( $input['enable_cf7'] ) ? 1 : 0;
        $sanitized['enable_woocommerce'] = ! empty( $input['enable_woocommerce'] ) ? 1 : 0;
        $sanitized['hide_logged_in']     = ! empty( $input['hide_logged_in'] ) ? 1 : 0;
        $sanitized['enable_rate_limit']  = ! empty( $input['enable_rate_limit'] ) ? 1 : 0;

        $difficulty = isset( $input['difficulty'] ) ? sanitize_text_field( $input['difficulty'] ) : $defaults['difficulty'];
        $operation  = isset( $input['operation'] ) ? sanitize_text_field( $input['operation'] ) : $defaults['operation'];

        $allowed_difficulty = array( 'easy', 'medium', 'hard' );
        $allowed_operation  = array( 'random', 'addition', 'subtraction', 'multiplication' );

        $sanitized['difficulty'] = in_array( $difficulty, $allowed_difficulty, true ) ? $difficulty : $defaults['difficulty'];
        $sanitized['operation']  = in_array( $operation, $allowed_operation, true ) ? $operation : $defaults['operation'];

        $sanitized['error_message'] = isset( $input['error_message'] )
            ? sanitize_text_field( $input['error_message'] )
            : $defaults['error_message'];

        $sanitized['max_attempts']   = isset( $input['max_attempts'] ) ? absint( $input['max_attempts'] ) : $defaults['max_attempts'];
        $sanitized['block_duration'] = isset( $input['block_duration'] ) ? absint( $input['block_duration'] ) : $defaults['block_duration'];

        return wp_parse_args( $sanitized, $defaults );
    }
}

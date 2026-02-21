<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option( 'pmc_settings' );
delete_option( 'pmc_login_logs' );
delete_option( 'pmc_failed_logs' );

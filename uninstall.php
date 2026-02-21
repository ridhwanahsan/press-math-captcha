<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option( 'pmc_settings' );

global $wpdb;
$transients = $wpdb->get_col(
    "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_pmc_%' OR option_name LIKE '_transient_timeout_pmc_%'"
);

if ( ! empty( $transients ) ) {
    foreach ( $transients as $transient ) {
        delete_option( $transient );
    }
}

<?php
// If uninstall not called from WordPress exit
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
exit ();

// Delete option from options table
delete_option( 'pur_options' );
delete_option( 'pur_settings' );
delete_option( 'pur_db_version' );


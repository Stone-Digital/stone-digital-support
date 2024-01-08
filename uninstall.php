<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * Author: Stone Digital
 * Author URI: https://stonedigital.com.au
 * @package Support @ Stone Digital
 * @since 1.1.4
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

if ( is_multisite() ) {

	$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A );
	delete_site_option( 'stonedigital_plugin_hide_login_url_name' );
	delete_site_option( 'stonedigital_plugin_hide_redirection_url_name' );

	flush_rewrite_rules();

	if ( $blogs ) {

		foreach ( $blogs as $blog ) {
			switch_to_blog( $blog['blog_id'] );
			delete_option( 'stonedigital_plugin_hide_login_url_name' );
			delete_option( 'stonedigital_plugin_hide_redirection_url_name' );

			flush_rewrite_rules();

			//info: optimize table
			$GLOBALS['wpdb']->query( "OPTIMIZE TABLE `" . $GLOBALS['wpdb']->prefix . "options`" );
			restore_current_blog();
		}
	}

} else {
	delete_option( 'stonedigital_plugin_hide_login_url_name' );
	delete_option( 'stonedigital_plugin_hide_redirection_url_name' );

	flush_rewrite_rules();

	//info: optimize table
	$GLOBALS['wpdb']->query( "OPTIMIZE TABLE `" . $GLOBALS['wpdb']->prefix . "options`" );
}
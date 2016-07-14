<?php

/**
 *
 * @link       http://wpdynamictables.com
 * @since      1.0.0
 *
 * @package    WP Dynamic Tables
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if( get_option('wpdt-remove-data-on-uninstall') && get_option('wpdt-remove-data-on-uninstall') == true ){

	global $wpdb;

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	require plugin_dir_path( __FILE__ ) . 'includes/class-wp-dynamic-tables.php';
	require plugin_dir_path( __FILE__ ) . 'includes/class-wp-dynamic-tables-update-plugin.php';
	
	$plugin = new WP_Dynamic_Tables();
	$plugin_admin = new WP_Dynamic_Tables_Admin( $plugin->get_plugin_name(), $plugin->get_version() );
	$plugin_admin->wpdt_deactivate_license( 'on_uninstall' );

	$sql =
	"DELETE
	custom_posts, custom_posts_data
	FROM " . $wpdb->prefix . "posts custom_posts
	JOIN " . $wpdb->prefix . "postmeta custom_posts_data
	ON custom_posts_data.post_id = custom_posts.id
	WHERE custom_posts.post_type = 'wp_dynamic_tables'";
	
	dbDelta($sql);

	delete_option('wpdt-remove-data-on-uninstall');
	delete_option('wpdt-custom-css');
	delete_option('wpdt_product_license_key');
	delete_option('wpdt_user_envato_api_key');	
	delete_option('wpdt_envato_user_name');
	delete_option('wpdt_product_verified_code');
}
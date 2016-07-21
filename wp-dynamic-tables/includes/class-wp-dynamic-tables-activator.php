<?php

/**
 * Fired during plugin activation
 *
 * @link       http://wpdynamictables.com/
 * @since      1.0.0
 *
 * @package    WP_Dynamic_Tables
 * @subpackage WP_Dynamic_Tables/includes
 */

/**
 * @since      1.0.0
 * @package    WP_Dynamic_Tables
 * @subpackage WP_Dynamic_Tables/includes
 * @author     Your Name <email@example.com>
 */
class WP_Dynamic_Tables_Activator {

	/**
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		set_site_transient( 'update_plugins', '' );
	}

}

<?php

/**
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
class WP_Dynamic_Tables {

	/**
	 * @since    1.0.0
	 * @access   protected
	 * @var      WP_Dynamic_Tables_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'wp-dynamic-tables';
		$this->version = '1.0.8';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		add_shortcode( 'wpdtable', array( $this, 'wpdtable_shortcode' ) );

		add_action( 'widgets_init', function(){
		     register_widget( 'Wpdt_Widget' );
		});
	}

	public function wpdtable_shortcode( $atts ){

		$ret = '';

		extract( shortcode_atts(
			array(
				'id' => '',
			), $atts )
		);

		if( $atts['id'] != '' ){

			$ret = wpdt_display_table( $atts['id'] );
		}

		return $ret;
	}

	/**
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-dynamic-tables-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-dynamic-tables-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-dynamic-tables-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-dynamic-tables-public.php';

		$this->loader = new WP_Dynamic_Tables_Loader();
	}

	/**
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new WP_Dynamic_Tables_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new WP_Dynamic_Tables_Admin( $this->get_plugin_name(), $this->get_version() );

		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php' );

		$this->loader->add_action( 'admin_init', $plugin_admin, 'wp_dynamic_tables_add_meta_boxes' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'wp_dynamic_tables_additional_admin_pages' );
		$this->loader->add_filter( 'manage_wp_dynamic_tables_posts_columns', $plugin_admin, 'wp_dynamic_tables_additional_tablelist_columns' );
		$this->loader->add_filter( 'manage_wp_dynamic_tables_posts_custom_column', $plugin_admin, 'wp_dynamic_tables_additional_tablelist_columns_display' );
		$this->loader->add_filter( 'post_row_actions', $plugin_admin, 'wp_dynamic_tables_remove_view_link_cpt' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'wp_dynamic_tables_on_posts_save' );
		$this->loader->add_filter( 'plugin_action_links_' . $plugin_basename, $plugin_admin, 'wp_dynamic_tables_add_action_links' );
		$this->loader->add_filter( 'upload_mimes', $plugin_admin, 'wpdt_set_wpt_allowed_mimes' );
		$this->loader->add_action( 'wp_loaded', $plugin_admin, 'wpdt_execute_before_wp_header' );
		$this->loader->add_action( 'wp_ajax_get_url_file_table_columns_data', $plugin_admin, 'get_url_file_table_columns_data' );
		$this->loader->add_action( 'wp_ajax_nopriv_get_url_file_table_columns_data', $plugin_admin, 'get_url_file_table_columns_data' );
		$this->loader->add_action( 'wp_ajax_get_db_table_columns_data', $plugin_admin, 'get_db_table_columns_data' );
		$this->loader->add_action( 'wp_ajax_nopriv_get_db_table_columns_data', $plugin_admin, 'get_db_table_columns_data' );
		$this->loader->add_action( 'wp_ajax_wpdt_activate_license', $plugin_admin, 'wpdt_activate_license' );
		$this->loader->add_action( 'wp_ajax_nopriv_wpdt_activate_license', $plugin_admin, 'wpdt_activate_license' );
		$this->loader->add_action( 'wp_ajax_wpdt_deactivate_license', $plugin_admin, 'wpdt_deactivate_license' );
		$this->loader->add_action( 'wp_ajax_nopriv_wpdt_deactivate_license', $plugin_admin, 'wpdt_deactivate_license' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	}

	/**
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new WP_Dynamic_Tables_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init', $plugin_public, 'wp_dynamic_table_cpt' );
		$this->loader->add_action('wp_head', $plugin_public, 'custom_css' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_ajax_get_sside_tdata', $plugin_public, 'get_sside_tdata' );
		$this->loader->add_action( 'wp_ajax_nopriv_get_sside_tdata', $plugin_public, 'get_sside_tdata' );
		$this->loader->add_action( 'wp_ajax_wpdt_custom_export_xls', $plugin_public, 'wpdt_custom_export_xls' );
		$this->loader->add_action( 'wp_ajax_nopriv_wpdt_custom_export_xls', $plugin_public, 'wpdt_custom_export_xls' );
		$this->loader->add_action( 'wp_ajax_wpdt_fe_update_table', $plugin_public, 'wpdt_fe_update_table' );
		$this->loader->add_action( 'wp_ajax_nopriv_wpdt_fe_update_table', $plugin_public, 'wpdt_fe_update_table' );

		$this->loader->add_action( 'wp_ajax_wpdt_add_table_row', $plugin_public, 'wpdt_add_table_row' );
		$this->loader->add_action( 'wp_ajax_nopriv_wpdt_add_table_row', $plugin_public, 'wpdt_add_table_row' );

		$this->loader->add_action( 'wp_ajax_wpdt_remove_table_row', $plugin_public, 'wpdt_remove_table_row' );
		$this->loader->add_action( 'wp_ajax_nopriv_wpdt_remove_table_row', $plugin_public, 'wpdt_remove_table_row' );
	}

	/**
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * @since     1.0.0
	 * @return    WP_Dynamic_Tables_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
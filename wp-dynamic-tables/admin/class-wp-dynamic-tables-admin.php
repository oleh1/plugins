<?php

$wpdt_purchase_url = 'http://codecanyon.net/item/wordpress-dynamic-tables-input-from-xlsmysqlcsv/11189941';
$wpdt_service_url = 'http://104.236.248.154/fd78fgd/wpdnmctbls/verify-purchase-code/';

if ( ! defined( 'WPDT_UPDATE_API_URL' ) ) {
	 
	define( 'WPDT_UPDATE_API_URL', $wpdt_service_url );
}

if ( ! defined( 'WPDT_PURCHASE_URL' ) ) {
	
	define( 'WPDT_PURCHASE_URL', $wpdt_purchase_url  );
}

/**
 * @link       http://wpdynamictables.com/
 * @since      1.0.0
 *
 * @package    WP_Dynamic_Tables
 * @subpackage WP_Dynamic_Tables/admin
 */

/**
 * @package    WP_Dynamic_Tables
 * @subpackage WP_Dynamic_Tables/admin
 * @author     Your Name <email@example.com>
 */

class WP_Dynamic_Tables_Admin {

	/**
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		
		ini_set("precision", "50");

		$lib = WP_PLUGIN_DIR . '/wp-dynamic-tables/includes/spreadsheet-reader/';
		$lib_file1 = $lib . 'php-excel-reader/excel_reader2.php';
		$lib_file2 = $lib . 'SpreadsheetReader.php';

		require_once( $lib_file1 );
		require_once( $lib_file2 );

		require_once( WP_PLUGIN_DIR . "/wp-dynamic-tables/includes/PHPExcel_1.8.0_doc/Classes/PHPExcel.php" );

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-dynamic-tables-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_media();

		wp_enqueue_script( 'jquery-ui', plugin_dir_url( __FILE__ ) . 'js/jquery-ui-custom-1.11.2.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . '_admin', plugin_dir_url( __FILE__ ) . 'js/wp-dynamic-tables-admin.js', array( 'jquery-ui' ), $this->version, false );

		$localized_strings = array(
			'file_format_dismatch' => __( "File format doesn't match with selected table type", $this->plugin_name ),
			'first_save_settings' =>__( "You have to save your setting before activating license", $this->plugin_name ),
			'already_activated' =>__( "License code is already activated", $this->plugin_name ),
			'activation_error_ajax' =>__( "An error occured on license activation. Please try again.", $this->plugin_name ),
			'deactivation_error_ajax' =>__( "An error occured on license deactivation. Please try again.", $this->plugin_name ),
			'is_not_activated' =>__( "Your license is not activated", $this->plugin_name )
        );

		wp_localize_script( $this->plugin_name . '_admin', 'wpdt_admin_str', $localized_strings );
	}

	public function wp_dynamic_tables_add_meta_boxes(){

		if( isset( $_GET ) && !empty( $_GET ) && isset( $_GET['post_type'] ) &&  $_GET['post_type'] == "wp_dynamic_tables"  && isset( $_GET['wpdt_dupl'] ) &&  intval($_GET['wpdt_dupl']) > 0 ){

			$this->wpdt_duplicate_table( intval( $_GET['wpdt_dupl'] ) );
		}

		remove_meta_box( 'slugdiv', 'wp_dynamic_tables', 'normal' );

		add_meta_box( 'wp_dynamic_tables_table_settings', __( 'Table settings', $this->plugin_name ), array( $this, 'wp_dynamic_tables_settings_meta_box' ), 'wp_dynamic_tables', 'normal', 'high' );
		add_meta_box( 'wp_dynamic_tables_table_data', __( 'Table data', $this->plugin_name ), array( $this, 'wp_dynamic_tables_data_meta_box' ), 'wp_dynamic_tables', 'normal', 'high' );
	}

	public function wpdt_duplicate_table( $tid ){
		
		global $wpdb;

		$post_id = $tid;
	 
		$post = get_post( $post_id );
	 
		$current_user = wp_get_current_user();
		$new_post_author = $current_user->ID;

		if (isset( $post ) && $post != null) {
	 	
	 		$args = array(
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_author'    => $new_post_author,
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_name'      => $post->post_name,
				'post_parent'    => $post->post_parent,
				'post_password'  => $post->post_password,
				'post_status'    => 'publish',
				'post_title'     => $post->post_title . '_'.__( 'copy', $this->plugin_name ),
				'post_type'      => $post->post_type,
				'to_ping'        => $post->to_ping,
				'menu_order'     => $post->menu_order
			);
			 
			$new_post_id = wp_insert_post( $args );
	 
			$taxonomies = get_object_taxonomies($post->post_type);
	
			foreach ($taxonomies as $taxonomy) {
				$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
				wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
			}

			$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");

			if (count($post_meta_infos)!=0) {
				$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
				foreach ($post_meta_infos as $meta_info) {
					$meta_key = $meta_info->meta_key;
					$meta_value = addslashes($meta_info->meta_value);
					$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
				}

				$sql_query.= implode(" UNION ALL ", $sql_query_sel);
				$wpdb->query($sql_query);
			}

			wp_redirect( admin_url( 'edit.php?post_type=wp_dynamic_tables' ) );
			exit;
		}
	}

	public function wp_dynamic_tables_on_posts_save( $id ){

		if ( ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || !isset( $_POST['wp_dynamic_tables_nonce'] ) || !wp_verify_nonce( $_POST['wp_dynamic_tables_nonce'], 'wpdt-settings-nonce-abcd' ) ){
			
			return;
		}

		$table_type = isset( $_POST['wpdt_table_type'] ) ? $_POST['wpdt_table_type'] : 'none';

		$mysql_query = '';
		$mysql_external_dbhost = '';
		$mysql_external_dbname = '';
		$mysql_external_dbusername = '';
		$mysql_external_dbpassword = '';
		$mysql_external_query = '';
		$csv_url = '';
		$csv_filename = '';
		$excel_url = '';
		$excel_filename = '';
		$ods_url = '';
		$ods_filename = '';
		$xml_url = '';
		$xml_filename = '';
		$table_values = array();

		$upload_dir = wp_upload_dir();
		$upload_url = $upload_dir['baseurl'];

		switch( $table_type ){
			case 'mysql':

				global $wpdb;

				$mysql_query = isset( $_POST['wpdt_mysql_query'] ) ? trim( $_POST['wpdt_mysql_query'] ) : '';

				$mysql_query = str_replace( "\\", "", $mysql_query );

				if( $mysql_query !='' ){
				
					$queryResults = $wpdb->get_results( $mysql_query, ARRAY_A );
					$table_values = $this->get_mysql_results_to_wpdt_array( $queryResults );

					foreach($table_values as $key=>$value){

						$table_values[$key] = str_replace( '"', "'", $value );
						$table_values[$key] = str_replace( "\\", "", $table_values[$key] );
					}
				}

				
			break;
			case 'mysql_external':
				$mysql_external_dbhost = isset( $_POST['wpdt_ext_mysql_dbhost'] ) ? trim( $_POST['wpdt_ext_mysql_dbhost'] ) : '';
				$mysql_external_dbname = isset( $_POST['wpdt_ext_mysql_dbname'] ) ? trim( $_POST['wpdt_ext_mysql_dbname'] ) : '';
				$mysql_external_dbusername = isset( $_POST['wpdt_ext_mysql_dbusername'] ) ? trim( $_POST['wpdt_ext_mysql_dbusername'] ) : '';
				$mysql_external_dbpassword = isset( $_POST['wpdt_ext_mysql_dbpassword'] ) ? trim( $_POST['wpdt_ext_mysql_dbpassword'] ) : '';
				
				$mysql_external_query = isset( $_POST['wpdt_ext_mysql_query'] ) ? trim( $_POST['wpdt_ext_mysql_query'] ): '';
				$mysql_external_query = str_replace( "\\", "", $mysql_external_query );
				

				/* Open DB connection */

				$db_status = mysqli_connect($mysql_external_dbhost, $mysql_external_dbusername, $mysql_external_dbpassword);

				if (!$db_status) {

				    /* Close DB connection */

					if ( is_object($db_status) && get_class($db_status) === 'mysqli') {

						mysqli_close($db_status);
					}
				}
				else{

					$db_select = mysqli_select_db( $db_status, $mysql_external_dbname );

					if (!$db_select) {

					    /* Close DB connection */

						if ( is_object($db_status) && get_class($db_status) === 'mysqli') {

							mysqli_close($db_status);
						}
					}
					else{

						$queryResults = mysqli_query( $db_status, $mysql_external_query );

						$queryData = array();

						while ( $row = mysqli_fetch_assoc($queryResults) ) {

						    array_push( $queryData, $row );
						}

						/* Close DB connection */

						if ( is_object($db_status) && get_class($db_status) === 'mysqli') {

							mysqli_close($db_status);
						}

						if( !empty( $queryData) ){

							$table_values = $this->get_mysql_results_to_wpdt_array( $queryData );
							
							foreach($table_values as $key=>$value){
							
								$table_values[$key] = str_replace('"',"'",$value);
								$table_values[$key] = str_replace( "\\", "", $table_values[$key] );
							}
						}
						else{

							$r['msg'] = __( 'No results found', $this->plugin_name );
					    	$r['error'] = 1;
						}

					}
				}
			break;
			case 'csv':
				$csv_url = isset( $_POST['wpdt_upload_file_csv_url'] ) ? str_replace( $upload_url, "", esc_url( trim( $_POST['wpdt_upload_file_csv_url'] ) ) ) : '';
				$csv_filename = isset( $_POST['wpdt_upload_file_csv_filename'] ) ? trim( $_POST['wpdt_upload_file_csv_filename'] ) : '';
				
				if( $csv_url != '' ){
					$csv_path = str_replace( content_url(), WP_CONTENT_DIR, $upload_url . $csv_url );
					$table_values = $this->csv_file_to_array( $csv_path );
				}
			break;
			case 'excel':
				$excel_url = isset( $_POST['wpdt_upload_file_excel_url'] ) ? str_replace( $upload_url, "", esc_url( trim( $_POST['wpdt_upload_file_excel_url'] ) ) ) : '';
				$excel_filename = isset( $_POST['wpdt_upload_file_excel_filename'] ) ? trim( $_POST['wpdt_upload_file_excel_filename'] ) : '';

				if( $excel_url != '' ){
					$excel_path = str_replace( content_url(), WP_CONTENT_DIR, $upload_url . $excel_url );
					$table_values = $this->excel_file_to_array( $excel_path );
				}
			break;
			case 'ods':
				$ods_url = isset( $_POST['wpdt_upload_file_ods_url'] ) ? str_replace( $upload_url, "", esc_url( trim( $_POST['wpdt_upload_file_ods_url'] ) ) ) : '';
				$ods_filename = isset( $_POST['wpdt_upload_file_ods_filename'] ) ? trim( $_POST['wpdt_upload_file_ods_filename'] ) : '';
				
				if( $ods_url != '' ){
					$ods_path = str_replace( content_url(), WP_CONTENT_DIR, $upload_url . $ods_url );
					$table_values = $this->ods_file_to_array( $ods_path );
				}
			break;
			case 'xml':
				$xml_url = isset( $_POST['wpdt_upload_file_xml_url'] ) ? str_replace( $upload_url, "", esc_url( trim( $_POST['wpdt_upload_file_xml_url'] ) ) ) : '';
				$xml_filename = isset( $_POST['wpdt_upload_file_xml_filename'] ) ? trim( $_POST['wpdt_upload_file_xml_filename'] ) : '';
				
				if( $xml_url != '' ){
					$xml_path = str_replace( content_url(), WP_CONTENT_DIR, $upload_url . $xml_url );
					$table_values = $this->xml_file_to_array( $xml_path );
				}
			break;
			case 'none':
			default:
			break;
		}
		
		$paginav_rows = isset( $_POST['wpdt_paginav_rows'] ) ? trim( $_POST['wpdt_paginav_rows'] ) : '';
		update_post_meta( $id, 'paginav_rows', $paginav_rows );

		update_post_meta( $id, 'wpdt_mysql_query', $mysql_query );
		
		$columnsSettings = array();

		if( !empty( $table_values ) ){
			
			$table_values_pre = $table_values;
$table_values = json_encode( $table_values, JSON_UNESCAPED_UNICODE );
 if( trim( $table_values ) === '' ){
    $table_values = json_encode( $table_values_pre );
}
 $table_values_pre = null;
			
			update_post_meta( $id, 'table_columns_data', $table_values );

			update_post_meta( $id, 'table_type', $table_type );
		}

		if( isset( $_POST['default_order_num'] ) ){

			$r = array(
				'default_order_num',
				'custom_order_num',
				'wpdt_publish_col',
				'wpdt_chart_enable',
				'wpdt_col_width',
				'wpdt_col_width_val',
				'wpdt_col_width_type',
				'wpdt_col_type',
				'wpdt_col_num_format',
				'wpdt_col_currency_pos',
				'wpdt_col_def_sort',
				'wpdt_col_disable_tablets',
				'wpdt_col_disable_mobiles',
				'wpdt_col_exclude_search'
			);

			$le = count( $_POST['default_order_num'] );

			for( $c = 0; $c < $le; $c++ ){

				if ( !isset( $_POST['wpdt_publish_col'][$c] ) ){

					$_POST['wpdt_publish_col'][$c] = 'no';
				}
				else{

					$_POST['wpdt_publish_col'][$c] = 'yes';
				}

				if ( !isset( $_POST['wpdt_chart_enable'][$c] ) ){

					$_POST['wpdt_chart_enable'][$c] = 'no';
				}
				else{

					$_POST['wpdt_chart_enable'][$c] = 'yes';
				}

				if ( !isset( $_POST['wpdt_col_disable_tablets'][$c] ) ){

					$_POST['wpdt_col_disable_tablets'][$c] = 'no';
				}
				else{

					$_POST['wpdt_col_disable_tablets'][$c] = 'yes';
				}

				if ( !isset( $_POST['wpdt_col_disable_mobiles'][$c] ) ){

					$_POST['wpdt_col_disable_mobiles'][$c] = 'no';
				}
				else{

					$_POST['wpdt_col_disable_mobiles'][$c] = 'yes';
				}

				if ( !isset( $_POST['wpdt_col_exclude_search'][$c] ) ){

					$_POST['wpdt_col_exclude_search'][$c] = 'no';
				}
				else{

					$_POST['wpdt_col_exclude_search'][$c] = 'yes';
				}

				if( ( $c + 1 ) == $le ){

					$columnsSettings = array();

					foreach ($r as $key => $value) {
						
						if( isset( $_POST[ $value ] ) ){
							
							$columnsSettings[ $value ] = $_POST[ $value ];
						}
					}
				}
			}
		}

		if( empty( $columnsSettings ) ){

			$columnsSettings = "";
		}
		else{

			$columnsSettings = json_encode( $columnsSettings );
		}

		update_post_meta( $id, 'table_columns_settings', $columnsSettings );

		$per_tables_rows = isset( $_POST['wpdt_per_table_rows'] ) ? intval( $_POST['wpdt_per_table_rows'] ) : 10;
		update_post_meta( $id, 'per_tables_rows', $per_tables_rows );

		$enable_table_title = !isset( $_POST['wpdt_mysql_query_live_update'] ) || $_POST['wpdt_mysql_query_live_update'] != 'on' ? 0 : 1;
		update_post_meta( $id, 'wpdt_mysql_query_live_update', $enable_table_title );

		$enable_table_title = !isset( $_POST['wpdt_enable_table_title'] ) || $_POST['wpdt_enable_table_title'] != 'on' ? 0 : 1;
		update_post_meta( $id, 'enable_table_title', $enable_table_title );

		$enable_table_tools = !isset( $_POST['wpdt_enable_table_tools'] ) || $_POST['wpdt_enable_table_tools'] != 'on' ? 0 : 1;
		update_post_meta( $id, 'enable_table_tools', $enable_table_tools );

		$enable_search = !isset( $_POST['wpdt_enable_search'] ) || $_POST['wpdt_enable_search'] != 'on' ? 0 : 1;
		update_post_meta( $id, 'enable_search', $enable_search );

		$enable_sorting = !isset( $_POST['wpdt_enable_sorting'] ) || $_POST['wpdt_enable_sorting'] != 'on' ? 0 : 1;
		update_post_meta( $id, 'enable_sorting', $enable_sorting );

		$enable_word_wrap = !isset( $_POST['wpdt_enable_word_wrap'] ) || $_POST['wpdt_enable_word_wrap'] != 'on' ? 0 : 1;
		update_post_meta( $id, 'enable_word_wrap', $enable_word_wrap );

		$enable_chart = !isset( $_POST['wpdt_enable_chart'] ) || $_POST['wpdt_enable_chart'] != 'on' ? 0 : 1;
		update_post_meta( $id, 'enable_chart', $enable_chart );

		$chart_position = isset( $_POST['wpdt_chart_position'] ) && ( $_POST['wpdt_chart_position'] == 'above' || $_POST['wpdt_chart_position'] == 'below' ) ? $_POST['wpdt_chart_position'] : 'below';
		update_post_meta( $id, 'chart_position', $chart_position );
		
		$chart_type = isset( $_POST['wpdt_chart_type'] ) ? $_POST['wpdt_chart_type'] : 'line';
		update_post_meta( $id, 'chart_type', $chart_type );

		$enable_responsive = !isset( $_POST['wpdt_enable_responsive'] ) || $_POST['wpdt_enable_responsive'] != 'on' ? 0 : 1;
		update_post_meta( $id, 'enable_responsive', $enable_responsive );

		$enable_lazy_load = !isset( $_POST['wpdt_enable_lazy_load'] ) || $_POST['wpdt_enable_lazy_load'] != 'on' ? 0 : 1;
		update_post_meta( $id, 'enable_lazy_load', $enable_lazy_load );

		$enable_frontEdit = !isset( $_POST['wpdt_enable_front_edit'] ) || $_POST['wpdt_enable_front_edit'] != 'on' ? 0 : 1;
		update_post_meta( $id, 'enable_front_edit', $enable_frontEdit );

		$canEdit_roles = !isset( $_POST['wpdt-user-role-selections'] ) || !is_array( $_POST['wpdt-user-role-selections']) || empty( $_POST['wpdt-user-role-selections'] ) ? array('administrator') : $_POST['wpdt-user-role-selections'];

		if( !in_array( 'administrator', $canEdit_roles ) ){
			
			$canEdit_roles[] = 'administrator';
		}

		update_post_meta( $id, 'can_edit_roles', $canEdit_roles );
	}

	public function wp_dynamic_tables_settings_meta_box( $post ){

		$id = $post->ID;

		$table_type  = get_post_meta( $id, 'table_type', true );

		$mysql_query = '';

		$mysql_external_dbhost = '';
		$mysql_external_dbname = '';
		$mysql_external_dbusername = '';
		$mysql_external_dbpassword = '';
		$mysql_external_query = '';

		$csv_url = '';
		$csv_filename = '';

		$excel_url = '';
		$excel_filename = '';

		$ods_url = '';
		$ods_filename = '';

		$xml_url = '';
		$xml_filename = '';

		$upload_input_filename = '';

		$wpdt_insert_mysql_table_wrapper_class = 'hide';
		$wpdt_insert_ext_mysql_table_wrapper_class = 'hide';
		$wpdt_insert_file_table_wrapper_class = 'hide';

		$upload_dir = wp_upload_dir();
		$upload_url = $upload_dir['baseurl'];

		switch( $table_type ){
			case 'mysql':
			 	$mysql_query = get_post_meta( $id, 'wpdt_mysql_query', true );
				$wpdt_insert_mysql_table_wrapper_class = '';
			break;
			case 'mysql_external':
				$wpdt_insert_ext_mysql_table_wrapper_class = '';
			break;
			case 'csv':
				$wpdt_insert_file_table_wrapper_class = '';
			break;
			case 'excel':
				$wpdt_insert_file_table_wrapper_class = '';
			break;
			case 'ods':
				$wpdt_insert_file_table_wrapper_class = '';
			break;
			case 'xml':
				$wpdt_insert_file_table_wrapper_class = '';
			break;
			case 'none':
			default:
			break;
		}

		$per_tables_rows = get_post_meta( $id, 'per_tables_rows', true );

		$mysql_query_live_update = get_post_meta( $id, 'wpdt_mysql_query_live_update', true );

		$enable_table_title = get_post_meta( $id, 'enable_table_title', true );
		$enable_table_tools = get_post_meta( $id, 'enable_table_tools', true );
		$enable_search = get_post_meta( $id, 'enable_search', true );
		$enable_sorting = get_post_meta( $id, 'enable_sorting', true );
		$enable_word_wrap = get_post_meta( $id, 'enable_word_wrap', true );
		$enable_chart = get_post_meta( $id, 'enable_chart', true );
		$chart_position = get_post_meta( $id, 'chart_position', true );
		$chart_type = get_post_meta( $id, 'chart_type', true );
		$enable_responsive = get_post_meta( $id, 'enable_responsive', true );
		$enable_lazy_load = get_post_meta( $id, 'enable_lazy_load', true );
		$enable_frontEdit = get_post_meta( $id, 'enable_front_edit', true );
		$canEdit_roles = get_post_meta( $id, 'can_edit_roles', true );
		
		$paginav_rows = get_post_meta( $id, 'paginav_rows', true );

		$table_type = $table_type != '' ? $table_type : 'none';
		$per_tables_rows = $per_tables_rows != '' ? intval( $per_tables_rows ) : 10;
		$mysql_query_live_update = $mysql_query_live_update != '' ? intval($mysql_query_live_update) : 0;
		$enable_table_title = $enable_table_title != '' ? intval( $enable_table_title ) : 0;
		$enable_table_tools = $enable_table_tools != '' ? intval( $enable_table_tools ) : 1;
		$enable_search = $enable_search != '' ? intval( $enable_search ) : 0;
		$enable_sorting = $enable_sorting != '' ? intval( $enable_sorting ) : 1;
		$enable_word_wrap = $enable_word_wrap != '' ? intval( $enable_word_wrap ) : 1;
		$enable_chart = $enable_chart != '' ? intval( $enable_chart ) : 0;
		$chart_position = $chart_position != '' ? ( $chart_position == 'below' || $chart_position == 'above' ? $chart_position : 'below' ) : 'below';

		$valid_chart_types = array( 'line', 'spline', 'area', 'pie', 'bar', 'scatter', 'area-spline', 'step', 'area-step' );
		$chart_type = in_array( $chart_type, $valid_chart_types ) ? $chart_type : 'line';

		$enable_responsive = $enable_responsive != '' ? intval( $enable_responsive ) : 0;
		$enable_lazy_load = $enable_lazy_load != '' ? intval( $enable_lazy_load ) : 0;
		$enable_frontEdit = $enable_frontEdit != '' ? intval( $enable_frontEdit ) : 0;
		$canEdit_roles = $canEdit_roles != '' && is_array($canEdit_roles) ? $canEdit_roles : array('administrator');

		$mysql_query_live_update_checked = $mysql_query_live_update == 1 ? ' checked="checked" ': '';

		$enable_table_title_checked = $enable_table_title == 1 ? ' checked="checked" ': '';
		$enable_table_tools_checked = $enable_table_tools == 1 ? ' checked="checked" ': '';
		$enable_search_checked = $enable_search == 1 ? ' checked="checked" ': '';
		$enable_sorting_checked = $enable_sorting == 1 ? ' checked="checked" ': '';
		$enable_word_wrap_checked = $enable_word_wrap == 1 ? ' checked="checked" ': '';		
		$enable_chart_checked = $enable_chart == 1 ? ' checked="checked" ': '';
		$enable_responsive_checked = $enable_responsive == 1 ? ' checked="checked" ': '';
		$enable_lazy_load_checked = $enable_lazy_load == 1 ? ' checked="checked" ': '';
		$enable_frontEdit_checked = $enable_frontEdit == 1 ? ' checked="checked" ': '';

		$chart_position_hide_area_class = $enable_chart ? '' : 'hide';
		$front_edit_hide_area_class = $enable_frontEdit ? '' : 'hide';

		$mysql_query_live_update_area_classes = $table_type == 'mysql' ? '' : 'hide';

		$wp_dtables_edit_rows_settings_classes = $table_type == 'mysql' && $mysql_query_live_update == 1 ? 'hide' : '';
		$save_cpt_nonce = wp_create_nonce('wpdt-settings-nonce-abcd');
		?>
		<div class="wp_dtables_metabox_wrapper">
			<input type="hidden" id="wp_dynamic_tables_nonce" name="wp_dynamic_tables_nonce" value="<?php echo $save_cpt_nonce; ?>" />
			
			<!-- Shortcode addon - vagelis -->
			<div class="wp_dtables_setting">
			<label for="sr_table_type" class="select-lbl"><?php _e( "Shortcode:", $this->plugin_name ); ?></label>
			<span id="sr_table_type" class="select-lbl"> <?php echo '[wpdtable id="' . $post->ID . '"]';?></span>
		    </div>

			<div class="wp_dtables_setting">

				<label for="wpdt_table_type" class="select-lbl"><?php _e( "Table type:", $this->plugin_name ); ?></label>
				<select name="wpdt_table_type" id="wpdt_table_type">
					<option value="none" <?php if( $table_type == 'none' ){ echo ' selected="selected" '; } ?>><?php  _e( "- Select table type -", $this->plugin_name );?></option>
					<option value="mysql" <?php if( $table_type == 'mysql' ){ echo ' selected="selected" '; } ?>><?php  _e( "MySQL query", $this->plugin_name );?></option>
					<option value="mysql_external" <?php if( $table_type == 'mysql_external' ){ echo ' selected="selected" '; } ?>><?php  _e( "MySQL query - Non WP MySQL Connection", $this->plugin_name );?></option>
					<option value="csv" <?php if( $table_type == 'csv' ){ echo ' selected="selected" '; } ?>><?php  _e( "CSV", $this->plugin_name );?></option>
					<option value="excel" <?php if( $table_type == 'excel' ){ echo ' selected="selected" '; } ?>><?php  _e( "EXCEL (.xls, .xlsx)", $this->plugin_name );?></option>
					<option value="ods" <?php if( $table_type == 'ods' ){ echo ' selected="selected" '; } ?>><?php  _e( "ODS", $this->plugin_name );?></option>
					<option value="xml" <?php if( $table_type == 'xml' ){ echo ' selected="selected" '; } ?>><?php  _e( "XML", $this->plugin_name );?></option>
				</select>

				<div class="wpdt-insert-mysql-table-wrapper <?php echo $wpdt_insert_mysql_table_wrapper_class; ?>">
					<label for="wpdt_mysql_query"><?php _e( "MySQL query:", $this->plugin_name ); ?> </label>
					<div class="wpdt-mysql-tarea">
						<textarea name="wpdt_mysql_query" id="wpdt_mysql_query"><?php echo $mysql_query; ?></textarea>
						<div class="wpdt-test-query-wrap"></div>
						<button class="wpdt-test-mysql-query button button-primary button-large"><?php _e( "Test query", $this->plugin_name ); ?></button>
						<img src="<?php echo 'http://localhost/wp-layers-tests/wp-admin/images/spinner.gif'; ?>" alt="" class="wpdt-query-test-spinner" />
					</div>
				</div>

				<div class="wpdt-insert-ext-mysql-table-wrapper <?php echo $wpdt_insert_ext_mysql_table_wrapper_class; ?>">
					
					<label for="wpdt_ext_mysql_dbhost"><?php _e( "Database host:", $this->plugin_name ); ?> </label>
					<input type="text" name="wpdt_ext_mysql_dbhost" id="wpdt_ext_mysql_dbhost" value="<?php echo $mysql_external_dbhost; ?>"/>

					<br/>

					<label for="wpdt_ext_mysql_dbname"><?php _e( "Database name:", $this->plugin_name ); ?> </label>
					<input type="text" name="wpdt_ext_mysql_dbname" id="wpdt_ext_mysql_dbname" value="<?php echo $mysql_external_dbname; ?>"/>

					<br/>

					<label for="wpdt_ext_mysql_dbusername"><?php _e( "Database username:", $this->plugin_name ); ?> </label>
					<input type="text" name="wpdt_ext_mysql_dbusername" id="wpdt_ext_mysql_dbusername" value="<?php echo $mysql_external_dbusername; ?>"/>

					<br/>

					<label for="wpdt_ext_mysql_dbpassword"><?php _e( "Database password:", $this->plugin_name ); ?> </label>
					<input type="text" name="wpdt_ext_mysql_dbpassword" id="wpdt_ext_mysql_dbpassword" value="<?php echo $mysql_external_dbpassword; ?>"/>

					<br/>

					<label for="wpdt_ext_mysql_query"><?php _e( "MySQL query:", $this->plugin_name ); ?> </label>
					<div class="wpdt-mysql-tarea">
						<textarea name="wpdt_ext_mysql_query" id="wpdt_ext_mysql_query"><?php echo $mysql_external_query; ?></textarea>
						<div class="wpdt-test-query-wrap ext-host"></div>
						<button class="wpdt-test-mysql-query ext-host button button-primary button-large"><?php _e( "Test query", $this->plugin_name ); ?></button>
						<img src="<?php echo 'http://localhost/wp-layers-tests/wp-admin/images/spinner.gif'; ?>" alt="" class="wpdt-query-test-spinner" />
						</div>

				</div><!--/ .wpdt-insert-mysql-table-wrapper -->

				<div class="wpdt-insert-file-table-wrapper <?php echo $wpdt_insert_file_table_wrapper_class; ?>">
					
					<label><?php _e( "Upload file:", $this->plugin_name ); ?> </label>
					
					<div class="wpdt-file-upload-tarea">
						<input type="text" name="wpdt_upload_file_src" id="wpdt_upload_file_src" value="<?php echo $upload_input_filename; ?>" disabled="disabled" />
						<button class="wpdt-upload-file-button button button-primary button-large"><?php _e("Upload file", $this->plugin_name ); ?></button>
						<div class="wpdt-file-upload-wrap"></div>
					</div>

					<input type="hidden" name="wpdt_upload_file_csv_url" id="wpdt_upload_file_csv_url" value="<?php echo $csv_url; ?>" />
					<input type="hidden" name="wpdt_upload_file_excel_url" id="wpdt_upload_file_excel_url" value="<?php echo $excel_url; ?>" />
					<input type="hidden" name="wpdt_upload_file_ods_url" id="wpdt_upload_file_ods_url" value="<?php echo $ods_url; ?>" />
					<input type="hidden" name="wpdt_upload_file_xml_url" id="wpdt_upload_file_xml_url" value="<?php echo $xml_url; ?>" />

					<input type="hidden" name="wpdt_upload_file_csv_filename" id="wpdt_upload_file_csv_filename" value="<?php echo $csv_filename; ?>" />
					<input type="hidden" name="wpdt_upload_file_excel_filename" id="wpdt_upload_file_excel_filename" value="<?php echo $excel_filename; ?>" />
					<input type="hidden" name="wpdt_upload_file_ods_filename" id="wpdt_upload_file_ods_filename" value="<?php echo $ods_filename; ?>" />
					<input type="hidden" name="wpdt_upload_file_xml_filename" id="wpdt_upload_file_xml_filename" value="<?php echo $xml_filename; ?>" />

				</div><!--/ .wpdt-insert-mysql-table-wrapper -->

			</div>

			<div class="wp_dtables_setting wpdt-mysql-query-live-update-area <?php echo $mysql_query_live_update_area_classes; ?>">
				<label for="wpdt_mysql_query_live_update" class="checkbox-lbl"><input <?php echo $mysql_query_live_update_checked; ?> type="checkbox" name="wpdt_mysql_query_live_update" id="wpdt_mysql_query_live_update"/> <span><?php _e( "Automatic MySQL query data update", $this->plugin_name ); ?></span></label>
			</div>		
			
			<div class="wp_dtables_setting">
				<label for="wpdt_paginav_rows" class="select-lbl"><?php _e( "Number of rows for pagination to display:", $this->plugin_name ); ?></label>
				<input type="text" name="wpdt_paginav_rows" id="wpdt_paginav_rows" value="<?php echo $paginav_rows; ?>"/>
			</div>
			
			<div class="wp_dtables_setting">

				<label for="wpdt_per_table_rows" class="select-lbl"><?php _e( "Default entries per table:", $this->plugin_name ); ?></label>
				<select name="wpdt_per_table_rows" id="wpdt_per_table_rows">
					<option value="5"   <?php if( $per_tables_rows == 5   ){ echo ' selected="selected" '; } ?>><?php  _e( "5 entries",   $this->plugin_name );?></option>
					<option value="10"  <?php if( $per_tables_rows == 10  ){ echo ' selected="selected" '; } ?>><?php  _e( "10 entries",  $this->plugin_name );?></option>
					<option value="15"  <?php if( $per_tables_rows == 15  ){ echo ' selected="selected" '; } ?>><?php  _e( "15 entries",  $this->plugin_name );?></option>
					<option value="20"  <?php if( $per_tables_rows == 20  ){ echo ' selected="selected" '; } ?>><?php  _e( "20 entries",  $this->plugin_name );?></option>
					<option value="25"  <?php if( $per_tables_rows == 25  ){ echo ' selected="selected" '; } ?>><?php  _e( "25 entries",  $this->plugin_name );?></option>
					<option value="30"  <?php if( $per_tables_rows == 30  ){ echo ' selected="selected" '; } ?>><?php  _e( "30 entries",  $this->plugin_name );?></option>
					<option value="40"  <?php if( $per_tables_rows == 40  ){ echo ' selected="selected" '; } ?>><?php  _e( "40 entries",  $this->plugin_name );?></option>
					<option value="50"  <?php if( $per_tables_rows == 50  ){ echo ' selected="selected" '; } ?>><?php  _e( "50 entries",  $this->plugin_name );?></option>
					<option value="100" <?php if( $per_tables_rows == 100 ){ echo ' selected="selected" '; } ?>><?php  _e( "100 entries", $this->plugin_name );?></option>
				</select>
			</div>

			<div class="wp_dtables_setting">
				<label for="wpdt_enable_table_title" class="checkbox-lbl"><input <?php echo $enable_table_title_checked; ?> type="checkbox" name="wpdt_enable_table_title" id="wpdt_enable_table_title"/> <span><?php _e( "Show table title", $this->plugin_name ); ?></span></label>
			</div>

			<div class="wp_dtables_setting">
				<label for="wpdt_enable_table_tools" class="checkbox-lbl"><input <?php echo $enable_table_tools_checked; ?> type="checkbox" name="wpdt_enable_table_tools" id="wpdt_enable_table_tools"/> <span><?php _e( "Enable table tools to front-end", $this->plugin_name ); ?> <?php _e( "( copy, save to Excel, save to PDF etc.)", $this->plugin_name ); ?></span></label>
			</div>

			<div class="wp_dtables_setting">
				<label for="wpdt_enable_search" class="checkbox-lbl"><input <?php echo $enable_search_checked; ?> type="checkbox" name="wpdt_enable_search" id="wpdt_enable_search"/> <span><?php _e( "Enable search field", $this->plugin_name ); ?></span></label>
			</div>

			<div class="wp_dtables_setting">
				<label for="wpdt_enable_sorting" class="checkbox-lbl"><input <?php echo $enable_sorting_checked; ?> type="checkbox" name="wpdt_enable_sorting" id="wpdt_enable_sorting"/> <span><?php _e( "Enable sorting", $this->plugin_name ); ?></span></label>
			</div>
			
			<div class="wp_dtables_setting">
				<label for="wpdt_enable_word_wrap" class="checkbox-lbl"><input <?php echo $enable_word_wrap_checked; ?> type="checkbox" name="wpdt_enable_word_wrap" id="wpdt_enable_word_wrap"/> <span><?php _e( "Enable word wrap", $this->plugin_name ); ?></span></label>
			</div>
			
			<div class="wp_dtables_setting">
				<label for="wpdt_enable_chart" class="checkbox-lbl"><input <?php echo $enable_chart_checked; ?> type="checkbox" name="wpdt_enable_chart" id="wpdt_enable_chart"/> <span><?php _e( "Enable chart", $this->plugin_name ); ?></span></label>
			</div>

			<div class="wp_dtables_setting display_on_enabled_chart <?php echo $chart_position_hide_area_class; ?>">

				<label for="wpdt_chart_type" class="select-lbl"><?php _e( "Chart type:", $this->plugin_name ); ?></label>
				<select name="wpdt_chart_type" id="wpdt_chart_type">
					<option value="line" <?php if( $chart_type == 'line' ){ echo ' selected="selected" '; } ?>><?php  _e("Line", $this->plugin_name );?></option>
					<option value="spline" <?php if( $chart_type == 'spline' ){ echo ' selected="selected" '; } ?>><?php  _e("Spline", $this->plugin_name );?></option>
					<option value="area" <?php if( $chart_type == 'area' ){ echo ' selected="selected" '; } ?>><?php  _e("Area", $this->plugin_name );?></option>
					<option value="pie" <?php if( $chart_type == 'pie' ){ echo ' selected="selected" '; } ?>><?php  _e("Pie", $this->plugin_name );?></option>
					<option value="bar" <?php if( $chart_type == 'bar' ){ echo ' selected="selected" '; } ?>><?php  _e("Bar", $this->plugin_name );?></option>
					<option value="scatter" <?php if( $chart_type == 'scatter' ){ echo ' selected="selected" '; } ?>><?php  _e("Scatter", $this->plugin_name );?></option>
					<option value="area-spline" <?php if( $chart_type == 'area-spline' ){ echo ' selected="selected" '; } ?>><?php  _e("Area Spline", $this->plugin_name );?></option>
					<option value="step" <?php if( $chart_type == 'step' ){ echo ' selected="selected" '; } ?>><?php  _e("Step", $this->plugin_name );?></option>
					<option value="area-step" <?php if( $chart_type == 'area-step' ){ echo ' selected="selected" '; } ?>><?php  _e("Area step", $this->plugin_name );?></option>
				</select>

				<br/>

				<label for="wpdt_chart_position" class="select-lbl"><?php _e( "Chart position:", $this->plugin_name ); ?></label>
				<select name="wpdt_chart_position" id="wpdt_chart_position">
					<option value="above" <?php if( $chart_position == 'above' ){ echo ' selected="selected" '; } ?>><?php  _e("Above table", $this->plugin_name );?></option>
					<option value="below" <?php if( $chart_position == 'below' ){ echo ' selected="selected" '; } ?>><?php  _e("Below table", $this->plugin_name );?></option>
				</select>

			</div>

			<div class="wp_dtables_setting">
				<label for="wpdt_enable_responsive" class="checkbox-lbl"><input <?php echo $enable_responsive_checked; ?> type="checkbox" name="wpdt_enable_responsive" id="wpdt_enable_responsive"/> <span><?php _e( "Enable responsive", $this->plugin_name ); ?></span></label>
			</div>
			
			<div class="wp_dtables_setting">
				<label for="wpdt_enable_lazy_load" class="checkbox-lbl"><input <?php echo $enable_lazy_load_checked; ?> type="checkbox" name="wpdt_enable_lazy_load" id="wpdt_enable_lazy_load"/> <span><?php _e( "Enable lazy load", $this->plugin_name ); ?></span></label>
			</div>

			<div class="wp_dtables_edit_rows_settings <?php echo $wp_dtables_edit_rows_settings_classes; ?>">
				
				<div class="wp_dtables_setting no-bottom-border">
					<label for="wpdt_enable_front_edit" class="checkbox-lbl"><input <?php echo $enable_frontEdit_checked; ?> type="checkbox" name="wpdt_enable_front_edit" id="wpdt_enable_front_edit"/> <span><?php _e( "Enable table data front-end edit", $this->plugin_name ); ?></span></label>
				</div>

				<div class="wp_dtables_setting display_on_front_edit no-bottom-border <?php echo $front_edit_hide_area_class; ?>">
					
					<fieldset>
						<legend><?php _e("Select roles:", $this->plugin_name ); ?></legend>
						<?php

						global $wp_roles;

						$all_roles = $wp_roles->roles;

						foreach ( $all_roles as $key => $value) {

							$t = $value['name'];

							$selectedO = $key == 'administrator' || in_array( esc_attr( strtolower( $t ) ), $canEdit_roles ) ? 'checked="checked" ': '';
							
							if( $key == 'administrator' ){

								$selectedO = $selectedO . ' disabled="disabled" ';
							}

							?>

							<label>
								<input type="checkbox" name="wpdt-user-role-selections[]" value="<?php echo esc_attr( strtolower( $t ) ); ?>" <?php echo $selectedO; ?> /><?php echo $t; ?>
							</label>

							<?php
						}

						?>
					</fieldset>
				
				</div>

			</div>
		</div><!--/ .wp_dtables_metabox_wrapper -->

		<?php
	}

	public function wp_dynamic_tables_data_meta_box( $post ){

		?>
		
		<div class="tables-data-columns-outer empty">
			<?php

			$id = $post->ID;

			$columnsSettings = get_post_meta( $id, 'table_columns_settings', true );

			if( !$columnsSettings || $columnsSettings == '' ){
				?>
				<span class="empty-msg"><?php _e( "No data inserted", $this->plugin_name ); ?></span>
				<?php
			}
			else{

				$columnsSettings = json_decode( $columnsSettings, true );

				$table_values = get_post_meta( $id, 'table_columns_data', true );

				/*print_r( $table_values );*/

				$table_values = json_decode( $table_values, true );

				$table_first_row = $table_values[0];

				/*$tnt = 0;

				foreach( $table_values as $x => $y ){

					if( $tnt == 0 ){
						$table_first_row = $y;
					}

					$tnt++;
				}*/

				$columns_num = count( $table_first_row );

				$columnsOrder = $columnsSettings['default_order_num'];

				?>

				<ul class="tables-data-columns" style="width:<?php echo 30 + ($columns_num * 273); ?>px;">

					<?php

					$cnt = 0;

					foreach ( $columnsOrder as $i => $y) {

						if( isset( $table_first_row[$y] ) || $table_first_row[$y] === null ){

							$cnt = $cnt + 1;

							$column_title = __('Column', $this->plugin_name ) . ' ' . $cnt;

							if( trim( $table_first_row[$y] ) != '' ){

								$column_title .= ' <span>( ' . $table_first_row[$y] . ' )</span>';
							}

							$def_order = $columnsSettings['default_order_num'][$y];
							$custom_order = $columnsSettings['custom_order_num'][$y];
							$publish_col = $columnsSettings['wpdt_publish_col'][$y];
							$chart_enable = $columnsSettings['wpdt_chart_enable'][$y];
							$width_sel = $columnsSettings['wpdt_col_width'][$y];
							$width_val = $columnsSettings['wpdt_col_width_val'][$y];
							$width_type = $columnsSettings['wpdt_col_width_type'][$y];
							$column_type = $columnsSettings['wpdt_col_type'][$y];
							$column_num_format = $columnsSettings['wpdt_col_num_format'][$y];
							$column_currency_pos = $columnsSettings['wpdt_col_currency_pos'][$y];
							$def_sort = $columnsSettings['wpdt_col_def_sort'][$y];
							$disable_tablets = $columnsSettings['wpdt_col_disable_tablets'][$y];
							$disable_mobiles = $columnsSettings['wpdt_col_disable_mobiles'][$y];

							$exclude_search = isset($columnsSettings['wpdt_col_exclude_search']) && isset($columnsSettings['wpdt_col_exclude_search'][$y]) ? $columnsSettings['wpdt_col_exclude_search'][$y] : 'no';
							
							$publish_col_checked = $publish_col == 'yes' ? ' checked="checked" ' : '';
							 
							$chart_enable_checked = $chart_enable == 'yes' ? ' checked="checked" ' : '';

							$disable_tablets_checked = $disable_tablets == 'yes' ? ' checked="checked" ' : '';
							$disable_mobiles_checked = $disable_mobiles == 'yes' ? ' checked="checked" ' : '';

							$exclude_search_checked = $exclude_search == 'yes' ? ' checked="checked" ' : '';

							$selected_custom_width_class = $width_sel == 'auto' ? ' hide ' : "";

							$selected_col_num_format_class = $column_type == 'text' ? ' hide' : '';
							$selected_col_currency_pos_class = $column_type != 'currency' ? ' hide' : '';
							
							?>

							<li>
								<span class="tables-data-column-header"><?php echo $column_title; ?></span>
								
								<div class="tables-data-column-settings">

									<input type="hidden" name="default_order_num[<?php echo $i; ?>]" class="hidden_default_order_num_class" value="<?php echo $def_order; ?>" />
									<input type="hidden" name="custom_order_num[<?php echo $i; ?>]" class="hidden_custom_order_num_class" value="<?php echo $custom_order; ?>" />
									
									<div class="wp_dtables_columns_setting">

										<label class="checkbox-lbl"><input type="checkbox" name="wpdt_publish_col[<?php echo $i; ?>]" class="wpdt_publish_col_class" <?php echo $publish_col_checked; ?>/> <span><?php _e( "Publish", $this->plugin_name ); ?></span></label>
									</div>

									<div class="wp_dtables_columns_setting">
										<label class="select-lbl"><?php _e( "Width:", $this->plugin_name ); ?></label>
										<select name="wpdt_col_width[<?php echo $i; ?>]" class="wpdt_col_width_class">
											<option value="auto" <?php if( $width_sel == 'auto' ){ echo ' selected="selected" '; } ?>><?php _e("Auto", $this->plugin_name );?></option>
											<option value="custom" <?php if( $width_sel == 'custom' ){ echo ' selected="selected" '; } ?>><?php _e("Custom", $this->plugin_name );?></option>
										</select>

										<div class="on-custom-col-width <?php echo $selected_custom_width_class; ?>">
											<input type="text" name="wpdt_col_width_val[<?php echo $i; ?>]" class="wpdt_col_width_val_class" value="<?php echo $width_val; ?>" />
											<select name="wpdt_col_width_type[<?php echo $i; ?>]" class="wpdt_col_width_type_class">
												<option value="px" <?php if( $width_type == 'px' ){ echo ' selected="selected" '; } ?>><?php _e("px", $this->plugin_name );?></option>
												<option value="pc" <?php if( $width_type == 'pc' ){ echo ' selected="selected" '; } ?>><?php _e("%", $this->plugin_name );?></option>
											</select>
										</div>
									</div>

									<div class="wp_dtables_columns_setting">
										<label class="select-lbl"><?php _e( "Column type:", $this->plugin_name ); ?></label>
										<select name="wpdt_col_type[<?php echo $i; ?>]" class="wpdt_col_type_class">
											<option value="text" <?php if( $column_type == 'text' ){ echo ' selected="selected" '; } ?>><?php _e("Text", $this->plugin_name );?></option>
											<option value="number" <?php if( $column_type == 'number' ){ echo ' selected="selected" '; } ?>><?php _e("Number", $this->plugin_name );?></option>
											<option value="currency" <?php if( $column_type == 'currency' ){ echo ' selected="selected" '; } ?>><?php _e("Currency amount", $this->plugin_name );?></option>
										</select>
									</div>

									<div class="wp_dtables_columns_setting col-num-format-class <?php echo $selected_col_num_format_class; ?>">
										<label class="select-lbl"><?php _e( "Number format:", $this->plugin_name ); ?></label>
										<select name="wpdt_col_num_format[<?php echo $i; ?>]" class="wpdt_col_nformat_class">
											<option value="1" <?php if( $column_num_format == 1 ){ echo ' selected="selected" '; } ?>><?php _e("1000.00", $this->plugin_name );?></option>
											<option value="2" <?php if( $column_num_format == 2 ){ echo ' selected="selected" '; } ?>><?php _e("1000,00", $this->plugin_name );?></option>
											<option value="3" <?php if( $column_num_format == 3 ){ echo ' selected="selected" '; } ?>><?php _e("1,000.00", $this->plugin_name );?></option>
											<option value="4" <?php if( $column_num_format == 4 ){ echo ' selected="selected" '; } ?>><?php _e("1.000,00", $this->plugin_name );?></option>
										</select>
									</div>

									<div class="wp_dtables_columns_setting col-currency-pos-class <?php echo $selected_col_num_format_class; ?>">
										<label class="select-lbl"><?php _e( "Symbol position:", $this->plugin_name ); ?></label>
										<select name="wpdt_col_currency_pos[<?php echo $i; ?>]" class="wpdt_col_cpos_class">
											<option value="before" <?php if( $column_currency_pos == 'before' ){ echo ' selected="selected" '; } ?>><?php _e("Before amount", $this->plugin_name );?></option>
											<option value="after" <?php if( $column_currency_pos == 'after' ){ echo ' selected="selected" '; } ?>><?php _e("After amount", $this->plugin_name );?></option>
										</select>
									</div>

									<div class="wp_dtables_columns_setting">
										<label class="select-lbl"><?php _e( "Default sort:", $this->plugin_name ); ?></label>
										<select name="wpdt_col_def_sort[<?php echo $i; ?>]">
											<option value="asc" <?php if( $def_sort == 'asc' ){ echo ' selected="selected" '; } ?>><?php _e("Ascending", $this->plugin_name );?></option>
											<option value="desc" <?php if( $def_sort == 'desc' ){ echo ' selected="selected" '; } ?>><?php _e("Descending", $this->plugin_name );?></option>
											<option value="none" <?php if( $def_sort == 'none' ){ echo ' selected="selected" '; } ?>><?php _e("None", $this->plugin_name );?></option>
										</select>
									</div>

									<div class="wp_dtables_columns_setting">

										<label class="checkbox-lbl"><input type="checkbox" name="wpdt_chart_enable[<?php echo $i; ?>]" class="wpdt_chart_enable_class" <?php echo $chart_enable_checked; ?>/> <span><?php _e( "Enable in chart", $this->plugin_name ); ?></span></label>
									</div>

									<div class="wp_dtables_columns_setting">
										<label class="checkbox-lbl"><input type="checkbox" name="wpdt_col_disable_tablets[<?php echo $i; ?>]" class="wpdt_col_disable_tablets_class" <?php  echo $disable_tablets_checked; ?>/> <span><?php _e( "Disable on tablets", $this->plugin_name ); ?></span></label>
									</div>

									<div class="wp_dtables_columns_setting">
										<label class="checkbox-lbl"><input type="checkbox" name="wpdt_col_disable_mobiles[<?php echo $i; ?>]" class="wpdt_col_disable_mobiles_class" <?php  echo $disable_mobiles_checked; ?>/> <span><?php _e( "Disable on mobiles", $this->plugin_name ); ?></span></label>
									</div>

									<div class="wp_dtables_columns_setting">
										<label class="checkbox-lbl"><input type="checkbox" name="wpdt_col_exclude_search[<?php echo $i; ?>]" class="wpdt_col_exclude_search_class" <?php  echo $exclude_search_checked; ?>/> <span><?php _e( "Exclude from search", $this->plugin_name ); ?></span></label>
 									</div>

								</div><!--/ .tables-data-column-settings -->
							</li>

							<?php

						}
					}

					?>

				</ul>

				<?php
			}
			?>
		</div>
		<?php
	}

	public function wp_dynamic_tables_generate_file_table_columns_settings( $arr = array() ){

		$f = $arr[0];

		$columns_num = count($f);
		$column_num_format = 1;
		$column_currency_pos = 'before';

		ob_start();
		?>
		<ul class="tables-data-columns" style="width:<?php echo 10 + ($columns_num * 275); ?>px;">
				
			<?php 

			$i = 0;

			$column_type ='text';

			foreach ($f as $x => $y) { 

				$column_title = __('Column', $this->plugin_name ) . ' ' . ($i + 1);

				if( trim($y) != '' ){

					$column_title .= ' <span>( ' . $y . ' )</span>';
				}

				?>
				
				<li>
					<span class="tables-data-column-header"><?php echo $column_title; ?></span>
					
					<div class="tables-data-column-settings">
						
						<input type="hidden" name="default_order_num[<?php echo $i; ?>]" class="hidden_default_order_num_class" value="<?php echo $i; ?>" />
						<input type="hidden" name="custom_order_num[<?php echo $i; ?>]" class="hidden_custom_order_num_class" value="<?php echo $i; ?>" />

						<div class="wp_dtables_columns_setting">
							<label class="checkbox-lbl"><input type="checkbox" name="wpdt_publish_col[<?php echo $i; ?>]" class="wpdt_publish_col_class" checked="checked"/> <span><?php _e( "Publish", $this->plugin_name ); ?></span></label>
						</div>

						<div class="wp_dtables_columns_setting">
							<label class="select-lbl"><?php _e( "Width:", $this->plugin_name ); ?></label>
							<select name="wpdt_col_width[<?php echo $i; ?>]" class="wpdt_col_width_class">
								<option value="auto" selected="selected"><?php _e("Auto", $this->plugin_name );?></option>
								<option value="custom"><?php _e("Custom", $this->plugin_name );?></option>
							</select>

							<div class="on-custom-col-width hide">
								<input type="text" name="wpdt_col_width_val[<?php echo $i; ?>]" class="wpdt_col_width_val_class" value="" />
								<select name="wpdt_col_width_type[<?php echo $i; ?>]" class="wpdt_col_width_type_class">
									<option value="px" selected="selected"><?php _e("px", $this->plugin_name );?></option>
									<option value="pc"><?php _e("%", $this->plugin_name );?></option>
								</select>
							</div>

						</div>

						<div class="wp_dtables_columns_setting">
							<label class="select-lbl"><?php _e( "Column type:", $this->plugin_name ); ?></label>
							<select name="wpdt_col_type[<?php echo $i; ?>]" class="wpdt_col_type_class">
								<option value="text" <?php if( $column_type == 'text' ){ echo ' selected="selected" '; } ?>><?php _e("Text", $this->plugin_name );?></option>
								<option value="number" <?php if( $column_type == 'number' ){ echo ' selected="selected" '; } ?>><?php _e("Number", $this->plugin_name );?></option>
								<option value="currency" <?php if( $column_type == 'currency' ){ echo ' selected="selected" '; } ?>><?php _e("Currency amount", $this->plugin_name );?></option>
							</select>
						</div>


						<div class="wp_dtables_columns_setting col-num-format-class hide">
							<label class="select-lbl"><?php _e( "Number format:", $this->plugin_name ); ?></label>
							<select name="wpdt_col_num_format[<?php echo $i; ?>]" class="wpdt_col_nformat_class">
								<option value="1" <?php if( $column_num_format == 1 ){ echo ' selected="selected" '; } ?>><?php _e("1000.00", $this->plugin_name );?></option>
								<option value="2" <?php if( $column_num_format == 2 ){ echo ' selected="selected" '; } ?>><?php _e("1000,00", $this->plugin_name );?></option>
								<option value="3" <?php if( $column_num_format == 3 ){ echo ' selected="selected" '; } ?>><?php _e("1,000.00", $this->plugin_name );?></option>
								<option value="4" <?php if( $column_num_format == 4 ){ echo ' selected="selected" '; } ?>><?php _e("1.000,00", $this->plugin_name );?></option>
							</select>
						</div>

						<div class="wp_dtables_columns_setting col-currency-pos-class hide">
							<label class="select-lbl"><?php _e( "Symbol position:", $this->plugin_name ); ?></label>
							<select name="wpdt_col_currency_pos[<?php echo $i; ?>]" class="wpdt_col_cpos_class">
								<option value="before" <?php if( $column_currency_pos == 'before' ){ echo ' selected="selected" '; } ?>><?php _e("Before amount", $this->plugin_name );?></option>
								<option value="after" <?php if( $column_currency_pos == 'after' ){ echo ' selected="selected" '; } ?>><?php _e("After amount", $this->plugin_name );?></option>
							</select>
						</div>

						<div class="wp_dtables_columns_setting">
							<label class="select-lbl"><?php _e( "Default sort:", $this->plugin_name ); ?></label>
							<select name="wpdt_col_def_sort[<?php echo $i; ?>]" class="wpdt_col_def_sort_class">
								<option value="asc"><?php _e("Ascending", $this->plugin_name );?></option>
								<option value="desc"><?php _e("Descending", $this->plugin_name );?></option>
								<option value="none"><?php _e("None", $this->plugin_name );?></option>
							</select>
						</div>

						<div class="wp_dtables_columns_setting">
							<label class="checkbox-lbl"><input type="checkbox" name="wpdt_chart_enable[<?php echo $i; ?>]" class="wpdt_chart_enable_class" /> <span><?php _e( "Enable in chart", $this->plugin_name ); ?></span></label>								
						</div>

						<div class="wp_dtables_columns_setting">
							<label class="checkbox-lbl"><input type="checkbox" name="wpdt_col_disable_tablets[<?php echo $i; ?>]" class="wpdt_col_disable_tablets_class" /> <span><?php _e( "Disable on tablets", $this->plugin_name ); ?></span></label>
						</div>

						<div class="wp_dtables_columns_setting">
							<label class="checkbox-lbl"><input type="checkbox" name="wpdt_col_disable_mobiles[<?php echo $i; ?>]" class="wpdt_col_disable_mobiles_class" /> <span><?php _e( "Disable on mobiles", $this->plugin_name ); ?></span></label>
						</div>

						<div class="wp_dtables_columns_setting">
							<label class="checkbox-lbl"><input type="checkbox" name="wpdt_col_exclude_search[<?php echo $i; ?>]" class="wpdt_col_exclude_search_class" /> <span><?php _e( "Exclude from search", $this->plugin_name ); ?></span></label>
						</div>

					</div><!--/ .tables-data-column-settings -->
				</li>

				<?php

				$i = $i + 1;
			} ?>

		</ul>
		<?php

		$output = ob_get_contents();

		ob_clean();

		return $output;
	}

	public function wp_dynamic_tables_additional_admin_pages( $post ){

		$admin_capability = "manage_options";

		add_submenu_page( 'edit.php?post_type=wp_dynamic_tables', __( 'Settings', $this->plugin_name ), __( 'Settings', $this->plugin_name ), $admin_capability, 'wp-dynamic-table-settings', 
			array( $this, 'wp_dynamic_tables_settings_callback' ) );
	}

	public function wp_dynamic_tables_settings_tabs( $current = 'wpdt_settings' ) {

	    $tabs = array( 
	    	'wpdt_settings' 		=> __( 'General', $this->plugin_name ) , 
	    	'wpdt_product_license'	=> __( 'Product license', $this->plugin_name )
	    );

	    echo '<h2 class="nav-tab-wrapper">';

	    foreach( $tabs as $tab => $name ){

	        $class = ( $tab == $current ) ? ' nav-tab-active' : '';

	        if( $tab == "wpdt_settings" ){

	        	$tab_str = "";
	        }
	        else{

	        	$tab_str = "&tab=$tab";
	        }

	        $link_pre = 'edit.php?post_type=wp_dynamic_tables&page=wp-dynamic-table-settings';

	        echo "<a class='nav-tab$class' href='" . admin_url( $link_pre . $tab_str ) . "'>$name</a>";

	    }
	    echo '</h2>';
	}

	public function wp_dynamic_tables_settings_callback(){

		$message = $this->get_plugin_msg();
		$errorMessage = $message['error'];
		$message = $message['message'];

		$updated_html = "";

		if( $message ){

			$updated_classes = array('updated top-msg');

			if($errorMessage){

				$updated_classes[] = 'error';
			}

			$updated_html .= "<div class='" . implode( " ", $updated_classes ) . "'>";
			$updated_html .= $message;
			$updated_html .= "</div>";
		}

		?>

		<div id="wpdt_settings">

			<h1 class=""><?php _e( "Dynamic Tables Settings", $this->plugin_name ); ?></h1>

			<?php

			$current_tab = "wpdt_settings";

			$link_pre = 'edit.php?post_type=wp_dynamic_tables&page=wp-dynamic-table-settings';

			$current_tabURL_slug = admin_url( $link_pre );

			if ( isset ( $_GET['tab'] ) ){

				$this->wp_dynamic_tables_settings_tabs( $_GET['tab'] );

				if( $_GET['tab'] != "wpdt_settings" ){

					$current_tab = $_GET['tab'];
					$current_tabURL_slug .= "&tab=" . $current_tab;
				}
			}
			else{

				$this->wp_dynamic_tables_settings_tabs( $current = 'wpdt_settings' );
			}

			echo $updated_html;

			switch( $current_tab ){
				case "wpdt_settings":

					$wpdt_remove_data_on_uninstall = get_option('wpdt-remove-data-on-uninstall') && get_option('wpdt-remove-data-on-uninstall') == true ? 'checked="checked"' : "";
					$custom_css = get_option('wpdt-custom-css') ? get_option('wpdt-custom-css') : "";

					$settings_nonce = wp_create_nonce('wpdt-settings-nonce-general');

					?>

					<form method="post" action="<?php echo esc_url( $current_tabURL_slug ); ?>" class="wpdt-settings-form">

						<input type="hidden" id="wpdt_general_settings_nonce" name="wpdt_general_settings_nonce" value="<?php echo $settings_nonce; ?>" />

						<div class="wpdt-form-group">
							<label for="wpdt-remove-data-on-uninstall"><?php _e( "Remove Dynamic Tables data on plugin uninstall", $this->plugin_name ); ?></label>&nbsp;
							<input type="checkbox" id="wpdt-remove-data-on-uninstall" name="wpdt-remove-data-on-uninstall" <?php echo $wpdt_remove_data_on_uninstall; ?> />
						</div>
 
						<div class="wpdt-form-group">
							<label for="wpdt-custom-css"><?php _e( "Add Custom CSS", $this->plugin_name ); ?></label>&nbsp;
							<br/>
							<textarea name="wpdt-custom-css" id="wpdt-custom-css"><?php echo $custom_css; ?></textarea>
						</div>

						<input type="hidden" name="action" value="update_wpdt_general_settings" />

						<p class="submit">
							<input type="submit" class="wpdt-settings-save button-primary" value="<?php _e('Save Settings', $this->plugin_name ) ?>" />
						</p>

					</form>
					<?php
				break;
				case "wpdt_product_license":

					add_thickbox();

					$wpdt_product_license_key = get_option('wpdt_product_license_key') ? trim(get_option('wpdt_product_license_key')) : "";
					$wpdt_user_envato_api_key = get_option('wpdt_user_envato_api_key') ? trim(get_option('wpdt_user_envato_api_key')) : "";
					$wpdt_envato_user_name = get_option('wpdt_envato_user_name') ? trim(get_option('wpdt_envato_user_name')) : "";

					if( $wpdt_product_license_key == "" || $wpdt_user_envato_api_key == "" || $wpdt_envato_user_name == "" ){

						$can_activate = 0;
					}
					else{

						$can_activate = 1;
					}

					$verified_code = get_option('wpdt_product_verified_code') ? trim(get_option('wpdt_product_verified_code')) : '';
					$is_active_license = $verified_code == '' ? 0 : 1;
					$can_activate = $is_active_license ? 0 : $can_activate;
					$activated_disabled_inputs = $is_active_license ? 'disabled="disabled"' : '';
					$to_activate_class = $is_active_license == 1 ? "" : "to-activate";

					$settings_license_nonce = wp_create_nonce('wpdt-settings-nonce-license');

					?>
					<form method="post" action="<?php echo esc_url( $current_tabURL_slug ); ?>" class="wpdt-settings-form form-product-license <?php echo $to_activate_class; ?>">

						<input type="hidden" id="wpdt_license_settings_nonce" name="wpdt_license_settings_nonce" value="<?php echo $settings_license_nonce; ?>" />
						
						<div class="updated license_key_info">
							<span class="dashicons dashicons-info"></span>
							<p><?php 
								printf( __( "By verifying your product's license you qualified to enable auto updates of <b>WP Dynamic Tables</b> plugin. The license (purchase) code may only be used for one WordPress site at a time. If you have previously activated your license code on another site, then you should deactivate it first or obtain a %snew one%s .", $this->plugin_name ),
								"<a href='" . esc_url( WPDT_PURCHASE_URL ) . "' title='' target='_blank'>",
								"</a>"
								) ; 
								?></p>
						</div>

						<div class="wpdt-form-group">

							
							<label for="wpdt-user-envato-name" class="product-license-label"><?php _e( "Envato username", $this->plugin_name ); ?></label>&nbsp;
							<input type="text" id="wpdt-user-envato-name" name="wpdt-user-envato-name" class="product-license-txt-input" value="<?php echo $wpdt_envato_user_name; ?>" <?php echo $activated_disabled_inputs; ?> />
						</div>

						<div class="wpdt-form-group">
							
							<label for="wpdt-user-envato-api-key" class="product-license-label"><?php _e( "Secret API key", $this->plugin_name ); ?></label>&nbsp;
							<input type="text" id="wpdt-user-envato-api-key" name="wpdt-user-envato-api-key" class="product-license-txt-input" value="<?php echo $wpdt_user_envato_api_key; ?>" <?php echo $activated_disabled_inputs; ?> />

							<?php
							
							$license_img = plugins_url( 'wp-dynamic-tables/includes/img/envato_secret_API_key.png' );

							?>

							<p class="where-find-license"><a href="<?php echo $license_img .'?width=auto&height=auto'; ?>" title="" target="_blank" class="thickbox"><?php _e( "Where to find it?", $this->plugin_name ); ?></a></p>
						</div>

						<div class="wpdt-form-group">
							
							<label for="wpdt-product-license-key" class="product-license-label"><?php _e( "Purchase code", $this->plugin_name ); ?></label>&nbsp;
							<input type="text" id="wpdt-product-license-key" name="wpdt-product-license-key" class="product-license-txt-input" value="<?php echo $wpdt_product_license_key; ?>" <?php echo $activated_disabled_inputs; ?> />

							<?php $license_img = plugins_url( 'wp-dynamic-tables/includes/img/envato-license-key.png' ); ?>

							<p class="where-find-license"><a href="<?php echo $license_img .'?width=auto&height=auto'; ?>" title="" target="_blank" class="thickbox"><?php _e( "Where to find it?", $this->plugin_name ); ?></a></p>
						</div>

						<div class="updated wpdt-license-messages"></div>

						<p class="submit wpdt-license-buttons">
							<input type="submit" class="wpdt-product-license-settings-save button button-primary" value="<?php _e('Save Settings', $this->plugin_name) ?>" <?php echo $activated_disabled_inputs; ?> />
							
							<button class="wpdt-activate-license button"><?php _e( "Activate license", $this->plugin_name ); ?></button>
							<button class="wpdt-deactivate-license button"><?php _e( "Deactivate license", $this->plugin_name ); ?></button>

							<img class="wpdt-loading-license-activation" alt="loading" src="<?php echo admin_url("images/spinner.gif"); ?>">
						</p>

						<input type="hidden" name="action" value="update_wpdt_product_license_settings" />

						<input type="hidden" id="wpdt_can_activate" name="wpdt_can_activate" value="<?php echo esc_attr( $can_activate ); ?>" />
						<input type="hidden" id="wpdt_is_active_license" name="wpdt_is_active_license" value="<?php echo esc_attr( $is_active_license ); ?>" />
					
					</form>					
					<?php
				break;
			}

			?>

		</div><!--/ #wpdt_settings -->

		<?php
	}

	public $plugin_has_msg = null;
	public $plugin_error_flag = null;
	
	/**
	 * Retrieve messages info
	 *
	 * @since    1.0.0
	 *
	 */

	public function get_plugin_msg() {

		$msg = $this->plugin_has_msg;
		$error = $this->plugin_error_flag;

		$ret = array();
		$ret["message"] = $msg;
		$ret["error"] = $error;

		return $ret;
	}

	/**
	 * Set messages info
	 *
	 * @since    1.0.0
	 *
	 */

	public function set_plugin_msg( $d, $er ) {

		$this->plugin_has_msg = $d;
		$this->plugin_error_flag = $er;
	}

	public function wpdt_execute_before_wp_header(){

		if( isset( $_POST ) && !empty( $_POST ) ){

			if( isset( $_POST['action'] ) ){

				switch( $_POST['action'] ){
					case "update_wpdt_general_settings":

						if( is_user_logged_in() ){

							if ( isset( $_POST['wpdt_general_settings_nonce'] ) && wp_verify_nonce( $_POST['wpdt_general_settings_nonce'], 'wpdt-settings-nonce-general' ) ){

								$remove_onUninstall = isset( $_POST['wpdt-remove-data-on-uninstall'] ) ? true : false;
								update_option( 'wpdt-remove-data-on-uninstall', $remove_onUninstall );

								$custom_css_style = trim( wp_unslash( $_POST['wpdt-custom-css'] ) );
								update_option( 'wpdt-custom-css', $custom_css_style );

								$success_message = __( "Settings saved successful.", $this->plugin_name );
								$this->set_plugin_msg( $success_message, 0 );
							}
						}
					break;
					case "update_wpdt_product_license_settings":

						if( is_user_logged_in() ){

							if ( isset( $_POST['wpdt_license_settings_nonce'] ) && wp_verify_nonce( $_POST['wpdt_license_settings_nonce'], 'wpdt-settings-nonce-license' ) ){

								$allowed_settings_role = array( "administrator" );

								$canView_settings = $this->user_can_view_settings( $allowed_settings_role );						

								if( $canView_settings ){

									$wpdt_envato_user_name 	 = isset( $_POST['wpdt-user-envato-name'] ) 	  ? wp_unslash( $_POST['wpdt-user-envato-name'] ) : "";
									$wpdt_user_envato_api_key = isset( $_POST['wpdt-user-envato-api-key'] ) ? wp_unslash( $_POST['wpdt-user-envato-api-key'] ) : "";
									$wpdt_product_license_key = isset( $_POST['wpdt-product-license-key'] ) ? wp_unslash( $_POST['wpdt-product-license-key'] ) : "";

									$a = update_option( 'wpdt_envato_user_name'   , $wpdt_envato_user_name    );
									$b = update_option( 'wpdt_user_envato_api_key', $wpdt_user_envato_api_key );
									$c = update_option( 'wpdt_product_license_key', $wpdt_product_license_key );							

									$success_message = __( "Settings saved successful.", $this->plugin_name );
									$this->set_plugin_msg( $success_message, 0 );
								}

							}
						}
					break;
				}
			}

		}
	}

	/**
	 * Check if user can display settings
	 *
	 * @since    1.0.0
	 *
	 * @return    boolean
	 */

	public function user_can_view_settings( $allowed_settings_role = array() ){

		$canView_settings = false;

		if( is_user_logged_in() ){
			
			global $current_user, $wpdb;

			$user = get_userdata( $current_user->ID );

			$capabilities = $user->{$wpdb->prefix . 'capabilities'};

			if ( !isset( $wp_roles ) ){
			
				$wp_roles = new WP_Roles();
			}

			foreach ( $wp_roles->role_names as $role => $name ){

				if ( array_key_exists( $role, $capabilities ) ){

					if( !$canView_settings && in_array( $role, $allowed_settings_role ) ){

						$canView_settings = true;
					}

				}
			}
		}

		return $canView_settings;
	}

	/**
	 * Activate Product License
	 *
	 * @since    1.0.0
	 *
	 * @return    boolean
	 */

	public function wpdt_activate_license(){
		
		$success_msg = __( "License activated successful", $this->plugin_name );
		$fail_msg = __( "An error occured on license activation. Please try again.", $this->plugin_name );

		$return = array();
		$return['error'] = 1;
		$return['msg'] = $fail_msg;

		$envato_username 		= get_option('wpdt_envato_user_name') ? trim(get_option('wpdt_envato_user_name')) : "";
		$envato_secret_api_key 	= get_option('wpdt_user_envato_api_key') ? trim(get_option('wpdt_user_envato_api_key')) : "";
		$product_purchase_code 	= get_option('wpdt_product_license_key') ? trim(get_option('wpdt_product_license_key')) : "";
		$site_url 				= get_bloginfo( 'url' );

		if( $envato_username != "" && $envato_secret_api_key != "" && $product_purchase_code != "" ){
			
			$args = array(
				'action' 		=> 'activate-license',
				'plugin_name' 	=> $this->plugin_name,
				'env_username'	=> $envato_username,
				'env_key'		=> $envato_secret_api_key,
				'license_code' 	=> $product_purchase_code,
				'site'			=> $site_url
			);

			if( version_compare(phpversion(), '5.3.0') >= 0 ){

				$t = wpdt_update_plugin::get_instance();
				
				$response = $t::wpdt_call_service_api( $args );
			}
			else{

				$t = new wpdt_update_plugin;

				$response = $t->wpdt_call_service_api( $args );
			}

			if( false !== $response && $response->error == false && $response->verified_code ){

				update_option( 'wpdt_product_verified_code' , $response->verified_code );

				$return['error'] = 0;
				$return['msg'] = $success_msg;
				set_site_transient( 'update_plugins', '' );
				set_site_transient( 'plugin_slugs', '' );
			}
			else{

			}

		}

		$return = json_encode($return);

		print_r( $return );

		die();
	}

	/**
	 * Deactivate Product License
	 *
	 * @since    1.0.0
	 *
	 * @return    boolean
	 */

	public function wpdt_deactivate_license( $tp = '' ){

		$success_msg = __( "License deactivated successful", $this->plugin_name );
		$fail_msg = __( "An error occured on license deactivation. Please try again.", $this->plugin_name );

		$return = array();
		$return['error'] = 1;
		$return['msg'] = $fail_msg;

		$envato_username 		= get_option('wpdt_envato_user_name') ? trim(get_option('wpdt_envato_user_name')) : "";
		$envato_secret_api_key 	= get_option('wpdt_user_envato_api_key') ? trim(get_option('wpdt_user_envato_api_key')) : "";
		$product_purchase_code 	= get_option('wpdt_product_license_key') ? trim(get_option('wpdt_product_license_key')) : "";
		$site_url 				= get_bloginfo( 'url' );
		$verified_code 			= get_option('wpdt_product_verified_code') ? trim(get_option('wpdt_product_verified_code')) : "";

		if( $envato_username != "" && $envato_secret_api_key != "" && $product_purchase_code != "" && $verified_code != "" ){
			
			$args = array(
				'action' 		=> 'deactivate-license',
				'plugin_name' 	=> $this->plugin_name,
				'env_username'	=> $envato_username,
				'env_key'		=> $envato_secret_api_key,
				'license_code' 	=> $product_purchase_code,
				'site'			=> $site_url,
				'verified_code' => $verified_code
			);

			if( version_compare(phpversion(), '5.3.0') >= 0 ){

				$t = wpdt_update_plugin::get_instance();

				$response = $t::wpdt_call_service_api( $args );
			}
			else{

				$t = new wpdt_update_plugin;			

				$response = $t->wpdt_call_service_api( $args );
			}

			if( false !== $response ){
				
				if( $response->error ){

					switch( $response->etype ){
						case 'e1':
						break;
						case 'e2':
						case 'e3':
							update_option( 'wpdt_product_verified_code' , '' );
							set_site_transient( 'update_plugins', '' );
							set_site_transient( 'plugin_slugs', '' );
						break;
					}
				}
				else{

					update_option( 'wpdt_product_verified_code' , '' );
					set_site_transient( 'update_plugins', '' );
					set_site_transient( 'plugin_slugs', '' );

					$return['error'] = 0;
					$return['msg'] = $success_msg;
				}
			}
		}

		if( $tp != 'on_uninstall' ){
		
			$return = json_encode($return);

			print_r( $return );

			die();
		}
	}

	public function wp_dynamic_tables_add_action_links( $links ){

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'edit.php?post_type=wp_dynamic_tables&page=wp-dynamic-table-settings' ) . '">' . __( 'Settings', $this->plugin_name ) . '</a>'
			),
			$links
		);

		return $links;
	}

	public function wp_dynamic_tables_additional_tablelist_columns( $columns ){

		$new_columns = array();

		$new_columns['cb'] = $columns['cb'];
		$new_columns['id'] = __( 'ID', $this->plugin_name );
		$new_columns['title'] = $columns['title'];
		$new_columns['type'] = __( 'type', $this->plugin_name );
		$new_columns['shortcode'] = __( 'Shortcode', $this->plugin_name );
		$new_columns['date'] = __( 'Date created', $this->plugin_name );

		return $new_columns;
	}

	public function wp_dynamic_tables_additional_tablelist_columns_display( $column_name ){

		global $post;

		$table_type  = get_post_meta( $post->ID, 'table_type', true );

		$table_type = $table_type ? $table_type : __( 'none', $this->plugin_name );

		$table_type = ucfirst( $table_type );

	    switch ($column_name) {
		    case 'id':
		        echo $post->ID;
            break;
            case 'type':
		        echo $table_type;
            break;
            case 'shortcode':
		        echo '[wpdtable id="' . $post->ID . '"]';
            break;
	    	default:
	        break;
		}
	}

	public function wp_dynamic_tables_remove_view_link_cpt( $actions ) {

		global $current_screen, $post;
		
		if( $current_screen->post_type != 'wp_dynamic_tables' ) return $actions;
	    
		if( isset($actions['edit']) ){
		
		    $new_action = array(
		    	'edit' => $actions['edit'],
		    	'copy' => '<a href="' . admin_url('edit.php?post_type=wp_dynamic_tables&wpdt_dupl=' . $post->ID) . '" title="">' . __( 'Copy', $this->plugin_name ) . '</a>',
		    	'trash' => $actions['trash']
		    	);
		}
		else{

			$new_action = $actions;
		}

	    return $new_action;
	}

	public function wpdt_set_wpt_allowed_mimes( $mimes ){
	
		$mimes['xml'] = 'application/xml';
		return $mimes;
	}

	public function get_db_table_columns_data(){

		$r = array( 'error' => 0 );

		if( is_user_logged_in() ){

			if( isset( $_POST['dbdata'] ) ){

				$dbData = $_POST['dbdata'];

				if ( !isset( $dbData['nnc'] ) || !wp_verify_nonce( $dbData['nnc'], 'wpdt-settings-nonce-abcd' ) ){
			
					$r['msg'] = __( 'Not authorized action', $this->plugin_name );
				    $r['error'] = 1;
				    $r['html'] = '<span class="empty-msg">' . __( "No data inserted", $this->plugin_name ) . '</span>';
				}
				else{

					$dbData['query'] = str_replace( '\\', '', $dbData['query'] );

					if( intval( $dbData['extdb'] ) == 1 ){	// External db

						$mysql_external_dbname = trim($dbData['name']);
						$mysql_external_dbusername = trim($dbData['username']);
						$mysql_external_dbpassword = trim($dbData['pass']);
						$mysql_external_dbhost = trim($dbData['host']);
						$mysql_external_query = trim($dbData['query']);

						/* Open DB connection */

						$db_status = mysqli_connect($mysql_external_dbhost, $mysql_external_dbusername, $mysql_external_dbpassword);

						$r['$db_status'] = $db_status;

						if (!$db_status) {

						    $r['msg'] = __( 'Database connection failed', $this->plugin_name );
						    $r['error'] = 1;
						    $r['html'] = '<span class="empty-msg">' . __( "No data inserted", $this->plugin_name ) . '</span>';

						    /* Close DB connection */

							if ( is_object($db_status) && get_class($db_status) === 'mysqli') {

								mysqli_close($db_status);
							}
						}
						else{

							$db_select = mysqli_select_db( $db_status, $mysql_external_dbname );

							if (!$db_select) {

							    $r['msg'] = __( 'Database selection failed', $this->plugin_name );
							    $r['error'] = 1;
							    $r['html'] = '<span class="empty-msg">' . __( "No data inserted", $this->plugin_name ) . '</span>';

							    /* Close DB connection */

								if ( is_object($db_status) && get_class($db_status) === 'mysqli') {

									mysqli_close($db_status);
								}
							}
							else{

								$queryResults = mysqli_query( $db_status, $mysql_external_query );

								$queryData = array();

								while ( $row = mysqli_fetch_assoc($queryResults) ) {

								    array_push( $queryData, $row );
								}

								/* Close DB connection */

								if ( is_object($db_status) && get_class($db_status) === 'mysqli') {

									mysqli_close($db_status);
								}

								if( !empty( $queryData) ){

									$queryData = $this->get_mysql_results_to_wpdt_array( $queryData );

									$r['html'] = $this->wp_dynamic_tables_generate_file_table_columns_settings( $queryData );
									$r['msg'] = __( 'Ok', $this->plugin_name );
								}
								else{

									$r['html'] = '<span class="empty-msg">' . __( "No data inserted", $this->plugin_name ) . '</span>';
									$r['msg'] = __( 'No results found', $this->plugin_name );
							    	$r['error'] = 1;
								}

							}
						}
					}
					else{

						global $wpdb;
						
						$queryResults = $wpdb->get_results( $dbData['query'], ARRAY_A );

						if( $queryResults ){

							$queryResults = $this->get_mysql_results_to_wpdt_array( $queryResults );

							$r['html'] = $this->wp_dynamic_tables_generate_file_table_columns_settings( $queryResults );
							$r['msg'] = __( 'Ok', $this->plugin_name );
						}
						else{

							$r['msg'] = __( 'Query excecution failed', $this->plugin_name );
						    $r['error'] = 1;
						    $r['html'] = '<span class="empty-msg">' . __( "No data inserted", $this->plugin_name ) . '</span>';
						}
					}
				}
			}

		}
		else{

			$r['msg'] = __( 'Not authorized user', $this->plugin_name );
		    $r['error'] = 1;
		    $r['html'] = '<span class="empty-msg">' . __( "No data inserted", $this->plugin_name ) . '</span>';
		}

		$r = json_encode( $r );

		die( $r );
	}

	public function get_mysql_results_to_wpdt_array( $arr = array() ){

		$ret = $arr;

		if( !empty( $arr ) ){

			$row_num = 0;

			$rest_rows  = array();

			foreach ($arr as $k => $v ) {

				$col_num = 0;

				foreach ($v as $a => $b) {
					
					if( $col_num == 0 ){

						if( $row_num == 0 ){

							$rest_rows[ $row_num ] = array();
						}

						$rest_rows[ $row_num + 1 ] = array();
					}

					if( $row_num == 0 ){

						$rest_rows[ $row_num ][] = $a;
					}

					if( $col_num == 0 ){

						$rest_rows[ $row_num + 1 ] = array();
					}

					$rest_rows[ ($row_num + 1) ][] = $b;

					$col_num = $col_num + 1;
				}

				$row_num = $row_num + 1;
			}

			$ret = $rest_rows;
		}

		$ret = $this->wpdt_clean_empty_table_rows( $ret );

		return $ret;
	}

	public function get_url_file_table_columns_data(){

		$r = array();
		$r['error'] = 0;

		$url = $_POST['url'];
		$mime = $_POST['mime'];
		$path = str_replace( content_url(), WP_CONTENT_DIR, $url );

		switch( $mime ){
			case 'application/xml':

				$d = $this->xml_file_to_array( $path );
				
				$r['html'] = $this->wp_dynamic_tables_generate_file_table_columns_settings( $d );
			break;
			case 'text/csv':

				$d = $this->csv_file_to_array( $path );
				
				$r['html'] = $this->wp_dynamic_tables_generate_file_table_columns_settings( $d );

			break;
			case 'application/vnd.ms-excel':	// .xls
			case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':	// .xlsx
				
				$d = $this->excel_file_to_array( $path );
				
				$r['html'] = $this->wp_dynamic_tables_generate_file_table_columns_settings( $d );
			break;
			case 'application/vnd.oasis.opendocument.spreadsheet':

				$d = $this->ods_file_to_array( $path );
				
				$r['html'] = $this->wp_dynamic_tables_generate_file_table_columns_settings( $d );
			break;
			default:
				$r['error'] = 1;
				$r['error_id'] = 'invalid_mime';
				$r['msg'] = __( "Invalid mime type", $this->plugin_name );
				$r['html'] = '';
			break;
		}

		$r = json_encode( $r );

		die( $r );
	}

	public function csv_file_to_array( $src ){

		$csv = array();
		
		PHPExcel_Cell::setValueBinder( new PHPExcel_Cell_AdvancedValueBinder() );
		PHPExcel_Cell::setValueBinder( new WPDT_Cell_CSVValueBinder() );
		
		try {
		
		    $inputFileType = PHPExcel_IOFactory::identify($src);
		    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
		    $objPHPExcel = $objReader->load($src);
		
		} catch(Exception $e) {
		
		    die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
		}

		$sheet = $objPHPExcel->getActiveSheet();

		foreach ($sheet->getRowIterator() as $row) {
			
			$csv[ $row->getRowIndex() - 1 ] = array();

		    $cellIterator = $row->getCellIterator();
		    $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set

		    foreach ($cellIterator as $cell) {

		        if (!is_null($cell)) {

		            $csv[ $row->getRowIndex() - 1 ][] = $cell->getValue();
		        }
		        else{

		        	$csv[ $row->getRowIndex() - 1 ][] = '';
		        }
		    }
		}

		$objPHPExcel->disconnectWorksheets();
		unset($objPHPExcel);

		$csv = $this->wpdt_clean_empty_table_rows( $csv );

		return $csv;
	}

	public function xml_file_to_array( $src ){
		
		$xml = simplexml_load_file( $src );

		$cnt = 0;
		$first_row = array();
		$other_rows = array();
		$all_rows = array();

		if( file_exists( $src ) ){
		
			foreach ($xml as $key => $value) {
				
				$other_rows[$cnt] = array();

				foreach($value as $k => $v) {

					if( $cnt == 0 ){

						$first_row[] = $k;
					}

					$other_rows[$cnt][] = strip_tags( $value->$k->asXML() );
				}

				if( $cnt == 0 ){

					$all_rows[] = $first_row;
				}

				$all_rows[] = $other_rows[$cnt];
				$cnt = $cnt + 1;
			}
		}

		$all_rows = $this->wpdt_clean_empty_table_rows( $all_rows );

		return $all_rows;
	}

	public function ods_file_to_array( $src ){
		
		$ods = array();

		if( file_exists( $src ) ){

			$Reader = new SpreadsheetReader( $src );
		    
		    foreach ($Reader as $Row){       
		    
		        $ods[] = $Row;
		    }
		}

		$ods = $this->wpdt_clean_empty_table_rows( $ods );

	    return $ods;
	}

	public function excel_file_to_array( $src ){
		
		$xls = array();

		try {
		
		    $inputFileType = PHPExcel_IOFactory::identify($src);
		    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
		    $objReader->setReadDataOnly(true);
		    $objPHPExcel = $objReader->load($src);
		
		} catch(Exception $e) {
		
		    die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
		}
		
		$sheet = $objPHPExcel->getActiveSheet();
		$highestRow = $sheet->getHighestRow(); 
		$highestColumn = $sheet->getHighestColumn();

		for ($row = 1; $row <= $highestRow; $row++){

		    $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false);
		   
		    $xls[] = $rowData[0];
		}

		$objPHPExcel->disconnectWorksheets();

		unset($objPHPExcel);

		$xls = $this->wpdt_clean_empty_table_rows( $xls );

		return $xls;
	}

	public function wpdt_clean_empty_table_rows( $arr = array() ){

		$r = $arr;

		$arrLen = count( $arr );
		$empty_vals = array();

		foreach( $arr as $x=>$y ){

			if( is_array( $y ) ){

				foreach ( $y as $a => $b ) {
					
					if( trim( $b == '' ) ){

						if( !isset( $empty_vals[$a] ) ){

							$empty_vals[$a] = 1;
						}
						else{

							$empty_vals[$a] = $empty_vals[$a] + 1;
						}
					}
					else{

						$arr[$x][$a] = wpdt_json_escape( trim( $arr[$x][$a] ) );
					}
				}
			}
		}

		if( !empty($empty_vals) ){

			foreach ( $empty_vals as $key => $value) {
				
				 if( $value == $arrLen ){

				 	foreach( $arr as $x=>$y ){

				 		unset( $arr[$x][$key] );
					}

				}
			}
		}

		$r = $arr;

		return $r;
	}
}
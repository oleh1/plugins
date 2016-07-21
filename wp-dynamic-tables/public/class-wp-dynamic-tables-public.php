<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://wpdynamictables.com/
 * @since      1.0.0
 *
 * @package    WP_Dynamic_Tables
 * @subpackage WP_Dynamic_Tables/public
 */

/**
 *
 * @package    WP_Dynamic_Tables
 * @subpackage WP_Dynamic_Tables/public
 * @author     Your Name <email@example.com>
 */
class WP_Dynamic_Tables_Public {

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
	 * @param 	 string    $plugin_name       The name of the plugin.
	 * @param    string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Add custom style.
	 *
	 * @since    1.0.0
	 */
	public function custom_css(){

		$custom_css = get_option('wpdt-custom-css') ? trim( get_option('wpdt-custom-css') ) : "";
			
		if( $custom_css != "" ) {
		
			$output = "<style type='text/css'>" . $custom_css . "</style>";

			echo $output;
		}
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		global $post;

		if ( ( isset($post->post_content) && has_shortcode( $post->post_content, 'wpdtable' ) ) || is_active_widget( false, false, 'wpdt_widget', true ) ) {
		
			wp_enqueue_style( 'wpdt-c3-css', plugins_url() . '/wp-dynamic-tables/includes/c3_js/c3.min.css', array(), '0.4.10', 'all' );
			wp_enqueue_style( 'wpdt-data-tables-style', plugin_dir_url( __FILE__ ) . 'css/wpdt.jquery.dataTables.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-dynamic-tables-public.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		
		global $post;

		if ( ( isset($post->post_content) && has_shortcode( $post->post_content, 'wpdtable' ) ) || is_active_widget( false, false, 'wpdt_widget', true ) ) {

			wp_enqueue_script( 'wpdt-data-tables-script', plugins_url() . '/wp-dynamic-tables/includes/DataTables-1.10.6/media/js/jquery.dataTables.min.js', array( 'jquery' ), '1.10.6', false );
			wp_enqueue_script( 'wpdt-data-tables-responsive-script', plugins_url() . '/wp-dynamic-tables/includes/DataTables-1.10.6/extensions/Responsive/js/dataTables.responsive.min.js', array( 'wpdt-data-tables-script' ), '1.0.5', false );
			wp_enqueue_script( 'wpdt-data-tables-tools-script', plugins_url() . '/wp-dynamic-tables/includes/DataTables-1.10.6/extensions/TableTools/js/wpdt.dataTables.tableTools.js', array( 'wpdt-data-tables-script' ), '2.2.4', false );
			wp_enqueue_script( 'wpdt-d3-js', plugins_url() . '/wp-dynamic-tables/includes/d3_js/d3.min.js', array(), '3.5.5', false );
			wp_enqueue_script( 'wpdt-c3-js', plugins_url() . '/wp-dynamic-tables/includes/c3_js/c3.min.js', array( 'wpdt-d3-js' ), '0.4.10', false );
			wp_enqueue_script( $this->plugin_name . '_public', plugin_dir_url( __FILE__ ) . 'js/wp-dynamic-tables-public.js', array( 'wpdt-data-tables-tools-script' ), $this->version, false );

			$bsurl = "wp-dynamic-tables/includes/DataTables-1.10.6/media/swf/copy_csv_xls_pdf.swf";

			$localized_strings = array(
				'sSortAscending' => __( " - click/return to sort ascending", $this->plugin_name ),
				'sSortDescending' => __( " - click/return to sort descending", $this->plugin_name ),
				'sFirst' => __( "First", $this->plugin_name ),
				'sLast' => __( "Last", $this->plugin_name ),
				'sNext' => __( "Next", $this->plugin_name ),
				'sPrevious' => __( "Previous", $this->plugin_name ),
				"sEmptyTable" => __( "No data available in table", $this->plugin_name ),
				"sInfo" => __( "Showing _START_ to _END_ of _TOTAL_ entries", $this->plugin_name ),
				"sInfoEmpty" => __( "No entries to show", $this->plugin_name ),
				"sInfoFiltered" => __( " - filtering from _MAX_ records", $this->plugin_name ),
				"sLoadingRecords" => __( "Loading...", $this->plugin_name ),
				"sProcessing" => __( "Processing...", $this->plugin_name ),
				"sSearch" => __( "Search:", $this->plugin_name ),
				"sZeroRecords" => __( "No entries to show", $this->plugin_name ),
				"show_str"	=>  __( "Show", $this->plugin_name ),
				"entries_str" =>  __( "entries", $this->plugin_name ),
				"all_str" =>  __( "All", $this->plugin_name ),
				"five_str" => __( "5", $this->plugin_name ),
				"ten_str" => __( "10", $this->plugin_name ),
				"fifteen_str" => __( "15", $this->plugin_name ),
				"twenty_str" => __( "20", $this->plugin_name ),
				"twentyfive_str" => __( "25", $this->plugin_name ),
				"thirty_str" => __( "30", $this->plugin_name ),
				"forty_str" => __( "40", $this->plugin_name ),
				"fifty_str" => __( "50", $this->plugin_name ),
				"ahundred_str" => __( "100", $this->plugin_name ),
				"copy_button_text" => __( "Copy", $this->plugin_name ),
				"print_button_text" => __( "Print", $this->plugin_name ),
				"pdf_button_text" => __( "PDF", $this->plugin_name ),
				"excel_button_text" => __( "Excel", $this->plugin_name ),
				"csv_button_text" => __( "CSV", $this->plugin_name ),
				"print_view_title" => __( "Print view", $this->plugin_name ),
				"print_view_msg" => __( "Please use your browser's print function to print this table. Press escape when finished.", $this->plugin_name ),				
				"copy_view_title" => __( "Table copied", $this->plugin_name ),
				"copy_view_msg" => __( "Copied [rows_num] to the clipboard.", $this->plugin_name ),
				"lines_str_txt" => __( "rows", $this->plugin_name ),
				"line_str_txt" => __( "row", $this->plugin_name ),
				'tablestools_swf_url' => plugins_url( $bsurl ),
				"wpdt_ajaxurl"		=> admin_url('admin-ajax.php'),
				"cancel_str"	=> __( "Cancel", $this->plugin_name ),
				"save_str"	=> __( "Save", $this->plugin_name ),
				"create_str"	=> __( "Create", $this->plugin_name ),
				"remove_str"	=> __( "Remove", $this->plugin_name )
	        );
			
			wp_localize_script( $this->plugin_name . '_public', 'wpdt_public_str', $localized_strings );
		}
	}
	
	public function wp_dynamic_table_cpt() {
		
		$labels = array(
			'name'                => _x( 'Dynamic Tables', 'Post Type General Name', 'wp_dynamic_table' ),
			'singular_name'       => _x( 'Table', 'Post Type Singular Name', 'wp_dynamic_table' ),
			'menu_name'           => __( 'Dynamic Tables', 'wp_dynamic_table' ),
			'name_admin_bar'      => __( 'Dynamic Tables', 'wp_dynamic_table' ),
			'parent_item_colon'   => __( 'Parent Table:', 'wp_dynamic_table' ),
			'all_items'           => __( 'All Tables', 'wp_dynamic_table' ),
			'add_new_item'        => __( 'Add Table', 'wp_dynamic_table' ),
			'add_new'             => __( 'Add Table', 'wp_dynamic_table' ),
			'new_item'            => __( 'New Table', 'wp_dynamic_table' ),
			'edit_item'           => __( 'Edit Table', 'wp_dynamic_table' ),
			'update_item'         => __( 'Update Table', 'wp_dynamic_table' ),
			'view_item'           => __( 'View Table', 'wp_dynamic_table' ),
			'search_items'        => __( 'Search Table', 'wp_dynamic_table' ),
			'not_found'           => __( 'Not found', 'wp_dynamic_table' ),
			'not_found_in_trash'  => __( 'Not found in Trash', 'wp_dynamic_table' ),
		);
		$args = array(
			'label'               => __( 'Dynamic Table', 'wp_dynamic_table' ),
			'description'         => __( 'Table Description', 'wp_dynamic_table' ),
			'labels'              => $labels,
			'supports'            => array( 'title', ),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 20,
			'menu_icon'			  => plugins_url() . '/wp-dynamic-tables/includes/img/wp-dynamic-tables-icon.png',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
		);
		
		register_post_type( 'wp_dynamic_tables', $args );
	}

	public function get_sside_tdata(){

		$r = array();

		$request = $_POST;

		if( isset( $request['wpdt_id'] ) && intval( $request['wpdt_id'] ) > 0 ){

			$wpdt_id = $request['wpdt_id'];

			$isEditable = isset($request['editable']) && intval($request['editable']) == 1 ? 1 : 0;

			$hiddenColumns = isset($request['hc']) && trim($request['hc']) != '' ? explode(',', $request['hc']) : array();

			if( !empty( $hiddenColumns ) ){

				foreach ($hiddenColumns as $hck => $hcv) {
					
					$hiddenColumns[$hck] = intval($hcv);
				}
			}
			
			$enable_frontEdit = get_post_meta( $wpdt_id, 'enable_front_edit', true );
			$enable_frontEdit = $enable_frontEdit != '' ? intval( $enable_frontEdit ) : 0;

			$canEdit_roles = get_post_meta( $wpdt_id, 'can_edit_roles', true );
			$canEdit_roles = $canEdit_roles != '' && is_array($canEdit_roles) ? $canEdit_roles : array('administrator');

			$userCanEditTable = false;

			if( $enable_frontEdit && is_user_logged_in() ){

				global $current_user;

				if( $current_user->roles ){

					foreach ($current_user->roles as $key => $value) {
						
						if( $userCanEditTable == false && in_array( $value, $canEdit_roles ) ){

							$userCanEditTable = true;
						}
					}
				}
			}

			$isEditable = $isEditable == 1 && $userCanEditTable ? 1 : 0;

			$mysql_query_live_update = get_post_meta( $wpdt_id, 'wpdt_mysql_query_live_update', true );
			$mysql_query_live_update = $mysql_query_live_update != '' ? intval( $mysql_query_live_update ) : 0;

			$table_type  = get_post_meta( $wpdt_id, 'table_type', true );

			$isEditable = $isEditable && $table_type == 'mysql' && $mysql_query_live_update ? 0 : $isEditable;

			$table_data = get_post_meta( $wpdt_id, 'table_columns_data', true );
			$table_data = json_decode( $table_data );

			$table_settings = get_post_meta( $wpdt_id, 'table_columns_settings', true );
			$table_settings = json_decode( $table_settings, true );

			$cols_order_pre = $table_settings['default_order_num'];

			$tr3mp = $table_data;
			$table_data = array();
			$cols_order = array();

			foreach ($cols_order_pre as $x => $y) {

				if( $table_settings['wpdt_publish_col'][$y] != 'no' ){
					$cols_order[] = $y;
				}
			}

			foreach ($tr3mp as $key => $value) {
				
				$aaaCnt = 0;

				foreach ($cols_order as $x => $y) {
				
					if( !in_array($y, $hiddenColumns) ){

						$cell_type = $table_settings['wpdt_col_type'][$y];
						$cell_num_format = $table_settings['wpdt_col_num_format'][$y];
						$cell_currency_pos = $table_settings['wpdt_col_currency_pos'][$y];

						$vv = stripslashes($value[$y]);
						
						if( $cell_type != 'text' ){
						
							$vv = wpdt_format_number( $vv, $cell_type, $cell_num_format, $cell_currency_pos );
						}

						$table_data[$key][$aaaCnt] = $vv;
						$aaaCnt = $aaaCnt + 1;
					}
				}

				if( $isEditable ){

					array_unshift($table_data[$key], $key);
				}
			}
			
			unset( $table_data[0] );

			$table_data = array_values($table_data);
			if( isset($request['order']) && is_array($request['order']) && !empty($request['order']) ){
				
				$sort_arrays = array();
				$sortColumns = array();
				$sortColumns['column'] = array();
				$sortColumns['sort'] = array();

				foreach ($request['order'] as $key => $value) {
					
					$sortColumns['column'][] = $isEditable ? $value['column'] - 1 : $value['column'];
					$sortColumns['sort'][] = $value['dir'] == 'asc' ? SORT_ASC : SORT_DESC;
				}

				foreach ($table_data as $key => $value) {
					
					foreach ($sortColumns['column'] as $x => $y) {

						if( !isset($sort_arrays[$x]) ){

							$sort_arrays[$x] = array();
						}

						if( !isset($sort_arrays[$x]['data']) ){

							$sort_arrays[$x]['sort'] = array();
							$sort_arrays[$x]['data'] = array();

							$sort_arrays[$x]['sort'] = $sortColumns['sort'][$x];
						}
						$filter_col = $userCanEditTable ? $y : $y+1;
						$value[$y] = apply_filters( 'wpdt_cell_data', $value[$y], $wpdt_id, $key+1, $filter_col );
						$table_data[$key][$y] = $value[$y];
						$sort_arrays[$x]['data'][] = $value[$y];
					}
				}
//print_r($sort_arrays);
				$dynamicSort = array();

				foreach ($sort_arrays as $key => $value) {

					$dynamicSort[] = $sort_arrays[$key]['data'];
					$dynamicSort[] = $value['sort'];
				}

				$params = $dynamicSort;
				$params[] = &$table_data;

				$s = call_user_func_array('array_multisort', $params);
				
				$r['data'] = $s ? $params[ count( $params ) - 1 ] : $table_data;
			}
			else{

				$r['data'] = $table_data;
			}

			if( isset($request['search']) && is_array($request['search']) && !empty($request['search']) ){

				if( isset($request['search']['value']) ){

					$search = trim($request['search']['value']);

					if( $search != ''){

						$search = strtolower($search);

						$res = array();
						
						foreach ($r['data'] as $arr) {
						    
						    foreach ($arr as $kc => $value) {
						    	
						    	if( !$isEditable || $kc > 0 && $request['columns'][$kc+1]['searchable'] == 'true' ){
							    	
							    	$value = strtolower($value);
							        
							        if (preg_match('~'.preg_quote($search,'~').'~',$value)) {
							        
							            $res[] = $arr; 
							            break;
							        }
							    }
						    }
						}
						
						$r['data'] = $res;
					}
				}
			}

			if( $isEditable ){

				foreach ( $r['data'] as $k => $v ) {
					array_unshift($r['data'][$k], "<button class='wpdt-edit-row DTTT_button DTTT_button_text'>" . __( "Edit", $this->plugin_name ) . "</button><button class='wpdt-add-row DTTT_button DTTT_button_text'>" . __( "+", "wp-dynamic-tables" ) . "</button><button class='wpdt-remove-row DTTT_button DTTT_button_text'>" . __( "-", "wp-dynamic-tables" ) . "</button>");
				}
			}
		}

		$table_data_length = count( $r['data'] );

		$r['recordsTotal'] = $table_data_length;
		$r['recordsFiltered'] = $table_data_length;

		$r = json_encode( $r );

		die( $r );
	}

	public static function normalizeString ($str = ''){
	    $str = strip_tags($str); 
	    $str = preg_replace('/[\r\n\t ]+/', ' ', $str);
	    $str = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', ' ', $str);
	    $str = strtolower($str);
	    $str = html_entity_decode( $str, ENT_QUOTES, "utf-8" );
	    $str = htmlentities($str, ENT_QUOTES, "utf-8");
	    $str = preg_replace("/(&)([a-z])([a-z]+;)/i", '$2', $str);
	    $str = str_replace(' ', '-', $str);
	    $str = rawurlencode($str);
	    $str = str_replace('%', '-', $str);
	    return $str;
	}

	public function wpdt_custom_export_xls(){

		$request = $_POST;

		if ( isset( $request['wpdt_export_nonce'] ) && isset( $request['wpdt_nctit'] ) && wp_verify_nonce( $request['wpdt_export_nonce'], $request['wpdt_nctit'] ) ){

			$title = isset( $request['table_title'] ) ? urldecode( $request['table_title'] ) : "excel file " . date('Y-m-d');
			
			$filename = $this->normalizeString( $title ).'.xls';
			$tableId = isset( $request['tableId'] ) ? intval( $request['tableId'] ) : false;

			if( $tableId ){

				$table_data = get_post_meta( $tableId, 'table_columns_data', true );
				$table_settings = get_post_meta( $tableId, 'table_columns_settings', true );

				$table_data = json_decode( $table_data, true );
				$table_settings = json_decode( $table_settings, true );

				$cols_order_pre = $table_settings['default_order_num'];

				$data = array();

				foreach ($table_data as $a => $b) {

					foreach ($cols_order_pre as $x => $y) {
					
						if( $table_settings['wpdt_publish_col'][$y] != 'no' ){
					
							$data[$a][] = stripslashes( $table_data[$a][$x] );
						}
					}
				}

				if( !empty( $data ) ){

					$header_data = $data[0];
					
					array_splice($data, 0, 1);

					if( isset($request['order']) && trim($request['order']) != '' ){

						$yui = explode( ",", $request['order'] );
						$qwe = array();
						$frst = true;
						$cntrr = 0;

						foreach ($yui as $kuy => $vul) {
							
							if( $frst ){
								$frst = false;
							}
							else{

								$qwe[$cntrr]['column'] = $yui[$kuy-1];
								$qwe[$cntrr]['dir'] = $yui[$kuy];
								
								$cntrr++;

								$frst = true;
							}
						}

						$request['order'] = $qwe;

						$sort_arrays = array();
						$sortColumns = array();
						$sortColumns['column'] = array();
						$sortColumns['sort'] = array();

						foreach ($request['order'] as $key => $value) {
							$sortColumns['column'][] = intval($value['column']);
							$sortColumns['sort'][] = $value['dir'] == 'asc' ? SORT_ASC : SORT_DESC;
						}

						foreach ($data as $key => $value) {
							
							foreach ($sortColumns['column'] as $x => $y) {

								if( !isset($sort_arrays[$x]) ){
									$sort_arrays[$x] = array();
								}

								if( !isset($sort_arrays[$x]['data']) ){
									$sort_arrays[$x]['sort'] = array();
									$sort_arrays[$x]['data'] = array();
									$sort_arrays[$x]['sort'] = $sortColumns['sort'][$x];
								}

								$sort_arrays[$x]['data'][] = $value[$y];
							}
						}

						$dynamicSort = array();

						foreach ($sort_arrays as $key => $value) {
							$dynamicSort[] = $sort_arrays[$key]['data'];
							$dynamicSort[] = $value['sort'];
						}

						$params = $dynamicSort;
						$params[] = &$data;

						$s = call_user_func_array('array_multisort', $params);
						
						$data = $s ? $params[ count( $params ) - 1 ] : $data;
					}

					array_unshift($data, $header_data);

					require_once( WP_PLUGIN_DIR . "/wp-dynamic-tables/includes/PHPExcel_1.8.0_doc/Classes/PHPExcel.php" );

					$objPHPExcel = new PHPExcel();

					$objPHPExcel->getProperties()->setTitle($title);
					$objPHPExcel->getActiveSheet()->fromArray($data, NULL, 'A1');
					$objPHPExcel->getActiveSheet()->setTitle($title);
					$objPHPExcel->setActiveSheetIndex(0);

					header('Content-Type: application/vnd.ms-excel');
					header('Content-Disposition: attachment;filename="'.$filename. '"');
					header('Cache-Control: max-age=0');
					header('Cache-Control: max-age=1');
					header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
					header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
					header ('Cache-Control: cache, must-revalidate');
					header ('Pragma: public');

					$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
					$objWriter->save('php://output');
					exit;
				}
			}
		}
		die();
	}

	public function wpdt_add_table_row(){

		$r = array();
		$r['error'] = 1;

		$request = $_POST;

		if ( isset( $request['nnc'] ) && isset( $request['nnct'] ) && wp_verify_nonce( $request['nnc'], $request['nnct'] ) ){

			$tableId = isset($request['tid']) ? $request['tid'] : 'no';
			$rowId = isset($request['rid']) ? $request['rid'] : 'no';
			$insertBefore = isset($request['bfr']) ? $request['bfr'] : 0;
			$newRow = isset($request['newrow']) ? $request['newrow'] : 'no';

			if( $tableId != 'no' && $rowId != 'no' && $newRow != 'no' ){

				$enable_frontEdit = get_post_meta( $tableId, 'enable_front_edit', true );
				$enable_frontEdit = $enable_frontEdit != '' ? intval( $enable_frontEdit ) : 0;

				$userCanEditTable = false;

				if( $enable_frontEdit && is_user_logged_in() ){

					$canEdit_roles = get_post_meta( $tableId, 'can_edit_roles', true );
					$canEdit_roles = $canEdit_roles != '' && is_array($canEdit_roles) ? $canEdit_roles : array('administrator');

					global $current_user;

					if( $current_user->roles ){

						foreach ($current_user->roles as $key => $value) {
							
							if( $userCanEditTable == false && in_array( $value, $canEdit_roles ) ){

								$userCanEditTable = true;
							}
						}
					}
				}

				if( $userCanEditTable ){
				
					$table_data = get_post_meta( $tableId, 'table_columns_data', true );
					$table_data = json_decode( $table_data, true );

					$new_data = array();

					// print_r( $request );

					foreach ($table_data as $k => $v) {
						
						if( $rowId == $k ){

							if( $insertBefore ){

								$new_data[] = $newRow;
								$new_data[] = $v;
							}
							else{

								$new_data[] = $v;
								$new_data[] = $newRow;
							}

						}
						else{

							$new_data[] = $v;
						}
					}

					$new_data = json_encode( $new_data, JSON_UNESCAPED_UNICODE );

					update_post_meta( $tableId, 'table_columns_data', $new_data );
					
					$r['error'] = 0;
				}
			}
		}

		/*die();*/

		$r = json_encode( $r );

		die( $r );
	}

	public function wpdt_remove_table_row(){

		$r = array();
		$r['error'] = 1;

		$request = $_POST;

		if ( isset( $request['nnc'] ) && isset( $request['nnct'] ) && wp_verify_nonce( $request['nnc'], $request['nnct'] ) ){

			$tableId = isset($request['tid']) ? $request['tid'] : 'no';
			$rowId = isset($request['rid']) ? $request['rid'] : 'no';

			if( $tableId != 'no' && $rowId != 'no' ){

				$enable_frontEdit = get_post_meta( $tableId, 'enable_front_edit', true );
				$enable_frontEdit = $enable_frontEdit != '' ? intval( $enable_frontEdit ) : 0;

				$userCanEditTable = false;

				if( $enable_frontEdit && is_user_logged_in() ){

					$canEdit_roles = get_post_meta( $tableId, 'can_edit_roles', true );
					$canEdit_roles = $canEdit_roles != '' && is_array($canEdit_roles) ? $canEdit_roles : array('administrator');

					global $current_user;

					if( $current_user->roles ){

						foreach ($current_user->roles as $key => $value) {
							
							if( $userCanEditTable == false && in_array( $value, $canEdit_roles ) ){

								$userCanEditTable = true;
							}
						}
					}
				}

				if( $userCanEditTable ){
				
					$table_data = get_post_meta( $tableId, 'table_columns_data', true );
					$table_data = json_decode( $table_data, true );

					$new_data = array();

					foreach ($table_data as $k => $v) {
						
						if( $rowId != $k ){

							$new_data[] = $v;
						}
					}

					$new_data = json_encode( $new_data, JSON_UNESCAPED_UNICODE );

					update_post_meta( $tableId, 'table_columns_data', $new_data );
					
					$r['error'] = 0;
				}
			}
		}		

		$r = json_encode( $r );

		die( $r );
	}

	public function wpdt_fe_update_table(){

		$request = $_POST;
		
		$r = array();
		$r['error'] = 1;

		if ( isset( $request['nnc'] ) && isset( $request['nnct'] ) && wp_verify_nonce( $request['nnc'], $request['nnct'] ) ){

			$tableId = isset($request['tid']) ? $request['tid'] : 'no';
			$rowId = isset($request['rid']) ? $request['rid'] : 'no';
			$tnd = isset($request['rd']) ? $request['rd'] : 'no';
			$hiddenColumns = isset($request['hc']) && trim($request['hc']) != '' ? explode(',', $request['hc']) : array();

			$table_settings = get_post_meta( $tableId, 'table_columns_settings', true );
			$table_settings = json_decode( $table_settings, true );

			$newData = array();

			foreach( $table_settings['default_order_num'] as $h => $j ){

				$newData[] = $tnd[$j];
			}

			if( $tableId != 'no' && $rowId != 'no' && $newData != 'no' ){

				$enable_frontEdit = get_post_meta( $tableId, 'enable_front_edit', true );
				$enable_frontEdit = $enable_frontEdit != '' ? intval( $enable_frontEdit ) : 0;

				$userCanEditTable = false;

				if( $enable_frontEdit && is_user_logged_in() ){

					$canEdit_roles = get_post_meta( $tableId, 'can_edit_roles', true );
					$canEdit_roles = $canEdit_roles != '' && is_array($canEdit_roles) ? $canEdit_roles : array('administrator');

					global $current_user;

					if( $current_user->roles ){

						foreach ($current_user->roles as $key => $value) {
							
							if( $userCanEditTable == false && in_array( $value, $canEdit_roles ) ){

								$userCanEditTable = true;
							}
						}
					}
				}

				if( $userCanEditTable ){
				
					$table_data = get_post_meta( $tableId, 'table_columns_data', true );
					$table_data = json_decode( $table_data, true );

					$rowId = $rowId == 'header' ? 0 : $rowId;

					if( isset( $table_data[$rowId] ) ){

						if( empty( $hiddenColumns ) ){

							$table_data[$rowId] = $newData;
						}
						else{

							$t3mp = $table_data[$rowId];
							$table_data[$rowId] = array();
							$new_cnt = 0;

							foreach ($hiddenColumns as $hck => $hcv) {
								
								$hiddenColumns[$hck] = intval($hcv);
							}

							foreach ($t3mp as $k3 => $v3) {
								
								if( in_array( $k3, $hiddenColumns ) ){
									$table_data[$rowId][] = $v3;
								}
								else{
									
									$table_data[$rowId][] = $newData[$new_cnt];
									$new_cnt = $new_cnt + 1;
								}
							}
						}
					}

					foreach ($table_data as $k => $v) {
						
						foreach( $v as $x=>$y ){

							$table_data[$k][$x] = wpdt_json_escape( $y );
						}
					}					

					$table_data = json_encode($table_data);

					update_post_meta( $tableId, 'table_columns_data', $table_data );

					$r['error'] = 0;
				}
			}
		}

		$r = json_encode( $r );

		die( $r );
	}
}
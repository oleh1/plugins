<?php

/**
 * @link              http://wpdynamictables.com/
 * @since             1.0.0
 * @package           WP_Dynamic_Tables
 *
 * @wordpress-plugin
 * Plugin Name:       WP Dynamic Tables
 * Plugin URI:        http://wpdynamictables.com/
 * Description:       Lets you import tables from Excel, ODT, CVS, XML files and MySQL queries and publish them in the frontend in responsive mode. You can choose how to sort table , enable or disable columns, add charts for each Table, set width and rearrange their order.
 * Version:           1.0.8
 * Author:            WPCream
 * Author URI:        http://wpcream.com/
 * Text Domain:       wp-dynamic-tables
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function activate_wp_dynamic_tables() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-dynamic-tables-activator.php';
	WP_Dynamic_Tables_Activator::activate();
}
 
function deactivate_wp_dynamic_tables() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-dynamic-tables-deactivator.php';
	WP_Dynamic_Tables_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_dynamic_tables' );
register_deactivation_hook( __FILE__, 'deactivate_wp_dynamic_tables' );

require plugin_dir_path( __FILE__ ) . 'includes/class-wp-dynamic-tables-widget.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-dynamic-tables.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-dynamic-tables-update-plugin.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_wp_dynamic_tables() {

	$plugin = new WP_Dynamic_Tables();
	$plugin->run();

	if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
	
		$update_plugin = new wpdt_update_plugin;
		$update_plugin->wpdt_init_auto_update();
	}
}

run_wp_dynamic_tables();

function wpdt_display_chart( $values = array(), $id = 0, $chart_type = 'line' ){
	?>
	<div id="wpdt_chart_wrapper_id_<?php echo $id; ?>" class="wpdt_chart_wrapper" data-chart-type="<?php echo esc_attr( $chart_type ); ?>">

		<?php

		$columns = array();
		$cnt = 0;
		$p = array();

		foreach ( $values as $k => $v ) {

			$p[$cnt][] = $k;

			foreach ( $v as $a => $b ) {

				$p[$cnt][] = $b;
			}
			
			$cnt++;
		}

		foreach ( $p as $key => $value) {
			
			$columns[] = implode(',', $value);
		}

		foreach( $columns as $col ){

			echo '<input type="hidden" class="chart_hidden_calues" value="' . $col . '" />';
		}

		?>

	</div>
	<?php
}

function wpdt_json_escape($input, $esc_html = true) {
    $result = '';
    if (!is_string($input)) {
        $input = (string) $input;
    }

    $conv = array("\x08" => '\\b', "\t" => '\\t', "\n" => '\\n', "\f" => '\\f', "\r" => '\\r', '"' => '\\"', "'" => "\\'", '\\' => '\\\\');
    if ($esc_html) {
        $conv['<'] = '\\u003C';
        $conv['>'] = '\\u003E';
    }

    for ($i = 0, $len = strlen($input); $i < $len; $i++) {
        if (isset($conv[$input[$i]])) {
            $result .= $conv[$input[$i]];
        }
        else if ($input[$i] < ' ') {
            $result .= sprintf('\\u%04x', ord($input[$i]));
        }
        else {
            $result .= $input[$i];
        }
    }

    $result = str_replace( "\\", "", $result );

    return $result;
}

function wpdt_display_table( $id ){

	$ret = '';
	
	if( is_user_logged_in() && current_user_can( 'manage_options' ) ){

		$verify_url = admin_url( 'edit.php?post_type=wp_dynamic_tables&page=wp-dynamic-table-settings&tab=wpdt_product_license' );
		
		$non_active_html = 'Non verified "<strong>WP Dynamic Tables</strong>" plugin. <a href="'.$verify_url.'" title="" target="_blank">Verify now.</a>';
	}
	else{
		
		$non_active_html = 'Non verified "<strong>WP Dynamic Tables</strong>" plugin.';
	}

	$verified_code = get_option('wpdt_product_verified_code') ? trim(get_option('wpdt_product_verified_code')) : '';

	if( $verified_code == '' ){

		$ret = $non_active_html;
	}
	else{

		$table_type  = get_post_meta( $id, 'table_type', true );

		$mysql_query_live_update = get_post_meta( $id, 'wpdt_mysql_query_live_update', true );
		$mysql_query_live_update = $mysql_query_live_update != '' ? intval( $mysql_query_live_update ) : 0;

		if( $table_type == 'mysql' && $mysql_query_live_update ){

			global $wpdb;

			$plugin_admin = new WP_Dynamic_Tables_Admin( 'wp-dynamic-tables', '1.0.0' );

			$mysql_query = get_post_meta( $id, 'wpdt_mysql_query', true );

			$queryResults = $wpdb->get_results( $mysql_query, ARRAY_A );
			$table_values = $plugin_admin->get_mysql_results_to_wpdt_array( $queryResults );

			foreach($table_values as $key=>$value){

				$table_values[$key] = str_replace( '"', "'", $value );
				$table_values[$key] = str_replace( "\\", "", $table_values[$key] );
			}

			if( !empty( $table_values ) ){
			
				$table_values = json_encode( $table_values, JSON_UNESCAPED_UNICODE );
				
				update_post_meta( $id, 'table_columns_data', $table_values );
			}
		}

		$table_data = get_post_meta( $id, 'table_columns_data', true );
		$table_settings = get_post_meta( $id, 'table_columns_settings', true );

		$table_data = json_decode( $table_data, true );
		$table_settings = json_decode( $table_settings, true );
		
		$cols_order = array();
		$cols_order_pre = $table_settings['default_order_num'];

		$hidden_cols = array();

		foreach ($cols_order_pre as $x => $y) {

			if( $table_settings['wpdt_publish_col'][$y] != 'no' ){
				$cols_order[] = $y;
			}
			else{
				$hidden_cols[] = $y;
			}
		}

		if( $table_data && !empty( $table_data ) ){

			$data_length = count( $table_data );

			$table_title = trim( get_the_title( $id ) );

			$per_tables_rows = get_post_meta( $id, 'per_tables_rows', true );	
			$per_tables_rows = $per_tables_rows != '' ? intval( $per_tables_rows ) : 10;

			$enable_table_title = get_post_meta( $id, 'enable_table_title', true );
			$enable_table_title = $enable_table_title != '' ? intval( $enable_table_title) : 0;

			$enable_table_tools = get_post_meta( $id, 'enable_table_tools', true );
			$enable_table_tools = $enable_table_tools != '' ? intval( $enable_table_tools ) : 1;

			$enable_search = get_post_meta( $id, 'enable_search', true );
			$enable_search = $enable_search != '' ? intval( $enable_search ) : 1;

			$enable_sorting = get_post_meta( $id, 'enable_sorting', true );
			$enable_sorting = $enable_sorting != '' ? intval( $enable_sorting ) : 1;

			$enable_responsive = get_post_meta( $id, 'enable_responsive', true );
			$enable_responsive = $enable_responsive != '' ? intval( $enable_responsive ) : 0;

			$enable_word_wrap = get_post_meta( $id, 'enable_word_wrap', true );
			$enable_word_wrap = $enable_word_wrap != '' ? intval( $enable_word_wrap ) : 1;

			$enable_lazy_load = get_post_meta( $id, 'enable_lazy_load', true );
			$enable_lazy_load = $enable_lazy_load != '' ? intval( $enable_lazy_load ) : 0;

			$enable_chart = get_post_meta( $id, 'enable_chart', true );
			$enable_chart = $enable_chart != '' ? intval( $enable_chart ) : 0;

			$chart_position = get_post_meta( $id, 'chart_position', true );			
			$chart_position = $chart_position != '' ? ( $chart_position == 'below' || $chart_position == 'above' ? $chart_position : 'below' ) : 'below';
			
			$valid_chart_types = array( 'line', 'spline', 'area', 'pie', 'bar', 'scatter', 'area-spline', 'step', 'area-step' );
			$chart_type = get_post_meta( $id, 'chart_type', true );
			$chart_type = in_array( $chart_type, $valid_chart_types ) ? $chart_type : 'line';

			$enable_frontEdit = get_post_meta( $id, 'enable_front_edit', true );
			$enable_frontEdit = $enable_frontEdit != '' ? intval( $enable_frontEdit ) : 0;

			$enable_frontEdit = $enable_frontEdit && $table_type == 'mysql' && $mysql_query_live_update ? 0 : $enable_frontEdit;

			$enable_lazy_load = $enable_frontEdit || $enable_lazy_load ? 1 : 0;

			$canEdit_roles = get_post_meta( $id, 'can_edit_roles', true );
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

			$y_axis_data = array();

			$table_classes = array();

			if( $enable_chart ){

				$xAxisId = 0;
				$yAxis_ids = array();

				foreach ($cols_order as $x => $y) {

					if( $table_settings['wpdt_publish_col'][$y] != 'no' && $table_settings['wpdt_chart_enable'][$y] == 'yes' ){

						$yAxis_ids[] = intval( $table_settings['default_order_num'][$y] );
					}
				}

				foreach ( $cols_order as $e => $r) {

					if( !isset( $table_data[0][$e] ) || $table_data[0][$e] == '' ){

						$table_data[0][$e] = __( 'Column', 'wp-dynamic-tables' ) . '_' . ( $e + 1 );
					}
				}

				$y_axis_cnt = 0;

				if( !empty($yAxis_ids) ){
					foreach ( $yAxis_ids as $ff => $gg) {

						$slg = '';

						foreach ( $table_data as $fa => $gb ) {

							if( $fa == 0 ){

								$slg = $gb[$gg];
							}

							$y_axis_data[ $slg ][] = preg_replace("/([^0-9\\.])/i", "", $gb[$gg]);
						}
					}
				}
			}

			$table_attributers = '';
			if( !( get_post_meta( $id, 'paginav_rows', true ) ) ) {
				$paginav_rows = 5;
			} else {
				$paginav_rows = get_post_meta( $id, 'paginav_rows', true );
			}
			 
			if( intval($data_length) > $paginav_rows ) {

				$table_attributers .= ' data-page-length="' . esc_attr( $per_tables_rows ) . '" ';
				$table_attributers .= ' data-paging="true" ';
				$table_attributers .= ' data-length-change="true" ';
				$table_attributers .= ' data-length-menu="[ 5, 10, 15, 20, 25, 30, 40, 50, 100 ]" ';
			}
			else{

				$table_attributers .= ' data-paging="false" ';
				$table_attributers .= ' data-b-info="false" ';
			}

			$table_attributers .= ' data-table-title="' . esc_attr( $table_title ) . '" ';

			if( $enable_table_tools ){
				
				$table_attributers .= ' data-show-tools="true" ';
			}

			if( $enable_search ){
				
				$table_attributers .= ' data-show-search="true" ';
			}

			if( $userCanEditTable ){

				$table_attributers .= ' data-editable="true" ';
				$table_classes[] = 'wpdt-editable';
			}

			if( !empty( $hidden_cols ) ){

				$table_attributers .= ' data-wpdt-hc="'.implode(',', $hidden_cols).'" ';
			}

			$sort_columns_attr = '';

			if( $enable_sorting ){

				$y_cnt = 0;

				foreach ($cols_order as $x => $y) {

					if( $table_settings['wpdt_publish_col'][$y] != 'no' && $table_settings['wpdt_col_def_sort'][$y] != "none" ){

						$sort_columns_attr .= '[ '.( $userCanEditTable ? ($y_cnt+2) : $y_cnt ).', &quot;'.$table_settings['wpdt_col_def_sort'][$y].'&quot; ]';
					}

					$y_cnt = $y_cnt + 1;
				}

				$sort_columns_attr = str_replace("][", "], [", $sort_columns_attr);

				$table_attributers .= ' data-order="['. $sort_columns_attr . ']" ';
			
			}
			else{

				$table_attributers .= ' data-b-sort="false" ';
			}

			if( $enable_responsive ){

				$table_attributers .= ' data-responsive="true" ';
			}
			else{
				$table_classes[] = 'wpdt-non-responsive';
			}

			$table_attributers .= ' data-scroll-x="true" ';

			if( $enable_word_wrap ){

				$table_classes[] = 'wpdt-word-wrap';
			}

			if( $enable_lazy_load ){
				
				$table_attributers .= ' data-lazy-load="true" ';
				$table_attributers .= ' data-epp="' . $per_tables_rows . '" ';
			}

			if( $enable_lazy_load || $userCanEditTable || $enable_table_tools ){

				$table_attributers .= ' data-wpdt-id="' . $id . '" ';
			}

			if( $userCanEditTable ){
				$seach_exclude = array(0, 1);
			}
			else{

				$seach_exclude = array();
			}

			if( $enable_search ){

				foreach ($cols_order as $x => $y) {				
					
					if( $table_settings['wpdt_col_exclude_search'][$y] == 'yes' ){
						
						if( $userCanEditTable ){

							$seach_exclude[] = $x + 2;
						}
						else{

							$seach_exclude[] = $x;
						}
					}
				}

				if( !empty($seach_exclude) ){

					$table_attributers .= ' data-wpdt-s-excl="[' . implode( ',', $seach_exclude ) . ']" ';
				}
			}

			$table_attributers .= ' data-wpdt-columns-num="' . count( $cols_order ) . '" ';

			ob_start();
			
			if( $enable_table_title ){
			?>

			<h3><?php echo $table_title; ?></h3>

			<?php } ?>

			<?php

			if( $enable_chart && $chart_position == "above" && !empty($y_axis_data) ){

				wpdt_display_chart( $y_axis_data, $id, $chart_type );
			}

			?>

			<div class="wpdt-outs">
			
				<table class="wpdt-ins display <?Php echo implode( " ", $table_classes ); ?>" cellpadding="0" <?php echo $table_attributers; ?>>

					<?php

					if( $enable_table_tools || $userCanEditTable ){ 

						$unq = uniqid();
						$nonce_title = 'wpdt-export-nonce' . '-'. $unq;
						$export_nonce = wp_create_nonce( $nonce_title );
						?>						
						<input type="hidden" class="wpdt_export_nonce" name="<?php echo $nonce_title; ?>" value="<?php echo $export_nonce; ?>" />
						<?php
					}
					
					$cnt = 0;

					foreach ($table_data as $key => $value) {

						if( !$enable_lazy_load || $enable_lazy_load & $cnt == 0 ){

							if( $cnt == 0 ){
								?><thead><?php
							}
							elseif( $cnt == 1 ){
								?><tbody><?php
							}

							?>
							
							<tr>
								<?php

								foreach ($cols_order as $x => $y) {

									if( $table_settings['wpdt_publish_col'][$y] != 'no' ){

										$cell_order_attr = '';
										$cell_width = '';
										$cell_width_attr = '';

										$cell_type = $table_settings['wpdt_col_type'][$y];
										$cell_num_format = $table_settings['wpdt_col_num_format'][$y];
										$cell_currency_pos = $table_settings['wpdt_col_currency_pos'][$y];

										switch( $table_settings['wpdt_col_width'][$y] ){
											case 'custom':
												switch( $table_settings['wpdt_col_width_type'][$y] ){
													case 'pc':
														$cell_width = $table_settings['wpdt_col_width_val'][$y] . '%';
													break;
													case 'px':
														$cell_width = $table_settings['wpdt_col_width_val'][$y] . 'px';
													break;
												}
											break;
										}

										if( $cell_width != '' ){

											$cell_width_attr = ' style="width:' . esc_attr( $cell_width ) . ';" ';
										}

										$responsive_class = "";
										$cell_classes = "";

										if( $enable_responsive ){
											
											if( $table_settings['wpdt_col_disable_tablets'][$y] == 'yes' ){

												if( $table_settings['wpdt_col_disable_mobiles'][$y] == 'yes' ){

													$responsive_class .= 'desktop';
												}
												else{

													$responsive_class .= ' desktop mobile-l mobile-p ';
												}
											}
											elseif( $table_settings['wpdt_col_disable_mobiles'][$y] == 'yes' ){

												$responsive_class .= ' min-tablet ';
											}

											if( $responsive_class != '' ){
											
												$cell_classes = ' class="' . trim( $responsive_class ) . '" ';
											}							
										}

										if( $cnt == 0 ){

											if( $x == 0 && $userCanEditTable ){
												echo '<th class="wpdt-not-sort wpdt-not-print"><button class="wpdt-edit-header-row DTTT_button DTTT_button_text">' . __( "Edit", "wp-dynamic-tables" ) . '</button></th>';
												echo '<th class="wpdt-not-sort wpdt-not-print wpdt-not-visible"><i>ID</i></th>';
											}

											?>
											<th <?php echo $cell_width_attr; ?> <?php echo $cell_order_attr; ?> <?php echo $cell_classes; ?>><?php echo stripslashes( $value[$y] ); ?></th>
											<?php
										}
										else{

											if( $x == 0 && $userCanEditTable ){
												echo '<td class="wpdt-not-print"><button class="wpdt-edit-row DTTT_button DTTT_button_text">' . __( "Edit", "wp-dynamic-tables" ) . '</button><button class="wpdt-add-row DTTT_button DTTT_button_text">' . __( "+", "wp-dynamic-tables" ) . '</button><button class="wpdt-remove-row DTTT_button DTTT_button_text">' . __( "-", "wp-dynamic-tables" ) . '</button></td>';
												echo '<td class="wpdt-not-print wpdt-not-visible">'.$key.'</td>';
											}

											$formatedDataDisplay = isset($value[$y]) && $value[$y] != null ? stripslashes( $value[$y] ) : '';
											
											if( $cell_type != 'text' ){

												$formatedDataDisplay = wpdt_format_number( $formatedDataDisplay, $cell_type, $cell_num_format, $cell_currency_pos );
											}
											
											$formatedDataDisplay = apply_filters( 'wpdt_cell_data', $formatedDataDisplay, $id, $key, $x+1 );

											?>
											<td <?php echo $cell_width_attr; ?> <?php echo $cell_order_attr; ?> <?php echo $cell_classes; ?>><?php echo $formatedDataDisplay; ?></td>
											<?php
										}
									}
								}

								?>
							</tr>

							<?php

							$cnt = $cnt + 1;

							if( $cnt == 1 ){
								?></thead><?php
							}
							elseif( $cnt == $data_length ){
								?></tbody><?php
							}
						}
					}
					?>
				</table>
			
			</div>
			
			<?php

			if( $enable_chart && $chart_position == "below" && !empty($y_axis_data) ){

				wpdt_display_chart( $y_axis_data, $id, $chart_type );
			}

			?>
			
			<?php

			$ret = ob_get_contents();

			ob_clean();
		}

	}

	return $ret;
}

function wpdt_cleanNum($num){

	$r = $num;

	$dot_pos = strpos( $num, '.' );
	$comma_pos = strpos( $num, ',' );

	if( $dot_pos !== false || $comma_pos !== false ){

		if( $dot_pos === false && $comma_pos !== false ){

			$expl = explode(',', $num);

			if( count( $expl ) == 2 ){
				
				if( strlen( $expl[1] ) < 3 ){

					$num = str_replace(',', '.', $num);
				}
				else if( strlen( $expl[1] ) > 3 ){

					$num = str_replace(',', '.', $num);
				}
				else{

					$num = str_replace(',', '', $num);
				}
			}
		}
		else if( $dot_pos !== false && $comma_pos === false ){

			$expl = explode('.', $num);

			if( count( $expl ) == 2 ){
				
				if( strlen( $expl[1] ) == 3 ){
					
					$num = str_replace('.', '', $num);
				}
			}
			else{

				$num = str_replace('.', '', $num);
			}
		}
		else{
			
			if( $dot_pos > $comma_pos ){

				$num = str_replace(',', '', $num);
			}
			else{

				$num = str_replace('.', '', $num);
				$num = str_replace(',', '.', $num);
			}
		}

		$r = $num;
	}
	else{

		$r = round($num);
	}

	return $r;
}

function wpdt_currencyExtractString( $string ){

	preg_match("/([^0-9.,]*)([0-9.,]*)([^0-9.,]*)/", $string, $matches);
	// print_r($matches);

	return $matches;

	// return preg_replace("/([^0-9\\.])/i", "", $v);
	// return preg_replace("/[^0-9]/", '', $v);

	// return (double)$v;

	// return number_format( $v, 5 );
	// return $v;
}

function wpdt_format_number( $formatedDataDisplay, $cell_type, $cell_num_format, $cell_currency_pos ){

	$currency_symbol = '';

	$formatedDataDisplay_temp = wpdt_currencyExtractString( $formatedDataDisplay );

	$formatedDataDisplay = $formatedDataDisplay_temp[2];

	if( $cell_type == 'currency' ){
		$currency_symbol = $formatedDataDisplay_temp[1];
	}

	$a = $formatedDataDisplay;

	$formatedDataDisplay = wpdt_cleanNum($formatedDataDisplay);

	$b = $formatedDataDisplay;

	switch( $cell_num_format ){
		case 2:
			$formatedDataDisplay = str_replace('.', ',', $formatedDataDisplay);
		break;
		case 3:
			$thousandSymbol = ',';
			$decimalSymbol = '.';
			$expl = explode( $decimalSymbol, $formatedDataDisplay );
			$expl[0] = number_format( $expl[0], 0, '', $thousandSymbol );
			$formatedDataDisplay = implode( $decimalSymbol, $expl );
		break;
		case 4:
			$formatedDataDisplay = str_replace('.', ',', $formatedDataDisplay);
			$thousandSymbol = '.';
			$decimalSymbol = ',';
			$expl = explode( $decimalSymbol, $formatedDataDisplay );
			$expl[0] = number_format( $expl[0], 0, '', $thousandSymbol );
			$formatedDataDisplay = implode( $decimalSymbol, $expl );
		break;
	}

	if( $currency_symbol != '' ){
		switch( $cell_currency_pos ){
			case 'after':
				$formatedDataDisplay = $formatedDataDisplay . $currency_symbol;
			break;
			case 'before':
			default:
				$formatedDataDisplay = $currency_symbol . $formatedDataDisplay;
			break;
		}
	}

	return $formatedDataDisplay;
}
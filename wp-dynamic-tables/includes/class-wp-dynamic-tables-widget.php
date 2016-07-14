<?php
class Wpdt_Widget extends WP_Widget {

	protected $plugin_name;

	function __construct() {

		$this->plugin_name = 'wp-dynamic-tables';

		parent::__construct(
			'Wpdt_Widget', // Base ID
			__('WP Dynamic Tables', 'text_domain'), // Name
			array( 'description' => __( 'Select and add a table in your sidebar.', $this->plugin_name ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
	
     	echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {

			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		
		if( isset( $instance['wpdt_table'] ) && $instance['wpdt_table'] != 'none' ){


			$table_html = wpdt_display_table( $instance['wpdt_table'] );

			echo $table_html;
		}

		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		if ( isset( $instance[ 'wpdt_table' ] ) ) {
		
			$table_id = $instance[ 'wpdt_table' ];
		}
		else {

			$table_id = 'none';
		}

		?>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'wpdt_table' ); ?>" style="margin-bottom:5px;display:block;"><?php _e( 'Select table:', $this->plugin_name ); ?></label>
			<select id="<?php echo $this->get_field_id( 'wpdt_table' ); ?>" name="<?php echo $this->get_field_name( 'wpdt_table' ); ?>" class="widefat">
				<option value="none" <?php if( $table_id == 'none' ){ echo ' selected="selected" ';} ?>></option>

				<?php 

				$args = array(
				  'post_type' => 'wp_dynamic_tables',
				  'post_status' => 'publish'
				);
				
				$tables_list = get_posts( $args );

				foreach ( $tables_list as $table ) {
					
					$tid = $table->ID;
					$ttitle = $table->post_title;
					?>
						<option value="<?php echo $tid; ?>" <?php if( $table_id == $tid ){ echo ' selected="selected" ';} ?>><?php echo $ttitle; ?></option>
					<?php			
				}

				?>

			</select>
		</p>
		
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		
		$instance = array();		
		$instance['wpdt_table'] = ( ! empty( $new_instance['wpdt_table'] ) ) ? strip_tags( $new_instance['wpdt_table'] ) : '';

		return $instance;
	}

}
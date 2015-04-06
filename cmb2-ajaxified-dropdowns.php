<?php

/*
Plugin Name:	CMB2 Ajaxified Field ( Example )
Plugin URI:		http://www.webdevstudios.com
Description: 	An example plugin, not for production use.
Author: 		Jerry Wood Jr. ( aka. Jay )
Version:		1.0
Author URI:		http://www.webdevstudios.com
*/

class CMB2_Ajaxified_Example {
	const VERSION = '1.0';

	protected $prefix = 'ajaxified_';

	public function hooks() {
		add_action( 'cmb2_init', array( $this, 'cmb2_fields' ) );
		add_filter( 'cmb2_enqueue_js', array( $this, 'cmb2_scripts' ) );
		add_action( 'wp_ajax_get_colors', array( $this, 'return_colors' ) );
	}

	public function cmb2_fields() {

		$field = new_cmb2_box( array(
			'id'           => $this->prefix . 'metabox',
			'title'        => __( 'Ajax Dropdowns', 'ajaxified' ),
			'object_types' => array( 'post' ),
			'context'      => 'normal',
			'priority'     => 'high',
			'show_names'   => true,
		) );

		$field->add_field( array(
			'name'    => __( 'Vehicles', 'ajaxified' ),
			'desc'    => __( 'Select a vehicle.', 'ajaxified' ),
			'id'      => $this->prefix . 'vehicle',
			'type'    => 'select',
			'options' => array(
				'ferrari' => __( 'Ferrari', 'ajaxified' ),
				'porsche' => __( 'Porsche', 'ajaxified' ),
			)
		) );

		$field->add_field( array(
			'name'    => __( 'Color', 'ajaxified' ),
			'desc'    => __( 'Select a Color for this vehicle.', 'ajaxified' ),
			'id'      => $this->prefix . 'color',
			'type'    => 'select',
			'options' => $this->set_option(),
		) );
	}

	public function set_option() {
		global $post;

		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : false;
		if( false == $post_id && isset( $post->ID )){
			$post_id = $post->ID;
		}
		$current_vehicle = '';
		if( $post_id ) {
			$current_vehicle = get_post_meta( $post_id, $this->prefix . 'vehicle', 1 );
		}
		$current_vehicle = ! empty( $current_vehicle ) ? $current_vehicle : 'ferrari';
		return $this->get_colors( $current_vehicle );
	}

	public function get_colors( $vehicle = '' ) {

		if ( empty( $vehicle ) ) {
			return '';
		}

		$vehicles = array(
			'ferrari' => array(
				'red'  => __( 'RED', 'ajaxified' ),
				'blue' => __( 'BLUE', 'ajaxified' ),
			),
			'porsche' => array(
				'yellow' => __( 'YELLOW', 'ajaxified' ),
				'silver' => __( 'SILVER', 'ajaxified' ),
			),
		);

		return array_key_exists( $vehicle, $vehicles ) ? $vehicles[ $vehicle ] : false;
	}

	/**
	 * Use CMB2 filter to load our JavaScript
	 * when CMB loads his/hers.
	 *
	 * @param $return
	 *
	 * @return mixed
	 */
	public function cmb2_scripts( $return ) {

		wp_enqueue_script( 'ajaxified_dropdown', plugins_url( 'main.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );
		return $return;
	}

	public function return_colors() {
		$value = $_POST[ 'value' ];
		$safe_value = esc_attr( $value );

		$colors = $this->get_colors( $safe_value );
		if( ! $colors ){
			wp_send_json_error( array( 'msg' => 'Value inaccessible') );
		}

		$output = '';
		foreach( $colors as $color_value => $color_name ){
			$output .= sprintf( "<option value='%s'>%s</option>", $color_value, $color_name );
		}

		if( ! empty( $output ) ){
			wp_send_json_success( $output );
		}

		wp_send_json_error();
	}


}

$GLOBALS['CMB2_Ajaxified_Example'] = new CMB2_Ajaxified_Example();
$GLOBALS['CMB2_Ajaxified_Example']->hooks();
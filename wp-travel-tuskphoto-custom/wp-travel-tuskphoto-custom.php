<?php
/**
 * Plugin Name: WP Travel Tuskphoto Custom
 * Plugin URI: http://wptravel.io/
 * Description: This is the custom plugin developed to add the option for selecting and displaying additional currencies at front-end.
 * Version: 0.0.1
 * Author: WEN Solutions
 * Author URI: http://wptravel.io/downloads/
 * Requires at least: 4.4
 * Requires PHP: 5.5
 * Tested up to: 5.2
 *
 * Text Domain: wp-travel-tuskphoto-custom
 *
 * @package wp-travel-tuskphoto-custom
 * @author WenSolutions
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Function to include all the hooks and load them when plugins loaded.
 *
 * @return void
 */
function wp_travel_tuskphoto_custom_include_hooks() {

	// Styles and scripts.
	add_action( 'admin_enqueue_scripts', 'wp_travel_tuskphoto_custom_admin_styles' );

	// Included files.
	wp_travel_tuskphoto_custom_required_files();
}
add_action( 'plugins_loaded', 'wp_travel_tuskphoto_custom_include_hooks' );

/**
 * Admin scripts and styles
 *
 * @return void
 */
function wp_travel_tuskphoto_custom_admin_styles() {
	// Scripts.
	wp_register_script( 'wp_travel_tuskphoto_custom_admin_scripts', plugin_dir_url( __FILE__ ) . 'assets/admin-scripts.js', array(), '1.0.0', true );
	wp_enqueue_script( 'wp_travel_tuskphoto_custom_admin_scripts' );
}


/**
 * Function to include all the required files.
 *
 * @return void
 */
function wp_travel_tuskphoto_custom_required_files() {
	require_once dirname( __FILE__ ) . '/functions.php';
	// require_once dirname( __FILE__ ) . '/inc/public-single-itinerary.php';
	require_once dirname( __FILE__ ) . '/inc/admin-template-functions.php';
	require_once dirname( __FILE__ ) . '/inc/admin-price-tab.php';
}

<?php
/*
Plugin Name: Yourpay Payment Platform for WooCommerce
Plugin URI: http://www.yourpay.io
Description: Full WooCommerce payment gateway for VISADankort, VISA and Mastercards.
Version: 4.0.13
Author: Yourpay
Author URI: http://www.yourpay.io/
Text Domain: yourpay.io
Domain Path: /language
 */

if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}
define( 'YOURPAY__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'YOURPAY__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
require_once( YOURPAY__PLUGIN_DIR . 'woocommerce.php' );
require_once( YOURPAY__PLUGIN_DIR . 'yourpay.php' );
require_once( YOURPAY__PLUGIN_DIR . 'sdk.php' );
require_once( YOURPAY__PLUGIN_DIR . 'template.php' );
require_once( YOURPAY__PLUGIN_DIR . 'syncronize.php' );

add_action( 'init', array( 'yourpay', 'init' ) );
add_action( 'init', array( "WC_Yourpay2_0", 'init' ) );
function my_plugin_activate() {
	add_option( 'Activated_Plugin', 'yourpay' );

    if (! wp_next_scheduled ( 'yourpay_syncronize_hook')) {
        wp_schedule_event( time(), 'hourly', 'yourpay_syncronize_hook');
    }
}
add_action( 'yourpay_syncronize_hook', 'yourpay_syncronize_products' );

register_activation_hook( __FILE__, 'my_plugin_activate' );
function load_plugin() {
	if ( is_admin() && get_option( 'Activated_Plugin' ) == 'yourpay' ) {
		delete_option( 'Activated_Plugin' );
		exit( wp_redirect( admin_url( 'admin.php?page=yourpay_admin' ) ) );
	}
}
register_deactivation_hook( __FILE__, 'yourpay_syncronization_deactivation' );
function yourpay_syncronization_deactivation() {
    wp_clear_scheduled_hook( 'yourpay_syncronize_hook' );
}

add_action( 'init', 'myplugin_load_textdomain' );
function myplugin_load_textdomain() {
  load_plugin_textdomain( 'yourpay', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'admin_init', 'load_plugin' );

add_action( 'admin_init', array("yourpay","register_settings") );

add_action( 'admin_notices', array("yourpay","admin_notices_not_activated"));

add_action( 'wp_ajax_login', array("yourpay","yourpay_ajax_login") );

add_action( 'wp_ajax_login', array("yourpay","yourpay_ajax_create_account") );

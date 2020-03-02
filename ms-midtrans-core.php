<?php
/*
Plugin Name: MS Midtrans Core
Description: WooCommerce Payment method using Midtrans Core API | Support: 8:00am - 4:00pm Email: tom.wpdev@gmail.com 
Author: Minha Studio
Version: 1.0.0
Author URI: tompradana.wordpress.com
*/

if ( !function_exists( 'add_action' ) ) {
	exit;
}

// constant
$version = '1.0.0';
define( 'MS_MIDTRANS_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'MS_MIDTRANS_CORE_ENV', 'staging' );
if ( MS_MIDTRANS_CORE_ENV === 'staging' ) {
	$version = time();
}
define( 'MS_MIDTRANS_CORE_VERSION', $version );

// includes
include( MS_MIDTRANS_CORE_DIR . '/includes/classes/class.midtranscore-va.php' );
include( MS_MIDTRANS_CORE_DIR . '/includes/functions.php' );

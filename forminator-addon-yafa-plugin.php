<?php
/**
 * Plugin Name: Yet Another Forminator Addon
 * Version: 1.0-ALPHA.1
 * Description: Allow Forminator edit / resume submissions
 * Author: WPMU DEV
 * Author URI: http://premium.wpmudev.org
 * Text Domain: external_forminator
 * Domain Path: /languages/
 */

//Direct Load
define( 'FORMINATOR_ADDON_YAFA_VERSION', '1.0' );

/**
 * Make FORM with ID 260 have this addon auto connected
 * it should go on form settings or any persistent storage you want
 */
define( 'FORMINATOR_ADDON_YAFA_AUTO_CONNECT_FORM_ID', 260 );

function forminator_addon_yafa_url() {
	return trailingslashit( plugin_dir_url( __FILE__ ) );
}

function forminator_addon_yafa_dir() {
	return trailingslashit( dirname( __FILE__ ) );
}

function forminator_addon_yafa_assets_url() {
	return trailingslashit( forminator_addon_yafa_url() . '/addons/yafa/assets' );
}

add_action( 'forminator_addons_loaded', 'load_forminator_addon_yafa' );
function load_forminator_addon_yafa() {
	require_once dirname( __FILE__ ) . '/addons/yafa/forminator-addon-yafa-exception.php';
	require_once dirname( __FILE__ ) . '/addons/yafa/forminator-addon-yafa-form-hooks.php';

	require_once dirname( __FILE__ ) . '/addons/yafa/forminator-addon-yafa.php';
	if ( class_exists( 'Forminator_Addon_Loader' ) ) {
		Forminator_Addon_Loader::get_instance()->register( 'Forminator_Addon_Yafa' );
	}
}



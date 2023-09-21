<?php
/**
 * Plugin Name: WP Lagio Handle Cache
 * Description: A WordPress plugin to handle caching for improved website performance.
 * Version: 1.0
 * Author: Your Name
 */

// Define constants
define( 'WPLHC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPLHC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Hook activation and deactivation functions
register_activation_hook( __FILE__, 'wplhc_activate' );
register_deactivation_hook( __FILE__, 'wplhc_deactivate' );

// Activation function
function wplhc_activate() {
	// Add activation tasks here
}

// Deactivation function
function wplhc_deactivate() {
	// Add deactivation tasks here
}

require_once WPLHC_PLUGIN_DIR . 'src/RunPurge.php';

<?php
/**
 * Plugin Name: wp-jednotka
 * Description: Custom Elementor Free widgets for the Jednotka website.
 * Version: 0.34.18
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: elementor
 * Text Domain: jednotka-elementor
 * License: GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

define( 'JEDNOTKA_ELEMENTOR_VERSION', '0.34.18' );
define( 'JEDNOTKA_ELEMENTOR_PATH', plugin_dir_path( __FILE__ ) );
define( 'JEDNOTKA_ELEMENTOR_URL', plugin_dir_url( __FILE__ ) );

require_once JEDNOTKA_ELEMENTOR_PATH . 'includes/class-plugin.php';

add_action( 'plugins_loaded', array( '\Jednotka\Elementor\Plugin', 'init' ) );

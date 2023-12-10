<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://fortembr.com
 * @since             1.0.0
 * @package           Front_End_Plugin_Monitor
 *
 * @wordpress-plugin
 * Plugin Name:       Front-End Monitor
 * Plugin URI:        https://fortembr.com/products/wordpress-plugin-front-end-monitor
 * Description:       This identifies which plugins are being used on the front-end of your website. This tells you which plugins can be safely deleted without impacting the front-end of the website.
 * Version:           0.0.0-alpha
 * Author:            Fort Embr
 * Author URI:        https://fortembr.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       front-end-plugin-monitor
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'FRONT_END_PLUGIN_MONITOR_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-front-end-plugin-monitor-activator.php
 */
function activate_front_end_plugin_monitor() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-front-end-plugin-monitor-activator.php';
	Front_End_Plugin_Monitor_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-front-end-plugin-monitor-deactivator.php
 */
function deactivate_front_end_plugin_monitor() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-front-end-plugin-monitor-deactivator.php';
	Front_End_Plugin_Monitor_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_front_end_plugin_monitor' );
register_deactivation_hook( __FILE__, 'deactivate_front_end_plugin_monitor' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-front-end-plugin-monitor.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_front_end_plugin_monitor() {

	$plugin = new Front_End_Plugin_Monitor();
	$plugin->run();

}
run_front_end_plugin_monitor();

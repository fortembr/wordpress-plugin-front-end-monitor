<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://fortembr.com
 * @since      1.0.0
 *
 * @package    Front_End_Plugin_Monitor
 * @subpackage Front_End_Plugin_Monitor/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Front_End_Plugin_Monitor
 * @subpackage Front_End_Plugin_Monitor/includes
 * @author     Fort Embr <hello@fortembr.com>
 */
class Front_End_Plugin_Monitor_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'front-end-plugin-monitor',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}

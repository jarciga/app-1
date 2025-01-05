<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://www.doctor-verified.com
 * @since      1.0.0
 *
 * @package    Doctor_Verified
 * @subpackage Doctor_Verified/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Doctor_Verified
 * @subpackage Doctor_Verified/includes
 * @author     Doctor Verified <jayar.arciga.jr@gmail.com>
 */
class Doctor_Verified_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'doctor-verified',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}

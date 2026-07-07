<?php

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    shortcodes-finder
 * @subpackage shortcodes-finder/includes
 * @author     Scribit <wordpress@scribit.it>
 */
class Shortcodes_Finder_i18n
{


	/**
	 * Load the plugin text domain for translation.
	 * 
	 * Removed since 1.6.2 according to PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound Plugin-check warning
	 *
	 * @since    1.0.0
	 */
	/*public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'shortcodes-finder',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}*/
}

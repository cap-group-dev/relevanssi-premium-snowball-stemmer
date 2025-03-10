<?php
/**
 * Relevanssi Premium Snowball Stemmer
 *
 * /relevanssi-premium-snowball-stemmer.php
 *
 * @package Relevanssi Premium Snowball Stemmer
 * @author  Mikko Saari
 * @license https://wordpress.org/about/gpl/ GNU General Public License
 * @see     https://www.relevanssi.com/snowball-stemmer/
 *
 * @wordpress-plugin
 * Plugin Name: Relevanssi Premium Snowball Stemmer
 * Plugin URI: https://www.relevanssi.com/snowball-stemmer/
 * Description: This plugin adds Snowball Stemmer for Relevanssi Premium.
 * Version: 1.5
 * Author: Mikko Saari
 * Author URI: http://www.mikkosaari.fi/
 * Text Domain: relevanssi
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}

Relevanssi_Premium_Snowball_Stemmer::init();

if ( ! function_exists( 'relevanssi_premium_snowball_stemmer' ) ) {
	/**
	 * Get the Relevanssi Premium Snowball Stemmer instance.
	 *
	 * @return Relevanssi_Premium_Snowball_Stemmer The Relevanssi Premium Snowball Stemmer instance.
	 */
	function relevanssi_premium_snowball_stemmer(): Relevanssi_Premium_Snowball_Stemmer {
		return Relevanssi_Premium_Snowball_Stemmer::get_instance();
	}
}

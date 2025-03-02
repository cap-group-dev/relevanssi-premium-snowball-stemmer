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

/**
 * Returns the available stemmer languages.
 *
 * @return array Array of stemmer languages with display names as keys and codes as values.
 */
function relevanssi_premium_snowball_stemmer_languages() {
	return array(
		'catalá (Catalan)'       => 'ca',
		'dansk (Danish)'         => 'da',
		'Deutsch (German)'       => 'de',
		'English'                => 'en',
		'español (Spanish)'      => 'es',
		'français (French)'      => 'fr',
		'italiano (Italian)'     => 'it',
		'Nederlands (Dutch)'     => 'nl',
		'norsk (Norwegian)'      => 'no',
		'português (Portuguese)' => 'pt',
		'românește (Romanian)'   => 'ro',
		'русский язык (Russian)' => 'ru',
		'suomi (Finnish)'        => 'fi',
		'svensk (Swedish)'       => 'sv',
	);
}

require plugin_dir_path( __FILE__ ) . 'admin-menu.php';

add_filter( 'relevanssi_stemmer', 'relevanssi_premium_snowball_stemmer' );

/**
 * Does the actual stemming. Gets the language from the option
 * `relevanssi_premium_snowball_stemmer_language`.
 *
 * @param string $word The word to stem.
 *
 * @return string The stemmed word.
 */
function relevanssi_premium_snowball_stemmer( $word ) {
	if ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) {
		require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
	}

	$stemmer_languages = array_values( relevanssi_premium_snowball_stemmer_languages() );

	// If Polylang is active, use the current language.
	if ( function_exists( 'pll_current_language' ) ) {
		$current_lang = pll_current_language();

		// Only proceed if the current language is supported by the stemmer
		if ( !in_array( $current_lang, $stemmer_languages, true ) ) {
			return $word;
		}
		$language = $current_lang;
	} else {
		// Non-Polylang sites use the saved setting.
		$selected_languages = get_option( 'relevanssi_premium_snowball_stemmer_language' );
		$language = in_array( $selected_languages, $stemmer_languages, true ) ? $selected_languages : 'en';
	}

	try {
		$stemmer = Wamania\Snowball\StemmerFactory::create( $language );
	} catch ( Wamania\Snowball\NotFoundException $e ) {
		return $word;
	}

	/**
	 * Filters whether a word can be stemmed.
	 *
	 * If this filter hook returns `false`, the word will be returned unstemmed.
	 *
	 * @param boolean If true, stem the word; if false, skip. Default true.
	 * @param string $word     The word.
	 * @param string $language The stemmer language.
	 */
	if ( ! apply_filters( 'relevanssi_stemmer_allow_stemming', true, $word, $language ) ) {
		return $word;
	}

	$stemmed_word = $stemmer->stem( $word );
	if ( is_string( $stemmed_word ) && $word !== $stemmed_word ) {
		if ( 'AND' === get_option( 'relevanssi_implicit_operator' ) ) {
			return $stemmed_word;
		}
		return $word . ' ' . $stemmed_word;
	}

	// User asked for a non-existing language or some other error happened.
	return $word;
}

add_action(
	'relevanssi_disable_stemmer',
	function() {
		remove_filter( 'relevanssi_stemmer', 'relevanssi_premium_snowball_stemmer' );
	}
);

add_action(
	'relevanssi_enable_stemmer',
	function() {
		add_filter( 'relevanssi_stemmer', 'relevanssi_premium_snowball_stemmer' );
	}
);


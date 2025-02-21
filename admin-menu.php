<?php
/**
 * Relevanssi Premium Snowball Stemmer
 *
 * /admin-menu.php
 *
 * @package Relevanssi Premium Snowball Stemmer
 * @author  Mikko Saari
 * @license https://wordpress.org/about/gpl/ GNU General Public License
 * @see     https://www.relevanssi.com/snowball-stemmer/
 */

add_filter( 'relevanssi_tabs', 'relevanssi_premium_snowball_stemmer_tab', 20 );

/**
 * Adds the stemmer tab to the Relevanssi admin menu.
 *
 * @param array $tabs The tabs array.
 *
 * @return array The updated tabs array.
 */
function relevanssi_premium_snowball_stemmer_tab( $tabs ) {
	$tabs[] = array(
		'slug'     => 'snowball-stemmer',
		'name'     => 'Stemmer',
		'require'  => false,
		'callback' => 'relevanssi_premium_snowball_stemmer_render_tab',
		'save'     => true,
	);
	return $tabs;
}

/**
 * Renders the options page.
 *
 * Relevanssi Light doesn't have plenty of options at the moment. That is
 * unlikely to change in the future.
 */
function relevanssi_premium_snowball_stemmer_render_tab() {
	$languages = relevanssi_premium_snowball_stemmer_languages();
	$stemmer_languages = array_values( $languages );

	if ( ! empty( $_REQUEST ) && isset( $_REQUEST['submit'] ) ) {
		check_admin_referer( 'save_options', 'relevanssi_premium_snowball_stemmer' );

		// Only save settings if Polylang is not active
		if ( !function_exists( 'pll_languages_list' ) ) {
			$language = $_REQUEST['relevanssi_premium_snowball_language'];
			if ( in_array( $language, $stemmer_languages, true ) ) {
				update_option( 'relevanssi_premium_snowball_stemmer_language', $language );
			}
		}
	}

	?>
	<div class="wrap">
		<?php wp_nonce_field( 'save_options', 'relevanssi_premium_snowball_stemmer' ); ?>

		<h3 id="stemmer"><?php esc_html_e( 'Snowball Stemmer', 'relevanssi_premium_snowball_stemmer' ); ?></h3>

		<?php if ( function_exists( 'pll_languages_list' ) ) : ?>
			<p><strong><?php esc_html_e( 'Polylang is active. Stemmer language settings are automatically managed based on Polylang language settings.', 'relevanssi_premium_snowball_stemmer' ); ?></strong></p>

			<?php
			$pll_languages = pll_languages_list( array( 'fields' => 'names' ) );
			$pll_codes = pll_languages_list();
			foreach ( array_combine( $pll_codes, $pll_languages ) as $lang_code => $lang_name ) :
				$is_supported = in_array( $lang_code, $stemmer_languages, true );
				?>
				<p>
					<?php
					echo esc_html( sprintf(
						__( '%s (%s): %s', 'relevanssi_premium_snowball_stemmer' ),
						$lang_name,
						$lang_code,
						$is_supported ? __('Stemming enabled', 'relevanssi_premium_snowball_stemmer') : __('Stemming not available', 'relevanssi_premium_snowball_stemmer')
					) );
					?>
				</p>
			<?php endforeach; ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Choose the language', 'relevanssi_premium_snowball_stemmer' ); ?>:
			<select name="relevanssi_premium_snowball_language">
				<?php
				$selected_languages = get_option( 'relevanssi_premium_snowball_stemmer_language' );
				$selected_language = is_array( $selected_languages ) ? 'en' : $selected_languages;
				foreach ( $languages as $name => $code ) {
					$selected = $selected_language === $code ? "selected='selected'" : '';
					echo "<option value='" . esc_attr( $code ) . "' $selected>" . esc_html( $name ) . "</option>";
				}
				?>
			</select>
			</p>
		<?php endif; ?>
	</div>
	<?php
}

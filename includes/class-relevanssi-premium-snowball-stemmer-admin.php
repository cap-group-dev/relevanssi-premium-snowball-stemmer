<?php
/**
 * Relevanssi Premium Snowball Stemmer Admin
 *
 * @package Relevanssi Premium Snowball Stemmer
 */

/**
 * Class Relevanssi_Premium_Snowball_Stemmer_Admin
 */
class Relevanssi_Premium_Snowball_Stemmer_Admin {
	/**
	 * Main plugin instance.
	 *
	 * @var Relevanssi_Premium_Snowball_Stemmer
	 */
	private Relevanssi_Premium_Snowball_Stemmer $plugin;

	/**
	 * Constructor.
	 *
	 * @param Relevanssi_Premium_Snowball_Stemmer $plugin Main plugin instance.
	 */
	public function __construct( Relevanssi_Premium_Snowball_Stemmer $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Register hooks.
	 */
	public function register_hooks(): void {
		add_filter( 'relevanssi_tabs', array( $this, 'add_stemmer_tab' ), 20 );
	}

	/**
	 * Adds the stemmer tab to the Relevanssi admin menu.
	 *
	 * @param array $tabs The tabs array.
	 * @return array The updated tabs array.
	 */
	public function add_stemmer_tab( array $tabs ): array {
		$tabs[] = array(
			'slug'     => 'snowball-stemmer',
			'name'     => 'Stemmer',
			'require'  => false,
			'callback' => array( $this, 'render_tab' ),
			'save'     => function_exists( 'pll_languages_list' ) ? false : true,
		);
		return $tabs;
	}

	/**
	 * Renders the options page.
	 */
	public function render_tab(): void {
		$languages         = $this->plugin->get_languages();
		$stemmer_languages = array_values( $languages );

		if ( isset( $_REQUEST['submit'] ) ) {
			check_admin_referer( 'save_options', 'relevanssi_premium_snowball_stemmer' );

			// Only save settings if Polylang is not active.
			if ( ! function_exists( 'pll_languages_list' ) && isset( $_REQUEST['relevanssi_premium_snowball_language'] ) ) {
				$language = sanitize_text_field( wp_unslash( $_REQUEST['relevanssi_premium_snowball_language'] ) );
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
				$pll_codes     = pll_languages_list();
				foreach ( array_combine( $pll_codes, $pll_languages ) as $lang_code => $lang_name ) :
					$is_supported = in_array( $lang_code, $stemmer_languages, true );
					?>
					<p>
						<?php
						echo esc_html(
							sprintf(
								__( '%1$s (%2$s): %3$s', 'relevanssi_premium_snowball_stemmer' ),
								$lang_name,
								$lang_code,
								$is_supported ? __( 'Stemming enabled', 'relevanssi_premium_snowball_stemmer' ) : __( 'Stemming not available', 'relevanssi_premium_snowball_stemmer' )
							)
						);
						?>
					</p>
				<?php endforeach; ?>
			<?php else : ?>
				<p><?php esc_html_e( 'Choose the language', 'relevanssi_premium_snowball_stemmer' ); ?>:
				<select name="relevanssi_premium_snowball_language">
					<?php
					$selected_languages = get_option( 'relevanssi_premium_snowball_stemmer_language' );
					$selected_language  = is_array( $selected_languages ) ? 'en' : $selected_languages;
					foreach ( $languages as $name => $code ) {
						$selected = $selected_language === $code ? ' selected' : '';
						printf(
							'<option value="%s"%s>%s</option>',
							esc_attr( $code ),
							esc_attr( $selected ),
							esc_html( $name )
						);
					}
					?>
				</select>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}
}

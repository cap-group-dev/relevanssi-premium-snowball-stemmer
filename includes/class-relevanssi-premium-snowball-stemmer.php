<?php
/**
 * Relevanssi Premium Snowball Stemmer
 *
 * @package Relevanssi Premium Snowball Stemmer
 */

/**
 * Class Relevanssi_Premium_Snowball_Stemmer
 */
class Relevanssi_Premium_Snowball_Stemmer {
	/**
	 * Instance of the class.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Available languages.
	 *
	 * @var array
	 */
	protected array $languages = array(
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

	/**
	 * Stemmed words.
	 *
	 * @var array
	 */
	protected array $stemmed_words = array();

	/**
	 * Constructor.
	 */
	protected function __construct() {
		// Do nothing.
	}

	/**
	 * Initialize the plugin.
	 */
	public static function init() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->register_hooks();

			$admin = new Relevanssi_Premium_Snowball_Stemmer_Admin( self::$instance );
			$admin->register_hooks();
		}

		return self::$instance;
	}

	/**
	 * Get the plugin instance.
	 *
	 * @return self
	 */
	public static function get_instance(): self {
		return self::$instance;
	}

	/**
	 * Register hooks.
	 */
	protected function register_hooks(): void {
		add_filter( 'relevanssi_stemmer', array( $this, 'stem_word' ) );
		add_filter( 'relevanssi_highlight_regex', array( $this, 'adjust_highlight_regex' ), 10, 2 );
	}

	/**
	 * Get stemmed version of a word.
	 *
	 * @param string $word Word to stem.
	 * @return string Stemmed word.
	 */
	public function stem_word( string $word ): string {
		// Get language - prefer Polylang if available.
		$language = function_exists( 'pll_current_language' )
			? pll_current_language()
			: get_option( 'relevanssi_premium_snowball_stemmer_language', 'en' );

		try {
			$stemmer = Wamania\Snowball\StemmerFactory::create( $language );
			$stemmed = $stemmer->stem( $word );

			if ( is_string( $stemmed ) && $word !== $stemmed ) {
				$result = 'AND' === get_option( 'relevanssi_implicit_operator' )
					? $stemmed
					: $word . ' ' . $stemmed;

				$this->stemmed_words[ $word ] = $stemmed; // Store only the stem.
				return $result;
			}
		} catch ( Wamania\Snowball\NotFoundException $e ) {
			// If stemming fails, return original word.
		}

		return $word;
	}

	/**
	 * Adjusts the highlight regex pattern to include stemmed variations.
	 *
	 * @param string $regex The original highlight regex pattern.
	 * @param string $term  The search term being highlighted.
	 * @return string The modified regex pattern that includes stemmed variations.
	 */
	public function adjust_highlight_regex( string $regex, string $term ): string {
		// If we have no stems, return original regex.
		if ( empty( $this->stemmed_words ) ) {
			return $regex;
		}

		// Find the shortest stem from our stored stems.
		$shortest_stem = reset( $this->stemmed_words );
		foreach ( $this->stemmed_words as $stem ) {
			if ( strlen( $stem ) < strlen( $shortest_stem ) ) {
				$shortest_stem = $stem;
			}
		}

		// Create pattern using the shortest stem.
		$pr_term = preg_quote( $shortest_stem, '/' );
		$pr_term = relevanssi_add_accent_variations( $pr_term );

		// Support for wildcard matching.
		$pr_term = str_replace(
			array( '\*', '\?' ),
			array( '\S*', '.' ),
			$pr_term
		);

		if ( 'on' === get_option( 'relevanssi_expand_highlights' ) ) {
			$regex = "/([\w]*{$pr_term}[\W]|[\W]{$pr_term}[\w]*)/iu";
		} else {
			$regex = "/([\W]{$pr_term}|{$pr_term}[\W])/iu";
		}

		return $regex;
	}

	/**
	 * Get available languages.
	 *
	 * @return array Array of available languages.
	 */
	public function get_languages(): array {
		return $this->languages;
	}

	/**
	 * Get all stemmed words.
	 *
	 * @return array Array of stemmed words.
	 */
	public function get_stemmed_words(): array {
		return $this->stemmed_words;
	}
}

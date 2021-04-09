<?php
/**
 * Core Loader Class
 *
 * @package   Crayon Syntax Highlighter
 * @author    Fedor Urvanov, Aram Kocharyan
 * @copyright Copyright 2013, Aram Kocharyan
 * @link      https://urvanov.ru
 */

defined( 'ABSPATH' ) || exit;

// Class includes.
require_once 'class-urvanov-syntax-highlighter-global.php';
require_once URVANOV_SYNTAX_HIGHLIGHTER_PARSER_PHP;
require_once URVANOV_SYNTAX_HIGHLIGHTER_FORMATTER_PHP;
require_once URVANOV_SYNTAX_HIGHLIGHTER_SETTINGS_PHP;
require_once URVANOV_SYNTAX_HIGHLIGHTER_LANGS_PHP;

/**
 * Class Urvanov_Syntax_Highlighter (Old name: CrayonHighlighter).
 */
class Urvanov_Syntax_Highlighter {

	/**
	 * Class ID.
	 *
	 * @var string
	 */
	private $id = '';

	/**
	 * URL is initially NULL, meaning none provided.
	 *
	 * @var null
	 */
	private $url = null;

	/**
	 * Code variable.
	 *
	 * @var string
	 */
	private $code = '';

	/**
	 * Formatted code variable.
	 *
	 * @var string
	 */
	private $formatted_code = '';

	/**
	 * Title.
	 *
	 * @var string
	 */
	private $title = '';

	/**
	 * Line count.
	 *
	 * @var int
	 */
	private $line_count = 0;

	/**
	 * Marked lines array.
	 *
	 * @var array
	 */
	private $marked_lines = array();

	/**
	 * Line range.
	 *
	 * @var null
	 */
	private $range = null;

	/**
	 * Error message.
	 *
	 * @var string
	 */
	private $error = '';

	/**
	 * Determine whether the code needs to be loaded, parsed or formatted.
	 *
	 * @var bool
	 */
	private $needs_load = false;

	/**
	 * Does code need formatting.
	 *
	 * @var bool
	 */
	private $needs_format = false;

	/**
	 * Record the script run times.
	 *
	 * @var array
	 */
	private $runtime = array();

	/**
	 * Whether the code is mixed.
	 *
	 * @var bool
	 */
	private $is_mixed = false;

	/**
	 * Inline code on a single floating line.
	 *
	 * @var bool
	 */
	private $is_inline = false;

	/**
	 * Is text highlighted.
	 *
	 * @var bool
	 */
	private $is_highlighted = true;

	/**
	 * Stores the CrayonLang being used.
	 *
	 * @var null
	 */
	private $language = null;

	/**
	 * A copy of the current global settings which can be overridden.
	 *
	 * @var null
	 */
	private $settings = null;

	/**
	 * Methods
	 */

	/**
	 * Urvanov_Syntax_Highlighter constructor.
	 *
	 * @param ?string $url URL.
	 * @param ?string $language Language.
	 * @param mixed   $id ID.
	 */
	public function __construct( $url = null, $language = null, $id = null ) {
		if ( null !== $url ) {
			$this->url( $url );
		}

		if ( null !== $language ) {
			$this->language( $language );
		}

		// Default ID.
		$id = null !== $id ? $id : uniqid();
		$this->id( $id );
	}

	/**
	 * Tries to load the code locally, then attempts to load it remotely.
	 */
	private function load() {
		if ( empty( $this->url ) ) {
			$this->error( 'The specified URL is empty, please provide a valid URL.' );

			return;
		}

		/**
		 * Try to replace the URL with an absolute path if it is local, used to prevent scripts
		 * from executing when they are loaded.
		 */
		$url = $this->url;

		if ( $this->setting_val( Urvanov_Syntax_Highlighter_Settings::DECODE_ATTRIBUTES ) ) {
			$url = UrvanovSyntaxHighlighterUtil::html_entity_decode( $url );
		}

		$url       = UrvanovSyntaxHighlighterUtil::pathf( $url );
		$site_http = Urvanov_Syntax_Highlighter_Global_Settings::site_url();
		$scheme    = wp_parse_url( $url, PHP_URL_SCHEME );

		// Try to replace the site URL with a path to force local loading.
		if ( empty( $scheme ) ) {

			// No url scheme is given - path may be given as relative.
			$url = UrvanovSyntaxHighlighterUtil::path_slash( $site_http ) . UrvanovSyntaxHighlighterUtil::path_slash( $this->setting_val( Urvanov_Syntax_Highlighter_Settings::LOCAL_PATH ) ) . $url;
		}

		$http_code = 0;

		// If available, use the built in wp remote http get function.
		if ( function_exists( 'wp_remote_get' ) ) {
			$url_uid = 'urvanov_syntax_highlighter_' . UrvanovSyntaxHighlighterUtil::str_uid( $url );
			$cached  = get_transient( $url_uid, 'urvanov-syntax-highlighter-syntax' );
			Urvanov_Syntax_Highlighter_Settings_WP::load_cache();

			if ( false !== $cached ) {
				$content   = $cached;
				$http_code = 200;
			} else {
				$response = wp_remote_get(
					$url,
					array(
						'sslverify' => false,
						'timeout'   => 20,
					)
				);

				$content   = wp_remote_retrieve_body( $response );
				$http_code = wp_remote_retrieve_response_code( $response );
				$cache     = $this->setting_val( Urvanov_Syntax_Highlighter_Settings::CACHE );
				$cache_sec = Urvanov_Syntax_Highlighter_Settings::get_cache_sec( $cache );
				if ( $cache_sec > 1 && $http_code >= 200 && $http_code < 400 ) {
					set_transient( $url_uid, $content, $cache_sec );
					Urvanov_Syntax_Highlighter_Settings_WP::add_cache( $url_uid );
				}
			}
		}

		if ( $http_code >= 200 && $http_code < 400 ) {
			$this->code( $content );
		} else {
			if ( empty( $this->code ) ) {
				// If code is also given, just use that.
				$this->error( "The provided URL ('$this->url'), parsed remotely as ('$url'), could not be accessed." );
			}
		}

		$this->needs_load = false;
	}

	/**
	 * Central point of access for all other functions to update code.
	 */
	public function process() {
		$tmr           = new UrvanovSyntaxHighlighterTimer();
		$this->runtime = null;

		if ( $this->needs_load ) {
			$tmr->start();
			$this->load();
			$this->runtime[ URVANOV_SYNTAX_HIGHLIGHTER_LOAD_TIME ] = $tmr->stop();
		}
		if ( ! empty( $this->error ) || empty( $this->code ) ) {

			// Disable highlighting for errors and empty code.
			return;
		}

		if ( null === $this->language ) {
			$this->language_detect();
		}

		if ( $this->needs_format ) {
			$tmr->start();

			try {
				// Parse before hand to read modes.
				$code = $this->code;

				// If inline, then combine lines into one.
				if ( $this->is_inline ) {
					$code = preg_replace( '#[\r\n]+#ms', '', $code );
					if ( $this->setting_val( Urvanov_Syntax_Highlighter_Settings::TRIM_WHITESPACE ) ) {
						$code = trim( $code );
					}
				}

				// Decode html entities (e.g. if using visual editor or manually encoding).
				if ( $this->setting_val( Urvanov_Syntax_Highlighter_Settings::DECODE ) ) {
					$code = UrvanovSyntaxHighlighterUtil::html_entity_decode( $code );
				}

				// Save code so output is plain output is the same.
				$this->code = $code;

				// Allow mixed if langauge supports it and setting is set.
				Urvanov_Syntax_Highlighter_Parser::parse( $this->language->id() );
				if ( ! $this->setting_val( Urvanov_Syntax_Highlighter_Settings::ALTERNATE ) || ! $this->language->mode( Urvanov_Syntax_Highlighter_Parser::ALLOW_MIXED ) ) {

					// Format the code with the generated regex and elements.
					$this->formatted_code = Urvanov_Syntax_Highlighter_Formatter::format_code( $code, $this->language, $this );
				} else {

					// Format the code with Mixed Highlighting.
					$this->formatted_code = Urvanov_Syntax_Highlighter_Formatter::format_mixed_code( $code, $this->language, $this );
				}
			} catch ( Exception $e ) {
				$this->error( $e->message() );

				return;
			}
			$this->needs_format                                      = false;
			$this->runtime[ URVANOV_SYNTAX_HIGHLIGHTER_FORMAT_TIME ] = $tmr->stop();
		}
	}

	/**
	 * Used to format the glue in between code when finding mixed languages.
	 *
	 * @param string|string[] $glue      Code.
	 * @param bool            $highlight Language.
	 *
	 * @return array|string|string[]|null
	 */
	private function format_glue( $glue, bool $highlight ) {
		// TODO $highlight.

		return Urvanov_Syntax_Highlighter_Formatter::format_code( $glue, $this->language, $this, $highlight );
	}

	/**
	 * Sends the code to the formatter for printing. Apart from the getters and setters,
	 * this is the only other function accessible outside this class. $show_lines can also be a string.
	 *
	 * @param bool $show_lines Show lines.
	 * @param bool $print Print.
	 *
	 * @return string|void
	 */
	public function output( $show_lines = true, $print = true ) {
		$this->process();

		if ( empty( $this->error ) ) {

			// If no errors have occured, print the formatted code.
			$ret = Urvanov_Syntax_Highlighter_Formatter::print_code( $this, $this->formatted_code, $show_lines, $print );
		} else {
			$ret = Urvanov_Syntax_Highlighter_Formatter::print_error( $this, $this->error, '', $print );
		}

		// Reset the error message at the end of the print session.
		$this->error = '';

		// If $print = FALSE, $ret will contain the output.
		return $ret;
	}

	/**
	 * Get code.
	 *
	 * @param string $code Code string.
	 *
	 * @return string
	 */
	public function code( $code = null ): string {
		if ( null === $code ) {
			return $this->code;
		} else {
			// Trim whitespace.
			if ( $this->setting_val( Urvanov_Syntax_Highlighter_Settings::TRIM_WHITESPACE ) ) {
				$code = preg_replace( "#(^\\s*\\r?\\n)|(\\r?\\n\\s*$)#", '', $code );
			}

			if ( $this->setting_val( Urvanov_Syntax_Highlighter_Settings::TRIM_CODE_TAG ) ) {
				$code = preg_replace( '#^\s*<\s*code[^>]*>#msi', '', $code );
				$code = preg_replace( '#</\s*code[^>]*>\s*$#msi', '', $code );
			}

			$before = $this->setting_val( Urvanov_Syntax_Highlighter_Settings::WHITESPACE_BEFORE );

			if ( $before > 0 ) {
				$code = str_repeat( "\n", $before ) . $code;
			}

			$after = $this->setting_val( Urvanov_Syntax_Highlighter_Settings::WHITESPACE_AFTER );

			if ( $after > 0 ) {
				$code = $code . str_repeat( "\n", $after );
			}

			if ( ! empty( $code ) ) {
				$this->code         = $code;
				$this->needs_format = true;
			}

			return '';
		}
	}

	/**
	 * Language.
	 *
	 * @param mixed $id ID.
	 *
	 * @return null
	 */
	public function language( $id = null ) {
		if ( null === $id || ! is_string( $id ) ) {
			return $this->language;
		}

		// Not how I'd have written this, but, whatever.  As long as it works. - kp.
		// phpcs:ignore
		if ( ( $lang = Urvanov_Syntax_Highlighter_Resources::langs()->get( $id ) ) !== false || ( $lang = Urvanov_Syntax_Highlighter_Resources::langs()->alias( $id ) ) !== false ) {

			// Set the language if it exists or look for an alias.
			$this->language = $lang;
		} else {
			$this->language_detect();
		}

		// Prepare the language for use, even if we have no code, we need the name.
		Urvanov_Syntax_Highlighter_Parser::parse( $this->language->id() );
	}

	/**
	 * Detect language.
	 */
	public function language_detect() {

		// Attempt to detect the language.
		if ( ! empty( $id ) ) {
			$this->log( "The language '$id' could not be loaded." );
		}

		$this->language = Urvanov_Syntax_Highlighter_Resources::langs()->detect( $this->url, $this->setting_val( Urvanov_Syntax_Highlighter_Settings::FALLBACK_LANG ) );
	}

	/**
	 * URL.
	 *
	 * @param ?string $url URL.
	 *
	 * @return null
	 */
	public function url( $url = null ) {
		if ( null === $url ) {
			return $this->url;
		} else {
			$this->url        = $url;
			$this->needs_load = true;
		}
	}

	/**
	 * Title.
	 *
	 * @param ?string $title Title.
	 *
	 * @return string
	 */
	public function title( $title = null ): string {
		if ( ! UrvanovSyntaxHighlighterUtil::str( $this->title, $title ) ) {
			return $this->title;
		}

		return '';
	}

	/**
	 * Line count.
	 *
	 * @param mixed $line_count Line count.
	 *
	 * @return int
	 */
	public function line_count( $line_count = null ): int {
		if ( ! UrvanovSyntaxHighlighterUtil::num( $this->line_count, $line_count ) ) {
			return $this->line_count;
		}

		return 0;
	}

	/**
	 * Marked.
	 *
	 * @param ?string $str String to check.
	 *
	 * @return array|bool
	 */
	public function marked( $str = null ) {
		if ( null === $str ) {
			return $this->marked_lines;
		}

		// If only an int is given.
		if ( is_int( $str ) ) {
			$array = array( $str );

			return UrvanovSyntaxHighlighterUtil::arr( $this->marked_lines, $array );
		}

		// A string with ints separated by commas, can also contain ranges.
		$array = UrvanovSyntaxHighlighterUtil::trim_e( $str );
		$array = array_unique( $array );
		$lines = array();
		foreach ( $array as $line ) {

			// Check for ranges.
			if ( strpos( $line, '-' ) !== false ) {
				$ranges = UrvanovSyntaxHighlighterUtil::range_str( $line );
				$lines  = array_merge( $lines, $ranges );
			} else {

				// Otherwise check the string for a number.
				$line = intval( $line );
				if ( 0 !== $line ) {
					$lines[] = $line;
				}
			}
		}

		return UrvanovSyntaxHighlighterUtil::arr( $this->marked_lines, $lines );
	}

	/**
	 * Range.
	 *
	 * @param ?string $str String to check.
	 *
	 * @return bool|null
	 */
	public function range( $str = null ): ?bool {
		if ( null === $str ) {
			return $this->range;
		} else {
			$range = UrvanovSyntaxHighlighterUtil::range_str_single( $str );
			if ( $range ) {
				$this->range = $range;
			}
		}

		return false;
	}

	/**
	 * Log.
	 *
	 * @param mixed $var Variable.
	 */
	public function log( $var ) {
		if ( $this->setting_val( Urvanov_Syntax_Highlighter_Settings::ERROR_LOG ) ) {
			UrvanovSyntaxHighlighterLog::log( $var );
		}
	}

	/**
	 * ID.
	 *
	 * @param mixed $id ID.
	 *
	 * @return string
	 */
	public function id( $id = null ): string {
		if ( null === $id ) {
			return $this->id;
		} else {
			$this->id = strval( $id );
		}

		return '';
	}

	/**
	 * Error.
	 *
	 * @param string|null $string Error string.
	 *
	 * @return string
	 */
	public function error( $string = null ): string {
		if ( ! $string ) {
			return $this->error;
		}
		$this->error .= $string;
		$this->log( $string );

		// Add the error string and ensure no further processing occurs.
		$this->needs_load   = false;
		$this->needs_format = false;
	}

	/**
	 * Set and retreive settings.
	 * TODO fix this, it's too limiting.
	 *
	 * @param mixed $mixed Settings.
	 *
	 * @return mixed
	 */
	public function settings( $mixed = null ) {
		if ( null === $this->settings ) {
			$this->settings = Urvanov_Syntax_Highlighter_Global_Settings::get_obj();
		}

		if ( null === $mixed ) {
			return $this->settings;
		} elseif ( is_string( $mixed ) ) {
			return $this->settings->get( $mixed );
		} elseif ( is_array( $mixed ) ) {
			$this->settings->set( $mixed );

			return true;
		}

		return false;
	}

	/**
	 * Retrieve a single setting's value for use in the formatter. By default, on failure it will
	 * return TRUE to ensure FALSE is only sent when a setting is found. This prevents a fake
	 * FALSE when the formatter checks for a positive setting (Show/Enable) and fails. When a
	 * negative setting is needed (Hide/Disable), $default_return should be set to FALSE.
	 *
	 * TODO fix this (see above)
	 *
	 * @param null|string $name Name.
	 * @param bool        $default_return Default.
	 *
	 * @return mixed
	 */
	public function setting_val( $name = null, $default_return = true ) {
		$setting = $this->settings( $name );

		if ( is_string( $name ) && $setting ) {
			return $setting->value();
		} else {

			// Name not valid.
			return ( is_bool( $default_return ) ? $default_return : true );
		}
	}

	/**
	 * Set a setting value.
	 *
	 * TODO fix this (see above).
	 *
	 * @param null|string $name Name.
	 * @param bool        $value Value.
	 */
	public function setting_set( $name = null, $value = true ) {
		$this->settings->set( $name, $value );
	}

	/**
	 * Used to find current index in dropdown setting.
	 *
	 * @param string|null $name Name.
	 *
	 * @return int
	 */
	public function setting_index( $name = null ): int {
		$setting = $this->settings( $name );
		if ( is_string( $name ) && $setting->is_array() ) {
			return $setting->index();
		} else {

			// Returns -1 to avoid accidentally selecting an item in a dropdown.
			return Urvanov_Syntax_Highlighter_Settings::INVALID;
		}
	}

	/**
	 * Formatted code.
	 *
	 * @return string
	 */
	public function formatted_code(): string {
		return $this->formatted_code;
	}

	/**
	 * Runtime.
	 *
	 * @return array
	 */
	public function runtime(): ?array {
		return $this->runtime;
	}

	/**
	 * Is highlighted.
	 *
	 * @param bool|null $highlighted Is highlighted.
	 *
	 * @return bool
	 */
	public function is_highlighted( $highlighted = null ): bool {
		if ( null === $highlighted ) {
			return $this->is_highlighted;
		} else {
			$this->is_highlighted = $highlighted;
		}

		return false;
	}

	/**
	 * Is mixed.
	 *
	 * @param mixed $mixed Mixed.
	 *
	 * @return bool
	 */
	public function is_mixed( $mixed = null ): bool {
		if ( null === $mixed ) {
			return $this->is_mixed;
		} else {
			$this->is_mixed = $mixed;
		}
	}

	/**
	 * Is inline.
	 *
	 * @param bool|null $inline Is inline.
	 *
	 * @return bool
	 */
	public function is_inline( $inline = null ): bool {
		if ( null === $inline ) {
			return $this->is_inline;
		} else {
			$inline          = UrvanovSyntaxHighlighterUtil::str_to_bool( $inline, false );
			$this->is_inline = $inline;
		}
	}
}

<?php
/**
 * Language Class
 *
 * @package   Crayon Syntax Highlighter
 * @author    Fedor Urvanov, Aram Kocharyan
 * @copyright Copyright 2013, Aram Kocharyan
 * @link      https://urvanov.ru
 */

defined( 'ABSPATH' ) || exit;

require_once 'class-urvanov-syntax-highlighter-global.php';
require_once URVANOV_SYNTAX_HIGHLIGHTER_RESOURCE_PHP;

/**
 * Class Urvanov_Syntax_Highlighter_Langs_Resource_Type
 */
class Urvanov_Syntax_Highlighter_Langs_Resource_Type {
	/**
	 * Extension.
	 */
	const EXTENSION = 0;

	/**
	 * Alias.
	 */
	const ALIAS = 1;

	/**
	 * Delimiter.
	 */
	const DELIMITER = 2;
}

/**
 * Manages languages once they are loaded. The parser directly loads them, saves them here.
 */
class Urvanov_Syntax_Highlighter_Langs extends Urvanov_Syntax_Highlighter_User_Resource_Collection { // phpcs:ignore

	/**
	 * CSS classes for known elements.
	 *
	 * @var string[]
	 */
	private static $known_elements = array(
		'COMMENT'                                    => 'c',
		'PREPROCESSOR'                               => 'p',
		'STRING'                                     => 's',
		'KEYWORD'                                    => 'k',
		'STATEMENT'                                  => 'st',
		'RESERVED'                                   => 'r',
		'TYPE'                                       => 't',
		'TAG'                                        => 'ta',
		'MODIFIER'                                   => 'm',
		'IDENTIFIER'                                 => 'i',
		'ENTITY'                                     => 'e',
		'VARIABLE'                                   => 'v',
		'CONSTANT'                                   => 'cn',
		'OPERATOR'                                   => 'o',
		'SYMBOL'                                     => 'sy',
		'NOTATION'                                   => 'n',
		'FADED'                                      => 'f',
		Urvanov_Syntax_Highlighter_Parser::HTML_CHAR => 'h',
		Urvanov_Syntax_Highlighter_Parser::URVANOV_SYNTAX_HIGHLIGHTER_ELEMENT => 'crayon-internal-element',
	);

	/**
	 * Default language.
	 */
	const DEFAULT_LANG = 'default';

	/**
	 * Default language name.
	 */
	const DEFAULT_LANG_NAME = 'Default';

	/**
	 * Resource type.
	 */
	const RESOURCE_TYPE = 'Urvanov_Syntax_Highlighter_Lands_Resource_Type';

	/**
	 * Used to cache the objects, since they are unlikely to change during a single run.
	 *
	 * @var array
	 */
	private static $resource_cache = array();

	/**
	 * Urvanov_Syntax_Highlighter_Langs constructor.
	 */
	public function __construct() {
		$this->set_default( self::DEFAULT_LANG, self::DEFAULT_LANG_NAME );
		$this->directory( URVANOV_SYNTAX_HIGHLIGHTER_LANG_PATH );
		$this->relative_directory( URVANOV_SYNTAX_HIGHLIGHTER_LANG_DIR );
		$this->extension( 'txt' );

		UrvanovSyntaxHighlighterLog::debug( 'Setting lang directories' );
		$upload = Urvanov_Syntax_Highlighter_Global_Settings::upload_path();

		if ( $upload ) {
			$this->user_directory( $upload . URVANOV_SYNTAX_HIGHLIGHTER_LANG_DIR );
			if ( ! is_dir( $this->user_directory() ) ) {
				Urvanov_Syntax_Highlighter_Global_Settings::mkdir( $this->user_directory() );
				UrvanovSyntaxHighlighterLog::debug( $this->user_directory(), 'LANG USER DIR' );
			}
		} else {
			UrvanovSyntaxHighlighterLog::syslog( 'Upload directory is empty: ' . $upload . ' cannot load languages.' );
		}

		UrvanovSyntaxHighlighterLog::debug( $this->directory() );
		UrvanovSyntaxHighlighterLog::debug( $this->user_directory() );
	}

	/**
	 * Filename.
	 *
	 * @param mixed $id ID.
	 * @param null  $user USer.
	 *
	 * @return string
	 */
	public function filename( $id, $user = null ): string {
		return $id . "/$id." . $this->extension();
	}

	/**
	 * Load process.
	 */
	public function load_process() {
		parent::load_process();
		$this->load_exts();
		$this->load_aliases();
		$this->load_delimiters(); // TODO check for setting?
	}

	/**
	 * Load resources.
	 *
	 * @param string $dir Directory.
	 */
	public function load_resources( $dir = null ) { // phpcs:ignore
		parent::load_resources( $dir );
	}

	/**
	 * Create user resource instance.
	 *
	 * @param mixed  $id ID.
	 * @param String $name Name.
	 *
	 * @return Urvanov_Syntax_Highlighter_Lang
	 */
	public function create_user_resource_instance( $id, $name = null ): Urvanov_Syntax_Highlighter_Lang {
		return new Urvanov_Syntax_Highlighter_Lang( $id, $name );
	}

	/**
	 * Add default.
	 *
	 * @return void
	 */
	public function add_default() {
		$result = parent::add_default();
		if ( $this->is_state_loading() && ! $result ) {

			// Default not added, must already be loaded, ready to parse.
			Urvanov_Syntax_Highlighter_Parser::parse( self::DEFAULT_LANG );
		}
	}

	/**
	/* Attempts to detect the language based on extension, otherwise falls back to fallback language given.
	 * Returns a Urvanov_Syntax_Highlighter_Lang object.
	 *
	 * @param string $path Path.
	 * @param mixed  $fallback_id Fallback ID.
	 *
	 * @return array|false|mixed|null
	 */
	public function detect( $path = '', $fallback_id = null ) {
		$this->load();

		// phpcs:ignore WordPress.PHP.DontExtract
		extract( pathinfo( $path ) );

		// If fallback id if given.
		if ( null === $fallback_id ) {

			// Otherwise use global fallback.
			$fallback_id = Urvanov_Syntax_Highlighter_Global_Settings::get( Urvanov_Syntax_Highlighter_Settings::FALLBACK_LANG );
		}
		// Attempt to use fallback.
		$fallback = $this->get( $fallback_id );

		// Use extension before trying fallback.
		$extension = $extension ?? '';

		// I hate this logic. - kp.
		if ( ! empty( $extension ) && ( $lang = $this->ext( $extension ) ) || ( $lang = $this->get( $extension ) ) ) { // phpcs:ignore

			// If extension is found, attempt to find a language for it.
			// If that fails, attempt to load a language with the same id as the extension.
			return $lang;
		} elseif ( null !== $fallback || $fallback = $this->get_default() ) { // phpcs:ignore

			// Resort to fallback if loaded, or fallback to default.
			return $fallback;
		} else {

			// No language found.
			return null;
		}
	}

	/**
	 * Load all extensions and add them into each language.
	 */
	private function load_exts() {

		// Load only once.
		if ( ! $this->is_state_loading() ) {
			return;
		}

		$lang_exts = self::load_attr_file( URVANOV_SYNTAX_HIGHLIGHTER_LANG_EXT );
		if ( false !== $lang_exts ) {
			foreach ( $lang_exts as $lang_id => $exts ) {
				$lang = $this->get( $lang_id );
				$lang->ext( $exts );
			}
		}
	}

	/**
	 * Load all extensions and add them into each language.
	 */
	private function load_aliases() {

		// Load only once.
		if ( ! $this->is_state_loading() ) {
			return;
		}

		$lang_aliases = self::load_attr_file( URVANOV_SYNTAX_HIGHLIGHTER_LANG_ALIAS );
		if ( false !== $lang_aliases ) {
			foreach ( $lang_aliases as $lang_id => $aliases ) {
				$lang = $this->get( $lang_id );
				$lang->alias( $aliases );
			}
		}
	}

	/**
	 * Load all extensions and add them into each language.
	 */
	private function load_delimiters() {

		// Load only once.
		if ( ! $this->is_state_loading() ) {
			return;
		}

		$lang_delims = self::load_attr_file( URVANOV_SYNTAX_HIGHLIGHTER_LANG_DELIM );
		if ( false !== $lang_delims ) {
			foreach ( $lang_delims as $lang_id => $delims ) {
				$lang = $this->get( $lang_id );
				$lang->delimiter( $delims );
			}
		}
	}

	/**
	 * Used to load aliases and extensions to languages.
	 *
	 * @param string $path Path.
	 *
	 * @return array|false
	 */
	private function load_attr_file( string $path ) {
		$lines = UrvanovSyntaxHighlighterUtil::lines( $path, 'lwc' );
		if ( false !== $lines ) {
			$attributes = array(); // key = language id, value = array of attr.

			foreach ( $lines as $line ) {
				preg_match( '#^[\t ]*([^\r\n\t ]+)[\t ]+([^\r\n]+)#', $line, $matches );
				$lang = $this->get( $matches[1] );
				if ( 3 === count( $matches ) && $lang ) {
					// If the langauges of the attribute exists, return it in an array
					// TODO merge instead of replace key?
					$attributes[ $matches[1] ] = explode( ' ', $matches[2] );
				}
			}

			return $attributes;
		} else {
			UrvanovSyntaxHighlighterLog::syslog( 'Could not load attr file: ' . $path );

			return false;
		}
	}

	/**
	 * Returns the Urvanov_Syntax_Highlighter_Lang for the given extension.
	 *
	 * @param mixed $ext Ext.
	 *
	 * @return false|mixed
	 */
	public function ext( $ext ) {
		$this->load();

		foreach ( $this->get() as $lang ) {
			if ( $lang->has_ext( $ext ) ) {
				return $lang;
			}
		}

		return false;
	}

	/**
	 * Returns the Urvanov_Syntax_Highlighter_Lang for the given alias.
	 *
	 * @param string $alias Alias.
	 *
	 * @return false|mixed
	 */
	public function alias( $alias = '' ) {
		$this->load();

		foreach ( $this->get() as $lang ) {
			if ( $lang->has_alias( $alias ) ) {
				return $lang;
			}
		}

		return false;
	}

	/**
	 * Fetches a resource. Type is an int from Urvanov_Syntax_Highlighter_LangsResourceType.
	 *
	 * @param string $type Type.
	 * @param bool   $reload Reload.
	 * @param bool   $keep_empty_fetches Keep empty fetches.
	 *
	 * @return array|false|mixed
	 */
	public function fetch( $type = '', $reload = false, $keep_empty_fetches = false ) {
		$this->load();

		if ( ! array_key_exists( $type, self::$resource_cache ) || $reload ) {
			$fetches = array();
			foreach ( $this->get() as $lang ) {

				switch ( $type ) {
					case Urvanov_Syntax_Highlighter_Langs_Resource_Type::EXTENSION:
						$fetch = $lang->ext();
						break;
					case Urvanov_Syntax_Highlighter_Langs_Resource_Type::ALIAS:
						$fetch = $lang->alias();
						break;
					case Urvanov_Syntax_Highlighter_Langs_Resource_Type::DELIMITER:
						$fetch = $lang->delimiter();
						break;
					default:
						return false;
				}

				if ( ! empty( $fetch ) || $keep_empty_fetches ) {
					$fetches[ $lang->id() ] = $fetch;
				}
			}
			self::$resource_cache[ $type ] = $fetches;
		}

		return self::$resource_cache[ $type ];
	}

	/**
	 * Extensions.
	 *
	 * @param bool $reload Reload.
	 *
	 * @return array|false|mixed
	 */
	public function extensions( $reload = false ) {
		return $this->fetch( Urvanov_Syntax_Highlighter_Langs_Resource_Type::EXTENSION, $reload );
	}

	/**
	 * Aliases.
	 *
	 * @param bool $reload Reload.
	 *
	 * @return array|false|mixed
	 */
	public function aliases( $reload = false ) {
		return $this->fetch( Urvanov_Syntax_Highlighter_Langs_Resource_Type::ALIAS, $reload );
	}

	/**
	 * Delimiters.
	 *
	 * @param bool $reload Reload.
	 *
	 * @return array|false|mixed
	 */
	public function delimiters( $reload = false ) {
		return $this->fetch( Urvanov_Syntax_Highlighter_Langs_Resource_Type::DELIMITER, $reload );
	}

	/**
	 * Extensions inverted.
	 *
	 * @param bool $reload Reload.
	 *
	 * @return array
	 */
	public function extensions_inverted( $reload = false ): array {
		$extensions = $this->extensions( $reload );
		$inverted   = array();
		foreach ( $extensions as $lang => $exts ) {
			foreach ( $exts as $ext ) {
				$inverted[ $ext ] = $lang;
			}
		}

		return $inverted;
	}

	/**
	 * IDs and aliases.
	 *
	 * @param bool $reload Reload.
	 *
	 * @return array
	 */
	public function ids_and_aliases( $reload = false ): array {
		$fetch = $this->fetch( Urvanov_Syntax_Highlighter_Langs_Resource_Type::ALIAS, $reload, true );
		foreach ( $fetch as $id => $alias_array ) {
			$ids_and_aliases[] = $id;
			foreach ( $alias_array as $alias ) {
				$ids_and_aliases[] = $alias;
			}
		}

		return $ids_and_aliases;
	}

	/**
	 * Return the array of valid elements or a particular element value.
	 *
	 * @param string $name Name.
	 *
	 * @return false|string|string[]
	 */
	public static function known_elements( $name = null ) {
		if ( null === $name ) {
			return self::$known_elements;
		} elseif ( is_string( $name ) && array_key_exists( $name, self::$known_elements ) ) {
			return self::$known_elements[ $name ];
		} else {
			return false;
		}
	}

	/**
	 * Verify an element is valid.
	 *
	 * @param string $name Name.
	 *
	 * @return bool
	 */
	public static function is_known_element( string $name ): bool {
		return self::known_elements( $name ) !== false;
	}

	/**
	 * Compare two languages by name.
	 *
	 * @param string $a A.
	 * @param string $b B.
	 *
	 * @return int
	 */
	public static function langcmp( string $a, string $b ): int {
		$a = strtolower( $a->name() );
		$b = strtolower( $b->name() );

		if ( $a === $b ) {
			return 0;
		} else {
			return ( $a < $b ) ? - 1 : 1;
		}
	}

	/**
	 * Sort by name.
	 *
	 * @param array $langs Languages.
	 *
	 * @return array
	 */
	public static function sort_by_name( array $langs ): array {

		// Sort by name.
		usort( $langs, 'Urvanov_Syntax_Highlighter_Langs::langcmp' );
		$sorted_lags = array();
		foreach ( $langs as $lang ) {
			$sorted_lags[ $lang->id() ] = $lang;
		}

		return $sorted_lags;
	}

	/**
	 * Is parsed.
	 *
	 * @param mixed $id ID.
	 *
	 * @return bool
	 */
	public function is_parsed( $id = null ): bool {
		$lang = $this->get( $id );

		if ( null === $id ) {

			// Determine if all langs are successfully parsed.
			foreach ( $this->get() as $lang ) {
				if ( $lang->state() !== Urvanov_Syntax_Highlighter_Lang::PARSED_SUCCESS ) {
					return false;
				}
			}

			return true;
		} elseif ( false !== $lang ) {
			return $lang->is_parsed();
		}

		return false;
	}

	/**
	 * Is default.
	 *
	 * @param mixed $id ID.
	 *
	 * @return bool
	 */
	public function is_default( $id ): bool {
		$lang = $this->get( $id );

		if ( false !== $lang ) {
			return $lang->is_default();
		}

		return false;
	}
}

/**
 * Individual language.
 *
 * Class Urvanov_Syntax_Highlighter_Lang
 */
class Urvanov_Syntax_Highlighter_Lang extends Urvanov_Syntax_Highlighter_Version_Resource { // phpcs:ignore

	/**
	 * Ext.
	 *
	 * @var array
	 */
	private $ext = array();

	/**
	 * Aliases.
	 *
	 * @var array
	 */
	private $aliases = array();

	/**
	 * Delimiters.
	 *
	 * @var string
	 */
	private $delimiters = '';

	/**
	 * Associative array of CrayonElement objects.
	 *
	 * @var array
	 */
	private $elements = array();

	/**
	 * State.
	 *
	 * @var int
	 */
	private $state = self::UNPARSED;

	/**
	 * Modes.
	 *
	 * @var bool[]
	 */
	private $modes;

	/**
	 * Whether this language allows Multiple Highlighting from other languages.
	 */
	const PARSED_ERRORS = - 1;

	/**
	 * Unparsed.
	 */
	const UNPARSED = 0;

	/**
	 * Parsed success.
	 */
	const PARSED_SUCCESS = 1;

	/**
	 * Urvanov_Syntax_Highlighter_Lang constructor.
	 *
	 * @param mixed $id ID.
	 * @param null  $name Name.
	 */
	public function __construct( $id, $name = null ) {
		parent::__construct( $id, $name );

		$this->modes = Urvanov_Syntax_Highlighter_Parser::modes();
	}

	/**
	 * Clean ID.
	 *
	 * @param mixed $id ID.
	 *
	 * @return array|string|string[]|null
	 */
	public function clean_id( $id ) {
		$id = UrvanovSyntaxHighlighterUtil::space_to_hyphen( strtolower( trim( $id ) ) );

		return preg_replace( '/[^\w\-+#]/msi', '', $id );
	}

	/**
	 * Ext.
	 *
	 * @param mixed $ext Ext.
	 *
	 * @return array
	 */
	public function ext( $ext = null ): array {
		if ( null === $ext ) {
			return $this->ext;
		} elseif ( is_array( $ext ) && ! empty( $ext ) ) {
			foreach ( $ext as $e ) {
				$this->ext( $e );
			}
		} elseif ( is_string( $ext ) && ! empty( $ext ) && ! in_array( $ext, $this->ext, true ) ) {
			$ext         = strtolower( $ext );
			$ext         = str_replace( '.', '', $ext );
			$this->ext[] = $ext;
		}
	}

	/**
	 * Has ext.
	 *
	 * @param mixed $ext Ext.
	 *
	 * @return bool
	 */
	public function has_ext( $ext ): bool {
		return is_string( $ext ) && in_array( $ext, $this->ext, true );
	}

	/**
	 * Alias.
	 *
	 * @param string $alias Alias.
	 *
	 * @return array
	 */
	public function alias( $alias = null ): array {
		if ( null === $alias ) {
			return $this->aliases;
		} elseif ( is_array( $alias ) && ! empty( $alias ) ) {
			foreach ( $alias as $a ) {
				$this->alias( $a );
			}
		} elseif ( is_string( $alias ) && ! empty( $alias ) && ! in_array( $alias, $this->aliases, true ) ) {
			$alias           = strtolower( $alias );
			$this->aliases[] = $alias;
		}
	}

	/**
	 * Has alias.
	 *
	 * @param string $alias Alias.
	 *
	 * @return bool
	 */
	public function has_alias( string $alias ): bool {
		return is_string( $alias ) && in_array( $alias, $this->aliases, true );
	}

	/**
	 * Delimiter.
	 *
	 * @param string $delim Delimiter.
	 *
	 * @return string
	 */
	public function delimiter( $delim = null ): string {
		if ( null === $delim ) {
			return $this->delimiters;
			// Convert to regex for capturing delimiters.
		} elseif ( is_string( $delim ) && ! empty( $delim ) ) {
			$this->delimiters = '(?:' . $delim . ')';
		} elseif ( is_array( $delim ) && ! empty( $delim ) ) {
			$count = count( $delim );
			for ( $i = 0; $i < $count; $i ++ ) {
				$delim[ $i ] = UrvanovSyntaxHighlighterUtil::esc_atomic( $delim[ $i ] );
			}

			$this->delimiters = '(?:' . implode( ')|(?:', $delim ) . ')';
		}
	}

	/**
	 * RegEx.
	 *
	 * @param object $element Element.
	 *
	 * @return string
	 */
	public function regex( $element = null ): string {
		if ( null === $element ) {
			$regexes = array();
			foreach ( $this->elements as $element ) {
				$regexes[] = $element->regex();
			}

			return '#(?:(' . implode( ')|(', array_values( $regexes ) ) . '))#' .
					( $this->mode( Urvanov_Syntax_Highlighter_Parser::CASE_INSENSITIVE ) ? 'i' : '' ) .
					( $this->mode( Urvanov_Syntax_Highlighter_Parser::MULTI_LINE ) ? 'm' : '' ) .
					( $this->mode( Urvanov_Syntax_Highlighter_Parser::SINGLE_LINE ) ? 's' : '' );
		} elseif ( is_string( $element ) && array_key_exists( $element, $this->elements ) ) {
			return $this->elements[ $element ]->regex();
		}
	}

	/**
	 * Retrieve by element name or set by CrayonElement.
	 *
	 * @param string $name Name.
	 * @param object $element Element.
	 *
	 * @return mixed
	 */
	public function element( $name = '', $element = null ) {
		if ( is_string( $name ) ) {
			$name = trim( strtoupper( $name ) );
			if ( array_key_exists( $name, $this->elements ) && null === $element ) {
				return $this->elements[ $name ];
			} elseif ( get_class( $element ) === URVANOV_SYNTAX_HIGHLIGHTER_ELEMENT_CLASS ) {
				$this->elements[ $name ] = $element;
			}
		}
	}

	/**
	 * Elements.
	 *
	 * @return array
	 */
	public function elements(): array {
		return $this->elements;
	}

	/**
	 * Mode.
	 *
	 * @param string $name Name.
	 * @param mixed  $value Value.
	 *
	 * @return bool|bool[]
	 */
	public function mode( $name = null, $value = null ) {
		if ( is_string( $name ) && Urvanov_Syntax_Highlighter_Parser::is_mode( $name ) ) {
			$name = trim( strtoupper( $name ) );
			if ( null === $value && array_key_exists( $name, $this->modes ) ) {
				return $this->modes[ $name ];
			} elseif ( is_string( $value ) ) {
				if ( UrvanovSyntaxHighlighterUtil::str_equal_array( trim( $value ), array( 'ON', 'YES', '1' ) ) ) {
					$this->modes[ $name ] = true;
				} elseif ( UrvanovSyntaxHighlighterUtil::str_equal_array( trim( $value ), array( 'OFF', 'NO', '0' ) ) ) {
					$this->modes[ $name ] = false;
				}
			}
		} else {
			return $this->modes;
		}
	}

	/**
	 * State.
	 *
	 * @param int $state State.
	 *
	 * @return int
	 */
	public function state( $state = null ): int {
		if ( null === $state ) {
			return $this->state;
		} elseif ( is_int( $state ) ) {
			if ( $state < 0 ) {
				$this->state = self::PARSED_ERRORS;
			} elseif ( $state > 0 ) {
				$this->state = self::PARSED_SUCCESS;
			} elseif ( 0 === $state ) {
				$this->state = self::UNPARSED;
			}
		}
	}

	/**
	 * State info.
	 *
	 * @return string
	 */
	public function state_info(): string {
		switch ( $this->state ) {
			case self::PARSED_ERRORS:
				return 'Parsed With Errors';
			case self::PARSED_SUCCESS:
				return 'Successfully Parsed';
			case self::UNPARSED:
				return 'Not Parsed';
			default:
				return 'Undetermined';
		}
	}

	/**
	 * Is parsed.
	 *
	 * @return bool
	 */
	public function is_parsed(): bool {
		return ( self::UNPARSED !== $this->state );
	}

	/**
	 * Is default.
	 *
	 * @return bool
	 */
	public function is_default(): bool {
		return Urvanov_Syntax_Highlighter_Langs::DEFAULT_LANG === $this->id();
	}
}

/**
 * Class Urvanov_Syntax_Highlighter_Element
 */
class Urvanov_Syntax_Highlighter_Element { // phpcs:ignore

	/**
	 * The pure regex syntax without any modifiers or delimiters.
	 *
	 * @var string
	 */
	private $name = '';

	/**
	 * CSS.
	 *
	 * @var string
	 */
	private $css = '';

	/**
	 * RegEx.
	 *
	 * @var string
	 */
	private $regex = '';

	/**
	 * Fallback.
	 *
	 * @var string
	 */
	private $fallback = '';

	/**
	 * Path.
	 *
	 * @var string
	 */
	private $path = '';

	/**
	 * Urvanov_Syntax_Highlighter_Element constructor.
	 *
	 * @param string $name Name.
	 * @param string $path Path.
	 * @param string $regex RegEx.
	 */
	public function __construct( string $name, string $path, string $regex ) {
		$this->name( $name );
		$this->path( $path );
		$this->regex( $regex );
	}

	/**
	 * __toString
	 *
	 * @return false|string
	 */
	public function __toString() {
		return $this->regex();
	}

	/**
	 * Name.
	 *
	 * @param string $name Name.
	 *
	 * @return string
	 */
	public function name( $name = null ): string {
		if ( null === $name ) {
			return $this->name;
		} elseif ( is_string( $name ) ) {
			$name = trim( strtoupper( $name ) );
			if ( Urvanov_Syntax_Highlighter_Langs::is_known_element( $name ) ) {

				// If known element, set CSS to known class.
				$this->css( Urvanov_Syntax_Highlighter_Langs::known_elements( $name ) );
			}
			$this->name = $name;
		}
	}

	/**
	 * RegEx.
	 *
	 * @param string $regex RegEx.
	 *
	 * @return false|string
	 */
	public function regex( $regex = null ) {
		if ( null === $regex ) {
			return $this->regex;
		} elseif ( is_string( $regex ) ) {
			$result = Urvanov_Syntax_Highlighter_Parser::validate_regex( $regex, $this );

			if ( false !== $result ) {
				$this->regex = $result;
			} else {
				return false;
			}
		}
	}

	/**
	 * CSS.
	 *
	 * @param string $css CSS string.
	 *
	 * @return string
	 */
	public function css( $css = null ): string {
		if ( null === $css ) {
			return $this->css;
		} elseif ( is_string( $css ) ) {
			$this->css = Urvanov_Syntax_Highlighter_Parser::validate_css( $css );
		}
	}

	/**
	 * Fallback.
	 *
	 * @param mixed $fallback Fallback.
	 *
	 * @return string
	 */
	public function fallback( $fallback = null ): string {
		if ( null === $fallback ) {
			return $this->fallback;
		} elseif ( is_string( $fallback ) && Urvanov_Syntax_Highlighter_Langs::is_known_element( $fallback ) ) {
			$this->fallback = $fallback;
		}
	}

	/**
	 * Path.
	 *
	 * @param string $path Path.
	 *
	 * @return string
	 */
	public function path( $path = null ): string {
		if ( null === $path ) {
			return $this->path;
		} elseif ( is_string( $path ) && file_exists( $path ) ) {
			$this->path = $path;
		}
	}
}

<?php
/**
 * Parser Class
 *
 * @package   Crayon Syntax Highlighter
 * @author    Fedor Urvanov, Aram Kocharyan
 * @copyright Copyright 2013, Aram Kocharyan
 * @link      https://urvanov.ru
 */

defined( 'ABSPATH' ) || exit;

require_once 'class-urvanov-syntax-highlighter-global.php';
require_once URVANOV_SYNTAX_HIGHLIGHTER_LANGS_PHP;

/**
 * Manages parsing the syntax for any given language, constructing the regex, and validating the elements.
 * Class Urvanov_Syntax_Highlighter_Parser
 */
class Urvanov_Syntax_Highlighter_Parser {

	/**
	 * Caase insensitive.
	 */
	const CASE_INSENSITIVE = 'CASE_INSENSITIVE';

	/**
	 * Multi line.
	 */
	const MULTI_LINE = 'MULTI_LINE';

	/**
	 * Single line.
	 */
	const SINGLE_LINE = 'SINGLE_LINE';

	/**
	 * Allowed mixed.
	 */
	const ALLOW_MIXED = 'ALLOW_MIXED';

	/**
	 * HTML Char.
	 */
	const HTML_CHAR = 'HTML_CHAR';

	/**
	 * CHar RegEx.
	 */
	const HTML_CHAR_REGEX = '<|>|(&([\w-]+);?)|[ \t]+';

	/**
	 * Highlighter element.
	 */
	const URVANOV_SYNTAX_HIGHLIGHTER_ELEMENT = 'URVANOV_SYNTAX_HIGHLIGHTER_ELEMENT';

	/**
	 * Highlighter element RegEx
	 */
	const URVANOV_SYNTAX_HIGHLIGHTER_ELEMENT_REGEX = '\{\{urvanov-syntax-highlighter-internal:[^\}]*\}\}';

	/**
	 * RegEx capture.
	 */
	const URVANOV_SYNTAX_HIGHLIGHTER_ELEMENT_REGEX_CAPTURE = '\{\{urvanov-syntax-highlighter-internal:([^\}]*)\}\}';

	/**
	 * Modes.
	 *
	 * @var bool[]
	 */
	private static $modes = array(
		self::CASE_INSENSITIVE => true,
		self::MULTI_LINE       => true,
		self::SINGLE_LINE      => true,
		self::ALLOW_MIXED      => true,
	);

	/**
	 * Urvanov_Syntax_Highlighter_Parser constructor.
	 */
	private function __construct() {}

	/**
	 * Parse all languages stored in Urvanov_Syntax_Highlighter_Langs.
	 * Avoid using this unless you must list the details in language files for all languages.
	 *
	 * @return array Array of all loaded Urvanov_Syntax_Highlighter_Langs.
	 */
	public static function parse_all() {
		$langs = Urvanov_Syntax_Highlighter_Resources::langs()->get();
		if ( empty( $langs ) ) {
			return false;
		}
		foreach ( $langs as $lang ) {
			self::parse( $lang->id() );
		}

		return $langs;
	}

	/**
	 * Read a syntax file and parse the regex rules within it, this may require several other
	 * files containing lists of keywords and such to be read. Updates the parsed elements and
	 * regex in the Urvanov_Syntax_Highlighter_Lang with the given $id.
	 *
	 * @param mixed $id ID.
	 *
	 * @return false|void
	 */
	public static function parse( $id ) {

		// Verify the language is loaded and has not been parsed before.
		$lang = Urvanov_Syntax_Highlighter_Resources::langs()->get( $id );
		if ( ! $lang ) {
			UrvanovSyntaxHighlighterLog::syslog( "The language with id '$id' was not loaded and could not be parsed." );

			return false;
		} elseif ( $lang->is_parsed() ) {
			return;
		}

		// Read language file.
		$path = Urvanov_Syntax_Highlighter_Resources::langs()->path( $id );
		UrvanovSyntaxHighlighterLog::debug( 'Parsing language ' . $path );
		$file = UrvanovSyntaxHighlighterUtil::lines( $path, 'wcs' );
		if ( false === $file ) {
			UrvanovSyntaxHighlighterLog::debug( 'Parsing failed ' . $path );

			return false;
		}

		// Extract the language name.
		$name_pattern = '#^[ \t]*name[ \t]+([^\r\n]+)[ \t]*#mi';
		preg_match( $name_pattern, $file, $name );
		if ( count( $name ) > 1 ) {
			$name = $name[1];
			$lang->name( $name );
			$file = preg_replace( $name_pattern, '', $file );
		} else {
			$name = $lang->id();
		}

		// Extract the language version.
		$version_pattern = '#^[ \t]*version[ \t]+([^\r\n]+)[ \t]*#mi';
		preg_match( $version_pattern, $file, $version );
		if ( count( $version ) > 1 ) {
			$version = $version[1];
			$lang->version( $version );
			$file = preg_replace( $version_pattern, '', $file );
		}

		// Extract the modes.
		$mode_pattern = '#^[ \t]*(' . implode( '|', array_keys( self::$modes ) ) . ')[ \t]+(?:=[ \t]*)?([^\r\n]+)[ \t]*#mi';
		preg_match_all( $mode_pattern, $file, $mode_matches );
		if ( count( $mode_matches ) === 3 ) {
			$count = count( $mode_matches[0] );

			for ( $i = 0; $i < $count; $i ++ ) {
				$lang->mode( $mode_matches[1][ $i ], $mode_matches[2][ $i ] );
			}
			$file = preg_replace( $mode_pattern, '', $file );
		}

		/* Add reserved Crayon element. This is used by Crayon internally. */
		$urvanov_syntax_highlighter_element = new Urvanov_Syntax_Highlighter_Element( self::URVANOV_SYNTAX_HIGHLIGHTER_ELEMENT, $path, self::URVANOV_SYNTAX_HIGHLIGHTER_ELEMENT_REGEX );
		$lang->element( self::URVANOV_SYNTAX_HIGHLIGHTER_ELEMENT, $urvanov_syntax_highlighter_element );

		// Extract elements, classes and regex.
		$pattern = '#^[ \t]*([\w:]+)[ \t]+(?:\[([\w\t ]*)\][ \t]+)?([^\r\n]+)[ \t]*#m';
		preg_match_all( $pattern, $file, $matches );

		if ( ! empty( $matches[0] ) ) {
			$elements = $matches[1];
			$classes  = $matches[2];
			$regexes  = $matches[3];
		} else {
			UrvanovSyntaxHighlighterLog::syslog( "No regex patterns and/or elements were parsed from language file at '$path'." );
		}

		// Remember state in case we encounter catchable exceptions.
		$error = false;
		$count = count( $matches[0] );
		for ( $i = 0; $i < $count; $i ++ ) {

			// References.
			$name  = &$elements[ $i ];
			$class = &$classes[ $i ];
			$regex = &$regexes[ $i ];
			$name  = trim( strtoupper( $name ) );

			// Ensure both the element and regex are valid.
			if ( empty( $name ) || empty( $regex ) ) {
				UrvanovSyntaxHighlighterLog::syslog( "Element(s) and/or regex(es) are missing in '$path'." );
				$error = true;
				continue;
			}

			// Look for fallback element.
			$pieces = explode( ':', $name );
			if ( 2 === count( $pieces ) ) {
				$name     = $pieces[0];
				$fallback = $pieces[1];
			} elseif ( 1 === count( $pieces ) ) {
				$name     = $pieces[0];
				$fallback = '';
			} else {
				UrvanovSyntaxHighlighterLog::syslog( "Too many colons found in element name '$name' in '$path'" );
				$error = true;
				continue;
			}
			// Create a new Urvanov_Syntax_Highlighter_Element.
			$element = new Urvanov_Syntax_Highlighter_Element( $name, $path, '' );
			$element->fallback( $fallback );
			if ( ! empty( $class ) ) {
				// Avoid setting known css to blank.
				$element->css( $class );
			}
			if ( $element->regex( $regex ) === false ) {
				$error = true;
				continue;
			}
			// Add the regex to the element.
			$lang->element( $name, $element );
			$state = $error ? Urvanov_Syntax_Highlighter_Lang::PARSED_ERRORS : Urvanov_Syntax_Highlighter_Lang::PARSED_SUCCESS;
			$lang->state( $state );
		}

		/**
		 * Prevents < > and other html entities from being printed as is, which could lead to actual html tags
		 * from the printed code appearing on the page - not good. This can also act to color any HTML entities
		 * that are not picked up by previously defined elements.
		 */
		$html = new Urvanov_Syntax_Highlighter_Element( self::HTML_CHAR, $path, self::HTML_CHAR_REGEX );
		$lang->element( self::HTML_CHAR, $html );
	}

	/**
	 * Validates regex and accesses data stored in a Urvanov_Syntax_Highlighter_Element.
	 *
	 * @param string $regex   RegEx.
	 * @param object $element Element.
	 *
	 * @return array|false|string|string[]|null
	 */
	public static function validate_regex( string $regex, $element = null ) {
		if ( is_string( $regex ) && get_class( $element ) === URVANOV_SYNTAX_HIGHLIGHTER_ELEMENT_CLASS ) {

			// If the (?alt) tag has been used, insert the file into the regex.
			$file = self::regex_match( '#\(\?alt:(.+?)\)#', $regex );
			if ( 2 === count( $file ) ) {

				// Element 0 has full match, 1 has captured groups.
				$count = count( $file[1] );
				for ( $i = 0; $i < $count; $i ++ ) {
					$file_lines = UrvanovSyntaxHighlighterUtil::lines( dirname( $element->path() ) . Urvanov_Syntax_Highlighter_Global::fix_s() . $file[1][ $i ], 'rcwh' );
					if ( false !== $file_lines ) {
						$file_lines = implode( '|', $file_lines );

						// If any spaces exist, treat them as whitespace.
						$file_lines = preg_replace( '#[ \t]+#msi', '\s+', $file_lines );
						$regex      = str_replace( $file[0][ $i ], "(?:$file_lines)", $regex );
					} else {
						UrvanovSyntaxHighlighterLog::syslog( "Parsing of '{$element->path()}' failed, an (?alt) tag failed for the element '{$element->name()}'" );

						return false;
					}
				}
			}

			// If the (?default:element) function is used, replace the regex with the default, if exists.
			$def = self::regex_match( '#\(\?default(?:\:(\w+))?\)#', $regex );
			if ( 2 === count( $def ) ) {

				// Load default language.
				$default = Urvanov_Syntax_Highlighter_Resources::langs()->get( Urvanov_Syntax_Highlighter_Langs::DEFAULT_LANG );

				// If default has not been loaded, we can't use it, skip the element.
				if ( ! $default ) {
					UrvanovSyntaxHighlighterLog::syslog( "Could not use default regex in the element '{$element->name()}' in '{$element->path()}'" );

					return false;
				}
				$count = count( $def[1] );
				for ( $i = 0; $i < $count; $i ++ ) {

					// If an element has been provided.
					$element_name    = ( ! empty( $def[1][ $i ] ) ) ? $def[1][ $i ] : $element->name();
					$default_element = $default->element( $element_name );

					if ( false !== $default_element ) {
						$regex = str_replace( $def[0][ $i ], '(?:' . $default_element->regex() . ')', $regex );
					} else {
						UrvanovSyntaxHighlighterLog::syslog( "The language at '{$element->path()}' referred to the Default Language regex for element '{$element->name()}', which did not exist." );
						if ( URVANOV_SYNTAX_HIGHLIGHTER_DEBUG ) {
							UrvanovSyntaxHighlighterLog::syslog( 'Default language URL: ' . Urvanov_Syntax_Highlighter_Resources::langs()->url( Urvanov_Syntax_Highlighter_Langs::DEFAULT_LANG ) );
							UrvanovSyntaxHighlighterLog::syslog( 'Default language Path: ' . Urvanov_Syntax_Highlighter_Resources::langs()->path( Urvanov_Syntax_Highlighter_Langs::DEFAULT_LANG ) );
						}

						return false;
					}
				}
			}

			// If the (?html) tag is used, escape characters in html (<, > and &).
			$html = self::regex_match( '#\(\?html:(.+?)\)#', $regex );
			if ( 2 === count( $html ) ) {
				$count = count( $html[1] );

				for ( $i = 0; $i < $count; $i ++ ) {
					$regex = str_replace( $html[0][ $i ], htmlentities( $html[1][ $i ] ), $regex );
				}
			}

			// Ensure all parenthesis are atomic to avoid conflicting with element matches.
			$regex = UrvanovSyntaxHighlighterUtil::esc_atomic( $regex );

			// Escape #, this is our delimiter.
			$regex = UrvanovSyntaxHighlighterUtil::esc_hash( $regex );

			// Test if regex is valid.
			if ( false === preg_match( "#$regex#", '' ) ) {
				UrvanovSyntaxHighlighterLog::syslog( "The regex for the element '{$element->name()}' in '{$element->path()}' is not valid." );

				return false;
			}

			return $regex;
		} else {
			return '';
		}
	}

	/**
	 * Validate CSS.
	 *
	 * @param string $css CSS.
	 *
	 * @return string
	 */
	public static function validate_css( string $css ): string {
		if ( is_string( $css ) ) {

			// Remove dots in CSS class and convert to lowercase.
			$css     = str_replace( '.', '', $css );
			$css     = strtolower( $css );
			$css     = explode( ' ', $css );
			$css_str = '';

			foreach ( $css as $c ) {
				if ( ! empty( $c ) ) {
					$css_str .= $c . ' ';
				}
			}

			return trim( $css_str );
		} else {
			return '';
		}
	}

	/**
	 * RegEx match.
	 *
	 * @param string $pattern RegEx.
	 * @param string $subject Subject.
	 *
	 * @return array|mixed
	 */
	public static function regex_match( string $pattern, string $subject ) {
		if ( preg_match_all( $pattern, $subject, $matches ) ) {
			return $matches;
		}

		return array();
	}

	/**
	 * Modes.
	 *
	 * @return bool[]
	 */
	public static function modes(): array {
		return self::$modes;
	}

	/**
	 * Is mode.
	 *
	 * @param string $name Name.
	 *
	 * @return bool
	 */
	public static function is_mode( string $name ): bool {
		return is_string( $name ) && array_key_exists( $name, self::$modes );
	}
}

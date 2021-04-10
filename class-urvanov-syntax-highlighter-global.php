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

/**
 * Debug switch.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_DEBUG = false;

/**
 * Tag editor switch.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_TAG_EDITOR = true;

/**
 * Theme editor switch.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_THEME_EDITOR = true;

/**
 * Minify switch.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_MINIFY = false;

// These are overridden by functions since v1.1.1.
$urvanov_syntax_highlighter_version     = '2.9.0';
$urvanov_syntax_highlighter_date        = '10th April, 2021';
$urvanov_syntax_highlighter_author      = 'Fedor Urvanov';
$urvanov_syntax_highlighter_author_site = 'https://urvanov.ru';
$urvanov_syntax_highlighter_donate      = 'https://money.yandex.ru/to/41001288941320';
$urvanov_syntax_highlighter_website     = 'https://github.com/urvanov-ru/crayon-syntax-highlighter';
$urvanov_syntax_highlighter_email       = 'fedor@urvanov.ru';
$urvanov_syntax_highlighter_twitter     = 'https://twitter.com/crayonsyntax';
$urvanov_syntax_highlighter_git         = 'https://github.com/urvanov-ru/crayon-syntax-highlighter';
$urvanov_syntax_highlighter_plugin_wp   = 'https://wordpress.org/plugins/urvanov-syntax-highlighter/';

if ( ! class_exists( 'Urvanov_Syntax_Highlighter_Global' ) ) {

	/**
	 * Class Urvanov_Syntax_Highlighter_Global
	 */
	class Urvanov_Syntax_Highlighter_Global {

		/**
		 * Check for forwardslash/backslash in folder path to structure paths
		 * Old name crayon_s.
		 *
		 * @param string $url URL.
		 *
		 * @return string
		 */
		public static function fix_s( $url = '' ): string {
			$url = strval( $url );
			if ( ! empty( $url ) && ! preg_match( '#(\\\\|/)$#', $url ) ) {
				return $url . '/';
			} elseif ( empty( $url ) ) {
				return '/';
			} else {
				return $url;
			}
		}

		/**
		 * Returns path using forward slashes, slash added at the end
		 * Old name crayon_pf.
		 *
		 * @param string $url   URL.
		 * @param bool   $slash Slash.
		 *
		 * @return array|string|string[]
		 */
		public static function path_forward_slashes( string $url, $slash = true ) {
			$url = trim( $url );
			if ( $slash ) {
				$url = self::fix_s( $url );
			}

			return str_replace( '\\', '/', $url );
		}

		/**
		 * Returns path using back slashes.
		 * Old name: crayon_pb.
		 *
		 * @param string $url URL.
		 *
		 * @return array|string|string[]
		 */
		public static function path_back_slashes( string $url ) {
			return str_replace( '/', '\\', self::fix_s( trim( $url ) ) );
		}

		/**
		 * Get/Set plugin information.
		 * Old name: crayon_set_info.
		 *
		 * @param array $info_array Plugin info.
		 */
		public static function set_info( array $info_array ) {
			global $urvanov_syntax_highlighter_version, $urvanov_syntax_highlighter_date, $urvanov_syntax_highlighter_author, $urvanov_syntax_highlighter_website;

			if ( ! is_array( $info_array ) ) {
				return;
			}

			self::set_info_key( 'Version', $info_array, $urvanov_syntax_highlighter_version );
			self::set_info_key( 'Date', $info_array, $urvanov_syntax_highlighter_date );
			self::set_info_key( 'AuthorName', $info_array, $urvanov_syntax_highlighter_author );
			self::set_info_key( 'PluginURI', $info_array, $urvanov_syntax_highlighter_website );
		}

		/**
		 * Set info key.
		 *
		 * @param string $key   Key.
		 * @param array  $array Array...really tho?.
		 * @param mixed  $info Info.
		 *
		 * @return bool
		 */
		public static function set_info_key( string $key, array $array, &$info ): bool {
			if ( array_key_exists( $key, $array ) ) {
				$info = $array[ $key ];
			} else {
				return false;
			}

			return true;
		}

		/**
		 * V Args.
		 *
		 * @param mixed $var Var.
		 * @param mixed $default Default.
		 */
		public static function vargs( &$var, $default ) {
			$var = $var ?? $default;
		}

		/**
		 * Checks if the input is a valid PHP file and matches the $valid filename.
		 * Old name: crayon_is_php_file.
		 *
		 * @param string $filepath File path.
		 * @param mixed  $valid    Valid.
		 *
		 * @return bool
		 */
		public static function is_php_file( string $filepath, $valid ): bool {
			$path = pathinfo( self::path_forward_slashes( $filepath ) );

			return is_file( $filepath ) && 'php' === $path['extension'] && $valid === $path['filename'];
		}

		/**
		 * Is path URL.
		 *
		 * @param string $path Path.
		 *
		 * @return bool
		 */
		public static function is_path_url( string $path ): bool {
			$parts = wp_parse_url( $path );

			return isset( $parts['scheme'] ) && strlen( $parts['scheme'] ) > 1;
		}

		/**
		 * Load translations.
		 */
		public static function load_plugin_textdomain() {
			if ( function_exists( 'load_plugin_textdomain' ) ) {
				load_plugin_textdomain( 'urvanov-syntax-highlighter', false, URVANOV_SYNTAX_HIGHLIGHTER_DIR . URVANOV_SYNTAX_HIGHLIGHTER_TRANS_DIR );
			}
		}
	}
}

/**
 * Highlighter title.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_HIGHLIGHTER = 'Urvanov_Syntax_Highlighter';

/**
 * Element class.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_ELEMENT_CLASS = 'Urvanov_Syntax_Highlighter_Element';

/**
 * Settings.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_SETTING_CLASS = 'Urvanov_Syntax_Highlighter_Setting';

/**
 * Directory.
 */
define( 'URVANOV_SYNTAX_HIGHLIGHTER_DIR', Urvanov_Syntax_Highlighter_Global::path_forward_slashes( basename( dirname( __FILE__ ) ) ) );

/**
 * Language directory.
 */
define( 'URVANOV_SYNTAX_HIGHLIGHTER_LANG_DIR', Urvanov_Syntax_Highlighter_Global::fix_s( 'langs' ) );

/**
 * Theme directory.
 */
define( 'URVANOV_SYNTAX_HIGHLIGHTER_THEME_DIR', Urvanov_Syntax_Highlighter_Global::fix_s( 'themes' ) );

/**
 * Font directory.
 */
define( 'URVANOV_SYNTAX_HIGHLIGHTER_FONT_DIR', Urvanov_Syntax_Highlighter_Global::fix_s( 'fonts' ) );

/**
 * Util directory.
 */
define( 'URVANOV_SYNTAX_HIGHLIGHTER_UTIL_DIR', Urvanov_Syntax_Highlighter_Global::fix_s( 'util' ) );

/**
 * CSS directory.
 */
define( 'URVANOV_SYNTAX_HIGHLIGHTER_CSS_DIR', Urvanov_Syntax_Highlighter_Global::fix_s( 'css' ) );

/**
 * SRC directory.
 */
define( 'URVANOV_SYNTAX_HIGHLIGHTER_CSS_SRC_DIR', URVANOV_SYNTAX_HIGHLIGHTER_CSS_DIR . Urvanov_Syntax_Highlighter_Global::fix_s( 'src' ) );

/**
 * CSS Min directory.
 */
define( 'URVANOV_SYNTAX_HIGHLIGHTER_CSS_MIN_DIR', URVANOV_SYNTAX_HIGHLIGHTER_CSS_DIR . Urvanov_Syntax_Highlighter_Global::fix_s( 'min' ) );

/**
 * JavaScript directory.
 */
define( 'URVANOV_SYNTAX_HIGHLIGHTER_JS_DIR', Urvanov_Syntax_Highlighter_Global::fix_s( 'js' ) );

/**
 * JavaScript SRC directory.
 */
define( 'URVANOV_SYNTAX_HIGHLIGHTER_JS_SRC_DIR', URVANOV_SYNTAX_HIGHLIGHTER_JS_DIR . Urvanov_Syntax_Highlighter_Global::fix_s( 'src' ) );

/**
 * Javascript min directory.
 */
define( 'URVANOV_SYNTAX_HIGHLIGHTER_JS_MIN_DIR', URVANOV_SYNTAX_HIGHLIGHTER_JS_DIR . Urvanov_Syntax_Highlighter_Global::fix_s( 'min' ) );

/**
 * Translator directory.
 */
define( 'URVANOV_SYNTAX_HIGHLIGHTER_TRANS_DIR', Urvanov_Syntax_Highlighter_Global::fix_s( 'trans' ) );

/**
 * Theme editor directory.
 */
define( 'URVANOV_SYNTAX_HIGHLIGHTER_THEME_EDITOR_DIR', Urvanov_Syntax_Highlighter_Global::fix_s( 'theme-editor' ) );

/**
 * Tag editor directory.
 */
define( 'URVANOV_SYNTAX_HIGHLIGHTER_TAG_EDITOR_DIR', Urvanov_Syntax_Highlighter_Global::fix_s( 'tag-editor' ) );

/**
 * Root path.
 */
define( 'URVANOV_SYNTAX_HIGHLIGHTER_ROOT_PATH', Urvanov_Syntax_Highlighter_Global::path_forward_slashes( dirname( __FILE__ ) ) );

/**
 * Language path.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_LANG_PATH = URVANOV_SYNTAX_HIGHLIGHTER_ROOT_PATH . URVANOV_SYNTAX_HIGHLIGHTER_LANG_DIR;

/**
 * Theme path.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_THEME_PATH = URVANOV_SYNTAX_HIGHLIGHTER_ROOT_PATH . URVANOV_SYNTAX_HIGHLIGHTER_THEME_DIR;

/**
 * Font path.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_FONT_PATH = URVANOV_SYNTAX_HIGHLIGHTER_ROOT_PATH . URVANOV_SYNTAX_HIGHLIGHTER_FONT_DIR;

/**
 * Util path.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_UTIL_PATH = URVANOV_SYNTAX_HIGHLIGHTER_ROOT_PATH . URVANOV_SYNTAX_HIGHLIGHTER_UTIL_DIR;

/**
 * Tag editor path.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_TAG_EDITOR_PATH = URVANOV_SYNTAX_HIGHLIGHTER_ROOT_PATH . URVANOV_SYNTAX_HIGHLIGHTER_UTIL_DIR . URVANOV_SYNTAX_HIGHLIGHTER_TAG_EDITOR_DIR;

/**
 * Theme editor path.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_THEME_EDITOR_PATH = URVANOV_SYNTAX_HIGHLIGHTER_ROOT_PATH . URVANOV_SYNTAX_HIGHLIGHTER_UTIL_DIR . URVANOV_SYNTAX_HIGHLIGHTER_THEME_EDITOR_DIR;

/**
 * Log file.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_LOG_FILE = URVANOV_SYNTAX_HIGHLIGHTER_ROOT_PATH . 'log.txt';

/**
 * Touch file.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_TOUCH_FILE = URVANOV_SYNTAX_HIGHLIGHTER_UTIL_PATH . 'touch.txt';

/**
 * Log max size file.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_LOG_MAX_SIZE = 5000000; // Bytes.

/**
 * Language ext.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_LANG_EXT = URVANOV_SYNTAX_HIGHLIGHTER_LANG_PATH . 'extensions.txt';

/**
 * Language alias.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_LANG_ALIAS = URVANOV_SYNTAX_HIGHLIGHTER_LANG_PATH . 'aliases.txt';

/**
 * Language delimiter.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_LANG_DELIM = URVANOV_SYNTAX_HIGHLIGHTER_LANG_PATH . 'delimiters.txt';

/**
 * JavaScript min directory.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_JS_MIN = URVANOV_SYNTAX_HIGHLIGHTER_JS_MIN_DIR . 'urvanov_syntax_highlighter.min.js';

/**
 * JavaScript tag editor min directory.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_JS_TE_MIN = URVANOV_SYNTAX_HIGHLIGHTER_JS_MIN_DIR . 'urvanov_syntax_highlighter.te.min.js';

/**
 * JQuery popup directory.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_JQUERY_POPUP = URVANOV_SYNTAX_HIGHLIGHTER_JS_SRC_DIR . 'jquery.popup.js';

/**
 * Syntax highlighter directory.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_JS = URVANOV_SYNTAX_HIGHLIGHTER_JS_SRC_DIR . 'urvanov_syntax_highlighter.js';

/**
 * Syntax highlighter min directory.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_JS_ADMIN = URVANOV_SYNTAX_HIGHLIGHTER_JS_SRC_DIR . 'urvanov_syntax_highlighter_admin.js';

/**
 * Utility JS.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_JS_UTIL = URVANOV_SYNTAX_HIGHLIGHTER_JS_SRC_DIR . 'util.js';

/**
 * CSS JSON JS.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_CSSJSON_JS = URVANOV_SYNTAX_HIGHLIGHTER_JS_SRC_DIR . 'cssjson.js';

/**
 * Colorpicker CSS.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_CSS_JQUERY_COLORPICKER = URVANOV_SYNTAX_HIGHLIGHTER_JS_DIR . 'jquery-colorpicker/jquery.colorpicker.css';

/**
 * Colorpicker JS.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_JS_JQUERY_COLORPICKER = URVANOV_SYNTAX_HIGHLIGHTER_JS_DIR . 'jquery-colorpicker/jquery.colorpicker.js';

/**
 * Tnycolor min JS.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_JS_TINYCOLOR = URVANOV_SYNTAX_HIGHLIGHTER_JS_DIR . 'tinycolor-min.js';

/**
 * Tsg editor JS.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_TAG_EDITOR_JS = 'urvanov_syntax_highlighter_tag_editor.js';

/**
 * Highlighter CSS.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_EDITOR_CSS = 'util/tag-editor/urvanov_syntax_highlighter_editor.css';

/**
 * Colorbox MIN JS.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_COLORBOX_JS = 'colorbox/jquery.colorbox-min.js';

/**
 * COlorbox CSS.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_COLORBOX_CSS = 'colorbox/colorbox.css';

/**
 * Tag editor WP JS.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_TAG_EDITOR_PHP = URVANOV_SYNTAX_HIGHLIGHTER_TAG_EDITOR_PATH . 'class-urvanov-syntax-highlighter-tag-editor-wp.php';

/**
 * TinyMCE JS.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_TINYMCE_JS = 'urvanov_syntax_highlighter_tinymce.js';

/**
 * Highlighet QT JS.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_QUICKTAGS_JS = 'urvanov_syntax_highlighter_qt.js';

/**
 * Highlighter style CSS.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_STYLE = URVANOV_SYNTAX_HIGHLIGHTER_CSS_SRC_DIR . 'urvanov_syntax_highlighter_style.css';

/**
 * Admin style CSS.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_STYLE_ADMIN = URVANOV_SYNTAX_HIGHLIGHTER_CSS_SRC_DIR . 'admin_style.css';

/**
 * GLobal style CSS.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_STYLE_GLOBAL = URVANOV_SYNTAX_HIGHLIGHTER_CSS_SRC_DIR . 'global_style.css';

/**
 * Highlighter min CSS.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_STYLE_MIN = URVANOV_SYNTAX_HIGHLIGHTER_CSS_MIN_DIR . 'urvanov_syntax_highlighter.min.css';

/**
 * Crayon logo.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_LOGO = URVANOV_SYNTAX_HIGHLIGHTER_CSS_DIR . 'images/crayon_logo.png';

/**
 * Donate JPG.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_DONATE_BUTTON = URVANOV_SYNTAX_HIGHLIGHTER_CSS_DIR . 'images/donate.png';

/**
 * Theme editor PHP.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_THEME_EDITOR_PHP = URVANOV_SYNTAX_HIGHLIGHTER_THEME_EDITOR_PATH . 'class-urvanov-syntax-highlighter-theme-editor.php';

/**
 * Theme Editor JS.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_THEME_EDITOR_JS = URVANOV_SYNTAX_HIGHLIGHTER_UTIL_DIR . URVANOV_SYNTAX_HIGHLIGHTER_THEME_EDITOR_DIR . 'theme_editor.js';
/**
 * Theme Editor CSS.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_THEME_EDITOR_STYLE = URVANOV_SYNTAX_HIGHLIGHTER_UTIL_DIR . URVANOV_SYNTAX_HIGHLIGHTER_THEME_EDITOR_DIR . 'theme_editor.css';

/**
 * Formatter PHP.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_FORMATTER_PHP = URVANOV_SYNTAX_HIGHLIGHTER_ROOT_PATH . 'class-urvanov-syntax-highlighter-formatter.php';

/**
 * Highlighter PHP.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_HIGHLIGHTER_PHP = URVANOV_SYNTAX_HIGHLIGHTER_ROOT_PATH . 'class-urvanov-syntax-highlighter.php';

/**
 * Language PHP.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_LANGS_PHP = URVANOV_SYNTAX_HIGHLIGHTER_ROOT_PATH . 'class-urvanov-syntax-highlighter-langs.php';

/**
 * Parser PHP.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_PARSER_PHP = URVANOV_SYNTAX_HIGHLIGHTER_ROOT_PATH . 'class-urvanov-syntax-highlighter-parser.php';

/**
 * Settings PHP.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_SETTINGS_PHP = URVANOV_SYNTAX_HIGHLIGHTER_ROOT_PATH . 'class-urvanov-syntax-highlighter-settings.php';

/**
 * Themes PHP.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_THEMES_PHP = URVANOV_SYNTAX_HIGHLIGHTER_ROOT_PATH . 'class-urvanov-syntax-highlighter-themes.php';

/**
 * FOnts PHP.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_FONTS_PHP = URVANOV_SYNTAX_HIGHLIGHTER_ROOT_PATH . 'class-urvanov-syntax-highlighter-fonts.php';

/**
 * Resource PHP.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_RESOURCE_PHP = URVANOV_SYNTAX_HIGHLIGHTER_ROOT_PATH . 'class-urvanov-syntax-highlighter-resource.php';

/**
 * Util PHP.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_UTIL_PHP = URVANOV_SYNTAX_HIGHLIGHTER_UTIL_DIR . 'class-urvanov-syntax-highlighter-util.php';

/**
 * Timer PHP.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_TIMER_PHP = URVANOV_SYNTAX_HIGHLIGHTER_UTIL_DIR . 'class-urvanov-syntax-highlighter-timer.php';

/**
 * Log PHP.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_LOG_PHP = URVANOV_SYNTAX_HIGHLIGHTER_UTIL_DIR . 'class-urvanov-syntax-highlighter-log.php';

/**
 * Load time.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_LOAD_TIME = 'Load Time';

/**
 * Format time.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_FORMAT_TIME = 'Format Time';

/**
 * BR tag.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_BR = '<br />';

/**
 * New line code.
 */
const URVANOV_SYNTAX_HIGHLIGHTER_NL = "\r\n";

require_once URVANOV_SYNTAX_HIGHLIGHTER_UTIL_PHP;
require_once URVANOV_SYNTAX_HIGHLIGHTER_TIMER_PHP;
require_once URVANOV_SYNTAX_HIGHLIGHTER_LOG_PHP;


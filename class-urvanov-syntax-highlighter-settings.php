<?php
/**
 * Settings Class
 *
 * @package   Crayon Syntax Highlighter
 * @author    Fedor Urvanov, Aram Kocharyan
 * @copyright Copyright 2013, Aram Kocharyan
 * @link      https://urvanov.ru
 */

defined( 'ABSPATH' ) || exit;

require_once 'class-urvanov-syntax-highlighter-global.php';
require_once URVANOV_SYNTAX_HIGHLIGHTER_PARSER_PHP;
require_once URVANOV_SYNTAX_HIGHLIGHTER_THEMES_PHP;

/**
 * Stores Urvanov_Syntax_Highlighter_Setting objects.
 * Each Crayon instance stores an instance of this class containing its specific settings.
 */
// Old name: CrayonSettings

/**
 * Class Urvanov_Syntax_Highlighter_Settings
 */
class Urvanov_Syntax_Highlighter_Settings {
	// Properties and Constants ===============================================
	/**
	 *
	 */
	const INVALID = - 1; // Used for invalid dropdown index
	// Plugin data
	/**
	 *
	 */
	const VERSION = 'version';

	// Added when used in HTML to avoid id conflicts
	/**
	 *
	 */
	const PREFIX = 'urvanov-syntax-highlighter-';
	/**
	 *
	 */
	const SETTING = 'urvanov-syntax-highlighter-setting';
	/**
	 *
	 */
	const SETTING_SELECTED = 'urvanov-syntax-highlighter-setting-selected';
	/**
	 *
	 */
	const SETTING_CHANGED = 'urvanov-syntax-highlighter-setting-changed';
	/**
	 *
	 */
	const SETTING_SPECIAL = 'urvanov-syntax-highlighter-setting-special';
	/**
	 *
	 */
	const SETTING_ORIG_VALUE = 'data-orig-value';

	// Global names for settings
	/**
	 *
	 */
	const THEME = 'theme';
	/**
	 *
	 */
	const FONT = 'font';
	/**
	 *
	 */
	const FONT_SIZE_ENABLE = 'font-size-enable';
	/**
	 *
	 */
	const FONT_SIZE = 'font-size';
	/**
	 *
	 */
	const LINE_HEIGHT = 'line-height';
	/**
	 *
	 */
	const PREVIEW = 'preview';
	/**
	 *
	 */
	const HEIGHT_SET = 'height-set';
	/**
	 *
	 */
	const HEIGHT_MODE = 'height-mode';
	/**
	 *
	 */
	const HEIGHT = 'height';
	/**
	 *
	 */
	const HEIGHT_UNIT = 'height-unit';
	/**
	 *
	 */
	const WIDTH_SET = 'width-set';
	/**
	 *
	 */
	const WIDTH_MODE = 'width-mode';
	/**
	 *
	 */
	const WIDTH = 'width';
	/**
	 *
	 */
	const WIDTH_UNIT = 'width-unit';
	/**
	 *
	 */
	const TOP_SET = 'top-set';
	/**
	 *
	 */
	const TOP_MARGIN = 'top-margin';
	/**
	 *
	 */
	const LEFT_SET = 'left-set';
	/**
	 *
	 */
	const LEFT_MARGIN = 'left-margin';
	/**
	 *
	 */
	const BOTTOM_SET = 'bottom-set';
	/**
	 *
	 */
	const BOTTOM_MARGIN = 'bottom-margin';
	/**
	 *
	 */
	const RIGHT_SET = 'right-set';
	/**
	 *
	 */
	const RIGHT_MARGIN = 'right-margin';
	/**
	 *
	 */
	const H_ALIGN = 'h-align';
	/**
	 *
	 */
	const FLOAT_ENABLE = 'float-enable';
	/**
	 *
	 */
	const TOOLBAR = 'toolbar';
	/**
	 *
	 */
	const TOOLBAR_OVERLAY = 'toolbar-overlay';
	/**
	 *
	 */
	const TOOLBAR_HIDE = 'toolbar-hide';
	/**
	 *
	 */
	const TOOLBAR_DELAY = 'toolbar-delay';
	/**
	 *
	 */
	const COPY = 'copy';
	/**
	 *
	 */
	const POPUP = 'popup';
	/**
	 *
	 */
	const SHOW_LANG = 'show-lang';
	/**
	 *
	 */
	const SHOW_TITLE = 'show-title';
	/**
	 *
	 */
	const STRIPED = 'striped';
	/**
	 *
	 */
	const MARKING = 'marking';
	/**
	 *
	 */
	const START_LINE = 'start-line';
	/**
	 *
	 */
	const NUMS = 'nums';
	/**
	 *
	 */
	const NUMS_TOGGLE = 'nums-toggle';
	/**
	 *
	 */
	const TRIM_WHITESPACE = 'trim-whitespace';
	/**
	 *
	 */
	const WHITESPACE_BEFORE = 'whitespace-before';
	/**
	 *
	 */
	const WHITESPACE_AFTER = 'whitespace-after';
	/**
	 *
	 */
	const TRIM_CODE_TAG = 'trim-code-tag';
	/**
	 *
	 */
	const TAB_SIZE = 'tab-size';
	/**
	 *
	 */
	const TAB_CONVERT = 'tab-convert';
	/**
	 *
	 */
	const FALLBACK_LANG = 'fallback-lang';
	/**
	 *
	 */
	const LOCAL_PATH = 'local-path';
	/**
	 *
	 */
	const SCROLL = 'scroll';
	/**
	 *
	 */
	const PLAIN = 'plain';
	/**
	 *
	 */
	const PLAIN_TOGGLE = 'plain-toggle';
	/**
	 *
	 */
	const SHOW_PLAIN = 'show-plain';
	/**
	 *
	 */
	const DISABLE_RUNTIME = 'runtime';
	/**
	 *
	 */
	const DISABLE_DATE = 'disable-date';
	/**
	 *
	 */
	const TOUCHSCREEN = 'touchscreen';
	/**
	 *
	 */
	const DISABLE_ANIM = 'disable-anim';
	/**
	 *
	 */
	const ERROR_LOG = 'error-log';
	/**
	 *
	 */
	const ERROR_LOG_SYS = 'error-log-sys';
	/**
	 *
	 */
	const ERROR_MSG_SHOW = 'error-msg-show';
	/**
	 *
	 */
	const ERROR_MSG = 'error-msg';
	/**
	 *
	 */
	const HIDE_HELP = 'hide-help';
	/**
	 *
	 */
	const CACHE = 'cache';
	/**
	 *
	 */
	const EFFICIENT_ENQUEUE = 'efficient-enqueue';
	/**
	 *
	 */
	const CAPTURE_PRE = 'capture-pre';
	/**
	 *
	 */
	const CAPTURE_MINI_TAG = 'capture-mini-tag';
	/**
	 *
	 */
	const ALTERNATE = 'alternate';
	/**
	 *
	 */
	const SHOW_ALTERNATE = 'show_alternate';
	/**
	 *
	 */
	const PLAIN_TAG = 'plain_tag';
	/**
	 *
	 */
	const SHOW_PLAIN_DEFAULT = 'show-plain-default';
	/**
	 *
	 */
	const ENQUEUE_THEMES = 'enqueque-themes';
	/**
	 *
	 */
	const ENQUEUE_FONTS = 'enqueque-fonts';
	/**
	 *
	 */
	const MAIN_QUERY = 'main-query';
	/**
	 *
	 */
	const SAFE_ENQUEUE = 'safe-enqueue';
	/**
	 *
	 */
	const INLINE_TAG = 'inline-tag';
	/**
	 *
	 */
	const INLINE_TAG_CAPTURE = 'inline-tag-capture';
	/**
	 *
	 */
	const CODE_TAG_CAPTURE = 'code-tag-capture';
	/**
	 *
	 */
	const CODE_TAG_CAPTURE_TYPE = 'code-tag-capture-type';
	/**
	 *
	 */
	const INLINE_MARGIN = 'inline-margin';
	/**
	 *
	 */
	const INLINE_WRAP = 'inline-wrap';
	/**
	 *
	 */
	const BACKQUOTE = 'backquote';
	/**
	 *
	 */
	const COMMENTS = 'comments';
	/**
	 *
	 */
	const DECODE = 'decode';
	/**
	 *
	 */
	const DECODE_ATTRIBUTES = 'decode-attributes';
// 	const TINYMCE_USED = 'tinymce-used';
	/**
	 *
	 */
	const ATTR_SEP = 'attr-sep';
	/**
	 *
	 */
	const EXCERPT_STRIP = 'excerpt-strip';
	/**
	 *
	 */
	const RANGES = 'ranges';
	/**
	 *
	 */
	const TAG_EDITOR_FRONT = 'tag-editor-front';
	/**
	 *
	 */
	const TAG_EDITOR_SETTINGS = 'tag-editor-front-hide';
	/**
	 *
	 */
	const TAG_EDITOR_ADD_BUTTON_TEXT = 'tag-editor-button-add-text';
	/**
	 *
	 */
	const TAG_EDITOR_EDIT_BUTTON_TEXT = 'tag-editor-button-edit-text';
	/**
	 *
	 */
	const TAG_EDITOR_QUICKTAG_BUTTON_TEXT = 'tag-editor-quicktag-button-text';
	/**
	 *
	 */
	const WRAP_TOGGLE = 'wrap-toggle';
	/**
	 *
	 */
	const WRAP = 'wrap';
	/**
	 *
	 */
	const EXPAND = 'expand';
	/**
	 *
	 */
	const EXPAND_TOGGLE = 'expand-toggle';
	/**
	 *
	 */
	const MINIMIZE = 'minimize';
	/**
	 *
	 */
	const IGNORE = 'ignore';
	/**
	 *
	 */
	const DELAY_LOAD_JS = 'delay-load-js';

	/**
	 * @var
	 */
	private static $cache_array;

	/**
	 * @param $cache
	 *
	 * @return array|mixed
	 */
	public static function get_cache_sec( $cache ) {
		$values = array_values( self::$cache_array );
		if ( array_key_exists( $cache, $values ) ) {
			return $values[ $cache ];
		} else {
			return $values[0];
		}
	}

	// The current settings, should be loaded with default if none exists

	/**
	 * @var array
	 */
	private $settings = array();

	// The settings with default values
	/**
	 * @var null
	 */
	private static $default = null;

	/**
	 * Urvanov_Syntax_Highlighter_Settings constructor.
	 */
	function __construct() {
		$this->init();
	}

	/**
	 * @return Urvanov_Syntax_Highlighter_Settings
	 */
	function copy() {
		$settings = new Urvanov_Syntax_Highlighter_Settings();
		foreach ( $this->settings as $setting ) {
			$settings->set( $setting ); // Overuse of set?
		}

		return $settings;
	}

	// Methods ================================================================

	/**
	 *
	 */
	private function init() {
		global $urvanov_syntax_highlighter_version;

		Urvanov_Syntax_Highlighter_Global::load_plugin_textdomain();

		self::$cache_array = array(
			esc_html__( 'Hourly', 'urvanov-syntax-highlighter' )      => 3600,
			esc_html__( 'Daily', 'urvanov-syntax-highlighter' )       => 86400,
			esc_html__( 'Weekly', 'urvanov-syntax-highlighter' )      => 604800,
			esc_html__( 'Monthly', 'urvanov-syntax-highlighter' )     => 18144000,
			esc_html__( 'Immediately', 'urvanov-syntax-highlighter' ) => 1,
		);

		$settings = array(
			new Urvanov_Syntax_Highlighter_Setting( self::VERSION, $urvanov_syntax_highlighter_version, null, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::THEME, Urvanov_Syntax_Highlighter_Themes::DEFAULT_THEME ),
			new Urvanov_Syntax_Highlighter_Setting( self::FONT, Urvanov_Syntax_Highlighter_Fonts::DEFAULT_FONT ),
			new Urvanov_Syntax_Highlighter_Setting( self::FONT_SIZE_ENABLE, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::FONT_SIZE, 12 ),
			new Urvanov_Syntax_Highlighter_Setting( self::LINE_HEIGHT, 15 ),
			new Urvanov_Syntax_Highlighter_Setting( self::PREVIEW, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::HEIGHT_SET, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::HEIGHT_MODE, array(
				esc_html__( 'Max', 'urvanov-syntax-highlighter' ),
				esc_html__( 'Min', 'urvanov-syntax-highlighter' ),
				esc_html__( 'Static', 'urvanov-syntax-highlighter' ),
			) ),
			new Urvanov_Syntax_Highlighter_Setting( self::HEIGHT, '500' ),
			new Urvanov_Syntax_Highlighter_Setting( self::HEIGHT_UNIT, array(
				esc_html__( 'Pixels', 'urvanov-syntax-highlighter' ),
				esc_html__( 'Percent', 'urvanov-syntax-highlighter' ),
			) ),
			new Urvanov_Syntax_Highlighter_Setting( self::WIDTH_SET, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::WIDTH_MODE, array(
				esc_html__( 'Max', 'urvanov-syntax-highlighter' ),
				esc_html__( 'Min', 'urvanov-syntax-highlighter' ),
				esc_html__( 'Static', 'urvanov-syntax-highlighter' ),
			) ),
			new Urvanov_Syntax_Highlighter_Setting( self::WIDTH, '500' ),
			new Urvanov_Syntax_Highlighter_Setting( self::WIDTH_UNIT, array(
				esc_html__( 'Pixels', 'urvanov-syntax-highlighter' ),
				esc_html__( 'Percent', 'urvanov-syntax-highlighter' ),
			) ),
			new Urvanov_Syntax_Highlighter_Setting( self::TOP_SET, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::TOP_MARGIN, 12 ),
			new Urvanov_Syntax_Highlighter_Setting( self::BOTTOM_SET, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::BOTTOM_MARGIN, 12 ),
			new Urvanov_Syntax_Highlighter_Setting( self::LEFT_SET, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::LEFT_MARGIN, 12 ),
			new Urvanov_Syntax_Highlighter_Setting( self::RIGHT_SET, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::RIGHT_MARGIN, 12 ),
			new Urvanov_Syntax_Highlighter_Setting( self::H_ALIGN, array(
				esc_html__( 'None', 'urvanov-syntax-highlighter' ),
				esc_html__( 'Left', 'urvanov-syntax-highlighter' ),
				esc_html__( 'Center', 'urvanov-syntax-highlighter' ),
				esc_html__( 'Right', 'urvanov-syntax-highlighter' ),
			) ),
			new Urvanov_Syntax_Highlighter_Setting( self::FLOAT_ENABLE, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::TOOLBAR, array(
				esc_html__( 'On MouseOver', 'urvanov-syntax-highlighter' ),
				esc_html__( 'Always', 'urvanov-syntax-highlighter' ),
				esc_html__( 'Never', 'urvanov-syntax-highlighter' ),
			) ),
			new Urvanov_Syntax_Highlighter_Setting( self::TOOLBAR_OVERLAY, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::TOOLBAR_HIDE, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::TOOLBAR_DELAY, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::COPY, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::POPUP, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::SHOW_LANG, array(
				esc_html__( 'When Found', 'urvanov-syntax-highlighter' ),
				esc_html__( 'Always', 'urvanov-syntax-highlighter' ),
				esc_html__( 'Never', 'urvanov-syntax-highlighter' ),
			) ),
			new Urvanov_Syntax_Highlighter_Setting( self::SHOW_TITLE, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::STRIPED, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::MARKING, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::START_LINE, 1 ),
			new Urvanov_Syntax_Highlighter_Setting( self::NUMS, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::NUMS_TOGGLE, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::TRIM_WHITESPACE, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::WHITESPACE_BEFORE, 0 ),
			new Urvanov_Syntax_Highlighter_Setting( self::WHITESPACE_AFTER, 0 ),
			new Urvanov_Syntax_Highlighter_Setting( self::TRIM_CODE_TAG, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::TAB_CONVERT, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::TAB_SIZE, 4 ),
			new Urvanov_Syntax_Highlighter_Setting( self::FALLBACK_LANG, Urvanov_Syntax_Highlighter_Langs::DEFAULT_LANG ),
			new Urvanov_Syntax_Highlighter_Setting( self::LOCAL_PATH, '' ),
			new Urvanov_Syntax_Highlighter_Setting( self::SCROLL, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::PLAIN, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::PLAIN_TOGGLE, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::SHOW_PLAIN_DEFAULT, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::SHOW_PLAIN,
				array(
					esc_html__( 'On Double Click', 'urvanov-syntax-highlighter' ),
					esc_html__( 'On Single Click', 'urvanov-syntax-highlighter' ),
					esc_html__( 'On MouseOver', 'urvanov-syntax-highlighter' ),
					esc_html__( 'Disable Mouse Events', 'urvanov-syntax-highlighter' ),
				) ),
			new Urvanov_Syntax_Highlighter_Setting( self::DISABLE_ANIM, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::TOUCHSCREEN, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::DISABLE_RUNTIME, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::DISABLE_DATE, '' ),
			new Urvanov_Syntax_Highlighter_Setting( self::ERROR_LOG, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::ERROR_LOG_SYS, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::ERROR_MSG_SHOW, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::ERROR_MSG, esc_html__( 'An error has occurred. Please try again later.', 'urvanov-syntax-highlighter' ) ),
			new Urvanov_Syntax_Highlighter_Setting( self::HIDE_HELP, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::CACHE, array_keys( self::$cache_array ), 1 ),
			new Urvanov_Syntax_Highlighter_Setting( self::EFFICIENT_ENQUEUE, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::CAPTURE_PRE, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::CAPTURE_MINI_TAG, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::ALTERNATE, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::SHOW_ALTERNATE, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::PLAIN_TAG, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::ENQUEUE_THEMES, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::ENQUEUE_FONTS, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::MAIN_QUERY, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::SAFE_ENQUEUE, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::INLINE_TAG, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::INLINE_TAG_CAPTURE, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::CODE_TAG_CAPTURE, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::CODE_TAG_CAPTURE_TYPE, array(
				esc_html__( 'Inline Tag', 'urvanov-syntax-highlighter' ),
				esc_html__( 'Block Tag', 'urvanov-syntax-highlighter' ),
			) ),
			new Urvanov_Syntax_Highlighter_Setting( self::INLINE_MARGIN, 5 ),
			new Urvanov_Syntax_Highlighter_Setting( self::INLINE_WRAP, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::BACKQUOTE, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::COMMENTS, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::DECODE, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::DECODE_ATTRIBUTES, true ),
			// 			new Urvanov_Syntax_Highlighter_Setting(self::TINYMCE_USED, FALSE),
			new Urvanov_Syntax_Highlighter_Setting( self::ATTR_SEP, array( ':', '_' ) ),
			new Urvanov_Syntax_Highlighter_Setting( self::EXCERPT_STRIP, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::RANGES, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::TAG_EDITOR_FRONT, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::TAG_EDITOR_SETTINGS, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::TAG_EDITOR_ADD_BUTTON_TEXT, esc_html__( 'Add Code', 'urvanov-syntax-highlighter' ) ),
			new Urvanov_Syntax_Highlighter_Setting( self::TAG_EDITOR_EDIT_BUTTON_TEXT, esc_html__( 'Edit Code', 'urvanov-syntax-highlighter' ) ),
			new Urvanov_Syntax_Highlighter_Setting( self::TAG_EDITOR_QUICKTAG_BUTTON_TEXT, 'crayon' ),
			new Urvanov_Syntax_Highlighter_Setting( self::WRAP_TOGGLE, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::WRAP, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::EXPAND, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::EXPAND_TOGGLE, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::MINIMIZE, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::DELAY_LOAD_JS, false ),
		);

		$this->set( $settings );

		$nonNegs        = array(
			self::FONT_SIZE,
			self::LINE_HEIGHT,
			self::HEIGHT,
			self::WIDTH,
			self::START_LINE,
			self::WHITESPACE_BEFORE,
			self::WHITESPACE_AFTER,
			self::TAB_SIZE,
			self::INLINE_MARGIN,
		);
		$intNonNegValid = new Urvanov_Syntax_Highlighter_Non_Neg_Int_Validator();
		foreach ( $nonNegs as $name ) {
			$this->get( $name )->validator( $intNonNegValid );
		}
	}

	// Getter and Setter ======================================================

	// TODO this needs simplification
	/**
	 * @param       $name
	 * @param null  $value
	 * @param false $replace
	 */
	function set( $name, $value = null, $replace = false ) {
		// Set associative array of settings
		if ( is_array( $name ) ) {
			$keys = array_keys( $name );
			foreach ( $keys as $key ) {
				if ( is_string( $key ) ) {
					// Associative value
					$this->set( $key, $name[ $key ], $replace );
				} elseif ( is_int( $key ) ) {
					$setting = $name[ $key ];
					$this->set( $setting, null, $replace );
				}
			}
		} elseif ( is_string( $name ) && ! empty( $name ) && $value !== null ) {
			$value = Urvanov_Syntax_Highlighter_Settings::validate( $name, $value );
			if ( $replace || ! $this->is_setting( $name ) ) {
				// Replace/Create
				$this->settings[ $name ] = new Urvanov_Syntax_Highlighter_Setting( $name, $value );
			} else {
				// Update
				$this->settings[ $name ]->value( $value );
			}
		} elseif ( is_object( $name ) && get_class( $name ) == URVANOV_SYNTAX_HIGHLIGHTER_SETTING_CLASS ) {
			$setting = $name; // Semantics
			if ( $replace || ! $this->is_setting( $setting->name() ) ) {
				// Replace/Create
				$this->settings[ $setting->name() ] = $setting->copy();
			} else {
				// Update
				if ( $setting->is_array() ) {
					$this->settings[ $setting->name() ]->index( $setting->index() );
				} else {
					$this->settings[ $setting->name() ]->value( $setting->value() );
				}
			}
		}
	}

	/**
	 * @param null $name
	 *
	 * @return array|false|mixed
	 */
	function get( $name = null ) {
		if ( $name === null ) {
			$copy = array();
			foreach ( $this->settings as $name => $setting ) {
				$copy[ $name ] = $setting->copy(); // Deep copy
			}

			return $copy;
		} elseif ( is_string( $name ) ) {
			if ( $this->is_setting( $name ) ) {
				return $this->settings[ $name ];
			}
		}

		return false;
	}

	/**
	 * @param null $name
	 *
	 * @return null
	 */
	function val( $name = null ) {
		if ( ( $setting = self::get( $name ) ) != false ) {
			return $setting->value();
		} else {
			return null;
		}
	}

	/**
	 * @param $name
	 *
	 * @return mixed|null
	 */
	function val_str( $name ) {
		if ( ( $setting = self::get( $name ) ) != false ) {
			$def   = $setting->def();
			$index = $setting->value();
			if ( array_key_exists( $index, $def ) ) {
				return $def[ $index ];
			} else {
				return null;
			}
		}
	}

	/**
	 * @return array
	 */
	function get_array() {
		$array = array();
		foreach ( $this->settings as $setting ) {
			$array[ $setting->name() ] = $setting->value();
		}

		return $array;
	}

	/**
	 * @param $name
	 *
	 * @return bool
	 */
	function is_setting( $name ) {
		return ( is_string( $name ) && array_key_exists( $name, $this->settings ) );
	}

	/* Gets default settings, either as associative array of name=>value or Urvanov_Syntax_Highlighter_Setting
	 objects */
	/**
	 * @param null $name
	 * @param bool $objects
	 *
	 * @return array|false|mixed
	 */
	public static function get_defaults( $name = null, $objects = true ) {
		if ( self::$default === null ) {
			self::$default = new Urvanov_Syntax_Highlighter_Settings();
		}
		if ( $name === null ) {
			// Get all settings
			if ( $objects ) {
				// Return array of objects
				return self::$default->get();
			} else {
				// Return associative array of name=>value
				$settings = self::$default->get();
				$defaults = array();
				foreach ( $settings as $setting ) {
					$defaults[ $setting->name() ] = $setting->value();
				}

				return $defaults;
			}
		} else {
			// Return specific setting
			if ( $objects ) {
				return self::$default->get( $name );
			} else {
				return self::$default->get( $name )->value();
			}
		}
	}

	/**
	 * @return array|false|mixed
	 */
	public static function get_defaults_array() {
		return self::get_defaults( null, false );
	}

	// Validation =============================================================

	/**
	 * Validates settings coming from an HTML form and also for internal use.
	 * This is used when saving form an HTML form to the db, and also when reading from the db
	 * back into the global settings.
	 *
	 * @param string    $name
	 * @param alternate $value
	 */
	public static function validate( $name, $value ) {
		if ( ! is_string( $name ) ) {
			return '';
		}

		// Type-cast to correct value for known settings
		if ( ( $setting = Urvanov_Syntax_Highlighter_Global_Settings::get( $name ) ) != false ) {
			// Booleans settings that are sent as string are allowed to have "false" == false
			if ( is_bool( $setting->def() ) ) {
				if ( is_string( $value ) ) {
					$value = UrvanovSyntaxHighlighterUtil::str_to_bool( $value );
				}
			} else {
				// Ensure we don't cast integer settings to 0 because $value doesn't have any numbers in it
				$value = strval( $value );
				// Only occurs when saving from the form ($_POST values are strings)
				if ( $value == '' || ( $cleaned = $setting->sanitize( $value, false ) ) == '' ) {
					// The value sent has no integers, change to default
					$value = $setting->def();
				} else {
					// Cleaned value is int
					$value = $cleaned;
				}
				// Cast all other settings as usual
				if ( ! settype( $value, $setting->type() ) ) {
					// If we can't cast, then use default value
					if ( $setting->is_array() ) {
						$value = 0; // default index
					} else {
						$value = $setting->def();
					}
				}
			}
		} else {
			// If setting not found, remove value
			return '';
		}

		switch ( $name ) {
			case Urvanov_Syntax_Highlighter_Settings::LOCAL_PATH:
				$path = parse_url( $value, PHP_URL_PATH );
				// Remove all spaces, prefixed and trailing forward slashes
				$path = preg_replace( '#^/*|/*$|\s*#', '', $path );
				// Replace backslashes
				$path = preg_replace( '#\\\\#', '/', $path );
				// Append trailing forward slash
				if ( ! empty( $path ) ) {
					$path .= '/';
				}

				return $path;
			case Urvanov_Syntax_Highlighter_Settings::FONT_SIZE:
				if ( $value < 1 ) {
					$value = 1;
				}
				break;
			case Urvanov_Syntax_Highlighter_Settings::LINE_HEIGHT:
				$font_size = Urvanov_Syntax_Highlighter_Global_Settings::val( Urvanov_Syntax_Highlighter_Settings::FONT_SIZE );
				$value     = $value >= $font_size ? $value : $font_size;
				break;
			case Urvanov_Syntax_Highlighter_Settings::THEME:
				$value = strtolower( $value );
			// XXX validate settings here
		}

		// If no validation occurs, return value
		return $value;
	}

	// Takes an associative array of "smart settings" and regular settings. Smart settings can be used
	// to configure regular settings quickly.
	// E.g. 'max_height="20px"' will set 'height="20"', 'height_mode="0", height_unit="0"'
	/**
	 * @param $settings
	 *
	 * @return array|false
	 */
	public static function smart_settings( $settings ) {
		if ( ! is_array( $settings ) ) {
			return false;
		}

		// If a setting is given, it is automatically enabled
		foreach ( $settings as $name => $value ) {
			if ( ( $setting = Urvanov_Syntax_Highlighter_Global_Settings::get( $name ) ) !== false && is_bool( $setting->def() ) ) {
				$value = UrvanovSyntaxHighlighterUtil::str_to_bool( $value );
			}

			// XXX removed height and width, since it wasn't using the global settings for mode if only height was provided
			if ( $name == 'min-height' || $name == 'max-height' /* || $name == 'height'*/ ) {
				self::smart_hw( $name, Urvanov_Syntax_Highlighter_Settings::HEIGHT_SET, Urvanov_Syntax_Highlighter_Settings::HEIGHT_MODE, Urvanov_Syntax_Highlighter_Settings::HEIGHT_UNIT, $settings );
			} elseif ( $name == 'min-width' || $name == 'max-width' /* || $name == 'width'*/ ) {
				self::smart_hw( $name, Urvanov_Syntax_Highlighter_Settings::WIDTH_SET, Urvanov_Syntax_Highlighter_Settings::WIDTH_MODE, Urvanov_Syntax_Highlighter_Settings::WIDTH_UNIT, $settings );
			} elseif ( $name == Urvanov_Syntax_Highlighter_Settings::FONT_SIZE ) {
				$settings[ Urvanov_Syntax_Highlighter_Settings::FONT_SIZE_ENABLE ] = true;
			} elseif ( $name == Urvanov_Syntax_Highlighter_Settings::TOP_MARGIN ) {
				$settings[ Urvanov_Syntax_Highlighter_Settings::TOP_SET ] = true;
			} elseif ( $name == Urvanov_Syntax_Highlighter_Settings::LEFT_MARGIN ) {
				$settings[ Urvanov_Syntax_Highlighter_Settings::LEFT_SET ] = true;
			} elseif ( $name == Urvanov_Syntax_Highlighter_Settings::BOTTOM_MARGIN ) {
				$settings[ Urvanov_Syntax_Highlighter_Settings::BOTTOM_SET ] = true;
			} elseif ( $name == Urvanov_Syntax_Highlighter_Settings::RIGHT_MARGIN ) {
				$settings[ Urvanov_Syntax_Highlighter_Settings::RIGHT_SET ] = true;
			} elseif ( $name == Urvanov_Syntax_Highlighter_Settings::ERROR_MSG ) {
				$settings[ Urvanov_Syntax_Highlighter_Settings::ERROR_MSG_SHOW ] = true;
			} elseif ( $name == Urvanov_Syntax_Highlighter_Settings::H_ALIGN ) {
				$settings[ Urvanov_Syntax_Highlighter_Settings::FLOAT_ENABLE ] = true;
				$value                                                         = UrvanovSyntaxHighlighterUtil::tlower( $value );
				$values                                                        = array( 'none' => 0, 'left' => 1, 'center' => 2, 'right' => 3 );
				if ( array_key_exists( $value, $values ) ) {
					$settings[ Urvanov_Syntax_Highlighter_Settings::H_ALIGN ] = $values[ $value ];
				}
			} elseif ( $name == Urvanov_Syntax_Highlighter_Settings::SHOW_LANG ) {
				$value  = UrvanovSyntaxHighlighterUtil::tlower( $value );
				$values = array( 'found' => 0, 'always' => 1, 'true' => 1, 'never' => 2, 'false' => 2 );
				if ( array_key_exists( $value, $values ) ) {
					$settings[ Urvanov_Syntax_Highlighter_Settings::SHOW_LANG ] = $values[ $value ];
				}
			} elseif ( $name == Urvanov_Syntax_Highlighter_Settings::TOOLBAR ) {
				if ( UrvanovSyntaxHighlighterUtil::tlower( $value ) == 'always' ) {
					$settings[ Urvanov_Syntax_Highlighter_Settings::TOOLBAR ] = 1;
				} elseif ( UrvanovSyntaxHighlighterUtil::str_to_bool( $value ) === false ) {
					$settings[ Urvanov_Syntax_Highlighter_Settings::TOOLBAR ] = 2;
				}
			}
		}

		return $settings;
	}

	// Used for height and width smart settings, I couldn't bear to copy paste code twice...

	/**
	 * @param $name
	 * @param $set
	 * @param $mode
	 * @param $unit
	 * @param $settings
	 */
	private static function smart_hw( $name, $set, $mode, $unit, &$settings ) {
		if ( ! is_string( $name ) || ! is_string( $set ) || ! is_string( $mode ) || ! is_string( $unit ) || ! is_array( $settings ) ) {
			return;
		}
		$settings[ $set ] = true;
		if ( strpos( $name, 'max-' ) !== false ) {
			$settings[ $mode ] = 0;
		} elseif ( strpos( $name, 'min-' ) !== false ) {
			$settings[ $mode ] = 1;
		} else {
			$settings[ $mode ] = 2;
		}
		preg_match( '#(\d+)\s*([^\s]*)#', $settings[ $name ], $match );
		if ( count( $match ) == 3 ) {
			$name              = str_replace( array( 'max-', 'min-' ), '', $name );
			$settings[ $name ] = $match[1];
			switch ( strtolower( $match[2] ) ) {
				case 'px':
					$settings[ $unit ] = 0;
					break;
				case '%':
					$settings[ $unit ] = 1;
					break;
			}
		}
	}
}

/**
 * Stores global/static copy of Urvanov_Syntax_Highlighter_Settings loaded from db.
 * These settings can be overriden by individual Crayons.
 * Also manages global site settings and paths.
 */
// Old name: CrayonGlobalSettings

/**
 * Class Urvanov_Syntax_Highlighter_Global_Settings
 */
class Urvanov_Syntax_Highlighter_Global_Settings {
	// The global settings stored as a Urvanov_Syntax_Highlighter_Settings object.
	/**
	 * @var null
	 */
	private static $global = null;
	/* These are used to load local files reliably and prevent scripts like PHP from executing
	 when attempting to load their code. */
	// The URL of the site (eg. http://localhost/example/)
	/**
	 * @var string
	 */
	private static $site_http = '';
	// The absolute root directory of the site (eg. /User/example/)
	/**
	 * @var string
	 */
	private static $site_path = '';
	// The absolute root directory of the plugins (eg. /User/example/plugins)
	/**
	 * @var string
	 */
	private static $plugin_path = '';
	/**
	 * @var string
	 */
	private static $upload_path = '';
	/**
	 * @var string
	 */
	private static $upload_url = '';
	/**
	 * @var null
	 */
	private static $mkdir = null;

	/**
	 * Urvanov_Syntax_Highlighter_Global_Settings constructor.
	 */
	private function __construct() {
	}

	/**
	 *
	 */
	private static function init() {
		if ( self::$global === null ) {
			self::$global = new Urvanov_Syntax_Highlighter_Settings();
		}
	}

	/**
	 * @param null $name
	 *
	 * @return mixed
	 */
	public static function get( $name = null ) {
		self::init();

		return self::$global->get( $name );
	}

	/**
	 * @return mixed
	 */
	public static function get_array() {
		self::init();

		return self::$global->get_array();
	}

	/**
	 * @return mixed
	 */
	public static function get_obj() {
		self::init();

		return self::$global->copy();
	}

	/**
	 * @param null $name
	 *
	 * @return mixed
	 */
	public static function val( $name = null ) {
		self::init();

		return self::$global->val( $name );
	}

	/**
	 * @param null $name
	 *
	 * @return mixed
	 */
	public static function val_str( $name = null ) {
		self::init();

		return self::$global->val_str( $name );
	}

	/**
	 * @param $input
	 * @param $setting
	 * @param $value
	 *
	 * @return bool
	 */
	public static function has_changed( $input, $setting, $value ) {
		return $input == $setting && $value != Urvanov_Syntax_Highlighter_Global_Settings::val( $setting );
	}

	/**
	 * @param       $name
	 * @param null  $value
	 * @param false $replace
	 */
	public static function set( $name, $value = null, $replace = false ) {
		self::init();
		self::$global->set( $name, $value, $replace );
	}

	/**
	 * @param null $site_http
	 *
	 * @return string
	 */
	public static function site_url( $site_http = null ) {
		if ( $site_http === null ) {
			return self::$site_http;
		} else {
			self::$site_http = UrvanovSyntaxHighlighterUtil::url_slash( $site_http );
		}
	}

	/**
	 * @param null $site_path
	 *
	 * @return string
	 */
	public static function site_path( $site_path = null ) {
		if ( $site_path === null ) {
			return self::$site_path;
		} else {
			self::$site_path = UrvanovSyntaxHighlighterUtil::path_slash( $site_path );
		}
	}

	/**
	 * @param null $plugin_path
	 *
	 * @return string
	 */
	public static function plugin_path( $plugin_path = null ) {
		if ( $plugin_path === null ) {
			return self::$plugin_path;
		} else {
			self::$plugin_path = UrvanovSyntaxHighlighterUtil::path_slash( $plugin_path );
		}
	}

	/**
	 * @param null $upload_path
	 *
	 * @return string
	 */
	public static function upload_path( $upload_path = null ) {
		if ( $upload_path === null ) {
			return self::$upload_path;
		} else {
			self::$upload_path = UrvanovSyntaxHighlighterUtil::path_slash( $upload_path );
		}
	}

	/**
	 * @param null $upload_url
	 *
	 * @return string
	 */
	public static function upload_url( $upload_url = null ) {
		if ( $upload_url === null ) {
			return self::$upload_url;
		} else {
			self::$upload_url = UrvanovSyntaxHighlighterUtil::url_slash( $upload_url );
		}
	}

	/**
	 * @param null $mkdir
	 *
	 * @return null
	 */
	public static function set_mkdir( $mkdir = null ) {
		if ( $mkdir === null ) {
			return self::$mkdir;
		} else {
			self::$mkdir = $mkdir;
		}
	}

	/**
	 * @param null $dir
	 */
	public static function mkdir( $dir = null ) {
		if ( self::$mkdir ) {
			call_user_func( self::$mkdir, $dir );
		} else {
			@mkdir( $dir, 0777, true );
		}
	}
}


$INT = new Urvanov_Syntax_Highlighter_Validator( '#\d+#' );

/**
 * Validation class.
 */
// Old name: CrayonValidator

/**
 * Class Urvanov_Syntax_Highlighter_Validator
 */
class Urvanov_Syntax_Highlighter_Validator {
	/**
	 * @var string
	 */
	private $pattern = '#*#msi';

	/**
	 * Urvanov_Syntax_Highlighter_Validator constructor.
	 *
	 * @param $pattern
	 */
	public function __construct( $pattern ) {
		$this->pattern( $pattern );
	}

	/**
	 * @param $pattern
	 *
	 * @return null
	 */
	public function pattern( $pattern ) {
		if ( $pattern === null ) {
			return $pattern;
		} else {
			$this->pattern = $pattern;
		}
	}

	/**
	 * @param $str
	 *
	 * @return bool
	 */
	public function validate( $str ) {
		return preg_match( $this->pattern, $str ) !== false;
	}

	/**
	 * @param $str
	 *
	 * @return string
	 */
	public function sanitize( $str ) {
		preg_match_all( $this->pattern, $str, $matches );
		$result = '';
		foreach ( $matches as $match ) {
			$result .= $match[0];
		}

		return $result;
	}
}

// Old name: CrayonNonNegIntValidator

/**
 * Class Urvanov_Syntax_Highlighter_Non_Neg_Int_Validator
 */
class Urvanov_Syntax_Highlighter_Non_Neg_Int_Validator extends Urvanov_Syntax_Highlighter_Validator {
	/**
	 * Urvanov_Syntax_Highlighter_Non_Neg_Int_Validator constructor.
	 */
	public function __construct() {
		parent::__construct( '#\d+#' );
	}
}

// Old name: CrayonIntValidator

/**
 * Class Urvanov_Syntax_Highligher_Int_Validator
 */
class Urvanov_Syntax_Highligher_Int_Validator extends Urvanov_Syntax_Highlighter_Validator {
	/**
	 * Urvanov_Syntax_Highligher_Int_Validator constructor.
	 */
	public function __construct() {
		parent::__construct( '#-?\d+#' );
	}
}

/**
 * Individual setting.
 * Can store boolean, string, dropdown (with array of strings), etc.
 */
// Old name: CrayonSetting

/**
 * Class Urvanov_Syntax_Highlighter_Setting
 */
class Urvanov_Syntax_Highlighter_Setting {
	/**
	 * @var string
	 */
	private $name = '';
	/* The type of variables that can be set as the value.
	 * For dropdown settings, value is int, even though value() will return a string. */
	/**
	 * @var null
	 */
	private $type = null;
	/**
	 * @var null
	 */
	private $default = null; // stores string array for dropdown settings

	/**
	 * @var null
	 */
	private $value = null; // stores index int for dropdown settings

	/**
	 * @var bool
	 */
	private $is_array = false; // only TRUE for dropdown settings
	/**
	 * @var bool
	 */
	private $locked = false;

	/**
	 * @var null
	 */
	private $validator = null;


	/**
	 * Urvanov_Syntax_Highlighter_Setting constructor.
	 *
	 * @param        $name
	 * @param string $default
	 * @param null   $value
	 * @param null   $locked
	 */
	public function __construct( $name, $default = '', $value = null, $locked = null ) {
		$this->name( $name );
		if ( $default !== null ) {
			$this->def( $default ); // Perform first to set type
		}
		if ( $value !== null ) {
			$this->value( $value );
		}
		if ( $locked !== null ) {
			$this->locked( $locked );
		}
	}

	/**
	 * @return string
	 */
	function __tostring() {
		return $this->name;
	}

	/**
	 * @return Urvanov_Syntax_Highlighter_Setting
	 */
	function copy() {
		return new Urvanov_Syntax_Highlighter_Setting( $this->name, $this->default, $this->value, $this->locked );
	}

	/**
	 * @param null $name
	 *
	 * @return string
	 */
	function name( $name = null ) {
		if ( ! UrvanovSyntaxHighlighterUtil::str( $this->name, $name ) ) {
			return $this->name;
		}
	}

	/**
	 * @return null
	 */
	function type() {
		return $this->type;
	}

	/**
	 * @return bool
	 */
	function is_array() {
		return $this->is_array;
	}

	/**
	 * @param null $locked
	 *
	 * @return bool
	 */
	function locked( $locked = null ) {
		if ( $locked === null ) {
			return $this->locked;
		} else {
			$this->locked = ( $locked == true );
		}
	}

	/**
	 * Sets/gets value;
	 * Value is index (int) in default value (array) for dropdown settings.
	 * value($value) is alias for index($index) if dropdown setting.
	 * value() returns string value at current index for dropdown settings.
	 *
	 * @param $value
	 */
	function value( $value = null ) {
		if ( $value === null ) {
			/*if ($this->is_array) {
				return $this->default[$this->value]; // value at index
			} else */
			if ( $this->value !== null ) {
				return $this->value;
			} else {
				if ( $this->is_array ) {
					return 0;
				} else {
					return $this->default;
				}
			}
		} elseif ( $this->locked === false ) {
			if ( $this->is_array ) {
				$this->index( $value ); // $value is index
			} else {
				settype( $value, $this->type ); // Type cast
				$this->value = $value;
			}
		}
	}

	/**
	 * @return mixed|null
	 */
	function array_value() {
		if ( $this->is_array ) {
			return null;
		}

		return $this->default[ $this->value ];
	}

	/**
	 * Sets/gets default value.
	 * For dropdown settings, default value is array of all possible value strings.
	 *
	 * @param $default
	 */
	function def( $default = null ) {
		// Only allow default to be set once

		if ( $this->type === null && $default !== null ) {
			// For dropdown settings

			if ( is_array( $default ) ) { // The only time we don't use $this->is_array

				// If empty, set to blank array

				if ( empty( $default ) ) {
					$default = array( '' );
				} else {
					// Ensure all values are unique strings

					$default = UrvanovSyntaxHighlighterUtil::array_unique_str( $default );
				}
				$this->value = 0; // initial index

				$this->is_array = true;
				$this->type     = gettype( 0 ); // Type is int (index)

			} else {
				$this->is_array = false;
				$this->type     = gettype( $default );
				if ( is_int( $default ) ) {
					$this->validator( new Urvanov_Syntax_Highligher_Int_Validator() );
				}
			}
			$this->default = $default;
		} else {
			return $this->default;
		}
	}

	/**
	 * Sets/gets index.
	 *
	 * @param int|string $index
	 *
	 * @return FALSE if not dropdown setting
	 */
	function index( $index = null ) {
		if ( ! $this->is_array ) {
			return false;
		} elseif ( $index === null ) {
			return $this->value; // return current index
		} else {
			if ( ! is_int( $index ) ) {
				// Ensure $value is int for index
				$index = intval( $index );
			}
			// Validate index
			if ( $index < 0 || $index > count( $this->default ) - 1 ) {
				$index = 0;
			}
			$this->value = $index;
		}
	}

	/**
	 * Finds the index of a string in an array setting
	 */
	function find_index( $str ) {
		if ( ! $this->is_array || is_string( $str ) ) {
			return false;
		}
		for ( $i = 0; $i < count( $this->default ); $i ++ ) {
			if ( $this->default[ $i ] == $str ) {
				return $i;
			}
		}

		return false;
	}

	/**
	 * @param $validator
	 *
	 * @return null
	 */
	function validator( $validator ) {
		if ( $validator === null ) {
			return $this->validator;
		} else {
			$this->validator = $validator;
		}
	}

	/**
	 * @param $str
	 *
	 * @return mixed
	 */
	function sanitize( $str ) {
		if ( $this->validator != null ) {
			return $this->validator->sanitize( $str );
		} else {
			return $str;
		}
	}

}

?>

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

/**
 * Class Urvanov_Syntax_Highlighter_Settings
 */
class Urvanov_Syntax_Highlighter_Settings {

	/**
	 * INvalid.
	 */
	const INVALID = - 1; // Used for invalid dropdown index.

	/**
	 * Plugin data.
	 */
	const VERSION = 'version';

	/**
	 * Added when used in HTML to avoid id conflicts.
	 */
	const PREFIX = 'urvanov-syntax-highlighter-';

	/**
	 * Settings class.
	 */
	const SETTING = 'urvanov-syntax-highlighter-setting';

	/**
	 * Selected class.
	 */
	const SETTING_SELECTED = 'urvanov-syntax-highlighter-setting-selected';

	/**
	 * Changed class.
	 */
	const SETTING_CHANGED = 'urvanov-syntax-highlighter-setting-changed';

	/**
	 * Special class.
	 */
	const SETTING_SPECIAL = 'urvanov-syntax-highlighter-setting-special';

	/**
	 * Orign value class.
	 */
	const SETTING_ORIG_VALUE = 'data-orig-value';

	/**
	 * Global names for settings.
	 */
	const THEME = 'theme';

	/**
	 * Font class.
	 */
	const FONT = 'font';

	/**
	 * Size enable class.
	 */
	const FONT_SIZE_ENABLE = 'font-size-enable';

	/**
	 * Font size class.
	 */
	const FONT_SIZE = 'font-size';

	/**
	 * Line height class.
	 */
	const LINE_HEIGHT = 'line-height';

	/**
	 * Preview class.
	 */
	const PREVIEW = 'preview';

	/**
	 * Height set class.
	 */
	const HEIGHT_SET = 'height-set';

	/**
	 * Height moce class.
	 */
	const HEIGHT_MODE = 'height-mode';

	/**
	 * Height class.
	 */
	const HEIGHT = 'height';

	/**
	 * Height unit.
	 */
	const HEIGHT_UNIT = 'height-unit';

	/**
	 * Width set class.
	 */
	const WIDTH_SET = 'width-set';

	/**
	 * Width mode class.
	 */
	const WIDTH_MODE = 'width-mode';

	/**
	 * Width class.
	 */
	const WIDTH = 'width';

	/**
	 * Width unit class.
	 */
	const WIDTH_UNIT = 'width-unit';

	/**
	 * Top set class.
	 */
	const TOP_SET = 'top-set';

	/**
	 * Top margin class.
	 */
	const TOP_MARGIN = 'top-margin';

	/**
	 * Left set class.
	 */
	const LEFT_SET = 'left-set';

	/**
	 * Left margin class.
	 */
	const LEFT_MARGIN = 'left-margin';

	/**
	 * Bottom set class.
	 */
	const BOTTOM_SET = 'bottom-set';

	/**
	 * Bottom margin class.
	 */
	const BOTTOM_MARGIN = 'bottom-margin';

	/**
	 * Right set class.
	 */
	const RIGHT_SET = 'right-set';

	/**
	 * Right margin class.
	 */
	const RIGHT_MARGIN = 'right-margin';

	/**
	 * H align class.
	 */
	const H_ALIGN = 'h-align';

	/**
	 * Float enble class.
	 */
	const FLOAT_ENABLE = 'float-enable';

	/**
	 * Toolbar class.
	 */
	const TOOLBAR = 'toolbar';

	/**
	 * Toolbar overlay.
	 */
	const TOOLBAR_OVERLAY = 'toolbar-overlay';

	/**
	 * Toobar hide.
	 */
	const TOOLBAR_HIDE = 'toolbar-hide';

	/**
	 * Toolbar delay class.
	 */
	const TOOLBAR_DELAY = 'toolbar-delay';

	/**
	 * Copy class.
	 */
	const COPY = 'copy';

	/**
	 * Paste class.
	 */
	const POPUP = 'popup';

	/**
	 * Show lang class.
	 */
	const SHOW_LANG = 'show-lang';

	/**
	 * Show title class.
	 */
	const SHOW_TITLE = 'show-title';

	/**
	 * Striped class.
	 */
	const STRIPED = 'striped';

	/**
	 * Marking class.
	 */
	const MARKING = 'marking';

	/**
	 * Start line class.
	 */
	const START_LINE = 'start-line';

	/**
	 * Nums class.
	 */
	const NUMS = 'nums';

	/**
	 * Nums toggle class.
	 */
	const NUMS_TOGGLE = 'nums-toggle';

	/**
	 * Trim whbitespace class.
	 */
	const TRIM_WHITESPACE = 'trim-whitespace';

	/**
	 * Whitespace before class.
	 */
	const WHITESPACE_BEFORE = 'whitespace-before';

	/**
	 * Whitespace after class.
	 */
	const WHITESPACE_AFTER = 'whitespace-after';

	/**
	 * Time code tag class.
	 */
	const TRIM_CODE_TAG = 'trim-code-tag';

	/**
	 * Tab size class.
	 */
	const TAB_SIZE = 'tab-size';

	/**
	 * Tab convert.
	 */
	const TAB_CONVERT = 'tab-convert';

	/**
	 * Fallback lang.
	 */
	const FALLBACK_LANG = 'fallback-lang';

	/**
	 * Local path.
	 */
	const LOCAL_PATH = 'local-path';

	/**
	 * Scroll class.
	 */
	const SCROLL = 'scroll';

	/**
	 * Plain class.
	 */
	const PLAIN = 'plain';

	/**
	 * Plain toggle.
	 */
	const PLAIN_TOGGLE = 'plain-toggle';

	/**
	 * SHow plain class.
	 */
	const SHOW_PLAIN = 'show-plain';

	/**
	 * Disable runtime class.
	 */
	const DISABLE_RUNTIME = 'runtime';

	/**
	 * Disable date class.
	 */
	const DISABLE_DATE = 'disable-date';

	/**
	 * Touchscreen class.
	 */
	const TOUCHSCREEN = 'touchscreen';

	/**
	 * Disable animation class.
	 */
	const DISABLE_ANIM = 'disable-anim';

	/**
	 * Error log class.
	 */
	const ERROR_LOG = 'error-log';

	/**
	 * Error sys log.
	 */
	const ERROR_LOG_SYS = 'error-log-sys';

	/**
	 * Error msg show class.
	 */
	const ERROR_MSG_SHOW = 'error-msg-show';

	/**
	 * Error message class.
	 */
	const ERROR_MSG = 'error-msg';

	/**
	 * Help hide class.
	 */
	const HIDE_HELP = 'hide-help';

	/**
	 * Cache class.
	 */
	const CACHE = 'cache';

	/**
	 * Efficent enqueue class.
	 */
	const EFFICIENT_ENQUEUE = 'efficient-enqueue';

	/**
	 * Capture pre class.
	 */
	const CAPTURE_PRE = 'capture-pre';

	/**
	 * Capture mini tag.
	 */
	const CAPTURE_MINI_TAG = 'capture-mini-tag';

	/**
	 * Alternate class.
	 */
	const ALTERNATE = 'alternate';

	/**
	 * Show alternate class.
	 */
	const SHOW_ALTERNATE = 'show_alternate';

	/**
	 * Plain tag class.
	 */
	const PLAIN_TAG = 'plain_tag';

	/**
	 * Show plain default class.
	 */
	const SHOW_PLAIN_DEFAULT = 'show-plain-default';

	/**
	 * Enqueue themes class.
	 */
	const ENQUEUE_THEMES = 'enqueque-themes';

	/**
	 * Enqueue fonts class.
	 */
	const ENQUEUE_FONTS = 'enqueque-fonts';

	/**
	 * Main query class.
	 */
	const MAIN_QUERY = 'main-query';

	/**
	 * Safe enqueue class.
	 */
	const SAFE_ENQUEUE = 'safe-enqueue';

	/**
	 * Inline tag class.
	 */
	const INLINE_TAG = 'inline-tag';

	/**
	 * Inline tag capture class.
	 */
	const INLINE_TAG_CAPTURE = 'inline-tag-capture';

	/**
	 * Code tag capture.
	 */
	const CODE_TAG_CAPTURE = 'code-tag-capture';

	/**
	 * Caode tag capture type.
	 */
	const CODE_TAG_CAPTURE_TYPE = 'code-tag-capture-type';

	/**
	 * Inline margin.
	 */
	const INLINE_MARGIN = 'inline-margin';

	/**
	 * Inline wrap.
	 */
	const INLINE_WRAP = 'inline-wrap';

	/**
	 * Backquote.
	 */
	const BACKQUOTE = 'backquote';

	/**
	 * Comments.
	 */
	const COMMENTS = 'comments';

	/**
	 * Decode.
	 */
	const DECODE = 'decode';

	/**
	 * Decode attributes.
	 */
	const DECODE_ATTRIBUTES = 'decode-attributes';

	/**
	 * Attr sep.
	 */
	const ATTR_SEP = 'attr-sep';

	/**
	 * Eexcerpt class.
	 */
	const EXCERPT_STRIP = 'excerpt-strip';

	/**
	 * Ranges.
	 */
	const RANGES = 'ranges';

	/**
	 * Tag editor front.
	 */
	const TAG_EDITOR_FRONT = 'tag-editor-front';

	/**
	 * Tag editor settings.
	 */
	const TAG_EDITOR_SETTINGS = 'tag-editor-front-hide';

	/**
	 * Add button text.
	 */
	const TAG_EDITOR_ADD_BUTTON_TEXT = 'tag-editor-button-add-text';

	/**
	 * Edit button text.
	 */
	const TAG_EDITOR_EDIT_BUTTON_TEXT = 'tag-editor-button-edit-text';

	/**
	 * Quicktag button text .
	 */
	const TAG_EDITOR_QUICKTAG_BUTTON_TEXT = 'tag-editor-quicktag-button-text';

	/**
	 * Wrap toggle.
	 */
	const WRAP_TOGGLE = 'wrap-toggle';

	/**
	 * Wrap.
	 */
	const WRAP = 'wrap';

	/**
	 * Expand.
	 */
	const EXPAND = 'expand';

	/**
	 * Expand toggle.
	 */
	const EXPAND_TOGGLE = 'expand-toggle';

	/**
	 * Minimize.
	 */
	const MINIMIZE = 'minimize';

	/**
	 * Ignore.
	 */
	const IGNORE = 'ignore';

	/**
	 * Delay JS load.
	 */
	const DELAY_LOAD_JS = 'delay-load-js';

	/**
	 * Cache array.
	 *
	 * @var array
	 */
	private static $cache_array = array();

	/**
	 * Get cache.
	 *
	 * @param string $cache Cache.
	 *
	 * @return array|mixed
	 */
	public static function get_cache_sec( string $cache ) {
		$values = array_values( self::$cache_array );
		if ( array_key_exists( $cache, $values ) ) {
			return $values[ $cache ];
		} else {
			return $values[0];
		}
	}

	/**
	 * The current settings, should be loaded with default if none exists.
	 *
	 * @var array
	 */
	private $settings = array();

	/**
	 * The settings with default values.
	 *
	 * @var null
	 */
	private static $default = null;

	/**
	 * Urvanov_Syntax_Highlighter_Settings constructor.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Copy.
	 *
	 * @return Urvanov_Syntax_Highlighter_Settings
	 */
	public function copy(): Urvanov_Syntax_Highlighter_Settings {
		$settings = new Urvanov_Syntax_Highlighter_Settings();

		foreach ( $this->settings as $setting ) {
			$settings->set( $setting ); // Overuse of set?
		}

		return $settings;
	}

	/**
	 * Init.
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
			new Urvanov_Syntax_Highlighter_Setting(
				self::HEIGHT_MODE,
				array(
					esc_html__( 'Max', 'urvanov-syntax-highlighter' ),
					esc_html__( 'Min', 'urvanov-syntax-highlighter' ),
					esc_html__( 'Static', 'urvanov-syntax-highlighter' ),
				)
			),
			new Urvanov_Syntax_Highlighter_Setting( self::HEIGHT, '500' ),
			new Urvanov_Syntax_Highlighter_Setting(
				self::HEIGHT_UNIT,
				array(
					esc_html__( 'Pixels', 'urvanov-syntax-highlighter' ),
					esc_html__( 'Percent', 'urvanov-syntax-highlighter' ),
				)
			),
			new Urvanov_Syntax_Highlighter_Setting( self::WIDTH_SET, false ),
			new Urvanov_Syntax_Highlighter_Setting(
				self::WIDTH_MODE,
				array(
					esc_html__( 'Max', 'urvanov-syntax-highlighter' ),
					esc_html__( 'Min', 'urvanov-syntax-highlighter' ),
					esc_html__( 'Static', 'urvanov-syntax-highlighter' ),
				)
			),
			new Urvanov_Syntax_Highlighter_Setting( self::WIDTH, '500' ),
			new Urvanov_Syntax_Highlighter_Setting(
				self::WIDTH_UNIT,
				array(
					esc_html__( 'Pixels', 'urvanov-syntax-highlighter' ),
					esc_html__( 'Percent', 'urvanov-syntax-highlighter' ),
				)
			),
			new Urvanov_Syntax_Highlighter_Setting( self::TOP_SET, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::TOP_MARGIN, 12 ),
			new Urvanov_Syntax_Highlighter_Setting( self::BOTTOM_SET, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::BOTTOM_MARGIN, 12 ),
			new Urvanov_Syntax_Highlighter_Setting( self::LEFT_SET, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::LEFT_MARGIN, 12 ),
			new Urvanov_Syntax_Highlighter_Setting( self::RIGHT_SET, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::RIGHT_MARGIN, 12 ),
			new Urvanov_Syntax_Highlighter_Setting(
				self::H_ALIGN,
				array(
					esc_html__( 'None', 'urvanov-syntax-highlighter' ),
					esc_html__( 'Left', 'urvanov-syntax-highlighter' ),
					esc_html__( 'Center', 'urvanov-syntax-highlighter' ),
					esc_html__( 'Right', 'urvanov-syntax-highlighter' ),
				)
			),
			new Urvanov_Syntax_Highlighter_Setting( self::FLOAT_ENABLE, false ),
			new Urvanov_Syntax_Highlighter_Setting(
				self::TOOLBAR,
				array(
					esc_html__( 'On MouseOver', 'urvanov-syntax-highlighter' ),
					esc_html__( 'Always', 'urvanov-syntax-highlighter' ),
					esc_html__( 'Never', 'urvanov-syntax-highlighter' ),
				)
			),
			new Urvanov_Syntax_Highlighter_Setting( self::TOOLBAR_OVERLAY, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::TOOLBAR_HIDE, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::TOOLBAR_DELAY, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::COPY, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::POPUP, true ),
			new Urvanov_Syntax_Highlighter_Setting(
				self::SHOW_LANG,
				array(
					esc_html__( 'When Found', 'urvanov-syntax-highlighter' ),
					esc_html__( 'Always', 'urvanov-syntax-highlighter' ),
					esc_html__( 'Never', 'urvanov-syntax-highlighter' ),
				)
			),
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
			new Urvanov_Syntax_Highlighter_Setting(
				self::SHOW_PLAIN,
				array(
					esc_html__( 'On Double Click', 'urvanov-syntax-highlighter' ),
					esc_html__( 'On Single Click', 'urvanov-syntax-highlighter' ),
					esc_html__( 'On MouseOver', 'urvanov-syntax-highlighter' ),
					esc_html__( 'Disable Mouse Events', 'urvanov-syntax-highlighter' ),
				)
			),
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
			new Urvanov_Syntax_Highlighter_Setting(
				self::CODE_TAG_CAPTURE_TYPE,
				array(
					esc_html__( 'Inline Tag', 'urvanov-syntax-highlighter' ),
					esc_html__( 'Block Tag', 'urvanov-syntax-highlighter' ),
				)
			),
			new Urvanov_Syntax_Highlighter_Setting( self::INLINE_MARGIN, 5 ),
			new Urvanov_Syntax_Highlighter_Setting( self::INLINE_WRAP, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::BACKQUOTE, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::COMMENTS, true ),
			new Urvanov_Syntax_Highlighter_Setting( self::DECODE, false ),
			new Urvanov_Syntax_Highlighter_Setting( self::DECODE_ATTRIBUTES, true ),
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

		$non_negs = array(
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

		$int_non_neg_valid = new Urvanov_Syntax_Highlighter_Non_Neg_Int_Validator();
		foreach ( $non_negs as $name ) {
			$this->get( $name )->validator( $int_non_neg_valid );
		}
	}

	/**
	 * TODO this needs simplification.
	 *
	 * Set.
	 *
	 * @param string $name Name.
	 * @param string $value Value.
	 * @param bool   $replace Replace.
	 */
	public function set( $name = '', $value = null, $replace = false ) {

		// Set associative array of settings.
		if ( is_array( $name ) ) {
			$keys = array_keys( $name );
			foreach ( $keys as $key ) {
				if ( is_string( $key ) ) {
					// Associative value.
					$this->set( $key, $name[ $key ], $replace );
				} elseif ( is_int( $key ) ) {
					$setting = $name[ $key ];
					$this->set( $setting, null, $replace );
				}
			}
		} elseif ( is_string( $name ) && ! empty( $name ) && null !== $value ) {
			$value = self::validate( $name, $value );
			if ( $replace || ! $this->is_setting( $name ) ) {
				// Replace/Create.
				$this->settings[ $name ] = new Urvanov_Syntax_Highlighter_Setting( $name, $value );
			} else {
				// Update.
				$this->settings[ $name ]->value( $value );
			}
		} elseif ( is_object( $name ) && get_class( $name ) === URVANOV_SYNTAX_HIGHLIGHTER_SETTING_CLASS ) {
			$setting = $name; // Semantics.
			if ( $replace || ! $this->is_setting( $setting->name() ) ) {
				// Replace/Create.
				$this->settings[ $setting->name() ] = $setting->copy();
			} else {
				// Update.
				if ( $setting->is_array() ) {
					$this->settings[ $setting->name() ]->index( $setting->index() );
				} else {
					$this->settings[ $setting->name() ]->value( $setting->value() );
				}
			}
		}
	}

	/**
	 * Get.
	 *
	 * @param string $name Name.
	 *
	 * @return array|false|mixed
	 */
	public function get( $name = null ) {
		if ( null === $name ) {
			$copy = array();
			foreach ( $this->settings as $name => $setting ) {
				$copy[ $name ] = $setting->copy(); // Deep copy.
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
	 * Val.
	 *
	 * @param string $name Name.
	 *
	 * @return null
	 */
	public function val( $name = null ) {
		$setting = self::get( $name );
		if ( false !== $setting ) {
			return $setting->value();
		} else {
			return null;
		}
	}

	/**
	 * Val string.
	 *
	 * @param string $name Name.
	 *
	 * @return mixed|null
	 */
	public function val_str( string $name ) {
		$setting = self::get( $name );

		if ( false !== $setting ) {
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
	 * Get array.
	 *
	 * @return array
	 */
	public function get_array(): array {
		$array = array();
		foreach ( $this->settings as $setting ) {
			$array[ $setting->name() ] = $setting->value();
		}

		return $array;
	}

	/**
	 * Is setting.
	 *
	 * @param string $name Name.
	 *
	 * @return bool
	 */
	public function is_setting( string $name ): bool {
		return ( is_string( $name ) && array_key_exists( $name, $this->settings ) );
	}

	/**
	 * Gets default settings, either as associative array of name=>value or
	 * Urvanov_Syntax_Highlighter_Setting objects.
	 *
	 * @param string $name Name.
	 * @param bool   $objects Objects.
	 *
	 * @return array|false|mixed
	 */
	public static function get_defaults( $name = null, $objects = true ) {
		if ( null === self::$default ) {
			self::$default = new Urvanov_Syntax_Highlighter_Settings();
		}
		if ( null === $name ) {
			// Get all settings.
			if ( $objects ) {
				// Return array of objects.
				return self::$default->get();
			} else {
				// Return associative array of name=>value.
				$settings = self::$default->get();
				$defaults = array();
				foreach ( $settings as $setting ) {
					$defaults[ $setting->name() ] = $setting->value();
				}

				return $defaults;
			}
		} else {
			// Return specific setting.
			if ( $objects ) {
				return self::$default->get( $name );
			} else {
				return self::$default->get( $name )->value();
			}
		}
	}

	/**
	 * Get defaults array.
	 *
	 * @return array|false|mixed
	 */
	public static function get_defaults_array() {
		return self::get_defaults( null, false );
	}

	/**
	 * Validates settings coming from an HTML form and also for internal use.
	 * This is used when saving form an HTML form to the db, and also when reading from the db
	 * back into the global settings.
	 *
	 * @param string $name Name.
	 * @param mixed  $value Value.
	 */
	public static function validate( $name = '', $value = '' ) {
		if ( ! is_string( $name ) ) {
			return '';
		}

		// Type-cast to correct value for known settings.
		$setting = Urvanov_Syntax_Highlighter_Global_Settings::get( $name );
		if ( false !== $setting ) {

			// Booleans settings that are sent as string are allowed to have "false" == false.
			if ( is_bool( $setting->def() ) ) {
				if ( is_string( $value ) ) {
					$value = UrvanovSyntaxHighlighterUtil::str_to_bool( $value );
				}
			} else {
				// Ensure we don't cast integer settings to 0 because $value doesn't have any numbers in it.
				$value = strval( $value );
				// Only occurs when saving from the form ($_POST values are strings).
				$cleaned = $setting->sanitize( $value, false );
				if ( '' === $value || '' === $cleaned ) {
					// The value sent has no integers, change to default.
					$value = $setting->def();
				} else {
					// Cleaned value is int.
					$value = $cleaned;
				}

				// Cast all other settings as usual.
				if ( ! settype( $value, $setting->type() ) ) {
					// If we can't cast, then use default value.
					if ( $setting->is_array() ) {
						$value = 0; // default index.
					} else {
						$value = $setting->def();
					}
				}
			}
		} else {
			// If setting not found, remove value.
			return '';
		}

		switch ( $name ) {
			case self::LOCAL_PATH:
				$path = wp_parse_url( $value, PHP_URL_PATH );

				// Remove all spaces, prefixed and trailing forward slashes.
				$path = preg_replace( '#^/*|/*$|\s*#', '', $path );

				// Replace backslashes.
				$path = preg_replace( '#\\\\#', '/', $path );

				// Append trailing forward slash.
				if ( ! empty( $path ) ) {
					$path .= '/';
				}

				return $path;
			case self::FONT_SIZE:
				if ( $value < 1 ) {
					$value = 1;
				}
				break;
			case self::LINE_HEIGHT:
				$font_size = Urvanov_Syntax_Highlighter_Global_Settings::val( self::FONT_SIZE );
				$value     = $value >= $font_size ? $value : $font_size;
				break;
			case self::THEME:
				$value = strtolower( $value );
				// XXX validate settings here.
		}

		// If no validation occurs, return value.
		return $value;
	}

	/**
	 * Takes an associative array of "smart settings" and regular settings. Smart settings can be used
	 * to configure regular settings quickly.
	 * E.g. 'max_height="20px"' will set 'height="20"', 'height_mode="0", height_unit="0"'.
	 *
	 * @param mixed $settings Settings.
	 *
	 * @return array|false
	 */
	public static function smart_settings( $settings ) {
		if ( ! is_array( $settings ) ) {
			return false;
		}

		// If a setting is given, it is automatically enabled.
		foreach ( $settings as $name => $value ) {
			$setting = self::get( $name );
			if ( false !== $setting && is_bool( $setting->def() ) ) {
				$value = UrvanovSyntaxHighlighterUtil::str_to_bool( $value );
			}

			// XXX removed height and width, since it wasn't using the global settings for mode if only height was provided.
			if ( 'min-height' === $name || 'max-height' === $name ) {
				self::smart_hw( $name, self::HEIGHT_SET, self::HEIGHT_MODE, self::HEIGHT_UNIT, $settings );
			} elseif ( 'min-width' === $name || 'max-width' === $name ) {
				self::smart_hw( $name, self::WIDTH_SET, self::WIDTH_MODE, self::WIDTH_UNIT, $settings );
			} elseif ( self::FONT_SIZE === $name ) {
				$settings[ self::FONT_SIZE_ENABLE ] = true;
			} elseif ( self::TOP_MARGIN === $name ) {
				$settings[ self::TOP_SET ] = true;
			} elseif ( self::LEFT_MARGIN === $name ) {
				$settings[ self::LEFT_SET ] = true;
			} elseif ( self::BOTTOM_MARGIN === $name ) {
				$settings[ self::BOTTOM_SET ] = true;
			} elseif ( self::RIGHT_MARGIN === $name ) {
				$settings[ self::RIGHT_SET ] = true;
			} elseif ( self::ERROR_MSG === $name ) {
				$settings[ self::ERROR_MSG_SHOW ] = true;
			} elseif ( self::H_ALIGN === $name ) {
				$settings[ self::FLOAT_ENABLE ] = true;

				$value  = UrvanovSyntaxHighlighterUtil::tlower( $value );
				$values = array(
					'none'   => 0,
					'left'   => 1,
					'center' => 2,
					'right'  => 3,
				);

				if ( array_key_exists( $value, $values ) ) {
					$settings[ self::H_ALIGN ] = $values[ $value ];
				}
			} elseif ( self::SHOW_LANG === $name ) {
				$value  = UrvanovSyntaxHighlighterUtil::tlower( $value );
				$values = array(
					'found'  => 0,
					'always' => 1,
					'true'   => 1,
					'never'  => 2,
					'false'  => 2,
				);

				if ( array_key_exists( $value, $values ) ) {
					$settings[ self::SHOW_LANG ] = $values[ $value ];
				}
			} elseif ( self::TOOLBAR === $name ) {
				if ( UrvanovSyntaxHighlighterUtil::tlower( $value ) === 'always' ) {
					$settings[ self::TOOLBAR ] = 1;
				} elseif ( UrvanovSyntaxHighlighterUtil::str_to_bool( $value ) === false ) {
					$settings[ self::TOOLBAR ] = 2;
				}
			}
		}

		return $settings;
	}

	/**
	 * Used for height and width smart settings, I couldn't bear to copy paste code twice...
	 *
	 * @param mixed $name Name.
	 * @param mixed $set Set.
	 * @param mixed $mode Mode.
	 * @param mixed $unit Unit.
	 * @param mixed $settings Settings.
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
		if ( 3 === count( $match ) ) {
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
 *
 * Class Urvanov_Syntax_Highlighter_Global_Settings
 */
class Urvanov_Syntax_Highlighter_Global_Settings { // phpcs:ignore

	/**
	 * The global settings stored as a Urvanov_Syntax_Highlighter_Settings object.
	 *
	 * @var null
	 */
	private static $global = null;

	/**
	 * The URL of the site (eg. http://localhost/example/).
	 *
	 * @var string
	 */
	private static $site_http = '';

	/**
	 * The absolute root directory of the site (eg. /User/example/).
	 *
	 * @var string
	 */
	private static $site_path = '';

	/**
	 * The absolute root directory of the plugins (eg. /User/example/plugins).
	 *
	 * @var string
	 */
	private static $plugin_path = '';

	/**
	 * Upload path.
	 *
	 * @var string
	 */
	private static $upload_path = '';

	/**
	 * Upload URL.
	 *
	 * @var string
	 */
	private static $upload_url = '';

	/**
	 * Make dir.
	 *
	 * @var null
	 */
	private static $mkdir = null;

	/**
	 * Urvanov_Syntax_Highlighter_Global_Settings constructor.
	 */
	private function __construct() {}

	/**
	 * Init.
	 */
	private static function init() {
		if ( null === self::$global ) {
			self::$global = new Urvanov_Syntax_Highlighter_Settings();
		}
	}

	/**
	 * Get.
	 *
	 * @param string $name Name.
	 *
	 * @return mixed
	 */
	public static function get( $name = null ) {
		self::init();

		return self::$global->get( $name );
	}

	/**
	 * Get array.
	 *
	 * @return mixed
	 */
	public static function get_array() {
		self::init();

		return self::$global->get_array();
	}

	/**
	 * Get object.
	 *
	 * @return mixed
	 */
	public static function get_obj() {
		self::init();

		return self::$global->copy();
	}

	/**
	 * Val.
	 *
	 * @param string $name Name.
	 *
	 * @return mixed
	 */
	public static function val( $name = null ) {
		self::init();

		return self::$global->val( $name );
	}

	/**
	 * Val string.
	 *
	 * @param string $name Name.
	 *
	 * @return mixed
	 */
	public static function val_str( $name = null ) {
		self::init();

		return self::$global->val_str( $name );
	}

	/**
	 * Had changed.
	 *
	 * @param mixed $input Input.
	 * @param mixed $setting Setting.
	 * @param mixed $value Value.
	 *
	 * @return bool
	 */
	public static function has_changed( $input, $setting, $value ): bool {
		return $setting === $input && self::val( $setting ) !== $value;
	}

	/**
	 * Set.
	 *
	 * @param string $name Name.
	 * @param mixed  $value Value.
	 * @param bool   $replace Replace.
	 */
	public static function set( $name = '', $value = null, $replace = false ) {
		self::init();
		self::$global->set( $name, $value, $replace );
	}

	/**
	 * Site URL.
	 *
	 * @param string $site_http Site URL.
	 *
	 * @return string
	 */
	public static function site_url( $site_http = null ): string {
		if ( null === $site_http ) {
			return self::$site_http;
		} else {
			self::$site_http = UrvanovSyntaxHighlighterUtil::url_slash( $site_http );
		}

		return '';
	}

	/**
	 * Site path.
	 *
	 * @param string $site_path Site path.
	 *
	 * @return string
	 */
	public static function site_path( $site_path = null ): string {
		if ( null === $site_path ) {
			return self::$site_path;
		} else {
			self::$site_path = UrvanovSyntaxHighlighterUtil::path_slash( $site_path );
		}

		return '';
	}

	/**
	 * Plugin path.
	 *
	 * @param string $plugin_path Plugin path.
	 *
	 * @return string
	 */
	public static function plugin_path( $plugin_path = null ): string {
		if ( null === $plugin_path ) {
			return self::$plugin_path;
		} else {
			self::$plugin_path = UrvanovSyntaxHighlighterUtil::path_slash( $plugin_path );
		}

		return '';
	}

	/**
	 * Upload path.
	 *
	 * @param string $upload_path Upload path.
	 *
	 * @return string
	 */
	public static function upload_path( $upload_path = null ): string {
		if ( null === $upload_path ) {
			return self::$upload_path;
		} else {
			self::$upload_path = UrvanovSyntaxHighlighterUtil::path_slash( $upload_path );
		}

		return '';
	}

	/**
	 * Upload URL.
	 *
	 * @param string $upload_url Upload URL.
	 *
	 * @return string
	 */
	public static function upload_url( $upload_url = null ): string {
		if ( null === $upload_url ) {
			return self::$upload_url;
		} else {
			self::$upload_url = UrvanovSyntaxHighlighterUtil::url_slash( $upload_url );
		}

		return '';
	}

	/**
	 * Set make dir.
	 *
	 * @param mixed $mkdir Mkdir.
	 *
	 * @return null
	 */
	public static function set_mkdir( $mkdir = null ) {
		if ( null === $mkdir ) {
			return self::$mkdir;
		} else {
			self::$mkdir = $mkdir;
		}
	}

	/**
	 * Make dir.
	 *
	 * @param string $dir Dir.
	 */
	public static function mkdir( $dir = null ) {
		if ( self::$mkdir ) {
			call_user_func( self::$mkdir, $dir );
		} else {
			mkdir( $dir, 0777, true );
		}
	}
}

new Urvanov_Syntax_Highlighter_Validator( '#\d+#' );

/**
 * Class Urvanov_Syntax_Highlighter_Validator
 */
class Urvanov_Syntax_Highlighter_Validator { // phpcs:ignore

	/**
	 * Pattern.
	 *
	 * @var string
	 */
	private $pattern = '#*#msi';

	/**
	 * Urvanov_Syntax_Highlighter_Validator constructor.
	 *
	 * @param string $pattern Pattern.
	 */
	public function __construct( $pattern = '' ) {
		$this->pattern( $pattern );
	}

	/**
	 * Pattern.
	 *
	 * @param string $pattern Pattern.
	 *
	 * @return mixed
	 */
	public function pattern( $pattern = '' ) {
		if ( null === $pattern ) {
			return null;
		} else {
			$this->pattern = $pattern;
		}
	}

	/**
	 * Validate.
	 *
	 * @param string $str Str.
	 *
	 * @return bool
	 */
	public function validate( $str = '' ): bool {
		return preg_match( $this->pattern, $str ) !== false;
	}

	/**
	 * Sanitize.
	 *
	 * @param string $str Str.
	 *
	 * @return string
	 */
	public function sanitize( $str = '' ): string {
		preg_match_all( $this->pattern, $str, $matches );
		$result = '';

		foreach ( $matches as $match ) {
			$result .= $match[0];
		}

		return $result;
	}
}

/**
 * Class Urvanov_Syntax_Highlighter_Non_Neg_Int_Validator
 */
class Urvanov_Syntax_Highlighter_Non_Neg_Int_Validator extends Urvanov_Syntax_Highlighter_Validator { // phpcs:ignore

	/**
	 * Urvanov_Syntax_Highlighter_Non_Neg_Int_Validator constructor.
	 */
	public function __construct() {
		parent::__construct( '#\d+#' );
	}
}

/**
 * Class Urvanov_Syntax_Highligher_Int_Validator
 */
class Urvanov_Syntax_Highligher_Int_Validator extends Urvanov_Syntax_Highlighter_Validator { // phpcs:ignore

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
 *
 * Class Urvanov_Syntax_Highlighter_Setting
 */
class Urvanov_Syntax_Highlighter_Setting { // phpcs:ignore

	/**
	 * Name.
	 *
	 * @var string
	 */
	private $name = '';

	/**
	 * The type of variables that can be set as the value.
	 * For dropdown settings, value is int, even though value() will return a string.
	 *
	 * @var null
	 */
	private $type = null;

	/**
	 * Stores string array for dropdown settings
	 *
	 * @var null
	 */
	private $default = null;

	/**
	 * Stores index int for dropdown settings.
	 *
	 * @var null
	 */
	private $value = null;

	/**
	 * Only TRUE for dropdown settings.
	 *
	 * @var bool
	 */
	private $is_array = false;

	/**
	 * Locked.
	 *
	 * @var bool
	 */
	private $locked = false;

	/**
	 * Validator.
	 *
	 * @var null
	 */
	private $validator = null;

	/**
	 * Urvanov_Syntax_Highlighter_Setting constructor.
	 *
	 * @param string $name Name.
	 * @param string $default Default.
	 * @param mixed  $value Value.
	 * @param mixed  $locked Locked.
	 */
	public function __construct( $name = '', $default = '', $value = null, $locked = null ) {
		$this->name( $name );

		if ( null !== $default ) {
			$this->def( $default ); // Perform first to set type.
		}

		if ( null !== $value ) {
			$this->value( $value );
		}

		if ( null !== $locked ) {
			$this->locked( $locked );
		}
	}

	/**
	 * __toString.
	 *
	 * @return string
	 */
	public function __tostring() {
		return $this->name;
	}

	/**
	 * Copy.
	 *
	 * @return Urvanov_Syntax_Highlighter_Setting
	 */
	public function copy(): Urvanov_Syntax_Highlighter_Setting {
		return new Urvanov_Syntax_Highlighter_Setting( $this->name, $this->default, $this->value, $this->locked );
	}

	/**
	 * Name.
	 *
	 * @param string $name Name.
	 *
	 * @return string
	 */
	public function name( $name = null ): string {
		if ( ! UrvanovSyntaxHighlighterUtil::str( $this->name, $name ) ) {
			return $this->name;
		}

		return '';
	}

	/**
	 * Type.
	 *
	 * @return null
	 */
	public function type() {
		return $this->type;
	}

	/**
	 * Is array.
	 *
	 * @return bool
	 */
	public function is_array(): bool {
		return $this->is_array;
	}

	/**
	 * Locked.
	 *
	 * @param mixed $locked Locked.
	 *
	 * @return bool
	 */
	public function locked( $locked = null ): bool {
		if ( null === $locked ) {
			return $this->locked;
		} else {
			$this->locked = ( true === $locked );
		}

		return false;
	}

	/**
	 * Sets/gets value;
	 * Value is index (int) in default value (array) for dropdown settings.
	 * value($value) is alias for index($index) if dropdown setting.
	 * value() returns string value at current index for dropdown settings.
	 *
	 * @param mixed $value Value.
	 *
	 * @return mixed
	 */
	public function value( $value = null ) {
		if ( null === $value ) {
			if ( null !== $this->value ) {
				return $this->value;
			} else {
				if ( $this->is_array ) {
					return 0;
				} else {
					return $this->default;
				}
			}
		} elseif ( false === $this->locked ) {
			if ( $this->is_array ) {
				$this->index( $value ); // $value is index.
			} else {
				settype( $value, $this->type ); // Type cast.
				$this->value = $value;
			}

			return 0;
		}
	}

	/**
	 * Array value.
	 *
	 * @return mixed|null
	 */
	public function array_value() {
		if ( $this->is_array ) {
			return null;
		}

		return $this->default[ $this->value ];
	}

	/**
	 * Sets/gets default value.
	 * For dropdown settings, default value is array of all possible value strings.
	 *
	 * @param mixed $default Default.
	 */
	public function def( $default = null ) {

		// Only allow default to be set once.
		if ( null === $this->type && null !== $default ) {

			// For dropdown settings.
			if ( is_array( $default ) ) { // The only time we don't use $this->is_array.

				// If empty, set to blank array.
				if ( empty( $default ) ) {
					$default = array( '' );
				} else {
					// Ensure all values are unique strings.

					$default = UrvanovSyntaxHighlighterUtil::array_unique_str( $default );
				}
				$this->value = 0; // initial index.

				$this->is_array = true;
				$this->type     = gettype( 0 ); // Type is int (index).
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
	 * INdex.
	 *
	 * Sets/gets index.
	 *
	 * @param int|string $index Index.
	 *
	 * @return bool
	 */
	public function index( $index = null ) {
		if ( ! $this->is_array ) {
			return false;
		} elseif ( null === $index ) {
			return $this->value; // return current index.
		} else {
			if ( ! is_int( $index ) ) {
				// Ensure $value is int for index.
				$index = intval( $index );
			}

			// Validate index.
			if ( $index < 0 || $index > count( $this->default ) - 1 ) {
				$index = 0;
			}

			$this->value = $index;
		}

		return '';
	}

	/**
	 * Finds the index of a string in an array setting.
	 *
	 * @param string $str Str.
	 *
	 * @return false|int
	 */
	public function find_index( string $str ) {
		if ( ! $this->is_array || is_string( $str ) ) {
			return false;
		}

		$count = count( $this->default );
		for ( $i = 0; $i < $count; $i ++ ) {
			if ( $str === $this->default[ $i ] ) {
				return $i;
			}
		}

		return false;
	}

	/**
	 * Validator.
	 *
	 * @param mixed $validator Validator.
	 *
	 * @return null
	 */
	public function validator( $validator ): ?string {
		if ( null === $validator ) {
			return $this->validator;
		} else {
			$this->validator = $validator;
		}

		return '';
	}

	/**
	 * Sanitize.
	 *
	 * @param string $str Str.
	 *
	 * @return string
	 */
	public function sanitize( $str = '' ): string {
		if ( null !== $this->validator ) {
			return $this->validator->sanitize( $str );
		} else {
			return $str;
		}
	}
}

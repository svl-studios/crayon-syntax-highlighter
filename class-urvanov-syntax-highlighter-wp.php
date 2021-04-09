<?php
/**
 * Highlighter WP Class
 *
 * @package   Crayon Syntax Highlighter
 * @author    Fedor Urvanov, Aram Kocharyan
 * @copyright Copyright 2013, Aram Kocharyan
 * @link      https://urvanov.ru
 */

defined( 'ABSPATH' ) || exit;

require_once 'class-urvanov-syntax-highlighter-global.php';
require_once URVANOV_SYNTAX_HIGHLIGHTER_LANGS_PHP;
require_once URVANOV_SYNTAX_HIGHLIGHTER_THEMES_PHP;
require_once URVANOV_SYNTAX_HIGHLIGHTER_FONTS_PHP;
require_once URVANOV_SYNTAX_HIGHLIGHTER_SETTINGS_PHP;

/**
 * Manages global settings within WP and integrates them with Urvanov_Syntax_Highlighter_Settings.
 * CrayonHighlighter and any non-WP classes will only use Urvanov_Syntax_Highlighter_Settings to separate
 * the implementation of global settings and ensure any system can use them.
 * Class Urvanov_Syntax_Highlighter_Settings_WP
 */
class Urvanov_Syntax_Highlighter_Settings_WP {

	/**
	 * A copy of the current options in db.
	 *
	 * @var null
	 */
	private static $options = null;

	/**
	 * Posts containing crayons in db.
	 *
	 * @var null
	 */
	private static $urvanov_syntax_highlighter_posts = null;

	/**
	 * Posts containing legacy tags in db.
	 *
	 * @var null
	 */
	private static $urvanov_syntax_highlighter_legacy_posts = null;

	/**
	 * An array of cache names for use with Transients API.
	 *
	 * @var null
	 */
	private static $cache = null;

	/**
	 * Array of settings to pass to js.
	 *
	 * @var null
	 */
	private static $js_settings = null;

	/**
	 * JS strings.
	 *
	 * @var null
	 */
	private static $js_strings = null;

	/**
	 * Admin JS messages.
	 *
	 * @var null
	 */
	private static $admin_js_settings = null;

	/**
	 * Admins JS strings.
	 *
	 * @var null
	 */
	private static $admin_js_strings = null;

	/**
	 * Admin page.
	 *
	 * @var string
	 */
	private static $admin_page = '';

	/**
	 * Is fully loaded.
	 *
	 * @var bool
	 */
	private static $is_fully_loaded = false;

	/**
	 * Settings.
	 */
	const SETTINGS = 'urvanov_syntax_highlighter_fields';

	/**
	 * Fields.
	 */
	const FIELDS = 'urvanov_syntax_highlighter_settings';

	/**
	 * Options.
	 */
	const OPTIONS = 'urvanov_syntax_highlighter_options';

	/**
	 * Posts.
	 */
	const POSTS = 'urvanov_syntax_highlighter_posts';

	/**
	 * Legacy posts.
	 */
	const LEGACY_POSTS = 'urvanov_syntax_highlighter_legacy_posts';

	/**
	 * Cache.
	 */
	const CACHE = 'urvanov_syntax_highlighter_cache';

	/**
	 * General.
	 */
	const GENERAL = 'urvanov_syntax_highlighter_general';

	/**
	 * Debug.
	 */
	const DEBUG = 'urvanov_syntax_highlighter_debug';

	/**
	 * About.
	 */
	const ABOUT = 'urvanov_syntax_highlighter_about';

	/**
	 * Log clea.
	 */
	const LOG_CLEAR = 'log_clear';

	/**
	 * Log email admin.
	 */
	const LOG_EMAIL_ADMIN = 'log_email_admin';

	/**
	 * Log email dev.
	 */
	const LOG_EMAIL_DEV = 'log_email_dev';

	/**
	 * Sample code.
	 */
	const SAMPLE_CODE = 'sample-code';

	/**
	 * Cache clear.
	 */
	const CACHE_CLEAR = 'urvanov-syntax-highlighter-cache-clear';


	/**
	 * Urvanov_Syntax_Highlighter_Settings_WP constructor.
	 */
	private function __construct() {}

	/**
	 * Admin load.
	 */
	public static function admin_load() {
		self::$admin_page = add_options_page( 'Crayon Syntax Highlighter ' . esc_html__( 'Settings', 'urvanov-syntax-highlighter' ), 'Crayon', 'manage_options', 'urvanov_syntax_highlighter_settings', 'Urvanov_Syntax_Highlighter_Settings_WP::settings' );
		$admin_page       = self::$admin_page;

		add_action( "admin_print_scripts-$admin_page", 'Urvanov_Syntax_Highlighter_Settings_WP::admin_scripts' );
		add_action( "admin_print_styles-$admin_page", 'Urvanov_Syntax_Highlighter_Settings_WP::admin_styles' );
		add_action( "admin_print_scripts-$admin_page", 'Urvanov_Syntax_Highlighter_Theme_Editor_WP::admin_resources' );

		// Register settings, second argument is option name stored in db.
		register_setting( self::FIELDS, self::OPTIONS, 'Urvanov_Syntax_Highlighter_Settings_WP::settings_validate' );
		add_action( "admin_head-$admin_page", 'Urvanov_Syntax_Highlighter_Settings_WP::admin_init' );

		// Register settings for post page.
		add_action( 'admin_print_styles-post-new.php', 'Urvanov_Syntax_Highlighter_Settings_WP::admin_scripts' );
		add_action( 'admin_print_styles-post.php', 'Urvanov_Syntax_Highlighter_Settings_WP::admin_scripts' );
		add_action( 'admin_print_styles-post-new.php', 'Urvanov_Syntax_Highlighter_Settings_WP::admin_styles' );
		add_action( 'admin_print_styles-post.php', 'Urvanov_Syntax_Highlighter_Settings_WP::admin_styles' );

		// TODO deprecated since WP 3.3, remove eventually.
		global $wp_version;
		if ( $wp_version >= '3.3' ) {
			add_action( "load-$admin_page", 'Urvanov_Syntax_Highlighter_Settings_WP::help_screen' );
		} else {
			add_filter( 'contextual_help', 'Urvanov_Syntax_Highlighter_Settings_WP::cont_help', 10, 3 );
		}
	}

	/**
	 * Admin styles.
	 */
	public static function admin_styles() {
		global $urvanov_syntax_highlighter_version;

		if ( URVANOV_SYNTAX_HIGHLIGHTER_MINIFY ) {
			wp_enqueue_style( 'urvanov_syntax_highlighter', plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_STYLE_MIN, __FILE__ ), array( 'editor-buttons' ), $urvanov_syntax_highlighter_version );
		} else {
			wp_enqueue_style( 'urvanov_syntax_highlighter', plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_STYLE, __FILE__ ), array(), $urvanov_syntax_highlighter_version );
			wp_enqueue_style( 'urvanov_syntax_highlighter_global', plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_STYLE_GLOBAL, __FILE__ ), array(), $urvanov_syntax_highlighter_version );
			wp_enqueue_style( 'urvanov_syntax_highlighter_admin', plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_STYLE_ADMIN, __FILE__ ), array( 'editor-buttons' ), $urvanov_syntax_highlighter_version );
		}
	}

	/**
	 * Admin scripts.
	 */
	public static function admin_scripts() {
		global $urvanov_syntax_highlighter_version;

		if ( URVANOV_SYNTAX_HIGHLIGHTER_MINIFY ) {
			Urvanov_Syntax_Highlighter_Plugin::enqueue_resources();
		} else {
			wp_enqueue_script( 'urvanov_syntax_highlighter_util_js', plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_JS_UTIL, __FILE__ ), array( 'jquery' ), $urvanov_syntax_highlighter_version, false );
			self::other_scripts();
		}

		self::init_js_settings();

		if ( is_admin() ) {
			wp_enqueue_script(
				'urvanov_syntax_highlighter_admin_js',
				plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_JS_ADMIN, __FILE__ ),
				array(
					'jquery',
					'urvanov_syntax_highlighter_js',
					'wpdialogs',
				),
				$urvanov_syntax_highlighter_version,
				false
			);

			self::init_admin_js_settings();
		}
	}

	/**
	 * Other scripts.
	 */
	public static function other_scripts() {
		global $urvanov_syntax_highlighter_version;

		UrvanovSyntaxHighlighterLog::debug( 'other_scripts' );
		self::load_settings( true );
		$deps = array( 'jquery', 'urvanov_syntax_highlighter_util_js' );

		if ( Urvanov_Syntax_Highlighter_Global_Settings::val( Urvanov_Syntax_Highlighter_Settings::POPUP ) || is_admin() ) {
			// TODO include anyway and minify.
			wp_enqueue_script( 'urvanov_syntax_highlighter_jquery_popup', plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_JQUERY_POPUP, __FILE__ ), array( 'jquery' ), $urvanov_syntax_highlighter_version, false );
			$deps[] = 'urvanov_syntax_highlighter_jquery_popup';
		}

		$result = wp_enqueue_script( 'urvanov_syntax_highlighter_js', plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_JS, __FILE__ ), $deps, $urvanov_syntax_highlighter_version, false );
		UrvanovSyntaxHighlighterLog::debug( $result, 'wp_enqueue_script=' . $result );
	}

	/**
	 * Init JS settings.
	 */
	public static function init_js_settings() {
		UrvanovSyntaxHighlighterLog::debug( 'Init js settings...' );

		// This stores JS variables used in AJAX calls and in the JS files.
		global $urvanov_syntax_highlighter_version;
		self::load_settings( true );

		if ( ! self::$js_settings ) {
			self::$js_settings = array(
				'version'    => $urvanov_syntax_highlighter_version,
				'is_admin'   => intval( is_admin() ),
				'ajaxurl'    => admin_url( 'admin-ajax.php' ),
				'prefix'     => Urvanov_Syntax_Highlighter_Settings::PREFIX,
				'setting'    => Urvanov_Syntax_Highlighter_Settings::SETTING,
				'selected'   => Urvanov_Syntax_Highlighter_Settings::SETTING_SELECTED,
				'changed'    => Urvanov_Syntax_Highlighter_Settings::SETTING_CHANGED,
				'special'    => Urvanov_Syntax_Highlighter_Settings::SETTING_SPECIAL,
				'orig_value' => Urvanov_Syntax_Highlighter_Settings::SETTING_ORIG_VALUE,
				'debug'      => URVANOV_SYNTAX_HIGHLIGHTER_DEBUG,
			);
		}

		if ( ! self::$js_strings ) {
			self::$js_strings = array(
				// translators: %s = ??
				'copy'     => esc_html__( 'Press %1$s to Copy, %2$s to Paste', 'urvanov-syntax-highlighter' ),
				'minimize' => esc_html__( 'Click To Expand Code', 'urvanov-syntax-highlighter' ),
			);
		}

		UrvanovSyntaxHighlighterLog::debug( self::$js_settings, 'UrvanovSyntaxHighlighterSyntaxSettings to js...' );

		if ( URVANOV_SYNTAX_HIGHLIGHTER_MINIFY ) {
			$result = wp_localize_script( 'urvanov_syntax_highlighter_js', 'UrvanovSyntaxHighlighterSyntaxSettings', self::$js_settings );
			UrvanovSyntaxHighlighterLog::debug( $result, 'wp_localize_script UrvanovSyntaxHighlighterSyntaxSettings result = ' . $result );
			wp_localize_script( 'urvanov_syntax_highlighter_js', 'UrvanovSyntaxHighlighterSyntaxStrings', self::$js_strings );
			UrvanovSyntaxHighlighterLog::debug( $result, 'wp_localize_script UrvanovSyntaxHighlighterSyntaxString result = ' . $result );
		} else {
			wp_localize_script( 'urvanov_syntax_highlighter_util_js', 'UrvanovSyntaxHighlighterSyntaxSettings', self::$js_settings );
			wp_localize_script( 'urvanov_syntax_highlighter_util_js', 'UrvanovSyntaxHighlighterSyntaxStrings', self::$js_strings );
		}
	}

	/**
	 * Init admin JS settings.
	 */
	public static function init_admin_js_settings() {
		if ( ! self::$admin_js_settings ) {

			// We need to load themes at this stage.
			self::load_settings();

			$themes_      = Urvanov_Syntax_Highlighter_Resources::themes()->get();
			$stock_themes = array();
			$user_themes  = array();

			foreach ( $themes_ as $theme ) {
				$id   = $theme->id();
				$name = $theme->name();

				if ( $theme->user() ) {
					$user_themes[ $id ] = $name;
				} else {
					$stock_themes[ $id ] = $name;
				}
			}

			self::$admin_js_settings = array(
				'themes'         => array_merge( $stock_themes, $user_themes ),
				'stockThemes'    => $stock_themes,
				'userThemes'     => $user_themes,
				'defaultTheme'   => Urvanov_Syntax_Highlighter_Themes::DEFAULT_THEME,
				'themesURL'      => Urvanov_Syntax_Highlighter_Resources::themes()->dirurl( false ),
				'userThemesURL'  => Urvanov_Syntax_Highlighter_Resources::themes()->dirurl( true ),
				'sampleCode'     => self::SAMPLE_CODE,
				'dialogFunction' => 'wpdialog',
			);

			wp_localize_script( 'urvanov_syntax_highlighter_admin_js', 'UrvanovSyntaxHighlighterAdminSettings', self::$admin_js_settings );
		}

		if ( ! self::$admin_js_strings ) {
			self::$admin_js_strings = array(
				'prompt'     => esc_html__( 'Prompt', 'urvanov-syntax-highlighter' ),
				'value'      => esc_html__( 'Value', 'urvanov-syntax-highlighter' ),
				'alert'      => esc_html__( 'Alert', 'urvanov-syntax-highlighter' ),
				'no'         => esc_html__( 'No', 'urvanov-syntax-highlighter' ),
				'yes'        => esc_html__( 'Yes', 'urvanov-syntax-highlighter' ),
				'confirm'    => esc_html__( 'Confirm', 'urvanov-syntax-highlighter' ),
				'changeCode' => esc_html__( 'Change Code', 'urvanov-syntax-highlighter' ),
			);

			wp_localize_script( 'urvanov_syntax_highlighter_admin_js', 'UrvanovSyntaxHighlighterAdminStrings', self::$admin_js_strings );
		}
	}

	/**
	 * Settings.
	 */
	public static function settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'urvanov-syntax-highlighter' ) );
		}

		?>
		<script type="text/javascript">
			jQuery( document ).ready( function() {
				UrvanovSyntaxHighlighterAdmin.init();
			} );
		</script>

		<div id="urvanov-syntax-highlighter-main-wrap" class="wrap">

			<div id="icon-options-general" class="icon32">
				<br>
			</div>
			<h2>
				Crayon Syntax Highlighter
				<?php esc_html_e( 'Settings', 'urvanov-syntax-highlighter' ); ?>
			</h2>
			<?php self::help(); ?>
			<form id="urvanov-syntax-highlighter-settings-form" action="options.php" method="post">
				<?php
				settings_fields( self::FIELDS );
				?>

				<?php
				do_settings_sections( self::SETTINGS );
				?>

				<p class="submit">
					<input type="submit" name="submit" id="submit" class="button-primary"
						value="<?php esc_html_e( 'Save Changes', 'urvanov-syntax-highlighter' ); ?>"/>
					<span style="width:10px; height: 5px; float:left;"></span>
					<input type="submit"
						name="<?php echo esc_attr( self::OPTIONS ); ?>[reset]"
						id="reset"
						class="button-primary"
						value="<?php esc_html_e( 'Reset Settings', 'urvanov-syntax-highlighter' ); ?>"/>
				</p>
			</form>
		</div>
		<div id="urvanov-syntax-highlighter-theme-editor-wrap" class="wrap"></div>
		<?php

	}

	/**
	 * Load the global settings and update them from the db.
	 *
	 * @param bool $just_load_settings Settings.
	 */
	public static function load_settings( $just_load_settings = false ) {
		if ( null === self::$options ) {

			// Load settings from db.
			self::$options = get_option( self::OPTIONS );
			if ( ! self::$options ) {
				self::$options = Urvanov_Syntax_Highlighter_Settings::get_defaults_array();
				update_option( self::OPTIONS, self::$options );
			}

			// Initialise default global settings and update them from db.
			Urvanov_Syntax_Highlighter_Global_Settings::set( self::$options );
		}

		if ( ! self::$is_fully_loaded && ! $just_load_settings ) {
			// Load everything else as well.

			// For local file loading
			// This is used to decouple WP functions from internal Crayon classes.
			Urvanov_Syntax_Highlighter_Global_Settings::site_url( home_url() );
			Urvanov_Syntax_Highlighter_Global_Settings::site_path( ABSPATH );
			Urvanov_Syntax_Highlighter_Global_Settings::plugin_path( plugins_url( '', __FILE__ ) );
			$upload = wp_upload_dir();

			UrvanovSyntaxHighlighterLog::debug( $upload, 'WP UPLOAD FUNCTION' );
			UrvanovSyntaxHighlighterLog::debug( URVANOV_SYNTAX_HIGHLIGHTER_DIR, 'URVANOV_SYNTAX_HIGHLIGHTER_DIR=' . URVANOV_SYNTAX_HIGHLIGHTER_DIR );
			Urvanov_Syntax_Highlighter_Global_Settings::upload_path( UrvanovSyntaxHighlighterUtil::path_slash( $upload['basedir'] ) . URVANOV_SYNTAX_HIGHLIGHTER_DIR );
			Urvanov_Syntax_Highlighter_Global_Settings::upload_url( $upload['baseurl'] . '/' . URVANOV_SYNTAX_HIGHLIGHTER_DIR );
			UrvanovSyntaxHighlighterLog::debug( Urvanov_Syntax_Highlighter_Global_Settings::upload_path(), 'UPLOAD PATH' );
			Urvanov_Syntax_Highlighter_Global_Settings::set_mkdir( 'wp_mkdir_p' );

			// Load all available languages and themes.
			Urvanov_Syntax_Highlighter_Resources::langs()->load();
			Urvanov_Syntax_Highlighter_Resources::themes()->load();

			// Ensure all missing settings in db are replaced by default values.
			$changed = false;
			foreach ( Urvanov_Syntax_Highlighter_Settings::get_defaults_array() as $name => $value ) {

				// Add missing settings.
				if ( ! array_key_exists( $name, self::$options ) ) {
					self::$options[ $name ] = $value;
					$changed                = true;
				}
			}

			// A setting was missing, update options.
			if ( $changed ) {
				update_option( self::OPTIONS, self::$options );
			}

			self::$is_fully_loaded = true;
		}
	}

	/**
	 * Get settings.
	 *
	 * @return false|mixed|void
	 */
	public static function get_settings() {
		return get_option( self::OPTIONS );
	}

	/**
	 * Saves settings from Urvanov_Syntax_Highlighter_Global_Settings, or provided array, to the db.
	 *
	 * @param mixed $settings Settings.
	 */
	public static function save_settings( $settings = null ) {
		if ( null === $settings ) {
			$settings = Urvanov_Syntax_Highlighter_Global_Settings::get_array();
		}
		update_option( self::OPTIONS, $settings );
	}

	/**
	 * This loads the posts marked as containing Crayons
	 */
	public static function load_posts() {
		if ( null === self::$urvanov_syntax_highlighter_posts ) {

			// Load from db.
			self::$urvanov_syntax_highlighter_posts = get_option( self::POSTS );
			if ( ! self::$urvanov_syntax_highlighter_posts ) {

				// Posts don't exist! Scan for them. This will fill self::$urvanov_syntax_highlighter_posts.
				self::$urvanov_syntax_highlighter_posts = Urvanov_Syntax_Highlighter_Plugin::scan_posts();
				update_option( self::POSTS, self::$urvanov_syntax_highlighter_posts );
			}
		}

		return self::$urvanov_syntax_highlighter_posts;
	}

	/**
	 * Saves the marked posts to the db.
	 *
	 * @param mixed $posts Posts.
	 */
	public static function save_posts( $posts = null ) {
		if ( null === $posts ) {
			$posts = self::$urvanov_syntax_highlighter_posts;
		}

		update_option( self::POSTS, $posts );
		self::load_posts();
	}

	/**
	 * Adds a post as containing a Crayon.
	 *
	 * @param mixed $id ID.
	 * @param bool  $save Save.
	 */
	public static function add_post( $id, $save = true ) {
		self::load_posts();

		if ( ! in_array( $id, self::$urvanov_syntax_highlighter_posts, true ) ) {
			self::$urvanov_syntax_highlighter_posts[] = $id;
		}

		if ( $save ) {
			self::save_posts();
		}
	}

	/**
	 * Removes a post as not containing a Crayon.
	 *
	 * @param mixed $id ID.
	 * @param bool  $save Save.
	 */
	public static function remove_post( $id, $save = true ) {
		self::load_posts();

		$key = array_search( $id, self::$urvanov_syntax_highlighter_posts, true );
		if ( false === $key ) {
			return;
		}

		unset( self::$urvanov_syntax_highlighter_posts[ $key ] );

		if ( $save ) {
			self::save_posts();
		}
	}

	/**
	 * Remove posts.
	 */
	public static function remove_posts() {
		self::$urvanov_syntax_highlighter_posts = array();
		self::save_posts();
	}

	/**
	 * This loads the posts marked as containing Crayons.
	 *
	 * @param bool $force Force.
	 *
	 * @return array|false|mixed|void|null
	 */
	public static function load_legacy_posts( $force = false ) {
		if ( null === self::$urvanov_syntax_highlighter_legacy_posts || $force ) {

			// Load from db.
			self::$urvanov_syntax_highlighter_legacy_posts = get_option( self::LEGACY_POSTS );
			if ( ! self::$urvanov_syntax_highlighter_legacy_posts ) {

				// Posts don't exist! Scan for them. This will fill self::$urvanov_syntax_highlighter_legacy_posts.
				self::$urvanov_syntax_highlighter_legacy_posts = Urvanov_Syntax_Highlighter_Plugin::scan_legacy_posts();
				update_option( self::LEGACY_POSTS, self::$urvanov_syntax_highlighter_legacy_posts );
			}
		}

		return self::$urvanov_syntax_highlighter_legacy_posts;
	}

	/**
	 * Saves the marked posts to the db.
	 *
	 * @param mixed $posts Post.
	 */
	public static function save_legacy_posts( $posts = null ) {
		if ( null === $posts ) {
			$posts = self::$urvanov_syntax_highlighter_legacy_posts;
		}

		update_option( self::LEGACY_POSTS, $posts );
		self::load_legacy_posts();
	}

	/**
	 * Adds a post as containing a Crayon.
	 *
	 * @param mixed $id ID.
	 * @param bool  $save Save.
	 */
	public static function add_legacy_post( $id, $save = true ) {
		self::load_legacy_posts();

		if ( ! in_array( $id, self::$urvanov_syntax_highlighter_legacy_posts, true ) ) {
			self::$urvanov_syntax_highlighter_legacy_posts[] = $id;
		}

		if ( $save ) {
			self::save_legacy_posts();
		}
	}

	/**
	 * Removes a post as not containing a Crayon.
	 *
	 * @param mixed $id ID.
	 * @param bool  $save Save.
	 */
	public static function remove_legacy_post( $id, $save = true ) {
		self::load_legacy_posts();

		$key = array_search( $id, self::$urvanov_syntax_highlighter_legacy_posts, true );

		if ( false === $key ) {
			return;
		}

		unset( self::$urvanov_syntax_highlighter_legacy_posts[ $key ] );

		if ( $save ) {
			self::save_legacy_posts();
		}
	}

	/**
	 * Remove legacy posts.
	 */
	public static function remove_legacy_posts() {
		self::$urvanov_syntax_highlighter_legacy_posts = array();
		self::save_legacy_posts();
	}

	/**
	 * Cache.
	 *
	 * @param string $name Name.
	 */
	public static function add_cache( string $name ) {
		self::load_cache();
		if ( ! in_array( $name, self::$cache, true ) ) {
			self::$cache[] = $name;
		}
		self::save_cache();
	}

	/**
	 * Remove cache.
	 *
	 * @param string $name Name.
	 */
	public static function remove_cache( string $name ) {
		self::load_cache();

		$key = array_search( $name, self::$cache, true );

		if ( false === $key ) {
			return;
		}

		unset( self::$cache[ $key ] );
		self::save_cache();
	}

	/**
	 * Clear cache.
	 */
	public static function clear_cache() {
		self::load_cache();

		foreach ( self::$cache as $name ) {
			delete_transient( $name );
		}

		self::$cache = array();
		self::save_cache();
	}

	/**
	 * Load cahce.
	 */
	public static function load_cache() {

		// Load cache from db.
		self::$cache = get_option( self::CACHE );

		if ( ! self::$cache ) {
			self::$cache = array();
			update_option( self::CACHE, self::$cache );
		}
	}

	/**
	 * Save cache.
	 */
	public static function save_cache() {
		update_option( self::CACHE, self::$cache );
		self::load_cache();
	}

	/**
	 * Admin init.
	 */
	public static function admin_init() {

		// Load default settings if they don't exist.
		self::load_settings();

		// General.
		// Some of these will the $editor arguments, if TRUE it will alter for use in the Tag Editor.
		self::add_section( self::GENERAL, esc_html__( 'General', 'urvanov-syntax-highlighter' ) );
		self::add_field( self::GENERAL, esc_html__( 'Theme', 'urvanov-syntax-highlighter' ), 'theme' );
		self::add_field( self::GENERAL, esc_html__( 'Font', 'urvanov-syntax-highlighter' ), 'font' );
		self::add_field( self::GENERAL, esc_html__( 'Metrics', 'urvanov-syntax-highlighter' ), 'metrics' );
		self::add_field( self::GENERAL, esc_html__( 'Toolbar', 'urvanov-syntax-highlighter' ), 'toolbar' );
		self::add_field( self::GENERAL, esc_html__( 'Lines', 'urvanov-syntax-highlighter' ), 'lines' );
		self::add_field( self::GENERAL, esc_html__( 'Code', 'urvanov-syntax-highlighter' ), 'code' );
		self::add_field( self::GENERAL, esc_html__( 'Tags', 'urvanov-syntax-highlighter' ), 'tags' );
		self::add_field( self::GENERAL, esc_html__( 'Languages', 'urvanov-syntax-highlighter' ), 'langs' );
		self::add_field( self::GENERAL, esc_html__( 'Files', 'urvanov-syntax-highlighter' ), 'files' );
		self::add_field( self::GENERAL, esc_html__( 'Posts', 'urvanov-syntax-highlighter' ), 'posts' );
		self::add_field( self::GENERAL, esc_html__( 'Tag Editor', 'urvanov-syntax-highlighter' ), 'tag_editor' );
		self::add_field( self::GENERAL, esc_html__( 'Misc', 'urvanov-syntax-highlighter' ), 'misc' );

		// Debug.
		self::add_section( self::DEBUG, esc_html__( 'Debug', 'urvanov-syntax-highlighter' ) );
		self::add_field( self::DEBUG, esc_html__( 'Errors', 'urvanov-syntax-highlighter' ), 'errors' );
		self::add_field( self::DEBUG, esc_html__( 'Log', 'urvanov-syntax-highlighter' ), 'log' );

		// ABOUT.
		self::add_section( self::ABOUT, esc_html__( 'About', 'urvanov-syntax-highlighter' ) );
		$image = '<div id="urvanov-syntax-highlighter-logo">
				<img src="' . plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_LOGO, __FILE__ ) . '" /><br/></div>';

		self::add_field( self::ABOUT, $image, 'info' );
	}

	/**
	 * Add section.
	 *
	 * @param string $name     Name.
	 * @param string $title    Title.
	 * @param string $callback Callback.
	 */
	private static function add_section( string $name, string $title, $callback = '' ) {
		$callback = ( empty( $callback ) ? 'blank' : '' );
		add_settings_section( $name, $title, 'Urvanov_Syntax_Highlighter_Settings_WP::' . $callback, self::SETTINGS );
	}

	/**
	 * Add field.
	 *
	 * @param string $section  Section.
	 * @param string $title    Title.
	 * @param string $callback Callback.
	 * @param array  $args     Args.
	 */
	private static function add_field( string $section, string $title, string $callback, $args = array() ) {
		$unique = preg_replace( '#\\s#', '_', strtolower( $title ) );
		add_settings_field( $unique, $title, 'Urvanov_Syntax_Highlighter_Settings_WP::' . $callback, self::SETTINGS, $section, $args );
	}

	/**
	 * Validates all the settings passed from the form in $inputs.
	 *
	 * @param array $inputs Input array.
	 *
	 * @return array
	 */
	public static function settings_validate( array $inputs ): array {

		// Load current settings from db.
		self::load_settings( true );

		global $urvanov_syntax_highlighter_email;

		// When reset button is pressed, remove settings so default loads next time.
		if ( array_key_exists( 'reset', $inputs ) ) {
			self::clear_cache();

			return array();
		}

		// Convert old tags.
		if ( array_key_exists( 'convert', $inputs ) ) {
			$encode = array_key_exists( 'convert_encode', $inputs );
			Urvanov_Syntax_Highlighter_Plugin::convert_tags( $encode );
		}

		// Refresh internal tag management.
		if ( array_key_exists( 'refresh_tags', $inputs ) ) {
			Urvanov_Syntax_Highlighter_Plugin::refresh_posts();
		}

		// Clear the log if needed.
		if ( array_key_exists( self::LOG_CLEAR, $_POST ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			UrvanovSyntaxHighlighterLog::clear();
		}

		// Send to admin.
		if ( array_key_exists( self::LOG_EMAIL_ADMIN, $_POST ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			UrvanovSyntaxHighlighterLog::email( get_bloginfo( 'admin_email' ) );
		}

		// Send to developer.
		if ( array_key_exists( self::LOG_EMAIL_DEV, $_POST ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			UrvanovSyntaxHighlighterLog::email( $urvanov_syntax_highlighter_email, get_bloginfo( 'admin_email' ) );
		}

		// Clear the cache.
		if ( array_key_exists( self::CACHE_CLEAR, $_POST ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			self::clear_cache();
		}

		// If settings don't exist in input, set them to default.
		$global_settings = Urvanov_Syntax_Highlighter_Settings::get_defaults();

		$ignored = array( Urvanov_Syntax_Highlighter_Settings::HIDE_HELP );

		foreach ( $global_settings as $setting ) {

			// XXX Ignore some settings.
			if ( in_array( $setting->name(), $ignored, true ) ) {
				$inputs[ $setting->name() ] = Urvanov_Syntax_Highlighter_Global_Settings::val( $setting->name() );
				continue;
			}

			// If boolean setting is not in input, then it is set to FALSE in the form.
			if ( ! array_key_exists( $setting->name(), $inputs ) ) {

				// For booleans, set to FALSE (unchecked boxes are not sent as POST).
				if ( is_bool( $setting->def() ) ) {
					$inputs[ $setting->name() ] = false;
				} else {

					/**
					 * For array settings, set the input as the value, which by default is the default index.
					 */
					if ( is_array( $setting->def() ) ) {
						$inputs[ $setting->name() ] = $setting->value();
					} else {
						$inputs[ $setting->name() ] = $setting->def();
					}
				}
			}
		}

		$refresh = array(
			// These should trigger a refresh of which posts contain crayons, since they affect capturing.
			Urvanov_Syntax_Highlighter_Settings::INLINE_TAG => true,
			Urvanov_Syntax_Highlighter_Settings::INLINE_TAG_CAPTURE => true,
			Urvanov_Syntax_Highlighter_Settings::CODE_TAG_CAPTURE => true,
			Urvanov_Syntax_Highlighter_Settings::BACKQUOTE => true,
			Urvanov_Syntax_Highlighter_Settings::CAPTURE_PRE => true,
			Urvanov_Syntax_Highlighter_Settings::CAPTURE_MINI_TAG => true,
			Urvanov_Syntax_Highlighter_Settings::PLAIN_TAG => true,
		);

		// Validate inputs.
		foreach ( $inputs as $input => $value ) {
			// Convert all array setting values to ints.
			$inputs[ $input ] = Urvanov_Syntax_Highlighter_Settings::validate( $input, $value );
			$value            = $inputs[ $input ];

			// Clear cache when changed.
			if ( Urvanov_Syntax_Highlighter_Global_Settings::has_changed( $input, Urvanov_Syntax_Highlighter_Settings::CACHE, $value ) ) {
				self::clear_cache();
			}
			if ( isset( $refresh[ $input ] ) ) {
				if ( Urvanov_Syntax_Highlighter_Global_Settings::has_changed( $input, $input, $value ) ) {
					// Needs to take place, in case it refresh depends on changed value.
					Urvanov_Syntax_Highlighter_Global_Settings::set( $input, $value );
					Urvanov_Syntax_Highlighter_Plugin::refresh_posts();
				}
			}
		}

		return $inputs;
	}

	/**
	 * Section callback functions.
	 */
	public static function blank() {} // Used for required callbacks with blank content.

	/**
	 * Input.
	 *
	 * @param array $args Arguments.
	 */
	private static function input( $args = array() ) {
		$id      = '';
		$size    = 40;
		$margin  = false;
		$preview = 1;
		$break   = false;
		$type    = 'text';

		// phpcs:ignore WordPress.PHP.DontExtract
		extract( $args );

		echo '<input id="' . esc_attr( Urvanov_Syntax_Highlighter_Settings::PREFIX ) . esc_attr( $id ) . '" name="' . esc_attr( self::OPTIONS ) . '[' . esc_attr( $id ) . ']" class="' . esc_attr( Urvanov_Syntax_Highlighter_Settings::SETTING ) . '" size="' . esc_attr( $size ) . '" type="' . esc_attr( $type ) . '" value="' .
		esc_attr( self::$options[ $id ] ) . '" style="margin-left: ' . ( $margin ? '20px' : '0px' ) . ';" urvanov-syntax-highlighter-preview="' . ( $preview ? 1 : 0 ), '" />' . ( $break ? URVANOV_SYNTAX_HIGHLIGHTER_BR : '' ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	/**
	 * Checkbox.
	 *
	 * @param array $args Args.
	 * @param bool  $line_break Line break.
	 * @param bool  $preview Preview.
	 */
	private static function checkbox( array $args, $line_break = true, $preview = true ) {
		if ( empty( $args ) || ! is_array( $args ) || 2 !== count( $args ) ) {
			return;
		}

		$id          = $args[0];
		$text        = $args[1];
		$checked     = ! ( ! array_key_exists( $id, self::$options ) ) && true === self::$options[ $id ];
		$checked_str = $checked ? ' checked="checked"' : '';
		echo '<input id="', esc_attr( Urvanov_Syntax_Highlighter_Settings::PREFIX ) , esc_attr( $id ), '" name="', esc_attr( self::OPTIONS ), '[', esc_attr( $id ), ']" type="checkbox" class="' . esc_attr( Urvanov_Syntax_Highlighter_Settings::SETTING ) . '" value="1"', $checked_str, // phpcs:ignore WordPress.Security.EscapeOutput
		' urvanov-syntax-highlighter-preview="', ( $preview ? 1 : 0 ), '" /> ', '<label for="', esc_attr( Urvanov_Syntax_Highlighter_Settings::PREFIX ), $id, '">', $text, '</label>', ( $line_break ? URVANOV_SYNTAX_HIGHLIGHTER_BR : '' ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	/**
	 * Draws a dropdown by loading the default value (an array) from a setting.
	 *
	 * @param mixed  $id ID.
	 * @param bool   $line_break Line breack.
	 * @param bool   $preview Preview.
	 * @param bool   $echo Echo.
	 * @param array  $resources Resources.
	 * @param string $selected Selected.
	 *
	 * @return string|void
	 */
	private static function dropdown( $id, $line_break = true, $preview = true, $echo = true, $resources = null, $selected = null ) {
		if ( ! array_key_exists( $id, self::$options ) ) {
			return;
		}

		$resources = null !== $resources ? $resources : Urvanov_Syntax_Highlighter_Global_Settings::get( $id )->def();

		$return = '<select id="' . Urvanov_Syntax_Highlighter_Settings::PREFIX . $id . '" name="' . self::OPTIONS . '[' . $id . ']" class="' . Urvanov_Syntax_Highlighter_Settings::SETTING . '" urvanov-syntax-highlighter-preview="' . ( $preview ? 1 : 0 ) . '">';
		foreach ( $resources as $k => $v ) {
			if ( is_array( $v ) && count( $v ) ) {
				$data = $v[0];
				$text = $v[1];
			} else {
				$text = $v;
			}

			$is_selected = null !== $selected && $k === $selected ? 'selected' : selected( self::$options[ $id ], $k, false );
			$return     .= '<option ' . ( isset( $data ) ? 'data-value="' . $data . '"' : '' ) . ' value="' . $k . '" ' . $is_selected . '>' . $text . '</option>';
		}
		$return .= '</select>' . ( $line_break ? URVANOV_SYNTAX_HIGHLIGHTER_BR : '' );
		if ( $echo ) {
			echo $return; // phpcs:ignore WordPress.Security.EscapeOutput
		} else {
			return $return;
		}
	}

	/**
	 * Button.
	 *
	 * @param array $args Args.
	 *
	 * @return string
	 */
	private static function button( $args = array() ): string {

		// phpcs:ignore WordPress.PHP.DontExtract
		extract( $args );

		UrvanovSyntaxHighlighterUtil::set_var( $id, '' );
		UrvanovSyntaxHighlighterUtil::set_var( $class, '' );
		UrvanovSyntaxHighlighterUtil::set_var( $onclick, '' );
		UrvanovSyntaxHighlighterUtil::set_var( $title, '' );

		return '<a id="' . esc_attr( $id ) . '" class="button-primary ' . esc_attr( $class ) . '" onclick="' . $onclick . '">' . wp_kses_post( $title ) . '</a>';
	}

	/**
	 * Info Span.
	 *
	 * @param string $name Name.
	 * @param string $text Text.
	 */
	private static function info_span( string $name, string $text ) {
		echo '<span id="', esc_attr( $name ), '-info">', wp_kses_post( $text ), '</span>';
	}

	/**
	 * Span.
	 *
	 * @param string $text Text.
	 */
	private static function span( string $text ) {
		echo '<span>', wp_kses_post( $text ), '</span>';
	}

	/**
	 * Help.
	 */
	public static function help() {
		global $urvanov_syntax_highlighter_website, $urvanov_syntax_highlighter_twitter, $urvanov_syntax_highlighter_git, $urvanov_syntax_highlighter_plugin_wp, $urvanov_syntax_highlighter_donate;

		if ( Urvanov_Syntax_Highlighter_Global_Settings::val( Urvanov_Syntax_Highlighter_Settings::HIDE_HELP ) ) {
			return;
		}

		echo '<div id="urvanov-syntax-highlighter-help" class="updated settings-error urvanov-syntax-highlighter-help">
				<p><strong>Howdy, coder!</strong> Thanks for using Crayon. <strong>Useful Links:</strong> <a href="' . esc_url( $urvanov_syntax_highlighter_website ) . '" target="_blank">Documentation</a>, <a href="' . esc_url( $urvanov_syntax_highlighter_git ) . '" target="_blank">GitHub</a>, <a href="' . esc_url( $urvanov_syntax_highlighter_plugin_wp ) . '" target="_blank">Plugin Page</a>, <a href="' . esc_url( $urvanov_syntax_highlighter_twitter ) . '" target="_blank">Twitter</a>. Crayon has always been free. If you value my work please consider a <a href="' . esc_url( $urvanov_syntax_highlighter_donate ) . '">small donation</a> to show your appreciation. Thanks! <a class="urvanov-syntax-highlighter-help-close">X</a></p></div>
						';
	}

	/**
	 * Help screen.
	 */
	public static function help_screen() {
		$screen = get_current_screen();

		if ( $screen->id !== self::$admin_page ) {
			exit;
		}
	}

	/**
	 * Metrics.
	 */
	public static function metrics() {
		echo '<div id="urvanov-syntax-highlighter-section-metrics" class="urvanov-syntax-highlighter-hide-inline">';
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::HEIGHT_SET,
				'<span class="urvanov-syntax-highlighter-span-50">' . esc_html__( 'Height', 'urvanov-syntax-highlighter' ) . ' </span>',
			),
			false
		);
		self::dropdown( Urvanov_Syntax_Highlighter_Settings::HEIGHT_MODE, false );
		echo ' ';
		self::input(
			array(
				'id'   => Urvanov_Syntax_Highlighter_Settings::HEIGHT,
				'size' => 8,
			)
		);
		echo ' ';
		self::dropdown( Urvanov_Syntax_Highlighter_Settings::HEIGHT_UNIT );
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::WIDTH_SET,
				'<span class="urvanov-syntax-highlighter-span-50">' . esc_html__( 'Width', 'urvanov-syntax-highlighter' ) . ' </span>',
			),
			false
		);
		self::dropdown( Urvanov_Syntax_Highlighter_Settings::WIDTH_MODE, false );
		echo ' ';
		self::input(
			array(
				'id'   => Urvanov_Syntax_Highlighter_Settings::WIDTH,
				'size' => 8,
			)
		);
		echo ' ';
		self::dropdown( Urvanov_Syntax_Highlighter_Settings::WIDTH_UNIT );
		$text = array(
			esc_html__( 'Top Margin', 'urvanov-syntax-highlighter' )    => array(
				Urvanov_Syntax_Highlighter_Settings::TOP_SET,
				Urvanov_Syntax_Highlighter_Settings::TOP_MARGIN,
			),
			esc_html__( 'Bottom Margin', 'urvanov-syntax-highlighter' ) => array(
				Urvanov_Syntax_Highlighter_Settings::BOTTOM_SET,
				Urvanov_Syntax_Highlighter_Settings::BOTTOM_MARGIN,
			),
			esc_html__( 'Left Margin', 'urvanov-syntax-highlighter' )   => array(
				Urvanov_Syntax_Highlighter_Settings::LEFT_SET,
				Urvanov_Syntax_Highlighter_Settings::LEFT_MARGIN,
			),
			esc_html__( 'Right Margin', 'urvanov-syntax-highlighter' )  => array(
				Urvanov_Syntax_Highlighter_Settings::RIGHT_SET,
				Urvanov_Syntax_Highlighter_Settings::RIGHT_MARGIN,
			),
		);
		foreach ( $text as $p => $s ) {
			$set     = $s[0];
			$margin  = $s[1];
			$preview = ( esc_html__( 'Left Margin', 'urvanov-syntax-highlighter' ) === $p || esc_html__( 'Right Margin', 'urvanov-syntax-highlighter' ) === $p );
			self::checkbox( array( $set, '<span class="urvanov-syntax-highlighter-span-110">' . $p . '</span>' ), false, $preview );
			echo ' ';
			self::input(
				array(
					'id'      => $margin,
					'size'    => 8,
					'preview' => false,
				)
			);
			echo '<span class="urvanov-syntax-highlighter-span-margin">', esc_html__( 'Pixels', 'urvanov-syntax-highlighter' ), '</span>', URVANOV_SYNTAX_HIGHLIGHTER_BR; // phpcs:ignore WordPress.Security.EscapeOutput
		}

		echo '<span class="urvanov-syntax-highlighter-span" style="min-width: 135px;">' . esc_html__( 'Horizontal Alignment', 'urvanov-syntax-highlighter' ) . ' </span>';
		self::dropdown( Urvanov_Syntax_Highlighter_Settings::H_ALIGN );
		echo '<div id="urvanov-syntax-highlighter-subsection-float">';
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::FLOAT_ENABLE,
				esc_html__( 'Allow floating elements to surround Crayon', 'urvanov-syntax-highlighter' ),
			),
			false,
			false
		);
		echo '</div>';
		echo '<span class="urvanov-syntax-highlighter-span-100">' . esc_html__( 'Inline Margin', 'urvanov-syntax-highlighter' ) . ' </span>';
		self::input(
			array(
				'id'   => Urvanov_Syntax_Highlighter_Settings::INLINE_MARGIN,
				'size' => 2,
			)
		);
		echo '<span class="urvanov-syntax-highlighter-span-margin">', esc_html__( 'Pixels', 'urvanov-syntax-highlighter' ), '</span>';
		echo '</div>';
	}

	/**
	 * Toolbar.
	 */
	public static function toolbar() {
		echo '<div id="urvanov-syntax-highlighter-section-toolbar" class="urvanov-syntax-highlighter-hide-inline">';
		self::span( esc_html__( 'Display the Toolbar', 'urvanov-syntax-highlighter' ) . ' ' );
		self::dropdown( Urvanov_Syntax_Highlighter_Settings::TOOLBAR );
		echo '<div id="urvanov-syntax-highlighter-subsection-toolbar">';
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::TOOLBAR_OVERLAY,
				esc_html__( 'Overlay the toolbar on code rather than push it down when possible', 'urvanov-syntax-highlighter' ),
			)
		);
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::TOOLBAR_HIDE,
				esc_html__( 'Toggle the toolbar on single click when it is overlayed', 'urvanov-syntax-highlighter' ),
			)
		);
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::TOOLBAR_DELAY,
				esc_html__( 'Delay hiding the toolbar on MouseOut', 'urvanov-syntax-highlighter' ),
			)
		);
		echo '</div>';
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::SHOW_TITLE,
				esc_html__( 'Display the title when provided', 'urvanov-syntax-highlighter' ),
			)
		);
		self::span( esc_html__( 'Display the language', 'urvanov-syntax-highlighter' ) . ' ' );
		self::dropdown( Urvanov_Syntax_Highlighter_Settings::SHOW_LANG );
		echo '</div>';
	}

	/**
	 * Lines.
	 */
	public static function lines() {
		echo '<div id="urvanov-syntax-highlighter-section-lines" class="urvanov-syntax-highlighter-hide-inline">';
		self::checkbox( array( Urvanov_Syntax_Highlighter_Settings::STRIPED, esc_html__( 'Display striped code lines', 'urvanov-syntax-highlighter' ) ) );
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::MARKING,
				esc_html__( 'Enable line marking for important lines', 'urvanov-syntax-highlighter' ),
			)
		);
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::RANGES,
				esc_html__( 'Enable line ranges for showing only parts of code', 'urvanov-syntax-highlighter' ),
			)
		);
		self::checkbox( array( Urvanov_Syntax_Highlighter_Settings::NUMS, esc_html__( 'Display line numbers by default', 'urvanov-syntax-highlighter' ) ) );
		self::checkbox( array( Urvanov_Syntax_Highlighter_Settings::NUMS_TOGGLE, esc_html__( 'Enable line number toggling', 'urvanov-syntax-highlighter' ) ) );
		self::checkbox( array( Urvanov_Syntax_Highlighter_Settings::WRAP, esc_html__( 'Wrap lines by default', 'urvanov-syntax-highlighter' ) ) );
		self::checkbox( array( Urvanov_Syntax_Highlighter_Settings::WRAP_TOGGLE, esc_html__( 'Enable line wrap toggling', 'urvanov-syntax-highlighter' ) ) );
		self::span( esc_html__( 'Start line numbers from', 'urvanov-syntax-highlighter' ) . ' ' );
		self::input(
			array(
				'id'    => Urvanov_Syntax_Highlighter_Settings::START_LINE,
				'size'  => 2,
				'break' => true,
			)
		);
		echo '</div>';
	}

	/**
	 * Languages.
	 */
	public static function langs() {
		echo '<a id="langs"></a>';

		// Specialised dropdown for languages.
		if ( array_key_exists( Urvanov_Syntax_Highlighter_Settings::FALLBACK_LANG, self::$options ) ) {
			$langs = Urvanov_Syntax_Highlighter_Parser::parse_all();
			if ( false !== $langs ) {
				$langs = Urvanov_Syntax_Highlighter_Langs::sort_by_name( $langs );
				self::span( esc_html__( 'When no language is provided, use the fallback', 'urvanov-syntax-highlighter' ) . ': ' );
				self::dropdown( Urvanov_Syntax_Highlighter_Settings::FALLBACK_LANG, false, true, true, $langs );

				// Information about parsing.
				$parsed = Urvanov_Syntax_Highlighter_Resources::langs()->is_parsed();
				$count  = count( $langs );
				echo '</select>', URVANOV_SYNTAX_HIGHLIGHTER_BR, ( $parsed ? '' : '<span class="urvanov-syntax-highlighter-error">' ), // phpcs:ignore WordPress.Security.EscapeOutput

				// translators: %d = count.
				sprintf( esc_html( _n( '%d language has been detected.', '%d languages have been detected.', intval( $count ), 'urvanov-syntax-highlighter' ) ), intval( $count ) ), ' ',
				( $parsed ? esc_html__( 'Parsing was successful', 'urvanov-syntax-highlighter' ) : esc_html__( 'Parsing was unsuccessful', 'urvanov-syntax-highlighter' ) ), // phpcs:ignore
				( $parsed ? '. ' : '</span>' );

				// Check if fallback from db is loaded.
				$db_fallback = self::$options[ Urvanov_Syntax_Highlighter_Settings::FALLBACK_LANG ]; // Fallback name from db.

				if ( ! Urvanov_Syntax_Highlighter_Resources::langs()->is_loaded( $db_fallback ) || ! Urvanov_Syntax_Highlighter_Resources::langs()->exists( $db_fallback ) ) {
					// translators: %s = Backup lang.
					echo '<br/><span class="urvanov-syntax-highlighter-error">', sprintf( esc_html__( 'The selected language with id %s could not be loaded', 'urvanov-syntax-highlighter' ), '<strong>' . esc_html( $db_fallback ) . '</strong>' ), '. </span>';
				}

				// Language parsing info.
				// phpcs:ignore WordPress.Security.EscapeOutput
				echo URVANOV_SYNTAX_HIGHLIGHTER_BR, '<div id="urvanov-syntax-highlighter-subsection-langs-info"><div>' . self::button(
					array(
						'id'    => 'show-langs',
						'title' => esc_html__( 'Show Languages', 'urvanov-syntax-highlighter' ),
					)
				) . '</div></div>';
			} else {
				echo esc_html__( 'No languages could be parsed.', 'urvanov-syntax-highlighter' );
			}
		}
	}

	/**
	 * Show langs.
	 */
	public static function show_langs() {
		self::load_settings();

		require_once URVANOV_SYNTAX_HIGHLIGHTER_PARSER_PHP;

		$langs = Urvanov_Syntax_Highlighter_Parser::parse_all();
		if ( false !== $langs ) {
			$langs = Urvanov_Syntax_Highlighter_Langs::sort_by_name( $langs );
			echo '<table class="urvanov-syntax-highlighter-table" style="padding:0;border-spacing:0;"><tr class="urvanov-syntax-highlighter-table-header">',
			'<td>', esc_html__( 'ID', 'urvanov-syntax-highlighter' ), '</td><td>', esc_html__( 'Name', 'urvanov-syntax-highlighter' ), '</td><td>', esc_html__( 'Version', 'urvanov-syntax-highlighter' ), '</td><td>', esc_html__( 'File Extensions', 'urvanov-syntax-highlighter' ), '</td><td>', esc_html__( 'Aliases', 'urvanov-syntax-highlighter' ), '</td><td>', esc_html__( 'State', 'urvanov-syntax-highlighter' ), '</td></tr>';
			$keys = array_values( $langs );

			$count = count( $langs );
			for ( $i = 0; $i < $count; $i ++ ) {
				$lang    = $keys[ $i ];
				$count_2 = count( $langs );
				$tr      = ( $count_2 - 1 === $i ) ? 'urvanov-syntax-highlighter-table-last' : '';
				echo '<tr class="', esc_attr( $tr ), '">',
				'<td>', esc_html( $lang->id() ), '</td>',
				'<td>', esc_html( $lang->name() ), '</td>',
				'<td>', esc_html( $lang->version() ), '</td>',
				'<td>', implode( ', ', $lang->ext() ), '</td>', // phpcs:ignore WordPress.Security.EscapeOutput
				'<td>', implode( ', ', $lang->alias() ), '</td>', // phpcs:ignore WordPress.Security.EscapeOutput
				'<td class="', esc_html( strtolower( UrvanovSyntaxHighlighterUtil::space_to_hyphen( $lang->state_info() ) ) ), '">',
				esc_html( $lang->state_info() ), '</td>',
				'</tr>';
			}

			echo '</table><br/>' . esc_html__( "Languages that have the same extension as their name don't need to explicitly map extensions.", 'urvanov-syntax-highlighter' );
		} else {
			echo esc_html__( 'No languages could be found.', 'urvanov-syntax-highlighter' );
		}
		exit();
	}

	/**
	 * Posts.
	 */
	public static function posts() {
		echo '<a id="posts"></a>';

		// phpcs:ignore WordPress.Security.EscapeOutput
		echo self::button(
			array(
				'id'    => 'show-posts',
				'title' => esc_html__(
					'Show Crayon Posts',
					'urvanov-syntax-highlighter'
				),
			)
		);

		echo ' <input type="submit" name="', esc_attr( self::OPTIONS ), '[refresh_tags]" id="refresh_tags" class="button-primary" value="', esc_html__( 'Refresh', 'urvanov-syntax-highlighter' ), '" />';
		echo wp_kses_post( self::help_button( 'http://aramk.com/blog/2012/09/26/internal-post-management-crayon/' ) );
		echo '<div id="urvanov-syntax-highlighter-subsection-posts-info"></div>';
	}

	/**
	 * Post Compare.
	 *
	 * @param object $a Who.
	 * @param object $b Knows.
	 *
	 * @return int
	 */
	public static function post_cmp( $a = null, $b = null ): int {
		$a = $a->post_modified;
		$b = $b->post_modified;
		if ( $a === $b ) {
			return 0;
		} else {
			return $a < $b ? 1 : - 1;
		}
	}

	/**
	 * Show posts.
	 */
	public static function show_posts() {
		self::load_settings();

		$post_ids     = self::load_posts();
		$legacy_posts = self::load_legacy_posts();

		// Avoids O(n^2) by using a hash map, tradeoff in using strval.
		$legacy_map = array();
		foreach ( $legacy_posts as $legacy_id ) {
			$legacy_map[ strval( $legacy_id ) ] = true;
		}

		echo '<table class="urvanov-syntax-highlighter-table" style="padding:0;border-spacing:0;"><tr class="urvanov-syntax-highlighter-table-header">',
		'<td>', esc_html__( 'ID', 'urvanov-syntax-highlighter' ), '</td><td>', esc_html__( 'Title', 'urvanov-syntax-highlighter' ), '</td><td>', esc_html__( 'Posted', 'urvanov-syntax-highlighter' ), '</td><td>', esc_html__( 'Modifed', 'urvanov-syntax-highlighter' ), '</td><td>', esc_html__( 'Contains Legacy Tags?', 'urvanov-syntax-highlighter' ), '</td></tr>';

		$posts = array();

		$count = count( $post_ids );
		for ( $i = 0; $i < $count; $i ++ ) {
			$posts[ $i ] = get_post( $post_ids[ $i ] );
		}

		usort( $posts, 'Urvanov_Syntax_Highlighter_Settings_WP::post_cmp' );

		$count = count( $posts );
		for ( $i = 0; $i < $count; $i ++ ) {
			$post    = $posts[ $i ];
			$post_id = $post->ID;
			$title   = $post->post_title;
			$title   = ! empty( $title ) ? $title : 'N/A';
			$tr      = ( count( $posts ) - 1 === $i ) ? 'urvanov-syntax-highlighter-table-last' : '';

			echo '<tr class="', esc_attr( $tr ), '">',
			'<td>', esc_html( $post_id ), '</td>',
			'<td><a href="', esc_url( $post->guid ), '" target="_blank">', wp_kses_post( $title ), '</a></td>',
			'<td>', esc_html( $post->post_date ), '</td>',
			'<td>', esc_html( $post->post_modified ), '</td>',
			'<td>', isset( $legacy_map[ strval( $post_id ) ] ) ? '<span style="color: red;">' . esc_html__( 'Yes', 'urvanov-syntax-highlighter' ) . '</a>' : esc_html__( 'No', 'urvanov-syntax-highlighter' ), '</td>',
			'</tr>';
		}

		echo '</table>';
		exit();
	}

	/**
	 * Show preview.
	 */
	public static function show_preview() {
		echo '<div id="content">';

		self::load_settings(); // Run first to ensure global settings loaded.

		$urvanov_syntax_highlighter = Urvanov_Syntax_Highlighter_Plugin::instance();

		// Settings to prevent from validating.
		$preview_settings = array( self::SAMPLE_CODE );

		// Load settings from GET and validate.
		foreach ( $_POST as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification
			$value = stripslashes( sanitize_text_field( $value ) );
			if ( ! in_array( $key, $preview_settings, true ) ) {
				$_POST[ $key ] = Urvanov_Syntax_Highlighter_Settings::validate( $key, $value );
			} else {
				$_POST[ $key ] = $value;
			}
		}

		$urvanov_syntax_highlighter->settings( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! isset( $urvanov_syntax_highlighter_preview_dont_override_get ) || ! $urvanov_syntax_highlighter_preview_dont_override_get ) {
			$settings = array(
				Urvanov_Syntax_Highlighter_Settings::TOP_SET       => true,
				Urvanov_Syntax_Highlighter_Settings::TOP_MARGIN    => 10,
				Urvanov_Syntax_Highlighter_Settings::BOTTOM_SET    => false,
				Urvanov_Syntax_Highlighter_Settings::BOTTOM_MARGIN => 0,
			);
			$urvanov_syntax_highlighter->settings( $settings );
		}

		// Print the theme CSS.
		$theme_id = $urvanov_syntax_highlighter->setting_val( Urvanov_Syntax_Highlighter_Settings::THEME );

		if ( null !== $theme_id ) {
			echo Urvanov_Syntax_Highlighter_Resources::themes()->get_css( $theme_id, gmdate( 'U' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
		}

		$font_id = $urvanov_syntax_highlighter->setting_val( Urvanov_Syntax_Highlighter_Settings::FONT );
		if ( null !== $font_id ) {
			echo Urvanov_Syntax_Highlighter_Resources::fonts()->get_css( $font_id ); // phpcs:ignore WordPress.Security.EscapeOutput
		}

		// Load custom code based on language.
		$lang = $urvanov_syntax_highlighter->setting_val( Urvanov_Syntax_Highlighter_Settings::FALLBACK_LANG );
		$path = Urvanov_Syntax_Highlighter_Global_Settings::plugin_path() . URVANOV_SYNTAX_HIGHLIGHTER_UTIL_DIR . '/sample/' . $lang . '.txt';

		if ( isset( $_POST[ self::SAMPLE_CODE ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$urvanov_syntax_highlighter->code( sanitize_text_field( wp_unslash( $_POST[ self::SAMPLE_CODE ] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
		} elseif ( $lang && file_exists( $path ) ) {
			$urvanov_syntax_highlighter->url( $path );
		} else {
			$code = "
// A sample class
class Human {
	private int age = 0;
	public void birthday() {
		age++;
		print('Happy Birthday!');
	}
}
";
			$urvanov_syntax_highlighter->code( $code );
		}
		$urvanov_syntax_highlighter->title( 'Sample Code' );
		$urvanov_syntax_highlighter->marked( '5-7' );
		$urvanov_syntax_highlighter->output(
			$highlight = true, // phpcs:ignore
			$nums      = true,
			$print     = true
		);
		echo '</div>';
		Urvanov_Syntax_Highlighter_Global::load_plugin_textdomain();

		exit();
	}

	/**
	 * Theme.
	 *
	 * @param bool $editor Editor.
	 */
	public static function theme( $editor = false ) {
		$db_theme = self::$options[ Urvanov_Syntax_Highlighter_Settings::THEME ]; // Theme name from db.
		if ( ! array_key_exists( Urvanov_Syntax_Highlighter_Settings::THEME, self::$options ) ) {
			$db_theme = '';
		}

		$themes_array = Urvanov_Syntax_Highlighter_Resources::themes()->get_array();

		// Mark user themes.
		foreach ( $themes_array as $id => $name ) {
			$mark                = Urvanov_Syntax_Highlighter_Resources::themes()->get( $id )->user() ? ' *' : '';
			$themes_array[ $id ] = array( $name, $name . $mark );
		}

		$missing_theme = ! Urvanov_Syntax_Highlighter_Resources::themes()->is_loaded( $db_theme ) || ! Urvanov_Syntax_Highlighter_Resources::themes()->exists( $db_theme );
		self::dropdown( Urvanov_Syntax_Highlighter_Settings::THEME, false, false, true, $themes_array, $missing_theme ? Urvanov_Syntax_Highlighter_Themes::DEFAULT_THEME : null );

		if ( $editor ) {
			return;
		}

		// Theme editor.
		if ( URVANOV_SYNTAX_HIGHLIGHTER_THEME_EDITOR ) {
			echo '<div id="urvanov-syntax-highlighter-theme-editor-admin-buttons">';

			$buttons = array(
				'edit'      => esc_html__( 'Edit', 'urvanov-syntax-highlighter' ),
				'duplicate' => esc_html__( 'Duplicate', 'urvanov-syntax-highlighter' ),
				'submit'    => esc_html__( 'Submit', 'urvanov-syntax-highlighter' ),
				'delete'    => esc_html__( 'Delete', 'urvanov-syntax-highlighter' ),
			);
			foreach ( $buttons as $k => $v ) {
				echo '<a id="urvanov-syntax-highlighter-theme-editor-', esc_attr( $k ), '-button" data-nonce="' . esc_attr( wp_create_nonce( 'theme_editor_action' ) ) . '" class="button-secondary urvanov-syntax-highlighter-admin-button" loading="', esc_html__( 'Loading...', 'urvanov-syntax-highlighter' ), '" loaded="', esc_attr( $v ), '" >', esc_html( $v ), '</a>';
			}

			echo '<span class="urvanov-syntax-highlighter-span-5"></span>', wp_kses_post( self::help_button( 'http://aramk.com/blog/2012/12/27/urvanov-syntax-highlighter-theme-editor/' ) ), '<span class="urvanov-syntax-highlighter-span-5"></span>', esc_html__( 'Duplicate a Stock Theme into a User Theme to allow editing.', 'urvanov-syntax-highlighter' );
			echo '</br></div>';
		}

		// Preview Box.
		?>
		<div id="urvanov-syntax-highlighter-theme-panel">
			<div id="urvanov-syntax-highlighter-theme-info"></div>
			<div id="urvanov-syntax-highlighter-live-preview-wrapper">
				<div id="urvanov-syntax-highlighter-live-preview-inner">
					<div id="urvanov-syntax-highlighter-live-preview"></div>
					<div id="urvanov-syntax-highlighter-preview-info">
						<?php // translators: %s = HTML. ?>
						<?php printf( esc_html__( 'Change the %1$sfallback language%2$s to change the sample code or %3$schange it manually%4$s. Lines 5-7 are marked.', 'urvanov-syntax-highlighter' ), '<a href="#langs">', '</a>', '<a id="urvanov-syntax-highlighter-change-code" href="#">', '</a>' ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php

		// Preview checkbox.
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::PREVIEW,
				esc_html__( 'Enable Live Preview', 'urvanov-syntax-highlighter' ),
			),
			false,
			false
		);
		echo '</select><span class="urvanov-syntax-highlighter-span-10"></span>';
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::ENQUEUE_THEMES,
				esc_html__( 'Enqueue themes in the header (more efficient).', 'urvanov-syntax-highlighter' ) . self::help_button( 'http://aramk.com/blog/2012/01/07/enqueuing-themes-and-fonts-in-crayon/' ),
			)
		);

		// Check if theme from db is loaded.
		if ( $missing_theme ) {
			// translators: %s = Theme.
			echo '<span class="urvanov-syntax-highlighter-error">', sprintf( esc_html__( 'The selected theme with id %s could not be loaded', 'urvanov-syntax-highlighter' ), '<strong>' . esc_html( $db_theme ) . '</strong>' ), '. </span>';
		}
	}

	/**
	 * Font.
	 *
	 * @param bool $editor Editor.
	 */
	public static function font( $editor = false ) {
		$db_font = self::$options[ Urvanov_Syntax_Highlighter_Settings::FONT ]; // Theme name from db.
		if ( ! array_key_exists( Urvanov_Syntax_Highlighter_Settings::FONT, self::$options ) ) {
			$db_font = '';
		}
		$fonts_array = Urvanov_Syntax_Highlighter_Resources::fonts()->get_array();
		self::dropdown( Urvanov_Syntax_Highlighter_Settings::FONT, false, true, true, $fonts_array );
		echo '<span class="urvanov-syntax-highlighter-span-5"></span>';
		echo '<span class="urvanov-syntax-highlighter-span-10"></span>';
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::FONT_SIZE_ENABLE,
				esc_html__( 'Custom Font Size', 'urvanov-syntax-highlighter' ) . ' ',
			),
			false
		);
		self::input(
			array(
				'id'   => Urvanov_Syntax_Highlighter_Settings::FONT_SIZE,
				'size' => 2,
			)
		);
		echo '<span class="urvanov-syntax-highlighter-span-margin">', esc_html__( 'Pixels', 'urvanov-syntax-highlighter' ), ',&nbsp;&nbsp;', esc_html__( 'Line Height', 'urvanov-syntax-highlighter' ), ' </span>';
		self::input(
			array(
				'id'   => Urvanov_Syntax_Highlighter_Settings::LINE_HEIGHT,
				'size' => 2,
			)
		);
		echo '<span class="urvanov-syntax-highlighter-span-margin">', esc_html__( 'Pixels', 'urvanov-syntax-highlighter' ), '</span></br>';
		if ( ( ! Urvanov_Syntax_Highlighter_Resources::fonts()->is_loaded( $db_font ) || ! Urvanov_Syntax_Highlighter_Resources::fonts()->exists( $db_font ) ) ) {

			// Default font doesn't actually exist as a file, it means do not override default theme font.
			// translators: %s = Theme.
			echo '<span class="urvanov-syntax-highlighter-error">', sprintf( esc_html__( 'The selected font with id %s could not be loaded', 'urvanov-syntax-highlighter' ), '<strong>' . esc_html( $db_font ) . '</strong>' ), '. </span><br/>';
		}

		if ( $editor ) {
			return;
		}

		echo '<div style="height:10px;"></div>';
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::ENQUEUE_FONTS,
				esc_html__( 'Enqueue fonts in the header (more efficient).', 'urvanov-syntax-highlighter' ) . self::help_button( 'http://aramk.com/blog/2012/01/07/enqueuing-themes-and-fonts-in-crayon/' ),
			)
		);
	}

	/**
	 * Code.
	 *
	 * @param bool $editor Editor.
	 */
	public static function code( $editor = false ) {
		echo '<div id="urvanov-syntax-highlighter-section-code-interaction" class="urvanov-syntax-highlighter-hide-inline-only">';
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::PLAIN,
				esc_html__( 'Enable plain code view and display', 'urvanov-syntax-highlighter' ) . ' ',
			),
			false
		);
		self::dropdown( Urvanov_Syntax_Highlighter_Settings::SHOW_PLAIN );
		echo '<span id="urvanov-syntax-highlighter-subsection-copy-check">';
		self::checkbox( array( Urvanov_Syntax_Highlighter_Settings::PLAIN_TOGGLE, esc_html__( 'Enable plain code toggling', 'urvanov-syntax-highlighter' ) ) );
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::SHOW_PLAIN_DEFAULT,
				esc_html__( 'Show the plain code by default', 'urvanov-syntax-highlighter' ),
			)
		);
		self::checkbox( array( Urvanov_Syntax_Highlighter_Settings::COPY, esc_html__( 'Enable code copy/paste', 'urvanov-syntax-highlighter' ) ) );
		echo '</span>';
		self::checkbox( array( Urvanov_Syntax_Highlighter_Settings::POPUP, esc_html__( 'Enable opening code in a window', 'urvanov-syntax-highlighter' ) ) );
		self::checkbox( array( Urvanov_Syntax_Highlighter_Settings::SCROLL, esc_html__( 'Always display scrollbars', 'urvanov-syntax-highlighter' ) ) );
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::MINIMIZE,
				esc_html__( 'Minimize code', 'urvanov-syntax-highlighter' ) . self::help_button( 'http://aramk.com/blog/2013/01/15/minimizing-code-in-crayon/' ),
			)
		);
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::EXPAND,
				esc_html__( 'Expand code beyond page borders on mouseover', 'urvanov-syntax-highlighter' ),
			)
		);
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::EXPAND_TOGGLE,
				esc_html__( 'Enable code expanding toggling when possible', 'urvanov-syntax-highlighter' ),
			)
		);
		echo '</div>';
		if ( ! $editor ) {
			self::checkbox( array( Urvanov_Syntax_Highlighter_Settings::DECODE, esc_html__( 'Decode HTML entities in code', 'urvanov-syntax-highlighter' ) ) );
		}
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::DECODE_ATTRIBUTES,
				esc_html__( 'Decode HTML entities in attributes', 'urvanov-syntax-highlighter' ),
			)
		);
		echo '<div class="urvanov-syntax-highlighter-hide-inline-only">';
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::TRIM_WHITESPACE,
				esc_html__( 'Remove whitespace surrounding the shortcode content', 'urvanov-syntax-highlighter' ),
			)
		);
		echo '</div>';
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::TRIM_CODE_TAG,
				esc_html__( 'Remove &lt;code&gt; tags surrounding the shortcode content', 'urvanov-syntax-highlighter' ),
			)
		);
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::ALTERNATE,
				esc_html__( 'Allow Mixed Language Highlighting with delimiters and tags.', 'urvanov-syntax-highlighter' ) . self::help_button( 'http://aramk.com/blog/2011/12/25/mixed-language-highlighting-in-crayon/' ),
			)
		);
		echo '<div class="urvanov-syntax-highlighter-hide-inline-only">';
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::SHOW_ALTERNATE,
				esc_html__( 'Show Mixed Language Icon (+)', 'urvanov-syntax-highlighter' ),
			)
		);
		echo '</div>';
		self::checkbox( array( Urvanov_Syntax_Highlighter_Settings::TAB_CONVERT, esc_html__( 'Convert tabs to spaces', 'urvanov-syntax-highlighter' ) ) );
		self::span( esc_html__( 'Tab size in spaces', 'urvanov-syntax-highlighter' ) . ': ' );
		self::input(
			array(
				'id'    => Urvanov_Syntax_Highlighter_Settings::TAB_SIZE,
				'size'  => 2,
				'break' => true,
			)
		);
		self::span( esc_html__( 'Blank lines before code:', 'urvanov-syntax-highlighter' ) . ' ' );
		self::input(
			array(
				'id'    => Urvanov_Syntax_Highlighter_Settings::WHITESPACE_BEFORE,
				'size'  => 2,
				'break' => true,
			)
		);
		self::span( esc_html__( 'Blank lines after code:', 'urvanov-syntax-highlighter' ) . ' ' );
		self::input(
			array(
				'id'    => Urvanov_Syntax_Highlighter_Settings::WHITESPACE_AFTER,
				'size'  => 2,
				'break' => true,
			)
		);
	}

	/**
	 * Tags.
	 */
	public static function tags() {
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::INLINE_TAG,
				esc_html__( 'Capture Inline Tags', 'urvanov-syntax-highlighter' ) . self::help_button( 'http://aramk.com/blog/2012/03/07/inline-crayons/' ),
			)
		);
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::INLINE_WRAP,
				esc_html__( 'Wrap Inline Tags', 'urvanov-syntax-highlighter' ) . self::help_button( 'http://aramk.com/blog/2012/03/07/inline-crayons/' ),
			)
		);
		self::checkbox( array( Urvanov_Syntax_Highlighter_Settings::CODE_TAG_CAPTURE, esc_html__( 'Capture &lt;code&gt; as' ) ), false );
		echo ' ';
		self::dropdown( Urvanov_Syntax_Highlighter_Settings::CODE_TAG_CAPTURE_TYPE, false );
		echo wp_kses_post( self::help_button( 'http://aramk.com/blog/2012/03/07/inline-crayons/' ) ) . '<br/>';
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::BACKQUOTE,
				esc_html__( 'Capture `backquotes` as &lt;code&gt;', 'urvanov-syntax-highlighter' ) . self::help_button( 'http://aramk.com/blog/2012/03/07/inline-crayons/' ),
			)
		);
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::CAPTURE_PRE,
				esc_html__( 'Capture &lt;pre&gt; tags as Crayons', 'urvanov-syntax-highlighter' ) . self::help_button( 'http://aramk.com/blog/2011/12/27/mini-tags-in-crayon/' ),
			)
		);

		// translators: %s = HTML.
		echo '<div class="note" style="width: 350px;">', sprintf( esc_html__( 'Using this markup for Mini Tags and Inline tags is now %1$sdeprecated%2$s! Use the %3$sTag Editor%4$s instead and convert legacy tags.', 'urvanov-syntax-highlighter' ), '<a href="http://aramk.com/blog/2011/12/27/mini-tags-in-crayon/" target="_blank">', '</a>', '<a href="http://aramk.com/blog/2012/03/25/urvanov-syntax-highlighter-tag-editor/" target="_blank">', '</a>' ), '</div>';
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::CAPTURE_MINI_TAG,
				esc_html__( 'Capture Mini Tags like [php][/php] as Crayons.', 'urvanov-syntax-highlighter' ) . self::help_button( 'http://aramk.com/blog/2011/12/27/mini-tags-in-crayon/' ),
			)
		);
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::INLINE_TAG_CAPTURE,
				esc_html__( 'Capture Inline Tags like {php}{/php} inside sentences.', 'urvanov-syntax-highlighter' ) . self::help_button( 'http://aramk.com/blog/2012/03/07/inline-crayons/' ),
			)
		);
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::PLAIN_TAG,
				esc_html__( 'Enable [plain][/plain] tag.', 'urvanov-syntax-highlighter' ) . self::help_button( 'http://aramk.com/blog/2011/12/27/mini-tags-in-crayon/' ),
			)
		);
	}

	/**
	 * Files.
	 */
	public static function files() {
		echo '<a id="files"></a>';
		echo esc_html__( 'When loading local files and a relative path is given for the URL, use the absolute path', 'urvanov-syntax-highlighter' ), ': ',
		'<div style="margin-left: 20px">', esc_url( home_url() ), '/';
		self::input( array( 'id' => Urvanov_Syntax_Highlighter_Settings::LOCAL_PATH ) );
		echo '</div>', esc_html__( 'Followed by your relative URL.', 'urvanov-syntax-highlighter' );
	}

	/**
	 * Tag Editor.
	 */
	public static function tag_editor() {
		$can_convert = self::load_legacy_posts();

		if ( $can_convert ) {
			$disabled     = '';
			$convert_text = esc_html__( 'Convert Legacy Tags', 'urvanov-syntax-highlighter' );
		} else {
			$disabled     = 'disabled="disabled"';
			$convert_text = esc_html__( 'No Legacy Tags Found', 'urvanov-syntax-highlighter' );
		}

		echo '<input type="submit" name="', esc_attr( self::OPTIONS ), '[convert]" id="convert" class="button-primary" value="', esc_attr( $convert_text ), '"', $disabled, ' />&nbsp; '; // phpcs:ignore
		self::checkbox( array( 'convert_encode', esc_html__( 'Encode', 'urvanov-syntax-highlighter' ) ), false );
		echo wp_kses_post( self::help_button( 'http://aramk.com/blog/2012/09/26/converting-legacy-tags-to-pre/' ) ), URVANOV_SYNTAX_HIGHLIGHTER_BR, URVANOV_SYNTAX_HIGHLIGHTER_BR; // phpcs:ignore
		$sep = sprintf(
			// translators: %s = HTML.
			esc_html__( 'Use %s to separate setting names from values in the &lt;pre&gt; class attribute', 'urvanov-syntax-highlighter' ),
			self::dropdown( Urvanov_Syntax_Highlighter_Settings::ATTR_SEP, false, false, false )
		);

		echo '<span>', $sep, wp_kses_post( self::help_button( 'http://aramk.com/blog/2012/03/25/urvanov-syntax-highlighter-tag-editor/' ) ), '</span><br/>'; // phpcs:ignore

		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::TAG_EDITOR_FRONT,
				esc_html__( 'Display the Tag Editor in any TinyMCE instances on the frontend (e.g. bbPress)', 'urvanov-syntax-highlighter' ) . self::help_button( 'http://aramk.com/blog/2012/09/08/urvanov-syntax-highlighter-with-bbpress/' ),
			)
		);
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::TAG_EDITOR_SETTINGS,
				esc_html__( 'Display Tag Editor settings on the frontend', 'urvanov-syntax-highlighter' ),
			)
		);
		self::span( esc_html__( 'Add Code button text', 'urvanov-syntax-highlighter' ) . ' ' );
		self::input(
			array(
				'id'    => Urvanov_Syntax_Highlighter_Settings::TAG_EDITOR_ADD_BUTTON_TEXT,
				'break' => true,
			)
		);
		self::span( esc_html__( 'Edit Code button text', 'urvanov-syntax-highlighter' ) . ' ' );
		self::input(
			array(
				'id'    => Urvanov_Syntax_Highlighter_Settings::TAG_EDITOR_EDIT_BUTTON_TEXT,
				'break' => true,
			)
		);
		self::span( esc_html__( 'Quicktag button text', 'urvanov-syntax-highlighter' ) . ' ' );
		self::input(
			array(
				'id'    => Urvanov_Syntax_Highlighter_Settings::TAG_EDITOR_QUICKTAG_BUTTON_TEXT,
				'break' => true,
			)
		);
	}

	/**
	 * Misc.
	 */
	public static function misc() {
		echo esc_html__( 'Clear the cache used to store remote code requests', 'urvanov-syntax-highlighter' ), ': ';
		self::dropdown( Urvanov_Syntax_Highlighter_Settings::CACHE, false );
		echo '<input type="submit" id="', esc_attr( self::CACHE_CLEAR ), '" name="', esc_attr( self::CACHE_CLEAR ), '" class="button-secondary" value="', esc_html__( 'Clear Now', 'urvanov-syntax-highlighter' ), '" /><br/>';
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::EFFICIENT_ENQUEUE,
				esc_html__( 'Attempt to load Crayon\'s CSS and JavaScript only when needed', 'urvanov-syntax-highlighter' ) . self::help_button( 'http://aramk.com/blog/2012/01/23/failing-to-load-crayons-on-pages/' ),
			)
		);
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::SAFE_ENQUEUE,
				esc_html__( 'Disable enqueuing for page templates that may contain The Loop.', 'urvanov-syntax-highlighter' ) . self::help_button( 'http://aramk.com/blog/2012/01/23/failing-to-load-crayons-on-pages/' ),
			)
		);
		self::checkbox( array( Urvanov_Syntax_Highlighter_Settings::COMMENTS, esc_html__( 'Allow Crayons inside comments', 'urvanov-syntax-highlighter' ) ) );
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::EXCERPT_STRIP,
				esc_html__( 'Remove Crayons from excerpts', 'urvanov-syntax-highlighter' ),
			)
		);
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::MAIN_QUERY,
				esc_html__( 'Load Crayons only from the main Wordpress query', 'urvanov-syntax-highlighter' ),
			)
		);
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::TOUCHSCREEN,
				esc_html__( 'Disable mouse gestures for touchscreen devices (eg. MouseOver)', 'urvanov-syntax-highlighter' ),
			)
		);
		self::checkbox( array( Urvanov_Syntax_Highlighter_Settings::DISABLE_ANIM, esc_html__( 'Disable animations', 'urvanov-syntax-highlighter' ) ) );
		self::checkbox( array( Urvanov_Syntax_Highlighter_Settings::DISABLE_RUNTIME, esc_html__( 'Disable runtime stats', 'urvanov-syntax-highlighter' ) ) );
		echo '<span class="urvanov-syntax-highlighter-span-100">' . esc_html__( 'Disable for posts before', 'urvanov-syntax-highlighter' ) . ':</span> ';
		self::input(
			array(
				'id'    => Urvanov_Syntax_Highlighter_Settings::DISABLE_DATE,
				'type'  => 'date',
				'size'  => 8,
				'break' => false,
			)
		);
		echo '<br/>';
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::DELAY_LOAD_JS,
				esc_html__( 'Load scripts in the page footer using wp_footer() to improve loading performance.', 'urvanov-syntax-highlighter' ),
			)
		);
	}

	/**
	 * Errors.
	 */
	public static function errors() {
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::ERROR_LOG,
				esc_html__( 'Log errors for individual Crayons', 'urvanov-syntax-highlighter' ),
			)
		);
		self::checkbox( array( Urvanov_Syntax_Highlighter_Settings::ERROR_LOG_SYS, esc_html__( 'Log system-wide errors', 'urvanov-syntax-highlighter' ) ) );
		self::checkbox(
			array(
				Urvanov_Syntax_Highlighter_Settings::ERROR_MSG_SHOW,
				esc_html__( 'Display custom message for errors', 'urvanov-syntax-highlighter' ),
			)
		);
		self::input(
			array(
				'id'     => Urvanov_Syntax_Highlighter_Settings::ERROR_MSG,
				'size'   => 60,
				'margin' => true,
			)
		);
	}

	/**
	 * Log.
	 */
	public static function log() {
		$log = UrvanovSyntaxHighlighterLog::log();
		touch( URVANOV_SYNTAX_HIGHLIGHTER_LOG_FILE );
		$exists   = file_exists( URVANOV_SYNTAX_HIGHLIGHTER_LOG_FILE );
		$writable = is_writable( URVANOV_SYNTAX_HIGHLIGHTER_LOG_FILE );
		if ( ! empty( $log ) ) {
			echo '<div id="urvanov-syntax-highlighter-log-wrapper">', '<div id="urvanov-syntax-highlighter-log"><div id="urvanov-syntax-highlighter-log-text">', esc_html( $log ),
			'</div></div>', '<div id="urvanov-syntax-highlighter-log-controls">',
			'<input type="button" id="urvanov-syntax-highlighter-log-toggle" show_txt="', esc_html__( 'Show Log', 'urvanov-syntax-highlighter' ), '" hide_txt="', esc_html__( 'Hide Log', 'urvanov-syntax-highlighter' ), '" class="button-secondary" value="', esc_html__( 'Show Log', 'urvanov-syntax-highlighter' ), '"> ',
			'<input type="submit" id="urvanov-syntax-highlighter-log-clear" name="', esc_attr( self::LOG_CLEAR ),
			'" class="button-secondary" value="', esc_html__( 'Clear Log', 'urvanov-syntax-highlighter' ), '"> ', '<input type="submit" id="urvanov-syntax-highlighter-log-email" name="',
					esc_attr( self::LOG_EMAIL_ADMIN ) . '" class="button-secondary" value="', esc_html__( 'Email Admin', 'urvanov-syntax-highlighter' ), '"> ',
			'<input type="submit" id="urvanov-syntax-highlighter-log-email" name="', esc_attr( self::LOG_EMAIL_DEV ),
			'" class="button-secondary" value="', esc_html__( 'Email Developer', 'urvanov-syntax-highlighter' ), '"> ', '</div>', '</div>';
		}

		echo '<span', ( ! empty( $log ) ) ? ' class="urvanov-syntax-highlighter-span"' : '', '>', ( empty( $log ) ) ? esc_html__( 'The log is currently empty.', 'urvanov-syntax-highlighter' ) . ' ' : ''; // phpcs:ignore

		if ( $exists ) {
			$writable ? esc_html_e( 'The log file exists and is writable.', 'urvanov-syntax-highlighter' ) : esc_html_e( 'The log file exists and is not writable.', 'urvanov-syntax-highlighter' );
		} else {
			esc_html_e( 'The log file does not exist and is not writable.', 'urvanov-syntax-highlighter' );
		}
		echo '</span>';
	}

	/**
	 * Info.
	 */
	public static function info() {
		global $urvanov_syntax_highlighter_version, $urvanov_syntax_highlighter_date, $urvanov_syntax_highlighter_author, $urvanov_syntax_highlighter_website, $urvanov_syntax_highlighter_twitter, $urvanov_syntax_highlighter_git, $urvanov_syntax_highlighter_plugin_wp, $urvanov_syntax_highlighter_author_site, $urvanov_syntax_highlighter_email, $urvanov_syntax_highlighter_donate;
		echo '<a id="info"></a>';
		$version     = '<strong>' . esc_html__( 'Version', 'urvanov-syntax-highlighter' ) . ':</strong> ' . $urvanov_syntax_highlighter_version;
		$date        = $urvanov_syntax_highlighter_date;
		$developer   = '<strong>' . esc_html__( 'Developer', 'urvanov-syntax-highlighter' ) . ':</strong> <a href="' . esc_url( $urvanov_syntax_highlighter_author_site ) . '" target="_blank">' . $urvanov_syntax_highlighter_author . '</a>';
		$translators = '<strong>' . esc_html__( 'Translators', 'urvanov-syntax-highlighter' ) . ':</strong> ' .
						'
            Arabic (Djennad Hamza),
            Chinese Simplified (<a href="https://smerpup.com/" target="_blank">Dezhi Liu</a>, Jash Yin),
            Chinese Traditional (<a href="https://www.arefly.com/" target="_blank">Arefly</a>),
            Dutch (<a href="https://twitter.com/RobinRoelofsen" target="_blank">Robin Roelofsen</a>, <a href="https://twitter.com/#!/chilionsnoek" target="_blank">Chilion Snoek</a>),
            French (<a href="https://vhf.github.io" target="_blank">Victor Felder</a>),
            Finnish (<a href="https://github.com/vahalan" target="_blank">vahalan</a>),
            German (<a href="https://www.technologyblog.de/" target="_blank">Stephan Knau&#223;</a>),
            Italian (<a href="https://www.federicobellucci.net/" target="_blank">Federico Bellucci</a>),
            Japanese (<a href="https://twitter.com/#!/west_323" target="_blank">@west_323</a>),
            Korean (<a href="https://github.com/dokenzy" target="_blank">dokenzy</a>),
            Lithuanian (Vincent G),
            Norwegian (<a href="https://www.jackalworks.com/blogg" target="_blank">Jackalworks</a>),
            Persian (MahdiY),
            Polish (<a href="https://github.com/toszcze" target="_blank">Bartosz Romanowski</a>, <a href="http://rob006.net/" target="_blank">Robert Korulczyk</a>),
            Portuguese (<a href="https://www.adonai.eti.br" target="_blank">Adonai S. Canez</a>),
            Russian (<a href="https://simplelib.com/" target="_blank">Minimus</a>, Di_Skyer),
            Slovak (<a href="https://twitter.com/#!/webhostgeeks" target="_blank">webhostgeeks</a>),
            Slovenian (<a href="https://jodlajodla.si/" target="_blank">Jan Su&#353;nik</a>),
            Spanish (<a href="https://www.hbravo.com/" target="_blank">Hermann Bravo</a>),
            Tamil (KKS21199),
            Turkish (<a href="https://hakanertr.wordpress.com" target="_blank">Hakan</a>),
            Ukrainian (<a href="https://getvoip.com/blog" target="_blank">Michael Yunat</a>)';

		$links = '
	 			<a id="docs-icon" class="small-icon" title="Documentation" href="' . $urvanov_syntax_highlighter_website . '" target="_blank"></a>
				<a id="git-icon" class="small-icon" title="GitHub" href="' . $urvanov_syntax_highlighter_git . '" target="_blank"></a>
				<a id="wp-icon" class="small-icon" title="Plugin Page" href="' . $urvanov_syntax_highlighter_plugin_wp . '" target="_blank"></a>
	 			<a id="twitter-icon" class="small-icon" title="Twitter" href="' . $urvanov_syntax_highlighter_twitter . '" target="_blank"></a>
				<a id="gmail-icon" class="small-icon" title="Email" href="mailto:' . $urvanov_syntax_highlighter_email . '" target="_blank"></a>
				<div id="urvanov-syntax-highlighter-donate"><a href="' . $urvanov_syntax_highlighter_donate . '" title="Donate" target="_blank">
					<img src="' . plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_DONATE_BUTTON, __FILE__ ) . '"></a>
				</div>';

		echo '
				<table id="urvanov-syntax-highlighter-info" style="border:0;">
		  <tr>
				<td>' . $version . ' - ' . $date . '</td>
					</tr>
					<tr>
					<td>' . $developer . '</td>
		  </tr>
		  <tr>
				<td>' . $translators . '</td>
		  </tr>
		  <tr>
				<td colspan="2">' . $links . '</td>
		  </tr>
				</table>';

	}

	/**
	 * Help button.
	 *
	 * @param string $link URL.
	 *
	 * @return string
	 */
	public static function help_button( string $link ): string {
		return ' <a href="' . esc_url( $link ) . '" target="_blank" class="urvanov-syntax-highlighter-question">' . esc_html__( '?', 'urvanov-syntax-highlighter' ) . '</a>';
	}

	/**
	 * Plugin row meta.
	 *
	 * @param array  $meta Plugin meta.
	 * @param string $file Plugin file.
	 *
	 * @return array
	 */
	public static function plugin_row_meta( array $meta, string $file ): array {
		global $urvanov_syntax_highlighter_donate;
		if ( Urvanov_Syntax_Highlighter_Plugin::basename() === $file ) {
			$meta[] = '<a href="options-general.php?page=urvanov_syntax_highlighter_settings">' . esc_html__( 'Settings', 'urvanov-syntax-highlighter' ) . '</a>';
			$meta[] = '<a href="options-general.php?page=urvanov_syntax_highlighter_settings&theme-editor=1">' . esc_html__( 'Theme Editor', 'urvanov-syntax-highlighter' ) . '</a>';
			$meta[] = '<a href="' . $urvanov_syntax_highlighter_donate . '" target="_blank">' . esc_html__( 'Donate', 'urvanov-syntax-highlighter' ) . '</a>';
		}

		return $meta;
	}
}

// Add the settings menus.
if ( defined( 'ABSPATH' ) && is_admin() ) {

	// For the admin section.
	add_action( 'admin_menu', 'Urvanov_Syntax_Highlighter_Settings_WP::admin_load' );
	add_filter( 'plugin_row_meta', 'Urvanov_Syntax_Highlighter_Settings_WP::plugin_row_meta', 10, 2 );
}

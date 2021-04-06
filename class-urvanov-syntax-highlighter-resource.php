<?php
/**
 * Resource Class
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

/**
 * Class Urvanov_Syntax_Highlighter_Resources
 */
class Urvanov_Syntax_Highlighter_Resources {

	/**
	 * Languages.
	 *
	 * @var null
	 */
	private static $langs = null;

	/**
	 * Themes.
	 *
	 * @var null
	 */
	private static $themes = null;

	/**
	 * Fonts.
	 *
	 * @var null
	 */
	private static $fonts = null;

	/**
	 * Urvanov_Syntax_Highlighter_Resources constructor.
	 */
	private function __construct() {}

	/**
	 * Langs.
	 *
	 * @return Urvanov_Syntax_Highlighter_Langs|null
	 */
	public static function langs(): ?Urvanov_Syntax_Highlighter_Langs {
		if ( null === self::$langs ) {
			self::$langs = new Urvanov_Syntax_Highlighter_Langs();
		}

		return self::$langs;
	}

	/**
	 * Themes.
	 *
	 * @return Urvanov_Syntax_Highlighter_Themes|null
	 */
	public static function themes(): ?Urvanov_Syntax_Highlighter_Themes {
		if ( null === self::$themes ) {
			self::$themes = new Urvanov_Syntax_Highlighter_Themes();
		}

		return self::$themes;
	}

	/**
	 * Fonts.
	 *
	 * @return Urvanov_Syntax_Highlighter_Fonts|null
	 */
	public static function fonts(): ?Urvanov_Syntax_Highlighter_Fonts {
		if ( null === self::$fonts ) {
			self::$fonts = new Urvanov_Syntax_Highlighter_Fonts();
		}

		return self::$fonts;
	}
}

/**
 * Class Urvanov_Syntax_Highlighter_Resource_Collection
 */
class Urvanov_Syntax_Highlighter_Resource_Collection { // phpcs:ignore

	/**
	 * Collection.
	 *
	 * @var array
	 */
	private $collection = array();

	/**
	 * Loading state.
	 *
	 * @var int
	 */
	private $state = self::UNLOADED;

	/**
	 * Directory containing resources.
	 *
	 * @var string
	 */
	private $dir = '';

	/**
	 * Default ID.
	 *
	 * @var string
	 */
	private $default_id = '';

	/**
	 * Default name.
	 *
	 * @var string
	 */
	private $default_name = '';

	/**
	 * Unloaded.
	 */
	const UNLOADED = - 1;

	/**
	 * Loading.
	 */
	const LOADING = 0;

	/**
	 * Loaded.
	 */
	const LOADED = 1;

	/**
	 * Override in subclasses. Returns the absolute path for a given resource. Does not check for its existence.
	 *
	 * @param mixed $id Unused.
	 *
	 * @return string
	 */
	public function path( $id ): string {
		return '';
	}

	/**
	 * Verifies a resource exists.
	 *
	 * @param mixed $id ID.
	 *
	 * @return bool
	 */
	public function exists( $id ): bool {
		return file_exists( $this->path( $id ) );
	}

	/**
	 * Load all the available languages. Doesn't parse them for their names and regex.
	 */
	public function load() {

		// Load only once.
		if ( ! $this->is_state_unloaded() ) {
			return;
		}

		$this->state = self::LOADING;
		$this->load_process();
		$this->state = self::LOADED;
	}

	/**
	 * Load resources.
	 *
	 * @param mixed $dir Dir.
	 */
	public function load_resources( $dir = null ) {
		if ( null === $dir ) {
			$dir = $this->dir;
		}

		if ( ! $this->is_state_loading() ) {

			// Load only once.
			return;
		}

		try {
			// Look in directory for resources.
			if ( ! is_dir( $dir ) ) {
				UrvanovSyntaxHighlighterLog::syslog( 'The resource directory is missing, should be at \'' . $dir . '\'.' );
			} elseif ( false !== ( $handle = opendir( $dir ) ) ) {

				// Loop over directory contents.
				while ( false !== ( $file = readdir( $handle ) ) ) {
					if ( '.' !== $file && '..' !== $file ) {

						// Check if $file is directory, remove extension when checking for existence.
						if ( ! is_dir( $dir . $file ) ) {
							$file = UrvanovSyntaxHighlighterUtil::path_rem_ext( $file );
						}

						if ( $this->exists( $file ) ) {
							$this->add_resource( $this->resource_instance( $file ) );
						}
					}
				}

				closedir( $handle );
			}
		} catch ( Exception $e ) {
			UrvanovSyntaxHighlighterLog::syslog( 'An error occured when trying to load resources: ' . $e->getFile() . $e->getLine() );
		}
	}

	/**
	 * Override in subclasses.
	 */
	public function load_process() {
		if ( ! $this->is_state_loading() ) {
			return;
		}
		$this->load_resources();
		$this->add_default();
	}

	/**
	 * Override in subclasses.
	 *
	 * @return bool
	 */
	public function add_default() {
		if ( ! $this->is_state_loading() ) {
			return false;
		} elseif ( ! $this->is_loaded( $this->default_id ) ) {
			UrvanovSyntaxHighlighterLog::syslog( 'The default resource could not be loaded from \'' . $this->dir . '\'.' );

			// Add the default, but it will not be functionable.
			$default = $this->resource_instance( $this->default_id, $this->default_name );
			$this->add( $this->default_id, $default );

			return true;
		}

		return false;
	}

	/**
	 * Returns the default resource.
	 *
	 * @param mixed  $id   ID.
	 * @param string $name Name.
	 */
	public function set_default( $id, string $name ) {
		$this->default_id   = $id;
		$this->default_name = $name;
	}

	/**
	 * Returns the default resource.
	 *
	 * @return array|mixed|null
	 */
	public function get_default() {
		return $this->get( $this->default_id );
	}

	/**
	 * Override in subclasses to create subclass object if needed.
	 *
	 * @param mixed  $id ID.
	 * @param string $name Name.
	 *
	 * @return Urvanov_Syntax_Highlighter_Resource
	 */
	public function resource_instance( $id = '', $name = null ) {
		return new Urvanov_Syntax_Highlighter_Resource( $id, $name );
	}

	/**
	 * Add resource.
	 *
	 * @param mixed $id ID.
	 * @param mixed $resource Resource.
	 */
	public function add( $id, $resource ) {
		if ( is_string( $id ) && ! empty( $id ) ) {
			$this->collection[ $id ] = $resource;
			asort( $this->collection );
			UrvanovSyntaxHighlighterLog::debug( 'Added resource: ' . $this->path( $id ) );
		} else {
			UrvanovSyntaxHighlighterLog::syslog( 'Could not add resource: ', $id );
		}
	}

	/**
	 * Add resource.
	 *
	 * @param object $resource Resource.
	 */
	public function add_resource( $resource = null ) {
		$this->add( $resource->id(), $resource );
	}

	/**
	 * Remove.
	 *
	 * @param string $name Name.
	 */
	public function remove( $name = '' ) {
		if ( is_string( $name ) && ! empty( $name ) && array_key_exists( $name, $this->collection ) ) {
			unset( $this->collection[ $name ] );
		}
	}

	/**
	 * Remove all.
	 */
	public function remove_all() {
		$this->collection = array();
	}

	/**
	 * Returns the resource for the given id or NULL if it can't be found.
	 *
	 * @param mixed $id ID.
	 *
	 * @return array|mixed|null
	 */
	public function get( $id = null ) {
		$this->load();

		if ( null === $id ) {
			return $this->collection;
		} elseif ( is_string( $id ) && $this->is_loaded( $id ) ) {
			return $this->collection[ $id ];
		}

		return null;
	}

	/**
	 * Get array.
	 *
	 * @return array
	 */
	public function get_array(): array {
		$array = array();

		foreach ( $this->get() as $resource ) {
			$array[ $resource->id() ] = $resource->name();
		}

		return $array;
	}

	/**
	 * Is loaded.
	 *
	 * @param mixed $id ID.
	 *
	 * @return bool
	 */
	public function is_loaded( $id ): bool {
		if ( is_string( $id ) ) {
			return array_key_exists( $id, $this->collection );
		}

		return false;
	}

	/**
	 * Get state.
	 *
	 * @return int
	 */
	public function get_state(): int {
		return $this->state;
	}

	/**
	 * Is state loaded.
	 *
	 * @return bool
	 */
	public function is_state_loaded(): bool {
		return self::LOADED === $this->state;
	}

	/**
	 * Is state loading.
	 *
	 * @return bool
	 */
	public function is_state_loading(): bool {
		return self::LOADING === $this->state;
	}

	/**
	 * Is state Unloaded.
	 *
	 * @return bool
	 */
	public function is_state_unloaded(): bool {
		return self::UNLOADED === $this->state;
	}

	/**
	 * Directory.
	 *
	 * @param string $dir Dir.
	 *
	 * @return string
	 */
	public function directory( $dir = null ): string {
		if ( null === $dir ) {
			return $this->dir;
		} else {
			$this->dir = UrvanovSyntaxHighlighterUtil::path_slash( $dir );
		}

		return '';
	}

	/**
	 * URL.
	 *
	 * @param mixed $id ID.
	 *
	 * @return string
	 */
	public function url( $id ): string {
		return '';
	}

	/**
	 * Get CSS.
	 *
	 * @param mixed $id ID.
	 * @param null  $ver Version.
	 *
	 * @return string
	 */
	public function get_css( $id, $ver = null ): string {
		$resource = $this->get( $id );

		// phpcs:ignore
		return '<link rel="stylesheet" type="text/css" href="' . esc_url( $this->url( $resource->id() ) ) . ( $ver ? "?ver=$ver" : '' ) . '" />' . URVANOV_SYNTAX_HIGHLIGHTER_NL;
	}
}

/**
 * Class Urvanov_Syntax_Highlighter_Used_Resource_Collection
 */
class Urvanov_Syntax_Highlighter_Used_Resource_Collection extends Urvanov_Syntax_Highlighter_Resource_Collection { // phpcs:ignore

	/**
	 * Checks if any resoruces are being used.
	 *
	 * @param mixed $id ID.
	 *
	 * @return bool
	 */
	public function is_used( $id = null ): bool {
		if ( null === $id ) {
			foreach ( $this->get() as $resource ) {
				if ( $resource->used() ) {
					return true;
				}
			}

			return false;
		} else {
			$resource = $this->get( $id );
			if ( ! $resource ) {
				return false;
			} else {
				return $resource->used();
			}
		}
	}

	/**
	 * Set used.
	 *
	 * @param mixed $id ID.
	 * @param bool  $value Value.
	 *
	 * @return bool
	 */
	public function set_used( $id, $value = true ): bool {
		$resource = $this->get( $id );
		if ( null !== $resource && ! $resource->used() ) {
			$resource->used( true === $value );

			return true;
		}

		return false;
	}

	/**
	 * Get used.
	 *
	 * @return array
	 */
	public function get_used(): array {
		$used = array();

		foreach ( $this->get() as $resource ) {
			if ( $resource->used() ) {
				$used[] = $resource;
			}
		}

		return $used;
	}

	/**
	 * Override.
	 *
	 * @param mixed  $id ID.
	 * @param string $name Name.
	 *
	 * @return Urvanov_Syntax_Highlighter_Used_Resource
	 */
	public function resource_instance( $id = '', $name = null ) {
		return new Urvanov_Syntax_Highlighter_Used_Resource( $id, $name );
	}

	/**
	 * Get used CSS.
	 *
	 * @return array
	 */
	public function get_used_css(): array {
		$used = $this->get_used();
		$css  = array();
		foreach ( $used as $resource ) {
			$url                    = $this->url( $resource->id() );
			$css[ $resource->id() ] = $url;
		}

		return $css;
	}
}

/**
 * Class Urvanov_Syntax_Highlighter_User_Resource_Collection
 */
class Urvanov_Syntax_Highlighter_User_Resource_Collection extends Urvanov_Syntax_Highlighter_Used_Resource_Collection { // phpcs:ignore

	/**
	 * User dir.
	 *
	 * @var string
	 */
	private $user_dir = '';

	/**
	 * Current dir.
	 *
	 * @var null
	 */
	private $curr_dir = null;

	/**
	 * TODO better to use a base dir and relative.
	 *
	 * @var null
	 */
	private $relative_directory = null;

	/**
	 * TODO move this higher up inheritance.
	 *
	 * @var string
	 */
	private $extension = '';

	/**
	 * Resource instance override.
	 *
	 * @param mixed $id ID.
	 * @param null  $name Name.
	 *
	 * @return Urvanov_Syntax_Highlighter_Used_Resource|Urvanov_Syntax_Highlighter_User_Resource
	 */
	public function resource_instance( $id = '', $name = null ) {
		$resource = $this->create_user_resource_instance( $id, $name );
		$resource->user( $this->curr_dir === $this->user_directory() );

		return $resource;
	}

	/**
	 * Create user resource instance.
	 *
	 * @param mixed $id ID.
	 * @param null  $name Name.
	 *
	 * @return Urvanov_Syntax_Highlighter_User_Resource
	 */
	public function create_user_resource_instance( $id, $name = null ) {
		return new Urvanov_Syntax_Highlighter_User_Resource( $id, $name );
	}

	/**
	 * User dir.
	 *
	 * @param string $dir Dir.
	 *
	 * @return string
	 */
	public function user_directory( $dir = null ): string {
		if ( null === $dir ) {
			return $this->user_dir;
		} else {
			$this->user_dir = UrvanovSyntaxHighlighterUtil::path_slash( $dir );
		}

		return '';
	}

	/**
	 * Relative dir.
	 *
	 * @param string $relative_directory Relative dir.
	 *
	 * @return null
	 */
	public function relative_directory( $relative_directory = null ) {
		if ( null === $relative_directory ) {
			return $this->relative_directory;
		}
		$this->relative_directory = $relative_directory;
	}

	/**
	 * Exyension.
	 *
	 * @param string $extension Ext.
	 *
	 * @return string
	 */
	public function extension( $extension = null ): string {
		if ( null === $extension ) {
			return $this->extension;
		}

		$this->extension = $extension;

		return '';
	}

	/**
	 * Load resources.
	 *
	 * @param string $dir Dir.
	 */
	public function load_resources( $dir = null ) {
		$this->curr_dir = $this->directory();
		parent::load_resources( $this->curr_dir );
		$this->curr_dir = $this->user_directory();
		parent::load_resources( $this->curr_dir );
		$this->curr_dir = null;
	}

	/**
	 * Current dir.
	 *
	 * @return null
	 */
	public function current_directory() {
		return $this->curr_dir;
	}

	/**
	 * Dir is user.
	 *
	 * @param mixed  $id ID.
	 * @param string $user User.
	 *
	 * @return bool|mixed|null
	 */
	public function dir_is_user( $id, $user = null ) {
		if ( null === $user ) {
			if ( $this->is_state_loading() ) {

				// We seem to be loading resources - use current directory.
				$user = $this->current_directory() === $this->user_directory();
			} else {
				$theme = $this->get( $id );
				if ( $theme ) {
					$user = $theme->user();
				} else {
					$user = false;
				}
			}
		}

		return $user;
	}

	/**
	 * Dirpath.
	 *
	 * @param string $user User.
	 *
	 * @return array|string|string[]
	 */
	public function dirpath( $user = null ) {
		$path = $user ? $this->user_directory() : $this->directory();

		return UrvanovSyntaxHighlighterUtil::path_slash( $path );
	}

	/**
	 * Dirpath for ID.
	 *
	 * @param mixed  $id ID.
	 * @param string $user User.
	 *
	 * @return string
	 */
	public function dirpath_for_id( $id, $user = null ): string {
		$user = $this->dir_is_user( $id, $user );

		return $this->dirpath( $user ) . $id;
	}

	/**
	 * Dir URL.
	 *
	 * @param string $user User.
	 *
	 * @return array|string|string[]
	 */
	public function dirurl( $user = null ) {
		$path = $user ? Urvanov_Syntax_Highlighter_Global_Settings::upload_url() : Urvanov_Syntax_Highlighter_Global_Settings::plugin_path();

		return UrvanovSyntaxHighlighterUtil::path_slash( $path . $this->relative_directory() );
	}

	/**
	 * Path override.
	 *
	 * @param mixed  $id ID.
	 * @param string $user User.
	 *
	 * @return string
	 */
	public function path( $id, $user = null ): string {
		$user = $this->dir_is_user( $id, $user );

		return $this->dirpath( $user ) . $this->filename( $id, $user );
	}

	/**
	 * URL override.
	 *
	 * @param mixed  $id ID.
	 * @param string $user User.
	 *
	 * @return string
	 */
	public function url( $id, $user = null ): string {
		$user = $this->dir_is_user( $id, $user );

		return $this->dirurl( $user ) . $this->filename( $id, $user );
	}

	/**
	 * Filename override.
	 *
	 * @param mixed  $id ID.
	 * @param string $user User.
	 *
	 * @return string
	 */
	public function filename( $id, $user = null ): string {
		return "$id.$this->extension";
	}
}

/**
 * Class Urvanov_Syntax_Highlighter_Resource
 */
class Urvanov_Syntax_Highlighter_Resource { // phpcs:ignore

	/**
	 * ID.
	 *
	 * @var string
	 */
	private $id = '';

	/**
	 * Name.
	 *
	 * @var string
	 */
	private $name = '';


	/**
	 * Urvanov_Syntax_Highlighter_Resource constructor.
	 *
	 * @param mixed  $id ID.
	 * @param string $name Name.
	 */
	public function __construct( $id, $name = null ) {
		$id = $this->clean_id( $id );
		UrvanovSyntaxHighlighterUtil::str( $this->id, $id );

		( empty( $name ) ) ? $this->name( self::clean_name( $this->id ) ) : $this->name( $name );
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
	 * ID.
	 *
	 * @return string
	 */
	public function id(): string {
		return $this->id;
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
		} else {
			$this->name = $name;
		}

		return '';
	}

	/**
	 * Clean ID.
	 *
	 * @param mixed $id ID.
	 *
	 * @return array|string|string[]|null
	 */
	public function clean_id( $id ) {
		return self::clean_id_static( $id );
	}

	/**
	 * Clean ID static.
	 *
	 * @param mixed $id ID.
	 *
	 * @return array|string|string[]|null
	 */
	public static function clean_id_static( $id ) {
		$id = UrvanovSyntaxHighlighterUtil::space_to_hyphen( strtolower( trim( $id ) ) );

		return preg_replace( '#[^\w-]#msi', '', $id );
	}

	/**
	 * Clean name.
	 *
	 * @param mixed $id ID.
	 *
	 * @return string
	 */
	public static function clean_name( $id ): string {
		$id = UrvanovSyntaxHighlighterUtil::hyphen_to_space( strtolower( trim( $id ) ) );

		return UrvanovSyntaxHighlighterUtil::ucwords( $id );
	}
}

/**
 * Class Urvanov_Syntax_Highlighter_Used_Resource
 */
class Urvanov_Syntax_Highlighter_Used_Resource extends Urvanov_Syntax_Highlighter_Resource { // phpcs:ignore

	/**
	 * Keeps track of usage.
	 *
	 * @var bool
	 */
	private $used = false;

	/**
	 * Used.
	 *
	 * @param bool $used Used.
	 *
	 * @return bool
	 */
	public function used( $used = null ): bool {
		if ( null === $used ) {
			return $this->used;
		} else {
			$this->used = (bool) $used;
		}

		return false;
	}
}

/**
 * Class Urvanov_Syntax_Highlighter_User_Resource
 */
class Urvanov_Syntax_Highlighter_User_Resource extends Urvanov_Syntax_Highlighter_Used_Resource { // phpcs:ignore

	/**
	 * Keeps track of user modifications.
	 *
	 * @var bool
	 */
	private $user = false;

	/**
	 * USer.
	 *
	 * @param string $user User.
	 *
	 * @return bool
	 */
	public function user( $user = null ): bool {
		if ( null === $user ) {
			return $this->user;
		} else {
			$this->user = (bool) $user;
		}

		return false;
	}
}

/**
 * Class Urvanov_Syntax_Highlighter_Version_Resource
 */
class Urvanov_Syntax_Highlighter_Version_Resource extends Urvanov_Syntax_Highlighter_User_Resource { // phpcs:ignore

	/**
	 * Adds version.
	 *
	 * @var string
	 */
	private $version = '';

	/**
	 * Urvanov_Syntax_Highlighter_Version_Resource constructor.
	 *
	 * @param mixed $id ID.
	 * @param null  $name Name.
	 * @param null  $version Version.
	 */
	public function __construct( $id, $name = null, $version = null ) {
		parent::__construct( $id, $name );
		$this->version( $version );
	}

	/**
	 * Version.
	 *
	 * @param mixed $version Version.
	 *
	 * @return string
	 */
	public function version( $version = null ): string {
		if ( null === $version ) {
			return $this->version;
		} elseif ( is_string( $version ) ) {
			$this->version = $version;
		}

		return '';
	}
}

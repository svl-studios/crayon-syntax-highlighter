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

// Old name: CrayonResources

/**
 * Class Urvanov_Syntax_Highlighter_Resources
 */
class Urvanov_Syntax_Highlighter_Resources {
	/**
	 * @var null
	 */
	private static $langs = null;
	/**
	 * @var null
	 */
	private static $themes = null;
	/**
	 * @var null
	 */
	private static $fonts = null;

	/**
	 * Urvanov_Syntax_Highlighter_Resources constructor.
	 */
	private function __construct() {
	}

	/**
	 * @return Urvanov_Syntax_Highlighter_Langs|null
	 */
	public static function langs() {
		if ( self::$langs == null ) {
			self::$langs = new Urvanov_Syntax_Highlighter_Langs();
		}

		return self::$langs;
	}

	/**
	 * @return Urvanov_Syntax_Highlighter_Themes|null
	 */
	public static function themes() {
		if ( self::$themes == null ) {
			self::$themes = new Urvanov_Syntax_Highlighter_Themes();
		}

		return self::$themes;
	}

	/**
	 * @return Urvanov_Syntax_Highlighter_Fonts|null
	 */
	public static function fonts() {
		if ( self::$fonts == null ) {
			self::$fonts = new Urvanov_Syntax_Highlighter_Fonts();
		}

		return self::$fonts;
	}
}

// Old name: CrayonResourceCollection

/**
 * Class Urvanov_Syntax_Highlighter_Resource_Collection
 */
class Urvanov_Syntax_Highlighter_Resource_Collection {
	// Properties and Constants ===============================================

	// Loaded resources

	/**
	 * @var array
	 */
	private $collection = array();
	// Loading state

	/**
	 * @var int
	 */
	private $state = self::UNLOADED;
	// Directory containing resources

	/**
	 * @var string
	 */
	private $dir = '';
	/**
	 * @var string
	 */
	private $default_id = '';
	/**
	 * @var string
	 */
	private $default_name = '';
	/**
	 *
	 */
	const UNLOADED = - 1;
	/**
	 *
	 */
	const LOADING = 0;
	/**
	 *
	 */
	const LOADED = 1;

	// Methods ================================================================

	/* Override in subclasses. Returns the absolute path for a given resource. Does not check for its existence. */
	/**
	 * @param $id
	 *
	 * @return string
	 */
	public function path( $id ) {
		return '';
	}

	/* Verifies a resource exists. */
	/**
	 * @param $id
	 *
	 * @return bool
	 */
	public function exists( $id ) {
		return file_exists( $this->path( $id ) );
	}

	/* Load all the available languages. Doesn't parse them for their names and regex. */
	/**
	 *
	 */
	public function load() {
		// Load only once

		if ( ! $this->is_state_unloaded() ) {
			return;
		}
		$this->state = self::LOADING;
		$this->load_process();
		$this->state = self::LOADED;
	}

	/**
	 * @param null $dir
	 */
	public function load_resources( $dir = null ) {
		if ( $dir === null ) {
			$dir = $this->dir;
		}

		if ( ! $this->is_state_loading() ) {
			// Load only once
			return;
		}
		try {
			// Look in directory for resources
			if ( ! is_dir( $dir ) ) {
				UrvanovSyntaxHighlighterLog::syslog( 'The resource directory is missing, should be at \'' . $dir . '\'.' );
			} elseif ( ( $handle = @opendir( $dir ) ) != false ) {
				// Loop over directory contents
				while ( ( $file = readdir( $handle ) ) !== false ) {
					if ( $file != "." && $file != ".." ) {
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

	/* Override in subclasses. */
	/**
	 *
	 */
	public function load_process() {
		if ( ! $this->is_state_loading() ) {
			return;
		}
		$this->load_resources();
		$this->add_default();
	}

	/* Override in subclasses */
	/**
	 * @return bool
	 */
	public function add_default() {
		if ( ! $this->is_state_loading() ) {
			return false;
		} elseif ( ! $this->is_loaded( $this->default_id ) ) {
			UrvanovSyntaxHighlighterLog::syslog( 'The default resource could not be loaded from \'' . $this->dir . '\'.' );
			// Add the default, but it will not be functionable

			$default = $this->resource_instance( $this->default_id, $this->default_name );
			$this->add( $this->default_id, $default );

			return true;
		}

		return false;
	}

	/* Returns the default resource */
	/**
	 * @param $id
	 * @param $name
	 */
	public function set_default( $id, $name ) {
		$this->default_id   = $id;
		$this->default_name = $name;
	}

	/* Returns the default resource */
	/**
	 * @return array|mixed|null
	 */
	public function get_default() {
		return $this->get( $this->default_id );
	}

	/* Override in subclasses to create subclass object if needed */
	/**
	 * @param      $id
	 * @param null $name
	 *
	 * @return Urvanov_Syntax_Highlighter_Resource
	 */
	public function resource_instance( $id, $name = null ) {
		return new Urvanov_Syntax_Highlighter_Resource( $id, $name );
	}

	/**
	 * @param $id
	 * @param $resource
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
	 * @param $resource
	 */
	public function add_resource( $resource ) {
		$this->add( $resource->id(), $resource );
	}

	/**
	 * @param $name
	 */
	public function remove( $name ) {
		if ( is_string( $name ) && ! empty( $name ) && array_key_exists( $name, $this->collection ) ) {
			unset( $this->collection[ $name ] );
		}
	}

	/**
	 *
	 */
	public function remove_all() {
		$this->collection = array();
	}

	/* Returns the resource for the given id or NULL if it can't be found */
	/**
	 * @param null $id
	 *
	 * @return array|mixed|null
	 */
	public function get( $id = null ) {
		$this->load();
		if ( $id === null ) {
			return $this->collection;
		} elseif ( is_string( $id ) && $this->is_loaded( $id ) ) {
			return $this->collection[ $id ];
		}

		return null;
	}

	/**
	 * @return array
	 */
	public function get_array() {
		$array = array();
		foreach ( $this->get() as $resource ) {
			$array[ $resource->id() ] = $resource->name();
		}

		return $array;
	}

	/**
	 * @param $id
	 *
	 * @return bool
	 */
	public function is_loaded( $id ) {
		if ( is_string( $id ) ) {
			return array_key_exists( $id, $this->collection );
		}

		return false;
	}

	/**
	 * @return int
	 */
	public function get_state() {
		return $this->state;
	}

	/**
	 * @return bool
	 */
	public function is_state_loaded() {
		return $this->state == self::LOADED;
	}

	/**
	 * @return bool
	 */
	public function is_state_loading() {
		return $this->state == self::LOADING;
	}

	/**
	 * @return bool
	 */
	public function is_state_unloaded() {
		return $this->state == self::UNLOADED;
	}

	/**
	 * @param null $dir
	 *
	 * @return string
	 */
	public function directory( $dir = null ) {
		if ( $dir === null ) {
			return $this->dir;
		} else {
			$this->dir = UrvanovSyntaxHighlighterUtil::path_slash( $dir );
		}
	}

	/**
	 * @param $id
	 *
	 * @return string
	 */
	public function url( $id ) {
		return '';
	}

	/**
	 * @param      $id
	 * @param null $ver
	 *
	 * @return string
	 */
	public function get_css( $id, $ver = null ) {
		$resource = $this->get( $id );

		return '<link rel="stylesheet" type="text/css" href="' . $this->url( $resource->id() ) . ( $ver ? "?ver=$ver" : '' ) . '" />' . URVANOV_SYNTAX_HIGHLIGHTER_NL;
	}
}

// Old name: CrayonUsedResourceCollection

/**
 * Class Urvanov_Syntax_Highlighter_Used_Resource_Collection
 */
class Urvanov_Syntax_Highlighter_Used_Resource_Collection extends Urvanov_Syntax_Highlighter_Resource_Collection {

	// Checks if any resoruces are being used
	/**
	 * @param null $id
	 *
	 * @return bool
	 */
	public function is_used( $id = null ) {
		if ( $id === null ) {
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
	 * @param      $id
	 * @param bool $value
	 *
	 * @return bool
	 */
	public function set_used( $id, $value = true ) {
		$resource = $this->get( $id );
		if ( $resource !== null && ! $resource->used() ) {
			$resource->used( $value == true );

			return true;
		}

		return false;
	}

	/**
	 * @return array
	 */
	public function get_used() {
		$used = array();
		foreach ( $this->get() as $resource ) {
			if ( $resource->used() ) {
				$used[] = $resource;
			}
		}

		return $used;
	}

	// XXX Override

	/**
	 * @param      $id
	 * @param null $name
	 *
	 * @return Urvanov_Syntax_Highlighter_Used_Resource
	 */
	public function resource_instance( $id, $name = null ) {
		return new Urvanov_Syntax_Highlighter_Used_Resource( $id, $name );
	}

	/**
	 * @return array
	 */
	public function get_used_css() {
		$used = $this->get_used();
		$css  = array();
		foreach ( $used as $resource ) {
			$url                    = $this->url( $resource->id() );
			$css[ $resource->id() ] = $url;
		}

		return $css;
	}
}

// Old name: CrayonUserResourceCollection

/**
 * Class Urvanov_Syntax_Highlighter_User_Resource_Collection
 */
class Urvanov_Syntax_Highlighter_User_Resource_Collection extends Urvanov_Syntax_Highlighter_Used_Resource_Collection {
	/**
	 * @var string
	 */
	private $user_dir = '';
	/**
	 * @var null
	 */
	private $curr_dir = null;
	// TODO better to use a base dir and relative
	/**
	 * @var null
	 */
	private $relative_directory = null;
	// TODO move this higher up inheritance
	/**
	 * @var string
	 */
	private $extension = '';

	// XXX Override

	/**
	 * @param      $id
	 * @param null $name
	 *
	 * @return Urvanov_Syntax_Highlighter_Used_Resource|Urvanov_Syntax_Highlighter_User_Resource
	 */
	public function resource_instance( $id, $name = null ) {
		$resource = $this->create_user_resource_instance( $id, $name );
		$resource->user( $this->curr_dir == $this->user_directory() );

		return $resource;
	}

	/**
	 * @param      $id
	 * @param null $name
	 *
	 * @return Urvanov_Syntax_Highlighter_User_Resource
	 */
	public function create_user_resource_instance( $id, $name = null ) {
		return new Urvanov_Syntax_Highlighter_User_Resource( $id, $name );
	}

	/**
	 * @param null $dir
	 *
	 * @return string
	 */
	public function user_directory( $dir = null ) {
		if ( $dir === null ) {
			return $this->user_dir;
		} else {
			$this->user_dir = UrvanovSyntaxHighlighterUtil::path_slash( $dir );
		}
	}

	/**
	 * @param null $relative_directory
	 *
	 * @return null
	 */
	public function relative_directory( $relative_directory = null ) {
		if ( $relative_directory == null ) {
			return $this->relative_directory;
		}
		$this->relative_directory = $relative_directory;
	}

	/**
	 * @param null $extension
	 *
	 * @return string
	 */
	public function extension( $extension = null ) {
		if ( $extension == null ) {
			return $this->extension;
		}
		$this->extension = $extension;
	}

	/**
	 * @param null $dir
	 */
	public function load_resources( $dir = null ) {
		$this->curr_dir = $this->directory();
		parent::load_resources( $this->curr_dir );
		$this->curr_dir = $this->user_directory();
		parent::load_resources( $this->curr_dir );
		$this->curr_dir = null;
	}

	/**
	 * @return null
	 */
	public function current_directory() {
		return $this->curr_dir;
	}

	/**
	 * @param      $id
	 * @param null $user
	 *
	 * @return bool|mixed|null
	 */
	public function dir_is_user( $id, $user = null ) {
		if ( $user === null ) {
			if ( $this->is_state_loading() ) {
				// We seem to be loading resources - use current directory
				$user = $this->current_directory() == $this->user_directory();
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
	 * @param null $user
	 *
	 * @return array|string|string[]
	 */
	public function dirpath( $user = null ) {
		$path = $user ? $this->user_directory() : $this->directory();

		return UrvanovSyntaxHighlighterUtil::path_slash( $path );
	}

	/**
	 * @param      $id
	 * @param null $user
	 *
	 * @return string
	 */
	public function dirpath_for_id( $id, $user = null ) {
		$user = $this->dir_is_user( $id, $user );

		return $this->dirpath( $user ) . $id;
	}

	/**
	 * @param null $user
	 *
	 * @return array|string|string[]
	 */
	public function dirurl( $user = null ) {
		$path = $user ? Urvanov_Syntax_Highlighter_Global_Settings::upload_url() : Urvanov_Syntax_Highlighter_Global_Settings::plugin_path();

		return UrvanovSyntaxHighlighterUtil::path_slash( $path . $this->relative_directory() );
	}

	// XXX Override

	/**
	 * @param      $id
	 * @param null $user
	 *
	 * @return string
	 */
	public function path( $id, $user = null ) {
		$user = $this->dir_is_user( $id, $user );

		return $this->dirpath( $user ) . $this->filename( $id, $user );
	}

	// XXX Override

	/**
	 * @param      $id
	 * @param null $user
	 *
	 * @return string
	 */
	public function url( $id, $user = null ) {
		$user = $this->dir_is_user( $id, $user );

		return $this->dirurl( $user ) . $this->filename( $id, $user );
	}

	/**
	 * @param      $id
	 * @param null $user
	 *
	 * @return string
	 */
	public function filename( $id, $user = null ) {
		return "$id.$this->extension";
	}

}

// Old name: CrayonResource

/**
 * Class Urvanov_Syntax_Highlighter_Resource
 */
class Urvanov_Syntax_Highlighter_Resource {
	/**
	 * @var string
	 */
	private $id = '';
	/**
	 * @var string
	 */
	private $name = '';

	/**
	 * Urvanov_Syntax_Highlighter_Resource constructor.
	 *
	 * @param      $id
	 * @param null $name
	 */
	function __construct( $id, $name = null ) {
		$id = $this->clean_id( $id );
		UrvanovSyntaxHighlighterUtil::str( $this->id, $id );
		( empty( $name ) ) ? $this->name( self::clean_name( $this->id ) ) : $this->name( $name );
	}

	/**
	 * @return string
	 */
	function __tostring() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	function id() {
		return $this->id;
	}

	/**
	 * @param null $name
	 *
	 * @return string
	 */
	function name( $name = null ) {
		if ( $name === null ) {
			return $this->name;
		} else {
			$this->name = $name;
		}
	}

	/**
	 * @param $id
	 *
	 * @return array|string|string[]|null
	 */
	function clean_id( $id ) {
		return Urvanov_Syntax_Highlighter_Resource::clean_id_static( $id );
	}

	/**
	 * @param $id
	 *
	 * @return array|string|string[]|null
	 */
	public static function clean_id_static( $id ) {
		$id = UrvanovSyntaxHighlighterUtil::space_to_hyphen( strtolower( trim( $id ) ) );

		return preg_replace( '#[^\w-]#msi', '', $id );
	}

	/**
	 * @param $id
	 *
	 * @return string
	 */
	public static function clean_name( $id ) {
		$id = UrvanovSyntaxHighlighterUtil::hyphen_to_space( strtolower( trim( $id ) ) );

		return UrvanovSyntaxHighlighterUtil::ucwords( $id );
	}

}

// Old name: CrayonUsedResource

/**
 * Class Urvanov_Syntax_Highlighter_Used_Resource
 */
class Urvanov_Syntax_Highlighter_Used_Resource extends Urvanov_Syntax_Highlighter_Resource {
	// Keeps track of usage
	/**
	 * @var bool
	 */
	private $used = false;

	/**
	 * @param null $used
	 *
	 * @return bool
	 */
	function used( $used = null ) {
		if ( $used === null ) {
			return $this->used;
		} else {
			$this->used = ( $used ? true : false );
		}
	}
}

// Old name: CrayonUserResource

/**
 * Class Urvanov_Syntax_Highlighter_User_Resource
 */
class Urvanov_Syntax_Highlighter_User_Resource extends Urvanov_Syntax_Highlighter_Used_Resource {
	// Keeps track of user modifications
	/**
	 * @var bool
	 */
	private $user = false;

	/**
	 * @param null $user
	 *
	 * @return bool
	 */
	function user( $user = null ) {
		if ( $user === null ) {
			return $this->user;
		} else {
			$this->user = ( $user ? true : false );
		}
	}
}

// Old name: CrayonVersionResource

/**
 * Class Urvanov_Syntax_Highlighter_Version_Resource
 */
class Urvanov_Syntax_Highlighter_Version_Resource extends Urvanov_Syntax_Highlighter_User_Resource {
	// Adds version
	/**
	 * @var string
	 */
	private $version = '';

	/**
	 * Urvanov_Syntax_Highlighter_Version_Resource constructor.
	 *
	 * @param      $id
	 * @param null $name
	 * @param null $version
	 */
	function __construct( $id, $name = null, $version = null ) {
		parent::__construct( $id, $name );
		$this->version( $version );
	}

	/**
	 * @param null $version
	 *
	 * @return string
	 */
	function version( $version = null ) {
		if ( $version === null ) {
			return $this->version;
		} elseif ( is_string( $version ) ) {
			$this->version = $version;
		}
	}
}

?>
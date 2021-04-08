<?php
/**
 * Util Class
 *
 * @package   Crayon Syntax Highlighter
 * @author    Fedor Urvanov, Aram Kocharyan
 * @copyright Copyright 2013, Aram Kocharyan
 * @link      https://urvanov.ru
 */

defined( 'ABSPATH' ) || exit;

/**
 * Common utility functions mainly for formatting, parsing etc.
 *
 * Class UrvanovSyntaxHighlighterUtil
 */
class UrvanovSyntaxHighlighterUtil {

	/**
	 * Used to detect touchscreen devices.
	 *
	 * @var null
	 */
	private static $touchscreen = null;

	/**
	 * Return the lines inside a file as an array, options:
	 * l - lowercase
	 * w - remove whitespace
	 * r - escape regex chars
	 * c - remove comments
	 * s - return as string
	 *
	 * @param string $path Path.
	 * @param mixed  $opts Options.
	 *
	 * @return false|mixed|string
	 */
	public static function lines( $path, $opts = null ) {
		$path = self::pathf( $path );
		$str  = self::file( $path );

		if ( false === $str ) {
			// Log failure, n = no log.
			if ( strpos( $opts, 'n' ) === false ) {
				UrvanovSyntaxHighlighterLog::syslog( "Cannot read lines at '$path'.", 'UrvanovSyntaxHighlighterUtil::lines()' );
			}
			return false;
		}
		// Read the options.
		if ( is_string( $opts ) ) {
			$lowercase       = strpos( $opts, 'l' ) !== false;
			$whitespace      = strpos( $opts, 'w' ) !== false;
			$escape_regex    = strpos( $opts, 'r' ) !== false;
			$clean_commments = strpos( $opts, 'c' ) !== false;
			$return_string   = strpos( $opts, 's' ) !== false;
		} else {
			$lowercase       = false;
			$whitespace      = false;
			$escape_regex    = false;
			$clean_commments = false;
			$return_string   = false;
		}

		// Remove comments.
		if ( $clean_commments ) {
			$str = self::clean_comments( $str );
		}

		// Convert to lowercase if needed.
		if ( $lowercase ) {
			$str = strtolower( $str );
		}

		/**
		 * Match all the content on non-empty lines, also remove any whitespace to the left and right if needed.
		 */
		if ( $whitespace ) {
			$pattern = '[^\s]+(?:.*[^\s])?';
		} else {
			$pattern = '^(?:.*)?';
		}

		preg_match_all( '|' . $pattern . '|m', $str, $matches );
		$lines = $matches[0];

		// Remove regex syntax and assume all characters are literal.
		if ( $escape_regex ) {
			$count = count( $lines );
			for ( $i = 0; $i < $count; $i++ ) {
				$lines[ $i ] = self::esc_regex( $lines[ $i ] );

				// If we have used \#, then we don't want it to become.
				$lines[ $i ] = preg_replace( '|\\\\\\\\#|', '\#', $lines[ $i ] );
			}
		}

		// Return as string if needed.
		if ( $return_string ) {

			// Add line breaks if they were stripped.
			$delimiter = '';

			if ( $whitespace ) {
				$delimiter = URVANOV_SYNTAX_HIGHLIGHTER_NL;
			}

			$lines = implode( $delimiter, $lines );
		}

		return $lines;
	}

	/**
	 * Returns the contents of a file.
	 *
	 * @param string $path Path.
	 *
	 * @return false|string
	 */
	public static function file( $path ) {
		$str = file_get_contents( $path ); // TODO:  Replace with wp_filesystem.
		if ( false === $str ) {
			return false;
		} else {
			return $str;
		}
	}

	/**
	 * Zips a source file or directory.
	 *
	 * @param string $src                 A directory or file.
	 * @param string $dest                A directory or zip file. If a zip file is provided it must exist beforehand.
	 * @param bool   $remove_existing_zip Remove found file.
	 *
	 * @return string
	 * @throws Exception InvalidArgumentException.
	 * @throws Exception ZIP.
	 */
	public static function create_zip( string $src, string $dest, $remove_existing_zip = false ): string {
		if ( $src === $dest ) {
			throw new InvalidArgumentException( "Source '$src' and '$dest' cannot be the same" );
		}

		if ( is_dir( $src ) ) {
			$src  = self::path_slash( $src );
			$base = $src;

			// Make sure the destination isn't in the files.
			$files = self::get_files(
				$src,
				array(
					'recursive' => true,
					'ignore'    => array( $dest ),
				)
			);
		} elseif ( is_file( $src ) ) {
			$files = array( $src );
			$base  = dirname( $src );
		} else {
			throw new InvalidArgumentException( "Source '$src' is not a directory or file" );
		}

		if ( is_dir( $dest ) ) {
			$dest     = self::path_slash( $dest );
			$zip_file = $dest . basename( $src ) . '.zip';
		} elseif ( is_file( $dest ) ) {
			$zip_file = $dest;
		} else {
			throw new InvalidArgumentException( "Destination '$dest' is not a directory or file" );
		}

		if ( $remove_existing_zip ) {
			unlink( $zip_file );
		}

		$zip = new ZipArchive();

		if ( $zip->open( $zip_file, ZIPARCHIVE::CREATE ) === true ) {
			foreach ( $files as $file ) {
				$rel_file = str_replace( $base, '', $file );
				$zip->addFile( $file, $rel_file );
			}

			$zip->close();
		} else {
			throw new Exception( "Could not create zip file at '$zip_file'" );
		}

		return $zip_file;
	}

	/**
	 * Sends an email in html and plain encodings with a file attachment.
	 *
	 * @param array $args Arguments associative array.
	 *
	 *      'to' (string)
	 *      'from' (string)
	 *      'subject' (optional string)
	 *      'message' (HTML string)
	 *      'plain' (optional plain string)
	 *      'file' (optional file path of the attachment)
	 *
	 * @see http://webcheatsheet.com/php/send_email_text_html_attachment.php
	 * @throws Exception File read.
	 */
	public static function email_file( array $args ): bool {
		$to      = self::set_default( $args['to'] );
		$from    = self::set_default( $args['from'] );
		$subject = self::set_default( $args['subject'], '' );
		$message = self::set_default( $args['message'], '' );
		$plain   = self::set_default( $args['plain'], '' );
		$file    = self::set_default( $args['file'] );

		// MIME.
		$random_hash    = md5( gmdate( 'r', time() ) );
		$boundary_mixed = 'PHP-mixed-' . $random_hash;
		$boundary_alt   = 'PHP-alt-' . $random_hash;
		$charset        = 'UTF-8';
		$bits           = '8bit';

		// Headers.
		$headers  = 'MIME-Version: 1.0';
		$headers .= "Reply-To: $to\r\n";
		if ( null !== $from ) {
			$headers .= "From: $from\r\n";
		}

		$headers .= "Content-Type: multipart/mixed; boundary=$boundary_mixed";

		if ( null !== $file ) {
			$info      = pathinfo( $file );
			$filename  = $info['filename'];
			$extension = $info['extension'];
			$contents  = file_get_contents( $file ); // TODO:  Replace with wp_filesystem.
			if ( false === $contents ) {
				throw new Exception( "File contents of '$file' could not be read" );
			}

			$chunks     = chunk_split( base64_encode( $contents ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
			$attachment = <<<EOT
--$boundary_mixed
Content-Type: application/$extension; name=$filename.$extension
Content-Transfer-Encoding: base64
Content-Disposition: attachment

$chunks
EOT;
		} else {
			$attachment = '';
		}

		$body = <<<EOT
--$boundary_mixed
Content-Type: multipart/alternative; boundary=$boundary_alt

--$boundary_alt
Content-Type: text/plain; charset="$charset"
Content-Transfer-Encoding: $bits

$plain

--$boundary_alt
Content-Type: text/html; charset="$charset"
Content-Transfer-Encoding: $bits

$message
--$boundary_alt--

$attachment

--$boundary_mixed--
EOT;

		return wp_mail( $to, $subject, $body, $headers );
	}

	/**
	 * Get Files.
	 *
	 * @param string $path A directory.
	 * @param array  $args Argument array.
	 *                 hidden: If true, hidden files beginning with a dot will be included
	 *                 ignoreRef: If true, . and .. are ignored
	 *                 recursive: If true, this function is recursive
	 *                 ignore: An array of paths to ignore
	 *
	 * @return array Files in the directory.
	 */
	public static function get_files( string $path, $args = array() ): array {
		$hidden     = self::set_default( $args['hidden'], true );
		$ignore_ref = self::set_default( $args['ignoreRef'], true );
		$recursive  = self::set_default( $args['recursive'], false );
		$ignore     = self::set_default( $args['ignore'], null );

		$ignore_map = array();
		if ( $ignore ) {
			foreach ( $ignore as $i ) {
				if ( is_dir( $i ) ) {
					$i = self::path_slash( $i );
				}
				$ignore_map[ $i ] = true;
			}
		}

		$files = glob( $path . '*', GLOB_MARK );
		if ( $hidden ) {
			$files = array_merge( $files, glob( $path . '.*', GLOB_MARK ) );
		}
		if ( $ignore_ref || $ignore ) {
			$result = array();
			$count  = count( $files );

			for ( $i = 0; $i < $count; $i++ ) {
				$file = $files[ $i ];
				if ( ! isset( $ignore_map[ $file ] ) && ( ! $ignore_ref || ( '.' !== basename( $file ) && '..' !== basename( $file ) ) ) ) {
					$result[] = $file;
					if ( $recursive && is_dir( $file ) ) {
						$result = array_merge( $result, self::get_files( $file, $args ) );
					}
				}
			}
		} else {
			$result = $files;
		}
		return $result;
	}

	/**
	 * Delete directory.
	 *
	 * @param string $path Path.
	 *
	 * @throws Exception InvalidArgumentException.
	 */
	public static function delete_dir( string $path ) {
		if ( ! is_dir( $path ) ) {
			throw new InvalidArgumentException( "delete_dir: $path is not a directory" );
		}

		if ( substr( $path, strlen( $path ) - 1, 1 ) !== '/' ) {
			$path .= '/';
		}

		$files = self::get_files( $path );
		foreach ( $files as $file ) {
			if ( is_dir( $file ) ) {
				self::delete_dir( $file );
			} else {
				unlink( $file );
			}
		}

		rmdir( $path );
	}

	/**
	 * Copy directory.
	 *
	 * @param string $src Source.
	 * @param string $dst Destination.
	 * @param mixed  $mkdir Make dir.
	 *
	 * @throws InvalidArgumentException Invalid argument.
	 */
	public static function copy_dir( $src, $dst, $mkdir = null ) {

		// http://stackoverflow.com/questions/2050859.
		if ( ! is_dir( $src ) ) {
			throw new InvalidArgumentException( "copy_dir: $src is not a directory" );
		}

		$dir = opendir( $src );

		if ( null !== $mkdir ) {
			call_user_func( $mkdir, $dst );
		} else {
			mkdir( $dst, 0777, true );
		}

		$file = readdir( $dir );
		while ( false !== $file ) {
			if ( ( '.' !== $file ) && ( '..' !== $file ) ) {
				if ( is_dir( $src . '/' . $file ) ) {
					self::copy_dir( $src . '/' . $file, $dst . '/' . $file );
				} else {
					copy( $src . '/' . $file, $dst . '/' . $file );
				}
			}
		}

		closedir( $dir );
	}

	/**
	 * Supports arrays in the values.
	 *
	 * @param array $array Array to flip.
	 *
	 * @return array
	 */
	public static function array_flip( array $array ): array {
		$result = array();

		foreach ( $array as $k => $v ) {
			if ( is_array( $v ) ) {
				foreach ( $v as $u ) {
					self::internal_array_flip( $result, $k, $u );
				}
			} else {
				self::internal_array_flip( $result, $k, $v );
			}
		}

		return $result;
	}

	/**
	 * Private array flip.
	 *
	 * @param array $array Array.
	 * @param mixed $k Key.
	 * @param mixed $v Value.
	 */
	private static function internal_array_flip( &$array, $k, $v ) {
		if ( is_string( $v ) || is_int( $v ) ) {
			$array[ $v ] = $k;
		} else {
			trigger_error( 'Values must be STRING or INTEGER', E_USER_WARNING ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
		}
	}

	/**
	 * Detects if device is touchscreen or mobile.
	 *
	 * @return bool|null
	 */
	public static function is_touch(): ?bool {

		// Only detect once.
		if ( null !== self::$touchscreen ) {
			return self::$touchscreen;
		}

		$devices = self::lines( URVANOV_SYNTAX_HIGHLIGHTER_TOUCH_FILE, 'lw' );
		if ( false !== $devices ) {
			if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
				return false;
			}

			// Create array of device strings from file.
			$user_agent        = strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) );
			self::$touchscreen = ( self::strposa( $user_agent, $devices ) !== false );

			return self::$touchscreen;
		} else {
			UrvanovSyntaxHighlighterLog::syslog( 'Error occurred when trying to identify touchscreen devices' );
		}
	}

	/**
	 * Removes duplicates in array, ensures they are all strings.
	 *
	 * @param array $array Array.
	 *
	 * @return array
	 */
	public static function array_unique_str( array $array ): array {
		if ( ! is_array( $array ) || empty( $array ) ) {
			return array();
		}

		$count = count( $array );
		for ( $i = 0; $i < $count; $i++ ) {
			$array[ $i ] = strval( $array[ $i ] );
		}

		return array_unique( $array );
	}

	/**
	 * Same as array_key_exists, but returns the key when exists, else FALSE;
	 *
	 * @param mixed $key   Key.
	 * @param array $array Array.
	 *
	 * @return mixed
	 */
	public static function array_key_exists( $key, array $array ) {
		if ( ! is_array( $array ) || empty( $array ) || ! is_string( $key ) || empty( $key ) ) {
			false;
		}

		if ( array_key_exists( $key, $array ) ) {
			return $array[ $key ];
		}
	}

	/**
	 * Performs explode() on a string with the given delimiter and trims all whitespace.
	 *
	 * @param string $str       String.
	 * @param string $delimiter Delimiter.
	 *
	 * @return false|string[]
	 */
	public static function trim_e( string $str, $delimiter = ',' ) {
		if ( is_string( $delimiter ) ) {
			$str = trim( preg_replace( '|\s*(?:' . preg_quote( $delimiter ) . ')\s*|', $delimiter, $str ) ); // phpcs:ignore
			return explode( $delimiter, $str );
		}
		return $str;
	}

	/**
	 * Creates an array of integers based on a given range string of format 'int - int'.
	 * Eg. range_str('2 - 5');.
	 *
	 * @param string $str String.
	 *
	 * @return array|false
	 */
	public static function range_str( string $str ) {
		preg_match( '#(\d+)\s*-\s*(\d+)#', $str, $matches );
		if ( 3 === count( $matches ) ) {
			return range( $matches[1], $matches[2] );
		}

		return false;
	}

	/**
	 * Creates an array out of a single range string (e.i 'x-y').
	 *
	 * @param string $str String.
	 *
	 * @return array|false
	 */
	public static function range_str_single( string $str ) {
		$match = preg_match( '#(\d+)(?:\s*-\s*(\d+))?#', $str, $matches );

		if ( $match > 0 ) {
			if ( empty( $matches[2] ) ) {
				$matches[2] = $matches[1];
			}
			if ( $matches[1] <= $matches[2] ) {
				return array( $matches[1], $matches[2] );
			}
		}

		return false;
	}

	/**
	 * Sets a variable to a string if valid.
	 *
	 * @param mixed  $var Variable.
	 * @param string $str String.
	 * @param bool   $escape Escape.
	 *
	 * @return bool
	 */
	public static function str( &$var, string $str, $escape = true ): bool {
		if ( is_string( $str ) ) {
			$var = ( true === $escape ? self::htmlentities( $str ) : $str );

			return true;
		}

		return false;
	}

	/**
	 * Converts all special characters to entities.
	 *
	 * @param string $str String.
	 *
	 * @return string
	 */
	public static function htmlentities( string $str ): string {
		return htmlentities( $str, ENT_COMPAT, 'UTF-8' );
	}

	/**
	 * HTML decode entity.
	 *
	 * @param string $str String.
	 *
	 * @return string
	 */
	public static function html_entity_decode( string $str ): string {
		return html_entity_decode( $str, ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Converts <, >, & into entities.
	 *
	 * @param string $str String.
	 *
	 * @return string
	 */
	public static function htmlspecialchars( string $str ): string {
		return htmlspecialchars( $str, ENT_NOQUOTES, 'UTF-8' );
	}

	/**
	 * Sets a variable to an int if valid.
	 *
	 * @param mixed $var Variable.
	 * @param mixed $num Number.
	 *
	 * @return bool
	 */
	public static function num( &$var, $num ): bool {
		if ( is_numeric( $num ) ) {
			$var = intval( $num );

			return true;
		}

		return false;
	}

	/**
	 * Sets a variable to an array if valid.
	 *
	 * @param mixed $var Variable.
	 * @param array $array Array.
	 *
	 * @return bool
	 */
	public static function arr( &$var, array $array ): bool {
		if ( is_array( $array ) ) {
			$var = $array;

			return true;
		}

		return false;
	}

	/**
	 * Sets a variable to an array if valid.
	 *
	 * @param mixed $var   Variable.
	 * @param array $array Array.
	 * @param bool  $false Boolean.
	 *
	 * @return false|mixed
	 */
	public static function set_array( $var, array $array, $false = false ) {
		return $array[ $var ] ?? $false;
	}

	/**
	 * Sets a variable to null if not set.
	 *
	 * @param mixed $var Variable.
	 * @param bool  $false Switch.
	 */
	public static function set_var( &$var, $false = null ) {
		$var = $var ?? $false;
	}

	/**
	 * Sets a variable to null if not set.
	 *
	 * @param mixed $var Variable.
	 * @param mixed $default Default.
	 *
	 * @return mixed|null
	 */
	public static function set_default( &$var, $default = null ) {
		return $var ?? $default;
	}

	/**
	 * @param      $var
	 * @param null $default
	 *
	 * @return mixed|null
	 */
	public static function set_default_null( $var, $default = null ) {
		return $var !== null ? $var : $default;
	}

	// Thanks, http://www.php.net/manual/en/function.str-replace.php#102186

	/**
	 * @param $str_pattern
	 * @param $str_replacement
	 * @param $string
	 *
	 * @return array|mixed|string|string[]
	 */
	function str_replace_once( $str_pattern, $str_replacement, $string ) {
		if ( strpos( $string, $str_pattern ) !== false ) {
			$occurrence = strpos( $string, $str_pattern );
			return substr_replace( $string, $str_replacement, strpos( $string, $str_pattern ), strlen( $str_pattern ) );
		}
		return $string;
	}

	// Removes non-numeric chars in string

	/**
	 * @param      $str
	 * @param bool $return_zero
	 *
	 * @return array|string|string[]|null
	 */
	public static function clean_int( $str, $return_zero = true ) {
		$str = preg_replace( '#[^\d]#', '', $str );
		if ( $return_zero ) {
			// If '', then returns 0
			return strval( intval( $str ) );
		} else {
			// Might be ''
			return $str;
		}
	}

	// Replaces whitespace with hypthens

	/**
	 * @param $str
	 *
	 * @return array|string|string[]|null
	 */
	public static function space_to_hyphen( $str ) {
		return preg_replace( '#\s+#', '-', $str );
	}

	// Replaces hypthens with spaces

	/**
	 * @param $str
	 *
	 * @return array|string|string[]|null
	 */
	public static function hyphen_to_space( $str ) {
		return preg_replace( '#-#', ' ', $str );
	}

	// Remove comments with /* */, // or #, if they occur before any other char on a line

	/**
	 * @param $str
	 *
	 * @return array|string|string[]|null
	 */
	public static function clean_comments( $str ) {
		$comment_pattern = '#(?:^\s*/\*.*?^\s*\*/)|(?:^(?!\s*$)[\s]*(?://|\#)[^\r\n]*)#ms';
		$str             = preg_replace( $comment_pattern, '', $str );
		return $str;
	}

	// Convert to title case and replace underscores with spaces

	/**
	 * @param $str
	 *
	 * @return string
	 */
	public static function ucwords( $str ) {
		$str = strval( $str );
		$str = str_replace( '_', ' ', $str );
		return ucwords( $str );
	}

	// Escapes regex characters as literals

	/**
	 * @param $regex
	 *
	 * @return string
	 */
	public static function esc_regex( $regex ) {
		return /*htmlspecialchars(*/
			preg_quote( $regex ); /* , ENT_NOQUOTES)*/
	}

	// Escapes hash character as literals

	/**
	 * @param $regex
	 *
	 * @return array|false|string|string[]|null
	 */
	public static function esc_hash( $regex ) {
		if ( is_string( $regex ) ) {
			return preg_replace( '|(?<!\\\\)#|', '\#', $regex );
		} else {
			return false;
		}
	}

	// Ensure all parenthesis are atomic to avoid conflicting with element matches

	/**
	 * @param $regex
	 *
	 * @return array|string|string[]|null
	 */
	public static function esc_atomic( $regex ) {
		return preg_replace( '#(?<!\\\\)\((?!\?)#', '(?:', $regex );
	}

	// Returns the current HTTP URL

	/**
	 * @return string
	 */
	public static function current_url() {
		$p = self::isSecure() ? 'https://' : 'http://';
		return $p . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	// Removes Urvanov Syntax Highlighter plugin path from absolute path

	/**
	 * @param $url
	 *
	 * @return array|mixed|string|string[]
	 */
	public static function path_rel( $url ) {
		if ( is_string( $url ) ) {
			return str_replace( URVANOV_SYNTAX_HIGHLIGHTER_ROOT_PATH, '/', $url );
		}
		return $url;
	}

	// Returns path according to detected use of forwardslash/backslash
	// Deprecated from regular use after v.1.1.1
	/**
	 * @param $path
	 * @param $detect
	 *
	 * @return array|string|string[]
	 */
	public static function path( $path, $detect ) {
		$slash = self::detect_slash( $detect );
		return str_replace( array( '\\', '/' ), $slash, $path );
	}

	// Detect which kind of slash is being used in a path

	/**
	 * @param $path
	 *
	 * @return string
	 */
	public static function detect_slash( $path ) {
		if ( strpos( $path, '\\' ) ) {
			// Windows
			return $slash = '\\';
		} else {
			// UNIX
			return $slash = '/';
		}
	}

	// Returns path using forward slashes

	/**
	 * @param $url
	 *
	 * @return array|string|string[]
	 */
	public static function pathf( $url ) {
		return str_replace( '\\', '/', trim( strval( $url ) ) );
	}

	// Returns path using back slashes

	/**
	 * @param $url
	 *
	 * @return array|string|string[]
	 */
	public static function pathb( $url ) {
		return str_replace( '/', '\\', trim( strval( $url ) ) );
	}

	// returns 'true' or 'false' depending on whether this PHP file was served over HTTPS

	/**
	 * @return bool
	 */
	public static function isSecure() {
		// From https://core.trac.wordpress.org/browser/tags/4.0.1/src/wp-includes/functions.php
		if ( isset( $_SERVER['HTTPS'] ) ) {
			if ( 'on' == strtolower( $_SERVER['HTTPS'] ) ) {
				return true;
			}
			if ( '1' == $_SERVER['HTTPS'] ) {
				return true;
			}
		} elseif ( isset( $_SERVER['SERVER_PORT'] ) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
			return true;
		}
		return false;
	}

	/**
	 * @param $haystack
	 * @param $needle
	 *
	 * @return bool
	 */
	public static function startsWith( $haystack, $needle ) {
		return substr( $haystack, 0, strlen( $needle ) ) === $needle;
	}

	// Append either forward slash or backslash based on environment to paths

	/**
	 * @param $path
	 *
	 * @return array|string|string[]
	 */
	public static function path_slash( $path ) {
		$path = self::pathf( $path );
		if ( ! empty( $path ) && ! preg_match( '#\/$#', $path ) ) {
			$path .= '/';
		}
		if ( self::startsWith( $path, 'http://' ) && self::isSecure() ) {
			$path = str_replace( 'http://', 'https://', $path );
		}
		return $path;
	}

	/**
	 * @param $path
	 *
	 * @return array|string|string[]|null
	 */
	public static function path_slash_remove( $path ) {
		return preg_replace( '#\/+$#', '', $path );
	}

	// Append a forward slash to a path if needed

	/**
	 * @param $url
	 *
	 * @return array|string|string[]
	 */
	public static function url_slash( $url ) {
		$url = self::pathf( $url );
		if ( ! empty( $url ) && ! preg_match( '#\/$#', $url ) ) {
			$url .= '/';
		}
		if ( self::startsWith( $url, 'http://' ) && self::isSecure() ) {
			$url = str_replace( 'http://', 'https://', $url );
		}
		return $url;
	}

	// Removes extension from file path

	/**
	 * @param $path
	 *
	 * @return array|string|string[]|null
	 */
	public static function path_rem_ext( $path ) {
		$path = self::pathf( $path );
		return preg_replace( '#\.\w+$#m', '', $path );
	}

	// Shorten a URL into a string of given length, used to identify a URL uniquely

	/**
	 * @param $url
	 * @param $length
	 *
	 * @return array|string|string[]|null
	 */
	public static function shorten_url_to_length( $url, $length ) {
		if ( $length < 1 ) {
			return '';
		}
		$url = preg_replace( '#(^\w+://)|([/\.])#si', '', $url );
		if ( strlen( $url ) > $length ) {
			$diff      = strlen( $url ) - $length;
			$rem       = floor( strlen( $url ) / $diff );
			$rem_count = 0;
			for ( $i = $rem - 1; $i < strlen( $url ) && $rem_count < $diff; $i = $i + $rem ) {
				$url[ $i ] = '.';
				$rem_count++;
			}
			$url = preg_replace( '#\.#s', '', $url );
		}
		return $url;
	}

	// Creates a unique ID from a string

	/**
	 * @return string
	 */
	public static function get_var_str() {
		$get_vars = array();
		foreach ( $_GET as $get => $val ) {
			$get_vars[] = $get . '=' . $val;
		}
		return implode( $get_vars, '&' );
	}

	// Creates a unique ID from a string

	/**
	 * @param $str
	 *
	 * @return string
	 */
	public static function str_uid( $str ) {
		$uid = 0;
		for ( $i = 1; $i < strlen( $str ); $i++ ) {
			$uid += round( ord( $str[ $i ] ) * ( $i / strlen( $str ) ), 2 ) * 100;
		}
		return strval( dechex( strlen( $str ) ) ) . strval( dechex( $uid ) );
	}


	// strpos with an array of $needles

	/**
	 * @param       $haystack
	 * @param       $needles
	 * @param false $insensitive
	 *
	 * @return false|int
	 */
	public static function strposa( $haystack, $needles, $insensitive = false ) {
		if ( is_array( $needles ) ) {
			foreach ( $needles as $str ) {
				if ( is_array( $str ) ) {
					$pos = self::strposa( $haystack, $str, $insensitive );
				} else {
					$pos = $insensitive ? stripos( $haystack, $str ) : strpos( $haystack, $str );
				}
				if ( $pos !== false ) {
					return $pos;
				}
			}
			return false;
		} else {
			return strpos( $haystack, $needles );
		}
	}

	// tests if $needle is equal to any strings in $haystack

	/**
	 * @param      $needle
	 * @param      $haystack
	 * @param bool $case_insensitive
	 *
	 * @return bool
	 */
	public static function str_equal_array( $needle, $haystack, $case_insensitive = true ) {
		if ( ! is_string( $needle ) || ! is_array( $haystack ) ) {
			return false;
		}
		if ( $case_insensitive ) {
			$needle = strtolower( $needle );
		}
		foreach ( $haystack as $hay ) {
			if ( ! is_string( $hay ) ) {
				continue;
			}
			if ( $case_insensitive ) {
				$hay = strtolower( $hay );
			}
			if ( $needle == $hay ) {
				return true;
			}
		}
		return false;
	}

	// Support for singular and plural string variations

	/**
	 * @param      $int
	 * @param      $singular
	 * @param null $plural
	 *
	 * @return string
	 */
	public static function spnum( $int, $singular, $plural = null ) {
		if ( ! is_int( $int ) || ! is_string( $singular ) ) {
			$int      = intval( $int );
			$singular = strval( $singular );
		}
		if ( $plural == null || ! is_string( $plural ) ) {
			$plural = $singular . 's';
		}
		return $int . ' ' . ( ( $int == 1 ) ? $singular : $plural );
	}

	// Turn boolean into Yes/No

	/**
	 * @param $bool
	 *
	 * @return string
	 */
	public static function bool_yn( $bool ) {
		return $bool ? 'Yes' : 'No';
	}

	// String to boolean, default decides what boolean value to return when not found

	/**
	 * @param      $str
	 * @param bool $default
	 *
	 * @return bool
	 */
	public static function str_to_bool( $str, $default = true ) {
		$str = self::tlower( $str );
		if ( $default === false ) {
			if ( $str == 'true' || $str == 'yes' || $str == '1' ) {
				return true;
			} else {
				return false;
			}
		} else {
			if ( $str == 'false' || $str == 'no' || $str == '0' ) {
				return false;
			} else {
				return true;
			}
		}
	}

	/**
	 * @param       $bool
	 * @param false $strict
	 *
	 * @return string
	 */
	public static function bool_to_str( $bool, $strict = false ) {
		if ( $strict ) {
			return $bool === true ? 'true' : 'false';
		} else {
			return $bool ? 'true' : 'false';
		}
	}

	/**
	 * @param $str
	 *
	 * @return string
	 */
	public static function tlower( $str ) {
		return trim( strtolower( $str ) );
	}

	// Escapes $ and \ from the replacement to avoid becoming a backreference

	/**
	 * @param     $pattern
	 * @param     $replacement
	 * @param     $subject
	 * @param int $limit
	 * @param int $count
	 *
	 * @return array|string|string[]|null
	 */
	public static function preg_replace_escape_back( $pattern, $replacement, $subject, $limit = -1, &$count = 0 ) {
		return preg_replace( $pattern, self::preg_escape_back( $replacement ), $subject, $limit, $count );
	}

	// Escape backreferences from string for use with regex

	/**
	 * @param $string
	 *
	 * @return array|string|string[]|null
	 */
	public static function preg_escape_back( $string ) {
		// Replace $ with \$ and \ with \\
		$string = preg_replace( '#(\\$|\\\\)#', '\\\\$1', $string );
		return $string;
	}

	// Detect if on a Mac or PC

	/**
	 * @param false $default
	 *
	 * @return bool
	 */
	public static function is_mac( $default = false ) {
		$user = $_SERVER['HTTP_USER_AGENT'];
		if ( stripos( $user, 'macintosh' ) !== false ) {
			return true;
		} elseif ( stripos( $user, 'windows' ) !== false || stripos( $user, 'linux' ) !== false ) {
			return false;
		} else {
			return $default === true;
		}
	}

	// Decodes WP html entities

	/**
	 * @param $str
	 *
	 * @return array|mixed|string|string[]
	 */
	public static function html_entity_decode_wp( $str ) {
		if ( ! is_string( $str ) || empty( $str ) ) {
			return $str;
		}
		// http://www.ascii.cl/htmlcodes.htm
		$wp_entities = array( '&#8216;', '&#8217;', '&#8218;', '&#8220;', '&#8221;' );
		$wp_replace  = array( '\'', '\'', ',', '"', '"' );
		$str         = str_replace( $wp_entities, $wp_replace, $str );
		return $str;
	}

	// Constructs an html element
	// If $content = FALSE, then element is closed
	/**
	 * @param       $name
	 * @param null  $content
	 * @param array $attributes
	 *
	 * @return string
	 */
	public static function html_element( $name, $content = null, $attributes = array() ) {
		$atts = self::html_attributes( $attributes );
		$tag  = "<$name $atts";
		$tag .= $content === false ? '/>' : ">$content</$name>";
		return $tag;
	}

	/**
	 * @param        $attributes
	 * @param string $assign
	 * @param string $quote
	 * @param string $glue
	 *
	 * @return string
	 */
	public static function html_attributes( $attributes, $assign = '=', $quote = '"', $glue = ' ' ) {
		$atts = '';
		foreach ( $attributes as $k => $v ) {
			$atts .= $k . $assign . $quote . $v . $quote . $glue;
		}
		return $atts;
	}

	// Strips only the given tags in the given HTML string.

	/**
	 * @param $html
	 * @param $tags
	 *
	 * @return array|mixed|string|string[]|null
	 */
	public static function strip_tags_blacklist( $html, $tags ) {
		foreach ( $tags as $tag ) {
			$regex = '#<\s*\b' . $tag . '\b[^>]*>.*?<\s*/\s*' . $tag . '\b[^>]*>?#msi';
			$html  = preg_replace( $regex, '', $html );
		}
		return $html;
	}

	// Strips the given attributes found in the given HTML string.

	/**
	 * @param $html
	 * @param $atts
	 *
	 * @return array|mixed|string|string[]|null
	 */
	public static function strip_attributes( $html, $atts ) {
		foreach ( $atts as $att ) {
			$regex = '#\b' . $att . '\b(\s*=\s*[\'"][^\'"]*[\'"])?(?=[^<]*>)#msi';
			$html  = preg_replace( $regex, '', $html );
		}
		return $html;
	}

	// Strips all event attributes on DOM elements (prefixe with "on").

	/**
	 * @param $html
	 *
	 * @return array|string|string[]|null
	 */
	public static function strip_event_attributes( $html ) {
		$regex = '#\bon\w+\b(\s*=\s*[\'"][^\'"]*[\'"])?(?=[^<]*>)#msi';
		return preg_replace( $regex, '', $html );
	}

}



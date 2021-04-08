<?php
/**
 * Logging Class
 *
 * @package   Crayon Syntax Highlighter
 * @author    Fedor Urvanov, Aram Kocharyan
 * @copyright Copyright 2013, Aram Kocharyan
 * @link      https://urvanov.ru
 */

defined( 'ABSPATH' ) || exit;

require_once URVANOV_SYNTAX_HIGHLIGHTER_ROOT_PATH . 'class-urvanov-syntax-highlighter-settings.php';

/**
 * Manages logging variable values to the log file.
 *
 * Class UrvanovSyntaxHighlighterLog
 */
class UrvanovSyntaxHighlighterLog {

	/**
	 * File.
	 *
	 * @var null
	 */
	private static $file = null;

	/**
	 * Logs a variable value to a log file.
	 *
	 * @param mixed  $var Variable.
	 * @param string $title Title.
	 * @param bool   $trim_url URL.
	 *
	 * @return string|void
	 */
	public static function log( $var = null, $title = '', $trim_url = true ) {
		if ( null === $var ) {

			// Return log.
			$log = UrvanovSyntaxHighlighterUtil::file( URVANOV_SYNTAX_HIGHLIGHTER_LOG_FILE );
			if ( false !== $log ) {
				return $log;
			} else {
				return '';
			}
		} else {
			try {
				if ( null === self::$file ) {
					self::$file = fopen( URVANOV_SYNTAX_HIGHLIGHTER_LOG_FILE, 'a+' ); // TODO:  switch to wp_filesystem.

					if ( self::$file ) {
						$header = URVANOV_SYNTAX_HIGHLIGHTER_NL . '[Crayon Syntax Highlighter Log Entry - ' . gmdate( 'g:i:s A - d M Y' ) . ']' . URVANOV_SYNTAX_HIGHLIGHTER_NL . URVANOV_SYNTAX_HIGHLIGHTER_NL;
						fwrite( self::$file, $header );
					} else {
						return;
					}
				}
				// Capture variable dump.
				$buffer = trim( wp_strip_all_tags( var_export( $var, true ) ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
				$title  = ( ! empty( $title ) ? " [$title]" : '' );

				// Remove absolute path to plugin directory from buffer.
				if ( $trim_url ) {
					$buffer = UrvanovSyntaxHighlighterUtil::path_rel( $buffer );
				}
				$write = $title . ' ' . $buffer . URVANOV_SYNTAX_HIGHLIGHTER_NL; /* . URVANOV_SYNTAX_HIGHLIGHTER_LINE . URVANOV_SYNTAX_HIGHLIGHTER_NL*/

				// If we exceed max file size, truncate file first.
				if ( filesize( URVANOV_SYNTAX_HIGHLIGHTER_LOG_FILE ) + strlen( $write ) > URVANOV_SYNTAX_HIGHLIGHTER_LOG_MAX_SIZE ) {
					ftruncate( self::$file, 0 );
					fwrite(
						self::$file,
						'The log has been truncated since it exceeded ' . URVANOV_SYNTAX_HIGHLIGHTER_LOG_MAX_SIZE .
						' bytes.' . URVANOV_SYNTAX_HIGHLIGHTER_NL . /*URVANOV_SYNTAX_HIGHLIGHTER_LINE .*/ URVANOV_SYNTAX_HIGHLIGHTER_NL
					);
				}

				clearstatcache();

				fwrite( self::$file, $write, URVANOV_SYNTAX_HIGHLIGHTER_LOG_MAX_SIZE );
			} catch ( Exception $e ) {
				// Ignore fatal errors during logging.
			}
		}
	}

	/**
	 * Logs system-wide only if global settings permit.
	 *
	 * @param mixed  $var Variable.
	 * @param string $title Title.
	 * @param bool   $trim_url URL.
	 */
	public static function syslog( $var = null, $title = '', $trim_url = true ) {
		if ( Urvanov_Syntax_Highlighter_Global_Settings::val( Urvanov_Syntax_Highlighter_Settings::ERROR_LOG_SYS ) ) {
			$title = ( empty( $title ) ) ? 'SYSTEM LOG' : $title;

			self::log( $var, $title, $trim_url );
		}
	}

	/**
	 * Debug.
	 *
	 * @param Mixed  $var Variable.
	 * @param string $title Title.
	 * @param bool   $trim_url URL.
	 */
	public static function debug( $var = null, $title = '', $trim_url = true ) {
		if ( URVANOV_SYNTAX_HIGHLIGHTER_DEBUG ) {
			$title = ( empty( $title ) ) ? 'DEBUG' : $title;

			self::log( $var, $title, $trim_url );
		}
	}

	/**
	 * Clear.
	 */
	public static function clear() {
		if ( ! unlink( URVANOV_SYNTAX_HIGHLIGHTER_LOG_FILE ) ) {

			// Will result in nothing if we can't log.
			self::log( 'The log could not be cleared', 'Log Clear' );
		}

		self::$file = null; // Remove file handle.
	}

	/**
	 * Email submission.
	 *
	 * @param string $to Email recipient.
	 * @param string $from Email sender.
	 */
	public static function email( string $to, $from = null ) {
		$log_contents = UrvanovSyntaxHighlighterUtil::file( URVANOV_SYNTAX_HIGHLIGHTER_LOG_FILE );

		if ( false !== $log_contents ) {
			$headers = $from ? 'From: ' . $from : '';
			$result  = wp_mail( $to, 'Crayon Syntax Highlighter Log', $log_contents, $headers );
			self::log( 'The log was emailed to the admin.', 'Log Email' );
		} else {

			// Will result in nothing if we can't email.
			self::log( "The log could not be emailed to $to.", 'Log Email' );
		}
	}
}

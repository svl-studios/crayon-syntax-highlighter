<?php
/**
 * Font Class.
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
 * Manages fonts once they are loaded.
 *
 * Class Urvanov_Syntax_Highlighter_Fonts
 */
class Urvanov_Syntax_Highlighter_Fonts extends Urvanov_Syntax_Highlighter_User_Resource_Collection {

	/**
	 * Default font.
	 */
	const DEFAULT_FONT = 'monaco';

	/**
	 * Default front name.
	 */
	const DEFAULT_FONT_NAME = 'Monaco';

	/**
	 * Urvanov_Syntax_Highlighter_Fonts constructor.
	 */
	public function __construct() {
		$this->set_default( self::DEFAULT_FONT, self::DEFAULT_FONT_NAME );
		$this->directory( URVANOV_SYNTAX_HIGHLIGHTER_FONT_PATH );
		$this->relative_directory( URVANOV_SYNTAX_HIGHLIGHTER_FONT_DIR );
		$this->extension( 'css' );

		UrvanovSyntaxHighlighterLog::debug( 'Setting font directories' );
		$upload = Urvanov_Syntax_Highlighter_Global_Settings::upload_path();

		if ( $upload ) {
			$this->user_directory( $upload . URVANOV_SYNTAX_HIGHLIGHTER_FONT_DIR );
			if ( ! is_dir( $this->user_directory() ) ) {
				Urvanov_Syntax_Highlighter_Global_Settings::mkdir( $this->user_directory() );
				UrvanovSyntaxHighlighterLog::debug( $this->user_directory(), 'FONT USER DIR' );
			}
		} else {
			UrvanovSyntaxHighlighterLog::syslog( 'Upload directory is empty: ' . $upload . ' cannot load fonts.' );
		}

		UrvanovSyntaxHighlighterLog::debug( $this->directory() );
		UrvanovSyntaxHighlighterLog::debug( $this->user_directory() );
	}
}

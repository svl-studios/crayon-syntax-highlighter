<?php
/**
 * Theme Class
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
 * Manages themes once they are loaded.
 *
 * Class Urvanov_Syntax_Highlighter_Themes
 */
class Urvanov_Syntax_Highlighter_Themes extends Urvanov_Syntax_Highlighter_User_Resource_Collection {

	/**
	 * Default theme.
	 */
	const DEFAULT_THEME = 'classic';

	/**
	 * Default theme name.
	 */
	const DEFAULT_THEME_NAME = 'Classic';

	/**
	 * Prefix.
	 */
	const CSS_PREFIX = '.crayon-theme-';

	/**
	 * Printed themes.
	 *
	 * @var array
	 */
	private $printed_themes = array();

	/**
	 * Urvanov_Syntax_Highlighter_Themes constructor.
	 */
	public function __construct() {
		$this->set_default( self::DEFAULT_THEME, self::DEFAULT_THEME_NAME );
		$this->directory( URVANOV_SYNTAX_HIGHLIGHTER_THEME_PATH );
		$this->relative_directory( URVANOV_SYNTAX_HIGHLIGHTER_THEME_DIR );
		$this->extension( 'css' );

		UrvanovSyntaxHighlighterLog::debug( 'Setting theme directories' );

		$upload = Urvanov_Syntax_Highlighter_Global_Settings::upload_path();
		if ( $upload ) {
			$this->user_directory( $upload . URVANOV_SYNTAX_HIGHLIGHTER_THEME_DIR );

			if ( ! is_dir( $this->user_directory() ) ) {
				Urvanov_Syntax_Highlighter_Global_Settings::mkdir( $this->user_directory() );
				UrvanovSyntaxHighlighterLog::debug( $this->user_directory(), 'THEME USER DIR' );
			}
		} else {
			UrvanovSyntaxHighlighterLog::syslog( 'Upload directory is empty: ' . $upload . ' cannot load themes.' );
		}

		UrvanovSyntaxHighlighterLog::debug( $this->directory() );
		UrvanovSyntaxHighlighterLog::debug( $this->user_directory() );
	}

	/**
	 * Filename.
	 *
	 * @param mixed $id ID.
	 * @param mixed $user USer.
	 *
	 * @return string
	 */
	public function filename( $id, $user = null ): string {
		return UrvanovSyntaxHighlighterUtil::path_slash( $id ) . parent::filename( $id, $user );
	}
}

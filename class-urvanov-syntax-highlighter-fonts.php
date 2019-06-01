<?php
require_once ('global.php');
require_once (URVANOV_SYNTAX_HIGHLIGHTER_RESOURCE_PHP);

/* Manages fonts once they are loaded. */
class Urvanov_Syntax_Highlighter_Fonts extends CrayonUserResourceCollection {
	// Properties and Constants ===============================================

	const DEFAULT_FONT = 'monaco';
	const DEFAULT_FONT_NAME = 'Monaco';

	// Methods ================================================================

	function __construct() {
		$this->set_default(self::DEFAULT_FONT, self::DEFAULT_FONT_NAME);
        $this->directory(URVANOV_SYNTAX_HIGHLIGHTER_FONT_PATH);
        $this->relative_directory(URVANOV_SYNTAX_HIGHLIGHTER_FONT_DIR);
        $this->extension('css');

        CrayonLog::debug("Setting font directories");
        $upload = CrayonGlobalSettings::upload_path();
        if ($upload) {
            $this->user_directory($upload . URVANOV_SYNTAX_HIGHLIGHTER_FONT_DIR);
            if (!is_dir($this->user_directory())) {
                CrayonGlobalSettings::mkdir($this->user_directory());
                CrayonLog::debug($this->user_directory(), "FONT USER DIR");
            }
        } else {
            CrayonLog::syslog("Upload directory is empty: " . $upload . " cannot load fonts.");
        }
        CrayonLog::debug($this->directory());
        CrayonLog::debug($this->user_directory());
	}

}
?>
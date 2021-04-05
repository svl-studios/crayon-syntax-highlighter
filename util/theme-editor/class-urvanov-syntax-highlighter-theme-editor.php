<?php

// Old name : CrayonHTMLElement
class Urvanov_Syntax_Highlighter_HTML_Element {
	public $id;
	public $class = '';
	public $tag = 'div';
	public $closed = false;
	public $contents = '';
	public $attributes = array();
	const CSS_INPUT_PREFIX = "crayon-theme-input-";

	public static $borderStyles = array(
			'none',
			'hidden',
			'dotted',
			'dashed',
			'solid',
			'double',
			'groove',
			'ridge',
			'inset',
			'outset',
			'inherit',
	);

	public function __construct( $id ) {
		$this->id = $id;
	}

	public function addClass( $class ) {
		$this->class .= ' ' . self::CSS_INPUT_PREFIX . $class;
	}

	public function addAttributes( $atts ) {
		$this->attributes = array_merge( $this->attributes, $atts );
	}

	public function attributeString() {
		$str = '';
		foreach ( $this->attributes as $k => $v ) {
			$str .= "$k=\"$v\" ";
		}

		return $str;
	}

	public function __toString() {
		return '<' . $this->tag . ' id="' . self::CSS_INPUT_PREFIX . $this->id . '" class="' . self::CSS_INPUT_PREFIX . $this->class . '" ' . $this->attributeString() . ( $this->closed ? ' />' : ' >' . $this->contents . "</$this->tag>" );
	}
}

class Urvanov_Syntax_Highlighter_HTML_Input extends Urvanov_Syntax_Highlighter_HTML_Element {
	public $name;
	public $type;

	public function __construct( $id, $name = null, $value = '', $type = 'text' ) {
		parent::__construct( $id );
		$this->tag    = 'input';
		$this->closed = true;
		if ( $name === null ) {
			$name = Urvanov_Syntax_Highlighter_User_Resource::clean_name( $id );
		}
		$this->name  = $name;
		$this->class .= $type;
		$this->addAttributes( array(
				'type'  => $type,
				'value' => $value,
		) );
	}
}

class Urvanov_Syntax_Highlighter_HTML_Select extends Urvanov_Syntax_Highlighter_HTML_Input {
	public $options;
	public $selected = null;

	public function __construct( $id, $name = null, $value = '', $options = array() ) {
		parent::__construct( $id, $name, 'select' );
		$this->tag    = 'select';
		$this->closed = false;
		$this->addOptions( $options );
	}

	public function addOptions( $options, $default = null ) {
		for ( $i = 0; $i < count( $options ); $i ++ ) {
			$key                   = $options[ $i ];
			$value                 = isset( $options[ $key ] ) ? $options[ $key ] : $key;
			$this->options[ $key ] = $value;
		}
		if ( $default === null && count( $options ) > 1 ) {
			$this->attributes['data-default'] = $options[0];
		} else {
			$this->attributes['data-default'] = $default;
		}
	}

	public function getOptionsString() {
		$str = '';
		foreach ( $this->options as $k => $v ) {
			$selected = $this->selected == $k ? 'selected="selected"' : '';
			$str      .= "<option value=\"$k\" $selected>$v</option>";
		}

		return $str;
	}

	public function __toString() {
		$this->contents = $this->getOptionsString();

		return parent::__toString();
	}
}

class Urvanov_Syntax_Highlighter_HTML_Separator extends Urvanov_Syntax_Highlighter_HTML_Element {
	public $name = '';

	public function __construct( $name ) {
		parent::__construct( $name );
		$this->name = $name;
	}
}

class Urvanov_Syntax_Highlighter_HTML_Title extends Urvanov_Syntax_Highlighter_HTML_Separator {

}

class Urvanov_Syntax_Highlighter_Theme_Editor_Save {
	public $id;
	public $oldId;
	public $name;
	public $css;
	public $change_settings;
	public $allow_edit;
	public $allow_edit_stock_theme;
	public $delete;

	public function initialize_from_post() {
		Urvanov_Syntax_Highlighter_Settings_WP::load_settings();
		$this->oldId = stripslashes( sanitize_text_field( $_POST['id'] ) );
		$this->id    = $this->oldId;
		$this->name  = stripslashes( sanitize_text_field( $_POST['name'] ) );
		$this->css   = stripslashes( sanitize_textarea_field( $_POST['css'] ) );
		if ( array_key_exists( 'change_settings', $_POST ) ) {
			$this->change_settings = UrvanovSyntaxHighlighterUtil::set_default( sanitize_text_field( $_POST['change_settings'] ), true );
		}
		if ( array_key_exists( 'allow_edit', $_POST ) ) {
			$this->allow_edit = UrvanovSyntaxHighlighterUtil::set_default( sanitize_text_field( $_POST['allow_edit'] ), true );
		}
		if ( array_key_exists( 'allow_edit_stock_theme', $_POST ) ) {
			$this->allow_edit_stock_theme = UrvanovSyntaxHighlighterUtil::set_default( sanitize_text_field( $_POST['allow_edit_stock_theme'] ), URVANOV_SYNTAX_HIGHLIGHTER_DEBUG );
		}
		if ( array_key_exists( 'delete', $_POST ) ) {
			$this->delete = UrvanovSyntaxHighlighterUtil::set_default( sanitize_text_field( $_POST['delete'] ), true );
		}
	}
}

class Urvanov_Syntax_Highlighter_Theme_Editor_WP {

	public static $attributes = null;
	public static $attributeGroups = null;
	public static $attributeGroupsInverse = null;
	public static $attributeTypes = null;
	public static $attributeTypesInverse = null;
	public static $infoFields = null;
	public static $infoFieldsInverse = null;
	public static $settings = null;
	public static $strings = null;

	const ATTRIBUTE = 'attribute';

	const RE_COMMENT = '#^\s*\/\*[\s\S]*?\*\/#msi';

	public static function initFields() {
		if ( self::$infoFields === null ) {
			self::$infoFields        = array(
				// These are canonical and can't be translated, since they appear in the comments of the CSS
				'name'            => 'Name',
				'description'     => 'Description',
				'version'         => 'Version',
				'author'          => 'Author',
				'url'             => 'URL',
				'original-author' => 'Original Author',
				'notes'           => 'Notes',
				'maintainer'      => 'Maintainer',
				'maintainer-url'  => 'Maintainer URL',
			);
			self::$infoFieldsInverse = UrvanovSyntaxHighlighterUtil::array_flip( self::$infoFields );
			// A map of CSS element name and property to name
			self::$attributes = array();
			// A map of CSS attribute to input type
			self::$attributeGroups        = array(
					'color'        => array(
							'background',
							'background-color',
							'border-color',
							'color',
							'border-top-color',
							'border-bottom-color',
							'border-left-color',
							'border-right-color',
					),
					'size'         => array( 'border-width' ),
					'border-style' => array( 'border-style', 'border-bottom-style', 'border-top-style', 'border-left-style', 'border-right-style' ),
			);
			self::$attributeGroupsInverse = UrvanovSyntaxHighlighterUtil::array_flip( self::$attributeGroups );
			// Mapping of input type to attribute group
			self::$attributeTypes        = array(
					'select' => array( 'border-style', 'font-style', 'font-weight', 'text-decoration' ),
			);
			self::$attributeTypesInverse = UrvanovSyntaxHighlighterUtil::array_flip( self::$attributeTypes );
		}
	}

	public static function initSettings() {
		Urvanov_Syntax_Highlighter_Settings_WP::load_settings();
		self::initFields();
		self::initStrings();
		if ( self::$settings === null ) {
			self::$settings = array(
				// Only things the theme editor needs
				'cssThemePrefix' => Urvanov_Syntax_Highlighter_Themes::CSS_PREFIX,
				'cssInputPrefix' => Urvanov_Syntax_Highlighter_HTML_Element::CSS_INPUT_PREFIX,
				'attribute'      => self::ATTRIBUTE,
				'fields'         => self::$infoFields,
				'fieldsInverse'  => self::$infoFieldsInverse,
				'prefix'         => 'urvanov-syntax-highlighter-theme-editor',
			);
		}
	}

	public static function initStrings() {
		if ( self::$strings === null ) {
			self::$strings = array(
				// These appear only in the UI and can be translated
				'userTheme'          => esc_html__( 'User-Defined Theme', 'urvanov-syntax-highlighter' ),
				'stockTheme'         => esc_html__( 'Stock Theme', 'urvanov-syntax-highlighter' ),
				'success'            => esc_html__( 'Success!', 'urvanov-syntax-highlighter' ),
				'fail'               => esc_html__( 'Failed!', 'urvanov-syntax-highlighter' ),
				'delete'             => esc_html__( 'Delete', 'urvanov-syntax-highlighter' ),
				'deleteThemeConfirm' => esc_html__( 'Are you sure you want to delete the \'%s\' theme?', 'urvanov-syntax-highlighter' ),
				'deleteFail'         => esc_html__( 'Delete failed!', 'urvanov-syntax-highlighter' ),
				'duplicate'          => esc_html__( 'Duplicate', 'urvanov-syntax-highlighter' ),
				'newName'            => esc_html__( 'New Name', 'urvanov-syntax-highlighter' ),
				'duplicateFail'      => esc_html__( 'Duplicate failed!', 'urvanov-syntax-highlighter' ),
				'checkLog'           => esc_html__( 'Please check the log for details.', 'urvanov-syntax-highlighter' ),
				'discardConfirm'     => esc_html__( 'Are you sure you want to discard all changes?', 'urvanov-syntax-highlighter' ),
				'editingTheme'       => esc_html__( 'Editing Theme: %s', 'urvanov-syntax-highlighter' ),
				'creatingTheme'      => esc_html__( 'Creating Theme: %s', 'urvanov-syntax-highlighter' ),
				'submit'             => esc_html__( 'Submit Your Theme', 'urvanov-syntax-highlighter' ),
				'submitText'         => esc_html__( "Submit your User Theme for inclusion as a Stock Theme in Urvanov Syntax Highlighter! This will email me your theme - make sure it's considerably different from the stock themes :)", 'urvanov-syntax-highlighter' ),
				'message'            => esc_html__( 'Message', 'urvanov-syntax-highlighter' ),
				'submitMessage'      => esc_html__( 'Please include this theme in Urvanov Syntax Highlighter!', 'urvanov-syntax-highlighter' ),
				'submitSucceed'      => esc_html__( 'Submit was successful.', 'urvanov-syntax-highlighter' ),
				'submitFail'         => esc_html__( 'Submit failed!', 'urvanov-syntax-highlighter' ),
				'borderStyles'       => Urvanov_Syntax_Highlighter_HTML_Element::$borderStyles,
			);
		}
	}

	public static function admin_resources() {
		global $urvanov_syntax_highlighter_version;
		self::initSettings();
		$path = dirname( dirname( __FILE__ ) );

		wp_enqueue_script( 'cssjson_js', plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_CSSJSON_JS, $path ), $urvanov_syntax_highlighter_version );
		wp_enqueue_script( 'jquery_colorpicker_js', plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_JS_JQUERY_COLORPICKER, $path ), array(
				'jquery',
				'jquery-ui-core',
				'jquery-ui-widget',
				'jquery-ui-tabs',
				'jquery-ui-draggable',
				'jquery-ui-dialog',
				'jquery-ui-position',
				'jquery-ui-mouse',
				'jquery-ui-slider',
				'jquery-ui-droppable',
				'jquery-ui-selectable',
				'jquery-ui-resizable',
		), $urvanov_syntax_highlighter_version );
		wp_enqueue_script( 'jquery_tinycolor_js', plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_JS_TINYCOLOR, $path ), array(), $urvanov_syntax_highlighter_version );
		UrvanovSyntaxHighlighterLog::debug( self::$settings, 'Theme editor settings' );
		if ( URVANOV_SYNTAX_HIGHLIGHTER_MINIFY ) {
			wp_enqueue_script( 'urvanov_syntax_highlighter_theme_editor', plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_THEME_EDITOR_JS, $path ), array(
					'jquery',
					'urvanov_syntax_highlighter_js',
					'urvanov_syntax_highlighter_admin_js',
					'cssjson_js',
					'jquery_colorpicker_js',
					'jquery_tinycolor_js',
			), $urvanov_syntax_highlighter_version );
		} else {
			wp_enqueue_script( 'urvanov_syntax_highlighter_theme_editor', plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_THEME_EDITOR_JS, $path ), array(
					'jquery',
					'urvanov_syntax_highlighter_util_js',
					'urvanov_syntax_highlighter_admin_js',
					'cssjson_js',
					'jquery_colorpicker_js',
					'jquery_tinycolor_js',
			), $urvanov_syntax_highlighter_version );
		}

		wp_localize_script( 'urvanov_syntax_highlighter_theme_editor', 'UrvanovSyntaxHighlighterThemeEditorSettings', self::$settings );
		wp_localize_script( 'urvanov_syntax_highlighter_theme_editor', 'UrvanovSyntaxHighlighterThemeEditorStrings', self::$strings );

		wp_enqueue_style( 'urvanov_syntax_highlighter_theme_editor', plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_THEME_EDITOR_STYLE, $path ), array( 'wp-jquery-ui-dialog' ), $urvanov_syntax_highlighter_version );
		wp_enqueue_style( 'jquery_colorpicker', plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_CSS_JQUERY_COLORPICKER, $path ), array(), $urvanov_syntax_highlighter_version );
	}

	public static function form( $inputs ) {
		$str      = '<form class="' . self::$settings['prefix'] . '-form"><table>';
		$sepCount = 0;
		foreach ( $inputs as $input ) {
			if ( $input instanceof Urvanov_Syntax_Highlighter_HTML_Input ) {
				$str .= self::formField( $input->name, $input );
			} elseif ( $input instanceof Urvanov_Syntax_Highlighter_HTML_Separator ) {
				$sepClass = '';
				if ( $input instanceof Urvanov_Syntax_Highlighter_HTML_Title ) {
					$sepClass .= ' title';
				}
				if ( $sepCount == 0 ) {
					$sepClass .= ' first';
				}
				$str .= '<tr class="separator' . $sepClass . '"><td colspan="2"><div class="content">' . $input->name . '</div></td></tr>';
				$sepCount ++;
			} elseif ( is_array( $input ) && count( $input ) > 1 ) {
				$name    = $input[0];
				$fields  = '<table class="split-field"><tr>';
				$percent = 100 / count( $input );
				for ( $i = 1; $i < count( $input ); $i ++ ) {
					$class  = $i == count( $input ) - 1 ? 'class="last"' : '';
					$fields .= '<td ' . $class . ' style="width: ' . $percent . '%">' . $input[ $i ] . '</td>';
				}
				$fields .= '</tr></table>';
				$str    .= self::formField( $name, $fields, 'split' );
			}
		}
		$str .= '</table></form>';

		return $str;
	}

	public static function formField( $name, $field, $class = '' ) {
		return '<tr><td class="field ' . $class . '">' . $name . '</td><td class="value ' . $class . '">' . $field . '</td></tr>';
	}

	public static function content() {
		self::initSettings();
		$theme   = Urvanov_Syntax_Highlighter_Resources::themes()->get_default();
		$editing = false;

		if ( isset( $_GET['curr_theme'] ) ) {
			$currTheme = Urvanov_Syntax_Highlighter_Resources::themes()->get( $_GET['curr_theme'] );
			if ( $currTheme ) {
				$theme = $currTheme;
			}
		}

		if ( isset( $_GET['editing'] ) ) {
			$editing = UrvanovSyntaxHighlighterUtil::str_to_bool( $_GET['editing'], false );
		}

		$tInformation  = esc_html__( "Information", 'urvanov-syntax-highlighter' );
		$tHighlighting = esc_html__( "Highlighting", 'urvanov-syntax-highlighter' );
		$tFrame        = esc_html__( "Frame", 'urvanov-syntax-highlighter' );
		$tLines        = esc_html__( "Lines", 'urvanov-syntax-highlighter' );
		$tNumbers      = esc_html__( "Line Numbers", 'urvanov-syntax-highlighter' );
		$tToolbar      = esc_html__( "Toolbar", 'urvanov-syntax-highlighter' );

		$tBackground   = esc_html__( "Background", 'urvanov-syntax-highlighter' );
		$tText         = esc_html__( "Text", 'urvanov-syntax-highlighter' );
		$tBorder       = esc_html__( "Border", 'urvanov-syntax-highlighter' );
		$tTopBorder    = esc_html__( "Top Border", 'urvanov-syntax-highlighter' );
		$tBottomBorder = esc_html__( "Bottom Border", 'urvanov-syntax-highlighter' );
		$tBorderRight  = esc_html__( "Right Border", 'urvanov-syntax-highlighter' );

		$tHover         = esc_html__( "Hover", 'urvanov-syntax-highlighter' );
		$tActive        = esc_html__( "Active", 'urvanov-syntax-highlighter' );
		$tPressed       = esc_html__( "Pressed", 'urvanov-syntax-highlighter' );
		$tHoverPressed  = esc_html__( "Pressed & Hover", 'urvanov-syntax-highlighter' );
		$tActivePressed = esc_html__( "Pressed & Active", 'urvanov-syntax-highlighter' );

		$tTitle   = esc_html__( "Title", 'urvanov-syntax-highlighter' );
		$tButtons = esc_html__( "Buttons", 'urvanov-syntax-highlighter' );

		$tNormal        = esc_html__( "Normal", 'urvanov-syntax-highlighter' );
		$tInline        = esc_html__( "Inline", 'urvanov-syntax-highlighter' );
		$tStriped       = esc_html__( "Striped", 'urvanov-syntax-highlighter' );
		$tMarked        = esc_html__( "Marked", 'urvanov-syntax-highlighter' );
		$tStripedMarked = esc_html__( "Striped & Marked", 'urvanov-syntax-highlighter' );
		$tLanguage      = esc_html__( "Language", 'urvanov-syntax-highlighter' );

		$top     = '.crayon-top';
		$bottom  = '.crayon-bottom';
		$hover   = ':hover';
		$active  = ':active';
		$pressed = '.crayon-pressed';

		?>

		<div
				id="icon-options-general" class="icon32"></div>
		<h2>
			Urvanov Syntax Highlighter
			<?php esc_html_e( 'Theme Editor', 'urvanov-syntax-highlighter' ); ?>
		</h2>

		<h3 id="<?php echo self::$settings['prefix'] ?>-name">
			<?php
			//			if ($editing) {
			//				echo sprintf(esc_html__('Editing "%s" Theme', 'urvanov-syntax-highlighter' ), $theme->name());
			//			} else {
			//				echo sprintf(esc_html__('Creating Theme From "%s"', 'urvanov-syntax-highlighter' ), $theme->name());
			//			}
			?>
		</h3>
		<div id="<?php echo self::$settings['prefix'] ?>-info"></div>

		<p>
			<a id="urvanov-syntax-highlighter-editor-back"
			   class="button-primary"><?php esc_html_e( "Back To Settings", 'urvanov-syntax-highlighter' ); ?></a>
			<a id="urvanov-syntax-highlighter-editor-save" class="button-primary"><?php esc_html_e( "Save", 'urvanov-syntax-highlighter' ); ?></a>
			<span id="urvanov-syntax-highlighter-editor-status"></span>
		</p>

		<?php //esc_html_e('Use the Sidebar on the right to change the Theme of the Preview window.', 'urvanov-syntax-highlighter' ) ?>

		<div id="urvanov-syntax-highlighter-editor-top-controls"></div>

		<table id="urvanov-syntax-highlighter-editor-table" style="width: 100%;" cellspacing="5"
			   cellpadding="0">
			<tr>
				<td id="urvanov-syntax-highlighter-editor-preview-wrapper">
					<div id="urvanov-syntax-highlighter-editor-preview"></div>
				</td>
				<div id="urvanov-syntax-highlighter-editor-preview-css"></div>
				<td id="urvanov-syntax-highlighter-editor-control-wrapper">
					<div id="urvanov-syntax-highlighter-editor-controls">
						<ul>
							<li title="<?php echo $tInformation ?>"><a class="urvanov-syntax-highlighter-tab-information" href="#tabs-1"></a></li>
							<li title="<?php echo $tHighlighting ?>"><a class="urvanov-syntax-highlighter-tab-highlighting" href="#tabs-2"></a></li>
							<li title="<?php echo $tFrame ?>"><a class="urvanov-syntax-highlighter-tab-frame" href="#tabs-3"></a></li>
							<li title="<?php echo $tLines ?>"><a class="urvanov-syntax-highlighter-tab-lines" href="#tabs-4"></a></li>
							<li title="<?php echo $tNumbers ?>"><a class="urvanov-syntax-highlighter-tab-numbers" href="#tabs-5"></a></li>
							<li title="<?php echo $tToolbar ?>"><a class="urvanov-syntax-highlighter-tab-toolbar" href="#tabs-6"></a></li>
						</ul>
						<div id="tabs-1">
							<?php
							self::createAttributesForm( array(
									new Urvanov_Syntax_Highlighter_HTML_Title( $tInformation ),
							) );
							?>
							<div id="tabs-1-contents"></div>
							<!-- Auto-filled by theme_editor.js -->
						</div>
						<div id="tabs-2">
							<?php

							$highlight = ' .crayon-pre';
							$elems     = array(
									'c'  => esc_html__( "Comment", 'urvanov-syntax-highlighter' ),
									's'  => esc_html__( "String", 'urvanov-syntax-highlighter' ),
									'p'  => esc_html__( "Preprocessor", 'urvanov-syntax-highlighter' ),
									'ta' => esc_html__( "Tag", 'urvanov-syntax-highlighter' ),
									'k'  => esc_html__( "Keyword", 'urvanov-syntax-highlighter' ),
									'st' => esc_html__( "Statement", 'urvanov-syntax-highlighter' ),
									'r'  => esc_html__( "Reserved", 'urvanov-syntax-highlighter' ),
									't'  => esc_html__( "Type", 'urvanov-syntax-highlighter' ),
									'm'  => esc_html__( "Modifier", 'urvanov-syntax-highlighter' ),
									'i'  => esc_html__( "Identifier", 'urvanov-syntax-highlighter' ),
									'e'  => esc_html__( "Entity", 'urvanov-syntax-highlighter' ),
									'v'  => esc_html__( "Variable", 'urvanov-syntax-highlighter' ),
									'cn' => esc_html__( "Constant", 'urvanov-syntax-highlighter' ),
									'o'  => esc_html__( "Operator", 'urvanov-syntax-highlighter' ),
									'sy' => esc_html__( "Symbol", 'urvanov-syntax-highlighter' ),
									'n'  => esc_html__( "Notation", 'urvanov-syntax-highlighter' ),
									'f'  => esc_html__( "Faded", 'urvanov-syntax-highlighter' ),
									'h'  => esc_html__( "HTML", 'urvanov-syntax-highlighter' ),
									''   => esc_html__( "Unhighlighted", 'urvanov-syntax-highlighter' ),
							);
							$atts      = array( new Urvanov_Syntax_Highlighter_HTML_Title( $tHighlighting ) );
							foreach ( $elems as $class => $name ) {
								$fullClass = $class != '' ? $highlight . ' .crayon-' . $class : $highlight;
								$atts[]    = array(
										$name,
										self::createAttribute( $fullClass, 'color' ),
										self::createAttribute( $fullClass, 'font-weight' ),
										self::createAttribute( $fullClass, 'font-style' ),
										self::createAttribute( $fullClass, 'text-decoration' ),
								);
							}
							self::createAttributesForm( $atts );
							?>
						</div>
						<div id="tabs-3">
							<?php
							$inline = '-inline';
							self::createAttributesForm( array(
									new Urvanov_Syntax_Highlighter_HTML_Title( $tFrame ),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $tNormal ),
									//                            self::createAttribute('', 'background', $tBackground),
									array(
											$tBorder,
											self::createAttribute( '', 'border-width' ),
											self::createAttribute( '', 'border-color' ),
											self::createAttribute( '', 'border-style' ),
									),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $tInline ),
									self::createAttribute( $inline, 'background', $tBackground ),
									array(
											$tBorder,
											self::createAttribute( $inline, 'border-width' ),
											self::createAttribute( $inline, 'border-color' ),
											self::createAttribute( $inline, 'border-style' ),
									),
							) );
							?>
						</div>
						<div id="tabs-4">
							<?php
							$stripedLine       = ' .crayon-striped-line';
							$markedLine        = ' .crayon-marked-line';
							$stripedMarkedLine = ' .crayon-marked-line.crayon-striped-line';
							self::createAttributesForm( array(
									new Urvanov_Syntax_Highlighter_HTML_Title( $tLines ),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $tNormal ),
									self::createAttribute( '', 'background', $tBackground ),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $tStriped ),
									self::createAttribute( $stripedLine, 'background', $tBackground ),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $tMarked ),
									self::createAttribute( $markedLine, 'background', $tBackground ),
									array(
											$tBorder,
											self::createAttribute( $markedLine, 'border-width' ),
											self::createAttribute( $markedLine, 'border-color' ),
											self::createAttribute( $markedLine, 'border-style' ),
									),
									self::createAttribute( $markedLine . $top, 'border-top-style', $tTopBorder ),
									self::createAttribute( $markedLine . $bottom, 'border-bottom-style', $tBottomBorder ),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $tStripedMarked ),
									self::createAttribute( $stripedMarkedLine, 'background', $tBackground ),
							) );
							?>
						</div>
						<div id="tabs-5">
							<?php
							$nums             = ' .crayon-table .crayon-nums';
							$stripedNum       = ' .crayon-striped-num';
							$markedNum        = ' .crayon-marked-num';
							$stripedMarkedNum = ' .crayon-marked-num.crayon-striped-num';
							self::createAttributesForm( array(
									new Urvanov_Syntax_Highlighter_HTML_Title( $tNumbers ),
									array(
											$tBorderRight,
											self::createAttribute( $nums, 'border-right-width' ),
											self::createAttribute( $nums, 'border-right-color' ),
											self::createAttribute( $nums, 'border-right-style' ),
									),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $tNormal ),
									self::createAttribute( $nums, 'background', $tBackground ),
									self::createAttribute( $nums, 'color', $tText ),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $tStriped ),
									self::createAttribute( $stripedNum, 'background', $tBackground ),
									self::createAttribute( $stripedNum, 'color', $tText ),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $tMarked ),
									self::createAttribute( $markedNum, 'background', $tBackground ),
									self::createAttribute( $markedNum, 'color', $tText ),
									array(
											$tBorder,
											self::createAttribute( $markedNum, 'border-width' ),
											self::createAttribute( $markedNum, 'border-color' ),
											self::createAttribute( $markedNum, 'border-style' ),
									),
									self::createAttribute( $markedNum . $top, 'border-top-style', $tTopBorder ),
									self::createAttribute( $markedNum . $bottom, 'border-bottom-style', $tBottomBorder ),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $tStripedMarked ),
									self::createAttribute( $stripedMarkedNum, 'background', $tBackground ),
									self::createAttribute( $stripedMarkedNum, 'color', $tText ),
							) );
							?>
						</div>
						<div id="tabs-6">
							<?php
							$toolbar  = ' .crayon-toolbar';
							$title    = ' .crayon-title';
							$button   = ' .crayon-button';
							$info     = ' .crayon-info';
							$language = ' .crayon-language';
							self::createAttributesForm( array(
									new Urvanov_Syntax_Highlighter_HTML_Title( $tToolbar ),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $tFrame ),
									self::createAttribute( $toolbar, 'background', $tBackground ),
									array(
											$tBottomBorder,
											self::createAttribute( $toolbar, 'border-bottom-width' ),
											self::createAttribute( $toolbar, 'border-bottom-color' ),
											self::createAttribute( $toolbar, 'border-bottom-style' ),
									),
									array(
											$tTitle,
											self::createAttribute( $title, 'color' ),
											self::createAttribute( $title, 'font-weight' ),
											self::createAttribute( $title, 'font-style' ),
											self::createAttribute( $title, 'text-decoration' ),
									),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $tButtons ),
									self::createAttribute( $button, 'background-color', $tBackground ),
									self::createAttribute( $button . $hover, 'background-color', $tHover ),
									self::createAttribute( $button . $active, 'background-color', $tActive ),
									self::createAttribute( $button . $pressed, 'background-color', $tPressed ),
									self::createAttribute( $button . $pressed . $hover, 'background-color', $tHoverPressed ),
									self::createAttribute( $button . $pressed . $active, 'background-color', $tActivePressed ),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $tInformation . ' ' . esc_html__( "(Used for Copy/Paste)", 'urvanov-syntax-highlighter' ) ),
									self::createAttribute( $info, 'background', $tBackground ),
									array(
											$tText,
											self::createAttribute( $info, 'color' ),
											self::createAttribute( $info, 'font-weight' ),
											self::createAttribute( $info, 'font-style' ),
											self::createAttribute( $info, 'text-decoration' ),
									),
									array(
											$tBottomBorder,
											self::createAttribute( $info, 'border-bottom-width' ),
											self::createAttribute( $info, 'border-bottom-color' ),
											self::createAttribute( $info, 'border-bottom-style' ),
									),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $tLanguage ),
									array(
											$tText,
											self::createAttribute( $language, 'color' ),
											self::createAttribute( $language, 'font-weight' ),
											self::createAttribute( $language, 'font-style' ),
											self::createAttribute( $language, 'text-decoration' ),
									),
									self::createAttribute( $language, 'background-color', $tBackground ),
							) );
							?>
						</div>
					</div>
				</td>
			</tr>

		</table>

		<?php
		exit();
	}

	public static function createAttribute( $element, $attribute, $name = null ) {
		$group = self::getAttributeGroup( $attribute );
		$type  = self::getAttributeType( $group );
		if ( $type == 'select' ) {
			$input = new Urvanov_Syntax_Highlighter_HTML_Select( $element . '_' . $attribute, $name );
			if ( $group == 'border-style' ) {
				$input->addOptions( Urvanov_Syntax_Highlighter_HTML_Element::$borderStyles );
			} elseif ( $group == 'float' ) {
				$input->addOptions( array(
						'left',
						'right',
						'both',
						'none',
						'inherit',
				) );
			} elseif ( $group == 'font-style' ) {
				$input->addOptions( array(
						'normal',
						'italic',
						'oblique',
						'inherit',
				) );
			} elseif ( $group == 'font-weight' ) {
				$input->addOptions( array(
						'normal',
						'bold',
						'bolder',
						'lighter',
						'100',
						'200',
						'300',
						'400',
						'500',
						'600',
						'700',
						'800',
						'900',
						'inherit',
				) );
			} elseif ( $group == 'text-decoration' ) {
				$input->addOptions( array(
						'none',
						'underline',
						'overline',
						'line-through',
						'blink',
						'inherit',
				) );
			}
		} else {
			$input = new Urvanov_Syntax_Highlighter_HTML_Input( $element . '_' . $attribute, $name );
		}
		$input->addClass( self::ATTRIBUTE );
		$input->addAttributes( array(
				'data-element'   => $element,
				'data-attribute' => $attribute,
				'data-group'     => $group,
		) );

		return $input;
	}

	public static function createAttributesForm( $atts ) {
		echo self::form( $atts );
	}

	public static function save() {
		$save_args = new Urvanov_Syntax_Highlighter_Theme_Editor_Save;
		$save_args->initialize_from_post();
		self::saveFromArgs( $save_args );
	}

	/**
	 * Saves the given theme id and css, making any necessary path and id changes to ensure the new theme is valid.
	 * Echos 0 on failure, 1 on success and 2 on success and if paths have changed.
	 */
	public static function saveFromArgs( $save_args ) {
		Urvanov_Syntax_Highlighter_Settings_WP::load_settings();
//     	$save_args->id = stripslashes(sanitize_text_field($_POST['id']));
//     	$save_args->name = stripslashes(sanitize_text_field($_POST['name']));
//     	$save_args->css = stripslashes(sanitize_text_field($_POST['css']));
//     	$save_args->change_settings = UrvanovSyntaxHighlighterUtil::set_default(sanitize_text_field($_POST['change_settings']), TRUE);
//     	$save_args->allow_edit = UrvanovSyntaxHighlighterUtil::set_default(sanitize_text_field($_POST['allow_edit']), TRUE);
//     	$save_args->allow_edit_stock_theme = UrvanovSyntaxHighlighterUtil::set_default(sanitize_text_field($_POST['allow_edit_stock_theme']), URVANOV_SYNTAX_HIGHLIGHTER_DEBUG);
//     	$save_args->delete = UrvanovSyntaxHighlighterUtil::set_default(sanitize_text_field($_POST['delete']), TRUE);
		$oldTheme = Urvanov_Syntax_Highlighter_Resources::themes()->get( $save_args->id );
		UrvanovSyntaxHighlighterLog::log( $save_args->oldId, 'save_args->oldId' );
		UrvanovSyntaxHighlighterLog::log( $save_args->name, 'save_args->name' );
		if ( ! empty( $save_args->oldId ) && ! empty( $save_args->css ) && ! empty( $save_args->name ) ) {
			// By default, expect a user theme to be saved - prevents editing stock themes
			// If in DEBUG mode, then allow editing stock themes.
			$user    = $oldTheme !== null && $save_args->allow_edit_stock_theme ? $oldTheme->user() : true;
			$oldPath = Urvanov_Syntax_Highlighter_Resources::themes()->path( $save_args->oldId );
			$oldDir  = Urvanov_Syntax_Highlighter_Resources::themes()->dirpath_for_id( $save_args->oldId );
			// Create an instance to use functions, since late static binding is only available in 5.3 (PHP kinda sucks)
			$theme           = Urvanov_Syntax_Highlighter_Resources::themes()->resource_instance( '' );
			$newID           = $theme->clean_id( $save_args->name );
			$save_args->name = Urvanov_Syntax_Highlighter_Resource::clean_name( $newID );
			$newPath         = Urvanov_Syntax_Highlighter_Resources::themes()->path( $newID, $user );
			UrvanovSyntaxHighlighterLog::log( $oldPath, 'oldPath' );
			UrvanovSyntaxHighlighterLog::log( $newPath, 'newPath' );
			$newDir = Urvanov_Syntax_Highlighter_Resources::themes()->dirpath_for_id( $newID, $user );
			UrvanovSyntaxHighlighterLog::log( $newDir, 'newDir' );

			$exists = Urvanov_Syntax_Highlighter_Resources::themes()->is_loaded( $newID ) || ( is_file( $newPath ) && is_file( $oldPath ) );
			if ( $exists && $oldPath != $newPath ) {
				// Never allow overwriting a theme with a different id!
				echo - 3;
				exit();
			}

			if ( $oldPath == $newPath && $save_args->allow_edit === false ) {
				// Don't allow editing
				echo - 4;
				exit();
			}

			// Create the new path if needed
			if ( ! is_dir( $newDir ) ) {
				wp_mkdir_p( $newDir );
				$imageSrc = $oldDir . 'images';
				if ( is_dir( $imageSrc ) ) {
					try {
						// Copy image folder
						UrvanovSyntaxHighlighterUtil::copyDir( $imageSrc, $newDir . 'images', 'wp_mkdir_p' );
					} catch ( Exception $e ) {
						UrvanovSyntaxHighlighterLog::syslog( $e->getMessage(), "THEME SAVE" );
					}
				}
			}

			$refresh   = false;
			$replaceID = $save_args->oldId;
			UrvanovSyntaxHighlighterLog::log( $replaceID, '$replaceID' );
			// Replace ids in the CSS
			if ( ! is_file( $oldPath ) || strpos( $save_args->css, Urvanov_Syntax_Highlighter_Themes::CSS_PREFIX . $save_args->id ) === false ) {
				// The old path/id is no longer valid - something has gone wrong - we should refresh afterwards
				$refresh = true;
			}
			// XXX This is case sensitive to avoid modifying text, but it means that CSS must be in lowercase
			UrvanovSyntaxHighlighterLog::debug( "before caseSensitivePregReplace replaceId=$replaceID newID=$newID css=" . str_replace( array(
							"\r\n",
							"\r",
							"\n",
					), "q", $save_args->css ), "caseSensitivePregReplace" );
			$save_args->css = preg_replace( '#(?<=' . Urvanov_Syntax_Highlighter_Themes::CSS_PREFIX . ')' . $replaceID . '\b#ms', $newID, $save_args->css );
			UrvanovSyntaxHighlighterLog::debug( "after caseSensitivePregReplace replaceId=$replaceID newID=$newID css=" . str_replace( array(
							"\r\n",
							"\r",
							"\n",
					), "q", $save_args->css ), "caseSensitivePregReplace" );

			// Replace the name with the new one
			$info         = self::getCSSInfo( $save_args->css );
			$info['name'] = $save_args->name;
			UrvanovSyntaxHighlighterLog::syslog( $save_args->name, 'change name to ' );
			UrvanovSyntaxHighlighterLog::log( $save_args->name, 'change name to ' );
			$save_args->css = self::setCSSInfo( $save_args->css, $info );

			$result  = @file_put_contents( $newPath, $save_args->css );
			$success = $result !== false;
			if ( $success && $oldPath !== $newPath ) {
				if ( $save_args->id !== Urvanov_Syntax_Highlighter_Themes::DEFAULT_THEME && $save_args->delete ) {
					// Only delete the old path if it isn't the default theme
					try {
						// Delete the old path
						UrvanovSyntaxHighlighterUtil::deleteDir( $oldDir );
					} catch ( Exception $e ) {
						UrvanovSyntaxHighlighterLog::syslog( $e->getMessage(), "THEME SAVE" );
					}
				}
				// Refresh
				echo 2;
			} else {
				if ( $refresh ) {
					echo 2;
				} else {
					if ( $success ) {
						echo 1;
					} else {
						echo - 2;
					}
				}
			}
			// Set the new theme in settings
			if ( $save_args->change_settings ) {
				Urvanov_Syntax_Highlighter_Global_Settings::set( Urvanov_Syntax_Highlighter_Settings::THEME, $newID );
				Urvanov_Syntax_Highlighter_Settings_WP::save_settings();
			}
		} else {
			UrvanovSyntaxHighlighterLog::syslog( "save_args->id=$save_args->id\n\nsave_args->name=$save_args->name", "THEME SAVE" );
			echo - 1;
		}
		exit();
	}

	public static function duplicate() {
		Urvanov_Syntax_Highlighter_Settings_WP::load_settings();
		$save_args                         = new Urvanov_Syntax_Highlighter_Theme_Editor_Save();
		$save_args->oldId                  = sanitize_text_field( $_POST['id'] );
		$oldPath                           = Urvanov_Syntax_Highlighter_Resources::themes()->path( $save_args->oldId );
		$save_args->css                    = file_get_contents( $oldPath );
		$save_args->delete                 = false;
		$save_args->allow_edit             = false;
		$save_args->allow_edit_stock_theme = false;
		$save_args->name                   = stripslashes( sanitize_text_field( $_POST['name'] ) );
		self::saveFromArgs( $save_args );
	}

	public static function delete() {
		Urvanov_Syntax_Highlighter_Settings_WP::load_settings();
		$id  = sanitize_text_field( $_POST['id'] );
		$dir = Urvanov_Syntax_Highlighter_Resources::themes()->dirpath_for_id( $id );
		if ( is_dir( $dir ) && Urvanov_Syntax_Highlighter_Resources::themes()->exists( $id ) ) {
			try {
				UrvanovSyntaxHighlighterUtil::deleteDir( $dir );
				Urvanov_Syntax_Highlighter_Global_Settings::set( Urvanov_Syntax_Highlighter_Settings::THEME, Urvanov_Syntax_Highlighter_Themes::DEFAULT_THEME );
				Urvanov_Syntax_Highlighter_Settings_WP::save_settings();
				echo 1;
			} catch ( Exception $e ) {
				UrvanovSyntaxHighlighterLog::syslog( $e->getMessage(), "THEME SAVE" );
				echo - 2;
			}
		} else {
			echo - 1;
		}
		exit();
	}

	public static function submit() {
		global $urvanov_syntax_highlighter_email;
		Urvanov_Syntax_Highlighter_Settings_WP::load_settings();
		$id      = sanitize_text_field( $_POST['id'] );
		$message = sanitize_text_field( $_POST['message'] );
		$dir     = Urvanov_Syntax_Highlighter_Resources::themes()->dirpath_for_id( $id );
		$dest    = $dir . 'tmp';
		wp_mkdir_p( $dest );

		if ( is_dir( $dir ) && Urvanov_Syntax_Highlighter_Resources::themes()->exists( $id ) ) {
			try {
				$zipFile = UrvanovSyntaxHighlighterUtil::createZip( $dir, $dest, true );
				$result  = UrvanovSyntaxHighlighterUtil::emailFile( array(
						'to'      => $urvanov_syntax_highlighter_email,
						'from'    => get_bloginfo( 'admin_email' ),
						'subject' => 'Theme Editor Submission',
						'message' => $message,
						'file'    => $zipFile,
				) );
				UrvanovSyntaxHighlighterUtil::deleteDir( $dest );
				if ( $result ) {
					echo 1;
				} else {
					echo - 3;
				}
			} catch ( Exception $e ) {
				UrvanovSyntaxHighlighterLog::syslog( $e->getMessage(), "THEME SUBMIT" );
				echo - 2;
			}
		} else {
			echo - 1;
		}
		exit();
	}

	public static function getCSSInfo( $css ) {
		UrvanovSyntaxHighlighterLog::debug( "css=$css", "getCSSInfo" );
		$info = array();
		preg_match( self::RE_COMMENT, $css, $matches );
		if ( count( $matches ) ) {
			$comment = $matches[0];
			preg_match_all( '#([^\r\n:]*[^\r\n\s:])\s*:\s*([^\r\n]+)#msi', $comment, $matches );
			if ( count( $matches ) ) {
				for ( $i = 0; $i < count( $matches[1] ); $i ++ ) {
					$name                              = $matches[1][ $i ];
					$value                             = $matches[2][ $i ];
					$info[ self::getFieldID( $name ) ] = $value;
				}
			}
		}
		UrvanovSyntaxHighlighterLog::debug( $info, "getCSSInfo" );

		return $info;
	}

	public static function cssInfoToString( $info ) {
		UrvanovSyntaxHighlighterLog::log( $info, "cssInfoToString" );
		$str = "/*\n";
		foreach ( $info as $id => $value ) {
			$str .= self::getFieldName( $id ) . ': ' . $value . "\n";
		}
		$str .= "*/";

		UrvanovSyntaxHighlighterLog::log( "result = $str", "cssInfoToString" );

		return $str;
	}

	public static function setCSSInfo( $css, $info ) {
		return preg_replace( self::RE_COMMENT, self::cssInfoToString( $info ), $css );
	}

	public static function getFieldID( $name ) {
		if ( isset( self::$infoFieldsInverse[ $name ] ) ) {
			return self::$infoFieldsInverse[ $name ];
		} else {
			return Urvanov_Syntax_Highlighter_User_Resource::clean_id_static( $name );
		}
	}

	public static function getFieldName( $id ) {
		self::initFields();
		if ( isset( self::$infoFields[ $id ] ) ) {
			return self::$infoFields[ $id ];
		} else {
			return Urvanov_Syntax_Highlighter_User_Resource::clean_name( $id );
		}
	}

	public static function getAttributeGroup( $attribute ) {
		if ( isset( self::$attributeGroupsInverse[ $attribute ] ) ) {
			return self::$attributeGroupsInverse[ $attribute ];
		} else {
			return $attribute;
		}
	}

	public static function getAttributeType( $group ) {
		if ( isset( self::$attributeTypesInverse[ $group ] ) ) {
			return self::$attributeTypesInverse[ $group ];
		} else {
			return 'text';
		}
	}

}

?>

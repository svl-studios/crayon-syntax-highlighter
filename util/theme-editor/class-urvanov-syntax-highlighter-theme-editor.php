<?php
/**
 * Theme Editor
 *
 * @package   Crayon Syntax Highlighter
 * @author    Fedor Urvanov, Aram Kocharyan
 * @copyright Copyright 2013, Aram Kocharyan
 * @link      https://urvanov.ru
 */

defined( 'ABSPATH' ) || exit;


/**
 * Class Urvanov_Syntax_Highlighter_HTML_Element
 */
class Urvanov_Syntax_Highlighter_HTML_Element {

	/**
	 * ID.
	 *
	 * @var null
	 */
	public $id;

	/**
	 * Class.
	 *
	 * @var string
	 */
	public $class = '';

	/** Tag.
	 *
	 * @var string
	 */
	public $tag = 'div';

	/**
	 * Closed.
	 *
	 * @var bool
	 */
	public $closed = false;

	/**
	 * Contents.
	 *
	 * @var string
	 */
	public $contents = '';

	/**
	 * Attributes.
	 *
	 * @var array
	 */
	public $attributes = array();

	/**
	 * CSS Input Prefix.
	 */
	const CSS_INPUT_PREFIX = 'crayon-theme-input-';

	/**
	 * Border styles.
	 *
	 * @var string[]
	 */
	public static $border_styles = array(
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

	/**
	 * Urvanov_Syntax_Highlighter_HTML_Element constructor.
	 *
	 * @param mixed $id ID.
	 */
	public function __construct( $id ) {
		$this->id = $id;
	}

	/**
	 * Add class.
	 *
	 * @param string $class Class.
	 */
	public function add_class( string $class ) {
		$this->class .= ' ' . self::CSS_INPUT_PREFIX . $class;
	}

	/**
	 * Add attributes.
	 *
	 * @param array $atts Attributes.
	 */
	public function add_attributes( array $atts ) {
		$this->attributes = array_merge( $this->attributes, $atts );
	}

	/**
	 * Attribute string.
	 *
	 * @return string
	 */
	public function attribute_string(): string {
		$str = '';

		foreach ( $this->attributes as $k => $v ) {
			$str .= "$k=\"$v\" ";
		}

		return $str;
	}

	/**
	 * __toString.
	 *
	 * @return string
	 */
	public function __toString() {
		return '<' . $this->tag . ' id="' . esc_attr( self::CSS_INPUT_PREFIX . $this->id ) . '" class="' . esc_attr( self::CSS_INPUT_PREFIX . $this->class ) . '" ' . $this->attribute_string() . ( $this->closed ? ' />' : ' >' . $this->contents . "</$this->tag>" );
	}
}

/**
 * Class Urvanov_Syntax_Highlighter_HTML_Input
 */
class Urvanov_Syntax_Highlighter_HTML_Input extends Urvanov_Syntax_Highlighter_HTML_Element { // phpcs:ignore

	/**
	 * Name.
	 *
	 * @var mixed|string|null
	 */
	public $name;

	/**
	 * Type.
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Urvanov_Syntax_Highlighter_HTML_Input constructor.
	 *
	 * @param mixed  $id ID.
	 * @param null   $name Name.
	 * @param string $value Value.
	 * @param string $type Type.
	 */
	public function __construct( $id, $name = null, $value = '', $type = 'text' ) {
		parent::__construct( $id );

		$this->tag    = 'input';
		$this->closed = true;

		if ( null === $name ) {
			$name = Urvanov_Syntax_Highlighter_User_Resource::clean_name( $id );
		}

		$this->name   = $name;
		$this->class .= $type;

		$this->add_attributes(
			array(
				'type'  => $type,
				'value' => $value,
			)
		);
	}
}

/**
 * Class Urvanov_Syntax_Highlighter_HTML_Select
 */
class Urvanov_Syntax_Highlighter_HTML_Select extends Urvanov_Syntax_Highlighter_HTML_Input { // phpcs:ignore

	/**
	 * Options.
	 *
	 * @var mixed
	 */
	public $options;

	/**
	 * Selected.
	 *
	 * @var null
	 */
	public $selected = null;

	/**
	 * Urvanov_Syntax_Highlighter_HTML_Select constructor.
	 *
	 * @param mixed  $id ID.
	 * @param null   $name Name.
	 * @param string $value Value.
	 * @param array  $options Options.
	 */
	public function __construct( $id, $name = null, $value = '', $options = array() ) {
		parent::__construct( $id, $name, 'select' );

		$this->tag    = 'select';
		$this->closed = false;
		$this->add_options( $options );
	}

	/**
	 * Add options.
	 *
	 * @param array $options Options.
	 * @param mixed $default Default.
	 */
	public function add_options( array $options, $default = null ) {
		$count = count( $options );

		for ( $i = 0; $i < $count; $i ++ ) {
			$key                   = $options[ $i ];
			$value                 = $options[ $key ] ?? $key;
			$this->options[ $key ] = $value;
		}

		if ( null === $default && count( $options ) > 1 ) {
			$this->attributes['data-default'] = $options[0];
		} else {
			$this->attributes['data-default'] = $default;
		}
	}

	/**
	 * Get options string.
	 *
	 * @return string
	 */
	public function get_options_string(): string {
		$str = '';

		foreach ( $this->options as $k => $v ) {
			$selected = $this->selected === $k ? 'selected="selected"' : '';
			$str     .= "<option value=\"$k\" $selected>$v</option>";
		}

		return $str;
	}

	/**
	 * __toString.
	 *
	 * @return string
	 */
	public function __toString() {
		$this->contents = $this->get_options_string();

		return parent::__toString();
	}
}

/**
 * Class Urvanov_Syntax_Highlighter_HTML_Separator
 */
class Urvanov_Syntax_Highlighter_HTML_Separator extends Urvanov_Syntax_Highlighter_HTML_Element { // phpcs:ignore

	/**
	 * Name.
	 *
	 * @var string
	 */
	public $name = '';

	/**
	 * Urvanov_Syntax_Highlighter_HTML_Separator constructor.
	 *
	 * @param string $name Name.
	 */
	public function __construct( $name ) {
		parent::__construct( $name );

		$this->name = $name;
	}
}

/**
 * Class Urvanov_Syntax_Highlighter_HTML_Title
 */
class Urvanov_Syntax_Highlighter_HTML_Title extends Urvanov_Syntax_Highlighter_HTML_Separator {} // phpcs:ignore

/**
 * Class Urvanov_Syntax_Highlighter_Theme_Editor_Save
 */
class Urvanov_Syntax_Highlighter_Theme_Editor_Save { // phpcs:ignore

	/**
	 * ID.
	 *
	 * @var null
	 */
	public $id;

	/** Old ID.
	 *
	 * @var null
	 */
	public $old_id;

	/**
	 * Name.
	 *
	 * @var null
	 */
	public $name;

	/**
	 * CSS.
	 *
	 * @var null
	 */
	public $css;

	/**
	 * Change settings.
	 *
	 * @var null
	 */
	public $change_settings;

	/**
	 * Edit.
	 *
	 * @var null
	 */
	public $allow_edit;

	/**
	 * Edit stock theme.
	 *
	 * @var null
	 */
	public $allow_edit_stock_theme;

	/**
	 * Delete.
	 *
	 * @var null
	 */
	public $delete;

	/**
	 * Init from post.
	 */
	public function initialize_from_post() {
		Urvanov_Syntax_Highlighter_Settings_WP::load_settings();

		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'theme_editor_save' ) ) {
			$this->old_id = stripslashes( sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) ) );
			$this->id     = $this->old_id;
			$this->name   = stripslashes( sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ) );
			$this->css    = stripslashes( sanitize_textarea_field( wp_unslash( $_POST['css'] ?? '' ) ) );

			if ( array_key_exists( 'change_settings', $_POST ) ) {
				$this->change_settings = UrvanovSyntaxHighlighterUtil::set_default( sanitize_text_field( wp_unslash( $_POST['change_settings'] ) ), true );
			}

			if ( array_key_exists( 'allow_edit', $_POST ) ) {
				$this->allow_edit = UrvanovSyntaxHighlighterUtil::set_default( sanitize_text_field( wp_unslash( $_POST['allow_edit'] ) ), true );
			}

			if ( array_key_exists( 'allow_edit_stock_theme', $_POST ) ) {
				$this->allow_edit_stock_theme = UrvanovSyntaxHighlighterUtil::set_default( sanitize_text_field( wp_unslash( $_POST['allow_edit_stock_theme'] ) ), URVANOV_SYNTAX_HIGHLIGHTER_DEBUG );
			}

			if ( array_key_exists( 'delete', $_POST ) ) {
				$this->delete = UrvanovSyntaxHighlighterUtil::set_default( sanitize_text_field( wp_unslash( $_POST['delete'] ) ), true );
			}
		} else {
			wp_nonce_ays( 'expired' );
		}
	}
}

/**
 * Class Urvanov_Syntax_Highlighter_Theme_Editor_WP
 */
class Urvanov_Syntax_Highlighter_Theme_Editor_WP { // phpcs:ignore

	/**
	 * Attributes.
	 *
	 * @var null
	 */
	public static $attributes = null;

	/**
	 * Attribute groups.
	 *
	 * @var null
	 */
	public static $attribute_groups = null;

	/**
	 * Groups inverse.
	 *
	 * @var null
	 */
	public static $attribute_groups_inverse = null;

	/**
	 * Attr types.
	 *
	 * @var null
	 */
	public static $attribute_types = null;

	/**
	 * Types inverse.
	 *
	 * @var null
	 */
	public static $attribute_types_inverse = null;

	/**
	 * Info fields.
	 *
	 * @var null
	 */
	public static $info_fields = null;

	/**
	 * Fields inverse.
	 *
	 * @var null
	 */
	public static $info_fields_inverse = null;

	/**
	 * Settings.
	 *
	 * @var null
	 */
	public static $settings = null;

	/**
	 * Strings.
	 *
	 * @var null
	 */
	public static $strings = null;

	/**
	 * Attribute.
	 */
	const ATTRIBUTE = 'attribute';

	/**
	 * Comment RegEx.
	 */
	const RE_COMMENT = '#^\s*\/\*[\s\S]*?\*\/#msi';

	/**
	 * Init Fields.
	 */
	public static function init_fields() {
		if ( null === self::$info_fields ) {
			self::$info_fields = array(
				// These are canonical and can't be translated, since they appear in the comments of the CSS.
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

			self::$info_fields_inverse = UrvanovSyntaxHighlighterUtil::array_flip( self::$info_fields );

			// A map of CSS element name and property to name.
			self::$attributes = array();

			// A map of CSS attribute to input type.
			self::$attribute_groups = array(
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

			self::$attribute_groups_inverse = UrvanovSyntaxHighlighterUtil::array_flip( self::$attribute_groups );

			// Mapping of input type to attribute group.
			self::$attribute_types = array(
				'select' => array( 'border-style', 'font-style', 'font-weight', 'text-decoration' ),
			);

			self::$attribute_types_inverse = UrvanovSyntaxHighlighterUtil::array_flip( self::$attribute_types );
		}
	}

	/**
	 * Init settings.
	 */
	public static function init_settings() {
		Urvanov_Syntax_Highlighter_Settings_WP::load_settings();
		self::init_fields();
		self::init_strings();

		if ( null === self::$settings ) {
			self::$settings = array(
				// Only things the theme editor needs.
				'cssThemePrefix' => Urvanov_Syntax_Highlighter_Themes::CSS_PREFIX,
				'cssInputPrefix' => Urvanov_Syntax_Highlighter_HTML_Element::CSS_INPUT_PREFIX,
				'attribute'      => self::ATTRIBUTE,
				'fields'         => self::$info_fields,
				'fieldsInverse'  => self::$info_fields_inverse,
				'prefix'         => 'urvanov-syntax-highlighter-theme-editor',
			);
		}
	}

	/**
	 * Init strings.
	 */
	public static function init_strings() {
		if ( null === self::$strings ) {
			self::$strings = array(
				// These appear only in the UI and can be translated.
				'userTheme'          => esc_html__( 'User-Defined Theme', 'urvanov-syntax-highlighter' ),
				'stockTheme'         => esc_html__( 'Stock Theme', 'urvanov-syntax-highlighter' ),
				'success'            => esc_html__( 'Success!', 'urvanov-syntax-highlighter' ),
				'fail'               => esc_html__( 'Failed!', 'urvanov-syntax-highlighter' ),
				'delete'             => esc_html__( 'Delete', 'urvanov-syntax-highlighter' ),
				'deleteThemeConfirm' => esc_html__( 'Are you sure you want to delete the %s theme?', 'urvanov-syntax-highlighter' ), // phpcs:ignore
				'deleteFail'         => esc_html__( 'Delete failed!', 'urvanov-syntax-highlighter' ),
				'duplicate'          => esc_html__( 'Duplicate', 'urvanov-syntax-highlighter' ),
				'newName'            => esc_html__( 'New Name', 'urvanov-syntax-highlighter' ),
				'duplicateFail'      => esc_html__( 'Duplicate failed!', 'urvanov-syntax-highlighter' ),
				'checkLog'           => esc_html__( 'Please check the log for details.', 'urvanov-syntax-highlighter' ),
				'discardConfirm'     => esc_html__( 'Are you sure you want to discard all changes?', 'urvanov-syntax-highlighter' ),
				'editingTheme'       => esc_html__( 'Editing Theme: %s', 'urvanov-syntax-highlighter' ), // phpcs:ignorev
				'creatingTheme'      => esc_html__( 'Creating Theme: %s', 'urvanov-syntax-highlighter' ), // phpcs:ignore
				'submit'             => esc_html__( 'Submit Your Theme', 'urvanov-syntax-highlighter' ),
				'submitText'         => esc_html__( "Submit your User Theme for inclusion as a Stock Theme in Urvanov Syntax Highlighter! This will email me your theme - make sure it's considerably different from the stock themes :)", 'urvanov-syntax-highlighter' ),
				'message'            => esc_html__( 'Message', 'urvanov-syntax-highlighter' ),
				'submitMessage'      => esc_html__( 'Please include this theme in Urvanov Syntax Highlighter!', 'urvanov-syntax-highlighter' ),
				'submitSucceed'      => esc_html__( 'Submit was successful.', 'urvanov-syntax-highlighter' ),
				'submitFail'         => esc_html__( 'Submit failed!', 'urvanov-syntax-highlighter' ),
				'borderStyles'       => Urvanov_Syntax_Highlighter_HTML_Element::$border_styles,
			);
		}
	}

	/**
	 * Admin resources.
	 */
	public static function admin_resources() {
		global $urvanov_syntax_highlighter_version;

		self::init_settings();

		$path = dirname( dirname( __FILE__ ) );

		wp_enqueue_script(
			'cssjson_js',
			plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_CSSJSON_JS, $path ),
			array(),
			$urvanov_syntax_highlighter_version,
			true
		);

		wp_enqueue_script(
			'jquery_colorpicker_js',
			plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_JS_JQUERY_COLORPICKER, $path ),
			array(
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
			),
			$urvanov_syntax_highlighter_version,
			true
		);

		wp_enqueue_script(
			'jquery_tinycolor_js',
			plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_JS_TINYCOLOR, $path ),
			array(),
			$urvanov_syntax_highlighter_version,
			true
		);

		UrvanovSyntaxHighlighterLog::debug( self::$settings, 'Theme editor settings' );

		if ( URVANOV_SYNTAX_HIGHLIGHTER_MINIFY ) {
			wp_enqueue_script(
				'urvanov_syntax_highlighter_theme_editor',
				plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_THEME_EDITOR_JS, $path ),
				array(
					'jquery',
					'urvanov_syntax_highlighter_js',
					'urvanov_syntax_highlighter_admin_js',
					'cssjson_js',
					'jquery_colorpicker_js',
					'jquery_tinycolor_js',
				),
				$urvanov_syntax_highlighter_version,
				true
			);
		} else {
			wp_enqueue_script(
				'urvanov_syntax_highlighter_theme_editor',
				plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_THEME_EDITOR_JS, $path ),
				array(
					'jquery',
					'urvanov_syntax_highlighter_util_js',
					'urvanov_syntax_highlighter_admin_js',
					'cssjson_js',
					'jquery_colorpicker_js',
					'jquery_tinycolor_js',
				),
				$urvanov_syntax_highlighter_version,
				true
			);
		}

		wp_localize_script( 'urvanov_syntax_highlighter_theme_editor', 'UrvanovSyntaxHighlighterThemeEditorSettings', self::$settings );
		wp_localize_script( 'urvanov_syntax_highlighter_theme_editor', 'UrvanovSyntaxHighlighterThemeEditorStrings', self::$strings );

		wp_enqueue_style( 'urvanov_syntax_highlighter_theme_editor', plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_THEME_EDITOR_STYLE, $path ), array( 'wp-jquery-ui-dialog' ), $urvanov_syntax_highlighter_version );
		wp_enqueue_style( 'jquery_colorpicker', plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_CSS_JQUERY_COLORPICKER, $path ), array(), $urvanov_syntax_highlighter_version );
	}

	/**
	 * Form.
	 *
	 * @param array $inputs Inputs.
	 *
	 * @return string
	 */
	public static function form( array $inputs ): string {
		$str       = '<form class="' . esc_attr( self::$settings['prefix'] ) . '-form"><table>';
		$sep_count = 0;

		foreach ( $inputs as $input ) {
			if ( $input instanceof Urvanov_Syntax_Highlighter_HTML_Input ) {
				$str .= self::form_field( $input->name, $input );
			} elseif ( $input instanceof Urvanov_Syntax_Highlighter_HTML_Separator ) {
				$sep_class = '';

				if ( $input instanceof Urvanov_Syntax_Highlighter_HTML_Title ) {
					$sep_class .= ' title';
				}

				if ( 0 === $sep_count ) {
					$sep_class .= ' first';
				}

				$str .= '<tr class="separator' . esc_attr( $sep_class ) . '"><td colspan="2"><div class="content">' . wp_kses_post( $input->name ) . '</div></td></tr>';
				$sep_count ++;
			} elseif ( is_array( $input ) && count( $input ) > 1 ) {
				$name    = $input[0];
				$fields  = '<table class="split-field"><tr>';
				$percent = 100 / count( $input );

				$count = count( $input );
				for ( $i = 1; $i < $count; $i ++ ) {
					$class   = count( $input ) - 1 === $i ? 'class="last"' : '';
					$fields .= '<td ' . $class . ' style="width: ' . $percent . '%">' . $input[ $i ] . '</td>';
				}

				$fields .= '</tr></table>';
				$str    .= self::form_field( $name, $fields, 'split' );
			}
		}

		$str .= '</table></form>';

		return $str;
	}

	/**
	 * Form field.
	 *
	 * @param string $name  Name.
	 * @param string $field Field.
	 * @param string $class Class.
	 *
	 * @return string
	 */
	public static function form_field( string $name, string $field, $class = '' ): string {
		return '<tr><td class="field ' . esc_attr( $class ) . '">' . wp_kses_post( $name ) . '</td><td class="value ' . esc_attr( $class ) . '">' . wp_kses_post( $field ) . '</td></tr>';
	}

	/**
	 * Content.
	 */
	public static function content() {
		self::init_settings();

		$t_information  = esc_html__( 'Information', 'urvanov-syntax-highlighter' );
		$t_highlighting = esc_html__( 'Highlighting', 'urvanov-syntax-highlighter' );
		$t_frame        = esc_html__( 'Frame', 'urvanov-syntax-highlighter' );
		$t_lines        = esc_html__( 'Lines', 'urvanov-syntax-highlighter' );
		$t_numbers      = esc_html__( 'Line Numbers', 'urvanov-syntax-highlighter' );
		$t_toolbar      = esc_html__( 'Toolbar', 'urvanov-syntax-highlighter' );

		$t_background    = esc_html__( 'Background', 'urvanov-syntax-highlighter' );
		$t_text          = esc_html__( 'Text', 'urvanov-syntax-highlighter' );
		$t_border        = esc_html__( 'Border', 'urvanov-syntax-highlighter' );
		$t_top_border    = esc_html__( 'Top Border', 'urvanov-syntax-highlighter' );
		$t_bottom_border = esc_html__( 'Bottom Border', 'urvanov-syntax-highlighter' );
		$t_border_right  = esc_html__( 'Right Border', 'urvanov-syntax-highlighter' );

		$t_hover          = esc_html__( 'Hover', 'urvanov-syntax-highlighter' );
		$t_active         = esc_html__( 'Active', 'urvanov-syntax-highlighter' );
		$t_pressed        = esc_html__( 'Pressed', 'urvanov-syntax-highlighter' );
		$t_hover_pressed  = esc_html__( 'Pressed & Hover', 'urvanov-syntax-highlighter' );
		$t_active_pressed = esc_html__( 'Pressed & Active', 'urvanov-syntax-highlighter' );

		$t_title   = esc_html__( 'Title', 'urvanov-syntax-highlighter' );
		$t_buttons = esc_html__( 'Buttons', 'urvanov-syntax-highlighter' );

		$t_normal         = esc_html__( 'Normal', 'urvanov-syntax-highlighter' );
		$t_inline         = esc_html__( 'Inline', 'urvanov-syntax-highlighter' );
		$t_striped        = esc_html__( 'Striped', 'urvanov-syntax-highlighter' );
		$t_marked         = esc_html__( 'Marked', 'urvanov-syntax-highlighter' );
		$t_striped_marked = esc_html__( 'Striped & Marked', 'urvanov-syntax-highlighter' );
		$t_language       = esc_html__( 'Language', 'urvanov-syntax-highlighter' );

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

		<h3 id="<?php echo esc_attr( self::$settings['prefix'] ); ?>-name"></h3>
		<div id="<?php echo esc_attr( self::$settings['prefix'] ); ?>-info"></div>

		<p>
			<a id="urvanov-syntax-highlighter-editor-back" class="button-primary">
				<?php esc_html_e( 'Back To Settings', 'urvanov-syntax-highlighter' ); ?>
			</a>
			<a id="urvanov-syntax-highlighter-editor-save" data-nonce="<?php echo esc_attr( wp_create_nonce( 'theme_editor_save' ) ); ?>" class="button-primary"><?php esc_html_e( 'Save', 'urvanov-syntax-highlighter' ); ?></a>
			<span id="urvanov-syntax-highlighter-editor-status"></span>
		</p>

		<div id="urvanov-syntax-highlighter-editor-top-controls"></div>

		<table id="urvanov-syntax-highlighter-editor-table" style="width: 100%;padding:0;border-spacing:5px;">
			<tr>
				<td id="urvanov-syntax-highlighter-editor-preview-wrapper">
					<div id="urvanov-syntax-highlighter-editor-preview"></div>
				</td>
				<div id="urvanov-syntax-highlighter-editor-preview-css"></div>
				<td id="urvanov-syntax-highlighter-editor-control-wrapper">
					<div id="urvanov-syntax-highlighter-editor-controls">
						<ul>
							<li title="<?php echo esc_attr( $t_information ); ?>"><a class="urvanov-syntax-highlighter-tab-information" href="#tabs-1"></a></li>
							<li title="<?php echo esc_attr( $t_highlighting ); ?>"><a class="urvanov-syntax-highlighter-tab-highlighting" href="#tabs-2"></a></li>
							<li title="<?php echo esc_attr( $t_frame ); ?>"><a class="urvanov-syntax-highlighter-tab-frame" href="#tabs-3"></a></li>
							<li title="<?php echo esc_attr( $t_lines ); ?>"><a class="urvanov-syntax-highlighter-tab-lines" href="#tabs-4"></a></li>
							<li title="<?php echo esc_attr( $t_numbers ); ?>"><a class="urvanov-syntax-highlighter-tab-numbers" href="#tabs-5"></a></li>
							<li title="<?php echo esc_attr( $t_toolbar ); ?>"><a class="urvanov-syntax-highlighter-tab-toolbar" href="#tabs-6"></a></li>
						</ul>
						<div id="tabs-1">
							<?php
							self::create_attributes_form(
								array(
									new Urvanov_Syntax_Highlighter_HTML_Title( $t_information ),
								)
							);
							?>
							<div id="tabs-1-contents"></div>
							<!-- Auto-filled by theme_editor.js -->
						</div>
						<div id="tabs-2">
							<?php

							$highlight = ' .crayon-pre';
							$elems     = array(
								'c'  => esc_html__( 'Comment', 'urvanov-syntax-highlighter' ),
								's'  => esc_html__( 'String', 'urvanov-syntax-highlighter' ),
								'p'  => esc_html__( 'Preprocessor', 'urvanov-syntax-highlighter' ),
								'ta' => esc_html__( 'Tag', 'urvanov-syntax-highlighter' ),
								'k'  => esc_html__( 'Keyword', 'urvanov-syntax-highlighter' ),
								'st' => esc_html__( 'Statement', 'urvanov-syntax-highlighter' ),
								'r'  => esc_html__( 'Reserved', 'urvanov-syntax-highlighter' ),
								't'  => esc_html__( 'Type', 'urvanov-syntax-highlighter' ),
								'm'  => esc_html__( 'Modifier', 'urvanov-syntax-highlighter' ),
								'i'  => esc_html__( 'Identifier', 'urvanov-syntax-highlighter' ),
								'e'  => esc_html__( 'Entity', 'urvanov-syntax-highlighter' ),
								'v'  => esc_html__( 'Variable', 'urvanov-syntax-highlighter' ),
								'cn' => esc_html__( 'Constant', 'urvanov-syntax-highlighter' ),
								'o'  => esc_html__( 'Operator', 'urvanov-syntax-highlighter' ),
								'sy' => esc_html__( 'Symbol', 'urvanov-syntax-highlighter' ),
								'n'  => esc_html__( 'Notation', 'urvanov-syntax-highlighter' ),
								'f'  => esc_html__( 'Faded', 'urvanov-syntax-highlighter' ),
								'h'  => esc_html__( 'HTML', 'urvanov-syntax-highlighter' ),
								''   => esc_html__( 'Unhighlighted', 'urvanov-syntax-highlighter' ),
							);
							$atts      = array( new Urvanov_Syntax_Highlighter_HTML_Title( $t_highlighting ) );
							foreach ( $elems as $class => $name ) {
								$full_class = '' !== $class ? $highlight . ' .crayon-' . $class : $highlight;
								$atts[]     = array(
									$name,
									self::create_attribute( $full_class, 'color' ),
									self::create_attribute( $full_class, 'font-weight' ),
									self::create_attribute( $full_class, 'font-style' ),
									self::create_attribute( $full_class, 'text-decoration' ),
								);
							}
							self::create_attributes_form( $atts );
							?>
						</div>
						<div id="tabs-3">
							<?php
							$inline = '-inline';
							self::create_attributes_form(
								array(
									new Urvanov_Syntax_Highlighter_HTML_Title( $t_frame ),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $t_normal ),
									array(
										$t_border,
										self::create_attribute( '', 'border-width' ),
										self::create_attribute( '', 'border-color' ),
										self::create_attribute( '', 'border-style' ),
									),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $t_inline ),
									self::create_attribute( $inline, 'background', $t_background ),
									array(
										$t_border,
										self::create_attribute( $inline, 'border-width' ),
										self::create_attribute( $inline, 'border-color' ),
										self::create_attribute( $inline, 'border-style' ),
									),
								)
							);
							?>
						</div>
						<div id="tabs-4">
							<?php
							$striped_line        = ' .crayon-striped-line';
							$marked_line         = ' .crayon-marked-line';
							$striped_marked_line = ' .crayon-marked-line.crayon-striped-line';

							self::create_attributes_form(
								array(
									new Urvanov_Syntax_Highlighter_HTML_Title( $t_lines ),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $t_normal ),
									self::create_attribute( '', 'background', $t_background ),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $t_striped ),
									self::create_attribute( $striped_line, 'background', $t_background ),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $t_marked ),
									self::create_attribute( $marked_line, 'background', $t_background ),
									array(
										$t_border,
										self::create_attribute( $marked_line, 'border-width' ),
										self::create_attribute( $marked_line, 'border-color' ),
										self::create_attribute( $marked_line, 'border-style' ),
									),
									self::create_attribute( $marked_line . $top, 'border-top-style', $t_top_border ),
									self::create_attribute( $marked_line . $bottom, 'border-bottom-style', $t_bottom_border ),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $t_striped_marked ),
									self::create_attribute( $striped_marked_line, 'background', $t_background ),
								)
							);
							?>
						</div>
						<div id="tabs-5">
							<?php
							$nums               = ' .crayon-table .crayon-nums';
							$striped_num        = ' .crayon-striped-num';
							$marked_num         = ' .crayon-marked-num';
							$striped_marked_num = ' .crayon-marked-num.crayon-striped-num';

							self::create_attributes_form(
								array(
									new Urvanov_Syntax_Highlighter_HTML_Title( $t_numbers ),
									array(
										$t_border_right,
										self::create_attribute( $nums, 'border-right-width' ),
										self::create_attribute( $nums, 'border-right-color' ),
										self::create_attribute( $nums, 'border-right-style' ),
									),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $t_normal ),
									self::create_attribute( $nums, 'background', $t_background ),
									self::create_attribute( $nums, 'color', $t_text ),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $t_striped ),
									self::create_attribute( $striped_num, 'background', $t_background ),
									self::create_attribute( $striped_num, 'color', $t_text ),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $t_marked ),
									self::create_attribute( $marked_num, 'background', $t_background ),
									self::create_attribute( $marked_num, 'color', $t_text ),
									array(
										$t_border,
										self::create_attribute( $marked_num, 'border-width' ),
										self::create_attribute( $marked_num, 'border-color' ),
										self::create_attribute( $marked_num, 'border-style' ),
									),
									self::create_attribute( $marked_num . $top, 'border-top-style', $t_top_border ),
									self::create_attribute( $marked_num . $bottom, 'border-bottom-style', $t_bottom_border ),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $t_striped_marked ),
									self::create_attribute( $striped_marked_num, 'background', $t_background ),
									self::create_attribute( $striped_marked_num, 'color', $t_text ),
								)
							);
							?>
						</div>
						<div id="tabs-6">
							<?php
							$toolbar  = ' .crayon-toolbar';
							$title    = ' .crayon-title';
							$button   = ' .crayon-button';
							$info     = ' .crayon-info';
							$language = ' .crayon-language';
							self::create_attributes_form(
								array(
									new Urvanov_Syntax_Highlighter_HTML_Title( $t_toolbar ),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $t_frame ),
									self::create_attribute( $toolbar, 'background', $t_background ),
									array(
										$t_bottom_border,
										self::create_attribute( $toolbar, 'border-bottom-width' ),
										self::create_attribute( $toolbar, 'border-bottom-color' ),
										self::create_attribute( $toolbar, 'border-bottom-style' ),
									),
									array(
										$t_title,
										self::create_attribute( $title, 'color' ),
										self::create_attribute( $title, 'font-weight' ),
										self::create_attribute( $title, 'font-style' ),
										self::create_attribute( $title, 'text-decoration' ),
									),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $t_buttons ),
									self::create_attribute( $button, 'background-color', $t_background ),
									self::create_attribute( $button . $hover, 'background-color', $t_hover ),
									self::create_attribute( $button . $active, 'background-color', $t_active ),
									self::create_attribute( $button . $pressed, 'background-color', $t_pressed ),
									self::create_attribute( $button . $pressed . $hover, 'background-color', $t_hover_pressed ),
									self::create_attribute( $button . $pressed . $active, 'background-color', $t_active_pressed ),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $t_information . ' ' . esc_html__( '(Used for Copy/Paste)', 'urvanov-syntax-highlighter' ) ),
									self::create_attribute( $info, 'background', $t_background ),
									array(
										$t_text,
										self::create_attribute( $info, 'color' ),
										self::create_attribute( $info, 'font-weight' ),
										self::create_attribute( $info, 'font-style' ),
										self::create_attribute( $info, 'text-decoration' ),
									),
									array(
										$t_bottom_border,
										self::create_attribute( $info, 'border-bottom-width' ),
										self::create_attribute( $info, 'border-bottom-color' ),
										self::create_attribute( $info, 'border-bottom-style' ),
									),
									new Urvanov_Syntax_Highlighter_HTML_Separator( $t_language ),
									array(
										$t_text,
										self::create_attribute( $language, 'color' ),
										self::create_attribute( $language, 'font-weight' ),
										self::create_attribute( $language, 'font-style' ),
										self::create_attribute( $language, 'text-decoration' ),
									),
									self::create_attribute( $language, 'background-color', $t_background ),
								)
							);
							?>
						</div>
					</div>
				</td>
			</tr>
		</table>
		<?php

		exit();
	}

	/**
	 * Create attribute.
	 *
	 * @param string $element   Element.
	 * @param string $attribute Attribute.
	 * @param string $name      Name.
	 *
	 * @return Urvanov_Syntax_Highlighter_HTML_Input|Urvanov_Syntax_Highlighter_HTML_Select
	 */
	public static function create_attribute( string $element, string $attribute, $name = null ) {
		$group = self::get_attribute_group( $attribute );
		$type  = self::get_attribute_type( $group );

		if ( 'select' === $type ) {
			$input = new Urvanov_Syntax_Highlighter_HTML_Select( $element . '_' . $attribute, $name );
			if ( 'border-style' === $group ) {
				$input->add_options( Urvanov_Syntax_Highlighter_HTML_Element::$border_styles );
			} elseif ( 'float' === $group ) {
				$input->add_options(
					array(
						'left',
						'right',
						'both',
						'none',
						'inherit',
					)
				);
			} elseif ( 'font-style' === $group ) {
				$input->add_options(
					array(
						'normal',
						'italic',
						'oblique',
						'inherit',
					)
				);
			} elseif ( 'font-weight' === $group ) {
				$input->add_options(
					array(
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
					)
				);
			} elseif ( 'text-decoration' === $group ) {
				$input->add_options(
					array(
						'none',
						'underline',
						'overline',
						'line-through',
						'blink',
						'inherit',
					)
				);
			}
		} else {
			$input = new Urvanov_Syntax_Highlighter_HTML_Input( $element . '_' . $attribute, $name );
		}
		$input->add_class( self::ATTRIBUTE );
		$input->add_attributes(
			array(
				'data-element'   => $element,
				'data-attribute' => $attribute,
				'data-group'     => $group,
			)
		);

		return $input;
	}

	/**
	 * Create attributes form.
	 *
	 * @param array $atts Attributes.
	 */
	public static function create_attributes_form( array $atts ) {
		// phpcs:ignore WordPress.Security.EscapeOutput
		echo self::form( $atts );
	}

	/**
	 * Save.
	 */
	public static function save() {
		$save_args = new Urvanov_Syntax_Highlighter_Theme_Editor_Save();
		$save_args->initialize_from_post();

		self::save_from_args( $save_args );
	}

	/**
	 * Saves the given theme id and css, making any necessary path and id changes to ensure the new theme is valid.
	 * Echos 0 on failure, 1 on success and 2 on success and if paths have changed.
	 *
	 * @param object $save_args Saved args.
	 */
	public static function save_from_args( $save_args = null ) {
		Urvanov_Syntax_Highlighter_Settings_WP::load_settings();

		$old_theme = Urvanov_Syntax_Highlighter_Resources::themes()->get( $save_args->id );

		UrvanovSyntaxHighlighterLog::log( $save_args->old_id, 'save_args->old_id' );
		UrvanovSyntaxHighlighterLog::log( $save_args->name, 'save_args->name' );

		if ( ! empty( $save_args->old_id ) && ! empty( $save_args->css ) && ! empty( $save_args->name ) ) {

			// By default, expect a user theme to be saved - prevents editing stock themes.
			// If in DEBUG mode, then allow editing stock themes.
			$user     = null !== $old_theme && $save_args->allow_edit_stock_theme ? $old_theme->user() : true;
			$old_path = Urvanov_Syntax_Highlighter_Resources::themes()->path( $save_args->old_id );
			$old_dir  = Urvanov_Syntax_Highlighter_Resources::themes()->dirpath_for_id( $save_args->old_id );

			// Create an instance to use functions, since late static binding is only available in 5.3 (PHP kinda sucks).
			$theme           = Urvanov_Syntax_Highlighter_Resources::themes()->resource_instance();
			$new_id          = $theme->clean_id( $save_args->name );
			$save_args->name = Urvanov_Syntax_Highlighter_Resource::clean_name( $new_id );
			$new_path        = Urvanov_Syntax_Highlighter_Resources::themes()->path( $new_id, $user );

			UrvanovSyntaxHighlighterLog::log( $old_path, 'oldPath' );
			UrvanovSyntaxHighlighterLog::log( $new_path, 'newPath' );
			$new_dir = Urvanov_Syntax_Highlighter_Resources::themes()->dirpath_for_id( $new_id, $user );
			UrvanovSyntaxHighlighterLog::log( $new_dir, 'newDir' );

			$exists = Urvanov_Syntax_Highlighter_Resources::themes()->is_loaded( $new_id ) || ( is_file( $new_path ) && is_file( $old_path ) );
			if ( $exists && $old_path !== $new_path ) {

				// Never allow overwriting a theme with a different id!.
				echo - 3;
				exit();
			}

			if ( $old_path === $new_path && false === $save_args->allow_edit ) {
				// Don't allow editing.
				echo - 4;
				exit();
			}

			// Create the new path if needed.
			if ( ! is_dir( $new_dir ) ) {
				wp_mkdir_p( $new_dir );
				$image_src = $old_dir . 'images';

				if ( is_dir( $image_src ) ) {
					try {
						// Copy image folder.
						UrvanovSyntaxHighlighterUtil::copyDir( $image_src, $new_dir . 'images', 'wp_mkdir_p' );
					} catch ( Exception $e ) {
						UrvanovSyntaxHighlighterLog::syslog( $e->getMessage(), 'THEME SAVE' );
					}
				}
			}

			$refresh    = false;
			$replace_id = $save_args->old_id;

			UrvanovSyntaxHighlighterLog::log( $replace_id, '$replace_id' );

			// Replace ids in the CSS.
			if ( ! is_file( $old_path ) || strpos( $save_args->css, Urvanov_Syntax_Highlighter_Themes::CSS_PREFIX . $save_args->id ) === false ) {

				// The old path/id is no longer valid - something has gone wrong - we should refresh afterwards.
				$refresh = true;
			}

			// XXX This is case sensitive to avoid modifying text, but it means that CSS must be in lowercase.
			UrvanovSyntaxHighlighterLog::debug(
				"before caseSensitivePregReplace replaceId=$replace_id newID=$new_id css=" . str_replace(
					array(
						"\r\n",
						"\r",
						"\n",
					),
					'q',
					$save_args->css
				),
				'caseSensitivePregReplace'
			);

			$save_args->css = preg_replace( '#(?<=' . Urvanov_Syntax_Highlighter_Themes::CSS_PREFIX . ')' . $replace_id . '\b#ms', $new_id, $save_args->css );
			UrvanovSyntaxHighlighterLog::debug(
				"after caseSensitivePregReplace replaceId=$replace_id newID=$new_id css=" . str_replace(
					array(
						"\r\n",
						"\r",
						"\n",
					),
					'q',
					$save_args->css
				),
				'caseSensitivePregReplace'
			);

			// Replace the name with the new one.
			$info         = self::get_css_info( $save_args->css );
			$info['name'] = $save_args->name;

			UrvanovSyntaxHighlighterLog::syslog( $save_args->name, 'change name to ' );
			UrvanovSyntaxHighlighterLog::log( $save_args->name, 'change name to ' );
			$save_args->css = self::set_css_info( $save_args->css, $info );

			// TODO:  Replace this with wp_filesystem.
			$result  = file_put_contents( $new_path, $save_args->css );
			$success = false !== $result;

			if ( $success && $old_path !== $new_path ) {
				if ( Urvanov_Syntax_Highlighter_Themes::DEFAULT_THEME !== $save_args->id && $save_args->delete ) {

					// Only delete the old path if it isn't the default theme.
					try {

						// Delete the old path.
						UrvanovSyntaxHighlighterUtil::deleteDir( $old_dir );
					} catch ( Exception $e ) {
						UrvanovSyntaxHighlighterLog::syslog( $e->getMessage(), 'THEME SAVE' );
					}
				}

				// Refresh.
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

			// Set the new theme in settings.
			if ( $save_args->change_settings ) {
				Urvanov_Syntax_Highlighter_Global_Settings::set( Urvanov_Syntax_Highlighter_Settings::THEME, $new_id );
				Urvanov_Syntax_Highlighter_Settings_WP::save_settings();
			}
		} else {
			UrvanovSyntaxHighlighterLog::syslog( "save_args->id=$save_args->id\n\nsave_args->name=$save_args->name", 'THEME SAVE' );
			echo - 1;
		}

		exit();
	}

	/**
	 * Duplicate.
	 */
	public static function duplicate() {
		Urvanov_Syntax_Highlighter_Settings_WP::load_settings();

		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'theme_editor_action' ) ) {
			$save_args                         = new Urvanov_Syntax_Highlighter_Theme_Editor_Save();
			$save_args->old_id                 = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
			$old_path                          = Urvanov_Syntax_Highlighter_Resources::themes()->path( $save_args->old_id );
			$save_args->css                    = file_get_contents( $old_path ); // TODO: Replace with wp_filesystem.
			$save_args->delete                 = false;
			$save_args->allow_edit             = false;
			$save_args->allow_edit_stock_theme = false;
			$save_args->name                   = stripslashes( sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ) );

			self::save_from_args( $save_args );
		} else {
			wp_nonce_ays( 'expired' );
		}
	}

	/**
	 * Delete.
	 */
	public static function delete() {
		Urvanov_Syntax_Highlighter_Settings_WP::load_settings();

		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_email( wp_unslash( $_POST['nonce'] ) ), 'theme_editor_action' ) ) {
			$id  = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
			$dir = Urvanov_Syntax_Highlighter_Resources::themes()->dirpath_for_id( $id );

			if ( is_dir( $dir ) && Urvanov_Syntax_Highlighter_Resources::themes()->exists( $id ) ) {
				try {
					UrvanovSyntaxHighlighterUtil::deleteDir( $dir );
					Urvanov_Syntax_Highlighter_Global_Settings::set( Urvanov_Syntax_Highlighter_Settings::THEME, Urvanov_Syntax_Highlighter_Themes::DEFAULT_THEME );
					Urvanov_Syntax_Highlighter_Settings_WP::save_settings();
					echo 1;
				} catch ( Exception $e ) {
					UrvanovSyntaxHighlighterLog::syslog( $e->getMessage(), 'THEME SAVE' );
					echo - 2;
				}
			} else {
				echo - 1;
			}
		} else {
			wp_nonce_ays( 'expired' );
		}

		exit();
	}

	/**
	 * Submit.
	 */
	public static function submit() {
		global $urvanov_syntax_highlighter_email;

		Urvanov_Syntax_Highlighter_Settings_WP::load_settings();

		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'theme_editor_action' ) ) {
			$id      = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
			$message = sanitize_text_field( wp_unslash( $_POST['message'] ?? '' ) );
			$dir     = Urvanov_Syntax_Highlighter_Resources::themes()->dirpath_for_id( $id );
			$dest    = $dir . 'tmp';

			wp_mkdir_p( $dest );

			if ( is_dir( $dir ) && Urvanov_Syntax_Highlighter_Resources::themes()->exists( $id ) ) {
				try {
					$zip_file = UrvanovSyntaxHighlighterUtil::createZip( $dir, $dest, true );
					$result   = UrvanovSyntaxHighlighterUtil::emailFile(
						array(
							'to'      => $urvanov_syntax_highlighter_email,
							'from'    => get_bloginfo( 'admin_email' ),
							'subject' => 'Theme Editor Submission',
							'message' => $message,
							'file'    => $zip_file,
						)
					);

					UrvanovSyntaxHighlighterUtil::deleteDir( $dest );

					if ( $result ) {
						echo 1;
					} else {
						echo - 3;
					}
				} catch ( Exception $e ) {
					UrvanovSyntaxHighlighterLog::syslog( $e->getMessage(), 'THEME SUBMIT' );
					echo - 2;
				}
			} else {
				echo - 1;
			}
		} else {
			wp_nonce_ays( 'expired' );
		}

		exit();
	}

	/**
	 * Get CSS Info.
	 *
	 * @param string $css CSS.
	 *
	 * @return array
	 */
	public static function get_css_info( string $css ): array {
		UrvanovSyntaxHighlighterLog::debug( "css=$css", 'get_css_info' );
		$info = array();

		preg_match( self::RE_COMMENT, $css, $matches );
		if ( count( $matches ) ) {
			$comment = $matches[0];
			preg_match_all( '#([^\r\n:]*[^\r\n\s:])\s*:\s*([^\r\n]+)#msi', $comment, $matches );

			if ( count( $matches ) ) {
				$count = count( $matches[1] );

				for ( $i = 0; $i < $count; $i ++ ) {
					$name                              = $matches[1][ $i ];
					$value                             = $matches[2][ $i ];
					$info[ self::get_field_id( $name ) ] = $value;
				}
			}
		}

		UrvanovSyntaxHighlighterLog::debug( $info, 'get_css_info' );

		return $info;
	}

	/**
	 * CSS Info to string.
	 *
	 * @param array $info Info array.
	 *
	 * @return string
	 */
	public static function css_info_to_string( array $info ): string {
		UrvanovSyntaxHighlighterLog::log( $info, 'css_info_to_string' );
		$str = "/*\n";

		foreach ( $info as $id => $value ) {
			$str .= self::get_field_name( $id ) . ': ' . $value . "\n";
		}

		$str .= '*/';

		UrvanovSyntaxHighlighterLog::log( "result = $str", 'css_info_to_string' );

		return $str;
	}

	/**
	 * Set CSS Info.
	 *
	 * @param string $css  CSS.
	 * @param array  $info Info.
	 *
	 * @return array|string|string[]|null
	 */
	public static function set_css_info( string $css, array $info ) {
		return preg_replace( self::RE_COMMENT, self::css_info_to_string( $info ), $css );
	}

	/**
	 * Get Field ID.
	 *
	 * @param string $name Name.
	 *
	 * @return array|mixed|string|string[]|null
	 */
	public static function get_field_id( string $name ) {
		if ( isset( self::$info_fields_inverse[ $name ] ) ) {
			return self::$info_fields_inverse[ $name ];
		} else {
			return Urvanov_Syntax_Highlighter_User_Resource::clean_id_static( $name );
		}
	}

	/**
	 * Get field name.
	 *
	 * @param mixed $id ID.
	 *
	 * @return mixed|string
	 */
	public static function get_field_name( $id ) {
		self::init_fields();

		if ( isset( self::$info_fields[ $id ] ) ) {
			return self::$info_fields[ $id ];
		} else {
			return Urvanov_Syntax_Highlighter_User_Resource::clean_name( $id );
		}
	}

	/**
	 * Get attribute group.
	 *
	 * @param mixed $attribute Attribute.
	 *
	 * @return mixed
	 */
	public static function get_attribute_group( $attribute ) {
		if ( isset( self::$attribute_groups_inverse[ $attribute ] ) ) {
			return self::$attribute_groups_inverse[ $attribute ];
		} else {
			return $attribute;
		}
	}

	/**
	 * Get attribute type.
	 *
	 * @param mixed $group Group.
	 *
	 * @return mixed|string
	 */
	public static function get_attribute_type( $group ) {
		if ( isset( self::$attribute_types_inverse[ $group ] ) ) {
			return self::$attribute_types_inverse[ $group ];
		} else {
			return 'text';
		}
	}
}

<?php
/**
 * Tag Editor WP Class
 *
 * @package   Crayon Syntax Highlighter
 * @author    Fedor Urvanov, Aram Kocharyan
 * @copyright Copyright 2013, Aram Kocharyan
 * @link      https://urvanov.ru
 */

defined( 'ABSPATH' ) || exit;

require_once URVANOV_SYNTAX_HIGHLIGHTER_ROOT_PATH . 'class-urvanov-syntax-highlighter-settings.php';

/**
 * Class UrvanovSyntaxHighlighterTagEditorWP
 */
class UrvanovSyntaxHighlighterTagEditorWP {

	/**
	 * Settings.
	 *
	 * @var null
	 */
	public static $settings = null;

	/**
	 * Init.
	 */
	public static function init() {
		// Hooks.
		if ( URVANOV_SYNTAX_HIGHLIGHTER_TAG_EDITOR ) {
			Urvanov_Syntax_Highlighter_Settings_WP::load_settings( true );
			if ( is_admin() ) {
				// XXX Only runs in wp-admin.
				add_action( 'admin_print_scripts-post-new.php', 'UrvanovSyntaxHighlighterTagEditorWP::enqueue_resources' );
				add_action( 'admin_print_scripts-post.php', 'UrvanovSyntaxHighlighterTagEditorWP::enqueue_resources' );
				add_filter( 'tiny_mce_before_init', 'UrvanovSyntaxHighlighterTagEditorWP::init_tinymce' );
				// Must come after.
				add_action( 'admin_print_scripts-post-new.php', 'Urvanov_Syntax_Highlighter_Settings_WP::init_js_settings' );
				add_action( 'admin_print_scripts-post.php', 'Urvanov_Syntax_Highlighter_Settings_WP::init_js_settings' );
				self::addbuttons();
			} elseif ( Urvanov_Syntax_Highlighter_Global_Settings::val( Urvanov_Syntax_Highlighter_Settings::TAG_EDITOR_FRONT ) ) {
				// XXX This will always need to enqueue, but only runs on front end.
				add_action( 'wp', 'UrvanovSyntaxHighlighterTagEditorWP::enqueue_resources' );
				add_filter( 'tiny_mce_before_init', 'UrvanovSyntaxHighlighterTagEditorWP::init_tinymce' );
				self::addbuttons();
			}
		}
	}

	/**
	 * Init settings.
	 */
	public static function init_settings() {

		if ( ! self::$settings ) {
			// Add settings.
			self::$settings = array(
				'home_url'               => home_url(),
				'css'                    => 'urvanov-syntax-highlighter-te',
				'css_selected'           => 'urvanov-syntax-highlighter-selected',
				'code_css'               => '#urvanov-syntax-highlighter-code',
				'url_css'                => '#urvanov-syntax-highlighter-url',
				'url_info_css'           => '#urvanov-syntax-highlighter-te-url-info',
				'lang_css'               => '#urvanov-syntax-highlighter-lang',
				'title_css'              => '#urvanov-syntax-highlighter-title',
				'mark_css'               => '#urvanov-syntax-highlighter-mark',
				'range_css'              => '#urvanov-syntax-highlighter-range',
				'inline_css'             => 'urvanov-syntax-highlighter-inline',
				'inline_hide_css'        => 'urvanov-syntax-highlighter-hide-inline',
				'inline_hide_only_css'   => 'urvanov-syntax-highlighter-hide-inline-only',
				'hl_css'                 => '#urvanov-syntax-highlighter-highlight',
				'switch_html'            => '#content-html',
				'switch_tmce'            => '#content-tmce',
				'tinymce_button_generic' => '.mce-btn',
				'tinymce_button'         => 'a.mce_urvanov_syntax_highlighter_tinymce,.mce-i-urvanov_syntax_highlighter_tinymce',
				'tinymce_button_unique'  => 'mce_urvanov_syntax_highlighter_tinymce',
				'tinymce_highlight'      => 'mce-active',
				'submit_css'             => '#urvanov-syntax-highlighter-te-ok',
				'cancel_css'             => '#urvanov-syntax-highlighter-te-cancel',
				'content_css'            => '#urvanov-syntax-highlighter-te-content',
				'dialog_title_css'       => '#urvanov-syntax-highlighter-te-title',
				'submit_wrapper_css'     => '#urvanov-syntax-highlighter-te-submit-wrapper',
				'data_value'             => 'data-value',
				'attr_sep'               => Urvanov_Syntax_Highlighter_Global_Settings::val_str( Urvanov_Syntax_Highlighter_Settings::ATTR_SEP ),
				'css_sep'                => '_',
				'fallback_lang'          => Urvanov_Syntax_Highlighter_Global_Settings::val( Urvanov_Syntax_Highlighter_Settings::FALLBACK_LANG ),
				'add_text'               => Urvanov_Syntax_Highlighter_Global_Settings::val( Urvanov_Syntax_Highlighter_Settings::TAG_EDITOR_ADD_BUTTON_TEXT ),
				'edit_text'              => Urvanov_Syntax_Highlighter_Global_Settings::val( Urvanov_Syntax_Highlighter_Settings::TAG_EDITOR_EDIT_BUTTON_TEXT ),
				'quicktag_text'          => Urvanov_Syntax_Highlighter_Global_Settings::val( Urvanov_Syntax_Highlighter_Settings::TAG_EDITOR_QUICKTAG_BUTTON_TEXT ),
				'submit_add'             => esc_html__( 'Add', 'urvanov-syntax-highlighter' ),
				'submit_edit'            => esc_html__( 'Save', 'urvanov-syntax-highlighter' ),
				'bar'                    => '#urvanov-syntax-highlighter-te-bar',
				'bar_content'            => '#urvanov-syntax-highlighter-te-bar-content',
				'extensions'             => Urvanov_Syntax_Highlighter_Resources::langs()->extensions_inverted(),
			);
		}
	}

	/**
	 * Enqueue Resources.
	 */
	public static function enqueue_resources() {
		global $urvanov_syntax_highlighter_version;
		self::init_settings();
		if ( URVANOV_SYNTAX_HIGHLIGHTER_MINIFY ) {
			wp_deregister_script( 'urvanov_syntax_highlighter_js' );
			wp_enqueue_script(
				'urvanov_syntax_highlighter_js',
				plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_JS_TE_MIN, dirname( dirname( __FILE__ ) ) ),
				array(
					'jquery',
					'quicktags',
					'wp-rich-text',
					'wp-element',
					'wp-editor',
					'wp-blocks',
					'wp-components',
					'wp-html-entities',
				),
				$urvanov_syntax_highlighter_version,
				false
			);
			Urvanov_Syntax_Highlighter_Settings_WP::init_js_settings();
			wp_localize_script( 'urvanov_syntax_highlighter_js', 'UrvanovSyntaxHighlighterTagEditorSettings', self::$settings );
		} else {
			wp_enqueue_script( 'urvanov_syntax_highlighter_colorbox_js', plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_COLORBOX_JS, __FILE__ ), array( 'jquery' ), $urvanov_syntax_highlighter_version, false );
			wp_enqueue_style( 'urvanov_syntax_highlighter_colorbox_css', plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_COLORBOX_CSS, __FILE__ ), array(), $urvanov_syntax_highlighter_version );
			wp_enqueue_script(
				'urvanov_syntax_highlighter_te_js',
				plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_TAG_EDITOR_JS, __FILE__ ),
				array(
					'urvanov_syntax_highlighter_util_js',
					'urvanov_syntax_highlighter_colorbox_js',
					'wpdialogs',
					'wp-rich-text',
					'wp-element',
					'wp-editor',
					'wp-blocks',
					'wp-components',
					'wp-html-entities',
				),
				$urvanov_syntax_highlighter_version,
				false
			);
			wp_enqueue_script(
				'urvanov_syntax_highlighter_qt_js',
				plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_QUICKTAGS_JS, __FILE__ ),
				array(
					'quicktags',
					'urvanov_syntax_highlighter_te_js',
				),
				$urvanov_syntax_highlighter_version,
				false
			);
			wp_localize_script( 'urvanov_syntax_highlighter_te_js', 'UrvanovSyntaxHighlighterTagEditorSettings', self::$settings );
			Urvanov_Syntax_Highlighter_Settings_WP::other_scripts();
		}
	}

	/**
	 * Init TinyMCE.
	 *
	 * @param array $init Init.
	 *
	 * @return array
	 */
	public static function init_tinymce( array $init ): array {
		if ( ! array_key_exists( 'extended_valid_elements', $init ) ) {
			$init['extended_valid_elements'] = '';
		}

		$init['extended_valid_elements'] .= ',pre[*],code[*],iframe[*]';

		return $init;
	}

	/**
	 * Add buttons.
	 */
	public static function addbuttons() {
		// Add only in Rich Editor mode.
		add_filter( 'mce_external_plugins', 'UrvanovSyntaxHighlighterTagEditorWP::add_plugin' );
		add_filter( 'mce_buttons', 'UrvanovSyntaxHighlighterTagEditorWP::register_buttons' );
		add_filter( 'bbp_before_get_the_content_parse_args', 'UrvanovSyntaxHighlighterTagEditorWP::bbp_get_the_content_args' );
	}

	/**
	 * BbPress Get content args.
	 *
	 * @param array $args Args.
	 *
	 * @return array
	 */
	public static function bbp_get_the_content_args( array $args ): array {
		// Turn off "teeny" to allow the bbPress TinyMCE to display external plugins.
		return array_merge( $args, array( 'teeny' => false ) );
	}

	/**
	 * Register buttons.
	 *
	 * @param array $buttons Button array.
	 *
	 * @return array
	 */
	public static function register_buttons( array $buttons ): array {
		array_push( $buttons, 'separator', 'urvanov_syntax_highlighter_tinymce' );

		return $buttons;
	}

	/**
	 * Add plugin.
	 *
	 * @param array $plugin_array Plugin array.
	 *
	 * @return array
	 */
	public static function add_plugin( array $plugin_array ): array {
		$plugin_array['urvanov_syntax_highlighter_tinymce'] = plugins_url( URVANOV_SYNTAX_HIGHLIGHTER_TINYMCE_JS, __FILE__ );

		return $plugin_array;
	}

	/**
	 * Select resource.
	 *
	 * @param string $id        ID.
	 * @param object $resources Resources.
	 * @param string $current   Current.
	 * @param bool   $set_class Set class.
	 */
	public static function select_resource( string $id, $resources = null, $current = '', $set_class = true ) {
		$id = Urvanov_Syntax_Highlighter_Settings::PREFIX . $id;
		if ( count( $resources ) > 0 ) {
			$class = $set_class ? 'class="' . Urvanov_Syntax_Highlighter_Settings::SETTING . ' ' . Urvanov_Syntax_Highlighter_Settings::SETTING_SPECIAL . '"' : '';
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '" ' . esc_html( $class ) . ' ' . esc_html( Urvanov_Syntax_Highlighter_Settings::SETTING_ORIG_VALUE ) . '="' . esc_attr( $current ) . '">';

			foreach ( $resources as $resource ) {
				$asterisk = $current === $resource->id() ? ' *' : '';
				echo '<option value="' . esc_attr( $resource->id() ) . '" ' . selected( $current, $resource->id() ) . ' >' . esc_html( $resource->name() ) . esc_html( $asterisk ) . '</option>';
			}

			echo '</select>';
		} else {
			// None found, default to text box.
			echo '<input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '" class="' . esc_attr( Urvanov_Syntax_Highlighter_Settings::SETTING . ' ' . Urvanov_Syntax_Highlighter_Settings::SETTING_SPECIAL ) . '" />';
		}
	}

	/**
	 * Checkbox.
	 *
	 * @param string $id ID.
	 */
	public static function checkbox( $id = '' ) {
		$id = Urvanov_Syntax_Highlighter_Settings::PREFIX . $id;
		echo '<input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '" class="' . esc_attr( Urvanov_Syntax_Highlighter_Settings::SETTING . ' ' . Urvanov_Syntax_Highlighter_Settings::SETTING_SPECIAL ) . '" />';
	}

	/**
	 * Textbox.
	 *
	 * @param string $id ID.
	 * @param array  $atts Attributes.
	 * @param bool   $set_class Class.
	 */
	public static function textbox( string $id, $atts = array(), $set_class = true ) {
		$id       = Urvanov_Syntax_Highlighter_Settings::PREFIX . $id;
		$atts_str = '';
		$class    = $set_class ? 'class="' . esc_html( Urvanov_Syntax_Highlighter_Settings::SETTING ) . ' ' . esc_html( Urvanov_Syntax_Highlighter_Settings::SETTING_SPECIAL ) . '"' : '';

		foreach ( $atts as $k => $v ) {
			$atts_str = esc_html( $k ) . '="' . esc_attr( $v ) . '" ';
		}

		// phpcs:ignore WordPress.Security.EscapeOutput
		echo '<input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '" ' . $class . ' ' . $atts_str . ' />';
	}

	/**
	 * Submit.
	 */
	public static function submit() {
		?>
		<input type="button"
			class="button-primary <?php echo esc_attr( self::$settings['submit_css'] ); ?>"
			value="<?php echo esc_attr( self::$settings['submit_add'] ); ?>"
			name="submit"/>
		<?php
	}

	/**
	 * Content.
	 */
	public static function content() {
		Urvanov_Syntax_Highlighter_Settings_WP::load_settings();

		$langs      = Urvanov_Syntax_Highlighter_Langs::sort_by_name( Urvanov_Syntax_Highlighter_Parser::parse_all() );
		$curr_lang  = Urvanov_Syntax_Highlighter_Global_Settings::val( Urvanov_Syntax_Highlighter_Settings::FALLBACK_LANG );
		$themes     = Urvanov_Syntax_Highlighter_Resources::themes()->get();
		$curr_theme = Urvanov_Syntax_Highlighter_Global_Settings::val( Urvanov_Syntax_Highlighter_Settings::THEME );
		$fonts      = Urvanov_Syntax_Highlighter_Resources::fonts()->get();
		$curr_font  = Urvanov_Syntax_Highlighter_Global_Settings::val( Urvanov_Syntax_Highlighter_Settings::FONT );

		self::init_settings();

		?>
		<div id="urvanov-syntax-highlighter-te-content" class="urvanov-syntax-highlighter-te">
			<div id="urvanov-syntax-highlighter-te-bar">
				<div id="urvanov-syntax-highlighter-te-bar-content">
					<div id="urvanov-syntax-highlighter-te-title">Title</div>
					<div id="urvanov-syntax-highlighter-te-controls">
						<a id="urvanov-syntax-highlighter-te-ok" href="#"><?php esc_html_e( 'OK', 'urvanov-syntax-highlighter' ); ?></a>
						<span class="urvanov-syntax-highlighter-te-seperator">|</span>
						<a id="urvanov-syntax-highlighter-te-cancel" href="#"><?php esc_html_e( 'Cancel', 'urvanov-syntax-highlighter' ); ?></a>
					</div>
				</div>
			</div>
			<table id="urvanov-syntax-highlighter-te-table" class="describe">
				<tr class="urvanov-syntax-highlighter-tr-center">
					<th><?php esc_html_e( 'Title', 'urvanov-syntax-highlighter' ); ?>
					</th>
					<td class="urvanov-syntax-highlighter-nowrap"><?php self::textbox( 'title', array( 'placeholder' => esc_html__( 'A short description', 'urvanov-syntax-highlighter' ) ) ); ?>
						<span id="urvanov-syntax-highlighter-te-sub-section"> <?php self::checkbox( 'inline' ); ?>
							<span class="urvanov-syntax-highlighter-te-section"><?php esc_html_e( 'Inline', 'urvanov-syntax-highlighter' ); ?> </span>
						</span>
						<span id="urvanov-syntax-highlighter-te-sub-section"> <?php self::checkbox( 'highlight' ); ?>
							<span class="urvanov-syntax-highlighter-te-section"><?php esc_html_e( "Don't Highlight", 'urvanov-syntax-highlighter' ); ?></span>
						</span>
					</td>
				</tr>
				<tr class="urvanov-syntax-highlighter-tr-center">
					<th><?php esc_html_e( 'Language', 'urvanov-syntax-highlighter' ); ?></th>
					<td class="urvanov-syntax-highlighter-nowrap"><?php self::select_resource( 'lang', $langs, $curr_lang ); ?>
						<span class="urvanov-syntax-highlighter-te-section"><?php esc_html_e( 'Line Range', 'urvanov-syntax-highlighter' ); ?> </span>
						<?php self::textbox( 'range', array( 'placeholder' => wp_kses_post( '(e.g. 3-5 or 3)', 'urvanov-syntax-highlighter' ) ) ); ?>
						<span class="urvanov-syntax-highlighter-te-section"><?php esc_html_e( 'Marked Lines', 'urvanov-syntax-highlighter' ); ?> </span>
						<?php self::textbox( 'mark', array( 'placeholder' => wp_kses_post( '(e.g. 1,2,3-5)', 'urvanov-syntax-highlighter' ) ) ); ?>
					</td>
				</tr>
				<tr class="urvanov-syntax-highlighter-tr-center" style="text-align: center;">
					<th>
						<div>
							<?php esc_html_e( 'Code', 'urvanov-syntax-highlighter' ); ?>
						</div>
						<input type="button" id="urvanov-syntax-highlighter-te-clear"
								class="secondary-primary" value="<?php esc_html_e( 'Clear', 'urvanov-syntax-highlighter' ); ?>"
								name="clear"/>
					</th>
					<td><textarea id="urvanov-syntax-highlighter-code" name="code"
									placeholder="<?php esc_html_e( 'Paste your code here, or type it in manually.', 'urvanov-syntax-highlighter' ); ?>"></textarea>
					</td>
				</tr>
				<tr class="urvanov-syntax-highlighter-tr-center">
					<th id="urvanov-syntax-highlighter-url-th"><?php esc_html_e( 'URL', 'urvanov-syntax-highlighter' ); ?>
					</th>
					<td><?php self::textbox( 'url', array( 'placeholder' => 'Relative local path or absolute URL' ) ); ?>
						<div id="urvanov-syntax-highlighter-te-url-info" class="urvanov-syntax-highlighter-te-info">
							<?php
							esc_html_e( 'If the URL fails to load, the code above will be shown instead. If no code exists, an error is shown.', 'urvanov-syntax-highlighter' );
							echo ' ';
							// translators: %s = HTML.
							printf( esc_html__( 'If a relative local path is given it will be appended to %1$s - which is defined in %2$sUrvanovSyntaxHighlighter &gt; Settings &gt; Files%3$s.', 'urvanov-syntax-highlighter' ), '<span class="urvanov-syntax-highlighter-te-quote">' . esc_url( get_home_url() . '/' . Urvanov_Syntax_Highlighter_Global_Settings::val( Urvanov_Syntax_Highlighter_Settings::LOCAL_PATH ) ) . '</span>', '<a href="options-general.php?page=urvanov_syntax_highlighter_settings" target="_blank">', '</a>' );
							?>
						</div>
					</td>
				</tr>
				<tr>
					<td id="urvanov-syntax-highlighter-te-submit-wrapper" colspan="2"
						style="text-align: center;"><?php self::submit(); ?></td>
				</tr>
				<tr>
					<td colspan="2">
					<?php
						$admin = isset( $_GET['is_admin'] ) ? intval( $_GET['is_admin'] ) : is_admin(); // phpcs:ignore WordPress.Security.NonceVerification
					if ( ! $admin && ! Urvanov_Syntax_Highlighter_Global_Settings::val( Urvanov_Syntax_Highlighter_Settings::TAG_EDITOR_SETTINGS ) ) {
						exit();
					}
					?>
						<hr/>
						<div>
							<h2 class="urvanov-syntax-highlighter-te-heading">
								<?php esc_html_e( 'Settings', 'urvanov-syntax-highlighter' ); ?>
							</h2>
						</div>
						<div id="urvanov-syntax-highlighter-te-settings-info" class="urvanov-syntax-highlighter-te-info">
							<?php
							esc_html_e( 'Change the following settings to override their global values.', 'urvanov-syntax-highlighter' );
							echo ' <span class="', esc_attr( Urvanov_Syntax_Highlighter_Settings::SETTING_CHANGED ), '">';
							esc_html_e( 'Only changes (shown yellow) are applied.', 'urvanov-syntax-highlighter' );
							echo '</span><br/>';
							// translators: %s = URL.
							echo sprintf( esc_html__( 'Future changes to the global settings under %1$sUrvanovSyntaxHighlighter &gt; Settings%2$s won\'t affect overridden settings.', 'urvanov-syntax-highlighter' ), '<a href="options-general.php?page=urvanov_syntax_highlighter_settings" target="_blank">', '</a>' );
							?>
						</div>
					</td>
				</tr>
				<?php
				$sections = array(
					__( 'Theme', 'urvanov-syntax-highlighter' ),
					__( 'Font', 'urvanov-syntax-highlighter' ),
					__( 'Metrics', 'urvanov-syntax-highlighter' ),
					__( 'Toolbar', 'urvanov-syntax-highlighter' ),
					__( 'Lines', 'urvanov-syntax-highlighter' ),
					__( 'Code', 'urvanov-syntax-highlighter' ),
				);

				foreach ( $sections as $section ) {
					echo '<tr><th>', esc_html( $section ), '</th><td>';
					call_user_func( 'Urvanov_Syntax_Highlighter_Settings_WP::' . strtolower( $section ), true );
					echo '</td></tr>';
				}
				?>
			</table>
		</div>

		<?php
		exit();
	}

}

if ( defined( 'ABSPATH' ) ) {
	add_action( 'init', 'UrvanovSyntaxHighlighterTagEditorWP::init' );
}

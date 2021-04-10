<?php
/**
 * Formatter Class
 *
 * @package   Crayon Syntax Highlighter
 * @author    Fedor Urvanov, Aram Kocharyan
 * @copyright Copyright 2013, Aram Kocharyan
 * @link      https://urvanov.ru
 */

defined( 'ABSPATH' ) || exit;

require_once 'class-urvanov-syntax-highlighter-global.php';
require_once URVANOV_SYNTAX_HIGHLIGHTER_HIGHLIGHTER_PHP;
require_once URVANOV_SYNTAX_HIGHLIGHTER_SETTINGS_PHP;
require_once URVANOV_SYNTAX_HIGHLIGHTER_PARSER_PHP;
require_once URVANOV_SYNTAX_HIGHLIGHTER_THEMES_PHP;

/*	Manages formatting the html with html and css. */

/**
 * Class Urvanov_Syntax_Highlighter_Formatter
 */
class Urvanov_Syntax_Highlighter_Formatter {

	/**
	 * Used to temporarily store the array of UrvanovSyntaxHighlighterElements passed to format_code(), so that
	 * format_matches() can access them and identify which elements were captured and format
	 * accordingly. This must be static for preg_replace_callback() to access it.
	 */

	/**
	 * Elements.
	 *
	 * @var array
	 */
	private static $elements = array();

	/**
	 * Current urvanovSyntaxHighlighter undergoing delimiter replace.
	 *
	 * @var null
	 */
	private static $curr;

	/**
	 * Delimiters.
	 *
	 * @var null
	 */
	private static $delimiters;

	/**
	 * Delim RegEx.
	 *
	 * @var null
	 */
	private static $delim_regex;

	/** Delim pieces.
	 *
	 * @var null
	 */
	private static $delim_pieces;

	/**
	 * Urvanov_Syntax_Highlighter_Formatter constructor.
	 */
	private function __construct() {}

	/**
	 * Formats the code using the parsed language elements.
	 *
	 * @param mixed $code Code.
	 * @param mixed $language Language.
	 * @param null  $hl Highlighter.
	 *
	 * @return array|string|string[]|null
	 */
	public static function format_code( $code, $language, $hl = null ) {

		// Ensure the language is defined.
		if ( null !== $language && $hl->is_highlighted() ) {
			$code = self::clean_code( $code, false, false, false, true );

			/**
			 * Perform the replace on the code using the regex, pass the captured matches for
			 * formatting before they are replaced
			 */
			try {
				Urvanov_Syntax_Highlighter_Parser::parse( $language->id() );

				// Match language regex.
				$elements = $language->elements();
				$regex    = $language->regex();
				if ( ! empty( $regex ) && ! empty( $elements ) ) {

					// Get array of UrvanovSyntaxHighlighterElements.
					self::$elements = array_values( $elements );
					$code           = preg_replace_callback( $regex, 'Urvanov_Syntax_Highlighter_Formatter::format_match', $code );
				}
			} catch ( Exception $e ) {
				$error = 'An error occured when formatting: ' . $e->message();
				isset( $hl ) ? $hl->log( $error ) : UrvanovSyntaxHighlighterLog::syslog( $error );
			}

			return $code;
		} else {
			return self::clean_code( $code, true, true, true, true );
		}
	}

	/**
	 * Performs a replace to format each match based on the captured element.
	 *
	 * @param array $matches RegEx matches.
	 *
	 * @return array|mixed|string|string[]|null
	 */
	private static function format_match( array $matches ) {

		/**
		 * First index in $matches is full match, subsequent indices are groups.
		 * Minimum number of elements in array is 2, so minimum captured group is 0.
		 */
		$captured_group_number = count( $matches ) - 2;
		$code                  = $matches[0];

		if ( array_key_exists( $captured_group_number, self::$elements ) ) {
			$captured_element = self::$elements[ $captured_group_number ];

			// Avoid capturing and formatting internal UrvanovSyntaxHighlighter elements.
			if ( $captured_element->name() === Urvanov_Syntax_Highlighter_Parser::URVANOV_SYNTAX_HIGHLIGHTER_ELEMENT ) {
				return $code; // Return as is.
			} else {
				// Separate lines and add css class, keep extended class last to allow overriding.
				$fallback_css = Urvanov_Syntax_Highlighter_Langs::known_elements( $captured_element->fallback() );
				$element_css  = $captured_element->css();
				$css          = ! empty( $fallback_css ) ? $fallback_css . ' ' . $element_css : $element_css;

				return self::split_lines( $code, $css );
			}
		} else {
			// All else fails, return the match.
			return $matches[0];
		}
	}

	/**
	 * Prints the formatted code, option to override the line numbers with a custom string.
	 *
	 * @param object $hl Highlighter.
	 * @param mixed  $code COde.
	 * @param bool   $line_numbers Line numbers.
	 * @param bool   $print Print.
	 *
	 * @return string
	 */
	public static function print_code( $hl = null, $code = '', $line_numbers = true, $print = true ): string {
		global $urvanov_syntax_highlighter_version;

		// We can print either block or inline, inline is treated differently, factor out common stuff here.
		$output = '';

		// Used for style tag.
		$main_style    = '';
		$code_style    = '';
		$toolbar_style = '';
		$info_style    = '';
		$font_style    = '';
		$line_style    = '';
		$pre_style     = '';

		// Unique ID for this instance of UrvanovSyntaxHighlighter.
		$uid = 'urvanov-syntax-highlighter-' . $hl->id();

		// Print theme id.
		// We make the assumption that the id is correct (checked in class-urvanov-syntax-highlighter-wp).
		$theme_id        = $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::THEME );
		$theme_id_dashed = UrvanovSyntaxHighlighterUtil::space_to_hyphen( $theme_id );
		if ( ! $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::ENQUEUE_THEMES ) ) {
			$output .= Urvanov_Syntax_Highlighter_Resources::themes()->get_css( $theme_id );
		}

		// Print font id
		// We make the assumption that the id is correct (checked in class-urvanov-syntax-highlighter-wp).
		$font_id        = $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::FONT );
		$font_id_dashed = UrvanovSyntaxHighlighterUtil::space_to_hyphen( $font_id );
		if ( ! $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::ENQUEUE_FONTS ) ) {
			$output .= Urvanov_Syntax_Highlighter_Resources::fonts()->get_css( $font_id );
		}

		// Inline margin.
		if ( $hl->is_inline() ) {
			$inline_margin = $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::INLINE_MARGIN ) . 'px !important;';
		}

		// Determine font size.
		// TODO improve logic.
		if ( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::FONT_SIZE_ENABLE ) ) {
			$_font_size   = $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::FONT_SIZE );
			$font_size    = intval( $_font_size ) . 'px !important;';
			$_line_height = intval( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::LINE_HEIGHT ) );

			// Don't allow line height to be less than font size.
			$line_height    = ( $_line_height > $_font_size ? $_line_height : $_font_size ) . 'px !important;';
			$toolbar_height = (int) $font_size * 1.5 . 'px !important;';
			$info_height    = (int) $font_size * 1.4 . 'px !important;';

			$font_style    .= "font-size: $font_size line-height: $line_height";
			$toolbar_style .= "font-size: $font_size";
			$line_style    .= "height: $line_height";

			if ( $hl->is_inline() ) {
				$font_style .= "font-size: $font_size";
			} else {
				$toolbar_style .= "height: $toolbar_height line-height: $toolbar_height";
				$info_style    .= "min-height: $info_height line-height: $info_height";
			}
		} elseif ( ! $hl->is_inline() ) {
			$_font_size = Urvanov_Syntax_Highlighter_Global_Settings::get( Urvanov_Syntax_Highlighter_Settings::FONT_SIZE );
			if ( false !== $_font_size ) {
				$font_size   = $_font_size->def() . 'px !important;';
				$line_height = ( $_font_size->def() * 1.4 ) . 'px !important;';
			}
		}

		$tab       = intval( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::TAB_SIZE ) );
		$pre_style = "-moz-tab-size:$tab; -o-tab-size:$tab; -webkit-tab-size:$tab; tab-size:$tab;";

		// This will return from function with inline print.
		if ( $hl->is_inline() ) {
			$wrap    = ! $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::INLINE_WRAP ) ? 'urvanov-syntax-highlighter-syntax-inline-nowrap' : '';
			$output .= '
				<span id="' . esc_attr( $uid ) . '" class="urvanov-syntax-highlighter-syntax urvanov-syntax-highlighter-syntax-inline ' . esc_attr( $wrap ) . ' crayon-theme-' . esc_attr( $theme_id_dashed ) . ' crayon-theme-' . esc_attr( $theme_id_dashed ) . '-inline urvanov-syntax-highlighter-font-' . esc_attr( $font_id_dashed ) . '" style="' . $font_style . '">' .
				'<span class="crayon-pre urvanov-syntax-highlighter-code" style="' . $font_style . ' ' . $pre_style . '">' . $code . '</span>' .
				'</span>';

			return $output;
		}

		// Below code only for block (default) printing.

		// Generate the code lines and separate each line as a div.
		$print_code = '';
		$print_nums = '';
		$hl->line_count( preg_match_all( "#(?:^|(?<=\r\n|\n))[^\r\n]*#", $code, $code_lines ) );

		// The line number to start from.
		$start_line = $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::START_LINE );
		$marking    = $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::MARKING );
		$striped    = $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::STRIPED );
		$range      = $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::RANGES ) ? $hl->range() : false;

		$count = $hl->line_count();
		for ( $i = 1; $i <= $count; $i ++ ) {

			// Check if the current line is in the range of code to display.
			if ( $range ) {
				if ( $i < $range[0] ) {
					continue;
				} elseif ( $i > $range[1] ) {
					break;
				}
			}
			$code_line = $code_lines[0][ $i - 1 ];

			// If line is blank, add a space so the div has the correct height.
			if ( '' === $code_line ) {
				$code_line = '&nbsp;';
			}

			// Check if the current line has been selected.
			$marked_lines = $hl->marked();

			// Check if lines need to be marked as important.
			if ( $marking && in_array( $i, $marked_lines, true ) ) {
				$marked_num  = ' crayon-marked-num';
				$marked_line = ' crayon-marked-line';

				// If multiple lines are marked, only show borders for top and bottom lines.
				if ( ! in_array( $i - 1, $marked_lines, true ) ) {
					$marked_num  .= ' crayon-top';
					$marked_line .= ' crayon-top';
				}

				// Single lines are both the top and bottom of the multiple marked lines.
				if ( ! in_array( $i + 1, $marked_lines, true ) ) {
					$marked_num  .= ' crayon-bottom';
					$marked_line .= ' crayon-bottom';
				}
			} else {
				$marked_num  = '';
				$marked_line = '';
			}

			// Stripe odd lines.
			if ( $striped && 0 === $i % 2 ) {
				$striped_num  = ' crayon-striped-num';
				$striped_line = ' crayon-striped-line';
			} else {
				$striped_num  = '';
				$striped_line = '';
			}

			// Generate the lines.
			$line_num    = $start_line + $i - 1;
			$line_id     = $uid . '-' . $line_num;
			$print_code .= '<div class="crayon-line' . $marked_line . $striped_line . '" id="' . $line_id . '">' . $code_line . '</div>';
			if ( ! is_string( $line_numbers ) ) {
				$print_nums .= '<div class="crayon-num' . $marked_num . $striped_num . '" data-line="' . $line_id . '">' . $line_num . '</div>';
			}
		}

		// If $line_numbers is a string, display it.
		if ( is_string( $line_numbers ) && ! empty( $line_numbers ) ) {
			$print_nums .= '<div class="crayon-num">' . $line_numbers . '</div>';
		} elseif ( empty( $line_numbers ) ) {
			$print_nums = false;
		}

		// Determine whether to print title, encode characters.
		$title = $hl->title();

		// Decode if needed.
		if ( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::DECODE_ATTRIBUTES ) ) {
			$title = UrvanovSyntaxHighlighterUtil::html_entity_decode( $title );
		}

		$print_title = '<span class="crayon-title">' . $title . '</span>';

		// Determine whether to print language.
		$print_lang = '';

		// XXX Use for printing the regex.
		if ( $hl->language() ) {
			$lang = $hl->language()->name();
			switch ( $hl->setting_index( Urvanov_Syntax_Highlighter_Settings::SHOW_LANG ) ) {
				case 0:
					if ( $hl->language()->id() === Urvanov_Syntax_Highlighter_Langs::DEFAULT_LANG ) {
						break;
					}
					// Falls through.
				case 1:
					$print_lang = '<span class="crayon-language">' . $lang . '</span>';
					break;
			}
		}

		// Disable functionality for errors.
		$error = $hl->error();

		// Combined settings for code.
		$code_settings = '';

		// Disable mouseover for touchscreen devices and mobiles, if we are told to.
		$touch = false; // Whether we have detected a touchscreen device.
		if ( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::TOUCHSCREEN ) && UrvanovSyntaxHighlighterUtil::is_touch() ) {
			$touch          = true;
			$code_settings .= ' touchscreen';
		}

		// Disabling Popup.
		if ( ! $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::POPUP ) ) {
			$code_settings .= ' no-popup';
		}

		// Minimize.
		if ( ! $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::MINIMIZE ) ) {
			$code_settings .= ' minimize';
		}

		// Draw the plain code and toolbar.
		$toolbar_settings   = '';
		$print_plain_button = '';
		$print_copy_button  = '';

		$toolbar_index = $hl->setting_index( Urvanov_Syntax_Highlighter_Settings::TOOLBAR );
		if ( empty( $error ) && ( 2 !== $toolbar_index || $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::MINIMIZE ) ) ) {

			// Enable mouseover setting for toolbar.
			if ( 0 === $toolbar_index && ! $touch ) {

				// No touchscreen detected.
				$toolbar_settings .= ' mouseover';
				if ( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::TOOLBAR_OVERLAY ) ) {
					$toolbar_settings .= ' overlay';
				}
				if ( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::TOOLBAR_HIDE ) ) {
					$toolbar_settings .= ' hide';
				}
				if ( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::TOOLBAR_DELAY ) ) {
					$toolbar_settings .= ' delay';
				}
			} elseif ( 1 === $toolbar_index ) {

				// Always display the toolbar.
				$toolbar_settings .= ' show';
			} elseif ( 2 === $toolbar_index ) {
				$toolbar_settings .= ' never-show';
			}

			$buttons = array();

			if ( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::NUMS_TOGGLE ) ) {
				$buttons['nums'] = esc_html__( 'Toggle Line Numbers', 'urvanov-syntax-highlighter' );
			}

			if ( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::PLAIN ) && $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::PLAIN_TOGGLE ) ) {
				$buttons['plain'] = esc_html__( 'Toggle Plain Code', 'urvanov-syntax-highlighter' );
			}

			if ( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::WRAP_TOGGLE ) ) {
				$buttons['wrap'] = esc_html__( 'Toggle Line Wrap', 'urvanov-syntax-highlighter' );
			}

			if ( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::EXPAND_TOGGLE ) ) {
				$buttons['expand'] = esc_html__( 'Expand Code', 'urvanov-syntax-highlighter' );
			}

			if ( ! $touch && $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::PLAIN ) && $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::COPY ) ) {
				$buttons['copy'] = esc_html__( 'Copy', 'urvanov-syntax-highlighter' );
			}

			if ( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::POPUP ) ) {
				$buttons['popup'] = esc_html__( 'Open Code In New Window', 'urvanov-syntax-highlighter' );
			}

			$buttons_str = '';
			foreach ( $buttons as $button => $value ) {
				$buttons_str .= '<div class="crayon-button urvanov-syntax-highlighter-' . $button . '-button"';
				if ( ! is_array( $value ) ) {
					$value = array( 'title' => $value );
				}
				foreach ( $value as $k => $v ) {
					$buttons_str .= ' ' . $k . '="' . $v . '"';
				}
				$buttons_str .= '><div class="urvanov-syntax-highlighter-button-icon"></div></div>';
			}

			/**
			 * The table is rendered invisible by CSS and enabled with JS when asked to. If JS
			 * is not enabled or fails, the toolbar won't work so there is no point to display it.
			 */
			$print_plus = $hl->is_mixed() && $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::SHOW_ALTERNATE ) ? '<span class="urvanov-syntax-highlighter-mixed-highlight" title="' . esc_html__( 'Contains Mixed Languages', 'urvanov-syntax-highlighter' ) . '"></span>' : '';
			$buttons    = $print_plus . $buttons_str . $print_lang;
			$toolbar    = '
			<div class="crayon-toolbar" data-settings="' . $toolbar_settings . '" style="' . $toolbar_style . '">' . $print_title . '
			<div class="crayon-tools" style="' . $toolbar_style . '">' . $buttons . '</div></div>
			<div class="crayon-info" style="' . $info_style . '"></div>';
		} else {
			$toolbar        = '';
			$buttons        = '';
			$plain_settings = '';
		}

		if ( empty( $error ) && $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::PLAIN ) ) {

			// Different events to display plain code.
			switch ( $hl->setting_index( Urvanov_Syntax_Highlighter_Settings::SHOW_PLAIN ) ) {
				case 0:
					$plain_settings = 'dblclick';
					break;
				case 1:
					$plain_settings = 'click';
					break;
				case 2:
					$plain_settings = 'mouseover';
					break;
				default:
					$plain_settings = '';
			}

			if ( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::SHOW_PLAIN_DEFAULT ) ) {
				$plain_settings .= ' show-plain-default';
			}

			$readonly           = $touch ? '' : 'readonly';
			$print_plain        = '';
			$print_plain_button = '';

			$textwrap    = ! $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::WRAP ) ? 'wrap="soft"' : '';
			$print_plain = '<textarea ' . $textwrap . ' class="urvanov-syntax-highlighter-plain print-no" data-settings="' . $plain_settings . '" ' . $readonly . ' style="' . $pre_style . ' ' . $font_style . '">' . "\n" . self::clean_code( $hl->code() ) . '</textarea>';
		} else {
			$print_plain    = '';
			$plain_settings = '';
			$plain_settings = '';
		}

		// Line numbers visibility.
		$num_vis      = '';
		$num_settings = '';

		if ( false === $line_numbers ) {
			$num_vis = 'urvanov-syntax-highlighter-invisible';
		} else {
			$num_settings = ( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::NUMS ) ? 'show' : 'hide' );
		}

		// Determine scrollbar visibility.
		$code_settings .= $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::SCROLL ) && ! $touch ? ' scroll-always' : ' scroll-mouseover';

		// Disable animations.
		if ( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::DISABLE_ANIM ) ) {
			$code_settings .= ' disable-anim';
		}

		// Wrap.
		if ( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::WRAP ) ) {
			$code_settings .= ' wrap';
		}

		// Expand.
		if ( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::EXPAND ) ) {
			$code_settings .= ' expand';
		}

		// Determine dimensions.
		if ( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::HEIGHT_SET ) ) {
			$height_style = self::dimension_style( $hl, Urvanov_Syntax_Highlighter_Settings::HEIGHT );

			// XXX Only set height for main, not code (if toolbar always visible, code will cover main).
			if ( 0 === $hl->setting_index( Urvanov_Syntax_Highlighter_Settings::HEIGHT_UNIT ) ) {
				$main_style .= $height_style;
			}
		}

		if ( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::WIDTH_SET ) ) {
			$width_style = self::dimension_style( $hl, Urvanov_Syntax_Highlighter_Settings::WIDTH );
			$code_style .= $width_style;

			if ( 0 === $hl->setting_index( Urvanov_Syntax_Highlighter_Settings::WIDTH_UNIT ) ) {
				$main_style .= $width_style;
			}
		}

		// Determine margins.
		if ( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::TOP_SET ) ) {
			$code_style .= ' margin-top: ' . $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::TOP_MARGIN ) . 'px;';
		}
		if ( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::BOTTOM_SET ) ) {
			$code_style .= ' margin-bottom: ' . $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::BOTTOM_MARGIN ) . 'px;';
		}
		if ( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::LEFT_SET ) ) {
			$code_style .= ' margin-left: ' . $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::LEFT_MARGIN ) . 'px;';
		}
		if ( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::RIGHT_SET ) ) {
			$code_style .= ' margin-right: ' . $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::RIGHT_MARGIN ) . 'px;';
		}

		// Determine horizontal alignment.
		$align_style = '';
		switch ( $hl->setting_index( Urvanov_Syntax_Highlighter_Settings::H_ALIGN ) ) {
			case 1:
				$align_style = ' float: left;';
				break;
			case 2:
				$align_style = ' float: none; margin-left: auto; margin-right: auto;';
				break;
			case 3:
				$align_style = ' float: right;';
				break;
		}

		$code_style .= $align_style;

		// Determine allowed float elements.
		if ( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::FLOAT_ENABLE ) ) {
			$clear_style = ' clear: none;';
		} else {
			$clear_style = '';
		}

		$code_style .= $clear_style;

		// Determine if operating system is mac.
		$urvanov_syntax_highlighter_os = UrvanovSyntaxHighlighterUtil::is_mac() ? 'mac' : 'pc';

		// Produce output.
		$output .= '
		<div id="' . $uid . '" class="urvanov-syntax-highlighter-syntax crayon-theme-' . $theme_id_dashed . ' urvanov-syntax-highlighter-font-' . $font_id_dashed . ' urvanov-syntax-highlighter-os-' . $urvanov_syntax_highlighter_os . ' print-yes notranslate" data-settings="' . $code_settings . '" style="' . $code_style . ' ' . $font_style . '">
		' . $toolbar . '
			<div class="urvanov-syntax-highlighter-plain-wrap">' . $print_plain . '</div>
			<div class="urvanov-syntax-highlighter-main" style="' . $main_style . '">
				<table class="crayon-table">
					<tr class="urvanov-syntax-highlighter-row">';

		if ( false !== $print_nums ) {
			$output .= '
				<td class="crayon-nums ' . $num_vis . '" data-settings="' . $num_settings . '">
					<div class="urvanov-syntax-highlighter-nums-content" style="' . $font_style . '">' . $print_nums . '</div>
				</td>';
		}

		$output .= '
						<td class="urvanov-syntax-highlighter-code"><div class="crayon-pre" style="' . $font_style . ' ' . $pre_style . '">' . $print_code . '</div></td>
					</tr>
				</table>
			</div>
		</div>';

		// Debugging stats.
		$runtime = $hl->runtime();
		if ( ! $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::DISABLE_RUNTIME ) && is_array( $runtime ) && ! empty( $runtime ) ) {
			$output = '<!-- Urvanov Syntax Highlighter v' . esc_html( $urvanov_syntax_highlighter_version ) . ' -->' . URVANOV_SYNTAX_HIGHLIGHTER_NL . $output . URVANOV_SYNTAX_HIGHLIGHTER_NL . '<!-- ';
			foreach ( $hl->runtime() as $type => $time ) {
				$output .= '[' . $type . ': ' . sprintf( '%.4f seconds', $time ) . '] ';
			}
			$output .= '-->' . URVANOV_SYNTAX_HIGHLIGHTER_NL;
		}

		// Determine whether to print to screen or save.
		if ( $print ) {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput
			return '';
		} else {
			return $output;
		}
	}

	/**
	 * Print error.
	 *
	 * @param object $hl Highlighter.
	 * @param string $error Error.
	 * @param string $line_numbers Line numbers.
	 * @param bool   $print Is print.
	 *
	 * @return string|void
	 */
	public static function print_error( $hl = null, $error = '', $line_numbers = 'ERROR', $print = true ) {
		if ( get_class( $hl ) !== URVANOV_SYNTAX_HIGHLIGHTER_HIGHLIGHTER ) {
			return;
		}

		// Either print the error returned by the handler, or a custom error message.
		if ( $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::ERROR_MSG_SHOW ) ) {
			$error = $hl->setting_val( Urvanov_Syntax_Highlighter_Settings::ERROR_MSG );
		}

		$error = self::split_lines( trim( $error ), 'urvanov-syntax-highlighter-error' );

		return self::print_code( $hl, $error, $line_numbers, $print );
	}

	/**
	 * Format mixed code.
	 *
	 * @param mixed  $code Code.
	 * @param string $language Language.
	 * @param object $hl Highlighter.
	 *
	 * @return array|string|string[]|null
	 */
	public static function format_mixed_code( $code, $language = '', $hl = null ) {
		self::$curr         = $hl;
		self::$delim_pieces = array();

		// Remove urvanovSyntaxHighlighter internal element from INPUT code.
		$code = preg_replace( '#' . Urvanov_Syntax_Highlighter_Parser::URVANOV_SYNTAX_HIGHLIGHTER_ELEMENT_REGEX_CAPTURE . '#msi', '', $code );

		if ( null === self::$delimiters ) {
			self::$delimiters = Urvanov_Syntax_Highlighter_Resources::langs()->delimiters();
		}

		// Find all delimiters in all languages.
		if ( null === self::$delim_regex ) {
			self::$delim_regex = '#(' . implode( ')|(', array_values( self::$delimiters ) ) . ')#msi';
		}

		// Extract delimited code, replace with internal elements.
		$internal_code = preg_replace_callback( self::$delim_regex, 'Urvanov_Syntax_Highlighter_Formatter::delim_to_internal', $code );

		// Format with given language.
		$formatted_code = self::format_code( $internal_code, $language, $hl );

		// Replace internal elements with delimited pieces.
		return preg_replace_callback( '#\{\{urvanov-syntax-highlighter-internal:(\d+)\}\}#', 'Urvanov_Syntax_Highlighter_Formatter::internal_to_code', $formatted_code );
	}

	/**
	 * Delim to internal.
	 *
	 * @param array $matches RegEx matches.
	 *
	 * @return mixed|string
	 */
	public static function delim_to_internal( array $matches ) {
		// Mark as mixed so we can show (+).
		self::$curr->is_mixed( true );
		$capture_group  = count( $matches ) - 2;
		$capture_groups = array_keys( self::$delimiters );
		$lang_id        = $capture_groups[ $capture_group ];

		$lang = Urvanov_Syntax_Highlighter_Resources::langs()->get( $lang_id );
		if ( ( null === $lang ) ) {
			return $matches[0];
		}

		$internal = sprintf( '{{urvanov-syntax-highlighter-internal:%d}}', count( self::$delim_pieces ) );

		// TODO fix.
		self::$delim_pieces[] = self::format_code( $matches[0], $lang, self::$curr );

		return $internal;
	}

	/**
	 * Internal to code.
	 *
	 * @param array $matches Matches.
	 *
	 * @return mixed
	 */
	public static function internal_to_code( array $matches ) {
		return self::$delim_pieces[ intval( $matches[1] ) ];
	}

	/**
	 * Prepares code for formatting.
	 *
	 * @param string $code Code.
	 * @param bool   $escape Escape.
	 * @param bool   $spaces Spaces.
	 * @param bool   $tabs Tabs.
	 * @param bool   $lines Lines.
	 *
	 * @return array|string|string[]
	 */
	public static function clean_code( $code = '', $escape = true, $spaces = false, $tabs = false, $lines = false ) {
		if ( empty( $code ) ) {
			return $code;
		}

		/* Convert <, > and & characters to entities, as these can appear as HTML tags and entities. */
		if ( $escape ) {
			$code = UrvanovSyntaxHighlighterUtil::htmlspecialchars( $code );
		}

		if ( $spaces ) {
			// Replace 2 spaces with html escaped characters.
			$code = preg_replace( '#[ ]{2}#msi', '&nbsp;&nbsp;', $code );
		}

		if ( $tabs && Urvanov_Syntax_Highlighter_Global_Settings::val( Urvanov_Syntax_Highlighter_Settings::TAB_CONVERT ) ) {
			// Replace tabs with 4 spaces.
			$code = preg_replace( '#\t#', str_repeat( '&nbsp;', Urvanov_Syntax_Highlighter_Global_Settings::val( Urvanov_Syntax_Highlighter_Settings::TAB_SIZE ) ), $code );
		}

		if ( $lines ) {
			$code = preg_replace( '#(\r\n)|\r|\n#msi', "\r\n", $code );
		}

		return $code;
	}

	/**
	 * Converts the code to entities and wraps in a <pre><code></code></pre>.
	 *
	 * @param string $code Code.
	 * @param bool   $encoded Encoded.
	 *
	 * @return string
	 */
	public static function plain_code( $code = '', $encoded = true ): string {
		if ( is_array( $code ) ) {

			// When used as a preg_replace_callback.
			$code = $code[1];
		}

		if ( ! $encoded ) {
			$code = UrvanovSyntaxHighlighterUtil::htmlentities( $code );
		}

		if ( Urvanov_Syntax_Highlighter_Global_Settings::val( Urvanov_Syntax_Highlighter_Settings::TRIM_WHITESPACE ) ) {
			$code = trim( $code );
		}

		return '<pre class="urvanov-syntax-highlighter-plain-tag">' . $code . '</pre>';
	}

	/**
	 * Split lines.
	 *
	 * @param string $code Code.
	 * @param string $class Class.
	 *
	 * @return array|string|string[]|null
	 */
	public static function split_lines( $code = '', $class = '' ) {
		$code  = self::clean_code( $code, true, true, true );
		$class = preg_replace( '#(\w+)#m', 'crayon-$1', $class );

		return preg_replace( '#^([^\r\n]+)(?=\r\n|\r|\n|$)#m', '<span class="' . $class . '">$1</span>', $code );
	}

	/**
	 * Dimension style.
	 *
	 * @param object $hl Highlighter.
	 * @param string $name Name.
	 *
	 * @return string
	 */
	private static function dimension_style( $hl = null, $name = '' ): string {
		$mode = '';
		$unit = '';

		switch ( $name ) {
			case Urvanov_Syntax_Highlighter_Settings::HEIGHT:
				$mode = Urvanov_Syntax_Highlighter_Settings::HEIGHT_MODE;
				$unit = Urvanov_Syntax_Highlighter_Settings::HEIGHT_UNIT;
				break;
			case Urvanov_Syntax_Highlighter_Settings::WIDTH:
				$mode = Urvanov_Syntax_Highlighter_Settings::WIDTH_MODE;
				$unit = Urvanov_Syntax_Highlighter_Settings::WIDTH_UNIT;
				break;
		}

		// XXX Uses actual index value to identify options.
		$mode     = $hl->setting_index( $mode );
		$unit     = $hl->setting_index( $unit );
		$dim_mode = '';
		$dim_unit = '';

		if ( false !== $mode ) {
			switch ( $mode ) {
				case 0:
					$dim_mode .= 'max-';
					break;
				case 1:
					$dim_mode .= 'min-';
					break;
			}
		}

		$dim_mode .= $name;

		if ( false !== $unit ) {
			switch ( $unit ) {
				case 0:
					$dim_unit = 'px';
					break;
				case 1:
					$dim_unit = '%';
					break;
			}
		}

		return ' ' . $dim_mode . ': ' . $hl->setting_val( $name ) . $dim_unit . ';';
	}
}

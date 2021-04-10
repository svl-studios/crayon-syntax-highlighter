/* global jQueryUrvanovSyntaxHighlighter, UrvanovSyntaxHighlighterUtil, UrvanovSyntaxHighlighterSyntaxStrings, popupWindow */

// Urvanov Syntax Highlighter JavaScript.

( function( $ ) {
	var PRESSED   = 'crayon-pressed';
	var UNPRESSED = '';

	var URVANOV_SYNTAX_HIGHLIGHTER_SYNTAX        = 'div.urvanov-syntax-highlighter-syntax';
	var URVANOV_SYNTAX_HIGHLIGHTER_TOOLBAR       = '.crayon-toolbar';
	var URVANOV_SYNTAX_HIGHLIGHTER_INFO          = '.crayon-info';
	var URVANOV_SYNTAX_HIGHLIGHTER_PLAIN         = '.urvanov-syntax-highlighter-plain';
	var URVANOV_SYNTAX_HIGHLIGHTER_MAIN          = '.urvanov-syntax-highlighter-main';
	var URVANOV_SYNTAX_HIGHLIGHTER_TABLE         = '.crayon-table';
	var URVANOV_SYNTAX_HIGHLIGHTER_CODE          = '.urvanov-syntax-highlighter-code';
	var URVANOV_SYNTAX_HIGHLIGHTER_TITLE         = '.crayon-title';
	var URVANOV_SYNTAX_HIGHLIGHTER_TOOLS         = '.crayon-tools';
	var URVANOV_SYNTAX_HIGHLIGHTER_NUMS          = '.crayon-nums';
	var URVANOV_SYNTAX_HIGHLIGHTER_NUM           = '.crayon-num';
	var URVANOV_SYNTAX_HIGHLIGHTER_WRAPPED       = 'urvanov-syntax-highlighter-wrapped';
	var URVANOV_SYNTAX_HIGHLIGHTER_NUMS_CONTENT  = '.urvanov-syntax-highlighter-nums-content';
	var URVANOV_SYNTAX_HIGHLIGHTER_NUMS_BUTTON   = '.urvanov-syntax-highlighter-nums-button';
	var URVANOV_SYNTAX_HIGHLIGHTER_WRAP_BUTTON   = '.urvanov-syntax-highlighter-wrap-button';
	var URVANOV_SYNTAX_HIGHLIGHTER_EXPAND_BUTTON = '.urvanov-syntax-highlighter-expand-button';
	var URVANOV_SYNTAX_HIGHLIGHTER_EXPANDED      = 'urvanov-syntax-highlighter-expanded urvanov-syntax-highlighter-toolbar-visible';
	var URVANOV_SYNTAX_HIGHLIGHTER_PLACEHOLDER   = 'urvanov-syntax-highlighter-placeholder';
	var URVANOV_SYNTAX_HIGHLIGHTER_POPUP_BUTTON  = '.urvanov-syntax-highlighter-popup-button';
	var URVANOV_SYNTAX_HIGHLIGHTER_COPY_BUTTON   = '.urvanov-syntax-highlighter-copy-button';
	var URVANOV_SYNTAX_HIGHLIGHTER_PLAIN_BUTTON  = '.urvanov-syntax-highlighter-plain-button';

	var makeUID;
	var urvanovSyntaxHighlighterInfo;
	var updateWrap;
	var updateNumsButton;
	var updateWrapButton;
	var updateExpandButton;
	var updatePlainButton;
	var toggleToolbar;
	var addSize;
	var minusSize;
	var initSize;
	var toggleExpand;
	var placeholderResize;
	var expandFinish;
	var toggleScroll;
	var fixScrollBlank;
	var restoreDimensions;
	var reconsileDimensions;
	var reconsileLines;
	var animt;
	var isNumber;
	var getUID;
	var codePopup;
	var retina;
	var isSlideHidden;

	// BEGIN AUXILIARY FUNCTIONS.
	$.fn.exists = function() {
		return this.length !== 0;
	};

	$.fn.style = function( styleName, value, priority ) {
		var style;

		// DOM node.
		var node = this.get( 0 );

		// Ensure we have a DOM node.
		if ( typeof node === 'undefined' ) {
			return;
		}

		// CSSStyleDeclaration.
		style = node.style;

		// Getter/Setter.
		if ( typeof styleName !== 'undefined' ) {
			if ( typeof value !== 'undefined' ) {
				// Set style property.
				priority = typeof priority !== 'undefined' ? priority : '';
				if ( typeof style.setProperty !== 'undefined' ) {
					style.setProperty( styleName, value, priority );
				} else {
					style[styleName] = value;
				}
			} else {
				// Get style property.
				return style[styleName];
			}
		} else {
			// Get CSSStyleDeclaration.
			return style;
		}
	};
	// END AUXILIARY FUNCTIONS.

	UrvanovSyntaxHighlighterSyntax = new function() {
		var base = this;
		var urvanovSyntaxHighlighters;
		var settings;
		var strings;
		var currUID = 0;
		var touchscreen;
		var pxToInt;

		base.init = function() {
			if ( 'undefined' === typeof urvanovSyntaxHighlighters ) {
				urvanovSyntaxHighlighters = {};
			}

			strings = UrvanovSyntaxHighlighterSyntaxStrings;

			$( URVANOV_SYNTAX_HIGHLIGHTER_SYNTAX ).each(
				function() {
					base.process( this );
				}
			);
		};

		base.process = function( c, replace ) {
			var uid;
			var toolbar;
			var info;
			var plain;
			var main;
			var table;
			var code;
			var title;
			var tools;
			var nums;
			var numsContent;
			var numsButton;
			var wrapButton;
			var expandButton;
			var popupButton;
			var copyButton;
			var plainButton;
			var mainStyle;
			var loadTimer;
			var i = 0;
			var loadFunc;
			var expand;

			c   = $( c );
			uid = c.attr( 'id' );

			if ( 'urvanov-syntax-highlighter-' === uid ) {
				// No ID, generate one.
				uid += getUID();
			}

			c.attr( 'id', uid );
			UrvanovSyntaxHighlighterUtil.log( uid );

			if ( typeof replace === 'undefined' ) {
				replace = false;
			}

			if ( ! replace && ! makeUID( uid ) ) {
				// Already a UrvanovSyntaxHighlighter.
				return;
			}

			toolbar      = c.find( URVANOV_SYNTAX_HIGHLIGHTER_TOOLBAR );
			info         = c.find( URVANOV_SYNTAX_HIGHLIGHTER_INFO );
			plain        = c.find( URVANOV_SYNTAX_HIGHLIGHTER_PLAIN );
			main         = c.find( URVANOV_SYNTAX_HIGHLIGHTER_MAIN );
			table        = c.find( URVANOV_SYNTAX_HIGHLIGHTER_TABLE );
			code         = c.find( URVANOV_SYNTAX_HIGHLIGHTER_CODE );
			title        = c.find( URVANOV_SYNTAX_HIGHLIGHTER_TITLE );
			tools        = c.find( URVANOV_SYNTAX_HIGHLIGHTER_TOOLS );
			nums         = c.find( URVANOV_SYNTAX_HIGHLIGHTER_NUMS );
			numsContent  = c.find( URVANOV_SYNTAX_HIGHLIGHTER_NUMS_CONTENT );
			numsButton   = c.find( URVANOV_SYNTAX_HIGHLIGHTER_NUMS_BUTTON );
			wrapButton   = c.find( URVANOV_SYNTAX_HIGHLIGHTER_WRAP_BUTTON );
			expandButton = c.find( URVANOV_SYNTAX_HIGHLIGHTER_EXPAND_BUTTON );
			popupButton  = c.find( URVANOV_SYNTAX_HIGHLIGHTER_POPUP_BUTTON );
			copyButton   = c.find( URVANOV_SYNTAX_HIGHLIGHTER_COPY_BUTTON );
			plainButton  = c.find( URVANOV_SYNTAX_HIGHLIGHTER_PLAIN_BUTTON );

			urvanovSyntaxHighlighters[uid]               = c;
			urvanovSyntaxHighlighters[uid].toolbar       = toolbar;
			urvanovSyntaxHighlighters[uid].plain         = plain;
			urvanovSyntaxHighlighters[uid].info          = info;
			urvanovSyntaxHighlighters[uid].main          = main;
			urvanovSyntaxHighlighters[uid].table         = table;
			urvanovSyntaxHighlighters[uid].code          = code;
			urvanovSyntaxHighlighters[uid].title         = title;
			urvanovSyntaxHighlighters[uid].tools         = tools;
			urvanovSyntaxHighlighters[uid].nums          = nums;
			urvanovSyntaxHighlighters[uid].nums_content  = numsContent;
			urvanovSyntaxHighlighters[uid].numsButton    = numsButton;
			urvanovSyntaxHighlighters[uid].wrapButton    = wrapButton;
			urvanovSyntaxHighlighters[uid].expandButton  = expandButton;
			urvanovSyntaxHighlighters[uid].popup_button  = popupButton;
			urvanovSyntaxHighlighters[uid].copy_button   = copyButton;
			urvanovSyntaxHighlighters[uid].plainButton   = plainButton;
			urvanovSyntaxHighlighters[uid].numsVisible   = true;
			urvanovSyntaxHighlighters[uid].wrapped       = false;
			urvanovSyntaxHighlighters[uid].plainVisible  = false;
			urvanovSyntaxHighlighters[uid].toolbar_delay = 0;
			urvanovSyntaxHighlighters[uid].time          = 1;

			// Set plain.
			$( URVANOV_SYNTAX_HIGHLIGHTER_PLAIN ).css( 'z-index', 0 );

			// XXX Remember CSS dimensions.
			mainStyle = main.style();

			urvanovSyntaxHighlighters[uid].mainStyle = {
				height: ( mainStyle && mainStyle.height ) || '',
				'max-height': ( mainStyle && mainStyle.maxHeight ) || '',
				'min-height': ( mainStyle && mainStyle.minHeight ) || '',
				width: ( mainStyle && mainStyle.width ) || '',
				'max-width': ( mainStyle && mainStyle.maxWidth ) || '',
				'min-width': ( mainStyle && mainStyle.minWidth ) || '',
			};

			urvanovSyntaxHighlighters[uid].mainHeightAuto = '' === urvanovSyntaxHighlighters[uid].mainStyle.height && '' === urvanovSyntaxHighlighters[uid].mainStyle['max-height'];

			urvanovSyntaxHighlighters[uid].loading        = true;
			urvanovSyntaxHighlighters[uid].scrollBlockFix = false;

			// Register click events.
			numsButton.on(
				'click',
				function() {
					UrvanovSyntaxHighlighterSyntax.toggleNums( uid );
				}
			);

			wrapButton.on(
				'click',
				function() {
					UrvanovSyntaxHighlighterSyntax.toggleWrap( uid );
				}
			);

			expandButton.on(
				'click',
				function() {
					UrvanovSyntaxHighlighterSyntax.toggleExpand( uid );
				}
			);

			plainButton.on(
				'click',
				function() {
					UrvanovSyntaxHighlighterSyntax.togglePlain( uid );
				}
			);

			copyButton.on(
				'click',
				function() {
					UrvanovSyntaxHighlighterSyntax.copyPlain( uid );
				}
			);

			// Enable retina if supported.
			retina( uid );

			loadFunc = function() {
				// If nums hidden by default.
				if ( 0 !== nums.filter( '[data-settings~="hide"]' ).length ) {
					numsContent.ready(
						function() {
							UrvanovSyntaxHighlighterUtil.log( 'function' + uid );
							UrvanovSyntaxHighlighterSyntax.toggleNums( uid, true, true );
						}
					);
				} else {
					updateNumsButton( uid );
				}

				if ( typeof urvanovSyntaxHighlighters[uid].expanded === 'undefined' ) {
					// Determine if we should enable code expanding toggling.
					if ( Math.abs( urvanovSyntaxHighlighters[uid].main.outerWidth() - urvanovSyntaxHighlighters[uid].table.outerWidth() ) < 10 ) {
						urvanovSyntaxHighlighters[uid].expandButton.hide();
					} else {
						urvanovSyntaxHighlighters[uid].expandButton.show();
					}
				}

				// TODO If width has changed or timeout, stop timer.
				if ( 5 === i ) {
					clearInterval( loadTimer );

					urvanovSyntaxHighlighters[uid].loading = false;
				}
				i++;
			};

			loadTimer = setInterval( loadFunc, 300 );

			fixScrollBlank( uid );

			// Add ref to num for each line.
			$( URVANOV_SYNTAX_HIGHLIGHTER_NUM, urvanovSyntaxHighlighters[uid] ).each(
				function() {
					var lineID = $( this ).attr( 'data-line' );
					var line   = $( '#' + lineID );
					var height = line.style( 'height' );

					if ( height ) {
						line.attr( 'data-height', height );
					}
				}
			);

			// Used for toggling.
			main.css( 'position', 'relative' );
			main.css( 'z-index', 1 );

			// Disable certain features for touchscreen devices.
			touchscreen = ( 0 !== c.filter( '[data-settings~="touchscreen"]' ).length );

			// Used to hide info.
			if ( ! touchscreen ) {
				main.on(
					'click',
					function() {
						urvanovSyntaxHighlighterInfo( uid, '', false );
					}
				);

				plain.on(
					'click',
					function() {
						urvanovSyntaxHighlighterInfo( uid, '', false );
					}
				);

				info.on(
					'click',
					function() {
						urvanovSyntaxHighlighterInfo( uid, '', false );
					}
				);
			}

			// Used for code popup.
			if ( 0 === c.filter( '[data-settings~="no-popup"]' ).length ) {
				urvanovSyntaxHighlighters[uid].popup_settings = popupWindow(
					popupButton,
					{
						height: screen.height - 200,
						width: screen.width - 100,
						top: 75,
						left: 50,
						scrollbars: 1,
						windowURL: '',
						data: '', // Data overrides URL.
					},
					function() {
						codePopup( uid );
					},
					function() {}
				);
			}

			plain.css( 'opacity', 0 );

			urvanovSyntaxHighlighters[uid].toolbarVisible   = true;
			urvanovSyntaxHighlighters[uid].hasOneLine       = table.outerHeight() < toolbar.outerHeight() * 2;
			urvanovSyntaxHighlighters[uid].toolbarMouseover = false;

			// If a toolbar with mouseover was found.
			if ( 0 !== toolbar.filter( '[data-settings~="mouseover"]' ).length && ! touchscreen ) {
				urvanovSyntaxHighlighters[uid].toolbarMouseover = true;
				urvanovSyntaxHighlighters[uid].toolbarVisible   = false;

				toolbar.css( 'margin-top', '-' + toolbar.outerHeight() + 'px' );
				toolbar.hide();

				// Overlay the toolbar if needed, only if doing so will not hide the
				// whole code!
				if ( 0 !== toolbar.filter( '[data-settings~="overlay"]' ).length &&
					! urvanovSyntaxHighlighters[uid].hasOneLine ) {
					toolbar.css( 'position', 'absolute' );
					toolbar.css( 'z-index', 2 );

					// Hide on single click when overlayed.
					if ( 0 !== toolbar.filter( '[data-settings~="hide"]' ).length ) {
						main.on(
							'click',
							function() {
								toggleToolbar( uid, undefined, undefined, 0 );
							}
						);
						plain.on(
							'click',
							function() {
								toggleToolbar( uid, false, undefined, 0 );
							}
						);
					}
				} else {
					toolbar.css( 'z-index', 4 );
				}

				// Enable delay on mouseout.
				if ( 0 !== toolbar.filter( '[data-settings~="delay"]' ).length ) {
					urvanovSyntaxHighlighters[uid].toolbar_delay = 500;
				}

				// Use .hover() for chrome, but in firefox mouseover/mouseout worked best.
				c.on(
					'mouseenter',
					function() {
						toggleToolbar( uid, true );
					}
				).on(
					'mouseleave',
					function() {
						toggleToolbar( uid, false );
					}
				);
			} else if ( touchscreen ) {
				toolbar.show();
			}

			// Minimize.
			if ( 0 === c.filter( '[data-settings~="minimize"]' ).length ) {
				base.minimize( uid );
			}

			// Plain show events.
			if ( 0 !== plain.length && ! touchscreen ) {
				if ( 0 !== plain.filter( '[data-settings~="dblclick"]' ).length ) {
					main.on(
						'dblclick',
						function() {
							UrvanovSyntaxHighlighterSyntax.togglePlain( uid );
						}
					);
				} else if ( 0 !== plain.filter( '[data-settings~="click"]' ).length ) {
					main.on(
						'click',
						function() {
							UrvanovSyntaxHighlighterSyntax.togglePlain( uid );
						}
					);
				} else if ( 0 !== plain.filter( '[data-settings~="mouseover"]' ).length ) {
					c.on(
						'mouseenter',
						function() {
							UrvanovSyntaxHighlighterSyntax.togglePlain( uid, true );
						}
					).on(
						'mouseleave',
						function() {
							UrvanovSyntaxHighlighterSyntax.togglePlain( uid, false );
						}
					);

					numsButton.hide();
				}

				if ( 0 !== plain.filter( '[data-settings~="show-plain-default"]' ).length ) {
					UrvanovSyntaxHighlighterSyntax.togglePlain( uid, true );
				}
			}

			// Scrollbar show events.
			expand = 0 !== c.filter( '[data-settings~="expand"]' ).length;

			if ( ! touchscreen && 0 !== c.filter( '[data-settings~="scroll-mouseover"]' ).length ) {
				// Disable on touchscreen devices and when set to mouseover.
				main.css( 'overflow', 'hidden' );
				plain.css( 'overflow', 'hidden' );

				c.on(
					'mouseenter',
					function() {
						toggleScroll( uid, true, expand );
					}
				).on(
					'mouseleave',
					function() {
						toggleScroll( uid, false, expand );
					}
				);
			}

			if ( expand ) {
				c.on(
					'mouseenter',
					function() {
						toggleExpand( uid, true );
					}
				).on(
					'mouseleave',
					function() {
						toggleExpand( uid, false );
					}
				);
			}

			// Disable animations.
			if ( 0 !== c.filter( '[data-settings~="disable-anim"]' ).length ) {
				urvanovSyntaxHighlighters[uid].time = 0;
			}

			// Wrap.
			if ( 0 !== c.filter( '[data-settings~="wrap"]' ).length ) {
				urvanovSyntaxHighlighters[uid].wrapped = true;
			}

			// Determine if Mac.
			urvanovSyntaxHighlighters[uid].mac = c.hasClass( 'urvanov-syntax-highlighter-os-mac' );

			// Update clickable buttons.
			updateNumsButton( uid );
			updatePlainButton( uid );

			updateWrap( uid );
		};

		makeUID = function( uid ) {
			UrvanovSyntaxHighlighterUtil.log( urvanovSyntaxHighlighters );
			if ( 'undefined' === typeof urvanovSyntaxHighlighters[uid] ) {
				urvanovSyntaxHighlighters[uid] = $( '#' + uid );
				UrvanovSyntaxHighlighterUtil.log( 'make ' + uid );
				return true;
			}

			UrvanovSyntaxHighlighterUtil.log( 'no make ' + uid );
			return false;
		};

		getUID = function() {
			return currUID++;
		};

		codePopup = function( uid ) {
			var code = '';
			var clone;

			if ( 'undefined' === typeof urvanovSyntaxHighlighters[uid] ) {
				return makeUID( uid );
			}

			settings = urvanovSyntaxHighlighters[uid].popup_settings;
			if ( settings && settings.data ) {
				// Already done.
				return;
			}

			clone = urvanovSyntaxHighlighters[uid].clone( true );
			clone.removeClass( 'urvanov-syntax-highlighter-wrapped' );

			// Unwrap.
			if ( urvanovSyntaxHighlighters[uid].wrapped ) {
				$( URVANOV_SYNTAX_HIGHLIGHTER_NUM, clone ).each(
					function() {
						var lineId = $( this ).attr( 'data-line' );
						var line   = $( '#' + lineId );
						var height = line.attr( 'data-height' );

						height = height ? height : '';
						if ( typeof height !== 'undefined' ) {
							line.css( 'height', height );
							$( this ).css( 'height', height );
						}
					}
				);
			}

			clone.find( URVANOV_SYNTAX_HIGHLIGHTER_MAIN ).css( 'height', '' );

			if ( urvanovSyntaxHighlighters[uid].plainVisible ) {
				code = clone.find( URVANOV_SYNTAX_HIGHLIGHTER_PLAIN );
			} else {
				code = clone.find( URVANOV_SYNTAX_HIGHLIGHTER_MAIN );
			}

			settings.data = base.getAllCSS() + '<body class="urvanov-syntax-highlighter-popup-window" style="padding:0; margin:0;"><div class="' +
				clone.attr( 'class' ) +
				' urvanov-syntax-highlighter-popup">' + base.removeCssInline( base.getHtmlString( code ) ) + '</div></body>';
		};

		base.minimize = function( uid ) {
			var button = $( '<div class="urvanov-syntax-highlighter-minimize urvanov-syntax-highlighter-button"><div>' );
			var cls    = 'urvanov-syntax-highlighter-minimized';
			var show;

			urvanovSyntaxHighlighters[uid].tools.append( button );

			// TODO translate.
			urvanovSyntaxHighlighters[uid].origTitle = urvanovSyntaxHighlighters[uid].title.html();
			if ( ! urvanovSyntaxHighlighters[uid].origTitle ) {
				urvanovSyntaxHighlighters[uid].title.html( strings.minimize );
			}

			show = function() {
				var toolbar;

				urvanovSyntaxHighlighters[uid].toolbarPreventHide = false;
				button.remove();
				urvanovSyntaxHighlighters[uid].removeClass( cls );
				urvanovSyntaxHighlighters[uid].title.html( urvanovSyntaxHighlighters[uid].origTitle );
				toolbar = urvanovSyntaxHighlighters[uid].toolbar;

				if ( 0 !== toolbar.filter( '[data-settings~="never-show"]' ).length ) {
					toolbar.remove();
				}
			};

			urvanovSyntaxHighlighters[uid].toolbar.on( 'click', show );
			button.on( 'click', show );
			urvanovSyntaxHighlighters[uid].addClass( cls );
			urvanovSyntaxHighlighters[uid].toolbarPreventHide = true;
			toggleToolbar( uid, undefined, undefined, 0 );
		};

		base.getHtmlString = function( object ) {
			return $( '<div>' ).append( object.clone() ).remove().html();
		};

		base.removeCssInline = function( string ) {
			var reStyle = /style\s*=\s*"([^"]+)"/gmi;
			var match   = null;
			var repl;

			while ( null !== ( match = reStyle.exec( string ) ) ) {
				repl   = match[1];
				repl   = repl.replace( /\b(?:width|height)\s*:[^;]+;/gmi, '' );
				string = string.sliceReplace( match.index, match.index + match[0].length, 'style="' + repl + '"' );
			}

			return string;
		};

		// Get all CSS on the page as a string.
		base.getAllCSS = function() {
			var cssStr   = '';
			var css      = $( 'link[rel="stylesheet"]' );
			var filtered = [];

			if ( 1 === css.length ) {
				// For minified CSS, only allow a single file.
				filtered = css;
			} else {
				// Filter all others for UrvanovSyntaxHighlighter CSS.
				filtered = css.filter( '[href*="urvanov-syntax-highlighter"], [href*="min/"]' );
			}

			filtered.each(
				function() {
					var string = base.getHtmlString( $( this ) );

					cssStr += string;
				}
			);

			return cssStr;
		};

		base.copyPlain = function( uid ) {
			var key;
			var text;

			if ( typeof urvanovSyntaxHighlighters[uid] === 'undefined' ) {
				return makeUID( uid );
			}

			base.togglePlain( uid, true, true );
			toggleToolbar( uid, true );

			key  = urvanovSyntaxHighlighters[uid].mac ? '\u2318' : 'CTRL';
			text = strings.copy;
			text = text.replace( /%s/, key + '+C' );
			text = text.replace( /%s/, key + '+V' );

			urvanovSyntaxHighlighterInfo( uid, text );

			return false;
		};

		urvanovSyntaxHighlighterInfo = function( uid, text, show ) {
			var info;

			if ( typeof urvanovSyntaxHighlighters[uid] === 'undefined' ) {
				return makeUID( uid );
			}

			info = urvanovSyntaxHighlighters[uid].info;

			if ( typeof text === 'undefined' ) {
				text = '';
			}
			if ( typeof show === 'undefined' ) {
				show = true;
			}

			if ( isSlideHidden( info ) && show ) {
				info.html( '<div>' + text + '</div>' );
				info.css( 'margin-top', -info.outerHeight() );
				info.show();
				urvanovSyntaxHighlighterSlide( uid, info, true );
				setTimeout(
					function() {
						urvanovSyntaxHighlighterSlide( uid, info, false );
					},
					5000
				);
			}

			if ( ! show ) {
				urvanovSyntaxHighlighterSlide( uid, info, false );
			}
		};

		retina = function( uid ) {
			var buttons;

			if ( window.devicePixelRatio > 1 ) {
				buttons = $( '.urvanov-syntax-highlighter-button-icon', urvanovSyntaxHighlighters[uid].toolbar );
				buttons.each(
					function() {
						var lowres  = $( this ).css( 'background-image' );
						var highres = lowres.replace( /\.(?=[^\.]+$)/g, '@2x.' );

						$( this ).css( 'background-size', '48px 128px' );
						$( this ).css( 'background-image', highres );
					}
				);
			}
		};

		isSlideHidden = function( object ) {
			var objectNegHeight = '-' + object.outerHeight() + 'px';

			if ( objectNegHeight === object.css( 'margin-top' ) || 'none' === object.css( 'display' ) ) {
				return true;
			}

			return false;
		};

		urvanovSyntaxHighlighterSlide = function( uid, object, show, animTime, hideDelay, callback ) {
			var complete = function() {
				if ( callback ) {
					callback( uid, object );
				}
			};

			var objectNegHeight = '-' + object.outerHeight() + 'px';

			if ( typeof show === 'undefined' ) {
				if ( isSlideHidden( object ) ) {
					show = true;
				} else {
					show = false;
				}
			}
			// Instant means no time delay for showing/hiding.
			if ( typeof animTime === 'undefined' ) {
				animTime = 100;
			}
			if ( false === animTime ) {
				animTime = false;
			}
			if ( 'undefined' === typeof hideDelay ) {
				hideDelay = 0;
			}
			object.stop( true );
			if ( true === show ) {
				object.show();
				object.animate(
					{
						marginTop: 0,
					},
					animt( animTime, uid ),
					complete
				);
			} else if ( false === show ) {
				// Delay if fully visible.
				if ( /*instant == false && */'0px' === object.css( 'margin-top' ) && hideDelay ) {
					object.delay( hideDelay );
				}
				object.animate(
					{
						marginTop: objectNegHeight,
					},
					animt( animTime, uid ),
					function() {
						object.hide();
						complete();
					}
				);
			}
		};

		base.togglePlain = function( uid, hover, select ) {
			var main;
			var plain;
			var visible;
			var hidden;

			if ( 'undefined' === typeof urvanovSyntaxHighlighters[uid] ) {
				return makeUID( uid );
			}

			main  = urvanovSyntaxHighlighters[uid].main;
			plain = urvanovSyntaxHighlighters[uid].plain;

			if ( ( main.is( ':animated' ) || plain.is( ':animated' ) ) && typeof hover === 'undefined' ) {
				return;
			}

			reconsileDimensions( uid );
console.log(main.css( 'z-index' ))
			if ( typeof hover !== 'undefined' ) {
				if ( hover ) {
					visible = main;
					hidden  = plain;
				} else {
					visible = plain;
					hidden  = main;
				}
			} else if ( 1 === Number( main.css( 'z-index' ) ) ) {
				visible = main;
				hidden  = plain;
			} else {
				visible = plain;
				hidden  = main;
			}

			urvanovSyntaxHighlighters[uid].plainVisible = ( hidden === plain );

			// Remember scroll positions of visible.
			urvanovSyntaxHighlighters[uid].top  = visible.scrollTop();
			urvanovSyntaxHighlighters[uid].left = visible.scrollLeft();

			/* Used to detect a change in overflow when the mouse moves out
			 * of the UrvanovSyntaxHighlighter. If it does, then overflow has already been changed,
			 * no need to revert it after toggling plain. */
			urvanovSyntaxHighlighters[uid].scrollChanged = false;

			fixScrollBlank( uid );

			// Show hidden, hide visible.
			visible.stop( true );
			visible.fadeTo(
				animt( 500, uid ),
				0,
				function() {
					visible.css( 'z-index', 0 );
				}
			);

			hidden.stop( true );
			hidden.fadeTo(
				animt( 500, uid ),
				1,
				function() {
					hidden.css( 'z-index', 1 );

					// Give focus to plain code.
					if ( hidden === plain ) {
						if ( select ) {
							plain.select();
						}
					}

					// Refresh scrollbar draw.
					hidden.scrollTop( urvanovSyntaxHighlighters[uid].top + 1 );
					hidden.scrollTop( urvanovSyntaxHighlighters[uid].top );
					hidden.scrollLeft( urvanovSyntaxHighlighters[uid].left + 1 );
					hidden.scrollLeft( urvanovSyntaxHighlighters[uid].left );
				}
			);

			// Restore scroll positions to hidden.
			hidden.scrollTop( urvanovSyntaxHighlighters[uid].top );
			hidden.scrollLeft( urvanovSyntaxHighlighters[uid].left );

			updatePlainButton( uid );

			// Hide toolbar if possible.
			toggleToolbar( uid, false );
			return false;
		};

		base.toggleNums = function( uid, hide, instant ) {
			var numsWidth;
			var negWidth;
			var numHidden;
			var numMargin;
			var hScrollVisible;
			var vScrollVisible;

			if ( typeof urvanovSyntaxHighlighters[uid] === 'undefined' ) {
				makeUID( uid );
				return false;
			}

			if ( urvanovSyntaxHighlighters[uid].table.is( ':animated' ) ) {
				return false;
			}

			numsWidth = Math.round( urvanovSyntaxHighlighters[uid].nums_content.outerWidth() + 1 );
			negWidth  = '-' + numsWidth + 'px';

			// Force hiding.
			if ( 'undefined' !== typeof hide ) {
				numHidden = false;
			} else {
				// Check hiding.
				numHidden = ( urvanovSyntaxHighlighters[uid].table.css( 'margin-left' ) === negWidth );
			}

			if ( numHidden ) {
				// Show.
				numMargin = '0px';

				urvanovSyntaxHighlighters[uid].numsVisible = true;
			} else {
				// Hide.
				urvanovSyntaxHighlighters[uid].table.css( 'margin-left', '0px' );
				urvanovSyntaxHighlighters[uid].numsVisible = false;

				numMargin = negWidth;
			}

			if ( 'undefined' !== typeof instant ) {
				urvanovSyntaxHighlighters[uid].table.css( 'margin-left', numMargin );
				updateNumsButton( uid );

				return false;
			}

			// Stop jerking animation from scrollbar appearing for a split second due to
			// change in width. Prevents scrollbar disappearing if already visible.
			hScrollVisible = ( urvanovSyntaxHighlighters[uid].table.outerWidth() +
				pxToInt( urvanovSyntaxHighlighters[uid].table.css( 'margin-left' ) ) > urvanovSyntaxHighlighters[uid].main.outerWidth() );

			vScrollVisible = ( urvanovSyntaxHighlighters[uid].table.outerHeight() > urvanovSyntaxHighlighters[uid].main.outerHeight() );

			if ( ! hScrollVisible && ! vScrollVisible ) {
				urvanovSyntaxHighlighters[uid].main.css( 'overflow', 'hidden' );
			}

			urvanovSyntaxHighlighters[uid].table.animate(
				{
					marginLeft: numMargin,
				},
				animt( 200, uid ),
				function() {
					if ( typeof urvanovSyntaxHighlighters[uid] !== 'undefined' ) {
						updateNumsButton( uid );
						if ( ! hScrollVisible && ! vScrollVisible ) {
							urvanovSyntaxHighlighters[uid].main.css( 'overflow', 'auto' );
						}
					}
				}
			);

			return false;
		};

		base.toggleWrap = function( uid ) {
			urvanovSyntaxHighlighters[uid].wrapped = ! urvanovSyntaxHighlighters[uid].wrapped;
			updateWrap( uid );
		};

		base.toggleExpand = function( uid ) {
			var expand;

			expand = ! UrvanovSyntaxHighlighterUtil.setDefault( urvanovSyntaxHighlighters[uid].expanded, false );
			toggleExpand( uid, expand );
		};

		updateWrap = function( uid, restore ) {
			restore = UrvanovSyntaxHighlighterUtil.setDefault( restore, true );
			if ( urvanovSyntaxHighlighters[uid].wrapped ) {
				urvanovSyntaxHighlighters[uid].addClass( URVANOV_SYNTAX_HIGHLIGHTER_WRAPPED );
			} else {
				urvanovSyntaxHighlighters[uid].removeClass( URVANOV_SYNTAX_HIGHLIGHTER_WRAPPED );
			}
			updateWrapButton( uid );
			if ( ! urvanovSyntaxHighlighters[uid].expanded && restore ) {
				restoreDimensions( uid );
			}
			urvanovSyntaxHighlighters[uid].wrapTimes = 0;
			clearInterval( urvanovSyntaxHighlighters[uid].wrapTimer );
			urvanovSyntaxHighlighters[uid].wrapTimer = setInterval(
				function() {
					if ( urvanovSyntaxHighlighters[uid].is( ':visible' ) ) {
						// XXX if hidden the height can't be determined.
						reconsileLines( uid );
						urvanovSyntaxHighlighters[uid].wrapTimes++;
						if ( 5 === urvanovSyntaxHighlighters[uid].wrapTimes ) {
							clearInterval( urvanovSyntaxHighlighters[uid].wrapTimer );
						}
					}
				},
				200
			);
		};

		// Convert '-10px' to -10.
		pxToInt = function( pixels ) {
			var result;

			if ( typeof pixels !== 'string' ) {
				return 0;
			}

			result = pixels.replace( /[^-0-9]/g, '' );

			if ( 0 === result.length ) {
				return 0;
			}

			return parseInt( result );
		};

		updateNumsButton = function( uid ) {
			if ( 'undefined' === typeof urvanovSyntaxHighlighters[uid] || 'undefined' === typeof urvanovSyntaxHighlighters[uid].numsVisible ) {
				return;
			}

			if ( urvanovSyntaxHighlighters[uid].numsVisible ) {
				urvanovSyntaxHighlighters[uid].numsButton.removeClass( UNPRESSED );
				urvanovSyntaxHighlighters[uid].numsButton.addClass( PRESSED );
			} else {
				// TODO doesn't work on iPhone.
				urvanovSyntaxHighlighters[uid].numsButton.removeClass( PRESSED );
				urvanovSyntaxHighlighters[uid].numsButton.addClass( UNPRESSED );
			}
		};

		updateWrapButton = function( uid ) {
			if ( 'undefined' === typeof urvanovSyntaxHighlighters[uid] || 'undefined' === typeof urvanovSyntaxHighlighters[uid].wrapped ) {
				return;
			}

			if ( urvanovSyntaxHighlighters[uid].wrapped ) {
				urvanovSyntaxHighlighters[uid].wrapButton.removeClass( UNPRESSED );
				urvanovSyntaxHighlighters[uid].wrapButton.addClass( PRESSED );
			} else {
				// TODO doesn't work on iPhone.
				urvanovSyntaxHighlighters[uid].wrapButton.removeClass( PRESSED );
				urvanovSyntaxHighlighters[uid].wrapButton.addClass( UNPRESSED );
			}
		};

		updateExpandButton = function( uid ) {
			if ( 'undefined' === typeof urvanovSyntaxHighlighters[uid] || 'undefined' === typeof urvanovSyntaxHighlighters[uid].expanded ) {
				return;
			}

			if ( urvanovSyntaxHighlighters[uid].expanded ) {
				urvanovSyntaxHighlighters[uid].expandButton.removeClass( UNPRESSED );
				urvanovSyntaxHighlighters[uid].expandButton.addClass( PRESSED );
			} else {
				// TODO doesn't work on iPhone.
				urvanovSyntaxHighlighters[uid].expandButton.removeClass( PRESSED );
				urvanovSyntaxHighlighters[uid].expandButton.addClass( UNPRESSED );
			}
		};

		updatePlainButton = function( uid ) {
			if ( 'undefined' === typeof urvanovSyntaxHighlighters[uid] || 'undefined' === typeof urvanovSyntaxHighlighters[uid].plainVisible ) {
				return;
			}

			if ( urvanovSyntaxHighlighters[uid].plainVisible ) {
				urvanovSyntaxHighlighters[uid].plainButton.removeClass( UNPRESSED );
				urvanovSyntaxHighlighters[uid].plainButton.addClass( PRESSED );
			} else {
				// TODO doesn't work on iPhone.
				urvanovSyntaxHighlighters[uid].plainButton.removeClass( PRESSED );
				urvanovSyntaxHighlighters[uid].plainButton.addClass( UNPRESSED );
			}
		};

		toggleToolbar = function( uid, show, animTime, hideDelay ) {
			var toolbar;

			if ( 'undefined' === typeof urvanovSyntaxHighlighters[uid] ) {
				return makeUID( uid );
			} else if ( ! urvanovSyntaxHighlighters[uid].toolbarMouseover ) {
				return;
			} else if ( false === show && urvanovSyntaxHighlighters[uid].toolbarPreventHide ) {
				return;
			} else if ( touchscreen ) {
				return;
			}

			toolbar = urvanovSyntaxHighlighters[uid].toolbar;

			if ( typeof hideDelay === 'undefined' ) {
				hideDelay = urvanovSyntaxHighlighters[uid].toolbar_delay;
			}

			urvanovSyntaxHighlighterSlide(
				uid,
				toolbar,
				show,
				animTime,
				hideDelay,
				function() {
					urvanovSyntaxHighlighters[uid].toolbarVisible = show;
				}
			);
		};

		addSize = function( orig, add ) {
			var copy     = $.extend( {}, orig );
			copy.width  += add.width;
			copy.height += add.height;

			return copy;
		};

		minusSize = function( orig, minus ) {
			var copy     = $.extend( {}, orig );
			copy.width  -= minus.width;
			copy.height -= minus.height;

			return copy;
		};

		initSize = function( uid ) {
			if ( typeof urvanovSyntaxHighlighters[uid].initialSize === 'undefined' ) {
				// Shared for scrollbars and expanding.
				urvanovSyntaxHighlighters[uid].toolbarHeight = urvanovSyntaxHighlighters[uid].toolbar.outerHeight();
				urvanovSyntaxHighlighters[uid].innerSize     = {
					width: urvanovSyntaxHighlighters[uid].width(),
					height: urvanovSyntaxHighlighters[uid].height(),
				};

				urvanovSyntaxHighlighters[uid].outerSize = {
					width: urvanovSyntaxHighlighters[uid].outerWidth(),
					height: urvanovSyntaxHighlighters[uid].outerHeight(),
				};

				urvanovSyntaxHighlighters[uid].borderSize = minusSize( urvanovSyntaxHighlighters[uid].outerSize, urvanovSyntaxHighlighters[uid].innerSize );

				urvanovSyntaxHighlighters[uid].initialSize = {
					width: urvanovSyntaxHighlighters[uid].main.outerWidth(),
					height: urvanovSyntaxHighlighters[uid].main.outerHeight(),
				};

				urvanovSyntaxHighlighters[uid].initialSize.height += urvanovSyntaxHighlighters[uid].toolbarHeight;

				urvanovSyntaxHighlighters[uid].initialOuterSize = addSize( urvanovSyntaxHighlighters[uid].initialSize, urvanovSyntaxHighlighters[uid].borderSize );

				urvanovSyntaxHighlighters[uid].finalSize = {
					width: urvanovSyntaxHighlighters[uid].table.outerWidth(),
					height: urvanovSyntaxHighlighters[uid].table.outerHeight(),
				};

				urvanovSyntaxHighlighters[uid].finalSize.height += urvanovSyntaxHighlighters[uid].toolbarHeight;

				// Ensure we don't shrink.
				urvanovSyntaxHighlighters[uid].finalSize.width     = UrvanovSyntaxHighlighterUtil.setMin( urvanovSyntaxHighlighters[uid].finalSize.width, urvanovSyntaxHighlighters[uid].initialSize.width );
				urvanovSyntaxHighlighters[uid].finalSize.height    = UrvanovSyntaxHighlighterUtil.setMin( urvanovSyntaxHighlighters[uid].finalSize.height, urvanovSyntaxHighlighters[uid].initialSize.height );
				urvanovSyntaxHighlighters[uid].diffSize            = minusSize( urvanovSyntaxHighlighters[uid].finalSize, urvanovSyntaxHighlighters[uid].initialSize );
				urvanovSyntaxHighlighters[uid].finalOuterSize      = addSize( urvanovSyntaxHighlighters[uid].finalSize, urvanovSyntaxHighlighters[uid].borderSize );
				urvanovSyntaxHighlighters[uid].initialSize.height += urvanovSyntaxHighlighters[uid].toolbar.outerHeight();
			}
		};

		toggleExpand = function( uid, expand ) {
			var main;
			var placeHolderSize;
			var expandHeight;
			var expandWidth;
			var newSize;
			var initialSize;
			var delay;

			if ( 'undefined' === typeof urvanovSyntaxHighlighters[uid] ) {
				return makeUID( uid );
			}

			if ( typeof expand === 'undefined' ) {
				return;
			}

			main = urvanovSyntaxHighlighters[uid].main;

			if ( expand ) {
				if ( typeof urvanovSyntaxHighlighters[uid].expanded === 'undefined' ) {
					initSize( uid );
					urvanovSyntaxHighlighters[uid].expandTime = UrvanovSyntaxHighlighterUtil.setRange( urvanovSyntaxHighlighters[uid].diffSize.width / 3, 300, 800 );

					urvanovSyntaxHighlighters[uid].expanded    = false;
					placeHolderSize                            = urvanovSyntaxHighlighters[uid].finalOuterSize;
					urvanovSyntaxHighlighters[uid].placeholder = $( '<div></div>' );

					urvanovSyntaxHighlighters[uid].placeholder.addClass( URVANOV_SYNTAX_HIGHLIGHTER_PLACEHOLDER );
					urvanovSyntaxHighlighters[uid].placeholder.css( placeHolderSize );
					urvanovSyntaxHighlighters[uid].before( urvanovSyntaxHighlighters[uid].placeholder );
					urvanovSyntaxHighlighters[uid].placeholder.css( 'margin', urvanovSyntaxHighlighters[uid].css( 'margin' ) );

					$( window ).on( 'resize', placeholderResize );
				}

				expandHeight = {
					height: 'auto',
					'min-height': 'none',
					'max-height': 'none',
				};

				expandWidth = {
					width: 'auto',
					'min-width': 'none',
					'max-width': 'none',
				};

				urvanovSyntaxHighlighters[uid].outerWidth( urvanovSyntaxHighlighters[uid].outerWidth() );
				urvanovSyntaxHighlighters[uid].css(
					{
						'min-width': 'none',
						'max-width': 'none',
					}
				);

				newSize = {
					width: urvanovSyntaxHighlighters[uid].finalOuterSize.width,
				};

				if ( ! urvanovSyntaxHighlighters[uid].mainHeightAuto && ! urvanovSyntaxHighlighters[uid].hasOneLine ) {
					newSize.height = urvanovSyntaxHighlighters[uid].finalOuterSize.height;
					urvanovSyntaxHighlighters[uid].outerHeight( urvanovSyntaxHighlighters[uid].outerHeight() );
				}

				main.css( expandHeight );
				main.css( expandWidth );
				urvanovSyntaxHighlighters[uid].stop( true );

				urvanovSyntaxHighlighters[uid].animate(
					newSize,
					animt( urvanovSyntaxHighlighters[uid].expandTime, uid ),
					function() {
						urvanovSyntaxHighlighters[uid].expanded = true;
						updateExpandButton( uid );
					}
				);

				urvanovSyntaxHighlighters[uid].placeholder.show();
				$( 'body' ).prepend( urvanovSyntaxHighlighters[uid] );
				urvanovSyntaxHighlighters[uid].addClass( URVANOV_SYNTAX_HIGHLIGHTER_EXPANDED );
				placeholderResize();
			} else {
				initialSize = urvanovSyntaxHighlighters[uid].initialOuterSize;
				delay       = urvanovSyntaxHighlighters[uid].toolbar_delay;

				if ( initialSize ) {
					urvanovSyntaxHighlighters[uid].stop( true );
					if ( ! urvanovSyntaxHighlighters[uid].expanded ) {
						urvanovSyntaxHighlighters[uid].delay( delay );
					}

					newSize = {
						width: initialSize.width,
					};

					if ( ! urvanovSyntaxHighlighters[uid].mainHeightAuto && ! urvanovSyntaxHighlighters[uid].hasOneLine ) {
						newSize.height = initialSize.height;
					}

					urvanovSyntaxHighlighters[uid].animate(
						newSize,
						animt( urvanovSyntaxHighlighters[uid].expandTime, uid ),
						function() {
							expandFinish( uid );
						}
					);
				} else {
					setTimeout(
						function() {
							expandFinish( uid );
						},
						delay
					);
				}

				urvanovSyntaxHighlighters[uid].placeholder.hide();
				urvanovSyntaxHighlighters[uid].placeholder.before( urvanovSyntaxHighlighters[uid] );
				urvanovSyntaxHighlighters[uid].css( { left: 'auto', top: 'auto' } );
				urvanovSyntaxHighlighters[uid].removeClass( URVANOV_SYNTAX_HIGHLIGHTER_EXPANDED );
			}

			reconsileDimensions( uid );

			if ( expand ) {
				updateWrap( uid, false );
			}
		};

		placeholderResize = function() {
			var uid;

			for ( uid in urvanovSyntaxHighlighters ) {
				if ( urvanovSyntaxHighlighters[uid].hasClass( URVANOV_SYNTAX_HIGHLIGHTER_EXPANDED ) ) {
					urvanovSyntaxHighlighters[uid].css( urvanovSyntaxHighlighters[uid].placeholder.offset() );
				}
			}
		};

		expandFinish = function( uid ) {
			urvanovSyntaxHighlighters[uid].expanded = false;
			restoreDimensions( uid );
			updateExpandButton( uid );
			if ( urvanovSyntaxHighlighters[uid].wrapped ) {
				updateWrap( uid );
			}
		};

		toggleScroll = function( uid, show, expand ) {
			var main;
			var plain;
			var visible;

			if ( typeof urvanovSyntaxHighlighters[uid] === 'undefined' ) {
				return makeUID( uid );
			}

			if ( typeof show === 'undefined' || expand || urvanovSyntaxHighlighters[uid].expanded ) {
				return;
			}

			main  = urvanovSyntaxHighlighters[uid].main;
			plain = urvanovSyntaxHighlighters[uid].plain;

			if ( show ) {
				// Show scrollbars.
				main.css( 'overflow', 'auto' );
				plain.css( 'overflow', 'auto' );
				if ( typeof urvanovSyntaxHighlighters[uid].top !== 'undefined' ) {
					visible = ( 1 === main.css( 'z-index' ) ? main : plain );

					// Browser will not render until scrollbar moves, move it manually.
					visible.scrollTop( urvanovSyntaxHighlighters[uid].top - 1 );
					visible.scrollTop( urvanovSyntaxHighlighters[uid].top );
					visible.scrollLeft( urvanovSyntaxHighlighters[uid].left - 1 );
					visible.scrollLeft( urvanovSyntaxHighlighters[uid].left );
				}
			} else {
				// Hide scrollbars.
				visible = ( 1 === main.css( 'z-index' ) ? main : plain );

				urvanovSyntaxHighlighters[uid].top  = visible.scrollTop();
				urvanovSyntaxHighlighters[uid].left = visible.scrollLeft();

				main.css( 'overflow', 'hidden' );
				plain.css( 'overflow', 'hidden' );
			}

			// Register that overflow has changed.
			urvanovSyntaxHighlighters[uid].scrollChanged = true;
			fixScrollBlank( uid );
		};

		/* Fix weird draw error, causes blank area to appear where scrollbar once was. */
		fixScrollBlank = function( uid ) {
			var redraw;

			// Scrollbar draw error in Chrome.
			urvanovSyntaxHighlighters[uid].table.style( 'width', '100%', 'important' );

			redraw = setTimeout(
				function() {
					urvanovSyntaxHighlighters[uid].table.style( 'width', '' );
					clearInterval( redraw );
				},
				10
			);
		};

		restoreDimensions = function( uid ) {
			// Restore dimensions.
			var main      = urvanovSyntaxHighlighters[uid].main;
			var mainStyle = urvanovSyntaxHighlighters[uid].mainStyle;

			main.css( mainStyle );

			// Width styles also apply to urvanovSyntaxHighlighter.
			urvanovSyntaxHighlighters[uid].css( 'height', 'auto' );
			urvanovSyntaxHighlighters[uid].css( 'width', mainStyle.width );
			urvanovSyntaxHighlighters[uid].css( 'max-width', mainStyle['max-width'] );
			urvanovSyntaxHighlighters[uid].css( 'min-width', mainStyle['min-width'] );
		};

		reconsileDimensions = function( uid ) {
			// Reconsile dimensions.
			urvanovSyntaxHighlighters[uid].plain.outerHeight( urvanovSyntaxHighlighters[uid].main.outerHeight() );
		};

		reconsileLines = function( uid ) {
			$( URVANOV_SYNTAX_HIGHLIGHTER_NUM, urvanovSyntaxHighlighters[uid] ).each(
				function() {
					var lineID = $( this ).attr( 'data-line' );
					var line   = $( '#' + lineID );
					var height = null;

					if ( urvanovSyntaxHighlighters[uid].wrapped ) {
						line.css( 'height', '' );
						height = line.outerHeight();
						height = height ? height : '';
						// TODO toolbar should overlay title if needed.
					} else {
						height = line.attr( 'data-height' );
						height = height ? height : '';
						line.css( 'height', height );
					}

					$( this ).css( 'height', height );
				}
			);
		};

		animt = function( x, uid ) {
			if ( 'fast' === x ) {
				x = 200;
			} else if ( 'slow' === x ) {
				x = 600;
			} else if ( ! isNumber( x ) ) {
				x = parseInt( x );
				if ( isNaN( x ) ) {
					return 0;
				}
			}

			return x * urvanovSyntaxHighlighters[uid].time;
		};

		isNumber = function( x ) {
			return typeof x === 'number';
		};
	};

	$( document ).ready(
		function() {
			UrvanovSyntaxHighlighterSyntax.init();
		}
	);
} )( jQueryUrvanovSyntaxHighlighter );

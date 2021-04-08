/* global UrvanovSyntaxHighlighterUtil, jQueryUrvanovSyntaxHighlighter, UrvanovSyntaxHighlighterTagEditorSettings, UrvanovSyntaxHighlighterSyntaxSettings, wp */

( function( $, wp ) {
	var CRAYON_INLINE_CSS = 'crayon-inline';
	var el                = wp.element.createElement;
	var registerBlockType = wp.blocks.registerBlockType;
	var blockStyle        = {};
	var UrvanovSyntaxHighlighterButton;

	registerBlockType(
		'urvanov-syntax-highlighter/code-block',
		{
			title: 'Urvanov Syntax Highlighter',

			icon: 'editor-code',

			category: 'formatting',
			attributes: {
				content: {
					type: 'string',
					source: 'html',
					selector: 'div',
				},
			},
			edit: function( props ) {
				var content = props.attributes.content;

				function onChangeContent( newContent ) {
					props.setAttributes( { content: newContent } );
				}

				return el(
					wp.element.Fragment,
					null,
					el(
						wp.editor.BlockControls,
						null,
						el(
							wp.components.Toolbar,
							null,
							el(
								wp.components.IconButton,
								{
									icon: 'editor-code',
									title: 'UrvanovSyntaxHighlighter',
									onClick: function() {
										window.UrvanovSyntaxHighlighterTagEditor.showDialog(
											{
												update: function() {},
												brHtmlBlockAfter: '',
												input: 'decode',
												output: 'encode',
												node: content ? UrvanovSyntaxHighlighterUtil.htmlToElements( content )[0] : null,
												insert: function( shortcode ) {
													onChangeContent(
														shortcode
													);
												},
											}
										);
									},
								},
								'UrvanovSyntaxHighlighter'
							)
						)
					),
					el( 'div', { style: blockStyle, dangerouslySetInnerHTML: { __html: props.attributes.content } } )
				);
			},

			save: function( props ) {
				var content = props.attributes.content;
				return el( 'div', { dangerouslySetInnerHTML: { __html: content } } );
			},
		}
	);

	UrvanovSyntaxHighlighterButton = function( props ) {
		var format;

		return wp.element.createElement(
			wp.editor.RichTextToolbarButton,
			{
				icon: 'editor-code',
				title: 'UrvanovSyntaxHighlighter',
				onClick: function() {
					var activeFormat = wp.richText.getActiveFormat( props.value, 'urvanov-syntax-highlighter/code-inline' );
					var startIndex   = props.value.start;
					var endIndex     = props.value.end;
					var inputRichTextValue;
					var inputValue;
					var inputNode;

					if ( activeFormat ) {
						format = 'urvanov-syntax-highlighter/code-inline';
						while (
							props.value.formats[startIndex] && props.value.formats[startIndex].find(
								// eslint-disable-next-line no-shadow
								function( el ) { // jshint ignore:line
									return el.type === format;
								}
							)
						) {
							startIndex--;
						}

						startIndex++;
						endIndex++;

						while (
							props.value.formats[endIndex] && props.value.formats[endIndex].find(
								// eslint-disable-next-line no-shadow
								function( el ) { // jshint ignore:line
									return el.type === format;
								}
							)
						) {
							endIndex++;
						}

						inputRichTextValue = wp.richText.slice( props.value, startIndex, endIndex );
						inputValue         = wp.richText.toHTMLString(
							{
								value: inputRichTextValue,
							}
						);

						inputNode = UrvanovSyntaxHighlighterUtil.htmlToElements( inputValue )[0];
					} else {
						inputRichTextValue = wp.richText.slice( props.value, startIndex, endIndex );
						inputValue         = '<span class="' + CRAYON_INLINE_CSS + '">' + wp.richText.toHTMLString(
							{
								value: inputRichTextValue,
							}
						) + '</span>';

						inputNode = UrvanovSyntaxHighlighterUtil.htmlToElements( inputValue )[0];
					}

					window.UrvanovSyntaxHighlighterTagEditor.showDialog(
						{
							update: function() {},
							node: inputNode,
							input: 'decode',
							output: 'encode',
							insert: function( shortcode ) {
								props.onChange(
									wp.richText.insert(
										props.value,
										wp.richText.create(
											{
												html: shortcode,
											}
										),
										startIndex,
										endIndex
									)
								);
							},
						}
					);
				},
				isActive: props.isActive,
			}
		);
	};

	wp.richText.registerFormatType(
		'urvanov-syntax-highlighter/code-inline',
		{
			title: 'UrvanovSyntaxHighlighter',
			tagName: 'span',
			className: CRAYON_INLINE_CSS,
			edit: UrvanovSyntaxHighlighterButton,
		}
	);

	window.UrvanovSyntaxHighlighterTagEditor = function() {
		var base    = this;
		var isInit  = false;
		var loaded  = false;
		var editing = false;
		var insertCallback;
		var editCallback;
		var showCallback;
		var hideCallback;
		var selectCallback;

		// Used for encoding, decoding.
		var inputHTML, outputHTML, editorName, ajaxClassTimer;
		var ajaxClassTimerCount = 0;
		var codeRefresh, urlRefresh;

		// Current $ obj of pre node.
		var currUrvanovSyntaxHighlighter = null;

		// Classes from pre node, excl. settings.
		var currClasses = '';

		// Whether to make span or pre.
		var isInline = false;

		// Generated in WP and contains the settings.
		var s, gs, util;

		// CSS.
		var dialog, code, clear, submit, cancel;

		var brHtmlBlockAfter;

		var colorboxSettings = {
			inline: true,
			width: 690,
			height: '90%',
			closeButton: false,
			fixed: true,
			transition: 'none',
			className: 'urvanov-syntax-highlighter-colorbox',
			onOpen: function() {
				$( this.outer ).prepend( $( s.bar_content ) );
			},
			onComplete: function() {
				$( s.code_css ).trigger( 'focus' );
			},
			onCleanup: function() {
				$( s.bar ).prepend( $( s.bar_content ) );
			},
		};

		base.init = function() {
			s    = UrvanovSyntaxHighlighterTagEditorSettings;
			gs   = UrvanovSyntaxHighlighterSyntaxSettings;
			util = UrvanovSyntaxHighlighterUtil;

			// This allows us to call $.colorbox and reload without needing a button click.
			colorboxSettings.href = s.content_css;
		};

		base.bind = function( buttonCls ) {
			var $buttons;

			if ( ! isInit ) {
				isInit = true;
				base.init();
			}

			$buttons = $( buttonCls );
			$buttons.each(
				function( i, button ) {
					var $button  = $( button );
					var $wrapper = $( '<a class="urvanov-syntax-highlighter-tag-editor-button-wrapper"></a>' ).attr( 'href', s.content_css );
					$button.after( $wrapper );
					$wrapper.append( $button );
					$wrapper.colorbox( colorboxSettings );
				}
			);
		};

		base.hide = function() {
			$.colorbox.close();
			return false;
		};

		// XXX Loads dialog contents.
		base.loadDialog = function( callback ) {
			// Loaded once url is given.
			if ( ! loaded ) {
				loaded = true;
			} else {
				// eslint-disable-next-line no-unused-expressions
				callback && callback();
				return;
			}

			// Load the editor content.
			UrvanovSyntaxHighlighterUtil.getAJAX(
				{
					action: 'urvanov-syntax-highlighter-tag-editor',
					is_admin: gs.is_admin,
				},
				function( data ) {
					var url;
					var urlInfo;
					var exts;
					var settingChange;

					dialog = $( '<div id="' + s.css + '"></div>' );
					dialog.appendTo( 'body' ).hide();
					dialog.html( data );

					base.setOrigValues();

					submit = dialog.find( s.submit_css );
					cancel = dialog.find( s.cancel_css );

					code  = $( s.code_css );
					clear = $( '#urvanov-syntax-highlighter-te-clear' );

					codeRefresh = function() {
						var clearVisible = clear.is( ':visible' );

						if ( code.val().length > 0 && ! clearVisible ) {
							clear.show();
							code.removeClass( gs.selected );
						} else if ( code.val().length <= 0 ) {
							clear.hide();
						}
					};

					code.keyup( codeRefresh );
					code.change( codeRefresh );
					clear.on(
						'click',
						function() {
							code.val( '' );
							code.removeClass( gs.selected );
							code.trigger( 'focus' );
						}
					);

					url     = $( s.url_css );
					urlInfo = $( s.urlInfo_css );
					exts    = UrvanovSyntaxHighlighterTagEditorSettings.extensions;

					urlRefresh = function() {
						var ext;
						var lang;
						var langId;
						var finalLang;

						if ( url.val().length > 0 && ! urlInfo.is( ':visible' ) ) {
							urlInfo.show();
							url.removeClass( gs.selected );
						} else if ( url.val().length <= 0 ) {
							urlInfo.hide();
						}

						// Check for extensions and select language automatically.
						ext = UrvanovSyntaxHighlighterUtil.getExt( url.val() );
						if ( ext ) {
							lang = exts[ext];

							// Otherwise use the extention as the lang.
							langId    = lang ? lang : ext;
							finalLang = UrvanovSyntaxHighlighterTagEditorSettings.fallback_lang;

							$( s.lang_css + ' option' ).each(
								function() {
									if ( $( this ).val() === langId ) {
										finalLang = langId;
									}
								}
							);

							$( s.lang_css ).val( finalLang );
						}
					};

					url.keyup( urlRefresh );
					url.change( urlRefresh );

					settingChange = function() {
						var setting   = $( this );
						var origValue = $( this ).attr( gs.origValue );
						var value;
						var highlight;

						if ( typeof origValue === 'undefined' ) {
							origValue = '';
						}

						// Depends on type.
						value = base.settingValue( setting );
						UrvanovSyntaxHighlighterUtil.log( setting.attr( 'id' ) + ' value: ' + value );
						highlight = null;

						if ( setting.is( 'input[type=checkbox]' ) ) {
							highlight = setting.next( 'span' );
						}

						UrvanovSyntaxHighlighterUtil.log( '   >>> ' + setting.attr( 'id' ) + ' is ' + origValue + ' = ' + value );
						if ( origValue === value ) {
							// No change.
							setting.removeClass( gs.changed );
							if ( highlight ) {
								highlight.removeClass( gs.changed );
							}
						} else {
							// Changed.
							setting.addClass( gs.changed );
							if ( highlight ) {
								highlight.addClass( gs.changed );
							}
						}

						// Save standardized value for later.
						base.settingValue( setting, value );
					};

					$( '.' + gs.setting + '[id]:not(.' + gs.special + ')' ).each(
						function() {
							$( this ).change( settingChange );
							$( this ).keyup( settingChange );
						}
					);

					// eslint-disable-next-line no-unused-expressions
					callback && callback();
				}
			);
		};

		// XXX Displays the dialog.
		base.showDialog = function( args ) {
			base.loadDialog(
				function() {
					$.colorbox( colorboxSettings );

					base._showDialog( args );
				}
			);
		};

		base._showDialog = function( args ) {
			var re;
			var matches;
			var atts = {};
			var id;
			var value;
			var title;
			var url;
			var availLangs = [];
			var att;
			var setting;
			var content;
			var dialogTitle;
			var ajaxWindow;
			var currNode;
			var inline;
			var fallback;
			var oldScroll;
			var highlight;
			var i;

			args = $.extend(
				{
					insert: null,
					edit: null,
					show: null,
					hide: base.hide,
					select: null,
					editor_str: null,
					ed: null,
					node: null,
					input: null,
					output: null,
					brHtmlBlockAfter: '<p>&nbsp;</p>',
				},
				args
			);

			// Need to reset all settings back to original, clear yellow highlighting.
			base.resetSettings();

			// Save these for when we add a UrvanovSyntaxHighlighter.
			insertCallback   = args.insert;
			editCallback     = args.edit;
			showCallback     = args.show;
			hideCallback     = args.hide;
			selectCallback   = args.select;
			inputHTML        = args.input;
			outputHTML       = args.output;
			editorName       = args.editor_str;
			brHtmlBlockAfter = args.brHtmlBlockAfter;
			currNode         = args.node;
			isInline         = false;

			// Unbind submit.
			submit.unbind();
			submit.on(
				'click',
				function( e ) {
					base.submitButton();
					e.preventDefault();
				}
			);

			base.setSubmitText( s.submit_add );

			cancel.unbind();
			cancel.on(
				'click',
				function( e ) {
					base.hide();
					e.preventDefault();
				}
			);

			if ( base.isUrvanovSyntaxHighlighter( currNode ) ) {
				currUrvanovSyntaxHighlighter = $( currNode );

				if ( 0 !== currUrvanovSyntaxHighlighter.length ) {
					// Read back settings for editing.
					currClasses = currUrvanovSyntaxHighlighter.attr( 'class' );
					re          = new RegExp( '\\b([A-Za-z-]+)' + s.attr_sep + '(\\S+)', 'gim' );
					matches     = re.execAll( currClasses );

					// Retain all other classes, remove settings.
					currClasses = $.trim( currClasses.replace( re, '' ) );

					for ( i in matches ) {
						id       = matches[i][1];
						value    = matches[i][2];
						atts[id] = value;
					}

					// Title.
					title = currUrvanovSyntaxHighlighter.attr( 'title' );
					if ( title ) {
						atts.title = title;
					}

					// URL.
					url = currUrvanovSyntaxHighlighter.attr( 'data-url' );
					if ( url ) {
						atts.url = url;
					}

					// Inverted settings.
					if ( typeof atts.highlight !== 'undefined' ) {
						atts.highlight = '0' ? '1' : '0';
					}

					// Inline.
					isInline    = currUrvanovSyntaxHighlighter.hasClass( CRAYON_INLINE_CSS );
					atts.inline = isInline ? '1' : '0';

					// Ensure language goes to fallback if invalid.
					$( s.lang_css + ' option' ).each(
						function() {
							value = $( this ).val();

							if ( value ) {
								availLangs.push( value );
							}
						}
					);

					if ( -1 === $.inArray( atts.lang, availLangs ) ) {
						atts.lang = s.fallback_lang;
					}

					// Validate the attributes.
					atts = base.validate( atts );

					// Load in attributes, add prefix.
					for ( att in atts ) {
						setting = $( '#' + gs.prefix + att + '.' + gs.setting );
						value   = atts[att];

						base.settingValue( setting, value );

						// Update highlights.
						setting.change();

						// If global setting changes and we access settings, it should declare loaded settings as changed even if they equal the global value, just so they aren't lost on save.
						if ( ! setting.hasClass( gs.special ) ) {
							setting.addClass( gs.changed );
							if ( setting.is( 'input[type=checkbox]' ) ) {
								highlight = setting.next( 'span' );
								highlight.addClass( gs.changed );
							}
						}

						UrvanovSyntaxHighlighterUtil.log( 'loaded: ' + att + ':' + value );
					}

					editing = true;
					base.setSubmitText( s.submit_edit );

					// Code.
					content = currUrvanovSyntaxHighlighter.html();
					if ( 'encode' === inputHTML ) {
						content = UrvanovSyntaxHighlighterUtil.encode_html( content );
					} else if ( 'decode' === inputHTML ) {
						content = UrvanovSyntaxHighlighterUtil.decode_html( content );
					}

					code.val( content );
				} else {
					UrvanovSyntaxHighlighterUtil.log( 'cannot load currNode of type pre' );
				}
			} else {
				if ( selectCallback ) {
					// Add selected content as code.
					code.val( selectCallback );
				}

				// We are creating a new UrvanovSyntaxHighlighter, not editing.
				editing = false;
				base.setSubmitText( s.submit_add );
				currUrvanovSyntaxHighlighter = null;
				currClasses                  = '';
			}

			// Inline.
			inline = $( '#' + s.inline_css );
			inline.change(
				function() {
					var inlineHide;
					var inlineSingle;
					var disabled;
					var obj;

					isInline = $( this ).is( ':checked' );

					inlineHide   = $( '.' + s.inline_hide_css );
					inlineSingle = $( '.' + s.inline_hide_only_css );
					disabled     = [ s.mark_css, s.range_css, s.title_css, s.url_css ];

					for ( i in disabled ) {
						obj = $( disabled[i] );
						obj.attr( 'disabled', isInline );
					}

					if ( isInline ) {
						inlineHide.hide();
						inlineSingle.hide();
						inlineHide.closest( 'tr' ).hide();

						for ( i in disabled ) {
							obj = $( disabled[i] );
							obj.addClass( 'urvanov-syntax-highlighter-disabled' );
						}
					} else {
						inlineHide.show();
						inlineSingle.show();
						inlineHide.closest( 'tr' ).show();
						for ( i in disabled ) {
							obj = $( disabled[i] );
							obj.removeClass( 'urvanov-syntax-highlighter-disabled' );
						}
					}
				}
			);

			inline.change();

			// Show the dialog.
			dialogTitle = editing ? s.edit_text : s.add_text;
			$( s.dialog_title_css ).html( dialogTitle );
			if ( showCallback ) {
				showCallback();
			}

			code.trigger( 'focus' );
			codeRefresh();
			urlRefresh();
			if ( ajaxClassTimer ) {
				clearInterval( ajaxClassTimer );
				ajaxClassTimerCount = 0;
			}

			ajaxWindow = $( '#TB_window' );
			ajaxWindow.hide();
			fallback = function() {
				ajaxWindow.show();

				// Prevent draw artifacts.
				oldScroll = $( window ).scrollTop();
				$( window ).scrollTop( oldScroll + 10 );
				$( window ).scrollTop( oldScroll - 10 );
			};

			ajaxClassTimer = setInterval(
				function() {
					if ( typeof ajaxWindow !== 'undefined' && ! ajaxWindow.hasClass( 'urvanov-syntax-highlighter-te-ajax' ) ) {
						ajaxWindow.addClass( 'urvanov-syntax-highlighter-te-ajax' );
						clearInterval( ajaxClassTimer );
						fallback();
					}
					if ( ajaxClassTimerCount >= 100 ) {
						// In case it never loads, terminate.
						clearInterval( ajaxClassTimer );
						fallback();
					}
					ajaxClassTimerCount++;
				},
				40
			);
		};

		// XXX Add UrvanovSyntaxHighlighter to editor.
		base.addUrvanovSyntaxHighlighter = function() {
			var inline;
			var brBefore = '';
			var brAfter  = '';
			var tag;
			var shortcode;
			var atts = {};
			var inlineRe;
			var id;
			var value;
			var mark;
			var range;
			var title;
			var content;
			var url = $( s.url_css );

			if ( 0 === url.val().length && 0 === code.val().length ) {
				code.addClass( gs.selected );
				code.trigger( 'focus' );
				return false;
			}

			code.removeClass( gs.selected );

			// Add inline for matching with CSS.
			inline   = $( '#' + s.inline_css );
			isInline = 0 !== inline.length && inline.is( ':checked' );

			// Spacing only for <pre>.
			if ( ! editing ) {
				// Don't add spaces if editing.
				if ( ! isInline ) {
					if ( 'html' === editorName ) {
						brAfter = brBefore = ' \n';
					} else {
						brAfter = brHtmlBlockAfter;
					}
				} else {
					// Add a space after.
					if ( 'html' === editorName ) {
						brAfter = brBefore = ' ';
					} else {
						brAfter = '&nbsp;';
					}
				}
			}

			tag        = ( isInline ? 'span' : 'pre' );
			shortcode  = brBefore + '<' + tag + ' ';
			shortcode += 'class="';

			inlineRe = new RegExp( '\\b' + CRAYON_INLINE_CSS + '\\b', 'gim' );
			if ( isInline ) {
				// If don't have inline class, add it.
				if ( null === inlineRe.exec( currClasses ) ) {
					currClasses += ' ' + CRAYON_INLINE_CSS + ' ';
				}
			} else {
				// Remove inline css if it exists.
				currClasses = currClasses.replace( inlineRe, '' );
			}

			// Grab settings as attributes.
			$( '.' + gs.changed + '[id],.' + gs.changed + '[' + s.data_value + ']' ).each(
				function() {
					id    = $( this ).attr( 'id' );
					value = $( this ).attr( s.data_value );

					// Remove prefix.
					id       = util.removePrefixFromID( id );
					atts[id] = value;
				}
			);

			// Settings.
			atts.lang = $( s.lang_css ).val();
			mark      = $( s.mark_css ).val();

			if ( 0 !== mark.length && ! isInline ) {
				atts.mark = mark;
			}

			range = $( s.range_css ).val();

			if ( 0 !== range.length && ! isInline ) {
				atts.range = range;
			}

			// XXX Code highlighting, checked means 0!.
			if ( $( s.hl_css ).is( ':checked' ) ) {
				atts.highlight = '0';
			}

			// XXX Very important when working with editor.
			atts.decode = 'true';

			// Validate the attributes.
			atts = base.validate( atts );

			for ( id in atts ) {
				// Remove prefix, if exists.
				value = atts[id];
				UrvanovSyntaxHighlighterUtil.log( 'add ' + id + ':' + value );
				shortcode += id + s.attr_sep + value + ' ';
			}

			// Add classes.
			shortcode += currClasses;

			// Don't forget to close quote for class.
			shortcode += '" ';

			if ( ! isInline ) {
				// Title.
				title = $( s.title_css ).val();
				if ( 0 !== title.length ) {
					shortcode += 'title="' + title + '" ';
				}

				// URL.
				url = $( s.url_css ).val();
				if ( 0 !== url.length ) {
					shortcode += 'data-url="' + url + '" ';
				}
			}

			content = $( s.code_css ).val();
			if ( 'encode' === outputHTML ) {
				content = UrvanovSyntaxHighlighterUtil.encode_html( content );
			} else if ( 'decode' === outputHTML ) {
				content = UrvanovSyntaxHighlighterUtil.decode_html( content );
			}

			content    = 'undefined' !== typeof content ? content : '';
			shortcode += '>' + content + '</' + tag + '>' + brAfter;

			if ( editing && editCallback ) {
				// Edit the current selected node.
				editCallback( shortcode );
			} else if ( insertCallback ) {
				// Insert the tag and hide dialog.
				insertCallback( shortcode );
			}

			return true;
		};

		base.submitButton = function() {
			UrvanovSyntaxHighlighterUtil.log( 'submit' );
			if ( false !== base.addUrvanovSyntaxHighlighter() ) {
				base.hideDialog();
			}
		};

		base.hideDialog = function() {
			UrvanovSyntaxHighlighterUtil.log( 'hide' );
			if ( hideCallback ) {
				hideCallback();
			}
		};

		// XXX Auxiliary methods.
		base.setOrigValues = function() {
			$( '.' + gs.setting + '[id]' ).each(
				function() {
					var setting = $( this );

					setting.attr( gs.origValue, base.settingValue( setting ) );
				}
			);
		};

		base.resetSettings = function() {
			UrvanovSyntaxHighlighterUtil.log( 'reset' );
			$( '.' + gs.setting ).each(
				function() {
					var setting = $( this );
					base.settingValue( setting, setting.attr( gs.origValue ) );

					// Update highlights.
					setting.change();
				}
			);

			code.val( '' );
		};

		base.settingValue = function( setting, value ) {
			if ( 'undefined' === typeof value ) {
				// getter.
				value = '';

				if ( setting.is( 'input[type=checkbox]' ) ) {
					// Boolean is stored as string.
					value = setting.is( ':checked' ) ? 'true' : 'false';
				} else {
					value = setting.val();
				}

				return value;
			}

			// setter.
			if ( setting.is( 'input[type=checkbox]' ) ) {
				if ( typeof value === 'string' ) {
					if ( 'true' === value || '1' === value ) {
						value = true;
					} else if ( 'false' === value || '0' === value ) {
						value = false;
					}
				}
				setting.prop( 'checked', value );
			} else {
				setting.val( value );
			}

			setting.attr( s.data_value, value );
		};

		base.validate = function( atts ) {
			var fields = [ 'range', 'mark' ];
			var i;
			var field;

			for ( i in fields ) {
				field = fields[i];

				if ( 'undefined' !== typeof atts[field] ) {
					atts[field] = atts[field].replace( /\s/g, '' );
				}
			}

			return atts;
		};

		base.isUrvanovSyntaxHighlighter = function( node ) {
			return null !== node &&
				( 'PRE' === node.nodeName || ( 'SPAN' === node.nodeName && $( node ).hasClass( CRAYON_INLINE_CSS ) ) );
		};

		base.elemValue = function( obj ) {
			var value = null;

			if ( obj.is( 'input[type=checkbox]' ) ) {
				value = obj.is( ':checked' );
			} else {
				value = obj.val();
			}

			return value;
		};

		base.setSubmitText = function( text ) {
			submit.html( text );
		};
	};
} )( jQueryUrvanovSyntaxHighlighter, wp );

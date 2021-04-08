/* global jQueryUrvanovSyntaxHighlighter, UrvanovSyntaxHighlighterAdmin, UrvanovSyntaxHighlighterSyntax, UrvanovSyntaxHighlighterThemeEditor, UrvanovSyntaxHighlighterThemeEditorStrings, UrvanovSyntaxHighlighterUtil, UrvanovSyntaxHighlighterSyntaxSettings, UrvanovSyntaxHighlighterAdminSettings, UrvanovSyntaxHighlighterAdminStrings */

// Urvanov Syntax Highlighter Admin JavaScript.
( function( $ ) {
	window.UrvanovSyntaxHighlighterAdmin = new function() { // jshint ignore:line
		var base = this;

		// Preview.
		var preview, previewWrapper, previewInner, previewInfo, previewCbox, previewDelayTimer, previewLoaded;

		// The DOM object ids that trigger a preview update.
		var previewObjNames = [];

		// The jQuery objects for these objects.
		var previewObjs       = [];
		var previewLastValues = [];

		// Alignment.
		var alignDrop, float;

		// Toolbar.
		var overlay, toolbar;

		// Error.
		var msgCbox, msg;

		// Log.
		var logButton, logText, logWrapper, changeButton, changeCode, plain, copy, clog, help;
		var mainWrap, themeEditorWrap, themeEditorLoading, themeEditorEditButton, themeEditorCreateButton, themeEditorDuplicateButton, themeEditorDeleteButton, themeEditorSubmitButton;
		var themeSelect, themeInfo;

		var settings      = null;
		var strings       = null;
		var adminSettings = null;
		var util          = null;

		var previewToggle;
		var floatToggle;
		var previewCallback;
		var previewTxtChange;
		var previewTxtCallback; // Only updates if text value changed.
		var previewTxtCallbackDelayed;
		var previewRegister;
		var toggleError;
		var toggleToolbar;
		var showLog;
		var hideLog;
		var len;

		base.init = function() {
			var dialogFunction;

			UrvanovSyntaxHighlighterUtil.log( 'admin init' );

			settings      = UrvanovSyntaxHighlighterSyntaxSettings;
			adminSettings = UrvanovSyntaxHighlighterAdminSettings;
			strings       = UrvanovSyntaxHighlighterAdminStrings;
			util          = UrvanovSyntaxHighlighterUtil;

			// Dialogs.
			dialogFunction = adminSettings.dialogFunction;
			dialogFunction = $.fn[dialogFunction] ? dialogFunction : 'dialog';

			$.fn.urvanovSyntaxHighlighterDialog = $.fn[dialogFunction];

			// Wraps.
			mainWrap        = $( '#urvanov-syntax-highlighter-main-wrap' );
			themeEditorWrap = $( '#urvanov-syntax-highlighter-theme-editor-wrap' );

			// Themes.
			themeSelect = $( '#urvanov-syntax-highlighter-theme' );
			themeInfo   = $( '#urvanov-syntax-highlighter-theme-info' );

			base.show_theme_info();
			themeSelect.on(
				'change',
				function() {
					base.show_theme_info();
					base.preview_update();
				}
			);

			themeEditorEditButton      = $( '#urvanov-syntax-highlighter-theme-editor-edit-button' );
			themeEditorCreateButton    = $( '#urvanov-syntax-highlighter-theme-editor-create-button' );
			themeEditorDuplicateButton = $( '#urvanov-syntax-highlighter-theme-editor-duplicate-button' );
			themeEditorDeleteButton    = $( '#urvanov-syntax-highlighter-theme-editor-delete-button' );
			themeEditorSubmitButton    = $( '#urvanov-syntax-highlighter-theme-editor-submit-button' );

			themeEditorEditButton.on(
				'click',
				function() {
					base.show_theme_editor(
						themeEditorEditButton,
						true
					);
				}
			);

			themeEditorCreateButton.on(
				'click',
				function() {
					base.show_theme_editor(
						themeEditorCreateButton,
						false
					);
				}
			);

			themeEditorDuplicateButton.on(
				'click',
				function() {
					UrvanovSyntaxHighlighterThemeEditor.duplicate( adminSettings.currTheme, adminSettings.currThemeName );
				}
			);

			themeEditorDeleteButton.on(
				'click',
				function() {
					if ( ! themeEditorEditButton.attr( 'disabled' ) ) {
						UrvanovSyntaxHighlighterThemeEditor.del( adminSettings.currTheme, adminSettings.currThemeName );
					}

					return false;
				}
			);

			themeEditorSubmitButton.on(
				'click',
				function() {
					UrvanovSyntaxHighlighterThemeEditor.submit( adminSettings.currTheme, adminSettings.currThemeName );
				}
			);

			// Help.
			help = $( '.urvanov-syntax-highlighter-help-close' );
			help.on(
				'click',
				function() {
					$( '.urvanov-syntax-highlighter-help' ).hide();
					UrvanovSyntaxHighlighterUtil.getAJAX(
						{
							action: 'urvanov-syntax-highlighter-ajax',
							'hide-help': 1,
						}
					);
				}
			);

			// Preview.
			preview        = $( '#urvanov-syntax-highlighter-live-preview' );
			previewWrapper = $( '#urvanov-syntax-highlighter-live-preview-wrapper' );
			previewInner   = $( '#urvanov-syntax-highlighter-live-preview-inner' );
			previewInfo    = $( '#urvanov-syntax-highlighter-preview-info' );
			previewCbox    = util.cssElem( '#preview' );

			if ( 0 !== preview.length ) {
				// Preview not needed in Tag Editor.
				previewRegister();

				preview.ready(
					function() {
						previewToggle();
					}
				);

				previewCbox.on(
					'change',
					function() {
						previewToggle();
					}
				);
			}

			$( '#show-posts' ).on(
				'click',
				function() {
					UrvanovSyntaxHighlighterUtil.getAJAX(
						{
							action: 'urvanov-syntax-highlighter-show-posts',
						},
						function( data ) {
							$( '#urvanov-syntax-highlighter-subsection-posts-info' ).html( data );
						}
					);
				}
			);

			$( '#show-langs' ).on(
				'click',
				function() {
					UrvanovSyntaxHighlighterUtil.getAJAX(
						{
							action: 'urvanov-syntax-highlighter-show-langs',
						},
						function( data ) {
							$( '#lang-info' ).hide();
							$( '#urvanov-syntax-highlighter-subsection-langs-info' ).html( data );
						}
					);
				}
			);

			// Convert.
			$( '#urvanov-syntax-highlighter-settings-form input' ).on(
				'focusin focusout mouseup',
				function() {
					$( '#urvanov-syntax-highlighter-settings-form' ).data( 'lastSelected', $( this ) );
				}
			);

			$( '#urvanov-syntax-highlighter-settings-form' ).on(
				'submit',
				function() {
					var last   = $( this ).data( 'lastSelected' ).get( 0 );
					var target = $( '#convert' ).get( 0 );
					var r;

					if ( last === target ) {
						// eslint-disable-next-line no-alert
						r = confirm( 'Please BACKUP your database first! Converting will update your post content. Do you wish to continue?' );

						return r;
					}
				}
			);

			// Alignment.
			alignDrop = util.cssElem( '#h-align' );
			float     = $( '#urvanov-syntax-highlighter-subsection-float' );

			alignDrop.on(
				'change',
				function() {
					floatToggle();
				}
			);

			alignDrop.ready(
				function() {
					floatToggle();
				}
			);

			// Custom Error.
			msgCbox = util.cssElem( '#error-msg-show' );
			msg     = util.cssElem( '#error-msg' );

			toggleError();

			msgCbox.on(
				'change',
				function() {
					toggleError();
				}
			);

			// Toolbar.
			overlay = $( '#urvanov-syntax-highlighter-subsection-toolbar' );
			toolbar = util.cssElem( '#toolbar' );

			toggleToolbar();

			toolbar.on(
				'change',
				function() {
					toggleToolbar();
				}
			);

			// Copy.
			plain = util.cssElem( '#plain' );
			copy  = $( '#urvanov-syntax-highlighter-subsection-copy-check' );

			plain.on(
				'change',
				function() {
					if ( plain.is( ':checked' ) ) {
						copy.show();
					} else {
						copy.hide();
					}
				}
			);

			// Log.
			logWrapper = $( '#urvanov-syntax-highlighter-log-wrapper' );
			logButton  = $( '#urvanov-syntax-highlighter-log-toggle' );
			logText    = $( '#urvanov-syntax-highlighter-log-text' );
			showLog    = logButton.attr( 'show_txt' );
			hideLog    = logButton.attr( 'hide_txt' );
			clog       = $( '#urvanov-syntax-highlighter-log' );

			logButton.val( showLog );
			logButton.on(
				'click',
				function() {
					var text;

					clog.width( logWrapper.width() );
					clog.toggle();

					// Scrolls content.
					clog.scrollTop( logText.height() );
					text = ( logButton.val() === showLog ? hideLog : showLog );
					logButton.val( text );
				}
			);

			changeButton = $( '#urvanov-syntax-highlighter-change-code' );
			changeButton.on(
				'click',
				function() {
					base.createDialog(
						{
							title: strings.changeCode,
							html: '<textarea id="urvanov-syntax-highlighter-change-code-text"></textarea>',
							desc: null,
							value: '',
							options: {
								buttons: {
									OK: function() {
										changeCode = $( '#urvanov-syntax-highlighter-change-code-text' ).val();
										base.preview_update();
										$( this ).urvanovSyntaxHighlighterDialog( 'close' );
									},
									Cancel: function() {
										$( this ).urvanovSyntaxHighlighterDialog( 'close' );
									},
								},
								open: function() {
									if ( changeCode ) {
										$( '#urvanov-syntax-highlighter-change-code-text' ).val( changeCode );
									}
								},
							},
						}
					);

					return false;
				}
			);

			$( '#urvanov-syntax-highlighter-fallback-lang' ).on(
				'change',
				function() {
					changeCode = null;
					base.preview_update();
				}
			);
		};

		/* Whenever a control changes preview */
		base.preview_update = function( vars ) {
			var val = 0;
			var obj;
			var i;
			var getVars = $.extend(
				{
					action: 'urvanov-syntax-highlighter-show-preview',
					theme: adminSettings.currTheme,
				},
				vars
			);

			if ( changeCode ) {
				getVars[adminSettings.sampleCode] = changeCode;
			}

			len = previewObjNames.length;
			for ( i = 0; i < len; i++ ) {
				obj = previewObjs[i];

				if ( 'checkbox' === obj.attr( 'type' ) ) {
					val = obj.is( ':checked' );
				} else {
					val = obj.val();
				}

				getVars[previewObjNames[i]] = val;
			}

			// Load Preview.
			UrvanovSyntaxHighlighterUtil.postAJAX(
				getVars,
				function( data ) {
					preview.html( data );

					// Important! Calls the urvanov_syntax_highlighter.js init.
					UrvanovSyntaxHighlighterSyntax.init();
					base.preview_ready();
				}
			);
		};

		base.preview_ready = function() {
			if ( ! previewLoaded ) {
				previewLoaded = true;
				if ( window.GET['theme-editor'] ) {
					UrvanovSyntaxHighlighterAdmin.show_theme_editor( themeEditorEditButton, true );
				}
			}
		};

		previewToggle = function() {
			if ( previewCbox.is( ':checked' ) ) {
				preview.show();
				previewInfo.show();
				base.preview_update();
			} else {
				preview.hide();
				previewInfo.hide();
			}
		};

		floatToggle = function() {
			if ( 0 !== alignDrop.val() ) {
				float.show();
			} else {
				float.hide();
			}
		};

		// Register all event handlers for preview objects.
		previewRegister = function() {
			// Instant callback.
			previewCallback = function() {
				base.preview_update();
			};

			// Checks if the text input is changed, if so, runs the callback
			// with given event.
			previewTxtChange = function( callback, event ) {
				var obj  = event.target;
				var last = previewLastValues[obj.id];

				if ( obj.value !== last ) {
					// Update last value to current.
					previewLastValues[obj.id] = obj.value;

					// Run callback with event.
					callback( event );
				}
			};

			// Only updates when text is changed.
			previewTxtCallback = function( event ) {
				previewTxtChange( base.preview_update, event );
			};

			// Only updates when text is changed, but callback.
			previewTxtCallbackDelayed = function( event ) {
				previewTxtChange(
					function() {
						clearInterval( previewDelayTimer );
						previewDelayTimer = setInterval(
							function() {
								base.preview_update();
								clearInterval( previewDelayTimer );
							},
							500
						);
					},
					event
				);
			};

			// Retreive preview objects.
			$( '[urvanov-syntax-highlighter-preview="1"]' ).each(
				function( i ) {
					var obj = $( this );
					var id  = obj.attr( 'id' );

					// XXX Remove prefix.
					id                 = util.removePrefixFromID( id );
					previewObjNames[i] = id;
					previewObjs[i]     = obj;

					// To capture key up events when typing.
					if ( 'text' === obj.attr( 'type' ) ) {
						previewLastValues[obj.attr( 'id' )] = obj.val();

						obj.on( 'keyup', previewTxtCallbackDelayed );
						obj.on( 'change', previewTxtCallback );
					} else {
						// For all other objects.
						obj.on( 'change', previewCallback );
					}
				}
			);
		};

		toggleError = function() {
			if ( msgCbox.is( ':checked' ) ) {
				msg.show();
			} else {
				msg.hide();
			}
		};

		toggleToolbar = function() {
			if ( 0 === toolbar.val() ) {
				overlay.show();
			} else {
				overlay.hide();
			}
		};

		base.get_vars = function() {
			var vars = {};

			window.location.href.replace(
				/[?&]+([^=&]+)=([^&]*)/gi,
				function( m, key, value ) {
					vars[key] = value;
				}
			);

			return vars;
		};

		// Changing wrap views.
		base.show_main = function() {
			themeEditorWrap.hide();
			mainWrap.show();

			return false;
		};

		base.refresh_themeInfo = function( callback ) {
			adminSettings.currTheme       = themeSelect.val();
			adminSettings.currThemeName   = themeSelect.find( 'option:selected' ).attr( 'data-value' );
			adminSettings.currThemeIsUser = adminSettings.currTheme in adminSettings.userThemes;
			adminSettings.currThemeURL    = base.get_theme_url( adminSettings.currTheme );

			// Load the theme file.
			$.ajax(
				{
					url: adminSettings.currThemeURL,
					success: function( data ) {
						adminSettings.currThemeCSS = data;

						if ( callback ) {
							callback();
						}
					},
					cache: false,
				}
			);

			adminSettings.currThemeCSS = '';
		};

		base.get_theme_url = function( $id ) {
			var url = $id in adminSettings.userThemes ? adminSettings.userThemesURL : adminSettings.themesURL;

			return url + $id + '/' + $id + '.css';
		};

		base.show_theme_info = function( callback ) {
			base.refresh_themeInfo(
				function() {
					var type;
					var typeName;
					var info     = UrvanovSyntaxHighlighterThemeEditor.readCSSInfo( adminSettings.currThemeCSS );
					var infoHTML = '';
					var disabled;
					var id;

					for ( id in info ) {
						if ( 'name' !== id ) {
							infoHTML += '<div class="fieldset">';

							if ( 'description' !== id ) {
								infoHTML += '<div class="' + id + ' field">' + UrvanovSyntaxHighlighterThemeEditor.getFieldName( id ) + ':</div>';
							}

							infoHTML += '<div class="' + id + ' value">' + info[id].linkify( '_blank' ) + '</div></div>';
						}
					}

					if ( adminSettings.currThemeIsUser ) {
						type     = 'user';
						typeName = UrvanovSyntaxHighlighterThemeEditorStrings.userTheme;
					} else {
						type     = 'stock';
						typeName = UrvanovSyntaxHighlighterThemeEditorStrings.stockTheme;
					}

					infoHTML = '<div class="type ' + type + '">' + typeName + '</div><div class="content">' + infoHTML + '</div>';
					themeInfo.html( infoHTML );

					// Disable for stock themes.
					disabled = ! adminSettings.currThemeIsUser && ! settings.debug;

					themeEditorEditButton.attr( 'disabled', disabled );
					themeEditorDeleteButton.attr( 'disabled', disabled );
					themeEditorSubmitButton.attr( 'disabled', disabled );

					if ( callback ) {
						callback();
					}
				}
			);
		};

		base.show_theme_editor = function( button, editing ) {
			var nonce;

			if ( themeEditorEditButton.attr( 'disabled' ) ) {
				return false;
			}

			base.refresh_themeInfo();
			button.html( button.attr( 'loading' ) );
			adminSettings.editing_theme = editing;
			themeEditorLoading          = true;
			nonce                       = themeEditorEditButton.data( 'nonce' );

			// Load theme editor.
			UrvanovSyntaxHighlighterUtil.getAJAX(
				{
					action: 'urvanov-syntax-highlighter-theme-editor',
					curr_theme: adminSettings.currTheme,
					editing: editing,
					nonce: nonce,
				},
				function( data ) {
					themeEditorWrap.html( data );

					// Load preview into editor.
					if ( themeEditorLoading ) {
						UrvanovSyntaxHighlighterThemeEditor.init();
					}

					UrvanovSyntaxHighlighterThemeEditor.show(
						function() {
							base.show_theme_editor_now( button );
						},
						previewInner
					);
				}
			);

			return false;
		};

		base.resetPreview = function() {
			previewWrapper.append( previewInner );
			UrvanovSyntaxHighlighterThemeEditor.removeStyle();
		};

		base.show_theme_editor_now = function( button ) {
			mainWrap.hide();
			themeEditorWrap.show();
			themeEditorLoading = false;
			button.html( button.attr( 'loaded' ) );
		};

		// JQUERY UI DIALOGS.
		base.createAlert = function( args ) {
			args = $.extend(
				{
					title: strings.alert,
					options: {
						buttons: {
							OK: function() {
								$( this ).urvanovSyntaxHighlighterDialog( 'close' );
							},
						},
					},
				},
				args
			);

			base.createDialog( args );
		};

		base.createDialog = function( args, options ) {
			var defaultArgs = {
				yesLabel: strings.yes,
				noLabel: strings.no,
				title: strings.confirm,
			};

			args = $.extend( defaultArgs, args );

			options = $.extend(
				{
					modal: true, title: args.title, zIndex: 10000, autoOpen: true,
					width: 'auto', resizable: false,
					buttons: {},
					dialogClass: 'wp-dialog',
					selectedButtonIndex: 1, // starts from 1.
					close: function() {
						$( this ).remove();
					},
				},
				options
			);

			options.buttons[args.yesLabel] = function() {
				if ( args.yes ) {
					args.yes();
				}

				$( this ).urvanovSyntaxHighlighterDialog( 'close' );
			};

			options.buttons[args.noLabel] = function() {
				if ( args.no ) {
					args.no();
				}

				$( this ).urvanovSyntaxHighlighterDialog( 'close' );
			};

			options      = $.extend( options, args.options );
			options.open = function() {
				$( '.ui-button' ).addClass( 'button-primary' );
				$( this ).parent().find( 'button:nth-child(' + options.selectedButtonIndex + ')' ).trigger( 'focus' );
				if ( args.options.open ) {
					args.options.open();
				}
			};

			$( '<div></div>' ).appendTo( 'body' ).html( args.html ).urvanovSyntaxHighlighterDialog( options );

			// Can be modified afterwards.
			return args;
		};
	}();
} )( jQueryUrvanovSyntaxHighlighter );

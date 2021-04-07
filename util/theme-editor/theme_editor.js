/* global jQueryUrvanovSyntaxHighlighter, CSSJSON, UrvanovSyntaxHighlighterUtil, UrvanovSyntaxHighlighterAdminSettings, UrvanovSyntaxHighlighterThemeEditorSettings, UrvanovSyntaxHighlighterThemeEditorStrings, UrvanovSyntaxHighlighterAdminStrings, UrvanovSyntaxHighlighterAdmin */

// Urvanov Syntax Highlighter Theme Editor JavaScript.
( function( $ ) {
	var showMain;
	var UrvanovSyntaxHighlighterThemeEditor;

	UrvanovSyntaxHighlighterThemeEditor = new function() { // jshint ignore:line
		var base = this;

		var adminSettings = UrvanovSyntaxHighlighterAdminSettings;
		var settings      = UrvanovSyntaxHighlighterThemeEditorSettings;
		var strings       = UrvanovSyntaxHighlighterThemeEditorStrings;
		var adminStrings  = UrvanovSyntaxHighlighterAdminStrings;
		var admin         = UrvanovSyntaxHighlighterAdmin;

		var info = {};
		var preview, previewUrvanovSyntaxHighlighter, previewCSS, status, title;
		var colorPickerPos;
		var changed, loaded;
		var themeID, themeJSON, themeCSS, themeStr;

		var themeInfo   = {};
		var reImportant = /\s+!important$/gmi;
		var reSize      = /^[0-9-]+px$/;
		var reCopy      = /-copy(-\d+)?$/;
		var changedAttr = 'data-value';
		var borderCSS   = { border: true, 'border-left': true, 'border-right': true, 'border-top': true, 'border-bottom': true };

		base.init = function( callback ) {
			// Called only once.
			UrvanovSyntaxHighlighterUtil.log( 'editor init' );
			base.initUI();
			if ( callback ) {
				callback();
			}
		};

		base.show = function( callback, urvanovSyntaxHighlighter ) {
			// Called each time editor is shown.
			previewUrvanovSyntaxHighlighter = urvanovSyntaxHighlighter.find( '.urvanov-syntax-highlighter-syntax' );
			preview.append( urvanovSyntaxHighlighter );
			base.load();
			if ( callback ) {
				callback();
			}
		};

		base.load = function() {
			loaded    = false;
			themeStr  = adminSettings.currThemeCSS;
			themeID   = adminSettings.currTheme;
			changed   = false;
			themeJSON = CSSJSON.toJSON(
				themeStr,
				{
					stripComments: true,
					split: true,
				}
			);

			themeJSON = base.filterCSS( themeJSON );
			UrvanovSyntaxHighlighterUtil.log( themeJSON );
			themeInfo = base.readCSSInfo( themeStr );
			base.removeExistingCSS();
			base.initInfoUI();
			base.updateTitle();
			base.updateInfo();
			base.setFieldValues( themeInfo );
			base.populateAttributes();
			base.updateLiveCSS();
			base.updateUI();
			loaded = true;
		};

		base.save = function() {
			var names;
			var id;
			var newThemeStr;

			// Update info from form fields.
			themeInfo = base.getFieldValues( $.keys( themeInfo ) );

			// Get the names of the fields and map them to their values.
			names = base.getFieldNames( themeInfo );

			for ( id in themeInfo ) {
				info[names[id]] = themeInfo[id];
			}

			// Update attributes.
			base.persistAttributes();

			// Save.
			themeCSS    = CSSJSON.toCSS( themeJSON );
			newThemeStr = base.writeCSSInfo( info ) + themeCSS;

			UrvanovSyntaxHighlighterUtil.postAJAX(
				{
					action: 'urvanov-syntax-highlighter-theme-editor-save',
					id: themeID,
					name: base.getName(),
					css: newThemeStr,
				},
				function( result ) {
					status.show();

					result = parseInt( result );
					if ( result > 0 ) {
						status.html( strings.success );
						if ( result === 2 ) {
							window.GET['theme-editor'] = 1;
							UrvanovSyntaxHighlighterUtil.reload();
						}
					} else {
						status.html( strings.fail );
					}
					changed = false;
					setTimeout(
						function() {
							status.fadeOut();
						},
						1000
					);
				}
			);
		};

		base.del = function( id, name ) {
			admin.createDialog(
				{
					title: strings.del,
					html: strings.deleteThemeConfirm.replace( '%s', name ),
					yes: function() {
						UrvanovSyntaxHighlighterUtil.postAJAX(
							{
								action: 'urvanov-syntax-highlighter-theme-editor-delete',
								id: id,
							},
							function( result ) {
								if ( result > 0 ) {
									UrvanovSyntaxHighlighterUtil.reload();
								} else {
									admin.createAlert(
										{
											html: strings.deleteFail + ' ' + strings.checkLog,
										}
									);
								}
							}
						);
					},
					options: {
						selectedButtonIndex: 2,
					},
				}
			);
		};

		base.duplicate = function( id ) {
			base.createPrompt(
				{
					title: strings.duplicate,
					text: strings.newName,
					value: base.getNextAvailableName( id ),
					ok: function( val ) {
						UrvanovSyntaxHighlighterUtil.postAJAX(
							{
								action: 'urvanov-syntax-highlighter-theme-editor-duplicate',
								id: id,
								name: val,
							},
							function( result ) {
								if ( result > 0 ) {
									UrvanovSyntaxHighlighterUtil.reload();
								} else {
									admin.createAlert(
										{
											html: strings.duplicateFail + ' ' + strings.checkLog,
										}
									);
								}
							}
						);
					},
				}
			);
		};

		base.submit = function( id ) {
			base.createPrompt(
				{
					title: strings.submit,
					desc: strings.submitText,
					text: strings.message,
					value: strings.submitMessage,
					ok: function( val ) {
						UrvanovSyntaxHighlighterUtil.postAJAX(
							{
								action: 'urvanov-syntax-highlighter-theme-editor-submit',
								id: id,
								message: val,
							},
							function( result ) {
								var msg = result > 0 ? strings.submitSucceed : strings.submitFail + ' ' + strings.checkLog;
								admin.createAlert(
									{
										html: msg,
									}
								);
							}
						);
					},
				}
			);
		};

		base.getNextAvailableName = function( id ) {
			var next = base.getNextAvailableID( id );
			return base.idToName( next[1] );
		};

		base.getNextAvailableID = function( id ) {
			var themes = adminSettings.themes;
			var count  = 0;
			var newID;
			var nextID;

			if ( reCopy.test( id ) ) {
				// Remove the "copy" if it already exists.
				newID = id.replace( reCopy, '' );
				if ( newID.length > 0 ) {
					id = newID;
				}
			}

			nextID = id;
			while ( nextID in themes ) {
				count++;
				if ( 1 === count ) {
					nextID = id + '-copy';
				} else {
					nextID = id + '-copy-' + count.toString();
				}
			}

			return [ count, nextID ];
		};

		base.readCSSInfo = function( cssStr ) {
			var infoStr   = /^\s*\/\*[\s\S]*?\*\//gmi.exec( cssStr );
			var match     = null;
			var infoRegex = /([^\r\n:]*[^\r\n\s:])\s*:\s*([^\r\n]+)/gmi;

			while ( null !== ( match = infoRegex.exec( infoStr ) ) ) {
				themeInfo[base.nameToID( match[1] )] = UrvanovSyntaxHighlighterUtil.encode_html( match[2] );
			}

			// Force title case on the name.
			if ( themeInfo.name ) {
				themeInfo.name = base.idToName( themeInfo.name );
			}

			return themeInfo;
		};

		base.getFieldName = function( id ) {
			var name = '';

			if ( id in settings.fields ) {
				name = settings.fields[id];
			} else {
				name = base.idToName( id );
			}

			return name;
		};

		base.getFieldNames = function( fields ) {
			var names = {};
			var id;

			for ( id in fields ) {
				names[id] = base.getFieldName( id );
			}

			return names;
		};

		base.removeExistingCSS = function() {
			// Remove the old <style> tag to prevent clashes.
			preview.find( 'link[rel="stylesheet"][href*="' + adminSettings.currThemeURL + '"]' ).remove();
		};

		base.initInfoUI = function() {
			var names;
			var fields = {};
			var id;
			var name;
			var value;

			UrvanovSyntaxHighlighterUtil.log( themeInfo );

			// TODO abstract.
			names = base.getFieldNames( themeInfo );

			for ( id in names ) {
				name         = names[id];
				value        = themeInfo[id];
				fields[name] = base.createInput( id, value );
			}

			$( '#tabs-1-contents' ).html( base.createForm( fields ) );

			base.getField( 'name' ).on(
				'change keydown keyup',
				function() {
					themeInfo.name = base.getFieldValue( 'name' );
					base.updateTitle();
				}
			);
		};

		base.nameToID = function( name ) {
			return name.toLowerCase().replace( /\s+/gmi, '-' );
		};

		base.idToName = function( id ) {
			id = id.replace( /-/gmi, ' ' );

			return id.toTitleCase();
		};

		base.getName = function() {
			var name = themeInfo.name;

			if ( ! name ) {
				name = base.idToName( themeID );
			}

			return name;
		};

		base.getField = function( id ) {
			return $( '#' + settings.cssInputPrefix + id );
		};

		base.getFieldValue = function( id ) {
			return base.getElemValue( base.getField( id ) );
		};

		base.getElemValue = function( elem ) {
			if ( elem ) {
				// TODO add support for checkboxes etc.
				return elem.val();
			}
			return null;
		};

		base.getFieldValues = function( fields ) {
			$( fields ).each(
				function( i, id ) {
					info[id] = base.getFieldValue( id );
				}
			);

			return info;
		};

		base.setFieldValue = function( id, value ) {
			base.setElemValue( base.getField( id ), value );
		};

		base.setFieldValues = function( obj ) {
			var i;

			for ( i in obj ) {
				base.setFieldValue( i, obj[i] );
			}
		};

		base.setElemValue = function( elem, val ) {
			if ( elem ) {
				// TODO add support for checkboxes etc.
				return elem.val( val );
			}

			return false;
		};

		base.getAttribute = function( element, attribute ) {
			return base.getField( element + '_' + attribute );
		};

		base.getAttributes = function() {
			return $( '.' + settings.cssInputPrefix + settings.attribute );
		};

		base.visitAttribute = function( attr, callback ) {
			var elems    = themeJSON.children;
			var root     = settings.cssThemePrefix + base.nameToID( themeInfo.name );
			var dataElem = attr.attr( 'data-element' );
			var dataAttr = attr.attr( 'data-attribute' );
			var elem     = elems[root + dataElem];

			callback( attr, elem, dataElem, dataAttr, root, elems );
		};

		base.persistAttributes = function( removeDefault ) {
			removeDefault = UrvanovSyntaxHighlighterUtil.setDefault( removeDefault, true );

			base.getAttributes().each(
				function() {
					base.persistAttribute( $( this ), removeDefault );
				}
			);
		};

		base.persistAttribute = function( attr, removeDefault ) {
			var val;

			removeDefault = UrvanovSyntaxHighlighterUtil.setDefault( removeDefault, true );

			base.visitAttribute(
				attr,
				function( attrib, elem, dataElem, dataAttr, root, elems ) {
					if ( removeDefault && 'SELECT' === attrib.prop( 'tagName' ) && attrib.val() === attrib.attr( 'data-default' ) ) {
						if ( elem ) {
							// If default is selected in a dropdown, then remove.
							delete elem.attributes[dataAttr];
						}

						return;
					}

					val = base.getElemValue( attrib );

					if ( ( null === val || '' === val ) ) {
						// No value given.
						if ( removeDefault && elem ) {
							delete elem.attributes[dataAttr];

							return;
						}
					} else {
						val = base.addImportant( val );

						if ( ! elem ) {
							elem = elems[root + dataElem] = {
								attributes: {},
								children: {},
							};
						}

						elem.attributes[dataAttr] = val;
					}

					UrvanovSyntaxHighlighterUtil.log( dataElem + ' ' + dataAttr );
				}
			);
		};

		base.populateAttributes = function() {
			var elems = themeJSON.children;
			var root  = settings.cssThemePrefix + base.nameToID( themeInfo.name );

			UrvanovSyntaxHighlighterUtil.log( elems, root );
			base.getAttributes().each(
				function() {
					var val;

					base.visitAttribute(
						$( this ),
						function( attr, elem, dataElem, dataAttr ) {
							if ( elem ) {
								if ( dataAttr in elem.attributes ) {
									val = base.removeImportant( elem.attributes[dataAttr] );
									base.setElemValue( attr, val );
									attr.trigger( 'change' );
								}
							}
						}
					);
				}
			);
		};

		base.addImportant = function( attr ) {
			if ( ! reImportant.test( attr ) ) {
				attr = attr + ' !important';
			}

			return attr;
		};

		base.removeImportant = function( attr ) {
			return attr.replace( reImportant, '' );
		};

		base.isImportant = function( attr ) {
			return null !== reImportant.exec( attr );
		};

		base.appendStyle = function( css ) {
			previewCSS.html( '<style>' + css + '</style>' );
		};

		base.removeStyle = function() {
			previewCSS.html( '' );
		};

		base.writeCSSInfo = function( inform ) {
			var infoStr = '/*\n';
			var field;

			for ( field in inform ) {
				infoStr += field + ': ' + inform[field] + '\n';
			}

			return infoStr + '*/\n';
		};

		base.filterCSS = function( css ) {
			var child;
			var atts;
			var att;
			var rules;
			var rule;

			// Split all border CSS attributes into individual attributes.
			for ( child in css.children ) {
				atts = css.children[child].attributes;

				for ( att in atts ) {
					if ( att in borderCSS ) {
						rules = base.getBorderCSS( atts[att] );

						for ( rule in rules ) {
							atts[att + '-' + rule] = rules[rule];
						}
						delete atts[att];
					}
				}
			}

			return css;
		},

		base.getBorderCSS = function( css ) {
			var result    = {};
			var important = base.isImportant( css );
			var width;
			var color;
			var rule;

			$.each(
				strings.borderStyles,
				function( i, style ) {
					if ( css.indexOf( style ) >= 0 ) {
						result.style = style;
					}
				}
			);

			width = /\d+\s*(px|%|em|rem)/gi.exec( css );
			if ( width ) {
				result.width = width[0];
			}

			color = /#\w+/gi.exec( css );
			if ( color ) {
				result.color = color[0];
			}

			if ( important ) {
				for ( rule in result ) {
					result[rule] = base.addImportant( result[rule] );
				}
			}

			return result;
		},

		base.createPrompt = function( args ) {
			var options;

			args = $.extend(
				{
					title: adminStrings.prompt,
					text: adminStrings.value,
					desc: null,
					value: '',
					options: {
						buttons: {
							OK: function() {
								if ( args.ok ) {
									args.ok( base.getFieldValue( 'prompt-text' ) );
								}

								$( this ).urvanovSyntaxHighlighterDialog( 'close' );
							},
							Cancel: function() {
								$( this ).urvanovSyntaxHighlighterDialog( 'close' );
							},
						},
						open: function() {
							base.getField( 'prompt-text' ).val( args.value ).focus();
						},
					},
				},
				args
			);

			args.html = '<table class="field-table urvanov-syntax-highlighter-prompt-' + base.nameToID( args.title ) + '">';
			if ( args.desc ) {
				args.html += '<tr><td colspan="2">' + args.desc + '</td></tr>';
			}

			args.html += '<tr><td>' + args.text + ':</td><td>' + base.createInput( 'prompt-text' ) + '</td></tr>';
			args.html += '</table>';
			options    = { width: '400px' };

			admin.createDialog( args, options );
		};

		base.initUI = function() {
			// Bind events.
			preview    = $( '#urvanov-syntax-highlighter-editor-preview' );
			previewCSS = $( '#urvanov-syntax-highlighter-editor-preview-css' );
			status     = $( '#urvanov-syntax-highlighter-editor-status' );
			title      = $( '#urvanov-syntax-highlighter-theme-editor-name' );
			info       = $( '#urvanov-syntax-highlighter-theme-editor-info' );

			$( '#urvanov-syntax-highlighter-editor-controls' ).tabs();
			$( '#urvanov-syntax-highlighter-editor-back' ).on(
				'click',
				function() {
					if ( changed ) {
						admin.createDialog(
							{
								html: strings.discardConfirm,
								title: adminStrings.confirm,
								yes: function() {
									showMain();
								},
							}
						);
					} else {
						showMain();
					}
				}
			);

			$( '#urvanov-syntax-highlighter-editor-save' ).on( 'click', base.save );

			// Set up jQuery UI.
			base.getAttributes().each(
				function() {
					var attr = $( this );
					var type = attr.attr( 'data-group' );
					var args;

					if ( 'color' === type ) {
						args = {
							parts: 'full',
							showNoneButton: true,
							colorFormat: '#HEX',
						};

						args.open = function() {
							var picker;

							$( '.ui-colorpicker-dialog .ui-button' ).addClass( 'button-primary' );
							if ( colorPickerPos ) {
								picker = $( '.ui-colorpicker-dialog:visible' );
								picker.css( 'left', colorPickerPos.left );
							}
						};

						args.select = function() {
							attr.trigger( 'change' );
						};

						args.close = function() {
							attr.trigger( 'change' );
						};

						attr.colorpicker( args );
						attr.on(
							'change',
							function() {
								var hex = attr.val();
								attr.css( 'background-color', hex );
								attr.css( 'color', UrvanovSyntaxHighlighterUtil.getReadableColor( hex ) );
							}
						);
					} else if ( 'size' === type ) {
						attr.on(
							'change',
							function() {
								var val = attr.val();
								if ( ! reSize.test( val ) ) {
									val = UrvanovSyntaxHighlighterUtil.removeChars( '^0-9-', val );
									if ( '' !== val ) {
										attr.val( val + 'px' );
									}
								}
							}
						);
					}
					if ( 'color' !== type ) {
						// For regular text boxes, capture changes on keys.
						attr.on(
							'keydown keyup',
							function() {
								if ( attr.val() !== attr.attr( changedAttr ) ) {
									UrvanovSyntaxHighlighterUtil.log( 'triggering', attr.attr( changedAttr ), attr.val() );
									attr.trigger( 'change' );
								}
							}
						);
					}

					// Update CSS changes to the live instance.
					attr.on(
						'change',
						function() {
							if ( attr.attr( changedAttr ) === attr.val() ) {
								return;
							}

							attr.attr( changedAttr, attr.val() );

							if ( loaded ) {
								base.persistAttribute( attr );
								base.updateLiveCSS();
							}
						}
					);
				}
			);

			$( '.ui-colorpicker-dialog' ).addClass( 'wp-dialog' );
			$( '.ui-colorpicker-dialog' ).on(
				'mouseup',
				function() {
					base.colorPickerMove( $( this ) );
				}
			);
		};

		base.colorPickerMove = function( picker ) {
			if ( picker ) {
				colorPickerPos = { left: picker.css( 'left' ), top: picker.css( 'top' ) };
			}
		};

		base.updateLiveCSS = function( clone ) {
			var json;
			var id;

			clone = UrvanovSyntaxHighlighterUtil.setDefault( clone, false );

			if ( previewUrvanovSyntaxHighlighter ) {
				if ( clone ) {
					id   = previewUrvanovSyntaxHighlighter.attr( 'id' );
					json = $.extend( true, {}, themeJSON );

					$.each(
						json.children,
						function( child ) {
							json.children['#' + id + child] = json.children[child];
							delete json.children[child];
						}
					);
				} else {
					json = themeJSON;
				}

				base.appendStyle( CSSJSON.toCSS( json ) );
			}
		};

		base.updateUI = function() {
			$( '#urvanov-syntax-highlighter-editor-controls input, #urvanov-syntax-highlighter-editor-controls select' ).on(
				'change',
				function() {
					changed = true;
				}
			);
		};

		base.createInput = function( id, value, type ) {
			value = UrvanovSyntaxHighlighterUtil.setDefault( value, '' );
			type  = UrvanovSyntaxHighlighterUtil.setDefault( type, 'text' );

			return '<input id="' + settings.cssInputPrefix + id + '" class="' + settings.cssInputPrefix + type + '" type="' + type + '" value="' + value + '" />';
		};

		base.createForm = function( inputs ) {
			var str = '<form class="' + settings.prefix + '-form"><table>';
			$.each(
				inputs,
				function( input ) {
					str += '<tr><td class="field">' + input + '</td><td class="value">' + inputs[input] + '</td></tr>';
				}
			);

			str += '</table></form>';

			return str;
		};

		showMain = function() {
			admin.resetPreview();
			admin.preview_update();
			admin.show_theme_info();
			admin.show_main();
		};

		base.updateTitle = function() {
			var name = base.getName();

			if ( adminSettings.editing_theme ) {
				title.html( strings.editingTheme.replace( '%s', name ) );
			} else {
				title.html( strings.creatingTheme.replace( '%s', name ) );
			}
		};

		base.updateInfo = function() {
			info.html( '<a target="_blank" href="' + adminSettings.currThemeURL + '">' + adminSettings.currThemeURL + '</a>' );
		};
	}();
} )( jQueryUrvanovSyntaxHighlighter );

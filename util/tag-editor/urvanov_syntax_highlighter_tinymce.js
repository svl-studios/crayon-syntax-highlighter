/* global tinymce, UrvanovSyntaxHighlighterTagEditorSettings, UrvanovSyntaxHighlighterTagEditor, jQueryUrvanovSyntaxHighlighter, switchEditors */

( function( $ ) {
	window.UrvanovSyntaxHighlighterTinyMCE = new function() {
		// TinyMCE specific.
		var s;
		var name    = 'urvanov_syntax_highlighter_tinymce';
		var te      = null;
		var currPre = null;
		var isInit  = false;

		// Switch events.
		var base = this;

		base.setHighlight = function( highlight ) {
			$( s.tinymce_button ).closest( s.tinymce_button_generic ).toggleClass( s.tinymce_highlight, highlight );
		};

		base.selectPreCSS = function( selected ) {
			if ( currPre ) {
				if ( selected ) {
					$( currPre ).addClass( s.css_selected );
				} else {
					$( currPre ).removeClass( s.css_selected );
				}
			}
		};

		base.isPreSelectedCSS = function() {
			if ( currPre ) {
				return $( currPre ).hasClass( s.css_selected );
			}
			return false;
		};

		base.loadTinyMCE = function() {
			var version = parseInt( tinymce.majorVersion );
			if ( ! isNaN( version ) && version <= 3 ) {
				return this._loadTinyMCEv3();
			}

			s  = UrvanovSyntaxHighlighterTagEditorSettings;
			te = UrvanovSyntaxHighlighterTagEditor;

			// TODO(aramk) find the TinyMCE version 4 compliant command for this.
			// tinymce.PluginManager.requireLangPack(name);.

			tinymce.PluginManager.add(
				name,
				function( ed, url ) {
					var switchHtml;

					// TODO(aramk) This is called twice for some reason.
					ed.on(
						'init',
						function() {
							ed.dom.loadCSS( url + '/urvanov_syntax_highlighter_te.css' );

							if ( isInit ) {
								return;
							}

							$( s.tinymce_button ).parent().addClass( s.tinymce_button_unique );
							UrvanovSyntaxHighlighterTagEditor.bind( '.' + s.tinymce_button_unique );

							// Remove all selected pre tags.
							$( '.' + s.css_selected, ed.getContent() ).removeClass( s.css_selected );
							isInit = true;
						}
					);

					// Prevent <p> on enter, turn into \n.
					ed.on(
						'keyDown',
						function( e ) {
							var selection = ed.selection;
							var node;

							if ( 13 === e.keyCode ) {
								node = selection.getNode();

								if ( 'PRE' === node.nodeName ) {
									selection.setContent( '\n', { format: 'raw' } );
									return tinymce.dom.Event.cancel( e );
								} else if ( te.isUrvanovSyntaxHighlighter( node ) ) {
									// Only triggers for inline <span>, ignore enter in inline.
									return tinymce.dom.Event.cancel( e );
								}
							}
						}
					);

					// Remove onclick and call ourselves.
					switchHtml = $( s.switchHtml );
					switchHtml.prop( 'onclick', null );
					switchHtml.on(
						'click',
						function() {
							// Remove selected pre class when switching to HTML editor.
							base.selectPreCSS( false );
							switchEditors.go( 'content', 'html' );
						}
					);

					// Highlight selected.
					ed.on(
						'nodeChange',
						function( event ) {
							var n = event.element;
							if ( n !== currPre ) {
								// We only care if we select another same object.
								if ( currPre ) {
									// If we have a previous pre, remove it.
									base.selectPreCSS( false );
									currPre = null;
								}
								if ( te.isUrvanovSyntaxHighlighter( n ) ) {
									// Add new pre.
									currPre = n;
									base.selectPreCSS( true );
									base.setHighlight( true );
								} else {
									// No pre selected.
									base.setHighlight( false );
								}
							}
						}
					);

					ed.addButton(
						name,
						{
							// TODO add translation.
							title: s.dialog_title_add,
							onclick: function() {
								te.showDialog(
									{
										insert: function( shortcode ) {
											ed.execCommand( 'mceInsertContent', 0, shortcode );
										},
										edit: function( shortcode ) {
											// This will change the currPre object.
											var newPre = $( shortcode );
											$( currPre ).replaceWith( newPre );

											// XXX DOM element not jQuery.
											currPre = newPre[0];
										},
										select: function() {
											return ed.selection.getContent( { format: 'text' } );
										},

										editor_str: 'tinymce',
										ed: ed,
										node: currPre,
										input: 'decode',
										output: 'encode',
									}
								);
							},
						}
					);
				}
			);
		};

		// TinyMCE v3 - deprecated.
		base._loadTinyMCEv3 = function() {
			s  = UrvanovSyntaxHighlighterTagEditorSettings;
			te = UrvanovSyntaxHighlighterTagEditor;

			tinymce.PluginManager.requireLangPack( name );

			tinymce.create(
				'tinymce.plugins.Crayon',
				{
					init: function( ed, url ) {
						var switchHtml;

						ed.onInit.add(
							function( editor ) {
								editor.dom.loadCSS( url + '/urvanov_syntax_highlighter_te.css' );
							}
						);

						// Prevent <p> on enter, turn into \n.
						ed.onKeyDown.add(
							function( editor, e ) {
								var node;
								var selection = editor.selection;

								if ( 13 === e.keyCode ) {
									node = selection.getNode();

									if ( 'PRE' === node.nodeName ) {
										selection.setContent( '\n', { format: 'raw' } );
										return tinymce.dom.Event.cancel( e );
									} else if ( te.isUrvanovSyntaxHighlighter( node ) ) {
										// Only triggers for inline <span>, ignore enter in inline.
										return tinymce.dom.Event.cancel( e );
									}
								}
							}
						);

						ed.onInit.add(
							function() {
								UrvanovSyntaxHighlighterTagEditor.bind( s.tinymce_button );
							}
						);

						ed.addCommand(
							'showCrayon',
							function() {
								te.showDialog(
									{
										insert: function( shortcode ) {
											ed.execCommand( 'mceInsertContent', 0, shortcode );
										},
										edit: function( shortcode ) {
											// This will change the currPre object.
											var newPre = $( shortcode );
											$( currPre ).replaceWith( newPre );

											// XXX DOM element not jQuery.
											currPre = newPre[0];
										},
										select: function() {
											return ed.selection.getContent( { format: 'text' } );
										},

										editor_str: 'tinymce',
										ed: ed,
										node: currPre,
										input: 'decode',
										output: 'encode',
									}
								);
							}
						);

						// Remove onclick and call ourselves.
						switchHtml = $( s.switchHtml );
						switchHtml.prop( 'onclick', null );
						switchHtml.on(
							'click',
							function() {
								// Remove selected pre class when switching to HTML editor.
								base.selectPreCSS( false );
								switchEditors.go( 'content', 'html' );
							}
						);

						// Highlight selected.
						ed.onNodeChange.add(
							function( editor, cm, n ) {
								if ( n !== currPre ) {
									// We only care if we select another same object.
									if ( currPre ) {
										// If we have a previous pre, remove it.
										base.selectPreCSS( false );
										currPre = null;
									}

									if ( te.isUrvanovSyntaxHighlighter( n ) ) {
										// Add new pre.
										currPre = n;
										base.selectPreCSS( true );
										base.setHighlight( true );
									} else {
										// No pre selected.
										base.setHighlight( false );
									}
								}
							}
						);

						ed.onBeforeSetContent.add(
							function( editor, o ) {
								// Remove all selected pre tags.
								var content = $( o.content );
								var wrapper = $( '<div>' );
								content.each(
									function() {
										$( this ).removeClass( s.css_selected );
										wrapper.append( $( this ).clone() );
									}
								);

								o.content = wrapper.html();
							}
						);

						ed.addButton(
							name,
							{
								// TODO add translation.
								title: s.dialog_title,
								cmd: 'showCrayon',
							}
						);
					},
					createControl: function() {
						return null;
					},
					getInfo: function() {
						return {
							longname: 'Urvanov Syntax Highlighter',
							author: 'Fedor Urvanov & Aram Kocharyan',
							authorurl: 'https://urvanov.ru/',
							infourl: 'https://github.com/urvanov-ru/crayon-syntax-highlighter',
							version: '1.0',
						};
					},
				}
			);

			tinymce.PluginManager.add( name, tinymce.plugins.Crayon );
		};
	};

	UrvanovSyntaxHighlighterTinyMCE.loadTinyMCE();
} )( jQueryUrvanovSyntaxHighlighter );

/* global jQueryUrvanovSyntaxHighlighter, UrvanovSyntaxHighlighterTagEditorSettings, UrvanovSyntaxHighlighterTagEditor, QTags */

( function( $ ) {
	var settings = UrvanovSyntaxHighlighterTagEditorSettings;
	// var urvanovSyntaxHighlighterQuickTags;

	window.urvanovSyntaxHighlighterQuickTags = new function() {
		var base = this;

		base.init = function() {
			var buttonText;
			var qtUrvanovSyntaxHighlighter;
			var findQtUrvanovSyntaxHighlighter;

			base.sel   = '*[id*="urvanov_syntax_highlighter_quicktag"],*[class*="urvanov_syntax_highlighter_quicktag"]';
			buttonText = settings.quicktag_text;
			buttonText = undefined !== buttonText ? buttonText : 'urvanov_syntax_highlighter';

			QTags.addButton(
				'urvanov_syntax_highlighter_quicktag',
				buttonText,
				function( el, canvas ) {
					UrvanovSyntaxHighlighterTagEditor.showDialog(
						{
							insert: function( shortcode ) {
								QTags.insertContent( shortcode );
							},
							select: base.getSelectedText( canvas ),
							editor_str: 'html',
							output: 'encode',
						}
					);

					$( base.sel ).removeClass( 'qtUrvanovSyntaxHighlighter_highlight' );
				}
			);

			findQtUrvanovSyntaxHighlighter = setInterval(
				function() {
					qtUrvanovSyntaxHighlighter = $( base.sel ).first();
					if ( typeof qtUrvanovSyntaxHighlighter !== 'undefined' ) {
						UrvanovSyntaxHighlighterTagEditor.bind( base.sel );
						clearInterval( findQtUrvanovSyntaxHighlighter );
					}
				},
				100
			);
		};

		base.getSelectedText = function( canvas ) {
			var startPos;
			var endPos;

			if ( '' === canvas ) {
				return null;
			}

			startPos = canvas.selectionStart;
			endPos   = canvas.selectionEnd;

			return canvas.value.substring( startPos, endPos );
		};
	};

	$( document ).ready(
		function() {
			urvanovSyntaxHighlighterQuickTags.init();
		}
	);
} )( jQueryUrvanovSyntaxHighlighter );

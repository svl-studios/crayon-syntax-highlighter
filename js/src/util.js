/* global jQuery, UrvanovSyntaxHighlighterSyntaxSettings, tinycolor */

// To avoid duplicates conflicting.
var jQueryUrvanovSyntaxHighlighter = jQuery;

( function( $ ) {
	// eslint-disable-next-line no-undef
	UrvanovSyntaxHighlighterUtil = new function() { // jshint ignore:line
		var base     = this;
		var settings = null;

		base.init = function() {
			settings = UrvanovSyntaxHighlighterSyntaxSettings;
			base.initGET();
		};

		base.addPrefixToID = function( id ) {
			return id.replace( /^([#.])?(.*)$/, '$1' + settings.prefix + '$2' );
		};

		base.removePrefixFromID = function( id ) {
			var re = new RegExp( '^[#.]?' + settings.prefix, 'i' );
			return id.replace( re, '' );
		};

		base.cssElem = function( id ) {
			return $( base.addPrefixToID( id ) );
		};

		base.setDefault = function( v, d ) {
			return ( typeof v === 'undefined' ) ? d : v;
		};

		base.setMax = function( v, max ) {
			return v <= max ? v : max;
		};

		base.setMin = function( v, min ) {
			return v >= min ? v : min;
		};

		base.setRange = function( v, min, max ) {
			return base.setMax( base.setMin( v, min ), max );
		};

		base.getExt = function( str ) {
			var ext;

			if ( -1 === str.indexOf( '.' ) ) {
				return undefined;
			}

			ext = str.split( '.' );

			if ( ext.length ) {
				ext = ext[ext.length - 1];
			} else {
				ext = '';
			}

			return ext;
		};

		base.initGET = function() {
			// URLs.
			window.currentURL = window.location.protocol + '//' + window.location.host + window.location.pathname;
			window.currentDir = window.currentURL.substring( 0, window.currentURL.lastIndexOf( '/' ) );

			// http://stackoverflow.com/questions/439463.
			function getQueryParams( qs ) {
				var params = {};
				var tokens;
				var re = /[?&]?([^=]+)=([^&]*)/g;

				qs = qs.split( '+' ).join( ' ' );

				// eslint-disable-next-line no-cond-assign
				while ( tokens = re.exec( qs ) ) {
					params[decodeURIComponent( tokens[1] )] = decodeURIComponent( tokens[2] );
				}

				return params;
			}

			window.GET = getQueryParams( document.location.search );
		};

		base.getAJAX = function( args, callback ) {
			args.version = settings.version;
			$.get( settings.ajaxurl, args, callback );
		};

		/**
		 * HTML to elements.
		 *
		 * @param {string} html representing any number of sibling elements
		 * @return {NodeList} Node list.
		 */
		base.htmlToElements = function( html ) {
			return $.parseHTML( html, document, true );
		};

		base.postAJAX = function( args, callback ) {
			args.version = settings.version;
			$.post( settings.ajaxurl, args, callback );
		};

		base.reload = function() {
			var get = '?';
			var i;

			for ( i in window.GET ) {
				get += i + '=' + window.GET[i] + '&';
			}
			window.location = window.currentURL + get;
		};

		base.escape = function( string ) {
			if ( typeof encodeURIComponent === 'function' ) {
				return encodeURIComponent( string );
			} else if ( typeof escape !== 'function' ) {
				return escape( string ); // jshint ignore:line
			}

			return string;
		};

		base.log = function( string ) {
			if ( typeof console !== 'undefined' && settings.debug ) {
				// eslint-disable-next-line no-console
				console.log( string );
			}
		};

		base.decode_html = function( str ) {
			return String( str ).replace( /&lt;/g, '<' ).replace( /&gt;/g, '>' ).replace( /&amp;/g, '&' );
		};

		base.encode_html = function( str ) {
			return String( str ).replace( /&/g, '&amp;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' );
		};

		/**
		 * Returns either black or white to ensure this color is distinguishable with the given RGB hex.
		 * This function can be used to create a readable foreground color given a background color, or vice versa.
		 * It forms a radius around white where black is returned. Outside this radius, white is returned.
		 *
		 * @param {string} hex An RGB hex (e.g. "#FFFFFF")
		 * @requires jQuery and TinyColor
		 * @param {Array} args The argument object. Properties:
		 *      amount: a value in the range [0,1]. If the distance of the given hex from white exceeds this value,
		 *          white is returned. Otherwise, black is returned.
		 *      xMulti: a multiplier to the distance in the x-axis.
		 *      yMulti: a multiplier to the distance in the y-axis.
		 *      normalizeHue: either falsey or an [x,y] array range. If hex is a colour with hue in this range,
		 *          then normalizeHueXMulti and normalizeHueYMulti are applied.
		 *      normalizeHueXMulti: a multiplier to the distance in the x-axis if hue is normalized.
		 *      normalizeHueYMulti: a multiplier to the distance in the y-axis if hue is normalized.
		 * @return {string} the RGB hex string of black or white.
		 */
		base.getReadableColor = function( hex, args ) {
			var color = tinycolor( hex );
			var hsv   = color.toHsv();

			// Origin is white.
			var coord = { x: hsv.s, y: 1 - hsv.v };
			var dist;

			args = $.extend(
				{
					amount: 0.5,
					xMulti: 1,
					// We want to achieve white a bit sooner in the y axis.
					yMulti: 1.5,
					normalizeHue: [ 20, 180 ],
					// For colors that appear lighter (yellow, green, light blue) we reduce the distance in the x direction,
					// stretching the radius in the x axis allowing more black than before.
					normalizeHueXMulti: 1 / 2.5,
					normalizeHueYMulti: 1,
				},
				args
			);

			// Multipliers.
			coord.x *= args.xMulti;
			coord.y *= args.yMulti;
			if ( args.normalizeHue && hsv.h > args.normalizeHue[0] && hsv.h < args.normalizeHue[1] ) {
				coord.x *= args.normalizeHueXMulti;
				coord.y *= args.normalizeHueYMulti;
			}

			dist = Math.sqrt( Math.pow( coord.x, 2 ) + Math.pow( coord.y, 2 ) );

			if ( dist < args.amount ) {
				hsv.v = 0; // black.
			} else {
				hsv.v = 1; // white.
			}

			hsv.s = 0;

			return tinycolor( hsv ).toHexString();
		};

		base.removeChars = function( chars, str ) {
			var re = new RegExp( '[' + chars + ']', 'gmi' );
			return str.replace( re, '' );
		};
	}();

	$( document ).ready(
		function() {
			// eslint-disable-next-line no-undef
			UrvanovSyntaxHighlighterUtil.init();
		}
	);

	// http://stackoverflow.com/questions/2360655/jquery-event-handlers-always-execute-in-order-they-were-bound-any-way-around-t.

	// [name] is the name of the event "click", "mouseover", ..
	// same as you'd pass it to bind()
	// [fn] is the handler function
	$.fn.bindFirst = function( name, fn ) {
		var handlers;
		var handler;

		// bind as you normally would
		// don't want to miss out on any jQuery magic.
		this.on( name, fn );

		// Thanks to a comment by @Martin, adding support for
		// namespaced events too.
		handlers = this.data( 'events' )[name.split( '.' )[0]];

		// take out the handler we just inserted from the end.
		handler = handlers.pop();

		// move it at the beginning.
		handlers.splice( 0, 0, handler );
	};

	// http://stackoverflow.com/questions/4079274/how-to-get-an-objects-properties-in-javascript-jquery.
	$.keys = function( obj ) {
		var keys = [];
		var key;

		for ( key in obj ) {
			keys.push( key );
		}

		return keys;
	};

	// Prototype modifications.
	RegExp.prototype.execAll = function( string ) {
		var matches = [];
		var match   = null;
		var matchArray;
		var i;

		while ( null !== ( match = this.exec( string ) ) ) {
			matchArray = [];

			for ( i in match ) {
				if ( parseInt( i ) === i ) {
					matchArray.push( match[i] );
				}
			}

			matches.push( matchArray );
		}

		return matches;
	};

	// Escape regex chars with \.
	RegExp.prototype.escape = function( text ) {
		return text.replace( /[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&' );
	};

	String.prototype.sliceReplace = function( start, end, repl ) {
		return this.substring( 0, start ) + repl + this.substring( end );
	};

	String.prototype.escape = function() {
		var tagsToReplace = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
		};
		return this.replace(
			/[&<>]/g, function( tag ) {
				return tagsToReplace[tag] || tag;
			}
		);
	};

	String.prototype.linkify = function( target ) {
		target = typeof target !== 'undefined' ? target : '';
		return this.replace( /(http(s)?:\/\/(\S)+)/gmi, '<a href="$1" target="' + target + '">$1</a>' );
	};

	String.prototype.toTitleCase = function() {
		var parts = this.split( /\s+/ );
		var title = '';
		$.each(
			parts,
			function( i, part ) {
				if ( '' !== part ) {
					title += part.slice( 0, 1 ).toUpperCase() + part.slice( 1, part.length );
					if ( parts.length - 1 !== i && '' !== parts[i + 1] ) {
						title += ' ';
					}
				}
			}
		);

		return title;
	};
} )( jQueryUrvanovSyntaxHighlighter );

(function(){
	/* frac.js (C) 2012-present SheetJS -- http://sheetjs.com */
	/* bothEquals() avoids use of &&, which gets prettified by WordPress. */
	var bothEquals = function( d1, d2, D ) {
		var ret = 0;
		if (d1<=D) {
			ret++;
		}
		if (d2<=D) {
			ret++;
		}
		return ret === 2;
	};
	var frac =function frac(x,D,mixed){var n1=Math.floor(x),d1=1;var n2=n1+1,d2=1;if(x!==n1)while(bothEquals(d1,d2,D)){var m=(n1+n2)/(d1+d2);if(x===m){if(d1+d2<=D){d1+=d2;n1+=n2;d2=D+1}else if(d1>d2)d2=D+1;else d1=D+1;break}else if(x<m){n2=n1+n2;d2=d1+d2}else{n1=n1+n2;d1=d1+d2}}if(d1>D){d1=d2;n1=n2}if(!mixed)return[0,n1,d1];var q=Math.floor(n1/d1);return[q,n1-q*d1,d1]};frac.cont=function cont(x,D,mixed){var sgn=x<0?-1:1;var B=x*sgn;var P_2=0,P_1=1,P=0;var Q_2=1,Q_1=0,Q=0;var A=Math.floor(B);while(Q_1<D){A=Math.floor(B);P=A*P_1+P_2;Q=A*Q_1+Q_2;if(B-A<5e-8)break;B=1/(B-A);P_2=P_1;P_1=P;Q_2=Q_1;Q_1=Q}if(Q>D){if(Q_1>D){Q=Q_2;P=P_2}else{Q=Q_1;P=P_1}}if(!mixed)return[0,sgn*P,Q];var q=Math.floor(sgn*P/Q);return[q,sgn*P-q*Q,Q]};

	/* {'¼': '1/4','½': '1/2','¾': '3/4','⅓': '1/3','⅔':'2/3','⅕':'1/5','⅖':'2/5','⅗':'3/5','⅘':'4/5','⅙':'1/6','⅚':'5/6','⅛':'1/8','⅜':'3/8','⅝':'5/8','⅞':'7/8'} */
	window.tastyRecipesVulgarFractions = JSON.parse(decodeURIComponent("%7B%22%C2%BC%22%3A%221%2F4%22%2C%22%C2%BD%22%3A%221%2F2%22%2C%22%C2%BE%22%3A%223%2F4%22%2C%22%E2%85%93%22%3A%221%2F3%22%2C%22%E2%85%94%22%3A%222%2F3%22%2C%22%E2%85%95%22%3A%221%2F5%22%2C%22%E2%85%96%22%3A%222%2F5%22%2C%22%E2%85%97%22%3A%223%2F5%22%2C%22%E2%85%98%22%3A%224%2F5%22%2C%22%E2%85%99%22%3A%221%2F6%22%2C%22%E2%85%9A%22%3A%225%2F6%22%2C%22%E2%85%9B%22%3A%221%2F8%22%2C%22%E2%85%9C%22%3A%223%2F8%22%2C%22%E2%85%9D%22%3A%225%2F8%22%2C%22%E2%85%9E%22%3A%227%2F8%22%7D"));

	window.tastyRecipesFormatAmount = function(amount, el) {
		if ( parseFloat( amount ) === parseInt( amount ) ) {
			return amount;
		}
		var roundType = 'frac';
		if (typeof el.dataset.amountShouldRound !== 'undefined') {
			if ('false' != el.dataset.amountShouldRound) {
				if ( 'number' === el.dataset.amountShouldRound ) {
					roundType = 'number';
				} else if ('frac' === el.dataset.amountShouldRound) {
					roundType = 'frac'
				} else if ('vulgar' === el.dataset.amountShouldRound) {
					roundType = 'vulgar'
				} else {
					roundType = 'integer';
				}
			}
		}
		if ('number' === roundType) {
			amount = Number.parseFloat(amount).toPrecision(2);
		} else if ('integer' === roundType) {
			amount = Math.round(amount);
		} else if ('frac' === roundType || 'vulgar' === roundType) {
			var denom = 8;
			if (typeof el.dataset.unit !== 'undefined') {
				var unit = el.dataset.unit;
				if (['cups','cup','c'].includes(unit)) {
					denom = 4;
					if (0.125 === amount) {
						denom = 8;
					}
					if ("0.1667" === Number.parseFloat( amount ).toPrecision(4)) {
						denom = 6;
					}
				}
				if (['tablespoons','tablespoon','tbsp'].includes(unit)) {
					denom = 2;
				}
				if (['teaspoons','teaspoon','tsp'].includes(unit)) {
					denom = 8;
				}
			}
			var amountArray = frac.cont( amount, denom, true );
			var newAmount = '';
			if ( amountArray[1] !== 0 ) {
				newAmount = amountArray[1] + '/' + amountArray[2];
				if ('vulgar' === roundType) {
					Object.keys(window.tastyRecipesVulgarFractions).forEach(function(vulgar) {
						if (newAmount === window.tastyRecipesVulgarFractions[vulgar]) {
							newAmount = vulgar;
						}
					});
				}
			}
			if ( newAmount ) {
				newAmount = ' ' + newAmount;
			}
			if ( amountArray[0] ) {
				newAmount = amountArray[0] + newAmount;
			}
			amount = newAmount;
		}
		return amount;
	}

	/**
	 * Update the URLs used to display a print view when the scale or unit
	 * selection on a recipe card has changed.
	 */
	window.tastyRecipesUpdatePrintLink = () => {
		const printLinks = document.querySelectorAll( '.tasty-recipes-print-link' );
		const printButtons = document.querySelectorAll( '.tasty-recipes-print-button' );

		// Use the first print button found as the canonical URL source.
		const printButton = document.querySelector( '.tasty-recipes-print-button' );

		// If no print button is available, we can't reliably update its href.
		if ( ! printButton ) {
			return;
		}

		const printURL = new URL( printButton.href );
		const searchParams = new URLSearchParams( printURL.search );

		const unitButton = document.querySelector( '.tasty-recipes-convert-button-active' );
		const scaleButton = document.querySelector( '.tasty-recipes-scale-button-active' );

		let unit = '';
		let scale = '';

		if ( unitButton ) {
			unit = unitButton.dataset.unitType;
			searchParams.delete('unit');
			searchParams.set( 'unit', unit );
		}

		if ( scaleButton ) {
			scale = scaleButton.dataset.amount;
			searchParams.set( 'scale', scale );
		}

		const paramString = searchParams.toString();
		const newURL = '' === paramString ? printURL.href : printURL.origin + printURL.pathname + '?' + paramString;

		printLinks.forEach( ( el ) => {
			el.href = newURL;
		});

		printButtons.forEach( ( el ) => {
			el.href = newURL;
		});
	}

	// When the document loads, look for unit and scale parameters and setup the recipe card
	// to reflect those values.
	document.addEventListener( 'DOMContentLoaded', () => {

		// Only reflect URL parameters on the print view.
		if ( ! window.location.href.includes( '/print/' ) ) {
			return;
		}

		const searchParams = new URLSearchParams( window.location.search );

		const unit = searchParams.get( 'unit' );
		const scale = searchParams.get( 'scale' );

		if ( unit && ( 'metric' === unit || 'usc' === unit ) ) {
			document.querySelector( '.tasty-recipes-convert-button[data-unit-type="' + unit + '"]' ).click();
		}

		if ( scale && Number(scale) > 0 ) {
			document.querySelector( '.tasty-recipes-scale-button[data-amount="' + Number(scale) + '"]' ).click();
		}
	});
}());

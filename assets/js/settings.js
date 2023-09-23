(function( wp, $ ){

	var converter = {

		type: false,
		getAction: 'tasty_recipes_get_count',
		convertAction: 'tasty_recipes_convert',
		template: 'tasty-recipes-convert',
		data: {
			label: '',
			loading: false,
			converting: false,
			count: 0,
			converted: 0,
			errorMessage: '',
		},

		initialize: function( wrapper ) {
			wrapper.show();
			this.wrapper = wrapper;
			this.type = wrapper.data('type');
			this.data.label = wrapper.data('label');
			this.data.loading = true;
			this.renderTemplate();
			this.bindEvents();
			this.fetchCount();
		},

		renderTemplate: function() {
			if ( this.type !== this.wrapper.data('type') ) {
				return;
			}
			var template = wp.template( this.template );
			$(this.wrapper).html( template( this.data ) );
		},

		bindEvents: function() {
			$(this.wrapper).on('click', 'button.start-conversion', $.proxy( function(){
				this.data.converting = true;
				this.renderTemplate();
				this.triggerConvert();
			}, this ));
		},

		fetchCount: function() {
			wp.ajax.send( this.getAction, {
				data: {
					nonce: tastyRecipesSettings.nonce,
					type: this.type,
				},
				type: 'GET'
			}).done( $.proxy( function( response ){
				this.data.loading = false;
				this.data.count = response.count;
				setTimeout($.proxy(function(){
					this.renderTemplate();
				}, this), 1000 );
			}, this) ).fail( $.proxy( function( response ){
				this.data.loading = false;
				this.data.errorMessage = response.message;
				setTimeout($.proxy(function(){
					this.renderTemplate();
				}, this), 1000 );
			}, this) );
		},

		triggerConvert: function() {
			this.data.converting = true;
			wp.ajax.send( this.convertAction, {
				data: {
					nonce: tastyRecipesSettings.nonce,
					type: this.type,
				},
				type: 'GET'
			}).done( $.proxy( function( response ){
				this.data.converted += response.converted;
				this.renderTemplate();
				if ( response.count ) {
					this.triggerConvert();
				} else {
					this.data.converting = false;
				}
			}, this) ).fail( $.proxy( function( response ){
				this.data.errorMessage = response.message;
				this.data.converting = false;
				this.renderTemplate();
			}, this) );
		}

	}

	$(document).ready(function(){
		var typeConverter = false;

		$('.tasty-recipes-converter-options button').each(function(){
			var button = $(this);
			wp.ajax.send( converter.getAction, {
				data: {
					nonce: tastyRecipesSettings.nonce,
					type: button.data('type'),
				},
				type: 'GET'
			}).done(function(response ){
				if ( response.count ) {
					button.removeAttr('disabled');
				}
			});
		});

		var wrapper = $('.tasty-recipes-converter-control');
		$('.tasty-recipes-converter-options button').on('click',function(e){
			e.preventDefault();
			delete typeConverter;
			var el = $(this);
			$('.tasty-recipes-converter-options button').removeClass('active button-primary');
			$('.tasty-recipes-converter-welcome').hide();
			el.addClass('active button-primary');
			typeConverter = jQuery.extend( true, {}, converter );
			wrapper.data( 'type', el.data('type') );
			wrapper.data( 'label', el.data('label') );
			typeConverter.initialize( wrapper );
		});

		wrapper.on('click', '.tasty-recipes-converter-close', $.proxy( function(){
			delete typeConverter;
			wrapper.html('');
			wrapper.data('type','');
			$('.tasty-recipes-converter-options button').removeClass('active button-primary');
			$('.tasty-recipes-converter-welcome').show();
		}, this) );

		let cook_mode_select = $('select[name="tasty_recipes_cook_mode"]');
		if ( cook_mode_select.length > 0 ) {
			cook_mode_select.change(function(event){
				let cook_mode           = $(this);
				let cook_mode_helper_tr = cook_mode.parents('tr').next('tr');

				1 === parseInt( cook_mode.val() ) ? cook_mode_helper_tr.show() : cook_mode_helper_tr.hide();
			});

			cook_mode_select.change();
		}

		const instacart_option_select = $('select[name="tasty_recipes_instacart"]');
		if ( instacart_option_select.length > 0 ) {
			instacart_option_select.change(function(event){
				const instacart_option          = $(this);
				const instacart_affiliate_id_tr = instacart_option.parents('tr').next('tr');

				1 === parseInt( instacart_option.val() ) ? instacart_affiliate_id_tr.show() : instacart_affiliate_id_tr.hide();
			});

			instacart_option_select.change();
		}

	});

}(window.wp, jQuery));

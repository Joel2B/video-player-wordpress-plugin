/**
 * Xbox Switcher
 * Version: 1.0
 * Author: Max López
 */

;(function ( $, window, document, undefined ) {

  function Plugin( element, options ) {
    var _ = this;
    _.el = element;
    _.$el = $(element);
    _.defaults = {
      on_text: '',
			off_text: '',
			on_value: 'on',
			off_value: 'off',
			readonly: false,
    };
    _.metadata = _.$el.data( 'switcher' ) || {};
    _.options = $.extend( {}, _.defaults, options, _.metadata );
    _.$el.data( 'switcher', _.options );
    _.init();
  }

  Plugin.prototype = {
    init : function () {
      var _ = this;
      if( _.$el.parent().hasClass( 'xbox-sw-wrap' ) ) {
				return;
			}
      if( _.$el.attr('type') == 'hidden' || _.$el.attr('type') == 'checkbox' ){
				_.build();
			}
    },

    build: function () {
      var _ = this;
      $input = _.$el;
			var has_icons = false;
			var html_toggle_on = '<div class="xbox-sw-toggle xbox-sw-toggle-on">';
			var html_toggle_off = '<div class="xbox-sw-toggle xbox-sw-toggle-off">';
			if( _.options.on_text !== '' && _.options.off_text !== '' ){
				html_toggle_on = html_toggle_on + _.options.on_text + '</div>';
				html_toggle_off = html_toggle_off + _.options.off_text + '</div>';
			} else {
				has_icons = true;
				html_toggle_on = html_toggle_on + '<i class="icon-sw-on"></i></div>';
				html_toggle_off = html_toggle_off + '<i class="icon-sw-off"></i></div>';
			}

			var is_disabled = $input.is(':disabled') ? true: false;

			var status_classes = '';
			status_classes += _.is_on() ? 'xbox-sw-on' : 'xbox-sw-off';
			status_classes += is_disabled ? ' xbox-sw-disabled' : '';
			status_classes += has_icons ? ' xbox-sw-has-icons' : '';

			var switcher_body =
					'<div class="xbox-sw-inner ' + status_classes + '">' +
						'<div class="xbox-sw-blob"></div>' +
						html_toggle_on + html_toggle_off +
					'</div>';

			$input.wrap('<div class="xbox-sw-wrap"></div>');
			$input.parent().append( switcher_body );
			$input.parent().find( '.xbox-sw-inner' ).addClass( 'xbox-sw-type-'+ $input.attr('type') );
    },

    is_on: function () {
			if( this.$el.next( '.xbox-sw-inner' ).hasClass( 'xbox-sw-on' ) ){
				return true;
			}
			if( this.$el.attr( 'type' ) == 'checkbox'){
				return this.$el.is( ':checked' );
			} else {
				if( this.$el.val() == this.options.on_value ){
					return true;
				}
			}
			return false;
    },

    set_on: function () {
      $input = $(this);
			if( ! $input.parent().hasClass( 'xbox-sw-wrap' ) ) {
				return;
			}
			var options = $input.data( 'switcher' );

			if( $input.attr( 'type' ) == 'checkbox' ){
				$input.prop( 'checked', true ).attr( 'checked', 'checked' );
			} else {
				$input.val( options.on_value );
			}
			//Eventos
			$input.trigger( 'changeOn' );
			$input.trigger( 'statusChange' );

			$input.parent().find( '.xbox-sw-inner' ).removeClass( 'xbox-sw-off' ).addClass( 'xbox-sw-on' );

			return true;
    },

    set_off: function () {
      $input = $(this);
			if( ! $input.parent().hasClass( 'xbox-sw-wrap' ) ) {
				return;
			}
			var options = $input.data( 'switcher' );

			if( $input.attr( 'type' ) == 'checkbox' ){
				$input.prop( 'checked', false ).removeAttr( 'checked' );
			} else {
				$input.val( options.off_value );
			}

			//Eventos
			$input.trigger( 'changeOff' );
			$input.trigger( 'statusChange' );

			$input.parent().find( '.xbox-sw-inner' ).removeClass( 'xbox-sw-on' ).addClass( 'xbox-sw-off' );

			return true;
    },

    destroy: function () {
      $(this).each(function() {
				$(this).parents( '.xbox-sw-wrap' ).children().not( 'input' ).remove();
				$(this).unwrap();
      });
			return true;
    }
  };

  //Eventos
  $(document).ready(function() {
		$(document).on('click tap', '.xbox-sw-inner:not(.xbox-sw-disabled)', function(e) {
			$input = $(this).parent().find('input');
			if( $(this).hasClass( 'xbox-sw-on' ) ) {
				$input.xboxSwitcher( 'set_off' );
			} else {
				$input.xboxSwitcher( 'set_on' );
			}
		});
		$(document).on('change', '.xbox-sw-wrap input', function(e) {
			if( $(this).next('.xbox-sw-inner').hasClass( 'xbox-sw-on' ) ) {
				$(this).xboxSwitcher( 'set_off' );
			} else {
				$(this).xboxSwitcher( 'set_on' );
			}
		});
	});

  $.fn.xboxSwitcher = function ( options ) {
    if ( Plugin.prototype[options] && options != 'init' && options != 'build' && options != 'is_on' ) {
      return Plugin.prototype[options].apply( this, Array.prototype.slice.call( arguments, 1) );
    } else if ( typeof options === 'object' || ! options ) {
      return this.each(function () {
        new Plugin( this, options );
      });
    } else {
      //nothing
    }
  };

})( jQuery, window, document );

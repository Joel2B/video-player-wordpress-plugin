;(function ( $, window, document, undefined ) {

  function Plugin( element, options ) {
    var _ = this;
    _.el = element;
    _.$el = $(element);
    _.defaults = {
      width : '100px',
      height : 'auto',
      active_class : '',
      active_color : 'blue'
    };
    _.metadata = _.$el.data( 'image-selector' ) || {};
    _.options = $.extend( {}, _.defaults, options, _.metadata );
    _.$el.data('image-selector', _.options);
    _.init();
  }

  Plugin.prototype = {
    init : function () {
      var _ = this;
      if( _.$el.hasClass('xbox-image-selector') ){
        return;
      }
      _.build();
      return _;
    },

    build: function () {
      var _ = this;
      _.$el.addClass('xbox-image-selector');
      _.$el.find('input').each(function(index, item) {
        var $input = $(item);
        var src = $input.data('image');
        if( is_empty( src ) ){
          return;
        }
        if( ! $input.parent('label.no-image').length ){
          $input.hide();
          $input.after('<img src="'+ src +'">');
          $input.next('img').css({
            'max-width': _.options.width,
            'height': _.options.height,
          });
        }

        if( $input.prop('checked') ){
          $input.parent('label').addClass('checked');
          if( is_empty( _.options.active_class ) ){
            $input.next('img').css('border', '1px solid ' + _.options.active_color );
          } else {
            $input.next('img').addClass(_.options.active_class);
          }
        }
      });
    },

    active : function(){
      var $input = $(this);
      var $el = $input.closest('.xbox-image-selector');
      var options = $el.data('image-selector');

      if( options.like_checkbox ){
        if( $input.parent('label').hasClass('checked') ){
          $input.removeAttr( 'checked' ).prop('checked', false).parent('label').removeClass('checked');
          if( $input.next('img') !== undefined ){
            if( is_empty( options.active_class ) ){
              $input.next('img').css('border', '1px transparent #FFF');
            } else {
              $input.next('img').removeClass( options.active_class );
            }
          }
        } else {
          $input.attr( 'checked', 'checked' ).prop('checked', true).parent('label').addClass('checked');
          if( $input.next('img') !== undefined ){
            if( is_empty( options.active_class ) ){
              $input.next('img').css('border', '1px solid ' + options.active_color );
            } else {
              $input.next('img').addClass( options.active_class );
            }
          }
        }
      } else {
        //Disable all
        $el.find('input').removeAttr( 'checked' ).prop('checked', false);
        $el.find('label').removeClass('checked');
        //Active current
        $input.attr( 'checked', 'checked' ).prop('checked', true).parent('label').addClass('checked');

        if( $input.next('img') !== undefined ){
          if( is_empty( options.active_class ) ){
            $el.find('img').css('border', '1px transparent #FFF');
            $input.next('img').css('border', '1px solid ' + options.active_color );
          } else {
            $el.find('img').removeClass( options.active_class );
            $input.next('img').addClass( options.active_class );
          }
        }
      }
      $input.trigger( 'imgSelectorChanged' );
      return false;
    },

    destroy: function () {
      $(this).each(function() {
        $(this).removeClass('xbox-image-selector').find('img').remove();
      });
      return true;
    },

    disable_all: function () {
      var $input = $(this);
      var $el = $input.closest('.xbox-image-selector');
      var options = $el.data('image-selector');
      if( options.like_checkbox ){
        $el.find('input').removeAttr( 'checked' ).prop('checked', false);
        $el.find('label').removeClass('checked');
        if( $input.next('img') !== undefined ){
          if( is_empty( options.active_class ) ){
            $el.find('img').css('border', '1px transparent #FFF');
          } else {
            $el.find('img').removeClass( options.active_class );
          }
        }
      }
      return true;
    }

  };

  function is_empty( value ){
    return ( value === undefined || value === false || $.trim(value).length === 0 );
  }

  $(document).ready(function($) {
    $(document).on('click.img_selector', '.xbox-image-selector input', function(event) {
      $(this).xboxImageSelector('active');
    });
    $(document).on('img_selector_disable_all', '.xbox-image-selector input', function(event) {
      $(this).xboxImageSelector('disable_all');
    });
  });

  $.fn.xboxImageSelector = function ( options ) {
    if ( Plugin.prototype[options] && options != 'init' && options != 'build' && options != 'is_empty' ) {
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
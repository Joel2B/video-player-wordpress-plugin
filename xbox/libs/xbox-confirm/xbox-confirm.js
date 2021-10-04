;(function ( $, window, document, undefined ) {

  function Plugin( options ) {
    var _ = this;
    _.el = '';
    _.$el = undefined;
    _.$overlay = undefined;
    _.visible = false;
    _.defaults = {
      wrap_class: '',
      title: '',
      content: '',
      hide_cancel: false,
      hide_confirm: false,
      cancel_text: '',
      confirm_text: '',
      cancel_class: '',
      confirm_class: 'xbox-btn-pink',
      cancel_bg: '',
      confirm_bg: '',
      cancel_color: '',
      confirm_color: '',
      onOpen : null,
      onClose : null,
      onConfirm : null,
      onCancel : null,
    };
    _.options = $.extend( {}, _.defaults, options);

    _.init();

    $(window).on("resize", function () {
      if( _.visible ){
        _.center();
      }
    });
  }

  Plugin.prototype = {
    init : function () {
      var _ = this;
      _.build();
      _.open();
      _.events();
    },

    build: function () {
      var _ = this;
      var header = _.options.title === '' ? '' : '<div class="xbox-confirm-header"><h3>' + _.options.title + '</h3></div>';
      var cancel_text = _.options.cancel_text || 'Cancel';
      var confirm_text = _.options.confirm_text || 'Accept';
      var confirm_btn = _.options.hide_confirm ? '' : '<button class="xbox-confirm-btn xbox-btn" type="button"><i class="xbox-icon xbox-icon-check"></i>' + confirm_text +'</button>';
      var cancel_btn = _.options.hide_cancel ? '' : '<button class="xbox-cancel-btn xbox-btn" type="button"><i class="xbox-icon xbox-icon-close"></i>' + cancel_text +'</button>';

      _.el =
        '<div id="cvp" class="xbox xbox-confirm ' + _.options.wrap_class +' ">' +
          '<div class="xbox-confirm-inner">' +
            '<span class="xbox-confirm-close-btn xbox-icon xbox-icon-times"></span>' +
            header +
            '<div class="xbox-confirm-content">' + _.options.content + '</div>' +
            '<div class="xbox-confirm-footer">' +
              cancel_btn + confirm_btn +
            '</div>' +
          '</div>' +
        '</div>';

      $('body').append(_.el);
      $('body').append('<div class="xbox-confirm-overlay"></div>');
      _.$el = $('body').find('.xbox-confirm');
      _.$overlay = $('body').find('.xbox-confirm-overlay');

      //Customization
      _.$el.find('.xbox-cancel-btn').addClass(_.options.cancel_class);
      _.$el.find('.xbox-confirm-btn').addClass(_.options.confirm_class);
      var cancel_css = {};
      var confirm_css = {};
      if( _.options.cancel_bg ){
        cancel_css.background = _.options.cancel_bg;
      }
      if( _.options.cancel_color ){
        cancel_css.color = _.options.cancel_color;
      }
      _.$el.find('.xbox-cancel-btn').css(cancel_css);

      if( _.options.confirm_bg ){
        confirm_css.background = _.options.confirm_bg;
      }
      if( _.options.confirm_color ){
        confirm_css.color = _.options.confirm_color;
      }
      _.$el.find('.xbox-confirm-btn').css(confirm_css);
    },

    open : function(){
      var _ = this;
      _.$overlay.fadeIn(400);
      _.$el.addClass('xbox-confirm-open');
      _.visible = true;
      _.center();
      if ( $.isFunction( _.options.onOpen ) ) {
        _.options.onOpen.call(this);
      }
    },

    events : function(){
      var _ = this;
      _.$overlay.on('click', function(event) {
        _.close(event, false);
        return false;
      });
      _.$el.find('.xbox-confirm-close-btn, .xbox-cancel-btn, .xbox-confirm-btn').on('click', function(event) {
        if( $(this).hasClass('xbox-confirm-btn') ){
          _.close(event, true);
        } else {
          _.close(event, false);
        }
        return false;
      });
    },

    close : function(event, confirm){
      var _ = this;
      _.$el.addClass('xbox-confirm-close');
      _.$el.one("webkitAnimationEnd oanimationend msAnimationEnd animationend", function(event) {
        _.destroy();
        if ( $.isFunction( _.options.onCancel ) && confirm === false ) {
          _.options.onCancel.call(this);
        }
        if ( $.isFunction( _.options.onConfirm ) && confirm ) {
          _.options.onConfirm.call(this);
        }
        if ( $.isFunction( _.options.onClose ) ) {
          _.options.onClose.call(this);
        }
      });
    },

    destroy : function(){
      var _ = this;
      _.$el.remove();
      _.$overlay.fadeOut(500).remove();
      _.visible = false;
    },

    center : function(){
      var _ = this;
      var left = ( $(window).width() - _.$el.width() ) / 2;
      var top = ( $(window).height() - _.$el.height() ) / 2;
      var margin_top = 20;
      _.$el.css({
        'top' : (top - margin_top) + 'px'
      });
    }
  };

  $.xboxConfirm = function ( options ) {
    new Plugin( options );
  };

})( jQuery, window, document );
(function (document, window, $){
    "use strict";

    function Plugin (el, options){
      this.el  = el;
      this.$el = $(el);

      this.options = $.extend({}, this._defaultOptions, options, this.$el.data());

      this.$nav    = this.$el.find('.xbox-tab-nav').first();
      this.$links  = this.$nav.find('a');
      this.$body = this.$el.find('.xbox-tab-body').first();
      this.$panels = this.$body.find('> .xbox-tab-content');

      this._checkType();

      if (this.options.type !== 'tabs'){
          this._setupAccordion();
      }

      this._setup();
      this._events();
      this._initialise();
    }

    Plugin.prototype._defaultOptions = {
      type        : 'responsive',
      breakpoint  : 768,
      speed       : 500,
      initial     : 0,
      collapsible : false,
      keepOpen    : false
    };

    Plugin.prototype._setup = function (index){
      index = index || this.options.initial;
      var _url = this.$links.eq(index).attr('href'); //store the first links url

      this.$panels.hide().eq(index).show(); //hide all tab panels

      this._updateActive(_url);
    };

    Plugin.prototype._setupAccordion = function (){
      var self = this;

      this.$panels.each(function(i, el){ //for each tab panel

        var _link = self.$links.eq(i).attr('href'), //store the links href
            _text = self.$links.eq(i).html(); //store the links text/title

        self.$panels.eq(i).before('<h3 class="xbox-accordion-title"><a href="' + _link + '"><span>' + _text + '</span></a></h3>'); //add the accordion title

      });

      this.$links = this.$links.add( this.$panels.parent().find('> .xbox-accordion-title > a') ); //update the links variable after new items have been created
    };

    Plugin.prototype._events = function (){
      var self = this;

      this.$links.on('click', function (event) { //on link click
        event.preventDefault(); //prevent default action

        self._change(this);
      });

      if (this.options.type === 'responsive'){
        $(window).resize(function(){
          self._checkType(); //check elements type i.e. tabs/accordion

          //Update active tab
          var index = self.$nav.find('.active').index();
          self._removeClasses();
          self._setup( index );
        });
      }
    };

    Plugin.prototype._change = function (trigger){
      var _trigger = $(trigger),
          _newPanel = _trigger.attr('href'); //store the items href

      if (!_trigger.parent().hasClass('active')) { //if the link is not already active
        if(this.$el.hasClass('tabs')){
          this._tabs(_newPanel);
        } else {
          this._accordion(_newPanel); //run change function depending on type
        }
        if( $.isFunction(this.options.change) ){
          this.options.change.call(this.$el, $(_newPanel));
        }
        $(document).trigger('xbox-tabs.change', [this.$el, $(_newPanel)]);

      } else if (this.$el.hasClass('accordion') && _trigger.parent().hasClass('active')) {
        if (this.options.collapsible === true) {
          this._accordionCollapse(_newPanel);
        }
      }
    };

    Plugin.prototype._initialise = function (){
      this.$el.addClass('xbox-tabs xbox-tabs-initialized');
      if( $.isFunction(this.options.initialised) ){
        this.options.initialised.call(this.$el);
      }
      $(document).trigger('xbox-tabs.initialised', [this.$el]);
    };

    Plugin.prototype._accordion = function (panel){
      if (!this.options.keepOpen) {
        this.$panels.stop(true, true).slideUp(this.options.speed);

        this._removeClasses();
      }

      this.$body.find('>.xbox-tab-content[data-tab="'+panel+'"]').stop(true, true).slideDown(this.options.speed);

      this._updateActive(panel);
    };

    Plugin.prototype._accordionCollapse = function (panel){
      this.$nav.find('a[href="' + panel + '"]').parent().removeClass('active');
      this.$body.find('>.xbox-accordion-title > a[href="' + panel + '"]').parent().removeClass('active');

      this.$body.find('>.xbox-tab-content[data-tab="'+panel+'"]').stop(true, true).slideUp(this.options.speed);
    };

    Plugin.prototype._tabs = function (panel){
      this.$panels.hide(); //hide current panel

      this._removeClasses();

      this._updateActive(panel);

      this.$body.find('>.xbox-tab-content[data-tab="'+panel+'"]').show();
    };

    Plugin.prototype._removeClasses = function (){
      this.$links.parent().removeClass('active');
    };

    Plugin.prototype._updateActive = function (panel){
      this.$nav.find('a[href="' + panel + '"]').parent().addClass('active');
      this.$body.find('>.xbox-accordion-title > a[href="' + panel + '"]').parent().addClass('active');
    };

    Plugin.prototype._checkType = function (){
      if (this.options.type === 'responsive'){
        if ($(window).outerWidth() > this.options.breakpoint) { //if the window is desktop/tablet
          this.$el.removeClass('accordion').addClass('tabs'); //add tabs class
        } else { //window is mobile size
          this.$el.removeClass('tabs').addClass('accordion'); //add accordion class
        }
      } else if (this.options.type === 'tabs' || this.options.type === 'accordion'){
        (this.options.type === 'tabs') ? this.$el.addClass('tabs') : this.$el.addClass('accordion');
      }
    };

    Plugin.prototype.open = function (index){
      var $trigger = this.$nav.find('li').eq(index).children('a');
      if ($trigger.length){
        this._change($trigger);
      }
    };



    $.fn.xboxTabs = function (options){
      var args = Array.prototype.slice.call(arguments, 1);

      return this.each(function (){
        var _this = $(this),
            _data = _this.data('xbox-tabs');

        if (!_data){
          _this.data('xbox-tabs', (_data = new Plugin(this, options)));
        }

        if (typeof options === "string" ){
          options = options.replace(/^_/, "");

          if (_data[options]){
            _data[options].apply(_data, args);
          }
        }
      });
    };

}(document, window, jQuery));
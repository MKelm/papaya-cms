/**
* papaya PopIn
*
* A simple object used by navigation links to open an lightbox iframe.
*/
(function($) {

  var stringify = {
    options : function(options) {
      var result = 'dependent=yes';
      result += ',width='+$.sizeToPixel(options.width, screen.width);
      result += ',height='+$.sizeToPixel(options.height, screen.height);
      result += ',scrollbars='+options.scollBars;
      result += ',resizable='+(options.resizable ? 'yes' : 'no');
      result += ',toolbar='+(options.toolbar ? 'yes' : 'no');
      result += ',menubar='+(options.menuBar ? 'yes' : 'no');
      result += ',location='+(options.locationBar ? 'yes' : 'no');
      result += ',status='+(options.statusBar ? 'yes' : 'no');
      return result;
    },
    position : function(options) {
      if (options.left != null && options.top != null) {
        return ',screenX='+options.left+',screenY='+options.top+
          ',left='+options.left+',top='+options.top;
      } else if (options.left != null) {
        return ',screenX='+options.left+',left='+options.left;
      } else if (options.top != null) {
        return ',screenY='+options.top+',top='+options.top;
      } else {
        return '';
      };
    },
    name : function(options) {
      return options.target+'_'+options.width+'_'+options.height;
    }
  };

  var popUp = {

    settings : {
      url : 'about:blank',
      width : '500px',
      height : '600px',
      top : null,
      left : null,
      resizable : false,
      scrollBars : 'auto',
      toolBar : false,
      menuBar : false,
      locationBar : false,
      statusBar : false,
      context : null
    },

    current : {
      window : null,
      name : ''
    },

    /**
     * Merge settings
     *
     * @param settings
     */
    setUp : function(settings) {
      this.settings = $.extend(true, this.settings, settings);
    },

    /**
     * Set width and height, center iframe, load the page into the iframe
     * and attach a hide callback, return the a promise (for an deferred object)
     * to attach events called after closing the iframe.
     *
     * @returns promise
     */
    open : function() {
      var position = stringify.position(this.settings);
      var targetName = stringify.name(this.settings);
      var target = window.open(
        this.settings.url,
        targetName,
        stringify.options(this.settings) + position
      );
      this.current.window = target;
      if (this.settings.url.match(/^[a-z]+:/)) {
        this.current.name = '';
      } else {
        this.current.name = targetName;
      }

      var that = this;
      var defer = $.Deferred();
      $.waitUntil(
        function () {
          return (target.document.readyState == 'complete');
        },
        10000
      ).done(
        function () {
          target.papayaContext = {
            defer : defer,
            data : that.settings.context
          };
          target.focus();
        }
      );
      target.papayaContext = {
        defer : defer,
        data : this.settings.context
      };
      target.focus();
      return defer.promise();
    }
  };

  $.papayaPopUp = function(settings) {
    var instance = $.extend(true, popUp);
    instance.setUp(settings);
    return instance;
  };
})(jQuery);

/**
* papaya PopIn
*
* A simple object used by navigation links to open an lightbox iframe.
*/
(function($) {

  var popIn = {

    node : null,

    template :
      '<div class="popIn">' +
        '<div class="lightBox"/>' +
        '<iframe src="about:blank" class="lightBoxFrame"></iframe>' +
      '<div class="popIn">',

    settings : {
      width : '500px',
      height : '489px',
      url : 'about:blank',
      context : null
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
      this.node = $(this.template);
      this.node.appendTo('body');

      var box = this.node.find('.lightBox');
      var iFrame = this.node.find('.lightBoxFrame');
      iFrame.width($.sizeToPixel(this.settings.width, $(window).width()));
      iFrame.height($.sizeToPixel(this.settings.height, $(window).height()));
      iFrame.center();
      if (iFrame.height() > $(document).height()) {
        box.css('height', iFrame.height() + 'px');
      } else {
        box.css('height', $(document).height() + 'px');
      }

      var that = this;
      var target = iFrame.get(0).contentWindow;

      target.document.location.replace(this.settings.url);
      var defer = $.Deferred();
      defer.always(
        function () {
          that.close();
        }
      );
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
      ).fail(
        function () {
          defer.reject();
        }
      );
      var escape = function(event) {
        if (event.keyCode == 27) {
          defer.reject();
          $(window).off('keyup', escape);
        }
      }
      $(window).on('keyup', escape);
      this.node.fadeIn();

      target.papayaContext = {
        defer : defer,
        data : this.settings.context
      };
      target.focus();

      return defer.promise();
    },

    close : function() {
      if (this.node) {
        this.node.hide();
        this.node.remove();
      }
    }
  };

  $.papayaPopIn = function(settings) {
    var instance = $.extend(true, popIn);
    instance.setUp(settings);
    return instance;
  };
})(jQuery);

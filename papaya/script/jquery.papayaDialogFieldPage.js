/**
* papaya Dialog Field Page
*
* Input field with page id popup
*/
(function($) {

  var field = {

    settings : {
      icon : 'pics/icons/16x16/items/page.png',
      url : 'script/controls/link.php',
      width : '500px',
      height : '489px'
    },

    onActionTrigger : function(event) {
      var that = this;
      event.preventDefault();
      $.papayaPopIn(
        {
          url : this.settings.url,
          width : this.settings.width,
          height : this.settings.height
        }
      )
      .open()
      .done(
        function (pageId) {
          if (pageId) {
            that.field.val(pageId);
          }
        }
      );
    }
  };

  $.papayaDialogFieldPage = function() {
    return $.extend(true, $.papayaDialogField(), field);
  };

  $.fn.papayaDialogFieldPage = function(settings) {
    this.each(
      function() {
        var instance = $.papayaDialogFieldPage();
        instance.setUp(this, settings);
      }
    );
    return this;
  };
})(jQuery);
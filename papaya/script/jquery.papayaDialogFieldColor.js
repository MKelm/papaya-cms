/**
* papaya Dialog Field Page
*
* Input field with page id popup
*/
(function($) {

  var field = {

    settings : {
      icon : 'pics/icons/16x16/actions/color-select.png',
      url : 'script/controls/color.php',
      width : '310',
      height : '300'
    },

    onActionTrigger : function(event) {
      var that = this;
      event.preventDefault();
      $.papayaPopIn(
        {
          url : this.settings.url,
          width : this.settings.width,
          height : this.settings.height,
          context : this.field.val()
        }
      )
      .open()
      .done(
        function (color) {
          if (color) {
            that.field.val(color);
          }
        }
      );
    }
  };

  $.papayaDialogFieldColor = function() {
    return $.extend(true, $.papayaDialogField(), field);
  };

  $.fn.papayaDialogFieldColor = function(settings) {
    this.each(
      function() {
        var instance = $.papayaDialogFieldColor();
        instance.setUp(this, settings);
      }
    );
    return this;
  };
})(jQuery);
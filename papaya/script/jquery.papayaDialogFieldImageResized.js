/**
* papaya Dialog Field Page
*
* Input field with page id popup
*/
(function($) {

  var field = {

    settings : {
      icon : 'pics/icons/16x16/items/image.png',
      dialogUrl : 'script/controls/image.php',
      dialogWidth : '604px',
      dialogHeight : '394px',
      rpcUrl : 'xmltree.php?rpc[cmd]=image_data&rpc[thumbnail]=1&rpc[image_conf]='
    },

    updateField : function(mediaItem) {
      this.field.val(
        mediaItem.id + ',' +
        mediaItem.width + ',' +
        mediaItem.height + ',' +
        mediaItem.resizeMode
      );
      this.update();
    }
  };

  $.papayaDialogFieldImageResized = function() {
    return $.extend(true, $.papayaDialogFieldImage(), field);
  };

  $.fn.papayaDialogFieldImageResized = function(settings) {
    this.each(
      function() {
        $.papayaDialogFieldImageResized().setUp(this, settings).update();
      }
    );
    return this;
  };
})(jQuery);
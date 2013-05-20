/**
* papaya Dialog Field Page
*
* Input field with page id popup
*/
(function($) {

  var field = {

    settings : {
      icon : 'pics/icons/16x16/items/publication.png',
      url : 'script/controls/googlemaps.php',
      width : '310',
      height : '410'
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
        function (latitude, longuitude) {
          if (latitude && longuitude) {
            that.field.val(latitude+","+longuitude);
          }
        }
      );
    }
  };

  $.papayaDialogFieldGeoPosition = function() {
    return $.extend(true, $.papayaDialogField(), field);
  };

  $.fn.papayaDialogFieldGeoPosition = function(settings) {
    this.each(
      function() {
        var instance = $.papayaDialogFieldGeoPosition();
        instance.setUp(this, settings);
      }
    );
    return this;
  };
})(jQuery);
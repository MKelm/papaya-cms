/**
* papaya Dialog Field Page
*
* Input field with page id popup
*/
(function($) {

  var field = {

    settings : {
    },

    template :
      '<table class="dialogField">'+
        '<tr>'+
          '<td class="field"></td>'+
          '<td class="action">'+
            '<span class="information"/>'+
          '</td>'+
        '</tr>'+
      '</table>',

    onChangeTrigger : function(event) {
      event.preventDefault();
      this.update();
    },

    update : function() {
      var current = this.field.val().length;
      var maximum = this.field.attr('maxlength');
      this.wrapper.find('.information').text(maximum - current);
    }
  };

  $.papayaDialogFieldCounted = function() {
    return $.extend(true, $.papayaDialogField(), field);
  };

  $.fn.papayaDialogFieldCounted = function(settings) {
    this.each(
      function() {
        if ($(this).attr('maxlength') > 0) {
          $.papayaDialogFieldCounted().setUp(this, settings).update();
        }
      }
    );
    return this;
  };
})(jQuery);
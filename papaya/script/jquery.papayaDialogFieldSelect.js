/**
* papaya Dialog Field Page
*
* Input field with page id popup
*/
(function($) {

  var field = {

    settings : {
      icon : 'pics/icons/16x16/items/page.png',
      url : 'script/controls/link.php'
    },

    template :
      '<table class="dialogField">'+
        '<tr>'+
          '<td class="action">'+
            '<input class="filter dialogInput dialogSearch" type="text"/>'+
          '</td>'+
          '<td class="field"></td>'+
        '</tr>'+
      '</table>',

    onActionTrigger : function(event) {
      var that = this;
      event.preventDefault();
      var expression = new RegExp(
        this.wrapper.find('.action input').val().replace("[","\\[").replace("]","\\]"),
        'i'
      );
      this.field.find('option').each(
        function () {
          var label = $(this).text();
          if (label.match(expression)) {
            $(this).show();
          } else {
            $(this).hide();
          }
        }
      );
      this.field.find('optgroup').each(
        function () {
          if ($(this).find('option:visible').length > 0) {
            $(this).show();
          } else {
            $(this).hide();
          }
        }
      );
      this.update();
    },

    update : function() {
      this.field.find('option:visible').each(
        function (index) {
          if (index % 2) {
            $(this).removeClass('odd').addClass('even');
          } else {
            $(this).removeClass('even').addClass('odd');
          }
        }
      );
      if (!this.field.find('option:selected').is(':visible')) {
        this.field.val(this.field.find('option:visible').eq(0).val());
      }
    }
  };

  $.papayaDialogFieldSelect = function() {
    return $.extend(true, $.papayaDialogField(), field);
  };

  $.fn.papayaDialogFieldSelect = function(settings) {
    this.each(
      function() {
        $.papayaDialogFieldSelect().setUp(this, settings).update();
      }
    );
    return this;
  };
})(jQuery);
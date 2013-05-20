/**
* papaya Dialog Checkbox Buttons
*
* Adds button to select all/none or invert the selection of a group of checkboxes.
*/
(function($) {

  var papayaDialogCheckboxes = {

    elements : null,

    initialize : function(div) {
      // store the checkboxes for later actions
      this.elements = $(div).find('input[type=checkbox]');

      var group = $('<div class="dialogControlButtons"/>').appendTo(div);

      this.createButton(
        'pics/icons/16x16/status/node-checked.png', 'All', $.proxy(this.checkAll, this)
      ).appendTo(group);
      this.createButton(
        'pics/icons/16x16/status/node-empty.png', 'None', $.proxy(this.checkNone, this)
      ).appendTo(group);
      this.createButton(
        'pics/icons/16x16/status/node-checked-disabled.png', 'Invert', $.proxy(this.checkInvert, this)
      ).appendTo(group);
    },

    createButton : function (image, caption, callback) {
      var button = $('<img src="pics/tpoint.gif"/ class="button">"');
      button.attr('src', image);
      button.attr('alt', caption);
      button.attr('title', caption);
      button.click(callback);
      return button;
    },

    checkAll : function () {
      this.elements.attr('checked', 'checked');
      return false;
    },

    checkNone : function () {
      this.elements.removeAttr('checked');
      return false;
    },

    checkInvert : function () {
      this.elements.each(
        function () {
          var checkbox = $(this);
          if (checkbox.attr('checked')) {
            checkbox.removeAttr('checked');
          } else {
            checkbox.attr('checked', 'checked');
          }
        }
      );
      return false;
    }
  };

  $.fn.papayaDialogCheckboxes = function() {
    this.each(
      function() {
        var instance = jQuery.extend(true, {}, papayaDialogCheckboxes);
        instance.initialize(this);
      }
    );
    return this;
  };
})(jQuery);

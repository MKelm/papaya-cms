/**
* papaya Dialog Hints
*
* Called using the <a> elements. The <a> elements contain the fragment with the id of the
* hint element.
*
* The script hides all hint elements except if the field has an error and bind a click event that
* toggles the visibility of the hint element.
*/
(function($) {

  var papayaDialogHint = {

    element : null,

    initialize : function(link) {
      // store the hint element
      this.element = $($(link).attr('href'));
      $(link)
        .click(
          $.proxy(this.toggle, this)
        );
      // do not hide the hint if an error was reported
      if ($(link).has('.errorMarker').length == 0) {
        this.element.hide();
      }
    },

    toggle : function() {
      this.element.stop(true, true);
      if (this.element.is(':hidden')) {
        this.element.fadeIn('slow');
      } else {
        this.element.fadeOut('slow');
      }
      return false;
    }
  };

  $.fn.papayaDialogHints = function() {
    this.each(
      function() {
        var instance = jQuery.extend(true, {}, papayaDialogHint);
        instance.initialize(this);
      }
    );
    return this;
  };
})(jQuery);
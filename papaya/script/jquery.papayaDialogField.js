/**
* papaya Dialog Field
*
* Super object for extended dialog fields, inputs that have some additional part to the right or
* left or the original field (input/select/textarea).
*/
(function($) {

  var field = {

    wrapper : null,
    field : null,
    action : null,

    settings : {
      icon : 'pics/tpoint.gif'
    },

    template :
      '<table class="dialogField">'+
        '<tr>'+
          '<td class="field"></td>'+
          '<td class="action">'+
            '<button class="icon" type="button"><img src="pics/tpoint.gif" alt=""/></button>'+
          '</td>'+
        '</tr>'+
      '</table>',

    setUp : function(node, settings) {
      this.settings = $.extend(true, this.settings, settings);

      this.field = $(node);
      this.field.on('change keyup', $.proxy(this.onChangeTrigger, this));

      this.wrapper = $(this.template).insertBefore(this.field);
      this.wrapper.find('td.field').append(this.field);

      this.action = this.wrapper.find('.action');
      this.action.find('img').attr('src', this.settings.icon);
      this.action.find('input').on('change keyup', $.proxy(this.onActionTrigger, this));
      this.action.find('button').click($.proxy(this.onActionTrigger, this));
      return this;
    },

    onChangeTrigger : function(event) {},

    onActionTrigger : function(event) {}

  };

  $.papayaDialogField = function() {
    return $.extend(true, {}, field);
  };
})(jQuery);

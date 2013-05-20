/**
* papaya Dialog Field Page
*
* Input field with page id popup
*/
(function($) {

  var field = {

    settings : {
      icon : 'pics/icons/16x16/items/folder.png',
      dialogUrl : 'script/controls/browsemediafile.php',
      dialogWidth : '90%',
      dialogHeight : '90%',
      rpcUrl : 'xmltree.php?rpc[cmd]=media_data&rpc[media_id]='
    },

    template :
      '<table class="dialogField">'+
        '<tr>'+
          '<td class="field"></td>'+
          '<td class="action">'+
            '<button class="icon" type="button"><img src="pics/tpoint.gif" alt=""/></button>'+
          '</td>'+
        '</tr>'+
        '<tr class="information" style="display: none;">'+
          '<td>'+
            '<div class="data">'+
              '<div class="title"></div>'+
              '<div class="description"/>'+
            '</div>'+
          '</td>'+
        '</tr>'+
      '</table>',

    onActionTrigger : function(event) {
      var that = this;
      event.preventDefault();
      $.papayaPopIn(
        {
          url : this.settings.dialogUrl,
          width : this.settings.dialogWidth,
          height : this.settings.dialogHeight,
          context : this.field.val()
        }
      )
      .open()
      .done(
        function (mediaItem) {
          if (mediaItem) {
            that.updateField(mediaItem);
          }
        }
      );
    },

    updateField : function(mediaItem) {
      this.field.val(
        mediaItem.id
      );
      this.update();
    },

    onChangeTrigger : function(event) {
      event.preventDefault();
      this.update();
    },

    update : function() {
      var fileId = this.field.val();
      if (fileId) {
        var that = this;
        $.ajax(
          {
            url : this.settings.rpcUrl + escape(fileId),
            dataType : 'xml'
          }
        ).done(
          function (data, textStatus, jqXHR) {
            var parameters = $(data).find('response data');
            if (parameters.find('[name=dyn_title]').attr('value')) {
              that.wrapper.find('.information .data .title').text(
                parameters.find('[name=dyn_title]').attr('value')
              );
              that.wrapper.find('.information .data .description').text(
                parameters.find('[name=dyn_mimetype]').attr('value')
              );
              that.wrapper.find('.information').show();
            } else {
              that.wrapper.find('.information').hide();
            }
          }
        );
      } else {
        this.wrapper.find('.information').hide();
      }
    }
  };

  $.papayaDialogFieldMediaFile = function() {
    return $.extend(true, $.papayaDialogField(), field);
  };

  $.fn.papayaDialogFieldMediaFile = function(settings) {
    this.each(
      function() {
        $.papayaDialogFieldMediaFile().setUp(this, settings).update();
      }
    );
    return this;
  };
})(jQuery);
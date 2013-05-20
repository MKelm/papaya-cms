/**
* papaya Dialog Field Page
*
* Input field with page id popup
*/
(function($) {

  var field = {

    settings : {
      icon : 'pics/icons/16x16/items/image.png',
      dialogUrl : 'script/controls/browseimg.php',
      dialogWidth : '90%',
      dialogHeight : '90%',
      rpcUrl : 'xmltree.php?rpc[cmd]=image_data&rpc[thumbnail]=1&rpc[image_conf]='
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
            '<div class="icon">'+
              '<a href="about:blank"><img src="pics/tpoint.gif" alt=""/></a>'+
            '</div>'+
            '<div class="data">'+
              '<div class="title"/>'+
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
            if (parameters.find('[name=thumbnail_src]').attr('value') != '') {
              that.wrapper.find('.information .icon img').attr(
                {
                  src : parameters.find('[name=thumbnail_src]').attr('value'),
                  width : parameters.find('[name=thumbnail_width]').attr('value'),
                  height : parameters.find('[name=thumbnail_height]').attr('value')
                }
              );
              that.wrapper.find('.information a').attr(
                'href',
                'mediadb.php?mdb*cmd=edit_file&mdb*file_id=' +
                  escape(parameters.find('[name=src]').attr('value'))
              );
              that.wrapper.find('.information .data .title').text(
                parameters.find('[name=title]').attr('value')
              );
              that.wrapper.find('.information .data .description').text(
                parameters.find('[name=description]').attr('value')
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

  $.papayaDialogFieldImage = function() {
    return $.extend(true, $.papayaDialogField(), field);
  };

  $.fn.papayaDialogFieldImage = function(settings) {
    this.each(
      function() {
        $.papayaDialogFieldImage().setUp(this, settings).update();
      }
    );
    return this;
  };
})(jQuery);
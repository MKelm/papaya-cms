PapayaFileForm = JsonClass(
  PapayaForm,
  {
    elements : {
      filetext : {
        check : '!regexp=[<>]'
      },
      fileinfo : {
        check : '!regexp=[<>"]'
      },
      filetarget : {
        check : ['req', 'alphahyphen']
      }
    },

    fileData : {
      id : '',
      name : '',
      title : '',
      size : 0
    },

    init : function() {
      this.attach(document.forms[0]);

      this.editor = tinyMCEPopup.editor;
      var elm = this.editor.selection.getNode();
      var action = 'insert';

      elm = this.editor.dom.getParent(elm, 'a');
      if (elm && elm.nodeName.toLowerCase() == 'a') {
        action = 'update';
      }

      this.papayaTag = new PapayaTag();
      this.papayaTag.name = 'file';
      // prefill form fields with existing data
      if (action == 'update') {
        this.papayaTag.loadFromHTMLNode(elm);
        this.fileData.id = this.papayaTag.getAttr('src');
        this.fileData.name = this.papayaTag.getAttr('dyn_file', '');
        this.fileData.title = this.papayaTag.getAttr('dyn_title', '');
        this.fileData.size = this.papayaTag.getAttr('dyn_size', 0);
        this.showFileData();
        this.setFieldValue('filetext', this.papayaTag.getAttr('text', ''));
        this.setFieldValue('fileinfo', this.papayaTag.getAttr('hint', ''));
        this.setFieldValue('filetarget', this.papayaTag.getAttr('target', '_self'));
      } else {
        var attrText = this.editor.selection.getContent({format : 'text'});
        this.setFieldValue('filetext', attrText);
      }
      window.focus();
    },

    apply : function() {
      if (this.fileData.id != '') {
        this.papayaTag.name = 'file';
        this.papayaTag.setAttr('src', this.fileData.id);
        this.papayaTag.setAttr('text', this.getFieldValue('filetext'));
        this.papayaTag.setAttr('hint', this.getFieldValue('fileinfo'));
        var plugin = tinyMCEPopup.getWindowArg('plugin');
        plugin.papayaParser.rememberData(this.papayaTag);
        if (this.papayaTag.elm) {
          this.editor.selection.select(this.papayaTag.elm);
        }
        this.editor.selection.setContent(this.papayaTag.getHTMLTag());
        tinyMCEPopup.close();
      }
    },

    browseFiles : function() {
      this.openPopup({
        file : '../../../controls/browsemediafile.php',
        width : Math.round(screen.width * 0.7),
        height : Math.round(screen.height * 0.7),
        name : 'papayaFileBrowser',
        modal : 'yes'
      }, {
        linkedObject : this
      });
    },

    setMediaFileData : function(id, fileData) {
      for (var key in fileData) {
        this.fileData[key] = fileData[key];
      }
      this.showFileData();
    },

    showFileData : function() {
      this.setFieldValue('filedataid', this.fileData.id);
      this.setFieldValue('filedataname', this.fileData.name);
      this.setFieldValue('filedatatitle', this.fileData.title);
      this.setFieldValue(
        'filedatasize',
        PapayaUtils.formatFileSizeToStr(this.fileData.size)
      );
    }
  }
);

/**
* This global function is a fallback (for IE) if we can not set the linkedObject in the popup
*/
function setMediaFileData(fileId, fileData) {
  PapayaFileDialog.setMediaFileData(fileId, fileData);
}

PapayaFileDialog = new PapayaFileForm();
tinyMCEPopup.onInit.add(PapayaFileDialog.init, PapayaFileDialog);
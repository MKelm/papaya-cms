/**
* @version $Id: media_dlg.js 38113 2013-02-12 15:43:25Z smekal $
* @author <a href="mailto:info@papaya-cms.com">Thomas Weinert</a>
*/

PapayaMediaForm = JsonClass(
  PapayaForm,
  {
    elements : {
      editMarginLeft : {
        check : 'numeric'
      },
      editMarginRight : {
        check : 'numeric'
      },
      editMarginTop : {
        check : 'numeric'
      },
      editMarginBottom : {
        check : 'numeric'
      },
      editMarginLeft : {
        check : 'numeric'
      },
      editSizeWidth : {
        check : ['numeric', 'gt=0']
      },
      editSizeHeight : {
        check : ['numeric', 'gt=0']
      },
      subtitle : {
        check : 'alphahyphen'
      },
      alttext : {
        check : 'alphahyphen'
      },
      pageid : {
        check : ['req', 'numeric', 'gt=0'],
        use : 'checked=linktype_pageid'
      },
      url : {
        check : ['req', '!regexp=[^A-Z0-9a-z&%+_.:/?=\\s-]'],
        use : 'checked=linktype_url'
      },
      email : {
        check : ['req', 'email'],
        use : 'checked=linktype_email'
      },
      emailsubject : {
        check : 'alphahyphen',
        use : 'checked=linktype_email'
      }
    },

    mediaData : {
      id : '',
      name : '',
      title : '',
      size : 0,
      align : '',
      resize : 'abs',
      width : 0,
      height : 0,
      orgWidth: 0,
      orgHeight: 0,
      lockProportions : false,
      linkMode : 'none'
    },

    init : function() {
      this.attach(document.forms[0]);

      this.editor = tinyMCEPopup.editor;
      var elm = this.editor.selection.getNode();
      var action = 'insert';

      elm = this.editor.dom.getParent(elm, 'img');
      if (elm && elm.nodeName.toLowerCase() == 'img') {
        action = 'update';
      }

      this.papayaTag = new PapayaTag();
      this.papayaTag.name = 'media';
      // prefill form fields with existing data
      if (action == 'update') {
        this.papayaTag.loadFromHTMLNode(elm);
        this.mediaData.id = this.papayaTag.getAttr('src');
        this.mediaData.name = this.papayaTag.getAttr('dyn_file');
        this.mediaData.type = this.papayaTag.getAttr('dyn_type');
        this.mediaData.title = this.papayaTag.getAttr('dyn_title');
        this.mediaData.size = this.papayaTag.getAttr('dyn_size', 0);
        this.mediaData.orgWidth = this.papayaTag.getAttr('dyn_orgwidth', 0);
        this.mediaData.orgHeight = this.papayaTag.getAttr('dyn_orgheight', 0);
        this.mediaData.width = this.papayaTag.getAttr('width', this.mediaData.orgWidth);
        this.mediaData.height = this.papayaTag.getAttr('height', this.mediaData.orgHeight);

        this.setFieldValue('editMarginTop', this.papayaTag.getAttr('tspace', 0));
        this.setFieldValue('editMarginRight', this.papayaTag.getAttr('rspace', 0));
        this.setFieldValue('editMarginBottom', this.papayaTag.getAttr('bspace', 0));
        this.setFieldValue('editMarginLeft', this.papayaTag.getAttr('lspace', 0));

        if (this.mediaData.width <= 0) {
          this.setFieldValue('editSizeWidth', '');
        } else {
          this.setFieldValue('editSizeWidth', this.mediaData.width);
        }
        if (this.mediaData.height <= 0) {
          this.setFieldValue('editSizeHeight', '');
        } else {
          this.setFieldValue('editSizeHeight', this.mediaData.height);
        }
        this.selectResize(this.papayaTag.getAttr('resize', 'max'));
        this.setFieldValue('subtitle', this.papayaTag.getAttr('subtitle', ''));
        this.setFieldValue('alttext', this.papayaTag.getAttr('alt', ''));
        this.setFieldValue('cssclass', this.papayaTag.getAttr('class', ''));

        this.selectAlign(this.papayaTag.getAttr('align', 'inline'));
        this.showFileData();

        if (this.papayaTag.hasAttr('topic') &&
            parseInt(this.papayaTag.getAttr('topic', 0)) > 0) {
          this.selectLinkMode('pageid', true);
          this.setFieldValue('pageid', this.papayaTag.getAttr('topic', 0));
        } else if (this.papayaTag.hasAttr('href') &&
                   this.papayaTag.getAttr('href') != '') {
          var attrHref = this.papayaTag.getAttr('href');
          if (attrHref.substr(0, 7) == 'mailto:') {
            var pos = attrHref.indexOf('?');
            var len = attrHref.length;
            this.setFieldValue('email', attrHref.substring(7, pos));
            this.setFieldValue('emailsubject', attrHref.substring(pos+1));
          } else {
            if (attrHref.substr(0, 10) == 'mediafile:') {
              this.selectLinkMode('file', true);
              this.setFieldValue('filedataid', attrHref.substring(10));
              this.callbackMediaFileChange();
            } else if (attrHref.substr(0, 11) == 'mediaimage:') {
              this.selectLinkMode('image', true);
              this.setFieldValue('imageid', attrHref.substring(11));
              this.callbackMediaFileChange();
            } else {
              this.selectLinkMode('url', true);
              this.setFieldValue('url', attrHref);
            }
            switch (this.papayaTag.getAttr('target')) {
              case '_blank' :
                this.setFieldValue('urltarget', '_blank');
                this.setFieldValue('filetarget', '_blank');
                break;
              case '_self' :
              default :
                this.setFieldValue('urltarget', '_self');
                this.setFieldValue('filetarget', '_self');
                break;
            }
          }
        } else if (this.papayaTag.getAttr('popup') == 'yes') {
          this.selectLinkMode('popup', true);
        } else {
          this.selectLinkMode('none', true);
        }
      } else {
        this.selectLinkMode('none', true);
      }
      this.showStatus();
      window.focus();
    },

    apply : function() {
      if (this.mediaData.id != '') {
        this.papayaTag.setAttr('src', this.mediaData.id);

        this.papayaTag.setAttr('tspace', this.getFieldValue('editMarginTop'));
        this.papayaTag.setAttr('rspace', this.getFieldValue('editMarginRight'));
        this.papayaTag.setAttr('bspace', this.getFieldValue('editMarginBottom'));
        this.papayaTag.setAttr('lspace', this.getFieldValue('editMarginLeft'));

        this.papayaTag.setAttr('width', this.getFieldValue('editSizeWidth'));
        this.papayaTag.setAttr('height', this.getFieldValue('editSizeHeight'));

        this.papayaTag.setAttr('align', this.mediaData.align);
        this.papayaTag.setAttr('resize', this.mediaData.resize);

        this.papayaTag.setAttr('subtitle', this.getFieldValue('subtitle'));
        this.papayaTag.setAttr('alt', this.getFieldValue('alttext'));
        this.papayaTag.setAttr('class', this.getFieldValue('cssclass'));

        switch (this.mediaData.linkMode) {
        case 'popup' :
          this.papayaTag.setAttr('popup', 'yes');
          this.papayaTag.setAttr('href', '');
          this.papayaTag.setAttr('target', '');
          this.papayaTag.setAttr('topic', '');
          break;
        case 'url' :
          this.papayaTag.setAttr('popup', '');
          this.papayaTag.setAttr('href', this.getFieldValue('url'));
          this.papayaTag.setAttr('target', this.getFieldValue('urltarget'));
          this.papayaTag.setAttr('topic', '');
          break;
        case 'email' :
          this.papayaTag.setAttr('popup', '');
          var emailHref = 'mailto:' + this.getFieldValue('email');
          var emailSubject = this.getFieldValue('emailsubject');
          if (emailSubject != '') {
            emailHref += '?' + emailSubject;
          }
          this.papayaTag.setAttr('href', emailHref);
          this.papayaTag.setAttr('target', '');
          this.papayaTag.setAttr('topic', '');
          break;
        case 'file' :
          this.papayaTag.setAttr('popup', '');
          var fileHref = 'mediafile:' + this.getFieldValue('filedataid');
          this.papayaTag.setAttr('href', fileHref);
          this.papayaTag.setAttr('target', this.getFieldValue('filetarget'));
          this.papayaTag.setAttr('topic', '');
          break;
        case 'image' :
          this.papayaTag.setAttr('popup', '');
          var imageHref = 'mediaimage:' + this.getFieldValue('imageid');
          this.papayaTag.setAttr('href', imageHref);
          this.papayaTag.setAttr('target', '');
          this.papayaTag.setAttr('topic', '');
          break;
        case 'pageid' :
          this.papayaTag.setAttr('popup', '');
          this.papayaTag.setAttr('href', '');
          this.papayaTag.setAttr('target', '');
          this.papayaTag.setAttr('topic', this.getFieldValue('pageid'));
          break;
        case 'none' :
        default :
          this.papayaTag.setAttr('popup', '');
          this.papayaTag.setAttr('href', '');
          this.papayaTag.setAttr('target', '');
          this.papayaTag.setAttr('topic', '');
          break;
        }
        var plugin = tinyMCEPopup.getWindowArg('plugin');
        plugin.papayaParser.rememberData(this.papayaTag);
        if (this.papayaTag.elm) {
          this.editor.selection.select(this.papayaTag.elm);
        }
        this.editor.selection.setContent(this.papayaTag.getHTMLTag());
        tinyMCEPopup.close();
      }
    },

    browseImages : function() {
      this.openPopup({
        file : '../../../controls/browseimg.php',
        width : Math.round(screen.width * 0.7),
        height : Math.round(screen.height * 0.7),
        name : 'papayaMediaBrowser',
        modal : 'no'
      }, {
        linkedObject : this
      });
    },

    browseMediaFiles : function(filter) {
      var context = new Object();
      context.setMediaFileData = PapayaUtils.scope(this, this.setMediaLinkData);
      this.openPopup({
        file : '../../../controls/browse'+filter+'.php',
        width : Math.round(screen.width * 0.7),
        height : Math.round(screen.height * 0.7),
        name : 'papayaMediaBrowser',
        modal : 'no'
      }, {
        linkedObject : context
      });
    },

    setMediaLinkData : function(fileId, fileData) {
      var dyn_size = (Math.round((fileData.size / 1024) * 100) / 100) + ' kB';
      if (this.mediaData.linkMode == 'file') {
        this.setFieldValue('filedataid', fileId);
        this.setFieldValue('filedatatitle', fileData.title);
        this.setFieldValue('filedataname', fileData.name);
        this.setFieldValue('filedatasize', dyn_size);
      } else {
        this.setFieldValue('imageid', fileId);
        this.setFieldValue('imagetitle', fileData.title);
        this.setFieldValue('imagename', fileData.name);
        this.setFieldValue('imagesize', dyn_size);
      }
    },

    setMediaFileData : function(fileId, fileData) {
      for (var key in fileData) {
        this.mediaData[key] = fileData[key];
      }
      this.mediaData.orgWidth = fileData.width;
      this.mediaData.orgHeight = fileData.height;
      this.getOriginalSize();
      this.showFileData();
    },

    showFileData : function() {
      this.showPreview();
    },

    showStatus : function() {
      switch (this.mediaData.resize) {
      case 'abs' :
        this.moveBackground('imageSizeMinCrop', '0 0');
        this.moveBackground('imageSizeAbs', '-34px -22px');
        this.moveBackground('imageSizeMax', '0 -43px');
        this.moveBackground('imageSizeMin', '0 -63px');
        break;
      case 'min' :
        this.moveBackground('imageSizeMinCrop', '0 0');
        this.moveBackground('imageSizeAbs', '0 -22px');
        this.moveBackground('imageSizeMax', '0 -43px');
        this.moveBackground('imageSizeMin', '-34px -63px');
        break;
      case 'mincrop' :
        this.moveBackground('imageSizeMinCrop', '-34px 0');
        this.moveBackground('imageSizeAbs', '0 -22px');
        this.moveBackground('imageSizeMax', '0 -43px');
        this.moveBackground('imageSizeMin', '0 -63px');
        break;
      default :
      case 'max' :
        this.moveBackground('imageSizeMinCrop', '0 0');
        this.moveBackground('imageSizeAbs', '0 -22px');
        this.moveBackground('imageSizeMax', '-34px -43px');
        this.moveBackground('imageSizeMin', '0 -63px');
        break;
      }
      switch (this.mediaData.align) {
      case 'left' :
        this.moveBackground('imageAlignCenter', '0 0');
        this.moveBackground('imageAlignInline', '0 -35px');
        this.moveBackground('imageAlignLeft', '-35px -70px');
        this.moveBackground('imageAlignRight', '0 -105px');
        break;
      case 'right' :
        this.moveBackground('imageAlignCenter', '0 0');
        this.moveBackground('imageAlignInline', '0 -35px');
        this.moveBackground('imageAlignLeft', '0 -70px');
        this.moveBackground('imageAlignRight', '-35px -105px');
        break;
      case 'center' :
        this.moveBackground('imageAlignCenter', '-35px 0');
        this.moveBackground('imageAlignInline', '0 -35px');
        this.moveBackground('imageAlignLeft', '0 -70px');
        this.moveBackground('imageAlignRight', '0 -105px');
        break;
      default :
      case 'inline' :
        this.moveBackground('imageAlignCenter', '0 0');
        this.moveBackground('imageAlignInline', '-35px -35px');
        this.moveBackground('imageAlignLeft', '0 -70px');
        this.moveBackground('imageAlignRight', '0 -105px');
        break;
      }
      if (this.mediaData.lockProportions) {
        this.moveBackground('imageProtectProportions', '0 0');
      } else {
        this.moveBackground('imageProtectProportions', '-20px 0');
      }
    },

    moveBackground : function(eId, p) {
      var e = document.getElementById(eId);
      if (e) {
        e.style.backgroundPosition = p;
      }
    },

    selectResize : function(s) {
      switch (s) {
      case 'abs' :
      case 'min' :
      case 'mincrop' :
        this.mediaData.resize = s;
        break;
      case 'max' :
      default :
        this.mediaData.resize = 'max';
        break;
      }
      this.showStatus();
    },

    selectAlign : function(s) {
      switch (s) {
      case 'left' :
      case 'right' :
      case 'center' :
        this.mediaData.align = s;
        break;
      case 'inline' :
      default :
        this.mediaData.align = '';
        break;
      }
      this.showStatus();
    },

    switchLockProportions : function() {
      this.mediaData.lockProportions = !(this.mediaData.lockProportions);
      this.showStatus();
    },

    selectLinkMode : function(newMode, active) {
      var panels = {
        none : 'panel_linkParamNone',
        popup : 'panel_linkParamPopup',
        pageid : 'panel_linkParamPageId',
        url : 'panel_linkParamURL',
        email : 'panel_linkParamEmail',
        file : 'panel_linkParamFile',
        image : 'panel_linkParamImage'
      };
      if (active) {
        var e;
        for (var i in panels) {
          e = document.getElementById(panels[i]);
          if (e) {
            var er = document.getElementById('linktype_' + newMode);
            if (i == newMode) {
              e.style.display = '';
              this.mediaData.linkMode = newMode;
              if (er && er.checked != active) {
                er.checked = active;
              }
            } else {
              e.style.display = 'none';
              if (er && er.checked != true) {
                er.checked = false;
              }
            }
          }
        }
      }
    },

    getOriginalSize : function() {
      if (this.mediaData.orgWidth) {
        this.setFieldValue('editSizeWidth', this.mediaData.orgWidth);
      }
      if (this.mediaData.orgHeight) {
        this.setFieldValue('editSizeHeight', this.mediaData.orgHeight);
      }
    },

    changeSize : function(e) {
      if (this.mediaData.lockProportions &&
          this.mediaData.orgWidth > 0 &&
          this.mediaData.orgHeight > 0) {
        switch (e.id) {
        case 'editSizeWidth' :
          var newWidth = parseInt(e.value);
          if (!isNaN(newWidth)) {
            this.mediaData.height = Math.round(
              newWidth * this.mediaData.orgHeight / this.mediaData.orgWidth
            );
            this.setFieldValue('editSizeHeight', this.mediaData.height);
          }
          break;
        case 'editSizeHeight' :
          var newHeight = parseInt(e.value);
          if (!isNaN(newHeight)) {
            this.mediaData.width = Math.round(
              newHeight * this.mediaData.orgWidth / this.mediaData.orgHeight
            );
            this.setFieldValue('editSizeWidth', this.mediaData.width);
          }
          break;
        }
      }
    },

    showPreview : function() {
      var iframe = document.getElementById('file_preview');
      var url = '../../../../mediafilebrw.php?mdb[mode]=preview&mdb[imagesonly]=1&mdb[file]=' + encodeURI(this.mediaData.id);
      if (iframe) {
        if (iframe.setAttribute) {
          iframe.setAttribute('src', url);
        } else {
          iframe.src = url;
        }
      }
    },

    browsePages : function(f) {
      this.openPopup({
        file : '../../../controls/link.php',
        width : '400',
        height : '500',
        name : 'papayaPageBrowser',
        modal : 'no'
      }, {
        linkedObject : document.getElementById(f)
      });
    },

    callbackMediaFileChange : function() {
      if (this.mediaData.linkMode == 'file') {
        var mediaSrc = this.getFieldValue('filedataid');
      } else {
        var mediaSrc = this.getFieldValue('imageid');
      }
      var callback = new Object();
      callback.rpcSetMediaData = PapayaUtils.scope(this, this.updateMediaFileInformation);
      var plugin = tinyMCEPopup.getWindowArg('plugin');
      loadXMLDoc(plugin.papayaParser.rpcURL + '?rpc[cmd]=media_data&rpc[media_id]=' + encodeURI(mediaSrc) + '&random=' + Math.round(Math.random()*1000), false, callback);
    },

    updateMediaFileInformation : function(data, params) {
      var responseParams = xmlParamNodesToArray(params);
      var dyn_size = (Math.round((responseParams.dyn_size / 1024) * 100) / 100) + ' kB';
      if (this.mediaData.linkMode == 'file') {
        this.setFieldValue('filedatatitle', responseParams.dyn_title);
        this.setFieldValue('filedataname', responseParams.dyn_title);
        this.setFieldValue('filedatasize', dyn_size);
      } else {
        this.setFieldValue('imagetitle', responseParams.dyn_title);
        this.setFieldValue('imagename', responseParams.dyn_title);
        this.setFieldValue('imagesize', dyn_size);
      }
    }
  }
);

/**
* This global function is a fallback (for IE) if we can not set the linkedObject in the popup
*/
function setMediaFileData(fileId, fileData) {
  PapayaMediaDialog.setMediaFileData(fileId, fileData);
}

PapayaMediaDialog = new PapayaMediaForm();
tinyMCEPopup.onInit.add(PapayaMediaDialog.init, PapayaMediaDialog);

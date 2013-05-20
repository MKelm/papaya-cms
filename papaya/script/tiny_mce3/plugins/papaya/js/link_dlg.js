PapayaLinkForm = JsonClass(
  PapayaForm,
  {
    elements : {
      pageid : {
        check : ['req', 'num', 'gt=0' ],
        use : 'panel=page_panel',
        info : 'papaya.fieldinfo_pageid'
      },
      pageidtext : {
        check : '!regexp=[<>]',
        use : 'panel=page_panel'
      },
      pageidtitle : {
        check : '!regexp=[<>]',
        use : 'panel=page_panel'
      },
      pageidtextmode : {
        check : ['req', 'alpha'],
        use : 'panel=page_panel'
      },
      pageidtarget : {
        check : ['req', 'alnumhyphen'],
        use : 'panel=page_panel'
      },
      pageidpopupwidth : {
        check : ['req', 'num', 'gt=0', 'lt=1000'],
        use : ['panel=page_panel', 'checkbox=pageidpopupuse']
      },
      pageidpopupheight : {
        check : ['req', 'num', 'gt=0', 'lt=800'],
        use : ['panel=page_panel', 'checkbox=pageidpopupuse']
      },

      url : {
        check : ['req', '!regexp=[^A-Z0-9a-z&%+_.,;\[\]\(\):/?=\\s-]' ],
        use : 'panel=url_panel'
      },
      urltext : {
        check : ['req', '!regexp=[<>]'],
        use : 'panel=url_panel'
      },
      urltitle : {
        check : '!regexp=[<>]',
        use : 'panel=url_panel'
      },
      urltarget : {
        check : ['req', 'alnumhyphen'],
        use : 'panel=url_panel'
      },
      urlpopupwidth : {
        check : ['req', 'num'],
        use : ['panel=url_panel', 'checkbox=urlpopupuse']
      },
      urlpopupheight : {
        check : ['req', 'num'],
        use : ['panel=url_panel', 'checkbox=urlpopupuse']
      },

      email : {
        check : ['req', 'email'],
        use : 'panel=email_panel'
      },
      emailsubject : {
        check : '!regexp=[<>&\"]',
        use : 'panel=email_panel'
      },
      emailtext : {
        check : '!regexp=[<>]',
        use : 'panel=email_panel'
      },
      emailtitle : {
        check : '!regexp=[<">]',
        use : 'panel=email_panel'
      }
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
      this.papayaTag.name = 'link';
      // prefill form fields with existing data
      if (action == 'update') {
        var tabPage = 'url';
        this.papayaTag.loadFromHTMLNode(elm);
        var attrHref = this.papayaTag.getAttr('href', '');
        if (this.papayaTag.hasAttr('topic')) {
          tabPage = 'pageid';
          this.setFieldValue('pageid', this.papayaTag.getAttr('topic', 0));
        } else if (attrHref.match(/^mailto:/i)) {
          tabPage = 'email';
          var pos = attrHref.indexOf('?');
          if (pos > 0) {
            var len = attrHref.length;
            this.setFieldValue('email', attrHref.substring(7, pos));
            this.setFieldValue('emailsubject', attrHref.substring(pos+1));
          } else {
            this.setFieldValue('email', attrHref.substring(7, len));
            this.setFieldValue('emailsubject', '');
          }
        } else if (this.papayaTag.hasAttr('href')) {
          this.setFieldValue('url', attrHref);
        } else {
          var href = elm.getAttribute('href');
          this.setFieldValue('url', href);
        }
        //page alternative text
        var attrText = this.papayaTag.getAttr('text', '');
        this.setFieldValue('pageidtext', attrText);
        this.setFieldValue('emailtext', attrText);
        this.setFieldValue('urltext', attrText);
        //link title attribute
        var attrTitle = this.papayaTag.getAttr('title', '');
        this.setFieldValue('pageidtitle', attrTitle);
        this.setFieldValue('emailtitle', attrTitle);
        this.setFieldValue('urltitle', attrTitle);
        //css class attribute
        var attrClass = this.papayaTag.getAttr('class', '');
        this.setFieldValue('pageidcss', attrClass);
        this.setFieldValue('emailcss', attrClass);
        this.setFieldValue('urlcss', attrClass);
        //target
        if (this.papayaTag.hasAttr('target')) {
          var attrTarget = this.papayaTag.getAttr('target', '');
          this.setFieldValue('pageidtarget', attrTarget);
          this.setFieldValue('urltarget', attrTarget);
        }
        //alternative text mode
        var attrAltMode = this.papayaTag.getAttr('altmode', 'auto');
        this.setFieldValue('pageidtextmode', attrAltMode);
        //set alternative text to current link text
        if (attrAltMode == 'yes' || this.papayaTag.getAttr('dyn_exists', 0) == 0) {
          this.setFieldValue(
            'pageidtext',
            PapayaUtils.HTMLCharEncode(PapayaUtils.text(elm))
          );
        }
        //popup data
        if (this.papayaTag.name == 'popup') {
          this.setFieldValue('pageidpopupuse', true);
          this.setFieldValue('urlpopupuse', true);
          if (this.papayaTag.hasAttr('width')) {
            var attrWidth = this.papayaTag.getAttr('width', 0);
            this.setFieldValue('pageidpopupwidth', attrWidth);
            this.setFieldValue('urlpopupwidth', attrWidth);
          }
          if (this.papayaTag.hasAttr('height')) {
            var attrHeight = this.papayaTag.getAttr('height', 0);
            this.setFieldValue('pageidpopupheight', attrHeight);
            this.setFieldValue('urlpopupheight', attrHeight);
          }
          this.setFieldValue('urlpopupscrollbars', this.papayaTag.getAttr('scrollbars', false));
          this.setFieldValue('urlpopupresize', this.papayaTag.getAttr('resize', false));
          this.setFieldValue('urlpopuptoolbar', this.papayaTag.getAttr('toolbar', false));
          this.setFieldValue('pageidpopupscrollbars', this.papayaTag.getAttr('scrollbars', false));
          this.setFieldValue('pageidpopupresize', this.papayaTag.getAttr('resize', false));
          this.setFieldValue('pageidpopuptoolbar', this.papayaTag.getAttr('toolbar', false));
        }
        switch (tabPage) {
        case 'pageid' :
          this.displayTab('page_tab','page_panel');
          break;
        case 'email' :
          this.displayTab('email_tab','email_panel');
          break;
        case 'url' :
        default :
          this.displayTab('url_tab','url_panel');
          break;
        }
      } else {
        var attrText = this.editor.selection.getContent({format : 'text'});
        this.setFieldValue('pageidtext', attrText);
        this.setFieldValue('emailtext', attrText);
        this.setFieldValue('urltext', attrText);
        this.displayTab('url_tab','url_panel');
      }
      window.focus();
    },

    apply : function() {
      switch (this.currentPanel) {
      case 'page_panel' :
        this.papayaTag.setAttr('topic', this.getFieldValue('pageid'));
        this.papayaTag.setAttr('text', this.getFieldValue('pageidtext'));
        this.papayaTag.setAttr('title', this.getFieldValue('pageidtitle'));
        this.papayaTag.setAttr('class', this.getFieldValue('pageidcss'));
        this.papayaTag.setAttr('altmode', this.getFieldValue('pageidtextmode'));
        this.papayaTag.setAttr('target', this.getFieldValue('pageidtarget'));
        if (this.getFieldValue('pageidpopupuse')) {
          this.papayaTag.name = 'popup';
          this.papayaTag.setAttr('width', this.getFieldValue('pageidpopupwidth'));
          this.papayaTag.setAttr('height', this.getFieldValue('pageidpopupheight'));
          this.papayaTag.setAttr('scrollbars', this.getFieldValueString('pageidpopupscrollbars'));
          this.papayaTag.setAttr('resize', this.getFieldValueString('pageidpopupresize'));
          this.papayaTag.setAttr('toolbar', this.getFieldValueString('pageidpopuptoolbar'));
        } else {
          this.papayaTag.name = 'link';
          this.papayaTag.setAttr('width', '');
          this.papayaTag.setAttr('height', '');
          this.papayaTag.setAttr('scrollbars', '');
          this.papayaTag.setAttr('resize', '');
          this.papayaTag.setAttr('toolbar', '');
        }
        this.papayaTag.setAttr('href', '');
        var plugin = tinyMCEPopup.getWindowArg('plugin');
        plugin.papayaParser.rememberData(this.papayaTag);
        break;
      case 'url_panel' :
        this.papayaTag.setAttr('href', this.getFieldValue('url'));
        this.papayaTag.setAttr('text', this.getFieldValue('urltext'));
        this.papayaTag.setAttr('title', this.getFieldValue('urltitle'));
        this.papayaTag.setAttr('class', this.getFieldValue('urlcss'));
        this.papayaTag.setAttr('target', this.getFieldValue('urltarget'));
        if (this.getFieldValue('urlpopupuse')) {
          this.papayaTag.name = 'popup';
          this.papayaTag.setAttr('width', this.getFieldValue('urlpopupwidth'));
          this.papayaTag.setAttr('height', this.getFieldValue('urlpopupheight'));
          this.papayaTag.setAttr('scrollbars', this.getFieldValueString('urlpopupscrollbars'));
          this.papayaTag.setAttr('resize', this.getFieldValueString('urlpopupresize'));
          this.papayaTag.setAttr('toolbar', this.getFieldValueString('urlpopuptoolbar'));
        } else {
          this.papayaTag.name = 'link';
          this.papayaTag.setAttr('width', '');
          this.papayaTag.setAttr('height', '');
          this.papayaTag.setAttr('scrollbars', '');
          this.papayaTag.setAttr('resize', '');
          this.papayaTag.setAttr('toolbar', '');
        }
        this.papayaTag.setAttr('topic', '');
        break;
      case 'email_panel' :
        var mailto = 'mailto:' + this.getFieldValue('email');
        var subject = this.getFieldValue('emailsubject');
        if (subject != '') {
          mailto += '?' + subject;
        }
        this.papayaTag.setAttr('href', mailto);
        var altText = this.getFieldValue('emailtext');
        if (altText == '') {
          altText = this.getFieldValue('email');
        }
        this.papayaTag.setAttr('text', altText);
        this.papayaTag.setAttr('title', this.getFieldValue('emailtitle'));
        this.papayaTag.setAttr('class', this.getFieldValue('emailcss'));
        this.papayaTag.name = 'link';
        this.papayaTag.setAttr('topic', '');
        this.papayaTag.setAttr('width', '');
        this.papayaTag.setAttr('height', '');
        this.papayaTag.setAttr('target', '_self');
        break;
      }
      if (this.papayaTag.elm) {
        this.editor.selection.select(this.papayaTag.elm);
      }
      this.editor.selection.setContent(this.papayaTag.getHTMLTag());
      tinyMCEPopup.close();
    },

    onBeforePageSwitch : function(newPageId) {
      switch (this.currentPanel) {
      case 'page_panel' :
        this.setFieldValue('urltext', this.getFieldValue('pageidtext'));
        this.setFieldValue('emailtext', this.getFieldValue('pageidtext'));
        this.setFieldValue('urltitle', this.getFieldValue('pageidtitle'));
        this.setFieldValue('emailtitle', this.getFieldValue('pageidtitle'));
        this.setFieldValue('urltarget', this.getFieldValue('pageidtarget'));
        this.setFieldValue('urlpopupuse', this.getFieldValue('pageidpopupuse'));
        this.setFieldValue('urlpopupwidth', this.getFieldValue('pageidpopupwidth'));
        this.setFieldValue('urlpopupheight', this.getFieldValue('pageidpopupheight'));
        this.setFieldValue('urlpopupscrollbars', this.getFieldValue('pageidpopupscrollbars'));
        this.setFieldValue('urlpopupresize', this.getFieldValue('pageidpopupresize'));
        this.setFieldValue('urlpopuptoolbar', this.getFieldValue('pageidpopuptoolbar'));
        break;
      case 'url_panel' :
        this.setFieldValue('pageidtext', this.getFieldValue('urltext'));
        this.setFieldValue('emailtext', this.getFieldValue('urltext'));
        this.setFieldValue('pageidtitle', this.getFieldValue('urltitle'));
        this.setFieldValue('emailtitle', this.getFieldValue('urltitle'));
        this.setFieldValue('pageidtarget', this.getFieldValue('urltarget'));
        this.setFieldValue('pageidpopupuse', this.getFieldValue('urlpopupuse'));
        this.setFieldValue('pageidpopupwidth', this.getFieldValue('urlpopupwidth'));
        this.setFieldValue('pageidpopupheight', this.getFieldValue('urlpopupheight'));
        this.setFieldValue('pageidpopupscrollbars', this.getFieldValue('urlpopupscrollbars'));
        this.setFieldValue('pageidpopupresize', this.getFieldValue('urlpopupresize'));
        this.setFieldValue('pageidpopuptoolbar', this.getFieldValue('urlpopuptoolbar'));
        break;
      case 'email_panel' :
        this.setFieldValue('pageidtext', this.getFieldValue('emailtext'));
        this.setFieldValue('urltext', this.getFieldValue('emailtext'));
        this.setFieldValue('pageidtitle', this.getFieldValue('emailtitle'));
        this.setFieldValue('urltitle', this.getFieldValue('emailtitle'));
        break;
      }
    },

    browsePages : function(element) {
      jQuery.papayaPopUp(
        {
          url : '../../../controls/link.php',
          width : '400',
          height : '500',
          name : 'papayaPageBrowser'
        }
      )
      .open()
      .done(
        function(pageId) {
          if (pageId) {
            $(element).val(pageId);
          }
        }
      );
    }
  }
);

PapayaLinkDialog = new PapayaLinkForm();
tinyMCEPopup.onInit.add(PapayaLinkDialog.init, PapayaLinkDialog);

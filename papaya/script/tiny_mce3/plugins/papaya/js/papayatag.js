PapayaTag = JsonClass(
  {
    elm : null,
    name : '',
    attr : {},

    patterns : {
      papayaTagName : /:([a-z]\w+)/i,

      serialized : /\[[^\]#]+#[^\]]+\]/g,

      attributes : /(^|\s)([a-z][a-z0-9_-]+)=((\'([^\']+)\')|(\"([^\"]+)\")|([^\s]))/ig,
      attributePair : /([a-z][a-z0-9_-]+)=((\'([^\']+)\')|(\"([^\"]+)\")|([^\s]))/i,

      htmlTag : /<(img|a)( [^>]*)?>(((.*)<\/\1>)?)/i,
      htmlWidth : /width=\"([^\"]+)\"/i,
      htmlHeight : /height=\"([^\"]+)\"/i,

      cssAttr : /style=\"([^\"]+)\"/i,
      cssWidth : /width:\s*((\d+)px)/i,
      cssHeight : /height:\s*((\d+)px)/i
    },

    htmlAttributeNames : {
      'class' : true,
      'height' : true,
      'width' : true,
      'src' : true,
      'href' : true,
      'target' : true,
      'name' : true,
      'id' : true,
      'data-papaya' : true,
      'margin' : true
    },

    dataAttribute : 'data-papaya',

    clearAttributes : function() {
      this.attr = new Object();
    },

    loadFromHTMLNode : function(elm) {
      this.clearAttributes();
      if (typeof elm != 'undefined' && elm) {
        this.elm = elm;

        //get html attributes
        var tagAttr = new Object();
        var attrName = null;
        var attrValue = '';
        for (var i = 0; i < elm.attributes.length; i++) {
          attrName = null;
          attrValue = '';
          if (elm.attributes[i].nodeName) {
            attrName = elm.attributes[i].nodeName;
            attrValue = elm.getAttribute(attrName);
          } else if (elm.attributes[i].name) {
            attrName = elm.attributes[i].name;
            attrValue = elm.attributes[i].value;
          }
          if (attrName) {
            if (this.htmlAttributeNames[attrName]) {
              tagAttr[attrName] = attrValue;
            }
          }
        }
        //load data from html attributes
        if (!this.loadFromHTMLAttr(tagAttr)) {
          if (elm.nodeName.toLowerCase() == 'a') {
            this.name = 'link';
            this.setAttr('text', PapayaUtils.text(elm));
            this.setAttr('href', tagAttr.href);
            if (typeof tagAttr.target != 'undefined' &&
                tagAttr.target != '') {
              this.setAttr('target', tagAttr.target);
            }
          }
        }
        if (this.name == 'file' &&
            this.getAttr('dyn_autocaption', '0') != '1') {
          this.setAttr('text', PapayaUtils.text(elm));
        }
        if (this.name == 'addon') {
          this.setAttr('text', PapayaUtils.text(elm));
        }
        return true;
      }
      return false;
    },

    loadFromHTMLTag : function(s) {
      this.clearAttributes();
      var tagData = s.match(this.patterns.htmlTag);
      var tagName = tagData[1];
      var tagAttrStr = tagData[2];
      var tagText = tagData[3];

      //get html attributes
      var tagAttr = new Object();
      var attrPairs = s.match(this.patterns.attributes);
      if (attrPairs) {
        for (var i = 0 ; i < attrPairs.length ; i++) {
          var attrPair = attrPairs[i].match(this.patterns.attributePair);
          var c1 = attrPair[2].substr(0, 1);
          var c2 = attrPair[2].substr(attrPair[2].length - 1);
          if ((c1 == '"' || c1 == "'") && c1 == c2) {
            tagAttr[attrPair[1]] = attrPair[2].substr(1, attrPair[2].length - 2);
          } else {
            tagAttr[attrPair[1]] = attrPair[2];
          }
        }
      }
      //load data from html attributes
      if (!this.loadFromHTMLAttr(tagAttr)) {
        //load data from a normal link
        if (tagName.toLowerCase() == 'a') {
          this.name = 'link';
          this.setAttr('text', this.getTextContent(s));
          this.setAttr('href', tagAttr.href);
          if (typeof tagAttr.target != 'undefined' &&
              tagAttr.target != '') {
            this.setAttr('target', tagAttr.target);
          }
        }
      } else {
        switch (this.name) {
        case 'file' :
          //capion-stuff
          if (this.getAttr('dyn_autocaption', '0') != '1') {
            this.setAttr('text', this.getTextContent(s));
          }
          break;
        case 'link' :
        case 'popup' :
          if (this.getAttr('altmode') == 'yes' || this.hasAttr('href')) {
            this.setAttr('text', this.getTextContent(s));
          }
          break;
        case 'addon' :
          this.setAttr('text', this.getTextContent(s));
          break;
        }
      }
    },

    getTextContent : function(s) {
      var oRegEx = s.match( />([^<]+)</ );
      if (oRegEx) {
        return oRegEx[1];
      }
      return '';
    },

    loadFromHTMLAttr : function(tagAttr) {
      if (tagAttr[this.dataAttribute]) {
        this.loadFromAttrString(tagAttr[this.dataAttribute]);
        switch (this.name) {
        case 'media' :
          if (this.getAttr('download', 'no') == 'no') {
            //size stuff
            var newSize = this.loadSizeFromHTML(tagAttr);
            if ((this.getAttr('dyn_width', 0) != newSize.width) ||
                (this.getAttr('dyn_height', 0) != newSize.height)) {
              this.setAttr('resize', 'abs');
              this.setAttr('width', newSize.width);
              this.setAttr('height', newSize.height);
              this.setAttr('dyn_width', newSize.dyn_width);
              this.setAttr('dyn_height', newSize.dyn_height);
            }
            break;
          } else {
            this.name = 'file';
          }
          break;
        }
        return true;
      }
      return false;
    },

    loadSizeFromHTML : function(tagAttr) {
      var css = tagAttr.style;
      var result = {
        width : parseInt(this.attr.width || 0, 10),
        height : parseInt(this.attr.height || 0, 10),
        dyn_width : parseInt(this.attr.width || 0, 10),
        dyn_height : parseInt(this.attr.height || 0, 10)
      };
      if (this.attr.dyn_width) {
        result.dyn_width = parseInt(this.attr.dyn_width || 0, 10);
      }
      if (this.attr.dyn_height) {
        result.dyn_height = parseInt(this.attr.dyn_height || 0, 10);
      }
      if (tagAttr.width) {
        result.width = parseInt(tagAttr.width, 10);
      }
      if (tagAttr.height) {
        result.height = parseInt(tagAttr.height, 10);
      }
      if (css) {
        var cssAttr = null;
        if (css[1]) {
          if (cssAttr = css[1].match(this.patterns.cssWidth)) {
            result.width = parseInt(cssAttr[2], 10);
          }
          if (cssAttr = css[1].match(this.patterns.cssHeight)) {
            result.height = parseInt(cssAttr[2], 10);
          }
        }
      }
      return result;
    },

    loadFromAttrString : function(s) {
      this.clearAttributes();
      this.name = s.substring(0, s.indexOf('#'));
      if (this.name != '') {
        var attrPairs = s.match(this.patterns.serialized);
        if (attrPairs) {
          for (var i = 0 ; i < attrPairs.length ; i++) {
            var attrPair = new Array();
            attrPair[0] = attrPairs[i].substring(1, attrPairs[i].indexOf("#"));
            attrPair[1] = attrPairs[i].substring(attrPairs[i].indexOf("#")+1, attrPairs[i].length-1);
            this.attr[decodeURI(attrPair[0])] = decodeURI(attrPair[1]);
          }
        }
        return true;
      }
      return false;
    },

    loadFromPapayaTag : function(s) {
      this.clearAttributes();
      var r = s.match(this.patterns.papayaTagName);
      if (r) {
        this.name = r[1];
        var attrPairs = s.match(this.patterns.attributes);
        if (attrPairs) {
          for (var i = 0 ; i < attrPairs.length ; i++) {
            var attrPair = attrPairs[i].match(this.patterns.attributePair);
            var c1 = attrPair[2].substr(0, 1);
            var c2 = attrPair[2].substr(attrPair[2].length - 1);
            if ((c1 == '"' || c1 == "'") && c1 == c2) {
              this.attr[attrPair[1]] = attrPair[2].substr(1, attrPair[2].length - 2);
            } else {
              this.attr[attrPair[1]] = attrPair[2];
            }
          }
        }
      }
    },

    hasAttr : function (key) {
      return (this.attr[key] != null && this.attr[key] != '');
    },

    getAttr : function (key, defaultVal) {
      if (this.attr[key] != null && this.attr[key] != '') {
        if (typeof defaultVal == 'number') {
          var i = parseInt(this.attr[key], 10);
          if (isNaN(i)) {
            return defaultVal;
          } else {
            return i;
          }
        } else {
          return this.attr[key];
        }
      } else {
        return defaultVal;
      }
    },

    setAttr : function (key, val) {
      if (key.substr(0,4) != 'mce_') {
        this.attr[key] = String(val);
        return true;
      }
      return false;
    },

    mergeDynamicAttr : function(attr) {
      if (typeof attr != 'undefined') {
        for (var paramName in attr) {
          if (String(paramName).substr(0, 4) == 'dyn_') {
            this.attr[paramName] = attr[paramName];
          }
        }
      }
    },

    getAttrString : function() {
      result = this.name + '#';
      for (key in this.attr) {
        if (this.attr[key] != null && this.attr[key] != '') {
          result += '[' + encodeURI(key) + '#' + encodeURI(this.attr[key]) + ']';
        }
      }
      return result;
    },

    getHTMLTag : function() {
      if (this.name) {
        switch (this.name) {
        case 'link' :
          return this.getHTMLTagLink();
        case 'popup' :
          return this.getHTMLTagPopup();
        case 'file' :
          return this.getHTMLTagFile();
        case 'media' :
          return this.getHTMLTagMedia();
        case 'image' :
          return this.getHTMLTagImage();
        case 'addon' :
          return this.getHTMLTagAddon();
        }
      }
      return '';
    },

    getHTMLTagLink : function() {
      html = '<a href="#" '+ this.dataAttribute + '="' + this.getAttrString() + '"';
      if (this.getAttr('dyn_exists', 0) == 1) {
        switch (this.getAttr('altmode')) {
        case 'yes' :
          caption =  this.getAttr('text', this.getAttr('dyn_title', ''));
          break;
        case 'no' :
          caption =  this.getAttr('dyn_title', '');
          break;
        default :
          caption =  this.getAttr('dyn_title', '');
        }
      } else if (this.hasAttr('href')) {
        caption =  this.getAttr('text', this.getAttr('href', ''));
      } else {
        caption = this.getAttr('text', 'invalid link');
      }
      html += ' class="papayaLink">'+caption+'</a>';
      return html;
    },

    getHTMLTagPopup : function() {
      html = '<a href="#" '+ this.dataAttribute + '="' + this.getAttrString() + '"';
      if (this.getAttr('dyn_exists', 0) == 1) {
        switch (this.getAttr('altmode')) {
        case 'yes' :
          caption =  this.getAttr('text', this.getAttr('dyn_title', ''));
          break;
        case 'no' :
          caption =  this.getAttr('dyn_title', '');
          break;
        default :
          caption =  this.getAttr('dyn_title', '');
        }
      } else if (this.hasAttr('href')) {
        caption =  this.getAttr('text', this.getAttr('href', ''));
      } else {
        caption = 'invalid link';
      }
      html += ' class="papayaPopup">'+PapayaUtils.HTMLCharEncode(caption)+'</a>';
      return html;
    },

    getHTMLTagFile : function() {
      if (this.hasAttr('text')) {
        caption = this.getAttr('text');
        this.setAttr('dyn_autocaption', 0);
      } else {
        caption = this.getHTMLTagFileCaption();
        this.setAttr('dyn_autocaption', 1);
      }
      return '<a href="#" '+ this.dataAttribute +'="'+this.getAttrString()+
        '" class="papayaFile">'+caption+'</a>';
    },

    getHTMLTagFileCaption : function() {
      if (this.hasAttr('dyn_title')) {
        return this.getAttr('dyn_title') + ' (' +
          PapayaUtils.formatFileSizeToStr(this.getAttr('dyn_size', 0))+')';
      } else {
        return this.getAttr('dyn_file') + ' (' +
          PapayaUtils.formatFileSizeToStr(this.getAttr('dyn_size', 0))+')';
      }
    },

    getHTMLTagMedia : function() {
      var html = '';
      var attrDownload = this.getAttr('download', 'no');
      var attrType = this.getAttr('dyn_type', 0);
      var mediaTypes = [1, 2, 3, 4, 13];
      function getSize(name, size) {
        if (typeof size == 'number' && size > 0) {
          return '" ' + name + '="' + size;
        }
        return '';
      }
      if (attrDownload == 'no' && PapayaUtils.inArray(attrType, mediaTypes)) {
        if (this.getAttr('dyn_exists', 0) == 1) {
          switch (attrType) {
          case 1:
          case 2:
          case 3:
            html = '<img src="' + this.getAttr('dyn_src') +
              '" ' + this.dataAttribute + '="'+this.getAttrString() +
              getSize('width', this.getAttr('width', 0)) +
              getSize('height', this.getAttr('height', 0)) +
              '" style="'+this.getHTMLTagMediaCSS() +
              '" class="papayaMedia" />';
            break;
          case 4:
          case 13:
            html = '<img src="'+this.base_url+'pics/tpoint.gif" ' +
              this.dataAttribute + '="' + this.getAttrString() +
              getSize('width', this.getAttr('width', 0)) +
              getSize('height', this.getAttr('height', 0)) +
              '" style="'+this.getHTMLTagMediaCSS()+
              '" class="papayaMediaFlash" />';
            break;
          }
        } else {
          html = '<img src="pics/icons/48x48/status/dialog-warning.png" ' +
            this.dataAttribute+ '="' + this.getAttrString() +
            '" width="48" height="48" class="papayaMedia" />';
        }
        return html;
      } else {
        this.name = 'file';
        return this.getHTMLTagFile();
      }
    },

    getHTMLTagMediaCSS : function() {
      var s = '';
      switch (this.getAttr('align')) {
      case 'center' :
        s += 'display: block; ';
        s += 'margin-left: auto; ';
        s += 'margin-right: auto; ';
        s += 'margin-top: ' + this.getAttr('tspace', 0) + 'px; ';
        s += 'margin-bottom: ' + this.getAttr('bspace', 0) + 'px; ';
        break;
      case 'left' :
      case 'right' :
        s += 'display: block; ';
        s += 'float: '+this.getAttr('align')+'; ';
      case 'middle' :
      default :
        s += 'margin: ' + this.getAttr('tspace', 0) +' '+
          this.getAttr('rspace', 0)+' '+
          this.getAttr('bspace', 0)+' '+
          this.getAttr('lspace', 0)+'; ';
      }
      return s;
    },

    getHTMLTagImage : function() {
      return '<img src="'+this.getAttr('dyn_src')+
         '" ' + this.dataAttribute+ '="'+this.getAttrString()+'" class="papayaImage" />';
    },

    getImageRequestURL : function() {
      var attrString = '';
      for (var i in this.attr) {
        attrString += '&img[' + escape(i) + ']=' + escape(this.attr[i]);
      }
      return this.attr['image'] + '.image.png?' + attrString.substring(1, attrString.length);
    },

    getHTMLTagAddon : function() {
      var s = '<a href="#" ' + this.dataAttribute+ '="' + this.getAttrString() + '"';
      if (this.hasAttr('text') && this.getAttr('text') != '') {
        caption = this.getAttr('text');
      } else if (this.hasAttr('dyn_text') && this.getAttr('dyn_text') != '') {
        caption = this.getAttr('dyn_text');
      } else {
        caption = 'addon';
      }
      s += ' class="papayaAddOn">'+caption+'</a>';
      return s;
    },

    getPapayaTag : function() {
      if (this.name) {
        var s = '<papaya:'+PapayaUtils.HTMLCharEncode(this.name);
        for (var paramName in this.attr) {
          if (String(paramName).substr(0,4) != 'dyn_') {
            var paramValue = this.getAttr(paramName, '');
            if (paramValue != '' && paramValue != '0') {
              s += ' ' + PapayaUtils.HTMLCharEncode(paramName) +
                   '="' + PapayaUtils.HTMLCharEncode(paramValue) + '"';
            }
          }
        }
        s += ' />';
        return s;
      } else {
        return '';
      }
    }
  }
);

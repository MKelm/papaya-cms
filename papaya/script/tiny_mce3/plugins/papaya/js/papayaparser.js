PapayaParser = JsonClass(
  'rpcURL',
  {
    rpcURL : '',

    patterns : {
      papayaTag : /<papaya:([a-z]\w+)\s?([^>]*)((\/)?)>/ig,
      htmlTagImg : /<img( [^>]*)?\/?>/ig,
      htmlTagA : /<a( [^>]*href=[^>]+)?>([^<]+|<[^\/]|<\/[^a]|<\/a[^>])*<\/a>/ig
    },

    data : {
      media : { },
      pages : { }
    },

    options : {
      linkTargetDefault : '_self'
    },

    toPapaya : function(s) {
      var buffer;
      var offset;
      var tagHTML;
      var tagStr;
      var tagStart;
      var tagLength;
      var i;

      var imgTags = s.match(this.patterns.htmlTagImg);
      if (imgTags) {
        buffer = '';
        offset = 0;
        for (i = 0 ; i < imgTags.length ; i++) {
          tagHTML = imgTags[i];
          tagStr = this.elmHTMLtoPapaya(tagHTML);
          tagStart = s.indexOf(tagHTML, offset);
          tagLength = tagHTML.length;
          buffer += s.substring(offset, tagStart) + tagStr;
          offset = tagStart + tagLength;
        }
        s = buffer + s.substring(offset);
      }
      var linkTags = s.match(this.patterns.htmlTagA);
      if (linkTags) {
        buffer = '';
        offset = 0;
        for (i = 0 ; i < linkTags.length ; i++) {
          tagHTML = linkTags[i];
          tagStr = this.elmHTMLtoPapaya(tagHTML);
          tagStart = s.indexOf(tagHTML, offset);
          tagLength = tagHTML.length;
          buffer += s.substring(offset, tagStart) + tagStr;
          offset = tagStart + tagLength;
        }
        s = buffer + s.substring(offset);
      }
      s = s.replace(/<p><\/p>/ig, '');
      return s;
    },

    toHTML : function(s) {
      var papayaTags = s.match(this.patterns.papayaTag);
      if (papayaTags) {
        var result = '';
        var offset = 0;
        for (var i = 0 ; i < papayaTags.length ; i++) {
          var tagStr = papayaTags[i];
          var tagHTML = this.elmPapayatoHTML(tagStr);
          var tagStart = s.indexOf(tagStr, offset);
          var tagLength = tagStr.length;
          result += s.substring(offset, tagStart) + tagHTML;
          offset = tagStart + tagLength;
        }
        result += s.substring(offset);
        return result;
      }
      return s;
    },

    elmHTMLtoPapaya : function(s) {
      var tag = new PapayaTag();
      tag.loadFromHTMLTag(s);
      if (!tag.hasAttr('target')) {
        tag.setAttr('target', this.options.linkTarget);
      }
      if (tag.name == 'link' && tag.attr.text == '') {
        return '';
      }
      this.rememberData(tag);
      var tagString = tag.getPapayaTag();
      if (tagString != '') {
        return tagString;
      } else {
        return s;
      }
    },

    elmPapayatoHTML : function(s) {
      var tag = new PapayaTag();
      tag.loadFromPapayaTag(s);
      this.rememberData(tag);
      return tag.getHTMLTag();
    },

    rememberData : function(tag) {
      switch (tag.name) {
      case 'media' :
      case 'file' :
        if (tag.hasAttr('src')) {
          var attrSrc = this.getMediaIdFromSource(tag.getAttr('src'));
          if (!this.data.media[attrSrc]) {
            this.data.media[attrSrc] = [];
            this.loadMediaData(attrSrc);
          }
          tag.mergeDynamicAttr(this.data.media[attrSrc]);
        }
        break;
      case 'image' :
        tag.setAttr('dyn_src', '../../../../' + tag.getImageRequestURL());
      case 'addon' :
        break;
      case 'link' :
      case 'popup' :
        if (tag.hasAttr('topic')) {
          var attrTopic = tag.getAttr('topic', 0);
          if (!this.data.pages[attrTopic]) {
            this.data.pages[attrTopic] = [];
            this.loadPageData(attrTopic);
          }
          tag.mergeDynamicAttr(this.data.pages[attrTopic]);
        }
        break;
      }
    },

    loadMediaData : function(mediaSrc) {
      loadXMLDoc(this.rpcURL + '?rpc[cmd]=media_data&rpc[media_id]=' + encodeURI(mediaSrc) + '&random=' + Math.round(Math.random()*1000), false, this);
    },

    rpcSetMediaData : function(data, params) {
      var responseParams = xmlParamNodesToArray(params);
      if (responseParams.src && responseParams.src != '') {
        if (!this.data.media[responseParams.src]) {
          this.data.media[responseParams.src] = [];
        }
        this.data.media[responseParams.src].dyn_src = responseParams.dyn_src;
        this.data.media[responseParams.src].dyn_title = responseParams.dyn_title;
        this.data.media[responseParams.src].dyn_file = responseParams.dyn_file;
        this.data.media[responseParams.src].dyn_size = responseParams.dyn_size;
        this.data.media[responseParams.src].dyn_mimetype = responseParams.dyn_mimetype;
        this.data.media[responseParams.src].dyn_orgwidth = responseParams.dyn_orgwidth;
        this.data.media[responseParams.src].dyn_orgheight = responseParams.dyn_orgheight;
        this.data.media[responseParams.src].dyn_type = responseParams.dyn_type;
        this.data.media[responseParams.src].dyn_exists = 1;
      }
    },

    loadPageData : function(topicId) {
      loadXMLDoc(this.rpcURL + '?rpc[cmd]=page_data&rpc[page_id]=' + parseInt(topicId, 10) + '&random=' + Math.round(Math.random()*1000), false, this);
    },

    rpcSetPageData : function(data, params) {
      var responseParams = xmlParamNodesToArray(params);
      var topicId = parseInt(responseParams.topic, 10);
      if (topicId > 0) {
        if (!this.data.pages[topicId]) {
          this.data.pages[topicId] = [];
        }
        this.data.pages[topicId].dyn_title = responseParams.dyn_title;
        this.data.pages[topicId].dyn_exists = 1;
      }
    },

    getMediaIdFromSource: function(source) {
      return source.substring(0, 32);
    }
  }
);

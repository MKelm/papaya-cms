function papayaParser () {
  this.tags = new Array();
  this.pattern_papaya_tag = /<(ndim|papaya):([a-z]\w+)\s?([^>]*)((\/)?)>/ig;
  this.pattern_papaya_attrs = /(^|\s)([a-z]\w+)=((\'([^\']+)\')|(\"([^\"]+)\")|([^\s]))/ig;
  this.pattern_papaya_attrpair = /([a-z]\w+)=((\'([^\']+)\')|(\"([^\"]+)\")|([^\s]))/i;  
  this.pattern_html_tag_img = /<img([^>]+)(id="papaya_(\d+)")([^>]*)\/?>/ig;
  this.pattern_html_tag_a = /<a([^>]+)(id="papaya_(\d+)")([^>]*)>([^<]+|<[^\/]|<\/[^a]|<\/a[^>])*<\/a>/ig;
  this.pattern_html_attr = /<((img)|(a))([^>]+)(id="papaya_(\d+)")([^>]*)>(((.*)<\/\1>)?)/i;
  this.pattern_cssattr = /style=\"([^\"]+)\"/i;
  this.pattern_css_width = /width:\s*((\d+)px)/i;
  this.pattern_css_height = /height:\s*((\d+)px)/i;
  this.pattern_html_width = /width=\"([^\"]+)\"/i;
  this.pattern_html_height = /height=\"([^\"]+)\"/i;
}

papayaParser.prototype.toPapaya = function(str, editorId) {
  var i = 0;
  var result = str;
  var htmltags = null;
  
  htmltags = str.match(this.pattern_html_tag_img);
  if (htmltags) {
    for (i=0;i<htmltags.length;i++) {
      result = result.split(htmltags[i]).join(this.getPapayaFromHTML(htmltags[i], editorId));      
    }  
  }
  
  htmltags = str.match(this.pattern_html_tag_a);
  if (htmltags) {
    for (i=0;i<htmltags.length;i++) {
      result = result.split(htmltags[i]).join(this.getPapayaFromHTML(htmltags[i], editorId));      
    }  
  }
  return result;
}

papayaParser.prototype.getPapayaFromHTML = function(htmltag, editorId) {
  var data = htmltag.match(this.pattern_html_attr);
  var element = null;
  var ptag = '';
  
  if (data && this.tags[editorId]) {
    element = this.tags[editorId][data[6]];
    if (element) {
      switch (element.tagname) {
        case 'media' :
          if (element.attr.download == 'yes') {
            //capion-stuff
            var oRegEx = htmltag.match( />([^<]+)</ );
            element.attr.text = oRegEx[1];
          } else {
            //size stuff
            var newSize = this.getSizeFromHTML(htmltag);
            if ((element.attr.dyn_width != newSize.width) && (element.attr.dyn_height != newSize.height)) {
              element.attr.resize = 'abs';
              element.attr.width = newSize.width;
              element.attr.height = newSize.height;
              element.attr.dyn_width = newSize.width;
              element.attr.dyn_height = newSize.height;
            } 
          }
          break;
        case 'image' :
        case 'addon' :
          //nothing to do
          break;
        case 'link' :
        case 'popup' :
          element.attr['alttext'] = null;
          if (element.attr['altmode'] == 'yes') {
            var oRegEx = htmltag.match( />([^<]+)</ );
            element.attr.text = oRegEx[1];
          }
          break; 
      }    
      ptag = this.getPapayaTagStr(element, false, false);
      return ptag;
    }
  }
}

papayaParser.prototype.getSizeFromHTML = function(htmltag) {
  var result = new Array();
  result.width = 0;
  result.height = 0;
  
  var css = htmltag.match(this.pattern_cssattr);
  var html_width = htmltag.match(this.pattern_html_width);
  var html_height = htmltag.match(this.pattern_html_height);
  if (html_width && html_height) {
    result.width = html_width[1];
    result.height = html_height[1];
  } else if (css) {
    var css_attr = null;
    if (css_attr = css[1].match(this.pattern_css_width)) {
      result.width = css_attr[2];
    }
    if (css_attr = css[1].match(this.pattern_css_height)) {
      result.height = css_attr[2];
    }
  }
  return result;
}

papayaParser.prototype.toXHTML = function(str, editorId) {
  var element = null;
  var attr = null;
  var i = 0;
  var result = '';
  
  if (!this.tags) this.tags = new Array();
  if (!this.tags[editorId]) this.tags[editorId] = new Array();
  
  var tags = str.match(this.pattern_papaya_tag);
  if (tags) {
    for (i=0;i<tags.length;i++) {
      this.addPapayaTag(tags[i], editorId);
    }
    result = str;
    for (i=0;i<this.tags[editorId].length;i++) {
      result = result.split(this.tags[editorId][i].tagstr).join(this.tags[editorId][i].html);
    }
    return result;
  } else {
    return str;
  }
}

papayaParser.prototype.stripDynamicAttributes = function(str) {
  var taglist = new Array();
  var element = null;
  var attr = null;
  var i = 0;
  var result = '';
  
  var tags = str.match(this.pattern_papaya_tag);
  if (tags) {
    for (i=0;i<tags.length;i++) {
      var idx = taglist.length;
      taglist[idx] = this.parsePapayaTag(tags[i], idx);
    }
    result = str;
    for (i=0;i<taglist.length;i++) {
      var papayaStr = this.getPapayaTagStr(taglist[i], false, false);
      result = result.split(taglist[i].tagstr).join(papayaStr);
    }
    return result;
  } else {
    return str;
  }
}

papayaParser.prototype.getPapayaTagStr = function(element, includeDynamic, includeDefaultValues) {
  var ptag = '<papaya:'+this.HTMLCharEncode(element.tagname);
  for (var paramname in element.attr) {
    if (element.attr[paramname] != null && (includeDynamic || paramname.substr(0,4) != 'dyn_')) {
      if (includeDynamic || includeDefaultValues) {
        ptag += ' '+this.HTMLCharEncode(paramname)+'="'+this.HTMLCharEncode(element.attr[paramname])+'"';
      } else {
        if (!(element.attr[paramname] == '' || String(element.attr[paramname]) == '0')) {
          ptag += ' '+this.HTMLCharEncode(paramname)+'="'+this.HTMLCharEncode(element.attr[paramname])+'"';          
        }
      }
    }
  }
  ptag += ' />';
  return ptag;
}

papayaParser.prototype.addPapayaTag = function(tagstr, editorId) {
  var idx = this.tags[editorId].length;
  this.tags[editorId][idx] = this.parsePapayaTag(tagstr, idx);
  return this.tags[editorId][idx].html;
}

papayaParser.prototype.parsePapayaTag = function(tagstr, counter) {
  var element = new Array();
  var tagname = tagstr.match(/:([a-z]\w+)/i);
  element.tagstr = tagstr;
  element.tagname = tagname[1];
  element.id = 'papaya_'+counter;
      
  var attr = new Array();
  attrs = tagstr.match(this.pattern_papaya_attrs);
  if (attrs) {
    for (k=0;k<attrs.length;k++) {
      attrmatch = attrs[k].match(this.pattern_papaya_attrpair);
      attr[attrmatch[1]] = this.HTMLCharDecode(this.removeQuotes(attrmatch[2]));
    }      
  }
  element.attr = attr;
  
  //create editable html 
  this.createHTML(element);
  
  return element;
}

papayaParser.prototype.removeQuotes = function (str) {
  if (str.length > 0) {
    if ((str.charAt(0) == '"') || (str.charAt(0) == "'")) {
      return str.substr(1,str.length-2);
    }
  }
  return str;
}

papayaParser.prototype.createHTML = function(element) {
  var i = 0;
  var html = '';
  var caption = '';
  if (element) {
    html = '';
    switch (element.tagname) {
      case 'media' :
        if (element.attr.download == 'yes' || element.attr.dyn_type <= 0 || element.attr.dyn_type > 4) {
          if (element.attr.text && element.attr.text != '') {
            caption =  element.attr.text;
            element.autoCaption = null;
          } else {
            caption = this.createDownloadCaption(element.attr.dyn_file, element.attr.dyn_title, element.attr.dyn_size);
            element.autoCaption = caption;
          }
          html = '<a href="#" id="'+element.id+'" class="papayaMedia">'+caption+'</a>';
        } else {
          switch (element.attr.dyn_type) {
            case '1':
            case '2':
            case '3':
              html = '<img src="'+element.attr.dyn_src+'" id="'+element.id+
                '" width="'+element.attr.dyn_width+
                '" height="'+element.attr.dyn_height+
                '" style="'+this.createCSS(element)+
                '" class="papayaMedia" />';
              break;
            case '4':
              html = '<img src="'+this.base_url+'pics/flashdummy.gif" id="'+element.id+
                '" width="'+element.attr.dyn_width+
                '" height="'+element.attr.dyn_height+
                '" style="'+this.createCSS(element)+
                '" class="papayaMedia" />';
              break;
          } 
        }
        element.html = html;
        break;
      case 'image' :
        element.html = '<img src="'+element.attr.dyn_src+'" id="'+element.id+'" class="papayaImage" />';
        break;
      case 'addon' :
        html = '<a href="#" id="'+element.id+'"';
        if ((element.attr.dyn_exists == 1) && (element.attr.dyn_text != '')) {
          caption = element.attr.dyn_text;
        } else {
          caption = 'addon';
        }
        html += ' class="papayaAddOn">'+caption+'</a>';
        element.html = html;
        break;
      case 'link' :
        html = '<a href="#" id="'+element.id+'"';
        if (element.attr.dyn_exists == 1) {
          switch (element.attr.altmode) {
            case 'yes' :
		  		    caption =  (element.attr.text) ? element.attr.text : element.attr.dyn_title;
              break;
            case 'no' :
    			    caption =  element.attr.dyn_title;
              break;
            default :
       			  caption =  element.attr.dyn_title;
          }
        } else if (element.attr.href) {
          caption =  (element.attr.text) ? element.attr.text : element.attr.href;
        } else {
          caption = 'invalid link';
        }
        html += ' class="papayaLink">'+caption+'</a>';
        element.html = html;
        break;
      case 'popup' : 
        html = '<a href="#" id="'+element.id+'"';
        if (element.attr.dyn_exists == 1) {
          switch (element.attr.altmode) {
            case 'yes' :
		  		    caption =  (element.attr.text) ? element.attr.text : element.attr.dyn_title;
              break;
            case 'no' :
    			    caption =  element.attr.dyn_title;
              break;
            default :
       			  caption =  element.attr.dyn_title;
          }
        } else if (element.attr.href) {
          caption = (element.attr.text) ? element.attr.text : element.attr.href;
        } else {
          caption = 'invalid link';
        }
	  		html += ' class="papayaPopup">'+this.HTMLCharEncode(caption)+'</a>';
        element.html = html;
        break;
    }
  }
}

papayaParser.prototype.createCSS = function(element) {
  var sCSS = '';
  switch (element.attr.align) {
    case 'center' :
      sCSS += 'display: block; ';
      sCSS += 'margin-left: auto; ';
      sCSS += 'margin-right: auto; ';
      sCSS += 'margin-top: '+this.stringToInteger(element.attr.tspace, 0)+'px; ';
      sCSS += 'margin-bottom: '+this.stringToInteger(element.attr.bspace, 0)+'px; ';
      break;
    case 'left' :
    case 'right' :
      sCSS += 'display: block; ';
      sCSS += 'float: '+element.attr.align+'; ';
    case 'middle' :
    default :
      sCSS += 'margin: '+this.stringToInteger(element.attr.tspace, 0)+' '+
        this.stringToInteger(element.attr.rspace, 0)+' '+
        this.stringToInteger(element.attr.bspace, 0)+' '+
        this.stringToInteger(element.attr.lspace, 0)+'; ';
  }
  return sCSS;
}

papayaParser.prototype.createDownloadCaption = function (fileName, fileTitle, fileSize) {
  if (fileTitle && fileTitle != '') {
    return fileTitle + ' (' + this.formatFileSizeToStr(fileSize)+')';       
  } else {
    return fileName + ' (' + this.formatFileSizeToStr(fileSize)+')';
  }
}

papayaParser.prototype.calcSizeData = function(element) {
  var iOrgWidth = this.stringToInteger(element.attr.dyn_orgwidth, 0);
  var iOrgHeight = this.stringToInteger(element.attr.dyn_orgheight, 0);
  var iThumbWidth = this.stringToInteger(element.attr.width, 0);
  var iThumbHeight = this.stringToInteger(element.attr.height, 0);
  if (iThumbWidth <= 0 || iThumbHeight <= 0) {
    element.attr.dyn_width = (iOrgWidth > 0) ? iOrgWidth : null;
    element.attr.dyn_height = (iOrgHeight > 0) ? iOrgHeight : null;     
    return '';
  } else if (iOrgWidth <= 0 || iOrgHeight <= 0) {
    element.attr.dyn_width = (iThumbWidth > 0) ? iThumbWidth : null;
    element.attr.dyn_height = (iThumbHeight > 0) ? iThumbHeight : null;     
    return '';
  }    
  var iDivWidth = iOrgWidth / iThumbWidth;
  var iDivHeight = iOrgHeight / iThumbHeight;
  switch (element.attr.resize) {
    case 'abs' : 
      element.attr.dyn_width = iThumbWidth;
      element.attr.dyn_height = iThumbHeight;
      break;
    case 'min' :
      if (iDivWidth >= iDivHeight) {
        element.attr.dyn_width = iThumbWidth;
        element.attr.dyn_height = (iOrgHeight / iDivWidth);
      } else {
        element.attr.dyn_height = iThumbHeight;
        element.attr.dyn_width = (iOrgWidth / iDivHeight);
      }    
      break;
    case 'mincrop' :
      element.attr.dyn_width = iThumbWidth;
      element.attr.dyn_height = iThumbHeight;    
      break;
    case 'max' :
    default :
      if (iDivWidth >= iDivHeight) {
        element.attr.dyn_width = iThumbWidth;
        element.attr.dyn_height = (iOrgHeight / iDivWidth);
      } else {
        element.attr.dyn_height = iThumbHeight;
        element.attr.dyn_width = (iOrgWidth / iDivHeight);
      }
  }
}

papayaParser.prototype.getTag = function (editorId, elementId) {
  var idx = elementId.substring(7,elementId.length);
  if (this.tags[editorId]) {
    if (this.tags[editorId][idx]) {
      return this.tags[editorId][idx];
    }
  }
}

papayaParser.prototype.setTag = function (editorId, element) {
  if (element) {
    if (!element.id) {
      element.id = 'papaya_'+this.tags[editorId].length;
    }
    var idx = element.id.substring(7,element.id.length);
    this.tags[editorId][idx] = element;
    this.createHTML(element);
  }
}

papayaParser.prototype.stringToInteger = function(strValue, defaultValue) {
  var result = parseInt(strValue);
  if (isNaN(result)) {
    return defaultValue;
  } else {
    return result;
  }
}

papayaParser.prototype.HTMLCharEncode = function(text) {
  var result = String(text);
	result = result.replace( /\"/g, "&quot;");
	result = result.replace( /</g, "&lt;");
	result = result.replace( />/g, "&gt;");
	return result;
}

papayaParser.prototype.HTMLCharDecode = function(text) {
  var result = String(text);
	result = result.replace( /&quot;/g, '"');
	result = result.replace( /&lt;/g, "<");
	result = result.replace( /&gt;/g, ">");
	return result;
}

papayaParser.prototype.formatFileSizeToStr = function (size) {
  if (size > 10000000000) {
    return (Math.round(size / 1073741824 * 100) / 100)+' GB';
  } else if (size > 10000000) {
    return (Math.round(size / 1048576 * 100) / 100)+' MB';
  } else if (size > 10000) {
    return (Math.round(size / 1024 * 100) / 100)+' kB';
  } else {
    return Math.round(size)+' Bytes';    
  }
}

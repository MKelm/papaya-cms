var tinyMCELoading = {
  plugins : "papaya,lists,tabfocus,autoresize,directionality,fullscreen,media,paste,save,searchreplace,table,visualblocks,xhtmlxtras",
  themes : "advanced",
  languages : 'en',
  disk_cache : true,
  debug : false,
  strict_loading_mode: true
};

var tinyMCEOptionsSimple = {
  mode : "textareas",
  editor_selector : "dialogSimpleRichtext",
  exec_mode : "src",
  strict_loading_mode: true,
  language : 'en',
  papayaParser : {
    linkTarget : '_self'
  },

  extended_valid_elements : "img[data-papaya|class|style|src|width|height|alt|title|align|hspace|vspace],a[data-papaya|class|href|target|name],div[data-papaya|class|style|id],audio[autobuffer|autoplay|controls|src],iframe[src|width|height|align|hspace|vspace|scrolling|name|frameborder|style]",

  plugins : "papaya,lists,tabfocus,autoresize,directionality,fullscreen,media,paste,save,searchreplace,table,visualblocks,xhtmlxtras",

  theme : "advanced",
  theme_advanced_buttons1 : "save,undo,redo,|, code,fullscreen,visualblocks,| ,search,replace,pastetext,cleanup,|, ltr,rtl,|, removeformat,bold,italic,formatselect",
  theme_advanced_buttons2 : "media,|, papayaLink,unlink,anchor,papayaFile,papayaMedia,papayaImage,papayaAddon",
  theme_advanced_buttons3 : "",
  theme_advanced_toolbar_location : "top",
  theme_advanced_toolbar_align : "left",
  theme_advanced_path_location : "bottom",
  theme_advanced_blockformats : "p,div,h1,h2,h3",

  theme_advanced_resizing : true,
  theme_advanced_resize_horizontal : false,
  accessibility_focus : false,
  //dialog_type : 'modal',

  paste_auto_cleanup_on_paste : true,
  paste_text_sticky : true,
  paste_remove_styles: true,
  paste_strip_class_attributes: 'mso',
  paste_postprocess : function (pl, o) {
    PapayaTinyMCEHandler.onBeforePaste(pl, o);
  },

  entity_encoding : "raw",
  apply_source_formatting : false,
  remove_linebreaks : true,
  save_onsavecallback : function(ed) {
    PapayaTinyMCEHandler.submit(ed);
  },
  onchange_callback : function(ed) {
    PapayaTinyMCEHandler.onChange(ed);
  }
};

var tinyMCEOptionsFull = {
  mode : "textareas",
  editor_selector : "dialogRichtext",
  exec_mode : "src",
  strict_loading_mode: true,
  language : 'en',
  papayaParser : {
    linkTarget : '_self'
  },

  extended_valid_elements : "img[data-papaya|class|style|src|width|height|alt|title|align|hspace|vspace],a[data-papaya|class|href|target|name],div[data-papaya|class|style|id],audio[autobuffer|autoplay|controls|src],iframe[src|width|height|align|hspace|vspace|scrolling|name|frameborder|style]",

  plugins : "papaya,lists,tabfocus,autoresize,directionality,fullscreen,media,paste,save,searchreplace,table,visualblocks,xhtmlxtras",

  theme : "advanced",
  theme_advanced_buttons1 : "save,undo,redo,|, code,fullscreen,visualblocks,| ,search,replace,pastetext,cleanup,|, ltr,rtl,|, removeformat,bold,italic,formatselect",
  theme_advanced_buttons2 : "media,|,papayaLink,unlink,anchor,papayaFile,papayaMedia,papayaImage,papayaAddon,|, justifyleft,justifycenter,justifyright,justifyfull,|, sub,sup,cite,ins,del,abbr,acronym",
  theme_advanced_buttons3 : "charmap,hr,|,bullist,numlist,|,outdent,indent,|,tablecontrols",
  theme_advanced_toolbar_location : "top",
  theme_advanced_toolbar_align : "left",
  theme_advanced_path_location : "bottom",
  theme_advanced_blockformats : "p,div,h1,h2,h3,h4,h5,h6,blockquote",

  theme_advanced_resizing : true,
  theme_advanced_resize_horizontal : false,
  accessibility_focus : false,
  //dialog_type : 'modal',

  paste_auto_cleanup_on_paste : true,
  paste_text_sticky : true,
  paste_remove_styles : true,
  paste_strip_class_attributes : 'mso',
  paste_postprocess : function (pl, o) {
    PapayaTinyMCEHandler.onBeforePaste(pl, o);
  },

  entity_encoding : "raw",
  apply_source_formatting : false,
  remove_linebreaks : true,
  save_onsavecallback : function(ed) {
    PapayaTinyMCEHandler.submit(ed);
  },
  onchange_callback : function(ed) {
    PapayaTinyMCEHandler.onChange(ed);
  }
};

var PapayaTinyMCEHandler = {

  submit : function(ed) {
    formObj = tinymce.DOM.get(ed.id).form || tinymce.DOM.getParent(ed.id, 'form');
    jQuery(formObj).submit();
  },

  onChange : function(ed) {
    jQuery(tinymce.DOM.get(ed.id)).change();
  },

  onBeforePaste : function (pl, o) {
    var filterWhitespaceNode = function() {
      var RegExp = /^[\u0009\u000a\u000b\u000c\u000d\u0020\u00a0\u1680\u180e\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u2028\u2029\u202f\u205f\u3000]+$/;
      var content = jQuery(this).text();
      if ((content.length == 0) ||
          RegExp.test(content)) {
        return true;
      }
      return false;
    };
    jQuery(o.node)
      .contents()
      .filter(
        function() {
          return (this.nodeType == 3);
        }
      )
      .filter(filterWhitespaceNode)
      .remove();
    jQuery(o.node)
      .find('p')
      .not(':has(img),:has(object),:has(div)')
      .filter(filterWhitespaceNode)
      .remove();
  }
};

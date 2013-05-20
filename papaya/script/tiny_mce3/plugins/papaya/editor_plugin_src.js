(function() {
  // Load plugin specific language pack
  tinymce.PluginManager.requireLangPack('papaya');

  tinymce.create('tinymce.plugins.PapayaPlugin', {

    getInfo : function() {
      return {
        longname : 'papaya CMS Tags Plugin',
        author : 'papaya Software GmbH',
        authorurl : 'http://www.papaya-cms.com',
        infourl : 'http://www.papaya-cms.com/richtext',
        version : '5.0.1 alpha'
      };
    },

    _getPapayaParser : function() {
      if (typeof this.papayaParser == 'undefined') {
        this.papayaParser = new PapayaParser(this.url + '/../../../../xmltree.php');
        if (typeof this.editor.settings.papayaParser == 'object') {
          for (var i in this.editor.settings.papayaParser) {
            this.papayaParser.options[i] = this.editor.settings.papayaParser[i];
          }
        }
      }
      return this.papayaParser;
    },

    init : function(ed, url) {
      this.editor = ed;
      this.url = url;

      var r = url.match(/(.*)(\/[^\/]*){3}$/);
      var scriptLoader = new tinymce.dom.ScriptLoader();
      scriptLoader.load(r[1] + '/xmlrpc.js');
      scriptLoader.load(url + '/js/jsonclass.js');
      scriptLoader.load(url + '/js/papayaparser.js');
      scriptLoader.load(url + '/js/papayatag.js');
      scriptLoader.load(url + '/js/papayautils.js');
      scriptLoader.loadQueue(function(){});

      ed.addCommand(
        'mcePapayaLink',
        function() {
          this.editor.windowManager.open({
            file :  url + '/link.php',
            width : '500',
            height : '320',
            name : 'papayaLinkWindow',
            modal : 'yes'
          }, {
            plugin : this
          });
        },
        this
      );
      ed.addButton('papayaLink', {
        title : 'papaya.link_desc',
        image : url + '/img/link.gif',
        cmd : 'mcePapayaLink'
      });

      ed.addCommand(
        'mcePapayaFile',
        function() {
          this.editor.windowManager.open({
            file : url + '/file.html',
            width : '500',
            height : '230',
            name : 'papayaFileWindow',
            modal : 'yes'
          }, {
            plugin : this
          });
        },
        this
      );
      ed.addButton('papayaFile', {
        title : 'papaya.file_desc',
        image : url + '/img/folder.png',
        cmd : 'mcePapayaFile'
      });

      ed.addCommand(
        'mcePapayaMedia',
        function() {
          this.editor.windowManager.open({
            file : url + '/media.html',
            width : '700',
            height : '370',
            name : 'papayaMediaWindow',
            modal : 'yes'
          }, {
            plugin : this
          });
        },
        this
      );
      ed.addButton('papayaMedia', {
        title : 'papaya.media_desc',
        image : url + '/img/media.png',
        cmd : 'mcePapayaMedia'
      });

      ed.addCommand(
        'mcePapayaImage',
        function() {
          this.editor.windowManager.open({
            file : url + '/dynimage.php',
            width : '600',
            height : '370',
            name : 'papayaDynamicImageWindow',
            modal : 'yes'
          }, {
            plugin : this
          });
        },
        this
      );
      ed.addButton('papayaImage', {
        title : 'papaya.image_desc',
        image : url + '/img/image.png',
        cmd : 'mcePapayaImage'
      });

      ed.addCommand(
        'mcePapayaAddon',
        function() {
          this.editor.windowManager.open({
            file : url + '/addon.php',
            width : '600',
            height : '370',
            name : 'papayaAddOnWindow',
            modal : 'yes'
          }, {
            plugin : this
          });
        },
        this
      );
      ed.addButton('papayaAddon', {
        title : 'papaya.addon_desc',
        image : url + '/img/addon.png',
        cmd : 'mcePapayaAddon'
      });

      ed.onNodeChange.add(this._onNodeChange, this);

      ed.onBeforeSetContent.add(
        function(ed, o) {
          o.content = this._getPapayaParser().toHTML(o.content);
        },
        this
      );
      ed.onGetContent.add(
        function(ed, o) {
          o.content = this._getPapayaParser().toPapaya(o.content);
        },
        this
      );

      ed.onChange.add(
        function() {
          return function(element, editor) {
            if (typeof element.innerText != 'undefined') {
              element.innerText = editor.getContent();
            } else {
              element.textContent = editor.getContent();
            }
            if (element.form.papayaFormProtector) {
              element.form.papayaFormProtector.activate(element.form);
            }
          }(ed.getElement(), ed);
        }
      );
    },

    _onNodeChange : function(ed, cm, node) {
      if (node == null)
        return;

      cm.setDisabled('papayaFile', false);
      cm.setDisabled('papayaLink', false);
      cm.setDisabled('papayaMedia', false);
      cm.setDisabled('papayaImage', false);
      cm.setDisabled('papayaAddon', false);
      do {
        var tagName = node.nodeName.toLowerCase();
        switch (tagName) {
        case 'img' :
          var papayaData = ed.dom.getAttrib(node, 'data-papaya');
          if (papayaData && (String(papayaData).indexOf('#') > 0)) {
            var papayaTagName = String(papayaData).substr(0, String(papayaData).indexOf('#'));
            switch (papayaTagName) {
            case 'media' :
              cm.setDisabled('papayaFile', true);
              cm.setDisabled('papayaLink', true);
              cm.setDisabled('papayaMedia', false);
              cm.setDisabled('papayaImage', true);
              cm.setDisabled('papayaAddon', true);
              break;
            case 'image' :
              cm.setDisabled('papayaFile', true);
              cm.setDisabled('papayaLink', true);
              cm.setDisabled('papayaMedia', true);
              cm.setDisabled('papayaImage', false);
              cm.setDisabled('papayaAddon', true);
              break;
            }
          } else {
            cm.setDisabled('papayaFile', true);
            cm.setDisabled('papayaLink', true);
            cm.setDisabled('papayaMedia', true);
            cm.setDisabled('papayaImage', true);
            cm.setDisabled('papayaAddon', true);
          }
          return true;
        case 'a' :
          var papayaData = ed.dom.getAttrib(node, 'data-papaya');
          if (papayaData) {
            var papayaTagName = String(papayaData).substr(0, String(papayaData).indexOf('#'));
            switch (papayaTagName) {
            case 'file' :
            case 'media' :
              cm.setDisabled('papayaFile', false);
              cm.setDisabled('papayaLink', true);
              cm.setDisabled('papayaMedia', true);
              cm.setDisabled('papayaImage', true);
              cm.setDisabled('papayaAddon', true);
              break;
            case 'addon' :
              cm.setDisabled('papayaFile', true);
              cm.setDisabled('papayaLink', true);
              cm.setDisabled('papayaMedia', true);
              cm.setDisabled('papayaImage', true);
              cm.setDisabled('papayaAddon', false);
              break;
            case 'link' :
            default :
              cm.setDisabled('papayaFile', true);
              cm.setDisabled('papayaLink', false);
              cm.setDisabled('papayaMedia', true);
              cm.setDisabled('papayaImage', true);
              cm.setDisabled('papayaAddon', true);
              break;
            }
          } else {
            cm.setDisabled('papayaFile', true);
            cm.setDisabled('papayaLink', false);
            cm.setDisabled('papayaMedia', true);
            cm.setDisabled('papayaImage', true);
            cm.setDisabled('papayaAddon', true);
          }
          return true;
        }
      } while ((node = node.parentNode));

      return true;
    }
  });
  // Register plugin
  tinymce.PluginManager.add('papaya', tinymce.plugins.PapayaPlugin);
})();

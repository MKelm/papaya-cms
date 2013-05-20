PapayaImageForm = JsonClass(
  PapayaForm,
  {
    elements : {
    },
    
    url : '../../../../xmltree.php?',
    
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
      this.papayaTag.name = 'image';
  		// prefill form fields with existing data
      if (action == 'update') {
        this.papayaTag.loadFromHTMLNode(elm);
        this.setFieldValue('image_ident', this.papayaTag.getAttr('image', ''));
      } else {
      
      }
      this.getImageDialog();
    	window.focus();
  	},
    
  	apply : function() {
      if (this.getFieldValue('image_ident')) {
        this.papayaTag.clearAttributes();
        this.papayaTag.setAttr('image', this.getFieldValue('image_ident'));
        this.papayaTag.setAttr('dyn_src', '../../../../' + this.getImageRequestURL());
        for (var i = 0; i < this.form.elements.length; i++) {  
          if (this.papayaTag) {
            var fieldName = null;
            if (this.form.elements[i].name) {
              var regs = this.form.elements[i].name.match(/\[(\w+)\]/);
              if (regs && regs[1]) {
                fieldName = regs[1];
                if (fieldName.substring(0,5) != 'info_') {
                  this.papayaTag.setAttr(
                    fieldName,
                    this.getFieldValue(this.form.elements[i].id)
                  );
                }
              }
            }
          }
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
    
    getImageDialog : function() {
      if (this.getFieldValue('image_ident')) {
        var url = this.url +
          'rpc[cmd]=imageconf_form&rpc[image_ident]=' +
          escape(this.getFieldValue('image_ident'));
        loadXMLDoc(url, false, this);
      }
    },
    
    showImageConfDialog : function(responseData) {
      var dynDialog = document.getElementById('dynamicDialog');
      dynDialog.innerHTML = PapayaUtils.HTMLCharDecode(responseData.firstChild.data);
      for (var i = 0; i < this.form.elements.length; i++) {  
        if (this.papayaTag) {
          var fieldName = null;
          if (this.form.elements[i].name) {
            var regs = this.form.elements[i].name.match(/\[(\w+)\]/);
            if (regs) {
              fieldName = regs[1];
              if (this.papayaTag.hasAttr(fieldName)) {
                this.form.elements[i].value = this.papayaTag.getAttr(fieldName);
              }
            }
          }
        }  
        if (!this.form.elements[i].disabled) {
          if (this.form.elements[i].tagName == 'INPUT') {
            this.form.elements[i].onchange = PapayaUtils.scope(this, this.showImagePreview);
          } else if (this.form.elements[i].tagName == 'SELECT' &&
                     this.form.elements[i].id != 'image_ident') {
            this.form.elements[i].onchange = PapayaUtils.scope(this, this.showImagePreview);
          }
        }  
      }
      this.showImagePreview();
    },
    
    showImagePreview : function() {
      document.getElementById('iframePreview').src =
        '../../../../../' + this.getImageRequestURL();
      return false;
    },
    
    getImageRequestURL : function ()  {
      var queryParams = '';
      for (var i = 0; i < this.form.elements.length; i++) {
        if (this.form.elements[i].name) {
          var regs = this.form.elements[i].name.match(/\[(\w+)\]/);
          if (regs) {
            if (this.form.elements[i].tagName == 'INPUT') {
              queryParams += '&' + escape(this.form.elements[i].name) +
                             '=' + escape(this.form.elements[i].value);
            } else if (this.form.elements[i].tagName == 'SELECT' && 
                       this.form.elements[i].id != 'image_ident') {
              queryParams += '&' + escape(this.form.elements[i].name) +
                             '=' + escape(this.form.elements[i].value);      
            }
          }
        }
      }
      return this.getFieldValue('image_ident') + '.image.jpg?' +
        queryParams.substring(1, queryParams.length);
    }
  }
);

PapayaImageDialog = new PapayaImageForm();
tinyMCEPopup.onInit.add(PapayaImageDialog.init, PapayaImageDialog);
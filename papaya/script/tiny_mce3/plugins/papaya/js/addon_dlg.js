PapayaAddOnForm = JsonClass(
  PapayaForm,
  {
    elements : {
      'addon_guid' : {
        check : 'regexp=^[0-9a-f]{32}$',
        info : 'papaya.fieldinfo_addonguid'
      }
    },

    url : '../../../../xmltree.php?',

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
      this.papayaTag.name = 'addon';
  		// prefill form fields with existing data
      if (action == 'update') {
        this.papayaTag.loadFromHTMLNode(elm);
        this.setFieldValue('addon_guid', this.papayaTag.getAttr('addon', ''));
      }
      this.getAddOnDialog();
    	window.focus();
  	},

  	apply : function() {
      if (this.getFieldValue('addon_guid')) {
        this.papayaTag.clearAttributes();
        this.papayaTag.setAttr('addon', this.getFieldValue('addon_guid'));
        for (var i = 0; i < this.form.elements.length; i++) {
          if (this.papayaTag) {
            var fieldName = null;
            if (this.form.elements[i].name) {
              var regs = this.form.elements[i].name.match(/\[(\w+)\]/);
              if (regs) {
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

    getAddOnDialog : function() {
      if (this.getFieldValue('addon_guid')) {
        var url = this.url +
          'rpc[cmd]=addon_form&rpc[addon_guid]=' +
          escape(this.getFieldValue('addon_guid'));
        loadXMLDoc(url, false, this);
      }
    },

    showAddOnDialog : function(responseData, responseParams) {
      var dynDialog = document.getElementById('dynamicDialog');
      var dialogData = '';
      var popupData = new Array();
      for (var i = 0; i < responseParams.length; i++) {
        var parameterName = responseParams[i].attributes.getNamedItem('name').value;
        if (parameterName == 'dialog') {
          if (responseParams[i].textContent) {
            dialogData = responseParams[i].textContent;
          } else if (responseParams[i].text) {
            dialogData = responseParams[i].text;
          } else if (responseParams[i].innerText) {
            dialogData = responseParams[i].innerText;
          } else {
            dialogData = responseParams[i].firstChild.data;
          }
        } else if (parameterName.substr(0, 6) == 'popup_') {
          var name = parameterName.substring(6, parameterName.length);
          popupData[name] = responseParams[i].firstChild.data;
        }
      }
      dynDialog.innerHTML = PapayaUtils.HTMLCharDecode(dialogData);
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
            this.form.elements[i].onchange = PapayaUtils.curry(this, this.getAddOnData, true);
          } else if (this.form.elements[i].tagName == 'SELECT' &&
                     this.form.elements[i].id != 'addon_guid') {
            this.form.elements[i].onchange = PapayaUtils.curry(this, this.getAddOnData, true);
          }
        }
      }
      this.updateAddOnPopUp(popupData);
      this.getAddOnData(true);
    },

    updateAddOnPopUp : function (popupData) {
      var browseButton = document.getElementById('browseButton');
      if (typeof popupData.url != 'undefined') {
        browseButton.value = popupData.button;
        browseButton.onclick = PapayaUtils.curry(this, this.showBrowsePopUp, popupData);
        browseButton.style.display = '';
      } else {
        browseButton.style.display = 'none';
      }
    },

    showBrowsePopUp : function (popupData) {
      var width = this.parseSizeValue(popupData.width, screen.width);
      var height = this.parseSizeValue(popupData.height, screen.height);
      this.openPopup({
        file : popupData.url,
        width : width,
        height : height,
        name : 'papayaAddOnPopUp',
        modal : 'yes'
      }, {
        linkedObject : this
      });
    },

    parseSizeValue : function (size, screenSize) {
      var match = size.match(/(\d+)%$/);
      if (match) {
        return Math.round(parseInt(match[1]) * screenSize) / 100;
      } else {
        return size;
      }
    },

    getAddOnData : function(async, data) {
      var i;
      var currentAddOnGuid = this.getFieldValue('addon_guid');
      var url = this.url +
        'rpc[cmd]=addon_data&rpc[addon_guid]=' +
        this.getFieldValue('addon_guid');

      // initialize data from tag
      var params = this.papayaTag.attr;
      // overwrite values with values from dialog field
      for (i = 0; i < this.form.elements.length; i++) {
        if (this.form.elements[i].name) {
          if ((this.form.elements[i].tagName.toLowerCase() != 'select') ||
              (this.form.elements[i].options.length > 0)) {
            var regs = this.form.elements[i].name.match(/\[(\w+)\]/);
            if (regs) {
              params[regs[1]] = this.form.elements[i].value;
            }
          }
        }
      }
      // overwrite values with values from the data object if provided
      if (typeof data == 'object') {
        for (i in data) {
          if (typeof params[i] != 'undefined') {
            params[i] = data[i];
          }
        }
      }

      var paramStr = '';
      for (i in params) {
        paramStr += '&rpc['+escape(i)+']='+escape(params[i]);
      }
      loadXMLDoc(url+paramStr, async, this);
    },

    setAddOnData : function(respData, respParams) {
      if (respParams) {
        var i, k;
        for (i = 0; i <= respParams.length-1; i++) {
          var paramName = respParams[i].attributes.getNamedItem('name').value;
          var paramValue = respParams[i].attributes.getNamedItem('value').value;
          var fieldId = 'addon_'+paramName;
          var field = document.getElementById(fieldId);
          if (field && (field.tagName.toLowerCase() == 'select')) {
            var options = respParams[i].getElementsByTagName('options');
            if (options.length > 0) {
              options = options[0].getElementsByTagName('option');
              for (k = field.childNodes.length - 1; k >= 0; k--) {
                field.removeChild(field.childNodes.item(k));
              }
              if (options.length > 0) {
                var newOption = null;
                for (k = 0; k < options.length; k++) {
                  newOption = field.ownerDocument.createElement('option');
                  newOption.setAttribute(
                    'value', options[k].attributes.getNamedItem('value').value
                  );
                  newOption.appendChild(
                    field.ownerDocument.createTextNode(
                      options[k].attributes.getNamedItem('caption').value
                    )
                  );
                  field.appendChild(newOption);
                }
              }
            }
            if (paramValue != '') {
              this.setFieldValue(fieldId, paramValue, true);
            } else if (this.papayaTag.hasAttr(paramName)) {
              this.setFieldValue(fieldId, this.papayaTag.getAttr(paramName), true);
            }
          } else {
            this.setFieldValue(fieldId, paramValue, true);
          }
        }
      }
    }
  }
);

PapayaAddOnDialog = new PapayaAddOnForm();
tinyMCEPopup.onInit.add(PapayaAddOnDialog.init, PapayaAddOnDialog);
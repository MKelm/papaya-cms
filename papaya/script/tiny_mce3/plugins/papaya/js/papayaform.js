PapayaForm = JsonClass(
  {
    editor : null,
    form : null,
    elements : null,
    currentPanel : 'foobar',
    papayaTag : null,

    attach : function (f) {
      this.form = f;
      if (this.form.onsubmit) {
        this.formSubmit = this.form.onsubmit;
      } else {
        this.formSubmit = null;
      }
      this.form.onsubmit = PapayaUtils.scope(this, this.onSubmit);
    },

    onSubmit : function () {
      if (this.validate) {
        if (this.validate()) {
          if (this.formSubmit) {
            return this.formSubmit();
          }
        }
      }
      return false;
    },

    setFieldValue : function(fieldId, val) {
      var field = document.getElementById(fieldId);
      if (field && field.form == this.form) {
        if (field.getAttribute) {
          var fieldType = field.getAttribute('type');
          if (typeof fieldType == 'string') {
            fieldType = fieldType.toLowerCase();
          } else {
            fieldType = '';
          }
          switch (fieldType) {
          case 'checkbox' :
            switch (typeof val) {
            case 'boolean' :
              field.checked = val;
              break;
            case 'string' :
              val = val.toLowerCase();
              if (val == '' || val == '0' || val == 'false' || 'val' == 'off') {
                field.checked = false;
              } else {
                field.checked = true;
              }
              break;
            case 'number' :
              field.checked = (val != 0)
               break;
            default :
              field.checked = false;
            }
            return;
          }
        }
        field.value = val;
      }
    },

    getFieldValue : function(fieldId) {
      var field = document.getElementById(fieldId);
      if (field && field.form == this.form) {
        var fieldType = field.getAttribute('type');
        if (typeof fieldType == 'string') {
          switch (fieldType.toLowerCase()) {
          case 'checkbox' :
            return field.checked;
          }
        }
        return field.value;
      }
      return '';
    },

    getFieldValueString : function(fieldId) {
      var field = document.getElementById(fieldId);
      if (field && field.form == this.form) {
        var fieldType = field.getAttribute('type');
        if (typeof fieldType == 'string') {
          switch (fieldType.toLowerCase()) {
          case 'checkbox' :
            return field.checked ? field.value : '';
          }
        }
        return field.value;
      }
      return '';
    },

    validate : function() {
      this.hideError();
      var r = true;
      for (i in this.elements) {
        if (r && !this.validateField(i, this.elements[i])) {
          r = false;
        } else {
          this.highlightField(i, false);
        }
      }
      return r;
    },

    validateField : function(fieldId, validation) {
      var use = true;
      if (typeof validation.use == 'object') {
        for (var i in validation.use) {
          if (!this.validateUse(validation.use[i])) {
            use = false;
            break;
          }
        }
      } else {
        use = this.validateUse(validation.use);
      }
      if (use) {
        if (typeof validation.check == 'object') {
          for (var i in validation.check) {
            if (!this.validateValue(fieldId, validation.check[i])) {
              return false;
            }
          }
        } else if (typeof validation.check == 'string') {
          if (!this.validateValue(fieldId, validation.check)) {
            return false;
          }
        }
      }
      this.highlightField(fieldId, false);
      return true;
    },

    validateUse : function(check) {
      switch (typeof check) {
      case 'boolean' :
        return check;
        break;
      case 'function' :
        return check();
        break;
      case 'string' :
        var pos = check.indexOf('=');
        var cmd = null;
        var param = null;
        if (pos > 0) {
          cmd = check.substring(0, pos);
          param = check.substring(pos + 1);
        } else {
          cmd = check;
        }
        switch (cmd) {
        case 'panel' :
          return (this.currentPanel == param);
        case 'checkbox' :
        case 'checked' :
          var e = document.getElementById(param);
          if (e && e.nodeName.toLowerCase() == 'input') {
            var eType = e.getAttribute('type').toLowerCase();
            if (eType == 'checkbox' || eType == 'radio') {
              return e.checked;
            }
          }
          break;
        default :
          var e = document.getElementById(cmd);
          if (e) {
            var tagName = e.nodeName.toLowerCase();
            if (tagName == 'input' || tagName == 'select' || tagName == 'textarea') {
              if (param != this.getFieldValue(cmd)) {
                return false;
              }
            }
          }
        }
        return true;
        break;
      case 'undefined' :
      default :
        return true;
      }
    },

    validateValue : function(fieldId, check) {
      var val = this.getFieldValue(fieldId);
      if (check == null) {
        //no check nothing to do
      } else if (val == '' && check != 'checked') {
        switch (check) {
        case 'req' :
        case 'required' :
          if (val == '') {
            this.showError(fieldId, 'missing');
            return false;
          }
          break;
        }
      } else if (typeof check == 'function') {
        return check(val);
      } else {
        var pos = check.indexOf('=');
        var cmd = null;
        var param = null;
        if (pos > 0) {
          cmd = check.substring(0, pos);
          param = check.substring(pos + 1);
        } else {
          cmd = check;
        }
        switch (cmd) {
        case 'req' :
        case 'required' :
          if (val == '') {
            this.showError(fieldId, 'missing');
            return false;
          }
          break;
        case 'maxlen' :
        case 'maxlength' :
          var iParam = parseInt(param, 10);
          if (isNaN(iParam) || val.length > iParam) {
            this.showError(fieldId, 'to_long', iParam);
            return false;
          }
          break;
        case 'minlen' :
        case 'minlength' :
          var iParam = parseInt(param, 10);
          if (isNaN(iParam) || val.length < iParam) {
            this.showError(fieldId, 'to_short', iParam);
            return false;
          }
          break;
        case 'num' :
        case 'numeric' :
          var charPos = val.search(/[^0-9]/);
          if (charPos >= 0) {
            this.showError([fieldId, charPos], 'not_numeric', charPos + 1);
            return false;
          }
          break;
        case 'alnum' :
        case 'alphanumeric' :
          var charPos = val.search(/[^a-zA-Z0-9]/);
          if (charPos >= 0) {
            this.showError([fieldId, charPos], 'not_alphanumeric', charPos + 1);
            return false;
          }
          break;
        case 'alpha' :
        case 'alphabetic' :
          var charPos = val.search(/[^a-zA-Z]+$/);
          if (charPos >= 0) {
            this.showError([fieldId, charPos], 'not_alphabetic', charPos + 1);
            return false;
          }
          break;
        case 'alnumhyphen' :
          var charPos = val.search(/[^a-zA-Z0-9_-]+$/);
          if (charPos >= 0) {
            this.showError([fieldId, charPos], 'not_alnumhyphen', charPos + 1);
            return false;
          }
          break;
        case 'email' :
          r = /^[-!#$%&*+\.0-9=?A-Z^_`a-z{|}~]+@[-!#$%&*+0-9=?A-Z^_`a-z{|}~]+\.[-!#$%&*+\.0-9=?A-Z^_`a-z{|}~]+$/;
          if (!r.test(val)) {
            this.showError(fieldId, 'no_email');
            return false;
          }
          break;
        case 'lt' :
        case 'lessthen' :
          var fParam = parseFloat(param);
          var fVal = parseFloat(val);
          if (isNaN(fParam) || fParam <= fVal) {
            this.showError(fieldId, 'to_large', fParam);
            return false;
          }
          break;
        case 'gt' :
        case 'greaterthen' :
          var fParam = parseFloat(param);
          var fVal = parseFloat(val);
          if (isNaN(fParam) || fParam >= fVal) {
            this.showError(fieldId, 'to_small', fParam);
            return false;
          }
          break;
        case 'url' :
          r = /^https?:\/\/([a-zA-Z0-9_-]+\.)*([a-zA-Z0-9-]{2,})(\.[a-zA-Z]{2,6})(\/[a-zA-Z0-9\._%;,-]*)*(\?[:a-zA-Z0-9\.\[\]/%_,;&=+-]+)?(#[:a-zA-Z0-9\.\[\]/%_,;&=+-]+)?$/;
          if (r) {
            if (r.test(val)) {
              return true;
            } else {
              this.showError(fieldId, 'error_no_http', param);
              return false;
            }
          } else {
            this.debug('warn', 'Broken RegEx for Field ' + fieldId + ': ' +param);
            return FALSE;
          }
          break;
        case 'regexp' :
        case 'regexpr' :
          var r = new RegExp(param);
          if (r) {
            if (r.test(val)) {
              return true;
            } else {
              this.showError(fieldId, 'regex_nomatch', param);
              return false;
            }
          } else {
            this.debug('warn', 'Broken RegEx for Field ' + fieldId + ': ' +param);
            return FALSE;
          }
          break;
        case '!regexp' :
        case 'notregexp' :
        case '!regexpr' :
        case 'notregexpr' :
          var r = new RegExp(param);
          if (r) {
            if (matches = r.exec(val)) {
              var charPos = val.indexOf(matches[0]);
              this.showError([fieldId, charPos], 'regex_match', [charPos + 1, param]);
              return false;
            } else {
              return true;
            }
          } else {
            this.debug('warn', 'Broken RegEx for Field ' + fieldId + ': ' +param);
            return true;
          }
          break;
        case 'dontselect' :
          var e = document.getElementById(fieldId);
          var iParam = parseInt(param, 10);
          if (e.selectedIndex == null) {
            this.debug('warn', 'Selected check for non-select item: "' + fieldId + '"');
            return true;
          } else if (param == 'first' && e.selectedIndex == 0) {
            this.showError(fieldId, 'no_select');
            return false;
          } else if (param == 'last' && e.selectedIndex == (e.options.length - 1)) {
            this.showError(fieldId, 'no_select');
            return false;
          } else if (isNaN(iParam) && e.selectedIndex == 0) {
            this.showError(fieldId, 'no_select');
            return false;
          } else if (!isNaN(iParam) && e.selectedIndex == iParam) {
            this.showError(fieldId, 'no_select');
            return false;
          }
          break;
        case 'checked' :
          var e = document.getElementById(fieldId);
          if (!e.checked) {
            this.showError(fieldId, 'not_checked');
          }
          break;
        }
      }
      this.highlightField(fieldId, false);
      return true;
    },

    displayTab : function(tabId, panelId) {
      if (this.onBeforePageSwitch) {
        this.onBeforePageSwitch(panelId);
      }
      mcTabs.displayTab(tabId, panelId);
      this.currentPanel = panelId;
      if (this.onAfterPageSwitch) {
        this.onAfterPageSwitch(panelId);
      }
    },

    showError : function(fieldData, errorString, params) {
      if (!this.errorBox) {
        this.errorBox = new PapayaErrorBox(this.form);
      }
      var fieldId;
      if (typeof fieldData == 'string') {
        fieldId = fieldData;
      } else {
        fieldId = fieldData[0];
      }
      var fieldLabel = document.getElementById(fieldId+'label');
      var errorMessage = this.translate('papaya.error_'+errorString);
      if (typeof params == 'undefined' || params == null) {
        params = [];
      } else if (typeof params == 'string') {
        params = [params];
      } else if (typeof params == 'number') {
        params = [params];
      } else if (!params.unshift) {
        params = [];
      }
      if (fieldLabel) {
        params.unshift(PapayaUtils.text(fieldLabel));
      } else {
        params.unshift(fieldId);
      }
      errorMessage = PapayaUtils.vsprintf(errorMessage, params);
      var errorInfo = this.elements[fieldId].info;
      if (errorInfo && errorInfo != '') {
        errorInfo = this.translate(errorInfo);
      } else {
        errorInfo = null;
      }
      this.errorBox.show(fieldData, errorMessage, errorInfo);
      this.highlightField(fieldId, true);
    },

    hideError : function() {
      if (this.errorBox) {
        this.errorBox.hide();
      }
    },

    highlightField : function(fieldId, enabled) {
      var e = document.getElementById(fieldId);
      if (e) {
        var classes = e.className.split(/[ ]+/);
        for (var i in classes) {
          if (classes[i] == 'papayaError') {
            if (enabled) {
              return;
            } else {
              var newClasses = classes.slice(0, i);
              newClasses = newClasses.concat(classes.slice(i + 1));
              e.className = newClasses.join(' ');
              return;
            }
          }
        }
        if (enabled) {
          classes[classes.length] = 'papayaError';
          e.className = classes.join(' ');
        }
      }
    },

    translate : function(s) {
      if (this.editor) {
        return this.editor.getLang(s);
      }
      return s;
    },

    debug : function (level, msg) {
      if (typeof console == 'object') {
        if (level == 'error' && console.error) {
          console.error(msg);
        } else if (level == 'warning' && console.warn) {
          console.warn(msg);
        } else if (console.log) {
          console.log(msg);
        }
      }
    },

    openPopup : function (s, p) {
      s = s || {};
      p = p || {};
      s.name = s.name || 'papaya_' + new Date().getTime();
      s.width = parseInt(s.width || 320, 10);
      s.height = parseInt(s.height || 240, 10);
      s.resizable = s.resizeable || false;
      s.left = s.left || parseInt(screen.width / 2.0, 10) - (s.width / 2.0);
      s.top = s.top || parseInt(screen.height / 2.0, 10) - (s.height / 2.0);
      s.modal = s.modal | false;

      var isIE = tinymce.isIE;

      if (s.modal && isIE) {
        s.center = true;
        s.help = false;
        s.dialogWidth = s.width + 'px';
        s.dialogHeight = s.height + 'px';
        s.scroll = s.scrollbars || false;
      }

      var f = '';
      // Build features string
      for (var k in s) {
        var v = s[k];
        if (tinymce.is(v, 'boolean'))
          v = v ? 'yes' : 'no';

        if (!/^(name|url)$/.test(k)) {
          if (isIE && s.modal) {
            f += (f ? ';' : '') + k + ':' + v;
          } else {
            f += (f ? ',' : '') + k + '=' + v;
          }
        }
      }

      u = s.url || s.file;
      try {
        if (isIE && s.modal) {
          w = 1;
          window.showModalDialog(u, window, f);
        } else
          w = window.open(u, s.name, f);
      } catch (ex) {
        // Ignore
      }

      if (w) {
        if (w.initPopup) {
          w.initPopup(p);
        } else {
          for (pn in p) {
            w[pn] = p[pn]
          }
        }
      } else {
        alert(this.editor.getLang('popup_blocked'));
      }
    }
  }
);

PapayaErrorBox = JsonClass(
  'form',
  {
    form : null,
    boxId : 'papayaErrorBox',
    msgId : 'papayaErrorMsg',
    infoId : 'papayaErrorInfo',
    buttonId : 'papayaErrorButton',
    box : null,
    msg : null,
    info : null,
    button : null,
    fieldData : null,

    init : function() {
      this.box = document.getElementById(this.boxId);
      if (!this.box) {
        this.box = document.createElement('div');
        this.box.id = this.boxId;
        this.form.appendChild(this.box);
      }
      this.msg = document.getElementById(this.msgId);
      if (!this.msg) {
        this.msg = document.createElement('p');
        this.msg.id = this.msgId;
        this.box.appendChild(this.msg);
      }
      this.info = document.getElementById(this.infoId);
      if (!this.info) {
        this.info = document.createElement('p');
        this.info.id = this.infoId;
        this.box.appendChild(this.info);
      }
      this.button = document.getElementById(this.buttonId);
      if (!this.button) {
        this.button = document.createElement('a');
        this.button.appendChild(document.createTextNode('Hide'));
        this.button.id = this.buttonId;
        this.button.href = '#';
        this.box.appendChild(this.button);
      }
      this.button.onclick = PapayaUtils.scope(this, this.hide);
      return this.box;
    },

    show : function(fieldData, errorString, info) {
      this.init();
      if (this.box && this.msg) {
        this.fieldData = fieldData;
        PapayaUtils.text(this.msg, errorString);
        if (info && info != '') {
          PapayaUtils.text(this.info, info);
          this.info.style.display = '';
        } else {
          this.info.style.display = 'none';
        }
        this.box.style.display = '';
      }
    },

    hide : function() {
      if (typeof this.box == 'object') {
        this.box.style.display = 'none';
        if (this.fieldData) {
          var e;
          var p = null;
          if (typeof this.fieldData == 'string') {
            e = document.getElementById(this.fieldData);
          } else {
            e = document.getElementById(this.fieldData[0]);
            p = parseInt(this.fieldData[1], 10);
          }
          if (e) {
            e.focus();
            if (!isNaN(p)) {
              if (e.value.length > p) {
	            if (e.setSelectionRange) {
	              e.setSelectionRange(p, p+1);
	            } else if (e.createTextRange) {
	              var range = e.createTextRange();
	              range.collapse(true);
	              range.moveEnd('character', p+1);
	              range.moveStart('character', p);
	              range.select();
	            }
              }
            }
          }
        }
      }
    }
  }
);
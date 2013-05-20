var PapayaUtils = {

  HTMLCharEncode : function(s) {
    var result = String(s);
    result = result.replace( /\"/g, "&quot;");
    result = result.replace( /</g, "&lt;");
    result = result.replace( />/g, "&gt;");
    return result;
  },

  HTMLCharDecode : function(s) {
    var result = String(s);
    result = result.replace( /&quot;/g, '"');
    result = result.replace( /&lt;/g, "<");
    result = result.replace( /&gt;/g, ">");
    return result;
  },

  inArray : function(val, a) {
    for (var i in a) {
      if (a[i] == val) {
        return true;
      }
    }
    return false;
  },

  formatFileSizeToStr : function(size) {
    if (size > 10000000000) {
      return (Math.round(size / 1073741824 * 100) / 100)+' GB';
    } else if (size > 10000000) {
      return (Math.round(size / 1048576 * 100) / 100)+' MB';
    } else if (size > 10000) {
      return (Math.round(size / 1024 * 100) / 100)+' kB';
    } else {
      return Math.round(size)+' Bytes';
    }
  },

  text : function (element, text) {
    if (typeof text != 'undefined') {
      if (typeof element.textContent != 'undefined') {
        element.textContent = text;
      } else if (typeof element.innerText != 'undefined') {
        element.innerText = text;
      } else if (typeof element.innerHtml != 'undefined') {
        element.innerHtml = text;
      }
    } else if (typeof element.textContent != 'undefined') {
      return element.textContent;
    } else if (typeof element.innerText != 'undefined') {
      return element.innerText;
    } else if (typeof element.innerHtml != 'undefined') {
      return element.innerHtml;
    }
    return '';
  },

  sprintf : function() {
    if (typeof arguments == 'undefined') {
      return null;
    }
    if (arguments.length < 1) {
      return null;
    }
    params = [];
    for (i = 1; i < arguments.length; i++) {
      params[params.length] = arguments[i];
    }
    return PapayaUtils.vsprintf(arguments[0], params);
  },

  vsprintf : function(s, a) {
    if (typeof s != 'string') {
      return null;
    }
    var jokers = s.match(/%%|%(\+)?([ 0]|'.)?(-)?(\d*)(\.\d+)?(\d\$)?[cdfs]/g);
    if (jokers && jokers.length > 0) {
      var result = '';
      var offset = 0;
      var argPos = 0;
      for (var i = 0; i < jokers.length; i++) {
        var joker = jokers[i];
        var pos = s.indexOf(joker, offset);
        result += s.substring(offset, pos);
        if (joker == '%%') {
          result += '%';
        } else {
          var jokerParts = joker.match(/%%|%(\+)?([ 0]|'.)?(-)?(\d*)(\.(\d+))?(\d\$)?([cdfs])/);
          if (jokerParts) {
            var signed = (jokerParts[1] == '+');
            var paddingChar = ' ';
            if (typeof jokerParts[2] == 'string' && jokerParts[2].length > 0) {
              paddingChar = jokerParts[2].substring(jokerParts[2].length - 1);
            }
            var paddingLeft = (jokerParts[3] == '-');
            var width = parseInt(jokerParts[4], 10);
            if (isNaN(width)) {
              width = 0;
            }
            var precision = parseInt(jokerParts[6], 10);
            if (isNaN(precision)) {
              precision = -1;
            }
            var argument;
            var argIdx = parseInt(jokerParts[7], 10);
            if (isNaN(argIdx)) {
              argument = a[argPos];
            } else {
              argument = a[argIdx];
            }

            var buffer = '';
            switch (jokerParts[8]) {
            case 'c' :
              buffer = parseInt(argument, 10);
              if (!isNaN(buffer)) {
                buffer = String.fromCharCode(buffer);
              }
              break;
            case 'd' :
              buffer = parseInt(argument, 10);
              if (!isNaN(buffer)) {
                buffer = buffer.toString();
                if (signed && buffer >= 0) {
                  buffer = '+' + buffer;
                }
              }
              break;
            case 'f' :
              buffer = parseFloat(argument);
              if (!isNaN(buffer)) {
                if (precision > 0) {
                  var factor = Math.pow(10, precision);
                  buffer = Math.round(buffer * factor) / factor;
                } else if (precision == 0) {
                  buffer = Math.round(buffer);
                }
                if (signed && buffer >= 0) {
                  buffer = '+' + buffer;
                }
              }
              break;
            case 's' :
              buffer = String(argument);
              if (precision > 0 && precision < buffer.length) {
                buffer = buffer.substring(0, precision);
              }
              break;
            }
            buffer = String(buffer);
            if (width > buffer.length) {
              padding = PapayaUtils.strRepeat(paddingChar, width - buffer.length);
              if (paddingLeft) {
                buffer = padding + buffer;
              } else {
                buffer += padding;
              }
            }
            result += buffer;
            argPos++;
          } else {
            result += joker;
          }
        }
        offset = pos + joker.length;
      }
      result += s.substring(offset);
      return result;
    } else {
      return s;
    }
  },

  strRepeat : function(s, c) {
    var r = '';
    for (var i = 0; i < c; i++) {
      r += s;
    }
    return r;
  },

  scope : function (scope, func) {
    return function () {
      func.apply(scope, arguments);
    };
  },

  curry : function (scope, func) {
    var s, f, n;
    // make the scope parameter optional such that it keeps the original this value
    if (typeof scope == 'function' && typeof func != 'function') {
      s = null; f = scope; n = 1;
    } else {
      s = scope; f = func; n = 2;
    }
    // save the arguments to pass to the new function
    var args = Array.prototype.slice.call(arguments, n);
    return function () {
      // override the this scope if desired
      var o = s || this;
      // add the arguments passed in to the other arguments
      var allArgs = args.concat(Array.prototype.slice.call(arguments, 0));
      // call the function with the new scope and arguments
      return f.apply(o, allArgs);
    };
  },

  decorate : function (fn, pre, post) {
    return function () {
      if (pre) {
        pre.apply(this, arguments);
      }
      fn.apply(this, arguments);
      if (post) {
        post.apply(this, arguments);
      }
    };
  }
};
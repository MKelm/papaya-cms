var decodeXMLChars = function(text) {
  var result = String(text);
	result = result.replace( /&quot;/g, '"');
	result = result.replace( /&lt;/g, "<");
	result = result.replace( /&gt;/g, ">");
	return result;
};

var xmlParamNodesToArray = function(paramNodes) {
  var params = [];
  if (paramNodes) {
    for (var i = 0; i < paramNodes.length; i++) {
      var paramName = null;
      var paramValue = null;
      var attrName = paramNodes[i].attributes.getNamedItem('name');
      var attrValue = paramNodes[i].attributes.getNamedItem('value');
      if (attrName) {
        paramName = attrName.value;
        if (attrValue) {
          paramValue = attrValue.value;
        } else if (paramNodes[i].firstChild) {
          paramValue = paramNodes[i].firstChild.data;
        }
      }
      if (paramName && paramValue) {
        params[paramName] = paramValue;
      }
    }
  }
  return params;
};

var checkXMLRequestStatus = function(req) {
  if (typeof req != 'undefined' && req.readyState == 4) {
    if (req.status == 200) {
      return true;
    } else {
      alert("There was a problem retrieving the XML data:\n" + req.readyState + ' ' + req.statusText);
    }
  }
  return false;
};

var requestHandlerCallback = function(req, aOwner) {
  var method;
  var data;
  var params;
  if (checkXMLRequestStatus && checkXMLRequestStatus(req)) {
    if (typeof req != 'undefined' && req.responseXML) {
      var response  = req.responseXML.documentElement;
      if (response.tagName == 'msg') {
        alert(decodeXMLChars(response.firstChild.data));
      } else if (response.tagName == 'response') {
        method = response.getElementsByTagName('method')[0].firstChild.data;
        data   = response.getElementsByTagName('data')[0];
        params = response.getElementsByTagName('param');
        if (typeof aOwner != 'undefined' && aOwner[method]) {
          eval('aOwner.' + method + '(data, params)');
        } else if (window[method]) {
          eval(method + '(data, params)');
        }
      } else if (response.tagName == 'responses' && response.childNodes.length > 0) {
        for (var i = 0; i < response.childNodes.length; i++) {
          var resonseFunc = response.childNodes[i];
          method = resonseFunc.getElementsByTagName('method')[0].firstChild.data;
          data   = resonseFunc.getElementsByTagName('data')[0];
          params = resonseFunc.getElementsByTagName('param');
          if (typeof aOwner != 'undefined' && aOwner[method]) {
            eval('aOwner.' + method + '(data, params)');
          } else if (window[method]) {
            eval(method + '(data, params)');
          }
        }
      }
    } else if (req.responseText !== '') {
      alert('Error: '+req.responseText);
    }
  }
};

var loadXMLDoc = function(url, aSync, aOwner) {
  var req;
  if (window.XMLHttpRequest) {
    // branch for native XMLHttpRequest object
    req = new XMLHttpRequest();
  } else if (window.ActiveXObject) {
    // branch for IE/Windows ActiveX version
    req = new ActiveXObject("Microsoft.XMLHTTP");
  }
  if (req) {
    if (aSync) {
      req.onreadystatechange = function(r, o) {
        return function () {
          requestHandlerCallback.apply(null, [r,o]);
        };
      }(req, aOwner);
    }
    req.open("GET", url, aSync);
    if (window.XMLHttpRequest) {
      req.send(null);
    } else {
      req.send();
    }
    if ((!aSync) && requestHandlerCallback) {
      requestHandlerCallback(req, aOwner);
    }
  }
};
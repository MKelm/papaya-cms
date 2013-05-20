var rpcTimeOut = null;
var uploadId = null;
var showUploadProgress = false;

function startFileUpload(form) {
  // do we have an upload form
  if (form) {
    var inputs = form.getElementsByTagName('input');
    var hasFiles = false;
    if (inputs) {
      for (var i = 0; i < inputs.length; i++) {
        if (inputs[i].type == 'file' && inputs[i].value != '') {
          hasFiles = true;
          break;
        }
      }
    }
    if (hasFiles) {
      uploadId = uniqueid();
      var idField = document.getElementById('UPLOAD_IDENTIFIER');
      var jsField = document.getElementById('UPLOAD_JAVASCRIPT');
      if (idField && jsField) {
        idField.value = uploadId;
        jsField.value = '1';

        var iFrameId = 'uploadProgressFrame';
        var iFrame = document.getElementById(iFrameId);
        if (!iFrame) {
          iframe = document.createElement('iframe');
          iframe.id = iFrameId;
          iframe.name = iFrameId;
          iframe.style.display = 'none';
          iframe.style.visibility = 'hidden';
          form.appendChild(iframe);
        }
        form.target = iFrameId;

        showUploadProgress = true;
        PapayaLightBox.init(progressDialogTitle, 'Close');
        PapayaLightBox.update(progressDialogStartMsg, 0);
        getFileUploadStatus();

        form.submit();
      }
    } else {
      alert(progressNoFileMsg);
    }
  }
  return false;
}

function uploadFinished(queryString) {
  var targetUrl = document.location.href;
  if (targetUrl.indexOf('#') > 0) {
    targetUrl = targetUrl.substring(0, targetUrl.indexOf('#'));
  }
  if (targetUrl.indexOf('?') > 0) {
    targetUrl = targetUrl.substring(0, targetUrl.indexOf('?'));
  }
  document.location.href = targetUrl + queryString;
}

function disableUploadProgress() {
  showUploadProgress = false;
  PapayaLightBox.hide();
}

function getFileUploadStatus() {
  url = 'mediadb.php?upload_progress_id='+escape(uploadId);
  if (rpcTimeOut) {
    window.clearTimeout(rpcTimeOut);
  }
  if (showUploadProgress) {
    rpcTimeOut = window.setTimeout('loadXMLDoc(url, true);', 500);
  }
}

function rpcFileUploadProgress(data, params) {
  var responseParams = xmlParamNodesToArray(params);
  var progress = parseInt(responseParams.progress);
  var message = responseParams.message;
  if (showUploadProgress) {
    PapayaLightBox.update(message, progress);
  }
  if (responseParams.status != 'FINISHED') {
    getFileUploadStatus();
  }
}

// create a unique ID every time the program starts.
function uniqueid() {
  var hex = "0123456789abcdef";
  var chr = '';     // buffer for the random hex string
  for (var i = 0; i < 32; i++)   {
    unround = Math.random() * 16;
    rnd     = Math.floor(unround);    // create random no 1-16
    chr     = chr + hex.charAt(rnd);  // hex char
  }
  return chr;   // set the upload ID ready for the hidden field
}


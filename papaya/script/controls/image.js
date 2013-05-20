function initializeImageData(imgDataStr) {
  if (imgDataStr != null && imgDataStr != '') {
    var paramname = 'rpc';
    var url = '../../xmltree.php?'
      + paramname + '[cmd]=image_data&'
      + paramname + '[image_conf]=' + escape(imgDataStr);
    loadXMLDoc(url, true);
  }
}

function rpcSetImageData(responseData, responseParams) {
  var responseArray = new Array();
  if (responseParams) {
    responseArray = xmlParamNodesToArray(responseParams);
    mediaFileData.id        = responseArray.src;
    mediaFileData.orgWidth   = responseArray.org_width;
    mediaFileData.orgHeight  = responseArray.org_height;
    mediaFileData.width      = responseArray.width;
    mediaFileData.height     = responseArray.height;
    mediaFileData.resizeMode = responseArray.resize;
    if (mediaFileData.width > 0) {
      document.getElementById('editSizeWidth').value = mediaFileData.width;
    } else if (mediaFileData.orgWidth > 0) {
      document.getElementById('editSizeWidth').value = mediaFileData.orgWidth;
    } else {
      document.getElementById('editSizeWidth').value = '';
    }
    if (mediaFileData.height > 0) {
      document.getElementById('editSizeHeight').value = mediaFileData.height;
    } else if (mediaFileData.orgHeight > 0) {
      document.getElementById('editSizeHeight').value = mediaFileData.orgHeight;
    } else {
      document.getElementById('editSizeHeight').value = '';
    }
    showResizeModeStatus();
    showPreview(responseArray.src);
  }
}

function showPreview(imgGuid) {
  var oIFrame = document.getElementById('iframePreview');
  if (oIFrame != null && imgGuid != '') {
    oIFrame.src = '../../mediafilebrw.php?mdb[mode]=preview&mdb[mid]=' + escape(imgGuid);
  }
}

function setMediaFileData(fileId, fileData) {
  for (var key in fileData) {
    mediaFileData[key] = fileData[key];
  }
  var elementWidth = document.getElementById('editSizeWidth');
  var elementHeight = document.getElementById('editSizeHeight');
  if (elementWidth.value == '') {
    elementWidth.value = mediaFileData.width;
  }
  if (elementHeight.value == '') {
    elementHeight.value = mediaFileData.height;
  }
  showPreview(fileId);
}

function mergeArray(first, second) {
  var result = first;
  for (var key in second) {
    result[key] = second[key];
  }
  return result;
}

function getOriginalSize() {
  oIFrame = frames['iframePreview'];
  mediaFileData.width  = mediaFileData.orgWidth;
  mediaFileData.height = mediaFileData.orgHeight;
  document.getElementById('editSizeWidth').value  = mediaFileData.width;
  document.getElementById('editSizeHeight').value = mediaFileData.height;
}

function switchProtectProportions() {
  bProtextProportions = !(bProtextProportions);
  if (bProtextProportions) {
    document.getElementById('imageProtectProportions').src =
       '../../pics/controls/size_linked_on.gif';
  } else {
    document.getElementById('imageProtectProportions').src =
       '../../pics/controls/size_linked_off.gif';
  }
}

function OnChangeWidth() {
  if (bProtextProportions) {
    var iWidth = parseInt(document.getElementById('editSizeWidth').value);
    var iOrgWidth = parseInt(mediaFileData.orgWidth);
    var iOrgHeight = parseInt(mediaFileData.orgHeight);
    if (isNaN(iWidth) || isNaN(iOrgWidth) || isNaN(iOrgHeight) ||
        iWidth < 1 || iOrgWidth < 1 || iOrgHeight < 1) {
      //nothing to do - invalid values
    } else {
      mediaFileData.height = Math.round(iWidth * iOrgHeight / iOrgWidth);
      if (mediaFileData.height != undefined) {
        document.getElementById('editSizeHeight').value = mediaFileData.height;
      }
    }
  }
  mediaFileData.width = parseInt(document.getElementById('editSizeWidth').value);
}

function OnChangeHeight() {
  if (bProtextProportions) {
    var iHeight = parseInt(document.getElementById('editSizeHeight').value);
    var iOrgWidth = parseInt(mediaFileData.orgWidth);
    var iOrgHeight = parseInt(mediaFileData.orgHeight);
    if (isNaN(iHeight) || isNaN(iOrgWidth) || isNaN(iOrgHeight) ||
        iHeight < 1 || iOrgWidth < 1 || iOrgHeight < 1) {
      //nothing to do - invalid values
    } else {
      mediaFileData.width = Math.round(iHeight * iOrgWidth / iOrgHeight);
      if (mediaFileData.width != undefined) {
        document.getElementById('editSizeWidth').value = mediaFileData.width;
      }
    }
  }
  mediaFileData.height = parseInt(document.getElementById('editSizeHeight').value);
}

function selectResize(mode) {
  switch (mode) {
  case 'mincrop' :
  case 'min' :
  case 'abs' :
    mediaFileData.resizeMode = mode;
    break;
  case 'max' :
  default :
    mediaFileData.resizeMode = 'max';
    break;
  }
  showResizeModeStatus();
}

function showResizeModeStatus() {
  var basePath = '../../pics/controls/';
  document.getElementById('imageSizeMinCrop').src = basePath+'size_mincrop.gif';
  document.getElementById('imageSizeMin').src = basePath+'size_min.gif';
  document.getElementById('imageSizeAbs').src = basePath+'size_abs.gif';
  document.getElementById('imageSizeMax').src = basePath+'size_max.gif';
  switch (mediaFileData.resizeMode) {
  case 'mincrop' :
    document.getElementById('imageSizeMinCrop').src = basePath+'size_mincrop_sel.gif';
    break;
  case 'min' :
    document.getElementById('imageSizeMin').src = basePath+'size_min_sel.gif';
    break;
  case 'abs' :
    document.getElementById('imageSizeAbs').src = basePath+'size_abs_sel.gif';
    break;
  case 'max' :
  default :
    document.getElementById('imageSizeMax').src = basePath+'size_max_sel.gif';
    break;
  }
}

function selectImage() {
  // Set the browser window feature.
  var iWidth  = Math.round(screen.width * 0.7);
  var iHeight  = Math.round(screen.height * 0.7);

  var iLeft = Math.round((screen.width  - iWidth) / 2);
  var iTop  = Math.round((screen.height - iHeight) / 2);

  var sOptions = "toolbar=no, status=no, resizable=no, dependent=yes" ;
  sOptions += ", width=" + iWidth ;
  sOptions += ", height=" + iHeight ;
  sOptions += ", left=" + iLeft ;
  sOptions += ", top=" + iTop ;

  var oWindow = window.open('./browseimg.php', 'PapayaBrowsePopupWindow', sOptions);
}
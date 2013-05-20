var selectedFileId = null;

function changeFrames(params, changeList, changePreview, changeThumbs) {
  var objList;
  var objPreview;
  var objThumbs;
  if (typeof changeList == 'undefined') {
    changeList = true;
  }
  if (typeof changePreview == 'undefined') {
    changePreview = true;
  }
  if (typeof changeThumbs == 'undefined') {
    changeThumbs = true;
  }
  if (changeList) {
    objList    = document.getElementById("frmFolders");
    objList.src = linkList + params;
  }
  if (changePreview) {
    objPreview = document.getElementById("frmPreview");
    objPreview.src = linkPreview + params;
  }
  if (changeThumbs) {
    objThumbs  = document.getElementById("frmThumbs");
    objThumbs.src = linkThumbs  + params;
  }
}

function searchFiles(form) {
  var queryString = '';
  var elements = [];
  elements = form.getElementsByTagName('input');
  for (var elementIndex = 0; elementIndex < elements.length; elementIndex++) {
    var element = elements[elementIndex];
    queryString += '&' + encodeURI(element.name) + '=' + encodeURI(element.value);
  }
  elements = form.getElementsByTagName('select');
  for (var elementIndex = 0; elementIndex < elements.length; elementIndex++) {
    var element = elements[elementIndex];
    queryString += '&' + encodeURI(element.name) + '=' + encodeURI(element.value);
  }
  changeFrames(queryString, false, true, true);
}

function resizeImage() {
  image = document.getElementById('preview');
  if (image != null) {
    if (image.style.pixelHeight != null) {
      orgLeft = parseInt(image.style.pixelLeft);
      orgTop  = parseInt(image.style.pixelTop);
    } else {
      orgLeft = parseInt(image.style.left);
      orgTop  = parseInt(image.style.top);
    }
    if (isNaN(orgLeft)) {
      orgLeft = 0;
    }
    if (isNaN(orgTop)) {
      orgTop = 0;
    }

    orgWidth  = imgwidth;
    orgHeight = imgheight;

    var maxWidth;
    var maxHeight;
    if (self.innerHeight) { // all except Explorer
      maxWidth  = self.innerWidth;
      maxHeight = self.innerHeight;
    } else if (document.documentElement && document.documentElement.clientHeight) {
    // Explorer 6 Strict Mode
      maxWidth  = document.documentElement.clientWidth;
      maxHeight = document.documentElement.clientHeight;
    } else if (document.body){ // other Explorers
      maxWidth  = document.body.clientWidth;
      maxHeight = document.body.clientHeight;
    }

    var divWidth  = (orgWidth  / maxWidth);
    var divHeight = (orgHeight / maxHeight);

    if (orgWidth >= maxWidth || orgHeight >= maxHeight) {
      if (divWidth >= divHeight) {
        newWidth  = maxWidth;
        newHeight = parseInt(orgHeight / divWidth);
      } else {
        newWidth  = parseInt(orgWidth / divHeight);
        newHeight = maxHeight;
      }
    } else {
      var newWidth  = orgWidth;
      var newHeight = orgHeight;
    }
    newTop  = parseInt((maxHeight - newHeight) / 2);
    newLeft = parseInt((maxWidth  - newWidth)  / 2);

    if ((orgWidth != newWidth) || (orgHeight != newHeight)) {
      if (image.style.pixelHeight != null) {
        image.style.pixelWidth  = newWidth;
        image.style.pixelHeight = newHeight;
      } else {
        image.style.width  = newWidth  + "px";
        image.style.height = newHeight + "px";
      }
    }
    if ((orgTop != newTop) || (orgLeft != newLeft)) {
      if (image.style.pixelHeight != null) {
        image.style.pixelLeft = newLeft;
        image.style.pixelTop  = newTop;
      } else {
        image.style.left = newLeft + "px";
        image.style.top  = newTop  + "px";
      }
    }

  }
  window.setTimeout("resizeImage()", 500);
}

function createMediaImageTag() {
  var editform = document.getElementById('imgform');
  if (editform != null) {
    var imgstr    = '<papaya:media src="'+imgurl+'"';
    var editfield = document.getElementById('imgtag');
    var resize_it = false;

    var editdownload = editform.imgdownload;
    if (editdownload.value != null) {
      editdownload = editdownload.value;
    } else {
      editdownload = editdownload.options[editdownload.selectedIndex].value;
    }
    if (editdownload != 'yes') {

      var editalign = editform.imgalign;
      if (editalign.value != null) {
        editalign = editalign.value;
      } else {
        editalign = editalign.options[editalign.selectedIndex].value;
      }
      if (editalign != '') {
        imgstr += ' align="'+editalign+'"';
      }
      var editsubtitle = editform.imgsubtitle.value;
      if (editsubtitle != '') {
        imgstr += ' subtitle="'+editsubtitle+'"';
      }
      var editalt = editform.imgalt.value;
      if (editalt != '') {
        imgstr += ' alt="'+editalt+'"';
      }
      var editwidth = parseInt(editform.imgwidth.value);
      if(isNaN(editwidth) == false) {
        imgstr += ' width="'+editwidth+'"';
        resize_it = true;
      }
      var editheight = parseInt(editform.imgheight.value);
      if(isNaN(editheight) == false) {
        imgstr += ' height="'+editheight+'"';
        resize_it = true;
      }
      var editresize = editform.imgresize;
      if (editresize.value != null) {
        editresize = editresize.value;
      } else {
        editresize = editresize.options[editresize.selectedIndex].value;
      }
      if (resize_it == true) {
        if ((editresize == 'min') || (editresize == 'mincrop') || (editresize == 'abs')) {
          imgstr += ' resize="'+editresize+'"';
        }
      }
      var editlspace = parseInt(editform.imglspace.value);
      if(isNaN(editlspace) == false) {
        imgstr += ' lspace="'+editlspace+'"';
      }
      var edittspace = parseInt(editform.imgtspace.value);
      if(isNaN(edittspace) == false) {
        imgstr += ' tspace="'+edittspace+'"';
      }
      var editrspace = parseInt(editform.imgrspace.value);
      if(isNaN(editrspace) == false) {
        imgstr += ' rspace="'+editrspace+'"';
      }
      var editbspace = parseInt(editform.imgbspace.value);
      if(isNaN(editbspace) == false) {
        imgstr += ' bspace="'+editbspace+'"';
      }

      var editcropx = parseInt(editform.imgcropx.value);
      var editcropy = parseInt(editform.imgcropy.value);
      if(isNaN(editcropx) == false || isNaN(editcropy) == false) {
        if(isNaN(editcropy)) {
          editcropy = 0;
        }
        if(isNaN(editcropx)) {
          editcropx = 0;
        }
        imgstr += ' xoff="'+editcropx+'" yoff="'+editcropy+'"';
      }

      var editcropwidth = parseInt(editform.imgcropwidth.value);
      if(isNaN(editcropwidth) == false) {
        imgstr += ' cropwidth="'+editcropwidth+'"';
      }
      var editcropheight = parseInt(editform.imgcropheight.value);
      if(isNaN(editcropheight) == false) {
        imgstr += ' cropheight="'+editcropheight+'"';
      }

      var editlink = editform.imglink.value;
      if (editlink != '') {
        imgstr += ' href="'+editlink+'"';
      }
    } else {
      imgstr += ' download="'+editdownload+'"';

      var editsubtitle = editform.imgsubtitle.value;
      if (editsubtitle != '') {
        imgstr += ' text="'+editsubtitle+'"';
      }
      var editalt = editform.imgalt.value;
      if (editalt != '') {
        imgstr += ' hint="'+editalt+'"';
      }
    }

    var edittarget = editform.imgtarget;
    if (edittarget.value != null) {
      edittarget = edittarget.value;
    } else {
      edittarget = edittarget.options[edittarget.selectedIndex].value;
    }
    if (edittarget != '') {
      imgstr += ' target="'+edittarget+'"';
    }

    imgstr += '/>';
    if (editfield != null) {
      editfield.value = imgstr;
    }
  }
}

function previewImage() {
  var editform = document.getElementById('textarea_');

  var popupWindow = window.open(
    '','','width='+400+',height='+300+',left='+100+',top='+100+',screenX='+100+',screenY='+100);
  popupWindow.location = "./mediafilebrw.php?mdb[mode]=imgpreview&mdb[img]=" + escape(editform.value);

}

function createMediaLinkTag() {
  var editform = document.getElementById('imgform');
  if (editform != null) {
    var imgstr    = '<papaya:media src="'+imgurl+'"';
    var editfield = editform.imgtag;
    var resize_it = false;

    var edittarget = editform.imgtarget;
    if (edittarget.value != null) {
      edittarget = edittarget.value;
    } else {
      edittarget = edittarget.options[edittarget.selectedIndex].value;
    }
    if (edittarget != '') {
      imgstr += ' target="'+edittarget+'"';
    }
    var edittext = editform.imgtext.value;
    if (edittext != '') {
      imgstr += ' text="'+edittext+'"';
    }
    var edithint = editform.imghint.value;
    if (edithint != '') {
      imgstr += ' hint="'+edithint+'"';
    }
    imgstr += '/>';
    if (editfield != null) {
      editfield.value = imgstr;
    }
  }
}
/**
* marks the selected file as selected (thumbnail down or listitem selected) and passes selection to parent frame
*/
function selectFile(fileId, fileData) {
  var file = document.getElementById('file'.fileId);
  // if we are in listview mode
  if (file) {
    if (file.tagName == 'tr' || file.tagName == 'TR') {
      table = file.parentNode;
      // get a list of all listitems
      var files = table.getElementsByTagName('tr');
      for (i=0; i < files.length; i++) {
        // if the current listitem is selected, unselect it, i.e. set correct class (odd/even)
        if (files[i].className == 'selected') {
          if (i % 2 == 0) {
            newClassName = 'even';
          } else {
            newClassName = 'odd';
          }
          files[i].className = newClassName;
        }
      }
      // set the currently selected file class to selected
      file.className = 'selected';
    } else {    // if we are in thumbnail mode
      if (selectedFileId) {
        var selectedFile = document.getElementById(selectedFileId);
        if (selectedFile) {
          selectedFile.className = 'thumbnail';
        }
      }
      // mark the selected thumbnail
      file.className = 'thumbnail selected';
      //remember it
      selectedFileId = fileId;
    }
  }
  // set the current media file in the parent frame to the selected one
  if (parent.setMediaFileData) {
    parent.setMediaFileData(fileId, fileData);
  }
}
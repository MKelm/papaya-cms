<?php
/**
*
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya
* @subpackage Skins-Default
* @version $Id: js.style.php 37387 2012-08-13 09:31:40Z weinert $
*/

/**
* inclusion of base or additional libraries
*/
require_once(dirname(__FILE__).'/../../inc.func.php');
includeThemeDefinition();
controlScriptFileCaching(__FILE__, FALSE);
header('Content-type: text/javascript');
?>
var ol_fgcolor = '<?php echo PAPAYA_BGCOLOR_QUICKINFO; ?>';
var ol_bgcolor = '<?php echo PAPAYA_BORDERCOLOR_QUICKINFO; ?>';
var ol_textcolor = '<?php echo PAPAYA_FGCOLOR_QUICKINFO; ?>';
var ol_textfont = 'sans-serif';

function addUploadButton(title, paramName) {
  if (typeof paramName == 'undefined') {
    paramName = 'mdb';
  }
  var i = 0;
  do {
    i++;
  } while (document.getElementById(paramName+'_upload_' + i));

  var addButtonLine = document.getElementById(paramName+'_upload_' + (i - 1)).parentNode.parentNode;

  var newLine = document.createElement('tr');

  if (addButtonLine.className == 'even') {
    newLine.className = 'odd';
  } else {
    newLine.className = 'even';
  }


  var firstColumn = document.createElement('td');
  firstColumn.appendChild(document.createTextNode(title + ' ' + (i+1)));
  firstColumn.className = 'caption';
  newLine.appendChild(firstColumn);

  var secondColumn = document.createElement('td');
  secondColumn.className = 'infos';
  newLine.appendChild(secondColumn);

  var tableField = document.createElement('input');
  tableField.setAttribute('type', 'file');
  tableField.setAttribute('size', '18');
  tableField.setAttribute('class', 'file');
  tableField.setAttribute('id', paramName + '_upload_' + i);
  tableField.setAttribute('name', paramName + '[upload][' + i + ']');

  var thirdColumn = document.createElement('td');
  thirdColumn.appendChild(tableField);
  thirdColumn.className = 'element';
  newLine.appendChild(thirdColumn);

  addButtonLine.parentNode.appendChild(newLine);
}

function invertCheckBoxes(element) {
  var form = element.parentNode.parentNode.parentNode;
  var inputs = form.getElementsByTagName('input');
  if (inputs.length > 0) {
    for (var i = 0; i < inputs.length; i++) {
      if (inputs[i].getAttribute('type') == 'checkbox') {
        inputs[i].checked = !(inputs[i].checked);
      }
    }
  }
}

var PapayaLightBox = {

  elements : {
    box : null,
    dialog : null,
    dialogTitle : null,
    dialogMessage : null,
    bar : null,
    button : null,
    buttonTitle : null
  },

  init : function(titleStr, buttonStr) {
    if (!this.elements.box) {
      //create the box
      this.elements.box = document.createElement('div');
      this.elements.box.id = 'lightBox';
      document.body.appendChild(this.elements.box);
      //create the dialog
      this.elements.dialog = document.createElement('div');
      this.elements.dialog.id = 'lightBoxDialog';
      document.body.appendChild(this.elements.dialog);
      //dialog title bar
      var titleBar = document.createElement('div');
      titleBar.className = 'title';
      this.elements.dialog.appendChild(titleBar);

      var titleArtwork = document.createElement('div');
      titleArtwork.className = 'titleArtworkOverlay';
      titleBar.appendChild(titleArtwork);

      var title = document.createElement('h1');
      title.className = 'lightBoxDialogTitle';
      titleArtwork.appendChild(title);

      this.elements.dialogTitle = document.createTextNode('dialog title');
      title.appendChild(this.elements.dialogTitle);

      var progressDialog = document.createElement('div');
      progressDialog.className = 'progressDialog';
      this.elements.dialog.appendChild(progressDialog);

      var message = document.createElement('h2');
      message.className = 'progressDialogMessage';
      progressDialog.appendChild(message);

      this.elements.dialogMessage = document.createTextNode('dialog message');
      message.appendChild(this.elements.dialogMessage);

      var progressBarBorder = document.createElement('div');
      progressBarBorder.className = 'progressBarBorder';
      progressDialog.appendChild(progressBarBorder);

      this.elements.bar = document.createElement('div');
      this.elements.bar.className = 'progressBar';
      progressBarBorder.appendChild(this.elements.bar);

      var buttonArea = document.createElement('div');
      buttonArea.className = 'lightBoxButtons';
      progressDialog.appendChild(buttonArea);

      this.elements.button = document.createElement('button');
      this.elements.button.className = 'progressBarButton';
      this.elements.button.onclick = function () {
        PapayaLightBox.hide();
      };
      buttonArea.appendChild(this.elements.button);

      this.elements.buttonTitle = document.createTextNode('dialog button');
      this.elements.button.appendChild(this.elements.buttonTitle);

      var footer = document.createElement('div');
      footer.className = 'footer';
      progressDialog.appendChild(footer);

      var footerArtwork = document.createElement('div');
      footerArtwork.className = 'footerArtworkOverlay';
      footer.appendChild(footerArtwork);
    }
    if (titleStr) {
      this.elements.dialogTitle.nodeValue = titleStr;
    }
    if (buttonStr) {
      this.elements.buttonTitle.nodeValue = buttonStr;
    }
  },

  show : function(titleStr) {
    this.init();
    if (this && this.elements) {
      if (this.elements.dialog.style.display != 'block') {
        //resize lightbox to document size
        var documentWidth, documentHeight;
        var scrollWidth = document.body.scrollWidth;
        if (document.body.offsetWidth > scrollWidth) {
          scrollWidth = document.body.offsetWidth;
        }
        var scrollHeight = document.body.scrollHeight;
        if (document.body.offsetHeight > scrollHeight) {
          scrollHeight = document.body.offsetHeight;
        }
        this.elements.box.style.height = scrollHeight;
        this.elements.box.style.width = scrollWidth;

        //move lightbox dialog
        var windowWidth;
        if (self.innerWidth) {
          // all except Explorer
        	windowWidth = self.innerWidth;
        } else if (document.documentElement && document.documentElement.clientWidth) {
        	// Explorer 6 Strict Mode
        	windowWidth = document.documentElement.clientWidth;
        } else if (document.body) {
          // other Explorers
        	windowWidth = document.body.clientWidth;
        }
        var newLeft = parseInt((windowWidth - 400) / 2, 10);
        if (this.elements.dialog.style.pixelLeft) {
          this.elements.dialog.style.pixelLeft = newLeft;
        } else {
          this.elements.dialog.style.left = newLeft + "px";
        }
        this.elements.dialog.style.display = 'block';
      }
      if (this.elements.box.style.display != 'block') {
        this.elements.box.style.display = 'block';
      }
    }
  },

  hide : function() {
    if (this && this.elements && this.elements.dialog) {
      if (this.elements.box.style.display != 'none') {
        this.elements.box.style.display = 'none';
      }
      if (this.elements.dialog.style.display != 'none') {
        this.elements.dialog.style.display = 'none';
      }
    }
  },

  update : function(messageStr, barPosition) {
    this.show();
    if (this && this.elements && this.elements.dialog) {
      var showCloseButton = true;
      if (this.elements.bar && (barPosition != -1)) {
        barPosition = parseInt(barPosition, 10);
        if (barPosition >= 0 && barPosition <= 100) {
          this.elements.bar.style.width = barPosition.toString() + '%';
        } else if (barPosition > 100) {
          this.elements.bar.style.width = '100%';
        } else {
          this.elements.bar.style.width = '1%';
        }
        if (barPosition < 100 && barPosition > 0) {
          showCloseButton = false;
        }
      }
      if (messageStr) {
        this.elements.dialogMessage.nodeValue = messageStr;
      }
      this.elements.button.style.display = showCloseButton ? '' : 'none';
    }
  }
};

var PapayaRichtextSwitch = {

  items : [],

  add : function (caption, href, selected) {
    this.items[this.items.length] = {
      caption : caption,
      href : href,
      selected : selected
    };
  },

  output : function (parentNode, caption) {
    if (parentNode) {
      var list = document.createElement('ul');
      list.className = 'rightLinks';
      var captionElement = document.createElement('li');
      captionElement.className = 'caption';
      var captionText = document.createTextNode(caption);
      captionElement.appendChild(captionText);
      list.appendChild(captionElement);
      for (var i = 0; i < this.items.length; i++) {
        var itemElement = document.createElement('li');
        if (this.items[i].selected) {
          itemElement.className = 'selected';
        }
        var itemLink = document.createElement('a');
        itemLink.setAttribute('href', this.items[i].href);
        var itemText = document.createTextNode(this.items[i].caption);
        itemLink.appendChild(itemText);
        itemElement.appendChild(itemLink);
        list.appendChild(itemElement);
      }
      parentNode.insertBefore(list, parentNode.firstChild);
    }
  }
};
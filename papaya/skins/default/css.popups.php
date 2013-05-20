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
* @version $Id: css.popups.php 37543 2012-10-12 14:41:18Z weinert $
*/

/**
* inclusion of base or additional libraries
*/
require_once(dirname(__FILE__).'/../../inc.func.php');
includeThemeDefinition();
controlScriptFileCaching(__FILE__, FALSE);
header('Content-type: text/css');
?>
body {
  font-family: sans-serif;
  margin: 0;
  padding: 0px;
  background-color: <?php echo PAPAYA_BGCOLOR_WORKAREA; ?>;
}
img {
  border: none;
}
form {
  margin: 0;
  padding: 0;
}

body#popup .title {
  background-color: <?php echo PAPAYA_BGCOLOR_HEADER; ?>;
  background-image: url(pics/<?php echo PAPAYA_THEME_PICS; ?>/background-title.png);
  background-position: left top;
  color: <?php echo PAPAYA_FGCOLOR_HEADER; ?>;
  font-weight: bold;
  font-size: 18px;
  border-bottom: 1px solid <?php echo PAPAYA_BGCOLOR_WINDOW; ?>;
}
body#popup .title h1 {
  font-weight: bold;
  font-size: 18px;
  margin: 0px;
}
body#popup .titleArtworkOverlay {
  background-image: url(pics/background-grid.png);
  background-position: right top;
  background-repeat: repeat;
  padding: 0;
  padding-left: 5px;
  padding-right: 10px;
  height: 37px;
  line-height: 37px;
}
body#popup .footer {
  background-color: <?php echo PAPAYA_BGCOLOR_HEADER; ?>;
  background-image: url(pics/<?php echo PAPAYA_THEME_PICS; ?>/background-title.png);
  background-position: left top;
  border-top: 1px solid <?php echo PAPAYA_BGCOLOR_WINDOW; ?>;
}
body#popup .footerArtworkOverlay {
  background-image: url(pics/background-grid.png);
  background-position: right top;
  background-repeat: repeat;
  padding: 0;
  height: 16px;
  line-height: 16px;
}
body#popup .popupButtonsArea {
  padding: 5px;
  text-align: right;
  white-space: nowrap;
  border: none;
  clear: both;
}
body#popup .popupButtonAreaArtwork {
  border-top: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
}
body#popup .popupButtonAreaArtwork .popupButtonsArea {
  border-top: 1px solid <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
}

div.divPreview {
  width: 300px;
  height: 300px;
  float: left;
}
iframe.iframePreview {
  width: 300px;
  height: 300px;
  background-color: <?php echo PAPAYA_BGCOLOR_WORKAREA; ?>;
}


fieldset {
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
}

input.text[disabled] {
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  border-bottom-color: <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  border-right-color: <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  background-color: <?php echo PAPAYA_BGCOLOR_WORKAREA; ?>;
  color: <?php echo PAPAYA_FGCOLOR_WORKAREA; ?>;
}
button.dialogButton, input.dialogButton {
  border: 1px solid <?php echo PAPAYA_BORDERCOLOR_UP; ?>;
  border-bottom-color: <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  border-right-color: <?php echo PAPAYA_BORDERCOLOR_DOWN; ?>;
  background-color: <?php echo PAPAYA_BGCOLOR_WORKAREA; ?>;
  cursor: pointer;
}
button.buttonLeft, input.buttonLeft {
  float: left;
}

.dialogXXSmall .dialogScale {
  width: 80px;
}


frameset {
  background-color: <?php echo PAPAYA_BGCOLOR_WORKAREA; ?>;
  border: solid 4px <?php echo PAPAYA_BGCOLOR_WORKAREA; ?>;
}
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
* @subpackage Scripts-Controls
* @version $Id: browsebtns.php 38508 2013-05-28 12:15:12Z faber $
*/

/**
* incusion of base or additional libraries
*/
require_once(dirname(__FILE__).'/inc.controls.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>Image Browser - Buttons</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css"
          href="../../skins/<?php echo PAPAYA_UI_SKIN; ?>/css.popups.php">
  </head>
  <body id="popup">
    <div class="popupButtonAreaArtwork">
      <div class="popupButtonsArea">
	  <input type="button" value="<?php echo _gt('Ok');?>"
           class="dialogButton" onclick="parent.Ok();" />
	  <input type="button" value="<?php echo _gt('Cancel')?>"
           class="dialogButton" onclick="parent.Cancel();" />
      </div>
    </div>
    <div class="footer">
      <div class="footerArtworkOverlay"> </div>
    </div>
  </body>
</html>
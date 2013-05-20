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
* @version $Id: calendar.php 32447 2009-10-12 17:04:01Z feder $
*/

/**
* load required libaries
*/
require_once(dirname(__FILE__).'/inc.controls.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
  <title><?php echo _gt('Select page');?></title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css"
          href="../../skins/<?php echo PAPAYA_UI_SKIN; ?>/css.popups.php">
    <script type="text/javascript">
      var aCurrentData = new Array();
      var linkedObject;

      function Cancel() {
        window.close();
      }

      function Ok() {
        if (linkedObject != null) {
          linkedObject.setDateString(aCurrentData.date);
        }
        window.close();
      }

    </script>
  </head>
  <body id="popup">
    <div class="title">
      <div class="titleArtworkOverlay">
	  <h1 id="popupTitle"><?php echo _gt('Select date');?></h1>
      </div>
    </div>

    <div class="footer">
      <div class="footerArtworkOverlay"> </div>
    </div>
  </body>
</html>
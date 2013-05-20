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
* @version $Id: browseheader.php 32831 2009-11-03 13:41:23Z weinert $
*/

/**
* incusion of base or additional libraries
*/
require_once(dirname(__FILE__).'/inc.controls.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>Image Browser - Header</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css"
          href="../../skins/<?php echo PAPAYA_UI_SKIN; ?>/css.popups.php">
  </head>
  <body id="popup">
    <div class="title">
      <div class="titleArtworkOverlay">
	  <h1 id="popupTitle"><?php echo _gt('Select file');?></h1>
      </div>
    </div>
  </body>
</html>
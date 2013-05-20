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
* @version $Id: css.richtext.php 35746 2011-05-11 13:05:38Z weinert $
*/

/**
* inclusion of base or additional libraries
*/
require_once(dirname(__FILE__).'/../../inc.func.php');
includeThemeDefinition();
controlScriptFileCaching(__FILE__, FALSE);
header('Content-type: text/css');
?>
body, div, ul, li {
  -moz-box-sizing: border-box;
  -khtml-box-sizing: border-box;
  box-sizing: border-box;
}
body, div, ul, li, table, th, td {
  font-size: 12px;
}
body {
  font-family: sans-serif;
  margin: 0;
  padding: 0px;
  background-color: <?php echo PAPAYA_BGCOLOR_INPUT; ?>;
  color: <?php echo PAPAYA_FGCOLOR_INPUT; ?>;
}
/* TinyMCE specific rules */
body.mceContentBody {
  background-color: <?php echo PAPAYA_BGCOLOR_INPUT; ?>;
  color: <?php echo PAPAYA_FGCOLOR_INPUT; ?>;
  padding: 5px;
}

.mceContentBody a {
   color: <?php echo PAPAYA_FGCOLOR_LINK; ?> !important; /* FF requires a important here */
}

.mceContentBody h1 {
  font-size: 16px;
}
.mceContentBody h2 {
  font-size: 14px;
}
.mceContentBody h3 {
  font-size: 12px;
}
.mceContentBody h4 {
  font-size: 11px;
}
.mceContentBody h5 {
  font-size: 10px;
}
.mceContentBody blockquote {
  padding: 5px 30px;
}
.mceContentBody code {
  font-family: teletype;
}

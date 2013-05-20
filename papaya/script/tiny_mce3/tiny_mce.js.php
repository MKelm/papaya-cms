<?php
/**
* This file includes the TinyMCE main file into the HTML page.
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
* @subpackage Administration-TinyMCE
* @version $Id: tiny_mce.js.php 32423 2009-10-12 14:19:14Z weinert $
*/

if (!empty($_GET['gzip']) && $_GET['gzip'] == 'true') {
  $allowCompress = TRUE;
} else {
  $allowCompress = FALSE;
}

$path = dirname(__FILE__);
$name = basename($path);
$path = dirname($path);

/**
* Administration file functions
*/
require_once(dirname($path).'/inc.func.php');
controlScriptFileCaching(__FILE__, FALSE, $allowCompress);
header('Content-type: text/javascript');

if (file_exists($path.'/'.$name.'.js')) {
  if (!empty($_GET['gzip']) && $_GET['gzip'] == 'true') {
    echo "\n\n/* $name/tiny_mce_gzip.js */\n\n";
    readfile($path.'/'.$name.'/tiny_mce_gzip.js');
  } else {
    echo "\n\n/* $name/tiny_mce.js */\n\n";
    readfile($path.'/'.$name.'/tiny_mce.js');
  }
  echo "\n\n/* $name.js */\n\n";
  readfile($path.'/'.$name.'.js');
} else {
  echo <<<JS
  if (console.error) {
    console.error('TinyMCE not found in "%s"', '$path$name.js');
  }
JS;
}
?>
<?php
/**
* Configuration
* necessary for main configuration
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
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
* @subpackage Administration
* @version $Id: inc.conf.php 38350 2013-04-02 15:18:52Z weinert $
*/

if (defined('PAPAYA_DOCUMENT_ROOT')) {
  $path = PAPAYA_DOCUMENT_ROOT;
} else {
  if (isset($_SERVER['PATH_TRANSLATED']) &&
      $_SERVER['PATH_TRANSLATED'] != '' &&
      FALSE === strpos($_SERVER['PATH_TRANSLATED'], '/modsec-')) {
    $path = strtr(dirname(dirname($_SERVER['PATH_TRANSLATED'])), '\\', '/');
  } else {
    $path = strtr(dirname(dirname($_SERVER['SCRIPT_FILENAME'])), '\\', '/');
  }
  if (substr($path, -1) != '/') {
    $path .= '/';
  }
  /**
  * Papaya document root - front controller directory on server
  * @ignore
  */
  define('PAPAYA_DOCUMENT_ROOT', $path);
}
require_once($path.'conf.inc.php');
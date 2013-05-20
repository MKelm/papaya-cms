<?php
/**
* This script is responsible for generating all frontend output of papaya CMS, including
* file delivery (if not static or themes). It also handles basic system errors like lack
* of the papaya library, static error document and maintenance mode.
*
* @copyright 2002-2008 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license   GNU General Public Licence (GPL) 2 http://www.gnu.org/copyleft/gpl.html
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya
* @subpackage Frontend
* @version $Id: index.php 36921 2012-04-02 15:31:16Z afflerbach $
*/

/**
* Including the basic configuration file
*/
require_once('./conf.inc.php');

if (defined('PAPAYA_DBG_DEVMODE') && PAPAYA_DBG_DEVMODE) {
  $PAPAYA_FOUND_LIBRARY = include_once(PAPAYA_INCLUDE_PATH.'system/papaya_page.php');
} else {
  $PAPAYA_FOUND_LIBRARY = @include_once(PAPAYA_INCLUDE_PATH.'system/papaya_page.php');
}
if ((!$PAPAYA_FOUND_LIBRARY) || (defined('PAPAYA_MAINTENANCE_MODE') && PAPAYA_MAINTENANCE_MODE)) {
  if (php_sapi_name() == 'cgi' || php_sapi_name() == 'fast-cgi') {
    @header('Status: 503 Service Unavailable');
  } else {
    @header('HTTP/1.1 503 Service Unavailable');
  }
  if (defined('PAPAYA_MAINTENANCE_MODE') && PAPAYA_MAINTENANCE_MODE &&
      defined('PAPAYA_ERRORDOCUMENT_MAINTENANCE') &&
      file_exists(PAPAYA_ERRORDOCUMENT_MAINTENANCE) &&
      is_file(PAPAYA_ERRORDOCUMENT_MAINTENANCE) &&
      is_readable(PAPAYA_ERRORDOCUMENT_MAINTENANCE)) {
    header('Content-type: text/html; charset=utf-8;');
    readfile(PAPAYA_ERRORDOCUMENT_MAINTENANCE);
  } elseif (defined('PAPAYA_ERRORDOCUMENT_503') && file_exists(PAPAYA_ERRORDOCUMENT_503) &&
    is_file(PAPAYA_ERRORDOCUMENT_503) && is_readable(PAPAYA_ERRORDOCUMENT_503)) {
    header('Content-type: text/html; charset=utf-8;');
    readfile(PAPAYA_ERRORDOCUMENT_503);
  } else {
    echo 'Service Unavailable';
  }
} else {
  if ((!defined('PAPAYA_WEBSITE_REVISION')) && is_readable('revision.inc.php')) {
    include_once('./revision.inc.php');
  }
  $PAPAYA_PAGE = new papaya_page();
  $PAPAYA_PAGE->execute();
  $PAPAYA_PAGE->get();
}

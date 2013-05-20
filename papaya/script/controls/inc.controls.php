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
* @version $Id: inc.controls.php 38496 2013-05-17 10:32:04Z weinert $
*/

if (isset($_SERVER['PATH_TRANSLATED']) && $_SERVER['PATH_TRANSLATED'] != '') {
  $path = strtr(dirname(dirname(dirname(dirname($_SERVER['PATH_TRANSLATED'])))), '\\', '/');
} else {
  $path = strtr(dirname(dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME'])))), '\\', '/');
}
if (substr($path, -1) != '/') {
  $path .= '/';
}

/**
* @ignore
*/
define('PAPAYA_DOCUMENT_ROOT', $path);
if (!defined('PAPAYA_ADMIN_PAGE')) {
  /**
  * @ignore
  */
  define('PAPAYA_ADMIN_PAGE', TRUE);
}
/**
* incusion of base or additional libraries
*/
require_once(dirname(__FILE__).'/../../inc.conf.php');
require_once(dirname(__FILE__).'/../../inc.func.php');

//application object
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Autoloader.php');
spl_autoload_register('PapayaAutoloader::load');
$application = PapayaApplication::getInstance();
$application->registerProfiles(
  new PapayaApplicationProfilesCms()
);

require_once(PAPAYA_INCLUDE_PATH.'system/papaya_options.php');
$PAPAYA_OPTIONS = new papaya_options();
$PAPAYA_OPTIONS->loadAndDefine();

$application->messages->setUp($application->options);

/**
* incusion of base or additional libraries
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_auth.php');
require_once(PAPAYA_INCLUDE_PATH.'system/papaya_xsl.php');
require_once(PAPAYA_INCLUDE_PATH.'system/papaya_errors.php');

/**
* @ignore
*/
define('PAPAYA_ADMIN_SESSION', TRUE);
if (defined('PAPAYA_SESSION_NAME')) {
  $application->session->setName('sid'.PAPAYA_SESSION_NAME.'admin');
} else {
  $application->session->setName('sidadmin');
}
$application->options->cache = PapayaSessionOptions::CACHE_PRIVATE;
$application->session->activate(FALSE);

require_once(PAPAYA_INCLUDE_PATH.'system/sys_phrases.php');
$application->phrases = new base_phrases();
$application->phrases->getLngId(PAPAYA_UI_LANGUAGE);

$PAPAYA_USER = &$application->getObject('AdministrationUser');
$PAPAYA_USER->msgs = &$PAPAYA_MSG;
$PAPAYA_USER->layout = &$PAPAYA_LAYOUT;
$PAPAYA_USER->images = &$PAPAYA_IMAGES;
$PAPAYA_USER->initialize();

$PAPAYA_SHOW_ADMIN_PAGE = (bool)$PAPAYA_USER->execLogin();
if (!$PAPAYA_SHOW_ADMIN_PAGE) {
  exit;
} elseif (
  isset($PAPAYA_USER->options['PAPAYA_UI_LANGUAGE']) &&
  PAPAYA_UI_LANGUAGE != $PAPAYA_USER->options['PAPAYA_UI_LANGUAGE']) {
  //user has a different ui language reset object
  $application->setObject('phrases', new base_phrases(), PapayaApplication::DUPLICATE_OVERWRITE);
  $application->phrases->getLngId($PAPAYA_USER->options['PAPAYA_UI_LANGUAGE']);
}
$application->session->close();

header('Content-type: text/html; charset=utf-8');
?>

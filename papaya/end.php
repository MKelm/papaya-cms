<?php
/**
* Logout
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
* @version $Id: end.php 38496 2013-05-17 10:32:04Z weinert $
*/

if (!defined('PAPAYA_ADMIN_PAGE')) {
  /**
  * This is an administration page
  * @ignore
  */
  define('PAPAYA_ADMIN_PAGE', TRUE);
}

require_once("./inc.conf.php");
require_once("./inc.func.php");

includeOrRedirect(PAPAYA_INCLUDE_PATH.'system/Papaya/Autoloader.php');
spl_autoload_register('PapayaAutoloader::load');

$application = PapayaApplication::getInstance();
$application->registerProfiles(new PapayaApplicationProfilesCms());

includeOrRedirect(PAPAYA_INCLUDE_PATH.'system/papaya_options.php');

$PAPAYA_OPTIONS = new papaya_options();

if (!($hasOptions = $PAPAYA_OPTIONS->loadAndDefine())) {
  if (defined('PAPAYA_DBG_DATABASE_ERROR') && PAPAYA_DBG_DATABASE_ERROR) {
    redirectToInstaller();
  }
} elseif (defined('PAPAYA_UI_SECURE') &&
          PAPAYA_UI_SECURE &&
          !PapayaUtilServerProtocol::isSecure()) {
  $url = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
  redirectToURL($url);
}

$application->messages->setUp($application->options);

require_once(PAPAYA_INCLUDE_PATH.'system/base_auth.php');

/**
* @ignore
*/
define('PAPAYA_ADMIN_SESSION', TRUE);
if (defined('PAPAYA_SESSION_NAME')) {
  $application->session->setName('sid'.PAPAYA_SESSION_NAME.'admin');
} else {
  $application->session->setName('sidadmin');
}
$application->session->options->cache = PapayaSessionOptions::CACHE_NONE;
$application->session->activate(FALSE);
$application->session->destroy();

$PAPAYA_USER = &$application->getObject('AdministrationUser');
$PAPAYA_USER->initialize();
$PAPAYA_USER->execLogin();

$protocol = PapayaUtilServerProtocol::get();
$url = $protocol.'://'.$_SERVER['HTTP_HOST'].
  str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])).'/';
redirectToURL($url);

?>
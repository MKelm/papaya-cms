<?php
/**
* PHP functions for the tinymce popups.
*
* @package Papaya
* @subpackage Administration-TinyMCE
*/

/**
* initialize object for a dynamic popup page
*
* @return boolean
*/
function initializeAdministrationPage() {
  error_reporting(2047);
  if (!defined('PAPAYA_ADMIN_PAGE')) {
    define('PAPAYA_ADMIN_PAGE', TRUE);
  }
  $path = dirname(dirname(dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME'])))));
  include_once(dirname($path).'/conf.inc.php');
  include_once($path.'/inc.func.php');
  include_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Autoloader.php');
  spl_autoload_register('PapayaAutoloader::load');
  $application = PapayaApplication::getInstance();
  $application->registerProfiles(
    new PapayaApplicationProfilesCms()
  );
  includeOrRedirect(PAPAYA_INCLUDE_PATH.'system/papaya_options.php');
  $options = new papaya_options();
  if (!$options->loadAndDefine()) {
    if (defined('PAPAYA_DBG_DATABASE_ERROR') && PAPAYA_DBG_DATABASE_ERROR) {
      redirectToInstaller();
    }
  }
  $application->messages->setUp($application->options);
  include_once(PAPAYA_INCLUDE_PATH.'system/base_auth.php');
  if (defined('PAPAYA_SESSION_NAME')) {
    $application->session->setName('sid'.PAPAYA_SESSION_NAME.'admin');
  } else {
    $application->session->setName('sidadmin');
  }
  $application->session->options->cache = PapayaSessionOptions::CACHE_PRIVATE;
  $application->session->activate(FALSE);
  $user = &$application->getObject('AdministrationUser');
  $user->initialize();
  return $user->execLogin();
}
?>
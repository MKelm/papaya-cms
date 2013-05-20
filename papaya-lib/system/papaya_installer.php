<?php
/**
* Install default database
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
* @version $Id: papaya_installer.php 38361 2013-04-04 12:09:41Z hapke $
*/

/**
* Basic class database access
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');
require_once(PAPAYA_INCLUDE_PATH.'system/sys_checkit.php');

/**
* Install default database
*
* @package Papaya
* @subpackage Administration
*/
class papaya_installer extends base_db {
  /**
  * Param name
  * @var string $paramName
  */
  var $paramName = 'inst';
  /**
  * language for license
  * @var string $licenseLng
  */
  var $licenseLng = 'en-US';
  /**
  * Phrase tables
  * @var array $phraseTables
  */
  var $phraseTables = array(
    'lng', 'phrase', 'phrase_log', 'phrase_module', 'phrase_relmod', 'phrase_trans'
  );
  /**
  * Auth tables
  * @var array $authTables
  */
  var $authTables = array(
    PapayaContentTables::AUTHENTICATION_USERS,
    PapayaContentTables::AUTHENTICATION_USER_GROUP_LINKS,
    PapayaContentTables::AUTHENTICATION_USER_OPTIONS,
    PapayaContentTables::AUTHENTICATION_GROUPS,
    PapayaContentTables::AUTHENTICATION_PERMISSIONS,
    PapayaContentTables::AUTHENTICATION_MODULE_PERMISSIONS,
    PapayaContentTables::AUTHENTICATION_MODULE_PERMISSION_LINKS,
    PapayaContentTables::AUTHENTICATION_LOGIN_TRIES,
    PapayaContentTables::AUTHENTICATION_LOGIN_IPS
  );

  /**
  * Tables in database
  * @var array $existingTables
  */
  var $existingTables = array();

  /**
  * Module manager instance to create/sync tables
  * @var object papaya_modulemanager $moduleManager
  */
  var $moduleManager = NULL;

  /**
  * response output as xml?
  * @var string $outputMode
  */
  var $outputMode = 'xml';

  /**
  * Installer wants to output a xml for rpc response
  * @var boolean $rpcResponseSent
  */
  var $rpcResponseSent = FALSE;

  /**
  * Path to Framework
  * @var string $pathFrameworkRoot
  */
  var $pathFrameworkRoot = '';

  /**
  * Path to installation folder
  * @var string $installationPath
  */
  var $installationPath = '';

  /**
  * Initialize parameters
  * @access public
  */
  function initialize() {
    include_once(PAPAYA_INCLUDE_PATH.'system/papaya_systemtest.php');
    $this->pathDocumentRoot = $_SERVER['DOCUMENT_ROOT'];
    $this->pathFrameworkRoot = dirname(dirname(__FILE__)).'/';
    $this->installationPath = papaya_systemtest::infoInstallPath();
    $this->sessionParamName = 'PAPAYA_SESS_'.$this->paramName;
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam('instdef_path_data');
    $this->initializeSessionParam('instdef_admin');
    $this->initializeSessionParam('instdef_surname');
    $this->initializeSessionParam('instdef_givenname');
    $this->initializeSessionParam('instdef_email');
    $this->initializeSessionParam('instdef_pw');
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
  * Execute
  *
  * @access public
  */
  function execute() {
    if (!isset($this->params['step'])) {
      $this->params['step'] = '';
    }
    $status = $this->getCurrentStatus();
    if ($status['authtables_exists']) {
      //need an authentication
      $this->initModuleManager();
      if ($this->initAuthentication()) {
        //authenticated
        switch ($this->params['step']) {
        case 'database' :
          $this->executeInstaller();
          break;
        case 'info' :
        default :
          $this->executeSystemTest();
        }
      } elseif (isset($this->params['cmd']) && trim($this->params['cmd']) != '') {
        $this->outputRPCResponse(
          -1,
          FALSE,
          $this->_gt(
            'Authentication needed - Please log in and restart installation process.'
          ),
          'authNeeded'
        );
      }
    } else {
      //authentication will not work yet
      switch ($this->params['step']) {
      case 'database' :
        $optionFileName = $this->pathFrameworkRoot.'modules/_base/DATA/table_options.xml';
        if (!$status['optiontable_exists'] &&
            isset($this->params['cmd']) && $this->params['cmd'] == 'table_create' &&
            isset($this->params['table']) && $this->params['table'] == PAPAYA_DB_TBL_OPTIONS) {
          $this->initModuleManager();
          if ($this->createTable($optionFileName, PAPAYA_DB_TBL_OPTIONS)) {
            $this->reloadInstaller(array('step' => 'database'));
          }
        }
        if ($this->checkTableExists(PAPAYA_DB_TBL_OPTIONS)) {
          $this->initModuleManager();
          $this->executeInstaller();
        } elseif ($status['optionfile_exists']) {
          $this->dialogCreateTable(PAPAYA_DB_TBL_OPTIONS);
        } else {
          $this->addMsg(
            MSG_ERROR,
            sprintf(
              $this->_gt('Cannot find "%s".'),
              papaya_strings::escapeHTMLChars($optionFileName)
            )
          );
        }
        break;
      case 'defaults' :
        $this->executeDefaults();
        break;
      case 'info' :
        $this->executeSystemTest();
        break;
      case 'license' :
        $this->executeLicense();
        break;
      case 'welcome' :
      default :
        $this->executeWelcome();
      }
    }
    if (!defined('PAPAYA_VERSION_STRING')) {
      define('PAPAYA_VERSION_STRING', '5');
    }
  }

  /**
  * execute defaults
  *
  * @access public
  */
  function executeDefaults() {

    include_once(PAPAYA_INCLUDE_PATH.'system/base_auth_secure.php');
    $changes = FALSE;
    $passwordError = FALSE;

    if (isset($this->params['save']) && $this->params['save'] == 1) {

      if (!$this->params['instdef_path_data'] || $this->params['instdef_path_data'] == '') {
        $this->addMSG(MSG_WARNING, $this->_gt('Check your input for: PAPAYA_PATH_DATA'));
      } else {
        if ($this->params['instdef_path_data'] != $this->params['old_path_data']) {
          $this->addMSG(MSG_INFO, $this->_gt('PAPAYA_PATH_DATA saved.'));
        }
      }
      if (!$this->params['instdef_admin'] || $this->params['instdef_admin'] == '') {
        $this->addMSG(MSG_WARNING, $this->_gt('Check your input for: Login name'));
      } else {
        if ($this->params['instdef_admin'] != $this->params['old_admin']) {
          $changes = TRUE;
        }
      }
      if (!$this->params['instdef_surname'] || $this->params['instdef_surname'] == '') {
        $this->addMSG(MSG_WARNING, $this->_gt('Check your input for: Surname'));
      } else {
        if ($this->params['instdef_surname'] != $this->params['old_surname']) {
          $changes = TRUE;
        }
      }
      if (!$this->params['instdef_givenname'] || $this->params['instdef_givenname'] == '') {
        $this->addMSG(MSG_WARNING, $this->_gt('Check your input for: Givenname'));
      } else {
        if ($this->params['instdef_givenname'] != $this->params['old_givenname']) {
          $changes = TRUE;
        }
      }
      if (!$this->params['instdef_email'] || $this->params['instdef_email'] == '') {
        $this->addMSG(MSG_WARNING, $this->_gt('Check your input for: Email'));
      } else {
        if (!checkit::isEmail($this->params['instdef_email'], TRUE)) {
          $this->addMSG(
            MSG_WARNING,
            $this->_gt('The input in Email must be a valid email address.')
          );
        } else {
          if ($this->params['instdef_email'] != $this->params['old_email']) {
            $changes = TRUE;
          }
        }
      }
      if (!$this->params['instdef_pw'] || $this->params['instdef_pw'] == '') {
        $this->addMSG(MSG_WARNING, $this->_gt('Check your input for: Password'));
        $passwordError = TRUE;
      }
      if (!$this->params['instdef_confpw'] || $this->params['instdef_confpw'] == '') {
        $this->addMSG(MSG_WARNING, $this->_gt('Retype your password.'));
        $passwordError = TRUE;
        $this->params['instdef_pw'] = '';
        $this->initializeSessionParam('instdef_pw');
        $this->setSessionValue($this->sessionParamName, $this->sessionParams);
      }
      if ($changes) {
        $this->addMSG(MSG_INFO, $this->_gt('Changes saved.'));
      }
      if (!$passwordError) {
        $this->params['instdef_pw'] = base_auth_secure::getPasswordHash(
          $this->params['instdef_pw']
        );
        $this->params['instdef_confpw'] = base_auth_secure::getPasswordHash(
          $this->params['instdef_confpw']
        );
        $this->initializeSessionParam('instdef_pw');
        $this->initializeSessionParam('instdef_confpw');
        $this->setSessionValue($this->sessionParamName, $this->sessionParams);

        if ($this->params['instdef_pw'] != $this->params['instdef_confpw']) {
          $this->addMSG(
            MSG_ERROR,
            $this->_gt('Passwords are different. Password changes not saved.')
          );
          $this->params['instdef_pw'] = '';
          $this->initializeSessionParam('instdef_pw');
          $this->setSessionValue($this->sessionParamName, $this->sessionParams);
        } else {
          if ($this->params['instdef_pw'] != $this->params['old_pw']) {
            $changes = TRUE;
          }
        }
      }
    }
    $this->layout->add($this->getXMLDefaults());
    $this->layout->addRight($this->getXMLDefaultsText());
  }

  /**
  * execute system test
  *
  * @access public
  */
  function executeSystemTest() {
    include_once(PAPAYA_INCLUDE_PATH.'system/papaya_systemtest.php');
    $systemTest = new papaya_systemtest();
    $systemTest->execute();
    $systemTest->images = &$this->images;
    $this->layout->add($systemTest->getXMLLists());
    $this->layout->addRight($this->getXMLInfoText($systemTest->resultTestSummary));
  }

  /**
  * execute license
  *
  * @access public
  */
  function executeLicense() {
    $this->layout->add($this->getXMLLicenseText());
  }

  /**
  * execute welcome screen
  *
  * @access public
  */
  function executeWelcome() {
    $this->layout->add($this->getXMLWelcomeText());
  }

  /**
  * Get current installation status
  * @return array
  */
  function getCurrentStatus() {
    $result = array(
      'database_connected' => $this->checkDatabase(),
      'optiontable_defined' =>
        defined('PAPAYA_DB_TBL_OPTIONS') && checkit::isAlpha(PAPAYA_DB_TBL_OPTIONS, TRUE),
      'optiontable_exists' => FALSE,
      'optionfile_exists' => FALSE,
      'authtables_exists' => FALSE,
      'login_exists' => FALSE
    );
    if ($result['database_connected'] && $result['optiontable_defined']) {
      $this->existingTables = array_flip($this->databaseQueryTableNames());
      $result['optiontable_exists'] = $this->checkTableExists(PAPAYA_DB_TBL_OPTIONS);
      $optionFileName = $this->pathFrameworkRoot.'modules/_base/DATA/table_options.xml';
      if (file_exists($optionFileName) &&
          is_file($optionFileName) &&
          is_readable($optionFileName)) {
        $result['optionfile_exists'] = TRUE;
      }
      /* we may be called before PapayaConfigurationCms::loadAndDefine() was called,
       * but the following functions depend on it,
       * thus check if an instance of PapayaConfigurationCms was injected
       * (then we assume loadAndDefine() was already called) */
      if ($result['optiontable_exists'] && isset($this->options)) {
        $result['authtables_exists'] = $this->checkAuthTables();
        $result['login_exists'] = $this->checkLoginExists();
      }
    }
    return $result;
  }

  /**
  * Execute Installer
  *
  * @access public
  */
  function executeInstaller() {
    if (!defined('PAPAYA_VERSION_STRING')) {
      include_once('./inc.version.php');
    }
    if (!isset($this->params['cmd'])) {
      $this->params['cmd'] = '';
    }
    switch ($this->params['cmd']) {
    case 'check_database':
      if (isset($this->params['table']) && isset($this->params['table_idx'])) {
        $count = $this->getTableCount(
          $this->params['table'],
          $this->papaya()->options->get('PAPAYA_DB_TABLEPREFIX')
        );
        $this->outputRPCResponse(
          (int)$this->params['table_idx'], TRUE, (int)$count, 'count'
        );
        if ($this->checkTableStruct(trim($this->params['table']))) {
          $this->outputRPCResponse(
            (int)$this->params['table_idx'],
            TRUE,
            sprintf($this->_gt('Table "%s" OK.'), $this->params['table']),
            'check'
          );
        } else {
          $this->outputRPCResponse(
            (int)$this->params['table_idx'],
            FALSE,
            sprintf($this->_gt('Table "%s" does not match data file.'), $this->params['table']),
            'check'
          );
        }
      } else {
        $this->outputRPCResponse(
          (int)$this->params['table_idx'],
          FALSE,
          sprintf($this->_gt('Table does not exist.'), $this->params['table']),
          'check'
        );
      }
      break;
    case 'install_database':
      if (isset($this->params['table']) && isset($this->params['table_idx'])) {
        $this->installDatabaseTable(
          trim($this->params['table']),
          (int)$this->params['table_idx']
        );
      }
      break;
    case 'reset_data':
      if (isset($this->params['table']) && isset($this->params['table_idx'])) {
        $this->outputRPCResponse(
          (int)$this->params['table_idx'], TRUE, 'Reset table content', 'update'
        );
        $this->resetDatabaseTableData(
          trim($this->params['table']), (int)$this->params['table_idx']
        );
      }
      break;
    case 'init_options':
      if ($this->initOptionValues()) {
        $this->outputRPCResponse(
          $this->params['table_idx'],
          TRUE,
          $this->_gt('Options checked.'),
          'init'
        );
      } else {
        $this->outputRPCResponse(
          $this->params['table_idx'],
          FALSE,
          $this->_gt('Options not checked.'),
          'init'
        );
      }
      $this->updatePathData();
      break;
    case 'init_modules':
      if ($this->initModules()) {
        $this->outputRPCResponse(
          $this->params['table_idx'],
          TRUE,
          $this->_gt('Scan completed.'),
          'init'
        );
      } else {
        $this->outputRPCResponse(
          $this->params['table_idx'],
          FALSE,
          $this->_gt('Could not complete modules scan.'),
          'init'
        );
      }
      break;
    default:
      $this->getTablesListView();
      $this->getProgressBarPanel();
      break;
    }
    $this->outputRPCResponseFinish();
  }

  /**
  * Initialize authentification
  *
  * @access public
  * @return boolean
  */
  function initAuthentication() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_auth.php');
    $GLOBALS['PAPAYA_USER'] = $this->papaya()->getObject('AdministrationUser');
    $this->authUser = &$GLOBALS['PAPAYA_USER'];
    $this->authUser->layout = &$this->layout;
    $this->authUser->images = &$this->images;
    $this->authUser->msgs = &$this->msgs;
    $this->authUser->initialize();
    if ($this->authUser->execLogin()) {
      if ($this->authUser->isAdmin()) {
        $this->layout->setParam('PAGE_USER', $this->authUser->user['fullname']);
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Initialize admin account
  *
  * @access public
  * @return boolean
  */
  function initAdminAccount() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_auth.php');
    $GLOBALS['PAPAYA_USER'] = $this->papaya()->getObject('AdministrationUser');
    $this->authUser = &$GLOBALS['PAPAYA_USER'];
    $this->authUser->layout = &$this->layout;
    $this->authUser->images = &$this->images;
    $this->authUser->msgs = &$this->msgs;
    $this->authUser->initialize();
    $sql = "SELECT user_id, group_id
              FROM %s
             WHERE group_id = -1 OR username = 'admin'
             ORDER BY group_id ASC";
    if ($res = $this->databaseQueryFmt($sql, PAPAYA_DB_TBL_AUTHUSER)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($row['group_id'] != -1) {
          $data = array(
            'group_id' => -1,
            'active' => 1
          );
          $this->databaseUpdateRecord(
            PAPAYA_DB_TBL_AUTHUSER, $data, 'user_id', $row['user_id']
          );
        }
        $this->authUser->load($row['user_id'], TRUE);
        $this->authUser->setSessionToken($row['user_id']);
        $this->authUser->synchronizeSurfer($row['user_id']);
        return $this->authUser->isAdmin();
      } elseif ($this->authUser->loadLogin('admin', '')) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Insert Admin Account
  *
  * @access public
  */
  function insertAdminAccount() {
    if (isset($this->params['instdef_pw']) &&
        $this->params['instdef_pw'] &&
        isset($this->params['instdef_admin']) &&
        $this->params['instdef_admin'] &&
        isset($this->params['instdef_surname']) &&
        $this->params['instdef_surname'] &&
        isset($this->params['instdef_givenname']) &&
        $this->params['instdef_givenname'] &&
        isset($this->params['instdef_email']) &&
        $this->params['instdef_email']) {

      foreach ($this->authTables as $table) {
        if (!$this->checkTableExists($this->databaseGetTableName($table))) {
          return FALSE;
        }
      }

      include_once(PAPAYA_INCLUDE_PATH.'system/base_auth.php');
      $GLOBALS['PAPAYA_USER'] = $this->papaya()->getObject('AdministrationUser');
      $this->authUser = &$GLOBALS['PAPAYA_USER'];
      $this->authUser->layout = &$this->layout;
      $this->authUser->images = &$this->images;
      $this->authUser->msgs = &$this->msgs;
      $this->authUser->initialize();
      $userId = md5(uniqid(rand(), TRUE));
      $data = array('user_id' => $userId,
                    'group_id' => -1,
                    'active' => 1,
                    'username' => $this->params['instdef_admin'],
                    'surname' => $this->params['instdef_surname'],
                    'givenname' => $this->params['instdef_givenname'],
                    'user_password' => $this->params['instdef_pw'],
                    'email' => $this->params['instdef_email'],
                    'userperm' => '');
      if ($this->databaseInsertRecord(PAPAYA_DB_TBL_AUTHUSER, NULL, $data)) {
        $this->authUser->setSessionToken($userId);
        $this->authUser->load($userId, TRUE);
        return $this->authUser->isAdmin();
      }
    }
    return FALSE;
  }

  /**
  * Synchronize Admin Account
  * for the back-end user with the front-end / surfer user
  *
  * @access public
  */
  function synchronizeAdminAccount() {
    if (isset($this->authUser) && $this->checkAuthTables()
        && $this->checkTableExists($this->authUser->tableCommunitySurfers)) {
      $this->authUser->synchronizeSurfer($this->authUser->userId);
    }
  }

  /**
  * Initialize path data
  *
  * @access public
  */
  function initPathData() {
    $path = '';
    if (isset($this->params['instdef_path_data']) && $this->params['instdef_path_data']) {
      $path = $this->params['instdef_path_data'];
    } else {
      $path = dirname($_SERVER['DOCUMENT_ROOT']).'/files/papaya-data/';
      if (!file_exists($path.'.')) {
        //next to document root
        $path = dirname($_SERVER['DOCUMENT_ROOT']).'/papaya-data/';
        if (!file_exists($path.'.')) {
          //document root subdirectory
          $path = $_SERVER['DOCUMENT_ROOT'].'/papaya-data/';
          if (!file_exists($path.'.')) {
            //installation root directory
            $path = $this->installationPath.'/papaya-data/';
          }
        }
      }
    }
    return $path;
  }
  /**
  * update path data
  *
  * @access public
  */
  function updatePathData() {
    $sql = "SELECT COUNT(*) FROM %s WHERE opt_name = 'PAPAYA_PATH_DATA'";
    if ($res = $this->databaseQueryFmt($sql, PAPAYA_DB_TBL_OPTIONS)) {
      if ($res->fetchField() == 0) {
        if ($value = $this->initPathData()) {
          $data = array(
            'opt_value' => $value,
            'opt_name' => 'PAPAYA_PATH_DATA'
          );
          return FALSE !== $this->databaseInsertRecord(PAPAYA_DB_TBL_OPTIONS, NULL, $data);
        }
      }
    }
    return FALSE;
  }
  /**
  * Initialize Phrases
  *
  * @access public
  */
  function initPhrases() {
    include_once(PAPAYA_INCLUDE_PATH.'system/sys_phrases.php');
    $application = $this->papaya();
    $application->phrases = new base_phrases();
    $application->phrases->getLngId(
      isset($this->authUser)
        ? $this->authUser->options['PAPAYA_UI_LANGUAGE']
        : $this->papaya()->options->get('PAPAYA_UI_LANGUAGE')
    );
  }

  /**
  * Get tables list view
  *
  * @access public
  */
  function getTablesListView() {
    $result = sprintf(
      '<listview title="%s">',
      papaya_strings::escapeHTMLChars($this->_gt('Tables'))
    );
    $result .= '<cols>';
    $result .= sprintf(
      '<col>%s</col>',
      papaya_strings::escapeHTMLChars($this->_gt('Name'))
    );
    $result .= sprintf(
      '<col align="center">%s</col>',
      papaya_strings::escapeHTMLChars($this->_gt('Found'))
    );
    $result .= sprintf(
      '<col align="center">%s</col>',
      papaya_strings::escapeHTMLChars($this->_gt('Structure'))
    );
    $result .= sprintf(
      '<col align="center">%s</col>',
      papaya_strings::escapeHTMLChars($this->_gt('Record count'))
    );
    $result .= sprintf(
      '<col align="center">%s</col>',
      papaya_strings::escapeHTMLChars($this->_gt('Default data'))
    );
    $result .= '</cols>';
    $result .= '<items>';
    $counter = 0;
    $max = count($tableNames = PapayaContentTables::getTables());
    foreach ($tableNames as $table) {
      $tableName = $this->databaseGetTableName($table);
      if ($tableName != $this->papaya()->options->get('PAPAYA_DB_TBL_OPTIONS')) {
        $result .= sprintf(
          '<listitem title="%s">',
          papaya_strings::escapeHTMLChars($tableName)
        );
        $imageIndex = ($this->checkTableExists($tableName))
          ? 'status-node-checked-disabled' : 'status-node-empty-disabled';
        $result .= sprintf(
          '<subitem align="center"><glyph src="%s" id="sign_exists_%s"/></subitem>',
          papaya_strings::escapeHTMLChars($this->images[$imageIndex]),
          papaya_strings::escapeHTMLChars($table)
        );
        $imageIndex = ($imageIndex != 'status-sign-problem')
          ? 'status-sign-off' : 'status-sign-problem';
        $result .= sprintf(
          '<subitem align="center"><glyph src="%s" id="sign_struct_%s"/></subitem>',
          papaya_strings::escapeHTMLChars($this->images[$imageIndex]),
          papaya_strings::escapeHTMLChars($table)
        );
        $count = ($this->checkTableExists($tableName)) ? '?' : '0';
        $result .= sprintf(
          '<subitem align="center"><div id="records_%s">%d</div></subitem>',
          papaya_strings::escapeHTMLChars($table), $count);
        if (file_exists($this->pathFrameworkRoot.'modules/_base/DATA/table_'.$table.'.csv')) {
          $result .= sprintf(
            '<subitem align="center">'.
              '<a href="javascript:jQuery.papayaInstaller.selectData(\'%s\',\'%d\');">'.
              '<glyph src="%s" id="box_data_%s"/></a></subitem>',
            papaya_strings::escapeHTMLChars($table),
            (int)$counter,
            papaya_strings::escapeHTMLChars($this->images['status-node-empty']),
            papaya_strings::escapeHTMLChars($table));
        } else {
          $result .= '<subitem />';
        }
        $result .= '</listitem>';
        $counter++;
      }
    }
    $result .= '</items>';
    $result .= '</listview>';
    $this->layout->add($result);
  }

  /**
  * Get progress javascript
  *
  * @param array &$tableCounts
  * @access public
  * @return string $result
  */
  function getProgressJavascript(&$tableCounts) {
    //$result = '<script type="text/javascript" src="script/xmlrpc.js"></script>'.LF;
    $result = '<script type="text/javascript" src="script/jquery.papayaInstaller.js"></script>'.LF;
    $result .= '<script type="text/javascript">'.LF;
    $result .= 'jQuery(document).ready(function() {'.LF;
    $result .= '  jQuery.papayaInstaller.init('.LF;
    $result .= '    {'.LF;
    $result .= '      parameterName : "'.$this->paramName.'",'.LF;
    $result .= '      imageOk : "'.$this->images['status-sign-ok'].'",'.LF;
    $result .= '      imageProblem : "'.$this->images['status-sign-problem'].'",'.LF;
    $result .= '      imageChecked : "'.$this->images['status-node-checked'].'",'.LF;
    $result .= '      imageUnchecked : "'.$this->images['status-node-empty'].'"'.LF;
    $result .= '    },'.LF;
    $result .= '    ['.LF;
    foreach (PapayaContentTables::getTables() as $table) {
      $tableName = $this->databaseGetTableName($table);
      if ($tableName != $this->papaya()->options->get('PAPAYA_DB_TBL_OPTIONS')) {
        if ($this->checkTableExists($tableName)) {
          $exists = 'true';
          $tableCounts['exists']++;
        } else {
          $exists = 'false';
        }
        if (file_exists(
              $this->pathFrameworkRoot.'modules/_base/DATA/table_'.$table.'.csv')) {
          $hasData = 'true';
          $tableCounts['hasdata']++;
        } else {
          $hasData = 'false';
        }
        if ($tableCounts['all'] > 0) {
          $result .= '      },'.LF;
        }
        $result .= '      {'.LF;
        $result .= '        name : "'.$table.'",'.LF;
        $result .= '        synced : false,'.LF;
        $result .= '        insert : false,'.LF;
        $result .= '        recordCount : 0,'.LF;
        $result .= '        exists : '.$exists.','.LF;
        $result .= '        csv : '.$hasData.LF;
        $tableCounts['all']++;
      }
    }
    $result .= '      }';
    $result .= '    ]'.LF;
    $result .= '  );'.LF;
    $result .= '});'.LF;
    $result .= '</script>'.LF;
    return $result;
  }

  /**
  * Get installer table line
  *
  * @param integer $no
  * @param integer $id
  * @param string $title
  * @param string $text
  * @param boolean $enabled optional, default value TRUE
  * @access public
  * @return string $result
  */
  function getInstallerTableLine($no, $id, $title, $text, $enabled = TRUE) {
    if ($enabled) {
      $styleDefaultLayer = '';
      $styleActiveLayer = ' style="display: none;"';
    } else {
      $styleDefaultLayer = ' style="display: none;"';
      $styleActiveLayer = '';
    }
    $result = '<tr id="header'.$id.'"'.$styleDefaultLayer.'>'.LF;
    $result .= '<td class="bullet"><a href="#" onclick="initInstall'.$id.
      '();"><img src="pics/steps/step'.$no.'.gif" alt="Step '.$no.'" /></a></td>'.LF;
    $result .= '<th><a href="#"'.
      ' onclick="jQuery.papayaInstaller.startStep(\''.$id.'\');">'.$title.'</a></th>'.LF;
    $result .= '</tr>'.LF;
    $result .= '<tr id="headerDisabled'.$id.'"'.$styleActiveLayer.'>'.LF;
    $result .= '<td class="bullet"><img src="pics/steps/step'.$no.
      '_disabled.gif" alt="Step '.$no.'" /></td>'.LF;
    $result .= '<th>'.$title.'</th>'.LF;
    $result .= '</tr>'.LF;
    if (isset($text)) {
      $result .= '<tr>'.LF;
      $result .= '<td> </td>'.LF;
      $result .= '<td><p>'.$text.'</p></td>';
      $result .= '</tr>'.LF;
    }
    return $result;
  }

  /**
  * Get progress bar panel
  *
  * @access public
  */
  function getProgressBarPanel() {
    $tableCounts = array('all' => 0, 'exists' => 0, 'hasdata' => 0);
    $step = 0;
    $this->layout->addScript($this->getProgressJavascript($tableCounts));
    $result = '<sheet>'.LF;
    $result .= '<text>'.LF;
    $result .= '<div class="installer">'.LF;
    $result .= '<table>'.LF;
    $result .= '<tr class="headerJavascriptWarning"><td colspan="2">';
    $result .= sprintf(
      '<h2>%s</h2><p>%s</p>',
      papaya_strings::escapeHTMLChars('Javascript Warning!'),
      papaya_strings::escapeHTMLChars('The installer needs javascript now, please activate it.')
    );
    $result .= '</td></tr>'.LF;
    if ($tableCounts['exists'] > 0) {
      $result .= $this->getInstallerTableLine(
        ++$step,
        'Analyze',
        papaya_strings::escapeHTMLChars('Analyze Database'),
        papaya_strings::escapeHTMLChars('Checks the structure of all existing tables'),
        ($tableCounts['exists'] > 0));
      $result .= $this->getInstallerTableLine(
        ++$step,
        'Create',
        papaya_strings::escapeHTMLChars('Update Database'),
        papaya_strings::escapeHTMLChars('Creates missing tables and updates existing tables'),
        ($tableCounts['exists'] == 0));
    } else {
      $result .= $this->getInstallerTableLine(
        ++$step,
        'Create',
        papaya_strings::escapeHTMLChars('Initialize Database'),
        papaya_strings::escapeHTMLChars('Creates tables'),
        ($tableCounts['exists'] == 0));
    }
    $result .= $this->getInstallerTableLine(
      ++$step,
      'Insert',
      papaya_strings::escapeHTMLChars('Insert default data'),
      papaya_strings::escapeHTMLChars(
        'Deletes current records in selected tables and'.
        ' inserts default data. You can select the tables by clicking on the checkboxes'.
        ' in the right column of the large listview.'
      ),
      FALSE
    );
    $result .= $this->getInstallerTableLine(
      ++$step,
      'Init',
      papaya_strings::escapeHTMLChars('Check options and modules'),
      papaya_strings::escapeHTMLChars(
        'Sets default values for undefined options and searches for modules'
      ),
      FALSE
    );
    $result .= $this->getInstallerTableLine(
      ++$step,
      'GoTo',
      papaya_strings::escapeHTMLChars('Go to admin interface'),
      NULL,
      TRUE
    );
    $result .= '</table>'.LF;
    $result .= '</div>'.LF;
    $result .= '</text>'.LF;
    $result .= '</sheet>'.LF;
    $this->layout->addRight($result);
  }

  /**
  * Reload installer
  *
  * @access public
  */
  function reloadInstaller($params) {
    $link = $this->getLink($params);
    $url = $this->getAbsoluteURL($link);
    if (php_sapi_name() == 'cgi') {
      header('Status: 302 Found');
    } elseif (php_sapi_name() == 'cgi-fcgi' || php_sapi_name() == 'fast-cgi') {
      header('HTTP/1.1 302 Found');
      header('Status: 302 Found');
    } else {
      header('HTTP/1.1 302 Found');
    }
    if (!$this->papaya()->options->get('PAPAYA_DISABLE_XHEADERS')) {
      header('X-Papaya-Status: reloading installer');
    }
    header('Location: '.$url);
    exit;
  }

  /**
  * Check auth tables
  *
  * @access public
  * @return boolean
  */
  function checkAuthTables() {
    //tables missed, check table structure
    $missed = 0;
    $old = 0;
    foreach ($this->authTables as $table) {
      if (!$this->checkTableExists($this->databaseGetTableName($table))) {
        $missed++;
      } elseif (!$this->checkTableStruct($table)) {
        $old++;
      }
    }
    if ($missed + $old == 0) {
      $sql = "SELECT COUNT(*) FROM %s";
      $params = array(
        $this->databaseGetTableName(PapayaContentTables::AUTHENTICATION_USERS)
      );
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        list($count) = $res->fetchRow();
        return ($count > 0) ? TRUE : FALSE;
      }
    }
    return FALSE;
  }

  /**
  * check if here is a least one login
  *
  * @access public
  * @return boolean
  */
  function checkLoginExists() {
    $userTable = $this->databaseGetTableName(PapayaContentTables::AUTHENTICATION_USERS);
    if ($this->checkTableExists($userTable)) {
      $sql = "SELECT COUNT(*) FROM %s";
      if ($res = $this->databaseQueryFmt($sql, $userTable)) {
        return $res->fetchField() > 0;
      }
    }
    return FALSE;
  }

  /**
  * Check database
  *
  * @access public
  * @return boolean
  */
  function checkDatabase() {
    try {
      $database = $this->getDatabaseAccess()->getDatabaseConnector();
      if ($database->connect($this, FALSE)) {
        return TRUE;
      }
    } catch (PapayaDatabaseExceptionConnect $e) {
    } catch (InvalidArgumentException $e) {
    }
    return FALSE;
  }

  /**
  * Check table exists
  *
  * @param $tableName
  * @access public
  * @return boolean
  */
  function checkTableExists($tableName) {
    return (isset($this->existingTables[$tableName]));
  }

  /**
  * Check table structure
  *
  * @param string $table
  * @access public
  * @return boolean $result
  */
  function checkTableStruct($table) {
    $this->initModuleManager();
    $tableFileName = $this->pathFrameworkRoot.'modules/_base/DATA/table_'.$table.'.xml';
    $result = TRUE;
    if ($struct = $this->moduleManager->loadTableStructure($tableFileName)) {
      if (isset($struct['actions']) && (int)$struct['actions'] > 0) {
        $result = FALSE;
      }
      unset($struct);
    }
    return $result;
  }

  /**
  * Get table count
  *
  * @param table $table
  * @param mixed $prefix optional string, default value NULL
  * @access public
  * @return integer $count or 0
  */
  function getTableCount($table, $prefix = NULL) {
    $tableName = (isset($prefix)) ? $prefix.'_'.$table : $table;
    $sql = "SELECT COUNT(*)
              FROM %s";
    if ($res = $this->databaseQueryFmt($sql, $tableName)) {
      list($count) = $res->fetchRow();
      return $count;
    }
    return 0;
  }

  /**
  * Initialize module manager
  *
  * @access public
  */
  function initModuleManager() {
    if (!(isset($this->moduleManager) && is_object($this->moduleManager))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/papaya_modulemanager.php');
      $this->moduleManager = new papaya_modulemanager();
      $this->moduleManager->alwaysPrefix = TRUE;
      $this->moduleManager->loadPackageWithDataFromDirectory(
        $this->pathFrameworkRoot.'modules/',
        '_base/'
      );
    }
  }

  /**
  * Create table dialog
  *
  * @param string $tableName
  * @param mixed $prefix optional string, default value NULL
  * @access public
  */
  function dialogCreateTable($tableName, $prefix = NULL) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $hidden = array(
      'step' => 'database',
      'cmd' => 'table_create',
      'table' => $tableName,
      'table_create_confirm' => 1,
    );
    $table = (isset($prefix)) ? $prefix.'_'.$tableName : $tableName;
    $msg = sprintf(
      $this->_gt('Create table "%s"?'),
      $table
    );
    $dialog = new base_msgdialog(
      $this, $this->paramName, $hidden, $msg, 'question'
    );
    $dialog->msgs = &$this->msgs;
    $dialog->buttonTitle = 'Create';
    if ($str = $dialog->getMsgDialog()) {
      $this->layout->add($str);
    }
  }

  /**
  * Create table
  *
  * @param string $xmlFileName
  * @param string $tableName
  * @access public
  * @return boolean $result
  */
  function createTable($xmlFileName, $tableName) {
    $result = FALSE;
    if ($struct = $this->moduleManager->loadTableStructure($xmlFileName)) {
      $struct['name'] = $tableName;
      if ($this->databaseCreateTable($struct, NULL)) {
        unset($this->params['cmd']);
        $this->addMsg(MSG_INFO, $this->_gt('Table created.'));
        $result = TRUE;
      }
      unset($struct);
    }
    return $result;
  }

  /**
  * Synchronize table
  *
  * @param string $xmlFileName
  * @param string $tableName
  * @access public
  * @return boolean $result
  */
  function syncTable($xmlFileName, $tableName) {
    $result = FALSE;
    if ($struct = $this->moduleManager->loadTableStructure($xmlFileName)) {
      $struct['name'] = $tableName;
      if ($this->moduleManager->syncTableStructure($struct)) {
        $result = TRUE;
      }
      unset($struct);
    }
    return $result;
  }

  /**
  * Install database table
  *
  * @param string $table
  * @param integer $idx
  * @access public
  */
  function installDatabaseTable($table, $idx) {
    if (preg_match('/^\w+$/', $table)) {
      $tableName = $this->databaseGetTableName($table);
      $tableFileName = $this->pathFrameworkRoot.'modules/_base/DATA/table_'.$table.'.xml';
      if (file_exists($tableFileName)) {
        if ($this->checkTableExists($tableName)) {
          if ($this->syncTable($tableFileName, $table)) {
            $this->outputRPCResponse(
              $idx, TRUE, $this->_gt('Table structure updated.'), 'sync'
            );
          } else {
            $this->outputRPCResponse(
              $idx, FALSE, $this->_gt('Database error.'), 'sync'
            );
          }
        } else {
          if ($this->createTable($tableFileName, $tableName)) {
            $this->existingTables[$tableName] = TRUE;
            if (in_array($table, $this->authTables)) {
              $this->insertAdminAccount();
            }
            if ($tableName == PAPAYA_DB_TBL_SURFER) {
              $this->synchronizeAdminAccount();
            }
            $this->outputRPCResponse(
              $idx, TRUE, $this->_gt('Table created.'), 'sync'
            );
          } else {
            $this->outputRPCResponse(
              $idx, FALSE, $this->_gt('Database error.'), 'sync'
            );
          }
        }
      } else {
        $this->outputRPCResponse(
          $idx, FALSE, $this->_gt('File not found.'), 'sync'
        );
      }
    } else {
      $this->outputRPCResponse(
        $idx, FALSE, $this->_gt('Invalid table name.'), 'sync'
      );
    }
  }

  /**
  * Reset database table data
  *
  * @param string $table
  * @param integer $idx
  * @access public
  */
  function resetDataBaseTableData($table, $idx) {
    if (preg_match('/^\w+$/', $table)) {
      $tableName = $this->databaseGetTableName($table);
      $dataFileName = $this->pathFrameworkRoot.'modules/_base/DATA/table_'.$table.'.csv';
      if (file_exists($dataFileName)) {
        if ($this->checkTableExists($tableName)) {
          if (FALSE !== $this->databaseEmptyTable($tableName)) {
            if (is_array($done = $this->insertCSV($tableName, $dataFileName))) {
              $count = $this->getTableCount(
                $this->params['table'], $this->papaya()->options->get('PAPAYA_DB_TABLEPREFIX')
              );
              if ($tableName == PAPAYA_DB_TBL_AUTHUSER) {
                $this->initAdminAccount();
              }
              $this->outputRPCResponse(
                (int)$this->params['table_idx'], TRUE, (int)$count, 'count'
              );
              $this->outputRPCResponse(
                $idx,
                TRUE,
                sprintf(
                  $this->_gt('Done. %d queries for %d bytes data'),
                  $done['queries'],
                  $done['data']
                ),
                'reset'
              );
            } else {
              $this->outputRPCResponse(
                (int)$this->params['table_idx'], TRUE, 0, 'count'
              );
              $this->outputRPCResponse(
                $idx, FALSE, $this->_gt('Database error.'), 'reset'
              );
            }
          } else {
            $this->outputRPCResponse(
              $idx, FALSE, $this->_gt('Database error.'), 'reset'
            );
          }
        } else {
          $this->outputRPCResponse(
            $idx, FALSE, $this->_gt('Table not found.'), 'reset'
          );
        }
      } else {
        $this->outputRPCResponse(
          $idx, FALSE, $this->_gt('File not found.'), 'reset'
        );
      }
    } else {
      $this->outputRPCResponse(
        $idx, FALSE, $this->_gt('Invalid table name.'), 'reset'
      );
    }
  }

  /**
  * Output rpc command response
  *
  * @param integer $idx
  * @param boolean $success
  * @param string $msg
  * @param string $cmd
  * @access public
  */
  function outputRPCResponse($idx, $success, $msg, $cmd) {
    if ($this->outputMode == 'xml') {
      if (!$this->rpcResponseSent) {
        $this->rpcResponseSent = TRUE;
        header('Content-type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<responses>';
      }
      echo '<response>';
      printf(
        '<method>rpcCallbackInstaller%s</method>',
        papaya_strings::escapeHTMLChars(ucfirst($cmd))
      );
      printf('<param name="idx" value="%d" />', (int)$idx);
      printf('<param name="success" value="%d" />', (int)$success);
      printf('<param name="message" value="%s" />', papaya_strings::escapeHTMLChars($msg));
      echo '<data></data>';
      echo '</response>';
      flush();
    }
  }

  /**
  * Output rpc response finish
  *
  * @access public
  */
  function outputRPCResponseFinish() {
    if ($this->outputMode == 'xml' && $this->rpcResponseSent) {
      echo '</responses>';
    }
  }

  /**
  * Insert CSV
  *
  * @param string $table
  * @param string $file
  * @access public
  * @return array
  */
  function insertCSV($table, $file) {
    $dataSize = 0;
    $sqlQueryCount = 0;
    include_once(PAPAYA_INCLUDE_PATH.'system/sys_checkit.php');
    include_once(PAPAYA_INCLUDE_PATH.'system/papaya_strings.php');
    if ($fh = fopen($file, 'r')) {
      $buffer = array();
      $bufferSize = 0;
      $maximumSize = filesize($file);
      if ($dataFields = papaya_strings::fgetcsv($fh, $maximumSize)) {
        foreach ($dataFields as $key => $val) {
          $fields[$key] = papaya_strings::ensureUTF8(
            strtr($val, array('\\\\n'=>'\\n', '\\n'=>"\n")));
        }
      }
      $rpcResponse = ((!ini_get('session.use_trans_sid')) && $this->rpcResponseSent);
      if ($rpcResponse) {
        $this->outputRPCResponse(
          (int)$this->params['table_idx'], TRUE, 'Reading ', 'update'
        );
      }
      while ($dataLine = papaya_strings::fgetcsv($fh, $maximumSize)) {
        $dataRow = array();
        foreach ($dataLine as $key => $val) {
          $dataRow[$fields[$key]] = papaya_strings::ensureUTF8(
            strtr($val, array('\\\\n'=>'\\n', '\\n'=>"\n")));
          $bufferSize += strlen($dataRow[$fields[$key]]);
        }
        $buffer[] = $dataRow;
        if ($bufferSize > 10000 && count($buffer) > 0) {
          if ($rpcResponse) {
            $this->outputRPCResponse(
              (int)$this->params['table_idx'], TRUE, 'Insert data', 'update'
            );
          }
          $this->databaseInsertRecords($table, $buffer);
          $buffer = array();
          $dataSize += $bufferSize;
          $sqlQueryCount++;
          $bufferSize = 0;
          if ($rpcResponse) {
            $this->outputRPCResponse(
              (int)$this->params['table_idx'], TRUE, 'Reading', 'update'
            );
          }
        }
      }
      if ($bufferSize > 0 && count($buffer) > 0) {
        if ($rpcResponse) {
          $this->outputRPCResponse(
            (int)$this->params['table_idx'], TRUE, 'Insert data', 'update'
          );
        }
        $this->databaseInsertRecords($table, $buffer);
        $dataSize += $bufferSize;
        $sqlQueryCount++;
      }
      return array('queries' => $sqlQueryCount, 'data' => $dataSize);
    }
    return FALSE;
  }

  /**
  * Initialize option values
  *
  * @access public
  * @return boolean
  */
  function initOptionValues() {
    $this->papaya()->options->load();
  }

  /**
  * Initialize modules
  *
  * @access public
  * @return boolean
  */
  function initModules() {
    $modules = new papaya_modulemanager();
    $path = dirname(dirname(__FILE__));
    $modules->loadTables = TRUE;
    $modules->searchModules($path.'/'.$modules->modulesPath);
    if (isset($modules->packages) && is_array($modules->packages) &&
        count($modules->packages) > 0 && isset($modules->modules) &&
        is_array($modules->modules) && count($modules->modules) > 0) {
      $modules->updatePackageTable();
      $this->outputRPCResponse(
        PAPAYA_DB_TBL_MODULEGROUPS,
        TRUE,
        (int)$this->getTableCount(PAPAYA_DB_TBL_MODULEGROUPS),
        'count'
      );
      $modules->updateModuleTable();
      $this->outputRPCResponse(
        PAPAYA_DB_TBL_MODULES,
        TRUE,
        (int)$this->getTableCount(PAPAYA_DB_TBL_MODULES),
        'count'
      );
      return TRUE;
    }

    return FALSE;
  }

  /**
  * Get test information summary xml
  * @param array $testSummary
  * @return string
  */
  function getXMLInfoText($testSummary) {
    $status = $this->getCurrentStatus();
    if ($status['authtables_exists'] || $status['login_exists']) {
      $link = 'database';
    } else {
      $link = 'defaults';
    }
    $result = '<sheet><text>'.LF;
    $result .= '<div class="installer">';
    $result .= '<h1>papaya CMS 5 installation tests.</h1>'.LF;
    $result .= '<p>The Installer has run some tests.</p>'.LF;
    $result .= '<table>';
    if ($testSummary[TESTRESULT_FAILED] > 0) {
      $result .= '<tr><td><img src="pics/icons/16x16/'.
         papaya_strings::escapeHTMLChars($this->images['status-sign-problem']).
         '" class="glyph16"/></td>';
      $result .= '<td><p>One or more tests <b>FAILED</b>. Please check the list to'.
        ' the left for details and compare it with the system requirements.'.
        '</p></td></tr>'.LF;
    }
    if ($testSummary[TESTRESULT_UNKNOWN] > 0) {
      $result .= '<tr><td><img src="pics/icons/16x16/'.
        papaya_strings::escapeHTMLChars($this->images['status-sign-info']).
        '" class="glyph16"/></td>';
      $result .= '<td><p>One or more features could not be tested. You need to check'.
        ' these features manually.</p></td></tr>'.LF;
    }
    if ($testSummary[TESTRESULT_OPTIONAL] > 0) {
      $result .= '<tr><td><img src="pics/icons/16x16/'.
        papaya_strings::escapeHTMLChars($this->images['status-sign-warning']).
        '" class="glyph16"/></td>';
      $result .= '<td><p>One or more optional features are not available. Plase check'.
        ' the lists to the left for  details.</p></td></tr>'.LF;
    }
    $result .= '</table>'.LF;
    if ($testSummary[TESTRESULT_FAILED] == 0) {
      $result .= sprintf(
        '<a href="%s" class="nextLink">Next &gt;&gt;</a>',
        papaya_strings::escapeHTMLChars($this->getLink(array('step' => $link)))
      );
    } else {
      $result .= '<p class="error">Your server does not support <i>papaya CMS 5</i>'.
        ' currently. The installation can not continue.</p>'.LF;
    }
    $result .= '<p>If you need more information about the system requirements and'.
      ' the installation, please check the following links.</p>'.LF;
    $result .= '<ul>';
    $result .= '<li><a href="http://www.papaya-cms.com/faq">FAQ</a></li>';
    $result .=
      '<li><a href="http://www.papaya-cms.com/installforum">Installation Forum</a></li>';
    $result .=
      '<li><a href="http://www.papaya-cms.com/support">Installation Support</a></li>';
    $result .= '<li><a href="http://www.papaya-cms.com/">papaya CMS Website</a></li>';
    $result .= '</ul>';
    $result .= '</div>';
    $result .= '</text></sheet>'.LF;
    return $result;
  }
  /**
  * get xml welcome text
  *
  * @access public
  * @return xml string
  */
  function getXMLWelcomeText() {
    $result = '<sheet width="800px">'.LF;
    $result .= '<text>'.LF;
    $result .= '<div class="installer">';
    $result .= '<br/>'.LF;
    $result .= '<h1>Welcome to the papaya CMS 5 installation.</h1>'.LF;
    $result .= '<p>The installer will guide you through the installation of'.
      ' papaya CMS 5 step by step.</p>'.LF;
    $result .= sprintf(
      '<a href="%s" class="nextLink">Next &gt;&gt;</a>',
      papaya_strings::escapeHTMLChars($this->getLink(array('step' => 'license')))
    );
    $result .= '<p>If you need more information about the system requirements and'.
      ' the installation, please check the following links.</p>'.LF;
    $result .= '<ul>';
    $result .= '<li><a href="http://www.papaya-cms.com/faq">FAQ</a></li>';
    $result .=
      '<li><a href="http://www.papaya-cms.com/installforum">Installation Forum</a></li>';
    $result .=
      '<li><a href="http://www.papaya-cms.com/support">Installation Support</a></li>';
    $result .= '<li><a href="http://www.papaya-cms.com/">papaya CMS Website</a></li>';
    $result .= '</ul>';
    $result .= '</div>';
    $result .= '</text>'.LF;
    $result .= '</sheet>'.LF;
    return $result;
  }

  /**
  * get xml license text
  *
  * @access public
  * @return xml string
  */
  function getXMLLicenseText() {
    $data = $this->getLicense();
    $result = '<sheet width="800px" align="center">';
    $result .= '<text><div class="installer">';
    $result .= '<h1 style="padding-left: 4px;">papaya CMS 5 license.</h1>'.LF;
    $result .= '<p style="padding-left: 4px;">To install papaya CMS 5 please read the'.
      ' terms of the following license before you continue.</p>'.LF;
    $result .= '<br/>'.LF;
    $result .= '<div style="text-align: center; padding-top: 2em; font-weight: bold;">';
    $result .= sprintf(
      '<a href="%s" class="nextLink">Accept license &gt;&gt;</a>',
      papaya_strings::escapeHTMLChars($this->getLink(array('step' => 'info')))
    );
    $result .= '</div>';
    $result .= '<hr/>';
    if ($this->licenseLng == 'de-DE') {
      $result .= sprintf(
        '<a href="%s" class="rightLink">English version</a>',
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('step' => 'license', 'license_lng' => 'en-US'))
        )
      );
    } else {
      $result .= sprintf(
        '<a href="%s" class="rightLink">German version</a>',
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('step' => 'license', 'license_lng' => 'de-DE'))
        )
      );
    }
    $result .= $data.'</div></text>';
    $result .= '</sheet>';
    return $result;
  }
  /**
  * get xml defaults text
  *
  * @access public
  * @return xml string
  */
  function getXMLDefaultsText() {
    $result = '<sheet width="300px" align="center">';
    $result .= '<text><div class="installer">';
    $result .= '<h1 style="padding-left: 4px;">papaya CMS 5 default data.</h1>'.LF;
    $result .= '<p style="padding-left: 4px;">You need to enter some default data'.
      ' before you continue installation.</p>'.LF;
    $result .= '<br/>'.LF;
    if ($this->params['instdef_path_data'] &&
        $this->params['instdef_path_data'] != '' &&
        $this->params['instdef_admin'] &&
        $this->params['instdef_admin'] != '' &&
        $this->params['instdef_surname'] &&
        $this->params['instdef_surname'] != '' &&
        $this->params['instdef_givenname'] &&
        $this->params['instdef_givenname'] != '' &&
        $this->params['instdef_email'] &&
        $this->params['instdef_email'] != '' &&
        checkit::isEmail($this->params['instdef_email'], TRUE) &&
        $this->params['instdef_pw'] &&
        $this->params['instdef_pw'] != '') {

      $result .= sprintf(
        '<a href="%s" class="nextLink">Next &gt;&gt;</a>',
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('step' => 'database'))
        )
      );
    }
    $result .= '<br/><p style="padding-left: 4px;">If you need more information'.
      ' about the system requirements and the installation, please check the following'.
      ' links.</p>'.LF;
    $result .= '<ul>';
    $result .= '<li><a href="http://www.papaya-cms.com/faq">FAQ</a></li>';
    $result .=
      '<li><a href="http://www.papaya-cms.com/installforum">Installation Forum</a></li>';
    $result .=
      '<li><a href="http://www.papaya-cms.com/support">Installation Support</a></li>';
    $result .= '<li><a href="http://www.papaya-cms.com/">papaya CMS Website</a></li>';
    $result .= '</ul>';
    $result .= '</div></text>';
    $result .= '</sheet>';
    return $result;
  }
  /**
  * get xml defaults
  *
  * @access public
  * @return xml string
  */
  function getXMLDefaults() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    $hidden = array(
      'step' => 'defaults',
      'save' => 1,
      'old_path_data' => $this->params['instdef_path_data'],
      'old_admin' => $this->params['instdef_admin'],
      'old_surname' => $this->params['instdef_surname'],
      'old_givenname' => $this->params['instdef_givenname'],
      'old_email' => $this->params['instdef_email'],
      'old_pw' => $this->params['instdef_pw']);
    $fields = array(
      'instdef_path_data' =>
        array('PAPAYA_PATH_DATA', 'isPath', TRUE, 'input', 80, '', ''),
      'Administrator Login',
      'instdef_givenname' =>
        array('Givenname', 'isAlphaNum', TRUE, 'input', 50, '', ''),
      'instdef_surname' =>
        array('Surname', 'isAlphaNum', TRUE, 'input', 50, '', ''),
      'instdef_email' =>
        array('Email', 'isEMail', TRUE, 'input', 50, '', ''),
      'instdef_admin' =>
        array('Login name', 'isAlphaNum', TRUE, 'input', 30, '', ''),
      'instdef_pw' =>
        array('Password', 'isPassword', TRUE, 'password', 30, '', ''),
      'instdef_confpw' =>
        array('Repetition', 'isPassword', TRUE, 'password', 30,
          'Please input your password again.', ''));
    $data = array(
      'instdef_path_data' => $this->initPathData(),
      'instdef_admin' => $this->params['instdef_admin'],
      'instdef_surname' => $this->params['instdef_surname'],
      'instdef_givenname' => $this->params['instdef_givenname'],
      'instdef_email' => $this->params['instdef_email']);
    $this->fieldDialog = new base_dialog(
      $this, $this->paramName, $fields, $data, $hidden
    );
    $this->fieldDialog->dialogTitle = 'papaya CMS 5 default data';
    $this->fieldDialog->baseLink = $this->baseLink;
    $this->fieldDialog->msgs = &$this->msgs;
    $this->fieldDialog->inputFieldSize = 'x-large';
    $this->fieldDialog->loadParams();
    return $this->fieldDialog->getDialogXml();
  }
  /**
  * Phrases - Locate files
  *
  * @param string $fileName filename
  * @access public
  * @return string file content
  */
  function getLicense() {
    if (isset($this->params['license_lng']) && $this->params['license_lng'] != '') {
      $this->licenseLng = $this->params['license_lng'];
    } else {
      if (isset($this->authUser)) {
        $this->licenseLng = $this->authUser->options['PAPAYA_UI_LANGUAGE'];
      } else {
        $this->licenseLng = $this->papaya()->options->get('PAPAYA_UI_LANGUAGE');
      }
    }
    $fileName = $this->installationPath.$this->papaya()->options->get('PAPAYA_PATH_ADMIN').
      '/data/'.$this->licenseLng.'/gpl.txt';
    $fileName = PapayaUtilFilePath::cleanup($fileName, FALSE);
    if ($fileName) {
      if ($data = @file_get_contents($fileName)) {
        return papaya_strings::ensureUTF8($data);
      }
    }
    return '';
  }
}

?>

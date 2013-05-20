<?php
/**
 * Provide functionality for log messages via jabber/xmpp.
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
 * @package Papaya-Modules
 * @subpackage Beta-JabberLogger
 * @version $Id: jabber_logger.php 33498 2010-01-12 16:25:43Z elbrecht $
 */

/**
 * Include parent class definition:
 */
require_once(PAPAYA_INCLUDE_PATH.'system/base_plugin.php');

/**
 * Module option class.
 */
require_once(PAPAYA_INCLUDE_PATH.'system/base_module_options.php');

/**
 * Provides for log messages via jabber.
 *
 * @package Papaya-Modules
 * @subpackage Beta-JabberLogger
 */
class jabber_logger extends base_plugin {

  /**
  * Indicates if there is an active connection
  * @var boolean
  */
  var $connectionUp = FALSE;

  var $active = FALSE;
  var $host;
  var $port;
  var $user;
  var $password;
  var $resource;
  var $server = NULL;
  var $printlog = FALSE;
  var $loglevel = NULL;
  var $useEncryption;
  var $useSSL;
  var $recipient;

  /**
   * Module options
   *
   * @var array $pluginOptionFields
   */
  var $pluginOptionFields = array(
    'active' => array('Active', '', TRUE, 'yesno', 1, 'Activate this logger module?', 0),
    'host' => array('Host', 'isNoHTML', TRUE, 'input', 200, '', 'jabber.papaya-cms.com'),
    'port' => array('Port', 'isNum', TRUE, 'input', 5, '', 5223),
    'user' => array('User', 'isSomeText', TRUE, 'input', 200, '', 'papaya5dev'),
    'password' => array('Password', 'isSomeText', TRUE, 'password', 200, '', 'pass1234'),
    'resource' => array('Resource', 'isNoHTML', TRUE, 'input', 200, '', 'Papaya/'),
    'server' => array('Server', 'isNoHTML', TRUE, 'input', 200, '', 'jabber.papaya-cms.com'),
    'printlog' => array('Print log', '', TRUE, 'yesno', 0),
    'loglevel' => array('Log level', 'isNum', TRUE, 'input', 1, '0..4', 0),
    'encryption' => array('Use encryption', '', TRUE, 'yesno', 0),
    'ssl' => array('Use SSL', '', TRUE, 'yesno', 0),
    'recipient' => array('Recipient', 'isSomeText', TRUE, 'input', 200,
                         'Where to send messages to', 'elbrecht@jabber.papaya-cms.com')
  );

  /**
   * Load some configuration data from module options
   */
  function loadConfig() {
    $this->active = base_module_options::readOption($this->guid, 'active') ? TRUE : FALSE;
    $this->host = base_module_options::readOption($this->guid, 'host');
    $this->port = base_module_options::readOption($this->guid, 'port');
    $this->user = base_module_options::readOption($this->guid, 'user');
    $this->password = base_module_options::readOption($this->guid, 'password');
    $this->resource = base_module_options::readOption($this->guid, 'resource');
    $this->server = base_module_options::readOption($this->guid, 'server');
    $this->printlog = base_module_options::readOption($this->guid, 'printlog') ? TRUE : FALSE;
    $this->loglevel = base_module_options::readOption($this->guid, 'loglevel');
    $this->useEncryption = base_module_options::readOption($this->guid, 'encryption')
      ? TRUE : FALSE;
    $this->useSSL = base_module_options::readOption($this->guid, 'ssl') ? TRUE : FALSE;
    $this->recipient = base_module_options::readOption($this->guid, 'recipient');
  }

  /**
  * Get Jabber connection for logging. Stores connection in static variable.
  *
  * @return boolean|XMPPHP_XMPP Jabber connection or FALSE on failure.
  * @access public
  */
  function getJabberConnection() {
    static $conn = NULL;
    if ($conn === NULL) {
      $this->loadConfig();
      if ($this->active && $this->includeXmpphpClass()) {
        $conn = new XMPPHP_XMPP(
          $this->host,
          $this->port,
          $this->user,
          $this->password,
          $this->resource,
          $this->server,
          $this->printlog,
          $this->loglevel
        );
        $conn->useEncryption($this->useEncryption);
        $conn->useSSL($this->useSSL);
        //self::$conn->autoSubscribe(TRUE);
        $conn->connect();
        $conn->processUntil('session_start');
        $conn->presence();
        $this->connectionUp = TRUE; // save for destructor method.
      } else {
        $conn = FALSE;
      }
    }
    return $conn;
  }

  /**
  * Include xmpp class definition file if it exists.
  *
  * @return boolean Success status.
  * @access private
  */
  function includeXmpphpClass() {
    $className = 'XMPPHP_XMPP';
    if (class_exists($className)) {
      return TRUE;
    }
    $fileName = dirname(__FILE__) . '/external/xmpphp/XMPPHP/XMPP.php';
    $oldPath = get_include_path();
    $this->addIncludePath(dirname(__FILE__).'/external/xmpphp/XMPPHP');
    if (is_file($fileName) && is_readable($fileName) && include_once($fileName)) {
      if (trim($className) != '' && class_exists($className)) {
        set_include_path($oldPath);
        return TRUE;
      } else {
        $this->addMsg(
          MSG_ERROR,
          sprintf(
            $this->_gt('Cannot initialize module class "%s"'), $className
          ),
          TRUE
        );
      }
    } else {
      $this->addMsg(
        MSG_ERROR,
        sprintf(
          $this->_gt('Cannot initialize module file "%s"'),
          $fileName
        ).' ('.$className.')',
        TRUE
      );
    }
    set_include_path($oldPath);
    return FALSE;
  }

  /**
  * Send text message to given or to default jabber account.
  *
  * @param string $message Text to send.
  * @param string $recipient Jabber account to use instead of the set one.
  * @return boolean Success status.
  */
  function sendText($message, $recipient = '') {
    if ($conn = $this->getJabberConnection()) {
      $recipient = ($recipient === '') ? $this->recipient : $recipient;
      $conn->message($recipient, $message);
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Method that is called from papaya logging.
  *
  * @param string $message Log message as text.
  * @return boolean Success status.
  */
  function outputDebug($message) {
    return $this->sendText($message);
  }
}

?>

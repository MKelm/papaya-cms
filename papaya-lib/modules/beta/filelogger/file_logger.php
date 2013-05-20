<?php
/**
 * Provide functionality for log messages to a local file.
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
 * @package Papaya-Modules
 * @subpackage Beta-FileLogger
 * @version $Id: file_logger.php 32535 2009-10-13 16:54:01Z weinert $
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
* Provides for log messages into a file.
*
* @package Papaya-Modules
* @subpackage Beta-FileLogger
*/
class file_logger extends base_plugin {

  /**
  * active module
  * @var boolean
  */
  var $active = FALSE;

  /**
  * log file name
  * @var string
  */
  var $file;
  /**
  * loggin level
  * @var integer
  */
  var $loglevel = NULL;

  /**
  * Module options
  *
  * @var array $pluginOptionFields
  */
  var $pluginOptionFields = array(
    'active' => array('Active', '', TRUE, 'yesno', 1, 'Activate this logger module?', 0),
    'file' => array('File', 'isNoHTML', TRUE, 'input', 200, '', '/tmp'),
    'loglevel' => array('Log level', 'isNum', TRUE, 'input', 1, '0..4', 0),
  );

  /**
  * Load some configuration data from module options
  */
  function loadConfig() {
    $this->active = base_module_options::readOption($this->guid, 'active') ? TRUE : FALSE;
    $this->file = base_module_options::readOption($this-> guid, 'file');
    $this->loglevel = base_module_options::readOption($this-> guid, 'loglevel');
  }

  /**
  * Output debug interface function
  * @param string $message
  * @return boolean
  */
  function outputDebug($message) {
    return $this->writeLog($message);
  }

  /**
  * append log message to file
  * @param string $message
  * @return boolean
  */
  protected function writeLog($message) {
    if (! defined(LF)) {
      define(LF, "\n");
    }
    $this->loadConfig(); // load here to have $this->guid set.
    if (! $this->active) {
      return FALSE;
    }
    ob_start();
    echo "================== ".date('Y-m-d H:i:s.0 T')." ==================".LF;
    echo $message . LF;
    echo LF;
    $write_buffer = ob_get_contents();
    ob_end_clean();
    $fp = fopen($this->file, "a");
    if ($fp) {
      fwrite($fp, $write_buffer);
      fclose($fp);
    }
    return TRUE;
  }

}
?>

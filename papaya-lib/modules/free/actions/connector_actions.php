<?php
/**
* papaya action dispatcher, base class
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
* @subpackage Free-Actions
* @version $Id: connector_actions.php 33806 2010-03-08 15:59:41Z kersken $
*/

/**
* Basic class plugin
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_plugin.php');

/**
* papaya action dispatcher, base class
*
* <code>
* <?php
*   include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
*   $actionsObj = base_pluginloader::getPluginInstance('79f18e7c40824a0f975363346716ff62', $this);
* ?>
* </code>
*
* @package Papaya-Modules
* @subpackage Free-Actions
*/
class connector_actions extends base_plugin {
  /**
   * base class instance
   * @var object $baseActions
   */
  var $baseActions = NULL;

  /**
  * Internal helper function to create a base_actions instance
  *
  * @access private
  * @author Sascha Kersken <info@papaya-cms.com>
  */
  function _initBaseActions() {
    if (!is_object($this->baseActions)) {
      include_once(dirname(__FILE__).'/base_actions.php');
      $this->baseActions = new base_actions($this->msgs);
    }
  }

  /**
  * Call action method on all suitable observers
  *
  * Returns the number of successfully callable observer methods
  *
  * @param string $group
  * @param string $action
  * @param mixed $params optional, default NULL
  * @return int
  */
  function call($group, $action, $params = NULL) {
    $this->_initBaseActions();
    return $this->baseActions->call($group, $action, $params);
  }
}
?>
<?php
/**
* superclass for administration plugins
*
* All modules must inherit this class
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
* @subpackage Modules
* @version $Id: base_module.php 34606 2010-08-04 15:08:48Z zerebecki $
*/

/**
* plugin superclass
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_plugin.php');

/**
* superclass for administration plugins
*
* @package Papaya
* @subpackage Modules
*/
class base_module extends base_plugin {

  /**
  * Guid
  * @var string $guid
  */
  var $guid;

  /**
   * layout object
   *
   * @var papaya_xsl $layout
   */
  var $layout = NULL;

  /**
   * user object
   *
   * @var base_auth $authUser
   */
  var $authUser;

  /**
  * Get XML
  *
  * @access public
  */
  function getXML() {
    if (is_object($this->layout)) {
      $this->execModule();
    }
  }

  /**
  * Execute module
  *
  * @access public
  */
  function execModule() {
  }

  /**
  * check if user has permissions
  *
  * @param integer $permId permission
  * @param boolean $showMessage optional, default value FALSE
  * @access public
  * @return boolean
  */
  function hasPerm($permId, $showMessage = FALSE) {
    if (is_object($this->authUser)) {
      if ($this->authUser->hasModulePerm($permId, $this->guid)) {
        return TRUE;
      } elseif ($this->authUser->isModulePermActive($permId, $this->guid) &&
                $this->authUser->isAdmin()) {
        return TRUE;
      }
    }
    if ($showMessage) {
      $this->addMsg(
        MSG_ERROR,
        papaya_strings::escapeHTMLChars($this->_gt('You don\'t have the needed permissions.'))
      );
    }
    return FALSE;
  }

  /**
  * get an icon uri like module:moduleguid/iconfile
  *
  * @param $iconPath
  * @access public
  * @return string
  */
  function getIconURI($iconPath) {
    return 'module:'.$this->guid.'/'.$iconPath;
  }
}

?>

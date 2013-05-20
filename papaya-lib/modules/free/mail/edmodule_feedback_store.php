<?php
/**
* Module Feedback
*
* Feedback administration
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
* @subpackage Free-Mail
* @version $Id: edmodule_feedback_store.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic module class
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_module.php');

/**
* Module Feedback
*
* Feedback store administration
*
* @package Papaya-Modules
* @subpackage Free-Mail
*/
class edmodule_feedback_store extends base_module {

  /**
  * Permissions
  * @var array $permissions
  */
  var $permissions = array(
    1 => 'Manage',
  );

  /**
  * Execute module
  *
  * @access public
  */
  function execModule() {
    if ($this->hasPerm(1, TRUE)) {
      $path = dirname(__FILE__);
      include_once($path.'/admin_feedback_store.php');
      $feedback = new admin_feedback_store;
      $feedback->module = &$this;
      $feedback->images = &$this->images;
      $feedback->msgs = &$this->msgs;
      $feedback->layout = &$this->layout;
      $feedback->authUser = &$this->authUser;
      $feedback->initialize();
      $feedback->execute();
      $feedback->getXML();
    }
  }

}
?>
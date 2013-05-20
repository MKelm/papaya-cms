<?php
/**
* Forum management.
*
* @copyright 2002-2011 by papaya Software GmbH - All rights reserved.
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
* @subpackage Free-Forum
* @version $Id: edmodule_forum.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class modules
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_module.php');

/**
* Forum management.
*
* @package Papaya-Modules
* @subpackage Free-Forum
*/
class edmodule_forum extends base_module {

  /**
  * Permissions
  * @var array $permissions
  */
  public $permissions = array(
    1 => 'Manage',
    2 => 'Edit categories',
    3 => 'Edit forums',
    4 => 'Edit entries'
  );

  /**
  * Plugin option fields
  * @var array $pluginOptionFields
  */
  public $pluginOptionFields = array(
    'REJECT_ATTACK_STRINGS' => array(
      'Reject Potential Attack Strings',
      'isSomeText',
      FALSE,
      'input',
      100,
      'List the character combinations to reject, separated by blanks',
      ''
    ),
    'ENTRY_MAX_LENGTH' => array(
      'Maximum Entry Length',
      'isNum',
      TRUE,
      'input',
      5,
      'Enter 0 for unlimited entries',
      0
    ),
    'SURFER_LIMIT' => array(
      'Surfer Limit',
      'isNum',
      TRUE,
      'input',
      5,
      'Enter the shown surfers per page',
      20
    ),
    'THREAD_LIMIT' => array(
      'Thread Limit',
      'isNum',
      TRUE,
      'input',
      5,
      'Enter the shown threads per page',
      20
    ),
    'BOARD_LIMIT' => array(
      'Board Limit',
      'isNum',
      TRUE,
      'input',
      5,
      'Enter the shown boards per page',
      20
    ),
    'THREAD_UPDATE_TIME_MODE' => array(
      'Thread Update Get Time',
      'isNum',
      TRUE,
      'translatedcombo',
      array(
        0 => 'By current time',
        1 => 'By last entries\' time'
      ),
      NULL,
      0
    )
  );

  /**
  * Execute module
  */
  public function execModule() {
    if ($this->hasPerm(1, TRUE)) {
      $path = dirname(__FILE__);
      include_once($path.'/admin_forum.php');
      $forum = new admin_forum;
      $forum->module = &$this;
      $forum->images = &$this->images;
      $forum->msgs = &$this->msgs;
      $forum->layout = &$this->layout;
      $forum->authUser = &$this->authUser;
      $forum->initialize();
      $forum->execute();
      $forum->getXML();
    }
  }
}
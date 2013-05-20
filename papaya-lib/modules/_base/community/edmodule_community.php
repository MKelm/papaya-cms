<?php
/**
* Community administration module
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
* @subpackage _Base-Community
* @version $Id: edmodule_community.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class modification module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_module.php');

/**
* Community administration module
*
* @package Papaya-Modules
* @subpackage _Base-Community
*/
class edmodule_community extends base_module {

  /**
  * Glyph
  * @var string
  */
  var $glyph = 'worlduser.gif';

  /**
  * Permissions
  * @var array
  */
  var $permissions = array(
    1 => 'Manage',
    2 => 'Connect',
    3 => 'Export',
    4 => 'Edit profile data',
    5 => 'General settings',
    6 => 'Advanced surfer administration'
  );

  /**
  * Execute module
  *
  * @access public
  */
  function execModule() {
    if ($this->hasPerm(1, TRUE)) {
      include_once(dirname(__FILE__)."/base_surfers_edit.php");
      $surf = new surfer_admin_edit($this->msgs);
      $surf->module = &$this;
      $surf->images = &$this->images;
      $surf->msgs = &$this->msgs;
      $surf->layout = &$this->layout;
      $surf->authUser = &$this->authUser;
      $surf->execute();
      $surf->get($this->layout);
      $surf->getButtons();
    }
  }
}

?>

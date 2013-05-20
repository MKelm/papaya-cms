<?php
/**
* Edit module planet (feed aggregator)
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
* @subpackage Beta-Planet
* @version $Id: edmodule_planet.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class modules
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_module.php');

/**
* Edit module planet (feed aggregator)
*
* Forum management
*
* @package Papaya-Modules
* @subpackage Beta-Planet
*/
class edmodule_planet extends base_module {
  /**
  * Permissions:
  * Additionally the general permission 'Manage' more permissions corresponding
  * to understood commands can be specified. When a command is given and it is
  * an element of the permission array and the current surfer has got this permission,
  * the operation is executed. nothing is done otherwise. If a command is not
  * represented as a permission only Manage will get evaluated.
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
      include_once($path.'/admin_planet.php');
      $planet = new admin_planet;
      $planet->module = &$this;
      $planet->images = &$this->images;
      $planet->msgs = &$this->msgs;
      $planet->layout = &$this->layout;
      $planet->authUser = &$this->authUser;

      $planet->initialize();
      $planet->execute();
      $planet->getXML();
    }
  }
}

?>
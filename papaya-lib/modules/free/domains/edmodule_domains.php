<?php
/**
* Edit module forum
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
* @subpackage Free-Domains
*/

/**
* Basic class modules
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_module.php');

/**
* Edit module domains
*
* Domain management
*
* @package Papaya-Modules
* @subpackage Free-Domains
*/
class edmodule_domains extends base_module {
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
      include_once($path.'/papaya_domains.php');
      $domains = new papaya_domains;
      $domains->module = &$this;
      $domains->images = &$this->images;
      $domains->msgs = &$this->msgs;
      $domains->layout = &$this->layout;
      $domains->authUser = &$this->authUser;
      $domains->initialize();
      $domains->execute();
      $domains->getXML();
    }
  }
}

?>
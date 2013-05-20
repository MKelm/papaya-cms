<?php
/**
* Wiki modification module
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
* @subpackage Beta-Wiki
* @version $Id: edmodule_wiki.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class modification module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_module.php');

/**
* Wiki modification module
*
* Wiki administration
*
* @package Papaya-Modules
* @subpackage Beta-Wiki
*/
class edmodule_wiki extends base_module {
  /**
  * Permissions
  * @var array $permissions
  */
  var $permissions = array(
    1 => 'Manage'
  );

  /**
  * Execute module
  *
  * @access public
  */
  function execModule() {
    if ($this->hasPerm(1, TRUE)) {
      include_once(dirname(__FILE__)."/admin_wiki.php");
      $wiki = new wiki_admin($this->msgs);
      $wiki->module = &$this;
      $wiki->images = &$this->images;
      $wiki->layout = &$this->layout;
      $wiki->execute();
      $wiki->getXML($this->layout);
      $wiki->getButtons();
    }
  }
}

?>
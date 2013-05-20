<?php
/**
* Module FAQ
*
* FAQ-management
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
* @subpackage Free-FAQ
* @version $Id: edmodule_faq.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_module.php');

/**
* Module FAQ
*
* FAQ-management
*
* @package Papaya-Modules
* @subpackage Free-FAQ
*/
class edmodule_faq extends base_module {

  /**
  * Permissions
  * @var array $permissions
  */
  var $permissions = array(
    1 => 'Manage',
    2 => 'Edit Entries/Comments',
    3 => 'Edit FAQs/Groups'
  );


  /**
  * Function for execute module
  *
  * @access public
  */
  function execModule() {
    if ($this->hasPerm(1, TRUE)) {
      $path = dirname(__FILE__);
      include_once($path.'/admin_faq.php');
      $faq = new admin_faq;
      $faq->module = &$this;
      $faq->images = &$this->images;
      $faq->msgs = &$this->msgs;
      $faq->layout = &$this->layout;
      $faq->authUser = &$this->authUser;

      // parameter etc einlesen
      $faq->initialize();
      // aus init verarbeiten
      $faq->execute();
      // daten ausgeben keine datenbankaktionen
      $faq->getXML();
    }
  }
}

?>
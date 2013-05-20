<?php
/**
* Papaya Ldap Browser
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
* @subpackage Free-Ldap
* @version $Id: edmodule_ldap.php 38404 2013-04-17 14:00:41Z kersken $
*/

/**
* Basic class modification module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_module.php');

/**
* Action dispatcher modification module
*
* Action dispatcher administration
*
* @package Papaya-Modules
* @subpackage Free-Ldap
*/
class edmodule_ldap extends base_module {
  /**
  * Plugin option fields
  * @var array $pluginOptionFields
  */
  var $pluginOptionFields = array(
    'LDAP_HOST' => array(
      'LDAP host', 'isNoHTML', TRUE, 'input', 255
    ),
    'LDAP_PORT' => array(
      'LDAP port', 'isNum', TRUE, 'input', 10, '', '389'
    )
  );

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
      $ldapAdmin = new PapayaModuleLdapAdmin();
      $ldapAdmin->msgs = &$this->msgs;
      $ldapAdmin->module = &$this;
      $ldapAdmin->images = &$this->images;
      $ldapAdmin->layout = &$this->layout;
      $ldapAdmin->execute();
      $ldapAdmin->getXML($this->layout);
    }
  }
}

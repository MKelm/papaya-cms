<?php
/**
* LDAP Connector Class
*
* This File contains the class <var>Connector.php</var>
*
* @copyright by papaya Software GmbH, Cologne, Germany - All rights reserved.
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
* @version $Id: Connector.php 38410 2013-04-17 16:56:45Z kersken $

/**
* LDAP Connector Class
*
* This class provides basic LDAP functionality that can be used in other modules
*
* @package Papaya-Modules
* @subpackage Free-Ldap
*/
class PapayaModuleLdapConnector extends base_connector {
  /**
  * The LDAP Wrapper object to be used
  * @var PapayaModuleLdapWrapper
  */
  private $wrapper = NULL;

  /**
  * Set/initialize/get the LDAP Wrapper object to be used
  *
  * @param PapayaModuleLdapWrapper $wrapper
  * @return PapayaModuleLdapWrapper
  */
  public function wrapper($wrapper = NULL) {
    if ($wrapper !== NULL) {
      $this->wrapper = $wrapper;
    } elseif ($this->wrapper === NULL) {
      $this->wrapper = new PapayaModuleLdapWrapper();
    }
    return $this->wrapper;
  }

  /**
  * Set/init/get LDAP options
  *
  * Optional parameter can be PapayaModuleLdapOptions instance or array with option keys => values
  *
  * @param mixed $options
  * @throws InvalidArgumentException
  * @return PapayaModuleLdapOptions
  */
  public function options($options = NULL) {
    return $this->wrapper()->options($options);
  }

  /**
  * Set/get the LDAP host to be used
  *
  * @param string $host optional, default NULL
  * @return string
  */
  public function host($host = NULL) {
    return $this->wrapper()->host($host);
  }

  /**
  * Set/get the LDAP port
  *
  * @param integer $port optional, default NULL
  * @return integer
  */
  public function port($port = NULL) {
    return $this->wrapper()->port($port);
  }

  /**
  * Bind to the LDAP server
  *
  * @param string $userDn
  * @param string $password
  * @return boolean TRUE on success, FALSE otherwise
  */
  public function bind($userDn, $password) {
    return $this->wrapper()->bind($userDn, $password);
  }

  /**
  * Perform an LDAP search action and retrieve the result
  *
  * @param string $baseDn
  * @param string $filter
  * @param array $attributes optional, default empty array
  * @param boolean $attributesOnly optional, default FALSE
  * @return array
  */
  public function search($baseDn, $filter, $attributes = array(), $attributesOnly = FALSE) {
    return $this->wrapper()->search($baseDn, $filter, $attributes, $attributesOnly);
  }

  /**
  * Add an entity to the LDAP database
  *
  * @param string $dn
  * @param array $data
  * @return boolean TRUE on success, FALSE otherwise
  */
  public function add($dn, $data) {
    return $this->wrapper()->add($dn, $data);
  }

  /**
  * Modify an entity in the LDAP database
  *
  * @param string $dn
  * @param array $data
  * @return boolean TRUE on success, FALSE otherwise
  */
  public function modify($dn, $data) {
    return $this->wrapper()->modify($dn, $data);
  }

  /**
  * Delete an entity from the LDAP database
  *
  * @param string $dn
  * @param array $data
  * @return boolean TRUE on success, FALSE otherwise
  */
  public function delete($dn) {
    return $this->wrapper()->delete($dn);
  }

  /**
  * Get the last error that occurred
  *
  * The format is array(0 => $errorNumber, 1 => $errorMessage)
  *
  * @return array
  */
  public function getLastError() {
    return $this->wrapper()->getLastError();
  }

  /**
  * Encrypt a string as an SSHA LDAP password with random salt
  *
  * @param string $plaintext
  * @return string
  */
  public function hashPassword($plaintext) {
    return $this->wrapper()->hashPassword($plaintext);
  }

  /**
  * Unbind and close the current connection
  *
  */
  public function close() {
    $this->wrapper()->close();
  }
}
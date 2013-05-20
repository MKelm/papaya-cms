<?php
/**
* LDAP Wrapper Class
*
* This File contains the class <var>Wrapper.php</var>
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
* @version $Id: Wrapper.php 38422 2013-04-19 09:09:20Z kersken $

/**
* LDAP Wrapper Class
*
* This class provides an object-oriented wrapper for the raw PHP LDAP functionality.
* Unfortunately, it has to make use of error suppression operators to get rid of warnings
* on unsuccessful ldap_bind() calls and the like.
*
* @package Papaya-Modules
* @subpackage Free-Ldap
*/
class PapayaModuleLdapWrapper {
  /**
  * Host name of LDAP server to connect to
  * @var string
  */
  protected $host = '';

  /**
  * Port of LDAP server to connect to
  * @var integer
  */
  protected $port = 389;

  /**
  * Options to be set on the LDAP server on connect
  * @var PapayaModuleLdapOptions
  */
  protected $options = NULL;

  /**
  * The internal LDAP connection to be used
  * @var resource
  */
  protected $connection = NULL;

  /**
  * The state of an LDAP binding for the current connection
  * @var boolean
  */
  protected $binding = FALSE;

  /**
  * Last LDAP error number
  * @var integer
  */
  protected $lastErrorNumber = 0;

  /**
  * Last LDAP error message
  * @var string
  */
  protected $lastErrorMessage = '';

  /**
  * Constructor
  *
  * Allows to optionally set host and port during instance creation
  *
  * @param string $host optional, default ''
  * @param integer $port optional, default 389
  */
  public function __construct($host = '', $port = 389) {
    if ($host != '') {
      $this->host($host);
    }
    $this->port($port);
  }

  /**
  * Set/init/get LDAP options
  *
  * Optional parameter can be PapayaModuleLdapOptions instance or array with option keys => values
  *
  * @param mixed $options
  * @throws InvalidArgumentException
  * @return PapayaModuleOptions
  */
  public function options($options = NULL) {
    if ($options !== NULL) {
      if ($options instanceof PapayaModuleLdapOptions) {
        $this->options = $options;
      } elseif (is_array($options)) {
        $this->options = new PapayaModuleLdapOptions($options);
      } else {
        throw new InvalidArgumentException('PapayaModuleLdapOptions instance or array expected.');
      }
    } elseif ($this->options === NULL) {
      $this->options = new PapayaModuleLdapOptions();
    }
    return $this->options;
  }

  /**
  * Set/get the LDAP host
  *
  * @param string $host optional, default NULL
  * @return string
  */
  public function host($host = NULL) {
    if ($host !== NULL) {
      $this->host = $host;
    }
    return $this->host;
  }

  /**
  * Set/get the LDAP port
  *
  * @param integer $port optional, default NULL
  * @return integer
  */
  public function port($port = NULL) {
    if ($port !== NULL) {
      $this->port = $port;
    }
    return $this->port;
  }

  /**
  * Bind to the LDAP server
  *
  * @param string $userDn
  * @param string $password
  * @return boolean TRUE on success, FALSE otherwise
  */
  public function bind($userDn, $password) {
    $this->binding = FALSE;
    if ($connection = $this->connect()) {
      $this->binding = @ldap_bind($connection, $userDn, $password);
      if (!$this->binding) {
        $this->lastErrorNumber = ldap_errno($connection);
        $this->lastErrorMessage = ldap_error($connection);
      }
    }
    return $this->binding;
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
    $result = array();
    if ($this->binding) {
      $search = @ldap_search(
        $this->connection,
        $baseDn,
        $filter,
        $attributes,
        $attributesOnly ? 1 : 0
      );
      if ($search) {
        $result = @ldap_get_entries($this->connection, $search);
      } else {
        $this->lastErrorNumber = ldap_errno($this->connection);
        $this->lastErrorMessage = ldap_error($this->connection);
      }
    }
    return $result;
  }

  /**
  * Add an entity to the LDAP database
  *
  * @param string $dn
  * @param array $data
  * @return boolean TRUE on success, FALSE otherwise
  */
  public function add($dn, $data) {
    $result = FALSE;
    if ($this->binding) {
      $result = @ldap_add($this->connection, $dn, $data);
      if (!$result) {
        $this->lastErrorNumber = ldap_errno($this->connection);
        $this->lastErrorMessage = ldap_error($this->connection);
      }
    }
    return $result;
  }

  /**
  * Modify an entity in the LDAP database
  *
  * @param string $dn
  * @param array $data
  * @return boolean TRUE on success, FALSE otherwise
  */
  public function modify($dn, $data) {
    $result = FALSE;
    if ($this->binding) {
      $result = @ldap_modify($this->connection, $dn, $data);
      if (!$result) {
        $this->lastErrorNumber = ldap_errno($this->connection);
        $this->lastErrorMessage = ldap_error($this->connection);
      }
    }
    return $result;
  }

  /**
  * Delete an entity from the LDAP database
  *
  * @param string $dn
  * @param array $data
  * @return boolean TRUE on success, FALSE otherwise
  */
  public function delete($dn) {
    $result = FALSE;
    if ($this->binding) {
      $result = @ldap_delete($this->connection, $dn);
      if (!$result) {
        $this->lastErrorNumber = ldap_errno($this->connection);
        $this->lastErrorMessage = ldap_error($this->connection);
      }
    }
    return $result;
  }

  /**
  * Get the last error that occurred
  *
  * The format is array(0 => $errorNumber, 1 => $errorMessage)
  *
  * @return array
  */
  public function getLastError() {
    return array($this->lastErrorNumber, $this->lastErrorMessage);
  }

  /**
  * Encrypt a string as an SSHA LDAP password with random salt
  *
  * @param string $plaintext
  * @return string
  */
  public function hashPassword($plaintext) {
    $salt = substr(
      str_shuffle(
        str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 4)
      ),
      0,
      4
    );
    return '{SSHA}'.base64_encode(sha1($plaintext.$salt, TRUE).$salt);
  }

  /**
  * Unbind and close the current connection
  *
  */
  public function close() {
    if ($this->binding) {
      @ldap_unbind($this->connection);
      $this->binding = FALSE;
    }
    if ($this->connection !== NULL) {
      @ldap_close($this->connection);
    }
    $this->connection = NULL;
  }

  /**
  * Initiate an LDAP connection
  *
  * Only connect if a connection has not been established yet
  *
  * @return mixed , LDAP connection resourse on success, NULL on failure
  */
  protected function connect() {
    if ($this->connection === NULL) {
      $this->binding = FALSE;
      $options = $this->options();
      if (defined('LDAP_OPT_DEBUG_LEVEL') && isset($this->options[LDAP_OPT_DEBUG_LEVEL])) {
        @ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, $this->options[LDAP_OPT_DEBUG_LEVEL]);
      }
      if ($conn = @ldap_connect($this->host, $this->port)) {
        $this->connection = $conn;
        foreach ($this->options as $key => $value) {
          if (!defined('LDAP_OPT_DEBUG_LEVEL') || $key != LDAP_OPT_DEBUG_LEVEL) {
            @ldap_set_option($this->connection, $key, $value);
          }
        }
      }
    }
    return $this->connection;
  }
}
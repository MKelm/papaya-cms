<?php
/**
* Database source name (DSN) specifies a data structure that contains the information
* about a specific data source
*
* @copyright 2009 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Database
* @version $Id: Name.php 35740 2011-05-11 07:29:49Z weinert $
*/

/**
* Database source name (DSN) specifies a data structure that contains the information
* about a specific data source
*
* @package Papaya-Library
* @subpackage Database
*
* @property string $api
* @property string $platform
* @property string $filename
* @property string $username
* @property string $password
* @property string $host
* @property string $port
* @property string $socket
* @property string $database
* @property PapayaRequestParameters $parameters
*/
class PapayaDatabaseSourceName {

  /**
  * Raw dsn string
  * @var string
  */
  private $_name = '';

  /**
  * Parsed dsn informations
  * @var array
  */
  private $_properties = array();

  /**
  * Additional parameters
  *
  * @var PapayaRequestParameters
  */
  private $_parameters = NULL;

  /**
  * Construct a dsn object and set it's properties from a dsn string
  *
  * @throws InvalidArgumentException
  * @param string $name
  */
  public function __construct($name) {
    $this->setName($name);
  }

  /**
  * Initialize object from a dsn string
  *
  * Parses the dsn string and build an array containing all parts. All version can have a
  * query string providing additional parameters
  *
  * Syntax 1:
  *   api(platform):user:pass@host:port/database
  *
  * The platform argument is optional, if it is not set, the api is used for this option, too.
  *
  * The authentication part is optional. If an authentication is given the password is optional,
  * but the username is not.
  *
  * The port is an optional suffix to the host.
  *
  * Syntax 2:
  *   api(platform):user:pass@unix(/path/to/socket)/database
  *
  * Nearly the same as syntax 1 but with a socket not a host.
  *
  * Syntax 3:
  *   api(platform):/path/file.sqlite
  *
  * The platform argument is optional, if it is not set, the api is used for this option, too.
  *
  * The filename needs to be an absolute path and file.
  *
  * @throws InvalidArgumentException
  * @param string $name
  */
  public function setName($name) {
    if (empty($name)) {
      throw new PapayaDatabaseExceptionConnect(
        sprintf('Can not initialize database connection from empty dsn.', $name)
      );
    }
    $patternServer = '(^
      (?P<api>[A-Za-z\d]+) # api name
      (?:\\((?P<platform>[A-Za-z\d]+)\\))? # platform name, optional - default is the api name
      :(?://)? # separator : or ://
      (?: # authentication, optional
        (?P<user>[^:@]+) # username, any char except : and @
        (?::(?P<pass>[^@]+))? # password, any char except @, optional
        @
      )?
      (?: # host or socket
        (?:
          (?P<host>[a-zA-Z\d._-]+) # host name or ip
          (?::(?P<port>\d+))? # port number, optional
        )|
        (?:
          unix\\((?P<socket>(?:[/\\\\][^?<>/\\\\:*|]+)+)\\) # unix file socket
        )
      )
      / # separator /
      (?P<database>[^/\\s]+) # database name
    $)xD';
    $patternFile = '(^
      (?P<api>[A-Za-z\d]+) # api name
      (?:\\((?P<platform>[A-Za-z\d]+)\\))? # platform name, optional - default is the api name
      :(?://)?  # separator : or ://
      (?P<file>
        (?:[a-zA-Z]:(?:[/\\\\][^?<>/\\\\:*|]+)+)| # local windows file name
        (?:[/\\\\][^?<>/\\\\:*|]+)+ # unix file name
      )
    $)xD';
    $queryStringStart = strpos($name, '?');
    if ($queryStringStart > 0) {
      $dsn = substr($name, 0, $queryStringStart - 1);
    } else {
      $dsn = $name;
    }
    if (preg_match($patternServer, $dsn, $matches) ||
        preg_match($patternFile, $dsn, $matches)) {
      $this->_name = $name;
      $this->_properties = array(
        'api' => $matches['api'],
        'platform' => $this->_getMatchValue($matches, 'platform', $matches['api']),
        'filename' => $this->_getMatchValue($matches, 'file'),
        'username' => $this->_getMatchValue($matches, 'user'),
        'password' => $this->_getMatchValue($matches, 'pass'),
        'host' => $this->_getMatchValue($matches, 'host'),
        'port' => (int)$this->_getMatchValue($matches, 'port'),
        'socket' => $this->_getMatchValue($matches, 'socket'),
        'database' => $this->_getMatchValue($matches, 'database')
      );
      if ($queryStringStart > 0) {
        $query = new PapayaRequestParametersQuery();
        $this->_parameters = $query->setString(substr($name, $queryStringStart + 1))->values();
      } else {
        $this->_parameters = new PapayaRequestParameters();
      }
    } else {
      throw new PapayaDatabaseExceptionConnect(
        sprintf('Can not initialize database connection from invalid dsn.', $name)
      );
    }
  }

  /**
  * Check if $name exists in $matches, return $default if not.
  *
  * @param array $matches
  * @param string $name
  * @param mixed $default
  */
  private function _getMatchValue($matches, $name, $default = NULL) {
    if (empty($matches[$name])) {
      return $default;
    } else {
      return $matches[$name];
    }
  }

  /**
  * Check if a dsn property does exists (contains a value in this case)
  *
  * @param name
  * @return boolean
  */
  public function __isset($name) {
    if (empty($this->_properties[$name])) {
      return FALSE;
    } else {
      return TRUE;
    }
  }

  /**
  * Get dynamic properties
  *
  * Provides read access to the values in the _properties array and the parameters property.
  *
  * @throws ErrorException
  * @param string $name
  * @return mixed
  */
  public function __get($name) {
    if (array_key_exists($name, $this->_properties)) {
      return $this->_properties[$name];
    } elseif ($name == 'parameters') {
      return $this->_parameters;
    }
    throw new ErrorException(
      sprintf('Undefined property: %s::$%s', __CLASS__, $name),
      0,
      0,
      __FILE__,
      __LINE__
    );
  }

  /**
  * Proptect properties agains changes from the outside
  * @param unknown_type $name
  * @param unknown_type $value
  */
  public function __set($name, $value) {
    throw new BadMethodCallException(
      sprintf('Property %s::$%s is not writeable.', __CLASS__, $name)
    );
  }
}
<?php
/**
* Request parameters are used to create cache condition data.
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @subpackage Plugins
* @version $Id: Parameters.php 38361 2013-04-04 12:09:41Z hapke $
*/

/*
* Request parameters are used to create cache condition data.
*
* @package Papaya-Library
* @subpackage Plugins
*/
class PapayaCacheIdentifierDefinitionSessionParameters
  extends PapayaObject
  implements PapayaCacheIdentifierDefinition {

  private $_identifiers = array();

  /**
   * Provide the request parameter names, a parameter group and a method.
   *
   * @param array|string $names
   * @param string|NULL $group
   * @param integer $_method
   */
  public function __construct($identifier) {
    $this->_identifiers = func_get_args();
  }

  /**
   * Read request parameters into an condition data array. Prefix it with the class name and
   * return it.
   *
   * If a paramter does not exist in the request, it will not be added to the condition data,
   * if none of the specified parameters exists the result will be TRUE.
   *
   * @return TRUE|array
   */
  public function getStatus() {
    $data = array();
    if ($this->papaya()->session && $this->papaya()->session->isActive()) {
      $values = $this->papaya()->session->values();
      foreach ($this->_identifiers as $identifier) {
        $key = $values->getKey($identifier);
        if (NULL !== ($value = $values[$key])) {
          $data[$key] = $value;
        }
      }
    }
    return empty($data) ? TRUE : array(get_class($this) => $data);
  }

  /**
   * Any kind of data from the session
   *
   * @see PapayaCacheIdentifierDefinition::getSources()
   * @return integer
   */
  public function getSources() {
    return self::SOURCE_SESSION;
  }
}

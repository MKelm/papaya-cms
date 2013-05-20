<?php
/**
* Map values of an array into another array.
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
* @subpackage Util
* @version $Id: Mapper.php 36654 2012-01-18 16:28:13Z weinert $
*/

/**
* Map values of an array into another array.
*
* @package Papaya-Library
* @subpackage Util
*/
class PapayaUtilArrayMapper {

  /**
  * Target array uses teh same keys, the values are array elements, the subelement specified
  * by $indexName is used in the result.
  *
  * @param array $array
  * @param string $indexName
  * @return array(scalar=>mixed)
  */
  public static function byIndex($array, $indexName) {
    if (!is_array($array)) {
      PapayaUtilConstraints::assertInstanceOf('Traversable', $array);
    }
    $result = array();
    foreach ($array as $key => $value) {
      if (array_key_exists($indexName, $value)) {
        $result[$key] = $value[$indexName];
      }
    }
    return $result;
  }
}
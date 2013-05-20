<?php
/**
* A filter factory to create filter objects for from data structures using profiles
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
* @subpackage Filter
* @version $Id: Factory.php 37369 2012-08-06 14:09:26Z weinert $
*/

/**
* A filter factory to create filter objects for from data structures using profiles
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterFactory {

  /**
   * Get the filter factory profile by name
   * @param string $name
   */
  public function getProfile($name) {
    $class = __CLASS__.'Profile'.PapayaUtilStringIdentifier::toCamelCase($name, TRUE);
    if (class_exists($class)) {
      return new $class();
    }
    throw new PapayaFilterFactoryExceptionInvalidProfile($class);
  }

  /**
   * Get the filter using the specified profile.
   *
   * If mandatory is set to false, the actual filter will be prefixed with an PapayaFilterEmpty
   * allowing empty values.
   *
   * @param PapayaFilterFactory|string $profile
   * @param boolean $mandatory
   * @param mixed $options
   * @return PapayaFilter
   */
  public function getFilter($profile, $mandatory = TRUE, $options = NULL) {
    if (!$profile instanceOf PapayaFilterFactoryProfile) {
      $profile = $this->getProfile($profile);
    }
    $profile->options($options);
    $filter = $profile->getFilter();
    if ($mandatory) {
      return $filter;
    } else {
      return new PapayaFilterLogicalOr(
        new PapayaFilterEmpty(),
        $filter
      );
    }
  }
}

<?php
/**
* Country connector class
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
* @subpackage _Base-Countries
* @version $Id: connector_countries.php 38293 2013-03-20 10:40:50Z kersken $
*/

/**
* Basic class country administration
*/
require_once(dirname(__FILE__).'/base_countries.php');

/**
* Basic class plugin
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_plugin.php');

/**
* Country connector class
*
* Usage:
* include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
* $countriesObj = base_pluginloader::getPluginInstance('99db2c2898403880e1ddeeebf7ee726c', $this);
*
* Function from base_countries
*
* $array = $countriesObj->callbackContinent()
*
* @package Papaya-Modules
* @subpackage _Base-Countries
*/
class connector_countries extends base_plugin {
  /**
  * Instance of the base module
  * @var country_admin
  */
  var $countryAdmin = NULL;

  /**
   * Constructor (overrides base_plugin's)
   */
  function __construct(&$aOwner, $paramName = NULL) {
    parent::__construct($aOwner, $paramName);
    $countryAdmin = $this->countryAdmin();
    if (isset($aOwner) && is_object($aOwner)) {
      $countryAdmin->msgs = $aOwner->msgs;
    }
  }

  /**
  * Get/initialize an instance of the base module
  *
  */
  function countryAdmin() {
    if ($this->countryAdmin === NULL) {
      $this->countryAdmin = new country_admin();
    }
    return $this->countryAdmin;
  }

  /**
   * Pipe methods to surfer_admin. details can be found there
   * @see country_admin::callbackContinent()
   */
  function callbackContinent($name, $field, $data, $paramName = NULL) {
    $countryAdmin = $this->countryAdmin();
    return $countryAdmin->callbackContinent($name, $field, $data, $paramName);
  }

  /**
   * Pipe methods to surfer_admin. details can be found there
   * @see country_admin::callbackContinent()
   */
  function callbackCountries($name, $field, $data, $lngId, $paramName = NULL,
                             $continentId = NULL, $countryCaption = NULL) {
    $countryAdmin = $this->countryAdmin();
    return $countryAdmin->callbackCountries(
      $name, $field, $data, $lngId, $paramName, $continentId, $countryCaption
    );
  }

  /**
  * Get a country's complete data by country id
  *
  * @param string $countryId
  * @return array
  */
  function getCountryById($countryId) {
    $countryAdmin = $this->countryAdmin();
    return $countryAdmin->getCountryById($countryId);
  }

  /**
  * get the country names by id
  * @param array|string $countryId one country id or an array of country ids
  * @return array|string
  */
  function getCountryNameById($countryId) {
    $countryAdmin = $this->countryAdmin();
    return $countryAdmin->getCountryNameById($countryId);
  }
  /**
  * Get country id by (English) name
  *
  * @param string $countryName
  * @return string
  */
  function getCountryIdByName($countryName) {
    $countryAdmin = $this->countryAdmin();
    return $countryAdmin->getCountryIdByName($countryName);
  }

  /**
  * Country exists?
  *
  * @param int $countryId
  * @return boolean
  */
  function countryExists($countryId) {
    $countryAdmin = $this->countryAdmin();
    return $countryAdmin->countryExists($countryId);
  }

  /**
  * Get a state's full data by state and country ids
  *
  * @param string $stateId
  * @param string $countryId
  * @return array
  */
  public function getStateById($stateId, $countryId) {
    $countryAdmin = $this->countryAdmin();
    return $countryAdmin->getStateById($stateId, $countryId);
  }

  /**
  * get the state names by id
  * @param array|string $stateId one state id or an array of state ids
  * @param string $countryId optional
  * @return array|string
  */
  function getStateNameById($stateId, $countryId = NULL) {
    $countryAdmin = $this->countryAdmin();
    return $countryAdmin->getStateNameById($stateId, $countryId);
  }

  /**
  * Country exists?
  *
  * @param string $stateId
  * @return boolean
  */
  function stateExists($stateId, $countryId = NULL) {
    $countryAdmin = $this->countryAdmin();
    return $countryAdmin->stateExists($stateId, $countryId);
  }

  /**
   * Returns country list in XHTML as &lt;option&gt; tags
   * Callers have to surround it by a &lt;select&gt; tag
   *
   * @param string $value is the selected value
   * @param int $continent filters the output list of countries by continent
   */
  function getCountryOptionsXHTML($value = '', $continent = NULL) {
    $countryAdmin = $this->countryAdmin();
    return $countryAdmin->getCountryListXHTML($value, $continent);
  }

  /**
  * Returns state list in XHTML as &lt;option&gt; tags
  * Callers have to surround it by a &lt;select&gt; tag
  *
  * @param string $country -- the country whose states you want to read
  * @param string $value --   the selected value (optional)
  */
  function getStateOptionsXHTML($country, $value = '') {
    $countryAdmin = $this->countryAdmin();
    return $countryAdmin->getStateListXHTML($country, $value);
  }

  /**
  * Returns state list as an XML tree
  * (useful for Ajax)
  *
  * Format:
  * &lt;states&gt;
  *   &lt;state id="n" [selected="selected"]&gt;name&lt;/state&gt;
  *   ...
  * &lt;/states&gt;
  *
  * @param string $country -- the country whose states you want to read
  * @param string $value   -- the selected value (optional)
  */
  function getStateListXML($country, $value = '') {
    $countryAdmin = $this->countryAdmin();
    return $countryAdmin->getStateListXML($country, $value);
  }

  /**
  * Get a city's full data by city, state, and country ids
  *
  * @param string $cityId
  * @param string $stateId
  * @param string $countryId
  * @return array
  */
  function getCityById($cityId, $stateId, $countryId) {
    $countryAdmin = $this->countryAdmin();
    return $countryAdmin->getCityById($cityId, $stateId, $countryId);
  }

  /**
  * Get a plain list of city names, with ids as keys
  *
  * @param string $countryId
  * @param string $stateId optional, default NULL
  * @return array
  */
  function getCities($countryId, $stateId = NULL) {
    $countryAdmin = $this->countryAdmin();
    return $countryAdmin->getCities($countryId, $stateId);
  }

  /**
  * Returns city list in XHTML as &lt;option&gt; tags
  * Callers have to surround it by a &lt;select&gt; tag
  *
  * @param string $state --   the state whose cities you want to read
  * @param string $country -- the country in which that state is located
  * @param string $value --   the selected value (optional)
  */
  function getCityOptionsXHTML($state, $country, $value = '') {
    $countryAdmin = $this->countryAdmin();
    return $countryAdmin->getCityListXML($state, $country, $value);
  }

  /**
  * Returns state list as an XML tree
  * (useful for Ajax)
  *
  * Format:
  * &lt;cities&gt;
  *   &lt;city id="n" [selected="selected"]&gt;name&lt;/city&gt;
  *   ...
  * &lt;/cities&gt;
  *
  * @param string $country -- the country whose states you want to read
  * @param string $value   -- the selected value (optional)
  */
  function getCityListXML($country, $value = '') {
    $countryAdmin = $this->countryAdmin();
    return $countryAdmin->getStateListXML($country, $value);
  }

  /**
   * return all countries
   *
   * @param integer $lngId
   * @param mixed array|string $countryIds optional, default NULL
   * @param integer $favoritePolicy [0 (default) = ignore, 1 = use, 2 = get favorites only]
   * @return array
   */
  function getCountries($lngId, $countryIds = NULL, $favoritePolicy = 0) {
    $countryAdmin = $this->countryAdmin();
    return $countryAdmin->getCountries($lngId, $countryIds, $favoritePolicy);
  }

  /**
  * Get favorite countries only
  *
  * @param integer $lngId
  * @return array
  */
  function getFavoriteCountries($lngId) {
    $countryAdmin = $this->countryAdmin();
    return $countryAdmin->getCountries($lngId);
  }

  /**
   * return all states
   *
   * @param integer $countryId
   * @return array
   */
  function getStates($countryId) {
    $countryAdmin = $this->countryAdmin();
    return $countryAdmin->getStates($countryId);
  }

  /**
  * Search country id by language dependent name
  *
  * @param  string $countryName
  * @param  int    $lngId         language id (optional)
  * @return string
  */
  function searchCountryIdByName($countryName, $lngId = NULL) {
    $countryAdmin = $this->countryAdmin();
    return $countryAdmin->searchCountryIdByName($countryName, $lngId);
  }
}
?>
<?php

/**
* Retrieve state/city lists as XML
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
*/

/**
* Basic class country administration
*/
require_once(dirname(__FILE__).'/base_countries.php');

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
 * Country management
 *
* @package Papaya-Modules
* @subpackage _Base-Countries
 */
class content_statelist extends base_content {

  /**
  * Generate the module's main XML output
  *
  * @return string XML
  */
  function getParsedData() {
    // Evaluate parameters
    if (isset($_GET['country']) && $_GET['country'] != '') {
      $countryId = $_GET['country'];
    } else {
      // Return an error message if there is no country id
      return '<error>No country id provided</error>';
    }
    $cmd = 'list_states';
    if (isset($_GET['cmd']) && $_GET['cmd'] == 'list_cities') {
      $cmd = 'list_cities';
      if (isset($_GET['state']) && $_GET['state'] != '') {
        $stateId = $_GET['state'];
      } else {
        return '<error>No state id provided</error>';
      }
    }
    // Get a country_admin instance
    $countryAdmin = new country_admin();
    // Check whether the country exists
    if (!$countryAdmin->countryExists($countryId)) {
      // Return another error message if this country doesn't exist
      return '<error>Invalid country id</error>';
    }
    if ($cmd == 'list_cities' && !$countryAdmin->stateExists($stateId)) {
      return '<error>Invalid state id</error>';
    }
    // After these checks, get the state or city list as requested
    $value = '';
    if (isset($_GET['value']) && trim($_GET['value'] != '')) {
      $value = $_GET['value'];
    }
    if ($cmd == 'list_cities') {
      $list = $countryAdmin->getCityListXML($stateId, $countryId, $value);
    } else {
      $list = $countryAdmin->getStateListXML($countryId, $value);
    }
    if (trim($list) == '') {
      return '<empty/>';
    }
    return $list;
  }
}
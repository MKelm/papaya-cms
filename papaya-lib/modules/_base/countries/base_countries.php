<?php
/**
* Country management
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
* @version $Id: base_countries.php 38429 2013-04-19 14:57:22Z bphilipp $
*/

/**
* Basic class database access
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');

/**
* Basic class check conditions
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_checkit.php');

/**
 * Country management
 *
* @package Papaya-Modules
* @subpackage _Base-Countries
 */
class country_admin extends base_db {

  /**
  * Papaya database table continents
  * @var string $tableContinents
  */
  var $tableContinents = '';

  /**
  * Papaya database table countries
  * @var string $tableCountries
  */
  var $tableCountries = '';

  /**
  * Papaya database table country names
  * @var string $tableCountryNames
  */
  var $tableCountryNames = '';

  /**
  * Papaya database table states
  * @var string $tableStates
  */
  var $tableStates = '';

  /**
  * Papaya database table cities
  * @var string $tableCities
  */
  var $tableCities = '';

  /**
  * Papaya database table languages
  * @var string $tableLng
  */
  var $tableLng = PAPAYA_DB_TBL_LNG;

  /**
  * Caching for country names by ids
  * @var array $countryNamesByIds
  */
  var $countryNamesByIds = array();

  /**
  * Caching for state names by ids
  * @var array $stateNamesByIds
  */
  var $stateNamesByIds = array();

  /**
  * Caching for new country ids by old country ids
  * @var array $newCountryIdCache
  */
  var $newCountryIdCache = array();

  /**
  * Dialog to edit countries
  * @var base_dialog
  */
  var $countryDialog = NULL;

  /**
  * Dialog to edit states
  * @var base_dialog
  */
  var $stateDialog = NULL;

    /**
  * Dialog to edit cities
  * @var base_dialog
  */
  var $cityDialog = NULL;

  /**
  * The module's own GUID (for module options)
  * @var string $moduleGuid
  */
  var $moduleGuid = 'bf6e40b71d3cfb0e80682c64b11d33af';

  /**
  * Constructor
  */
  function __construct($paramName = "cnt") {
    parent::__construct($paramName);
    // Set non-basic table names
    $this->tableContinents = PAPAYA_DB_TABLEPREFIX.'_continents';
    $this->tableCountries = PAPAYA_DB_TABLEPREFIX.'_countries';
    $this->tableCountryNames = PAPAYA_DB_TABLEPREFIX.'_countrynames';
    $this->tableStates = PAPAYA_DB_TABLEPREFIX.'_states';
    $this->tableCities = PAPAYA_DB_TABLEPREFIX.'_cities';
    $this->paramName = $paramName;
  }

  /**
  * Sets up the instantiated object
  */
  function initialize() {
    $this->initializeParams();

    // Check whether there is any data yet
    $this->checkData();
  }

  /**
  * Basic function for handling parameters
  *
  * Decides which actions to perform depending on the GET/POST paramaters
  * from the paramName array, stored in the params attribute
  *
  * @access public
  */
  function execute() {
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'del_country' :
        // Delete a country
        if (isset($this->params['country_id']) && $this->params['country_id'] != '' &&
            isset($this->params['confirm_delete'])) {
          // Clean up: Delete related cities, states, and localized names first
          $this->databaseDeleteRecord(
            $this->tableCities,
            'city_country',
            $this->params['country_id']
          );
          $this->databaseDeleteRecord(
            $this->tableStates,
            'state_country',
            $this->params['country_id']
          );
          $this->databaseDeleteRecord(
            $this->tableCountryNames,
            'countryname_countryid',
            $this->params['country_id']
          );
          $success = $this->databaseDeleteRecord(
            $this->tableCountries,
            'country_id',
            $this->params['country_id']
          );
          if (FALSE !== $success) {
            $this->addMsg(MSG_INFO, $this->_gt('Country deleted.'));
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Error, could not delete country.'));
          }
        }
        break;
      case 'save_country' :
        $this->saveCountry();
        break;
      case 'del_state' :
        // Delete a state
        if (isset($this->params['state_id']) && $this->params['state_id'] != '' &&
            isset($this->params['country_id']) && isset($this->params['confirm_delete'])) {
          $this->databaseDeleteRecord(
            $this->tableCities,
            array(
              'city_state' => $this->params['state_id'],
              'city_country' => $this->params['country_id']
            )
          );
          $success = $this->databaseDeleteRecord(
            $this->tableStates,
            array(
              'state_id' => $this->params['state_id'],
              'state_country' => $this->params['country_id']
            )
          );
          if (FALSE !== $success) {
            $this->addMsg(MSG_INFO, $this->_gt('State deleted.'));
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Error, could not delete state.'));
          }
        }
        break;
      case 'save_state' :
        $this->saveState();
        break;
      case 'del_city' :
        // Delete a city
        if (isset($this->params['city_id']) && isset($this->params['state_id']) &&
            isset($this->params['country_id']) && isset($this->params['confirm_delete'])) {
          $success = $this->databaseDeleteRecord(
            $this->tableCities,
            array(
              'city_id' => $this->params['city_id'],
              'city_state' => $this->params['state_id'],
              'city_country' => $this->params['country_id']
            )
          );
          if (FALSE !== $success) {
            $this->addMsg(MSG_INFO, $this->_gt('City deleted.'));
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Error, could not delete city.'));
          }
        }
        break;
      case 'save_city' :
        $this->saveCity();
        break;
      case 'add_fav' :
        if (!(
            isset(
              $this->params['country_id']) &&
              $this->countryExists($this->params['country_id']))) {
          $this->addMsg(MSG_ERROR, 'Cannot add a non-existing country to favorites.');
        } else {
          $data = array('country_favorite' => 1);
          $this->databaseUpdateRecord(
            $this->tableCountries, $data, 'country_id', $this->params['country_id']
          );
          $this->addMsg(
            MSG_INFO,
            $this->_gtf(
              'Added country "%s" to favorites.',
              $this->getCountryNameById($this->params['country_id'])
            )
          );
        }
        break;
      case 'del_fav' :
        if (!(
            isset(
              $this->params['country_id']) &&
              $this->countryExists($this->params['country_id']))) {
          $this->addMsg(MSG_ERROR, 'Cannot remove a non-existing country from favorites.');
        } else {
          $data = array('country_favorite' => 0);
          $this->databaseUpdateRecord(
            $this->tableCountries, $data, 'country_id', $this->params['country_id']
          );
          $this->addMsg(
            MSG_INFO,
            $this->_gtf(
              'Removed country "%s" from favorites.',
              $this->getCountryNameById($this->params['country_id'])
            )
          );
        }
        break;
      case 'reset' :
        if ($this->module->hasPerm(2, FALSE)) {
          if (isset($this->params['confirm_reset']) && $this->params['confirm_reset'] > 0) {
            $this->checkData(TRUE);
          }
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('You don\'t have the permission to reset the list.'));
        }
        break;
      }
    }
  }

  /**
  * Get page layout
  *
  * Creates the page layout according to parameters
  *
  * @param object xsl_layout &$layout
  * @access public
  */
  function get(&$layout) {
    $this->layout->setParam('COLUMNWIDTH_LEFT', '300px');
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'add_country' :
        $layout->add($this->getEditCountryForm(TRUE));
        break;
      case 'edit_country' :
        $layout->add($this->getEditCountryForm(FALSE));
        break;
      case 'del_country' :
        $this->getDelCountryForm($layout);
        break;
      case 'add_state' :
        $layout->add($this->getEditStateForm(TRUE));
        break;
      case 'del_state' :
        $this->getDelStateForm($layout);
        break;
      case 'edit_state' :
        $layout->add($this->getEditStateForm(FALSE));
        break;
      case 'add_city' :
        $layout->add($this->getEditCityForm(TRUE));
        break;
      case 'del_city' :
        $this->getDelCityForm($layout);
        break;
      case 'edit_city' :
        $layout->add($this->getEditCityForm(FALSE));
        break;
      case 'reset' :
        if ($this->module->hasPerm(2, FALSE)) {
          $this->getResetForm($layout);
        }
        break;
      }
    }
    $layout->addLeft($this->getFavoriteCountryList());
    $layout->addLeft($this->getCountryList());
    $layout->add($this->getStateList());
    $layout->add($this->getCityList());
  }

  /**
  * Save country
  *
  * Inserts a new or changes an existing country
  */
  function saveCountry() {
    $new = FALSE;
    if (isset($this->params['create']) && $this->params['create'] == 1) {
      $new = TRUE;
    }
    $this->initializeCountryForm($new);
    if (!$this->countryDialog->checkDialogInput()) {
      $this->layout->add($this->getEditCountryForm($new));
      return;
    }
    // Check whether a continent has been chosen, or exit with an error if not
    if (isset($this->params['continent']) && $this->params['continent'] > 0) {
      $continent = $this->params['continent'];
    } else {
      $this->addMsg(MSG_WARNING, 'Please choose a continent.');
      $this->layout->add($this->getEditCountryForm($new));
      return;
    }
    $countryId = $this->params['country_id'];
    $comment = '';
    if (isset($this->params['comment'])) {
      $comment = $this->params['comment'];
    }
    // Prepare basic data
    $data = array(
      'country_id' => $countryId,
      'country_continent' => $continent,
      'country_priority' => $this->params['priority'],
      'country_fallbackname' => $this->params['english_name'],
      'country_comment' => $comment
    );
    // Check whether this is a new or a changed country
    if (isset($this->params['create']) && $this->params['create'] == 1) {
      // New country
      // Check whether the chosen id already exists in database and reject it in this case
      $sql = "SELECT COUNT(*)
                FROM %s
               WHERE country_id = '%s'";
      $sqlParams = array($this->tableCountries, $countryId);
      $numRecords = 0;
      if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
        if ($num = $res->fetchField()) {
          $numRecords = $num;
        }
      }
      if ($numRecords > 0) {
        $this->addMsg(MSG_WARNING, $this->_gtf('The country Id "%s" already exists', $countryId));
        $this->layout->add($this->getEditCountryForm($new));
        return;
      }
      // Insert the new country; store its id for localized name relations
      $countryId = $this->databaseInsertRecord(
        $this->tableCountries, NULL, $data
      );
      $msg = 'Country added.';
    } else {
      // Change existing country
      $this->databaseUpdateRecord(
        $this->tableCountries, $data, 'country_id', $countryId
      );
      $msg = $this->_gt('Country updated.');
    }
    // Save the localized country titles, if provided
    foreach ($this->params as $param => $value) {
      // Find any param that starts with 'lang_'
      if (preg_match('/^lang_(.*)/', $param, $matches)) {
        // Get current language
        $lang = $matches[1];
        // Prepare data
        $data = array(
          'countryname_lang' => $lang,
          'countryname_text' => $value,
          'countryname_countryid' => $countryId
        );
        // Check whether this is a new or an existing name
        if (isset ($this->params['create']) && $this->params['create'] == 1) {
          // New: Simply insert the name
          if (trim($value) != '') {
            $this->databaseInsertRecord(
              $this->tableCountryNames, 'countryname_id', $data
            );
          }
        } else {
          // Existing
          // Get the name's id by category id and language
          $sql = "SELECT countryname_id
                    FROM %s
                   WHERE countryname_countryid = '%s'
                     AND countryname_lang = %d";
          $params = array($this->tableCountryNames, $countryId, $lang);
          if ($res = $this->databaseQueryFmt($sql, $params)) {
            // Check whether a country name in current language exists
            if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
              // Existing name
              // Check whether the new value is an empty string
              if (trim($value) != '') {
                // Not empty: update existing record
                $this->databaseUpdateRecord(
                  $this->tableCountryNames,
                  $data,
                  'countryname_id',
                  $row['countryname_id']
                );
              } else {
                // Empty: delete existing record
                $this->databaseDeleteRecord(
                  $this->tableCountryNames,
                  'countryname_id',
                  $row['countryname_id']
                );
              }
            } else {
              // New title: insert record
              if (trim($value) != '') {
                $this->databaseInsertRecord(
                  $this->tableCountryNames,
                  'countryname_id',
                  $data
                );
              }
            }
          }
        }
      }
    }
    $this->addMsg(MSG_INFO, $msg);
  }

  /**
  * Save state
  *
  * Inserts a new or changes an existing state
  */
  function saveState() {
    $new = isset($this->params['create']) && $this->params['create'] == 1 ? TRUE : FALSE;
    $this->initializeStateForm($new);
    if (!$this->stateDialog->checkDialogInput()) {
      $this->layout->add($this->getEditStateForm($new));
      return;
    }
    if (isset($this->params['country_id']) &&
        $this->countryExists($this->params['country_id'])) {
      $country = $this->params['country_id'];
    } else {
      $this->addMsg(MSG_WARNING, $this->_gt('No or invalid country.'));
      $this->layout->add($this->getEditStateForm);
      return;
    }
    // Prepare basic data
    $comment = isset($this->params['comment']) ? $this->params['comment'] : '';
    $data = array(
      'state_id' => $this->params['state_id'],
      'state_country' => $country,
      'state_name' => $this->params['name'],
      'state_comment' => $comment
    );
    // Check whether this is a new or a changed state
    if (isset($this->params['create']) && $this->params['create'] == 1) {
      // If the desired country/state id already exists, exit with an error
      $sql = "SELECT COUNT(*)
                FROM %s
               WHERE state_id = '%s'
                 AND state_country = '%s'";
      $sqlParams = array($this->tableStates, $this->params['state_id'], $country);
      $numRecords = 0;
      if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
        if ($num = $res->fetchField()) {
          $numRecords = $num;
        }
      }
      if ($numRecords > 0) {
        $this->addMsg(MSG_WARNING, 'This state already exists.');
        return;
      }
      // Insert the new state
      $this->databaseInsertRecord(
        $this->tableStates, NULL, $data
      );
      $msg = $this->_gt('State added.');
    } else {
      // Change existing state
      $this->databaseUpdateRecord(
        $this->tableStates,
        $data,
        array(
          'state_id' => $this->params['state_id'],
          'state_country' => $country
        )
      );
      $msg = $this->_gt('State updated.');
    }
    $this->addMsg(MSG_INFO, $msg);
  }

  /**
  * Save city
  *
  * Inserts a new or changes an existing city
  */
  function saveCity() {
    // Check whether name, id, state, and country have been provided,
    // or exit with an error if not
    $new = isset($this->params['create']) && $this->params['create'] == 1 ? TRUE : FALSE;
    $this->initializeCityForm($new);
    if (!$this->cityDialog->checkDialogInput()) {
      $this->layout->add($this->getEditCityForm($new));
      return;
    }
    if (isset($this->params['country_id']) &&
        $this->countryExists($this->params['country_id'])) {
      $country = $this->params['country_id'];
    } else {
      $this->addMsg(MSG_WARNING, $this->_gt('No or invalid country.'));
      $this->layout->add($this->getEditCityForm($new));
      return;
    }
    if (isset($this->params['state_id']) &&
        $this->stateExists($this->params['state_id'], $this->params['country_id'])) {
      $state = $this->params['state_id'];
    } else {
      $this->addMsg(MSG_WARNING, $this->_gt('No or invalid state'));
      $this->layout->add($this->getEditCityForm($new));
      return;
    }
    // Prepare basic data
    $cityId = $this->params['city_id'];
    $comment = isset($this->params['comment']) ? $this->params['comment'] : '';
    $data = array(
      'city_id' => $cityId,
      'city_state' => $state,
      'city_country' => $country,
      'city_name' => $this->params['name'],
      'city_lat_long' => isset($this->params['lat_long']) ? $this->params['lat_long'] : '',
      'city_comment' => $comment
    );
    // Check whether this is a new or a changed city
    if (isset($this->params['create']) && $this->params['create'] == 1) {
      // If the desired country/state/city id already exists, exit with an error
      $sql = "SELECT COUNT(*)
                FROM %s
               WHERE city_id = '%s'
                 AND city_state = '%s'
                 AND state_country = '%s'";
      $sqlParams = array($this->tableCities, $cityId, $state, $country);
      $numRecords = 0;
      if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
        if ($num = $res->fetchField()) {
          $numRecords = $num;
        }
      }
      if ($numRecords > 0) {
        $this->addMsg(MSG_WARNING, 'This city already exists.');
        return;
      }
      // Insert the new city
      $this->databaseInsertRecord(
        $this->tableCities, NULL, $data
      );
      $msg = $this->_gt('City added.');
    } else {
      // Change existing city
      $this->databaseUpdateRecord(
        $this->tableCities,
        $data,
        array(
          'city_id' => $this->params['city_id'],
          'city_state' => $this->params['state_id'],
          'city_country' => $this->params['country_id']
        )
      );
      $msg = $this->_gt('City updated.');
    }
    $this->addMsg(MSG_INFO, $msg);
  }

  /**
  * Get continent by country id
  *
  * @access public
  * @param string $country_id
  * @return int
  */
  function getContinentByCountryId($countryId) {
    $sql = "SELECT country_continent, country_id
              FROM %s
             WHERE country_id = '%s'";
    $sqlParams = array($this->tableCountries, $countryId);
    $continent = 0;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $continent = $row['country_continent'];
      }
    }
    return $continent;
  }

  /**
  * Get a country's complete data by country id
  *
  * @param string $countryId
  * @return array
  */
  function getCountryById($countryId) {
    $result = array();
    $sql = "SELECT c.country_id, c.country_fallbackname, c.country_comment,
                   cn.countryname_lang, cn.countryname_text
              FROM %s c
              LEFT JOIN %s cn
                ON c.country_id = cn.countryname_countryid
             WHERE c.country_id = '%s'";
    $sqlParams = array($this->tableCountries, $this->tableCountryNames, $countryId);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (empty($result)) {
          $result = array(
            'country_id' => $row['country_id'],
            'country_fallbackname' => $row['country_fallbackname'],
            'country_comment' => $row['country_comment'],
            'NAMES' => array()
          );
        }
        if ($row['countryname_lang'] !== NULL) {
          $result['NAMES'][$row['countryname_lang']] = $row['countryname_text'];
        }
      }
    }
    return $result;
  }

  /**
  * Get country name by id
  * as of 28.01.2008, it can handle multiple ids
  *
  * @access public
  * @param array|string $countryId
  * @return string
  */
  function getCountryNameById($countryId) {
    $isMultiples = is_array($countryId);
    $results = $this->getCountryNamesByIds($countryId);

    if (is_array($results)) {
      $keys = array_keys($results);
      if ($isMultiples) {
        return $results;
      } elseif (isset($results[$keys[0]])) {
        return $results[$keys[0]];
      }
    }
    return '';
  }

  /**
  * Get country names by ids
  *
  * @param array|string $countryIds
  * @return array
  */
  function getCountryNamesByIds($countryIds) {
    if (!is_array($countryIds)) {
      $countryIds = array($countryIds);
    }
    $results = array();

    // step 1: get country names from cache
    $toLoad = array();
    foreach ($countryIds as $countryId) {
      if (!empty($countryId) && $countryId != '-') {
        if (isset($this->countryNamesByIds[$countryId])) {
          $results[$countryId] = $this->countryNamesByIds[$countryId];

        } else {
          $toLoad[] = $countryId;
        }
      }
    }

    // step 2: load missing countries from database
    if (count($toLoad) > 0) {

      // condition 2: country id
      $toLoad = array_unique($toLoad);
      $filter = str_replace(
        '%',
        '%%',
        $this->databaseGetSQLCondition('country_id', $toLoad)
      );

      $sql = "SELECT country_fallbackname, country_id
                FROM %s
               WHERE ".$filter;
      $sqlParams = array($this->tableCountries);

      // perform datebase access
      if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $results[$row['country_id']] = $row['country_fallbackname'];
          // set country name in cache
          $this->countryNamesByIds[$row['country_id']] = $row['country_fallbackname'];
        }
      }
    }

    // step 3: return results
    if (count($results) > 0) {
      return $results;
    }
    return NULL;
  }

  /**
  * Get country id by (English) name
  *
  * @access public
  * @param string $countryName
  * @return string
  */
  function getCountryIdByName($countryName) {
    $sql = "SELECT country_id
              FROM %s
             WHERE country_fallbackname = '%s'";
    $sqlParams = array($this->tableCountries, $countryName);

    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      return $res->fetchField();
    }
    return FALSE;
  }

  /**
  * Country exists?
  *
  * @access public
  * @param int $countryId
  * @return boolean
  */
  function countryExists($countryId) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE country_id = '%s'";
    $exists = FALSE;
    $sqlParams = array($this->tableCountries, $countryId);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($num = $res->fetchField()) {
        $exists = $num > 0 ? TRUE : FALSE;
      }
    }
    return $exists;
  }

  /**
  * Get a state's full data by state and country ids
  *
  * @param string $stateId
  * @param string $countryId
  * @return array
  */
  public function getStateById($stateId, $countryId) {
    $result = array();
    $sql = "SELECT state_id, state_country, state_name, state_comment
              FROM %s
             WHERE state_id = '%s'
               AND state_country = '%s'";
    $sqlParams = array($this->tableStates, $stateId, $countryId);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result = $row;
      }
    }
    return $result;
  }

  /**
  * Get state name by id
  * As of 28.01.2008, it can handle multiple ids
  *
  * @access public
  * @param array|string $stateId
  * @param string $countryId
  * @return string
  */
  function getStateNameById($stateId, $countryId = NULL) {
    $isMultiples = is_array($stateId);
    $results = $this->getStateNamesByIds($stateId, $countryId);

    if (is_array($results)) {
      $keys = array_keys($results);
      if ($isMultiples) {
        return $results;
      } elseif (isset($results[$keys[0]])) {
        return $results[$keys[0]];
      }
    }
    return '';
  }

  /**
  * Get state names by ids
  *
  * Important: if you do not provide a country ID, state IDs may not be unique
  *
  * @param array|string $stateId
  * @param string $countryId optional, default NULL
  * @return array
  */
  function getStateNamesByIds($stateIds, $countryId = NULL) {
    if (!is_array($stateIds)) {
      $stateIds = array($stateIds);
    }
    $results = array();

    // step 1: get state names from cache
    $toLoad = array();
    foreach ($stateIds as $stateId) {
      if (!empty($stateId) && $stateId != '-') {
        if (isset($this->stateNamesByIds[$stateId])) {
          $results[$stateId] = $this->stateNamesByIds[$stateId];

        } else {
          $toLoad[] = $stateId;
        }
      }
    }

    // step 2: load missing state names from database
    if (count($toLoad) > 0) {

      // condition 1: state country
      if (!empty($countryId)) {
        $countryCondition = ' AND '.str_replace(
          '%', '%%', $this->databaseGetSQLCondition('state_country', $countryId)
        );
      } else {
        $countryCondition = '';
      }
      // condition 2: state id
      $toLoad = array_unique($toLoad);
      $filter = str_replace(
        '%', '%%', $this->databaseGetSQLCondition('state_id', $toLoad)
      );

      $sql = "SELECT state_name, state_id
                FROM %s
               WHERE ".$filter.$countryCondition;
      $sqlParams = array($this->tableStates);

      // perform datebase access
      if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $results[$row['state_id']] = $row['state_name'];
          // set state name in cache
          $this->stateNamesByIds[$row['state_id']] = $row['state_name'];
        }
      }
    }

    // step 3: return results
    if (count($results) > 0) {
      return $results;
    }
    return NULL;
  }

  /**
  * Get state id by name
  *
  * @param string $stateName
  * @return string
  */
  function getStateIdByName($stateName) {
    $sql = "SELECT state_id
              FROM %s
             WHERE state_name = '%s'";
    $sqlParams = array($this->tableStates, $stateName);

    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      return $res->fetchField();
    }
    return FALSE;
  }

  /**
  * Country exists?
  *
  * @access public
  * @param string $stateId
  * @return boolean
  */
  function stateExists($stateId, $countryId = NULL) {
    if ($countryId === NULL) {
      $countryCondition = '';
    } else {
      $countryCondition =
        sprintf(" AND state_country = '%s'", $countryId);
    }
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE state_id = '%s'".$countryCondition;
    $exists = FALSE;
    $sqlParams = array($this->tableStates, $stateId);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($num = $res->fetchField()) {
        $exists = ($num > 0) ? TRUE : FALSE;
      }
    }
    return $exists;
  }

  /**
  * Get all (or selected) countries
  *
  * @param integer $lngId
  * @param mixed string|array $countryIds optional, default NULL
  * @param integer $favoritePolicy [0 (default) = ignore, 1 = use, 2 = get favorites only]
  * @return array
  */
  function getCountries($lngId, $countryIds = NULL, $favoritePolicy = 0) {
    $countries = array();
    $filter = '';
    if (isset($countryIds) && $countryIds !== NULL) {
      if (!is_array($countryIds)) {
        $countryIds = array($countryIds);
      }
      $filter = " WHERE ".str_replace(
        '%', '%%', $this->databaseGetSQLCondition('country_id', $countryIds)
      );
    }
    if ($favoritePolicy == 2) {
      $filter .= ($filter == '' ? " WHERE" : " AND")." country_favorite = 1";
    }
    // First, get the countries with their fallback names
    $sql = "SELECT country_id,
                   country_fallbackname
              FROM %s ".$filter."
             ORDER BY ";
    if ($favoritePolicy > 0) {
      $sql .= "country_favorite DESC, ";
    }
    $sql .= "country_priority DESC, country_fallbackname ASC";
    $sqlData = array($this->tableCountries);
    if ($res = $this->databaseQueryFmt($sql, $sqlData)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $countries[$row['country_id']] = $row['country_fallbackname'];
      }
    }
    $countryIds = array_keys($countries);
    // Now set all matching localized names
    if (isset($countryIds) && $countryIds !== NULL) {
      $filter = ' AND '.
        str_replace(
          '%',
          '%%',
          $this->databaseGetSQLCondition(
            'countryname_countryid',
            $countryIds
          )
        );
    }
    $sql = "SELECT countryname_text,
                   countryname_countryid,
                   countryname_lang
              FROM %s
             WHERE countryname_lang = '%d' ".$filter;
    $sqlData = array($this->tableCountryNames, $lngId);
    if ($res = $this->databaseQueryFmt($sql, $sqlData)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $countries[$row['countryname_countryid']] = $row['countryname_text'];
      }
    }
    return $countries;
  }

  /**
  * Get favorite countries only
  *
  * @param integer $lngId
  * @return array
  */
  function getFavoriteCountries($lngId) {
    return $this->getCountries($lngId, NULL, 2);
  }

  /**
   * return all states of a country
   *
   * @param integer $countryId
   * @return array
   */
  function getStates($countryId) {
    $states = array();
    $sql = "SELECT state_id, state_name
              FROM %s
             WHERE state_country = '%s'
          ORDER BY state_name ASC";
    $sqlData = array($this->tableStates, $countryId);
    if ($res = $this->databaseQueryFmt($sql, $sqlData)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $states[$row['state_id']] = $row['state_name'];
      }
    }

    return $states;
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
    $result = array();
    $sql = "SELECT city_id, city_name, city_lat_long, city_comment
              FROM %s
             WHERE city_id = '%s'
               AND city_state = '%s'
               AND city_country = '%s'";
    $sqlParams = array($this->tableCities, $cityId, $stateId, $countryId);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result = $row;
      }
    }
    return $result;
  }

  /**
  * Get a plain list of city names, with ids as keys
  *
  * @param string $countryId
  * @param string $stateId optional, default NULL
  * @return array
  */
  function getCities($countryId, $stateId = NULL) {
    $result = array();
    $sql = "SELECT city_id, city_name
              FROM %s
             WHERE city_country = '%s'";
    if ($stateId !== NULL) {
      $sql .= " AND city_state = '%s'";
    }
    $sqlParams = array($this->tableCities, $countryId);
    if ($stateId !== NULL) {
      $sqlParams[] = $stateId;
    }
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['city_id']] = $row['city_name'];
      }
    }
    return $result;
  }

  /**
  * Get country list
  */
  function getCountryList() {
    $result = sprintf(
      '<listview title="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Countries'))
    );
    $result .= '<cols>'.LF;
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Base name'))
    );
    $result .= '<col/>'.LF;
    $result .= '</cols>'.LF;
    $result .= '<items>'.LF;
    $sql = "SELECT continent_id, continent_name
              FROM %s
          ORDER BY continent_name ASC";
    $sqlData = array($this->tableContinents);
    $continents = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlData)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $continents[$row['continent_id']] = $row;
      }
    }
    $sql = "SELECT country_id,
                   country_fallbackname,
                   country_continent,
                   country_favorite
              FROM %s
             ORDER BY country_priority DESC, country_fallbackname ASC";
    $sqlData = array($this->tableCountries);
    $countries = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlData)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $countries[$row['country_id']] = array(
          'name' => $row['country_fallbackname'],
          'favorite' => $row['country_favorite']
        );
        $continents[$row['country_continent']]['COUNTRIES'][] = $row['country_id'];
      }
    }
    $currentContinent = 0;
    if (isset($this->params['continent_id']) &&
        is_numeric($this->params['continent_id']) &&
        $this->params['continent_id'] >= 1 &&
        $this->params['continent_id'] <= 6) {
      $currentContinent = $this->params['continent_id'];
    } elseif (isset($this->params['country_id'])) {
      $currentContinent = $this->getContinentByCountryId($this->params['country_id']);
    }
    foreach ($continents as $continentId => $continent) {
      if ($continentId == $currentContinent) {
        $continentHref = $this->getLink();
        $result .= sprintf(
          '<listitem title="%s" span="2" image="%s" href="%s" />'.LF,
          papaya_strings::escapeHTMLChars($continent['continent_name']),
          papaya_strings::escapeHTMLChars($this->images['status-node-open']),
          papaya_strings::escapeHTMLChars($continentHref)
        );
        if (isset($continent['COUNTRIES']) && is_array($continent['COUNTRIES'])) {
          foreach ($continent['COUNTRIES'] as $countryId) {
            $countryName = $countries[$countryId]['name'];
            $countryFavorite = $countries[$countryId]['favorite'];
            if (isset($this->params['country_id']) && $this->params['country_id'] == $countryId) {
              $selected = ' selected="selected"';
            } else {
              $selected = '';
            }
            $editHref = $this->getLink(
              array('country_id' => $countryId, 'cmd' => 'edit_country'));
            $result .= sprintf(
              '<listitem title="%s" indent="1" image="%s" href="%s"%s>'.LF,
              papaya_strings::escapeHTMLChars($countryName),
              $this->module->getIconURI('countries.png'),
              papaya_strings::escapeHTMLChars($editHref),
              $selected
            );
            if ($countryFavorite) {
              $favHref = $this->getLink(
                array('country_id' => $countryId, 'cmd' => 'del_fav'));
              $favIcon = $this->images['items-favorite'];
              $favCaption = $this->_gt('Remove from favorites');
            } else {
              $favHref = $this->getLink(
                array('country_id' => $countryId, 'cmd' => 'add_fav'));
              $favIcon = $this->images['status-favorite-disabled'];
              $favCaption = $this->_gt('Add to favorites');
            }
            $result .= sprintf(
              '<subitem><a href="%s"><glyph src="%s" alt="%s" hint="%3$s"/></a></subitem>',
              papaya_strings::escapeHTMLChars($favHref),
              papaya_strings::escapeHTMLChars($favIcon),
              papaya_strings::escapeHTMLChars($favCaption)
            );
            $result .= '</listitem>'.LF;
          }
        }
      } else {
        $continentHref = $this->getLink(array('continent_id' => $continentId));
        $result .= sprintf(
          '<listitem title="%s" span="4" image="%s" href="%s" />'.LF,
          papaya_strings::escapeHTMLChars($continent['continent_name']),
          papaya_strings::escapeHTMLChars($this->images['status-node-closed']),
          papaya_strings::escapeHTMLChars($continentHref)
        );
      }
    }
    $result .= '</items>'.LF;
    $result .= '</listview>'.LF;
    return $result;
  }

  /**
  * Get country list
  */
  function getFavoriteCountryList() {
    $result = '';
    // Read favorites from database
    $sql = "SELECT country_id, country_fallbackname,
                   country_favorite
              FROM %s
             WHERE country_favorite = 1
          ORDER BY country_priority DESC, country_fallbackname ASC";
    $sqlParams = array($this->tableCountries);
    $favoriteCountries = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $favoriteCountries[$row['country_id']] = $row['country_fallbackname'];
      }
    }
    if (empty($favoriteCountries)) {
      return $result;
    }
    $result .= sprintf(
      '<listview title="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Favorite countries'))
    );
    $result .= '<cols>'.LF;
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Base name'))
    );
    $result .= '<col/>'.LF;
    $result .= '</cols>'.LF;
    $result .= '<items>'.LF;
    foreach ($favoriteCountries as $countryId => $countryName) {
      if (isset($this->params['country_id']) && $this->params['country_id'] == $countryId) {
        $selected = ' selected="selected"';
      } else {
        $selected = '';
      };
      $editHref = $this->getLink(
        array('country_id' => $countryId, 'cmd' => 'edit_country'));
      $result .= sprintf(
        '<listitem title="%s" indent="1" image="%s" href="%s"%s>'.LF,
        papaya_strings::escapeHTMLChars($countryName),
        papaya_strings::escapeHTMLChars($this->module->getIconURI('countries.png')),
        papaya_strings::escapeHTMLChars($editHref),
        $selected
      );
      $favHref = $this->getLink(
        array('country_id' => $countryId, 'cmd' => 'del_fav')
      );
      $favIcon = $this->images['places-trash'];
      $favCaption = $this->_gt('Remove from favorites');
      $result .= sprintf(
        '<subitem><a href="%s"><glyph src="%s" alt="%s" hint="%3$s"/></a></subitem>',
        papaya_strings::escapeHTMLChars($favHref),
        papaya_strings::escapeHTMLChars($favIcon),
        papaya_strings::escapeHTMLChars($favCaption)
      );
      $result .= '</listitem>'.LF;
    }
    $result .= '</items>'.LF;
    $result .= '</listview>'.LF;
    return $result;
  }

  /**
   * Get option List for a select form field.
   *
   * @access private
   * @see connector_countries::getCountyOptionsXHTML()
   */
  function getCountryListXHTML($value = '', $continent = NULL, $lngId = 0) {
    include_once (PAPAYA_INCLUDE_PATH.'system/base_language_select.php');
    $lngSelect = &base_language_select::getInstance();
    if ($lngId == 0) {
      $lngId = (int)$lngSelect->currentLanguageId;
    }
    // First, get the favorite countries
    $sql = "SELECT country_id, country_fallbackname, country_favorite
              FROM %s ";
    $sql .= ($continent !== NULL && $continent > 0)
      ? ' WHERE country_continent = ' . $continent : '';
    $sql .= (($continent !== NULL && $continent > 0) ? ' AND ' : ' WHERE ').'country_favorite = 1';
    $sql .= ' ORDER BY country_priority DESC, country_fallbackname ASC';
    if ($res = $this->databaseQueryFmt($sql, array($this->tableCountries))) {
      // Save favorite countries in an array to determine their
      // localized names without nested queries
      $favoriteCountries = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $favoriteCountries[$row['country_id']] = $row['country_fallbackname'];
      }
    }
    // Now go for all countries
    $sql = "SELECT country_id, country_fallbackname
              FROM %s ";
    $sql .= ($continent !== NULL && $continent > 0) ? ' WHERE country_continent=' . $continent : '';
    $sql .= ' ORDER BY country_priority DESC, country_fallbackname ASC';
    if ($res = $this->databaseQueryFmt($sql, array($this->tableCountries))) {
      // Save favorite countries in an array to determine their
      // localized names without nested queries
      $allCountries = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $allCountries[$row['country_id']] = $row['country_fallbackname'];
      }
    }
    // Get all localized country names
    $sql = "SELECT countryname_text,
                   countryname_countryid,
                   countryname_lang
              FROM %s
             WHERE countryname_lang=%d";
    $sqlData = array($this->tableCountryNames, $lngId);
    $countryNames = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlData)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $countryNames[$row['countryname_countryid']] = $row['countryname_text'];
      }
    }
    // Assign matching local names to favorites
    foreach ($favoriteCountries as $id => $name) {
      if (isset($countryNames[$id])) {
        $favoriteCountries[$id] = $countryNames[$id];
      }
    }
    asort($favoriteCountries);
    // Assign matching local names to all countries
    foreach ($allCountries as $id => $name) {
      if (isset($countryNames[$id])) {
        $allCountries[$id] = $countryNames[$id];
      }
    }
    asort($allCountries);
    // Create the form options
    $result = '';
    foreach ($favoriteCountries as $id => $name) {
      $selected = ($value == $id) ? ' selected="selected"' : '';
      $result .= sprintf(
        '<option value="%s" %s>%s</option>'.LF,
        papaya_strings::escapeHTMLChars($id),
        $selected,
        papaya_strings::escapeHTMLChars($name)
      );
    }
    $result .= '<option value="-">-----------------------</option>'.LF;
    foreach ($allCountries as $id => $name) {
      $selected = ($value == $id) ? ' selected="selected"' : '';
      $result .= sprintf(
        '<option value="%s" %s>%s</option>'.LF,
        papaya_strings::escapeHTMLChars($id),
        $selected,
        papaya_strings::escapeHTMLChars($name)
      );
    }
    return $result;
  }

  /**
  * Get edit country form
  *
  * Creates the form to edit a country
  * and its multilingual names
  * The first, mandatory paramter is a reference to
  * page layout, while the second, optional one
  * is a boolean that determines whether a new country is created (TRUE)
  * or whether an existing one is edited (FALSE, default)
  *
  * @access public
  * @param boolen $new optional, default FALSE
  * @return string XML
  */
  function getEditCountryForm($new = FALSE) {
    $result = '';
    $this->initializeCountryForm($new);
    if (is_object($this->countryDialog)) {
      $result = $this->countryDialog->getDialogXML();
    }
    return $result;
  }

  /**
  * Initialize the country dialog
  *
  * @param boolean $new
  */
  function initializeCountryForm($new) {
    if (is_object($this->countryDialog)) {
      return;
    }
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    // Display error msg and get out if trying to edit unavailable country
    if (!$new &&
        (!isset($this->params['country_id']) || $this->params['country_id'] == '')) {
      return;
    }
    // Generate form fields
    $fields = array(
      $new ? 'country_id' : 'country_id_display' => array(
        'ID (CC TLD)',
        'isNoHTML',
        TRUE,
        $new ? 'input' : 'disabled_input',
        8,
        ''
      ),
      'english_name' =>
        array('English name', 'isNoHTML', TRUE, 'input', 100, ''),
      'continent' =>
        array('Continent', 'isNoHTML', TRUE, 'function', 'callbackContinent'),
      'priority' =>
        array(
          'Priority',
          'isNum',
          TRUE,
          'input',
          10,
          'Optional; the higher the value, the higher in the list the country will show up',
          0
        ),
      'comment' =>
        array('Comment', 'isSomeText', FALSE, 'richtext', 6),
      'Localized names'
    );
    // Add fields for names in all available frontend languages except English
    $languages = $this->getLanguageSelector(TRUE, 'en');
    foreach ($languages as $id => $title) {
      $fields['lang_'.$id] = array(
        $title, 'isNoHTML', FALSE, 'input', 30, 'Enter name or remove text to delete it'
      );
    }
    $data = array();
    // Hidden field: cmd 'save_country'
    $hidden = array('cmd' => 'save_country');
    // Check whether this is a new country
    if (!$new) {
      // Existing country
      // Set hidden create parameter to 0
      $hidden['create'] = 0;
      $hidden['country_id'] = $this->params['country_id'];
      // Get data for current country from database
      $sql = "SELECT country_continent,
                     country_fallbackname,
                     country_id,
                     country_priority,
                     country_comment
                FROM %s WHERE country_id='%s'";
      $res =
        $this->databaseQueryFmt($sql, array($this->tableCountries, $this->params['country_id']));
      if ($res) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $data['country_id_display'] = $row['country_id'];
          $data['continent'] = $row['country_continent'];
          $data['english_name'] = $row['country_fallbackname'];
          $data['priority'] = $row['country_priority'];
          $data['comment'] = $row['country_comment'];
        }
      }
      // Get names for current country from database
      $sql = "SELECT countryname_lang,
                     countryname_text,
                     countryname_countryid
                FROM %s
               WHERE countryname_countryid='%s'";
      $res =
        $this->databaseQueryFmt(
          $sql,
          array($this->tableCountryNames, $this->params['country_id'])
        );
      if ($res) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $data['lang_'.$row['countryname_lang']] = $row['countryname_text'];
        }
      }
    } else {
      // New country
      // Set hidden create parameter to 1;
      // do nothing else because there is no data yet
      $hidden['create'] = 1;
    }
    // Create form
    $this->countryDialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    // Set different form titles for new or existing category,
    // respectively
    if ($new) {
      $this->countryDialog->dialogTitle = $this->_gt('Add country');
    } else {
      $this->countryDialog->dialogTitle = $this->_gt('Edit country');
    }
    $this->countryDialog->msgs = &$this->msgs;
    $this->countryDialog->loadParams();
  }

  /**
  * Get delete Country form
  *
  * Displays a security question before deleting a country
  * The only parameter is a reference to the page layout
  *
  * @access public
  * @param object xsl_layout $layout
  */
  function getDelCountryForm(&$layout) {
    // Display the question only if the confirm_delete parameter
    // has not been set by a previous run of this method
    // and if there's a category id to delete
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    if ((!isset($this->params['confirm_delete'])) &&
          isset($this->params['country_id'])) {
      $hidden = array(
        'cmd' => 'del_country',
        'confirm_delete' => 1,
        'country_id' => $this->params['country_id']
      );
      if (isset($this->params['country_name']) && $this->params['country_name'] != '') {
        $country = $this->params['country_name'];
      } else {
        $country = $this->params['country_id'];
      }
      $msg = sprintf($this->_gt('Delete country "%s"?'), $country);
      $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $dialog->baseLink = $this->baseLink;
      $layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Get state list
  */
  function getStateList() {
    $result = '';
    // If we don't have a valid country id, get out of here
    if (!isset($this->params['country_id']) ||
        !$this->countryExists($this->params['country_id'])) {
      return $result;
    }
    // Read states for current country from database
    $sql = "SELECT state_id, state_name, state_country
              FROM %s
             WHERE state_country = '%s'
          ORDER BY state_name ASC";
    $sqlParams = array($this->tableStates, $this->params['country_id']);
    $states = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $states[$row['state_id']] = array(
          'name' => $row['state_name'],
          'abbr' => $row['state_id']
        );
      }
    }
    // If there are no states, get out of here
    if (empty($states)) {
      return $result;
    }
    // Prepare list view
    $title = sprintf(
      $this->_gt('States for "%s"'),
      $this->getCountryNameById($this->params['country_id'])
    );
    $result .= sprintf(
      '<listview title="%s">'.LF,
      papaya_strings::escapeHTMLChars($title)
    );
    $result .= '<cols>'.LF;
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('ID (Abbreviation)'))
    );
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Name'))
    );
    $result .= '</cols>'.LF;
    $result .= '<items>'.LF;
    foreach ($states as $stateId => $stateData) {
      $stateName = $stateData['name'];
      $stateAbbr = $stateData['abbr'];
      if (isset($this->params['state_id']) && $this->params['state_id'] == $stateId) {
        $selected = ' selected="selected"';
      } else {
        $selected = '';
      }
      $editHref = $this->getLink(
        array(
          'state_id' => $stateId,
          'country_id' => $this->params['country_id'],
          'cmd' => 'edit_state'
        )
      );
      $result .= sprintf(
        '<listitem title="%s" href="%s"%s>'.LF,
        papaya_strings::escapeHTMLChars($stateAbbr),
        papaya_strings::escapeHTMLChars($editHref),
        $selected
      );
      $result .= sprintf(
        '<subitem><a href="%s">%s</a></subitem>',
        papaya_strings::escapeHTMLChars($editHref),
        papaya_strings::escapeHTMLChars($stateName)
      );
      $result .= '</listitem>'.LF;
    }
    $result .= '</items>'.LF;
    $result .= '</listview>'.LF;
    return $result;
  }

  /**
  * Get edit state form
  *
  * Creates the form to edit a state
  * of a specified country
  * The first, mandatory paramter is a reference to
  * page layout, while the second, optional one
  * is a boolean that determines whether a new state is created (TRUE)
  * or whether an existing one is edited (FALSE, default)
  *
  * @access public
  * @param boolean $new optional, default FALSE
  * @return string XML
  */
  function getEditStateForm($new = FALSE) {
    $result = '';
    $this->initializeStateForm($new);
    if (is_object($this->stateDialog)) {
      $result = $this->stateDialog->getDialogXML();
    }
    return $result;
  }

  /**
  * Initialize the dialog to add/edit states
  *
  * @param boolean $new optional, default FALSE
  */
  function initializeStateForm($new = FALSE) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    // Display error msg and get out if trying to edit unavailable state
    if (!$new &&
        (!isset($this->params['state_id']) || $this->params['state_id'] == '')) {
      return;
    }
    if ($new &&
              (!isset($this->params['country_id']) || $this->params['country_id'] == '')) {
      return;
    }
    // Generate form fields
    $fields = array(
      $new ? 'state_id' : 'state_id_display' => array(
        'ID (Abbreviation)',
        'isNoHTML',
        TRUE,
        $new ? 'input' : 'disabled_input',
        8,
        'Must be unique across its country'
      ),
      'name' => array('Name', 'isNoHTML', TRUE, 'input', 100, ''),
      'comment' => array('Comment', 'isSomeText', FALSE, 'richtext', 6, '')
    );
    $data = array();
    // Hidden field: cmd 'save_state'
    $hidden = array(
      'cmd' => 'save_state',
      'country_id' => $this->params['country_id']
    );
    // Check whether this is a new country
    if (!$new) {
      // Existing state
      // Set hidden create parameter to 0
      $hidden['create'] = 0;
      $hidden['state_id'] = $this->params['state_id'];
      // Get data for current state from database
      $sql = "SELECT state_id,
                     state_country,
                     state_name,
                     state_comment
                FROM %s
               WHERE state_id = '%s'
                 AND state_country = '%s'";
      $sqlParams = array(
        $this->tableStates,
        $this->params['state_id'],
        $this->params['country_id']
      );
      if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $data['state_id_display'] = $row['state_id'];
          $data['name'] = $row['state_name'];
          $data['comment'] = $row['state_comment'];
        }
      }
    } else {
      // New state
      // Set hidden create parameter to 1;
      // add hidden country parameter
      // do nothing else because there is no data yet
      $hidden['create'] = 1;
    }
    $hidden['country_id'] = $this->params['country_id'];
    // Create form
    $this->stateDialog = new base_dialog(
      $this,
      $this->paramName,
      $fields,
      $data,
      $hidden
    );
    // Set different form titles for new or existing category,
    // respectively
    if ($new) {
      $countryName = $this->getCountryNameById($this->params['country_id']);
      $this->stateDialog->dialogTitle = sprintf(
        $this->_gt('Add state for "%s"'), $countryName
      );
    } else {
      $this->stateDialog->dialogTitle = sprintf(
        $this->_gt('Edit state "%s"'), $data['name']
      );
    }
    $this->stateDialog->msgs = &$this->msgs;
    $this->stateDialog->loadParams();
  }
  
  /**
  * Get delete state form
  *
  * Displays a security question before deleting a state
  * The only parameter is a reference to the page layout
  *
  * @access public
  * @param object xsl_layout $layout
  */
  function getDelStateForm(&$layout) {
    // Display the question only if the confirm_delete parameter
    // has not been set by a previous run of this method
    // and if there's a category id to delete
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    if ((!isset($this->params['confirm_delete'])) &&
          isset($this->params['state_id'])) {
      $hidden = array(
        'cmd' => 'del_state',
        'confirm_delete' => 1,
        'country_id' => $this->params['country_id'],
        'state_id' => $this->params['state_id']
      );
      if (isset($this->params['state_name']) && $this->params['state_name'] != '') {
        $state = $this->params['state_name'];
      } else {
        $state = $this->params['state_id'];
      }
      $msg = sprintf($this->_gt('Delete state "%s"?'), $state);
      $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $dialog->baseLink = $this->baseLink;
      $layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Get option list of states for a select form field.
  *
  * @access public
  * @see connector_countries::getStateOptionsXHTML()
  */
  function getStateListXHTML($country, $value = '') {
    $result = '';
    $sql = "SELECT state_id, state_name
              FROM %s
             WHERE state_country = '%s'
          ORDER BY state_name ASC";
    $sqlParams = array($this->tableStates, $country);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $id = $row['state_id'];
        $selected = ($value == $id) ? ' selected="selected"' : '';
        $result .= LF.sprintf(
          '<option value="%s" %s>%s</option>',
          papaya_strings::escapeHTMLChars($id),
          $selected,
          papaya_strings::escapeHTMLChars($row['state_name'])
        );
      }
    }
    return $result;
  }

  /**
  * Get an XML list of states (for AJAX use)
  *
  * @see connector_countries::getStateListXML()
  */
  function getStateListXML($country, $value = '') {
    $result = '';
    $sql = "SELECT state_id, state_name
              FROM %s
             WHERE state_country = '%s'
          ORDER BY state_name ASC";
    $sqlParams = array($this->tableStates, $country);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($res->count() > 0) {
        $result = '<states>';
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $id = $row['state_id'];
          $selected = ($value == $id) ? ' selected="selected"' : '';
          $result .= LF.sprintf(
            '<state id="%s" %s>%s</state>',
            papaya_strings::escapeHTMLChars($id),
            $selected,
            papaya_strings::escapeHTMLChars($row['state_name'])
          );
        }
        $result .= LF.'</states>';
      }
    }
    return $result;
  }

  /**
  * Get city list
  */
  function getCityList() {
    $result = '';
    // If we don't have valid country and state ids, get out of here
    if (!isset($this->params['country_id']) ||
        !$this->countryExists($this->params['country_id']) ||
        !isset($this->params['state_id']) ||
        !$this->stateExists($this->params['state_id'], $this->params['country_id'])) {
      return $result;
    }
    // Read cities for current country/state from database
    $sql = "SELECT city_id, city_name, city_state, city_country
              FROM %s
             WHERE city_state = '%s'
               AND city_country = '%s'
          ORDER BY city_name ASC";
    $sqlParams = array(
      $this->tableCities,
      $this->params['state_id'],
      $this->params['country_id']
    );
    $cities = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $cities[$row['city_id']] = array(
          'name' => $row['city_name'],
          'abbr' => $row['city_id']
        );
      }
    }
    // If there are no cities, get out of here
    if (empty($cities)) {
      return $result;
    }
    // Prepare list view
    $title = sprintf(
      $this->_gt('Cities for "%s (%s)"'),
      $this->getStateNameById($this->params['state_id'], $this->params['country_id']),
      $this->getCountryNameById($this->params['country_id'])
    );
    $result .= sprintf(
      '<listview title="%s">'.LF,
      papaya_strings::escapeHTMLChars($title)
    );
    $result .= '<cols>'.LF;
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('ID (Abbreviation)'))
    );
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Name'))
    );
    $result .= '</cols>'.LF;
    $result .= '<items>'.LF;
    foreach ($cities as $cityId => $cityData) {
      $cityName = $cityData['name'];
      $cityAbbr = $cityData['abbr'];
      if (isset($this->params['city_id']) && $this->params['city_id'] == $cityId) {
        $selected = ' selected="selected"';
      } else {
        $selected = '';
      }
      $editHref = $this->getLink(
        array(
          'city_id' => $cityId,
          'state_id' => $this->params['state_id'],
          'country_id' => $this->params['country_id'],
          'cmd' => 'edit_city'
        )
      );
      $result .= sprintf(
        '<listitem title="%s" href="%s"%s>'.LF,
        papaya_strings::escapeHTMLChars($cityAbbr),
        papaya_strings::escapeHTMLChars($editHref),
        $selected
      );
      $result .= sprintf(
        '<subitem><a href="%s">%s</a></subitem>',
        papaya_strings::escapeHTMLChars($editHref),
        papaya_strings::escapeHTMLChars($cityName)
      );
      $result .= '</listitem>'.LF;
    }
    $result .= '</items>'.LF;
    $result .= '</listview>'.LF;
    return $result;
  }

  /**
  * Get edit city form
  *
  * Creates the form to edit a city
  * of a specified state and country
  *
  * The first, mandatory paramter is a reference to
  * page layout, while the second, optional one
  * is a boolean that determines whether a new city is created (TRUE)
  * or whether an existing one is edited (FALSE, default)
  *
  * @param boolean $new optional, default FALSE
  */
  function getEditCityForm($new = FALSE) {
    $result = '';
    $this->initializeCityForm($new);
    if (is_object($this->cityDialog)) {
      $result = $this->cityDialog->getDialogXML();
    }
    return $result;
  }

  /**
  * Initialize the city edit form
  *
  * @param boolean $new optional, default FALSE
  */
  function initializeCityForm($new = FALSE) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    if (!$new &&
        (!isset($this->params['city_id']) || $this->params['city_id'] == '')) {
      return;
    }
    if ($new && (
        !isset($this->params['state_id']) || $this->params['state_id'] == '' ||
        !isset($this->params['country_id']) || $this->params['country_id'] == '')) {
      return;
    }
    // Generate form fields
    $fields = array(
      $new ? 'city_id' : 'city_id_display' => array(
        'ID (Abbreviation)',
        'isNoHTML',
        TRUE,
        $new ? 'input' : 'disabled_input',
        8,
        'Must be unique across its state'
      ),
      'name' => array('Name', 'isNoHTML', TRUE, 'input', 100, ''),
      'lat_long' => array(
        'Latitude/longitude',
        '(\d+(\.\d+)?,\s*\d+(\.\d+)?)',
        FALSE,
        'input',
        100,
        ''
      ),
      'comment' => array(
        'Comment',
        'isSomeText',
        FALSE,
        'richtext',
        6
      )
    );
    $data = array();
    $hidden = array(
      'cmd' => 'save_city',
      'country_id' => $this->params['country_id'],
      'state_id' => $this->params['state_id']
    );
    // Check whether this is a new city
    if (!$new) {
      // Existing city
      // Set hidden create parameter to 0
      $hidden['create'] = 0;
      $hidden['city_id'] = $this->params['city_id'];
      // Get data for current city from database
      $sql = "SELECT city_id,
                     city_state,
                     city_country,
                     city_name,
                     city_lat_long,
                     city_comment
                FROM %s
               WHERE city_id = '%s'
                 AND city_state = '%s'
                 AND city_country = '%s'";
      $sqlParams = array(
        $this->tableCities,
        $this->params['city_id'],
        $this->params['state_id'],
        $this->params['country_id']
      );
      if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $data['city_id_display'] = $row['city_id'];
          $data['name'] = $row['city_name'];
          $data['lat_long'] = $row['city_lat_long'];
          $data['comment'] = $row['city_comment'];
        }
      }
    } else {
      // New city
      // Set hidden create parameter to 1;
      // do nothing else because there is no data yet
      $hidden['create'] = 1;
    }
    // Create form
    $this->cityDialog = new base_dialog(
      $this,
      $this->paramName,
      $fields,
      $data,
      $hidden
    );
    // Set different form titles for new or existing city,
    // respectively
    if ($new) {
      $stateName = $this->getStateNameById(
        $this->params['state_id'],
        $this->params['country_id']
      );
      $this->cityDialog->dialogTitle = sprintf(
        $this->_gt('Add city for "%s"'), $stateName
      );
    } else {
      $this->cityDialog->dialogTitle = sprintf(
        $this->_gt('Edit city "%s"'), $data['name']
      );
    }
    $this->cityDialog->msgs = &$this->msgs;
    $this->cityDialog->loadParams();
  }

  /**
  * Get delete city form
  *
  * Displays a security question before deleting a city
  * The only parameter is a reference to the page layout
  *
  * @access public
  * @param object xsl_layout $layout
  */
  function getDelCityForm(&$layout) {
    // Display the question only if the confirm_delete parameter
    // has not been set by a previous run of this method
    // and if there's a city id to delete
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    if ((!isset($this->params['confirm_delete'])) &&
          isset($this->params['country_id']) &&
          isset($this->params['state_id']) &&
          isset($this->params['city_id'])) {
      $hidden = array(
        'cmd' => 'del_city',
        'confirm_delete' => 1,
        'country_id' => $this->params['country_id'],
        'state_id' => $this->params['state_id'],
        'city_id' => $this->params['city_id']
      );
      if (isset($this->params['city_name']) && $this->params['city_name'] != '') {
        $city = $this->params['city_name'];
      } else {
        $city = $this->params['city_id'];
      }
      $msg = sprintf($this->_gt('Delete city "%s"?'), papaya_strings::escapeHTMLChars($city));
      $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $dialog->baseLink = $this->baseLink;
      $layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Get option list of cities for a select form field.
  *
  * @see connector_countries::getCityOptionsXHTML()
  */
  function getCityListXHTML($state, $country, $value = '') {
    $result = '';
    $sql = "SELECT city_id, city_name
              FROM %s
             WHERE city_state = '%s'
               AND city_country = '%s'
          ORDER BY city_name ASC";
    $sqlParams = array($this->tableCities, $state, $country);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $id = $row['state_id'];
        $selected = ($value == $id) ? ' selected="selected"' : '';
        $result .= LF.sprintf(
          '<option value="%s"%s>%s</option>',
          papaya_strings::escapeHTMLChars($id),
          $selected,
          papaya_strings::escapeHTMLChars($row['city_name'])
        );
      }
    }
    return $result;
  }

  /**
  * Get an XML list of states (for AJAX use)
  *
  * @see connector_countries::getCityListXML()
  */
  function getCityListXML($state, $country, $value = '') {
    $result = '';
    $sql = "SELECT city_id, city_name
              FROM %s
             WHERE city_state = '%s'
               AND city_country = '%s'
          ORDER BY city_name ASC";
    $sqlParams = array($this->tableCities, $state, $country);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($res->count() > 0) {
        $result = '<cities>';
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $id = $row['city_id'];
          $selected = ($value == $id) ? ' selected="selected"' : '';
          $result .= LF.sprintf(
            '<city id="%s"%s>%s</city>',
            papaya_strings::escapeHTMLChars($id),
            $selected,
            papaya_strings::escapeHTMLChars($row['city_name'])
          );
        }
        $result .= LF.'</cities>';
      }
    }
    return $result;
  }

  /**
  * Get confirm reset form
  *
  * Displays a security question before resetting
  * the complete country list to factory settings
  * The only parameter is a reference to the page layout
  *
  * @access public
  * @param object xsl_layout $layout
  */
  function getResetForm(&$layout) {
    // Display the question only if the confirm_reset parameter
    // has not been set by a previous run of this method
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    if (!isset($this->params['confirm_reset'])) {
      $hidden = array(
        'cmd' => 'reset',
        'confirm_reset' => 1
      );
      $msg = $this->_gt('Reset country list?');
      $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Reset';
      $dialog->baseLink = $this->baseLink;
      $layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Get buttons
  *
  * This method builds the main button bar for country management
  *
  * @access public
  */
  function getButtons() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    $toolbar = new base_btnbuilder;
    $toolbar->images = &$this->images;
    $cmd = '';
    if (isset($this->params['cmd'])) {
      $cmd = $this->params['cmd'];
    }
    if ($this->module->hasPerm(2, FALSE)) {
      $toolbar->addButton(
        'Reset list',
        $this->getLink(array('cmd' => 'reset')),
        'actions-execute',
        'Restore default settings',
        $cmd == 'reset'
      );
      $toolbar->addSeparator();
    }
    $toolbar->addButton(
      'Add country',
      $this->getLink(array('cmd' => 'add_country')),
      'actions-phrase-add',
      '',
      $cmd == 'add_country'
    );
    if (isset($this->params['country_id']) && $this->params['country_id'] != '') {
      $toolbar->addButton(
        'Delete country',
        $this->getLink(
          array(
            'country_id' => $this->params['country_id'],
            'cmd' => 'del_country'
          )
        ),
        'actions-phrase-delete',
        '',
        $cmd == 'del_country'
      );
      $toolbar->addSeparator();
      $toolbar->addButton(
        'Add state',
        $this->getLink(
          array(
            'country_id' => $this->params['country_id'],
            'cmd' => 'add_state'
          )
        ),
        'actions-phrase-add',
        '',
        $cmd == 'add_state'
      );
      if (isset($this->params['state_id']) && $this->params['state_id'] != '') {
        $toolbar->addButton(
          'Delete state',
          $this->getLink(
            array(
              'state_id' => $this->params['state_id'],
              'country_id' => $this->params['country_id'],
              'cmd' => 'del_state'
            )
          ),
          'actions-phrase-delete',
          '',
          $cmd == 'del_state'
        );
        $toolbar->addSeparator();
        $toolbar->addButton(
          'Add city',
          $this->getLink(
            array(
              'state_id' => $this->params['state_id'],
              'country_id' => $this->params['country_id'],
              'cmd' => 'add_city'
            )
          ),
          'actions-phrase-add',
          '',
          $cmd == 'add_city'
        );
        if (isset($this->params['city_id']) && $this->params['city_id'] != '') {
          $toolbar->addButton(
            'Delete city',
            $this->getLink(
              array(
                'city_id' => $this->params['city_id'],
                'state_id' => $this->params['state_id'],
                'country_id' => $this->params['country_id'],
                'cmd' => 'del_city'
              )
            ),
            'actions-phrase-delete',
            '',
            $cmd == 'del_city'
          );
        }
      }
    }
    if ($str = $toolbar->getXML()) {
      $this->layout->addMenu(
        sprintf(
          '<menu ident="edit">%s</menu>'.LF,
          $str
        )
      );
    }
  }

  /**
  * get array for language selection
  *
  * @access public
  * @param string $assoc optional
  * @param mixed string $exclude optional
  * @return string
  */
  function getLanguageSelector($assoc = TRUE, $exclude = '') {
    $langs = array();
    $sql = "SELECT lng_id, lng_short, lng_title
              FROM %s";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableLng))) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (isset($exclude) &&
            trim($exclude) != '' &&
            preg_match('(^'.preg_quote($exclude).')', $row['lng_short'])) {
          continue;
        }
        if ($assoc) {
          $langs[$row['lng_id']] = sprintf('%s (%s)', $row['lng_title'], $row['lng_short']);
        } else {
          $langs[] = $row['lng_id'];
        }
      }
    }
    return $langs;
  }

  /**
  * get language id by regexp (first match or 0)
  *
  * @access public
  * @param string $regexp
  * @return int
  */
  function getLanguageByRegexp($regexp) {
    $sql = "SELECT lng_id, lng_short
              FROM %s";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableLng))) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (preg_match('(^'.preg_quote($regexp).')', $row['lng_short'])) {
          return $row['lng_id'];
        }
      }
    }
    return 0;
  }

  /**
  * Continent callback function (for backend)
  */
  function callbackContinent($name, $field, $data, $paramName = NULL) {
    $paramName = ($paramName === NULL) ? $this->paramName : $paramName;
    $result = sprintf(
      '<select id="continent" name="%s[%s]" class="dialogSelect dialogScale" size="1">',
      papaya_strings::escapeHTMLChars($paramName),
      papaya_strings::escapeHTMLChars($name)
    );
    $selected = ($data == 0) ? ' selected="selected"' : '';
    $result .= sprintf(
      '<option value="0" %s>%s</option>',
      $selected,
      papaya_strings::escapeHTMLChars($this->_gt('[Please choose]'))
    );
    $sql = "SELECT continent_id, continent_name
              FROM %s
             ORDER BY continent_name ASC";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableContinents))) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $selected = ($data == $row['continent_id']) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%d" %s>%s</option>',
          papaya_strings::escapeHTMLChars($row['continent_id']),
          $selected,
          papaya_strings::escapeHTMLChars($row['continent_name'])
        );
      }
    }
    $result .= '</select>';
    return $result;
  }

  /**
  * Country callback function (for frontend)
  */
  function callbackCountries($name, $field, $data, $lngId, $paramName = NULL,
                             $continentId = NULL, $countryCaption = NULL) {
    $paramName = ($paramName === NULL) ? $this->paramName : $paramName;
    $countryCaption = ($countryCaption === NULL) ? '[Please choose]' : $countryCaption;

    $result = sprintf(
      '<select id="country" name="%s[%s]" class="dialogSelect dialogScale" size="1">',
      papaya_strings::escapeHTMLChars($paramName),
      papaya_strings::escapeHTMLChars($name)
    );
    $selected = ($data == 0) ? ' selected="selected"' : '';
    $result .= sprintf(
      '<option value="-" %s>%s</option>',
      $selected,
      papaya_strings::escapeHTMLChars($countryCaption)
    );
    $result .= $this->getCountryListXHTML($data, $continentId, $lngId);
    $result .= '</select>';
    return $result;
  }

  /**
  * Check data
  *
  * If the tables do not contain any data yet,
  * or if a reset is forced,
  * a basic set of data will be created
  *
  * @access public
  * @param boolean $reset optional
  */
  function checkData($reset = FALSE) {
    // The (non-legacy) database tables involved
    $tables = array(
      $this->tableContinents,
      $this->tableCountries,
      $this->tableCountryNames,
      $this->tableStates
     );
    // Perform security checks only if no reset is forced
    if (!$reset) {
      // Sum of numbers of records in all three tables
      $sum = 0;
      foreach ($tables as $table) {
        $sql = "SELECT COUNT(*)
                  FROM %s";
        $sqlData = array($table);
        if ($res = $this->databaseQueryFmt($sql, $sqlData)) {
          if ($num = $res->fetchField()) {
            $sum += $num;
          }
        }
      }
      // Exit if sum is greater than 0
      if ($sum > 0) {
        return;
      }
    } else {
      // Reset: empty the tables
      foreach ($tables as $table) {
        $this->databaseEmptyTable($table);
      }
    }
    $this->addMsg(MSG_INFO, 'Resetting country data');
    // Add default data
    // Continents
    $continents = array(
      1 => 'Africa',
      2 => 'Asia',
      3 => 'Australia and Oceania',
      4 => 'Europe',
      5 => 'North and Central America',
      6 => 'South America'
    );
    foreach ($continents as $id => $continent) {
      $data[] = array(
        'continent_id' => $id,
        'continent_name' => $continent
      );
    }
    if (!$this->databaseInsertRecords($this->tableContinents, $data)) {
      $this->addMsg(
        MSG_ERROR,
        $this->_gt('Could not insert default country continents data!')
      );
    }

    // The complete country list
    $countries = array(
      array(
        'country_id' => 'ad',
        'country_fallbackname' => 'Andorra',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'ae',
        'country_fallbackname' => 'United Arab Emirates',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'af',
        'country_fallbackname' => 'Afghanistan',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'ag',
        'country_fallbackname' => 'Antigua and Barbuda',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'ai',
        'country_fallbackname' => 'Anguilla',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'al',
        'country_fallbackname' => 'Albania',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'am',
        'country_fallbackname' => 'Armenia',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'an',
        'country_fallbackname' => 'Netherlands Antilles',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'ao',
        'country_fallbackname' => 'Angola',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'ar',
        'country_fallbackname' => 'Argentina',
        'country_continent' => '6'
      ),
      array(
        'country_id' => 'as',
        'country_fallbackname' => 'American Samoa',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'at',
        'country_fallbackname' => 'Austria',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'au',
        'country_fallbackname' => 'Australia',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'aw',
        'country_fallbackname' => 'Aruba',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'az',
        'country_fallbackname' => 'Azerbaidjan',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'ba',
        'country_fallbackname' => 'Bosnia-Herzegovina',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'bb',
        'country_fallbackname' => 'Barbados',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'bd',
        'country_fallbackname' => 'Bangladesh',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'be',
        'country_fallbackname' => 'Belgium',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'bf',
        'country_fallbackname' => 'Burkina Faso',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'bg',
        'country_fallbackname' => 'Bulgaria',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'bh',
        'country_fallbackname' => 'Bahrain',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'bi',
        'country_fallbackname' => 'Burundi',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'bj',
        'country_fallbackname' => 'Benin',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'bm',
        'country_fallbackname' => 'Bermuda',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'bn',
        'country_fallbackname' => 'Brunei',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'bo',
        'country_fallbackname' => 'Bolivia',
        'country_continent' => '6'
      ),
      array(
        'country_id' => 'br',
        'country_fallbackname' => 'Brazil',
        'country_continent' => '6'
      ),
      array(
        'country_id' => 'bs',
        'country_fallbackname' => 'Bahamas',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'bt',
        'country_fallbackname' => 'Bhutan',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'bw',
        'country_fallbackname' => 'Botswana',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'by',
        'country_fallbackname' => 'Belarus',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'bz',
        'country_fallbackname' => 'Belize',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'ca',
        'country_fallbackname' => 'Canada',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'cc',
        'country_fallbackname' => 'Cocos (
        Keeling) Islands',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'cf',
        'country_fallbackname' => 'Central African Republic',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'cd',
        'country_fallbackname' => 'Congo,
        Democratic Republic',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'cg',
        'country_fallbackname' => 'Congo',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'ch',
        'country_fallbackname' => 'Switzerland',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'ci',
        'country_fallbackname' => 'Ivory Coast',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'ck',
        'country_fallbackname' => 'Cook Islands',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'cl',
        'country_fallbackname' => 'Chile',
        'country_continent' => '6'
      ),
      array(
        'country_id' => 'cm',
        'country_fallbackname' => 'Cameroon',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'cn',
        'country_fallbackname' => 'China',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'co',
        'country_fallbackname' => 'Colombia',
        'country_continent' => '6'
      ),
      array(
        'country_id' => 'cr',
        'country_fallbackname' => 'Costa Rica',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'cu',
        'country_fallbackname' => 'Cuba',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'cv',
        'country_fallbackname' => 'Cape Verde',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'cx',
        'country_fallbackname' => 'Christmas Island',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'cy',
        'country_fallbackname' => 'Cyprus',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'cz',
        'country_fallbackname' => 'Czech Republic',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'de',
        'country_fallbackname' => 'Germany',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'dj',
        'country_fallbackname' => 'Djibouti',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'dk',
        'country_fallbackname' => 'Denmark',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'dm',
        'country_fallbackname' => 'Dominica',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'do',
        'country_fallbackname' => 'Dominican Republic',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'dz',
        'country_fallbackname' => 'Algeria',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'ec',
        'country_fallbackname' => 'Ecuador',
        'country_continent' => '6'
      ),
      array(
        'country_id' => 'ee',
        'country_fallbackname' => 'Estonia',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'eg',
        'country_fallbackname' => 'Egypt',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'eh',
        'country_fallbackname' => 'Western Sahara',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'er',
        'country_fallbackname' => 'Eritrea',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'es',
        'country_fallbackname' => 'Spain',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'et',
        'country_fallbackname' => 'Ethiopia',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'fi',
        'country_fallbackname' => 'Finland',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'fj',
        'country_fallbackname' => 'Fiji',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'fk',
        'country_fallbackname' => 'Falkland Islands',
        'country_continent' => '6'
      ),
      array(
        'country_id' => 'fm',
        'country_fallbackname' => 'Micronesia',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'fo',
        'country_fallbackname' => 'Faroe Islands',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'fr',
        'country_fallbackname' => 'France',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'ga',
        'country_fallbackname' => 'Gabon',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'gd',
        'country_fallbackname' => 'Grenada',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'ge',
        'country_fallbackname' => 'Georgia',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'gf',
        'country_fallbackname' => 'French Guyana',
        'country_continent' => '6'
      ),
      array(
        'country_id' => 'gh',
        'country_fallbackname' => 'Ghana',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'gi',
        'country_fallbackname' => 'Gibraltar',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'gm',
        'country_fallbackname' => 'Gambia',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'gn',
        'country_fallbackname' => 'Guinea',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'gp',
        'country_fallbackname' => 'Guadeloupe',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'gq',
        'country_fallbackname' => 'Equatorial Guinea',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'gr',
        'country_fallbackname' => 'Greece',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'gt',
        'country_fallbackname' => 'Guatemala',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'gu',
        'country_fallbackname' => 'Guam (USA)',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'gw',
        'country_fallbackname' => 'Guinea-Bissau',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'gy',
        'country_fallbackname' => 'Guyana',
        'country_continent' => '6'
      ),
      array(
        'country_id' => 'hk',
        'country_fallbackname' => 'Hong Kong',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'hn',
        'country_fallbackname' => 'Honduras',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'hr',
        'country_fallbackname' => 'Croatia',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'ht',
        'country_fallbackname' => 'Haiti',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'hu',
        'country_fallbackname' => 'Hungary',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'id',
        'country_fallbackname' => 'Indonesia',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'ie',
        'country_fallbackname' => 'Ireland',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'il',
        'country_fallbackname' => 'Israel',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'in',
        'country_fallbackname' => 'India',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'io',
                'country_fallbackname' => 'British Indian Ocean Territory',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'iq',
        'country_fallbackname' => 'Iraq',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'ir',
        'country_fallbackname' => 'Iran',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'is',
        'country_fallbackname' => 'Iceland',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'it',
        'country_fallbackname' => 'Italy',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'jm',
        'country_fallbackname' => 'Jamaica',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'jo',
        'country_fallbackname' => 'Jordan',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'jp',
        'country_fallbackname' => 'Japan',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'ke',
        'country_fallbackname' => 'Kenya',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'kg',
        'country_fallbackname' => 'Kyrgyz Republic',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'kh',
        'country_fallbackname' => 'Cambodia',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'ki',
        'country_fallbackname' => 'Kiribati',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'km',
        'country_fallbackname' => 'Comoros',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'kn',
                'country_fallbackname' => 'Saint Kitts & Nevis Anguilla',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'kp',
        'country_fallbackname' => 'North Korea',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'kr',
        'country_fallbackname' => 'South Korea',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'kw',
        'country_fallbackname' => 'Kuwait',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'ky',
        'country_fallbackname' => 'Cayman Islands',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'kz',
        'country_fallbackname' => 'Kazakhstan',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'la',
        'country_fallbackname' => 'Laos',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'lb',
        'country_fallbackname' => 'Lebanon',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'lc',
        'country_fallbackname' => 'Saint Lucia',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'li',
        'country_fallbackname' => 'Liechtenstein',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'lk',
        'country_fallbackname' => 'Sri Lanka',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'lr',
        'country_fallbackname' => 'Liberia',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'ls',
        'country_fallbackname' => 'Lesotho',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'lt',
        'country_fallbackname' => 'Lithuania',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'lu',
        'country_fallbackname' => 'Luxembourg',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'lv',
        'country_fallbackname' => 'Latvia',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'ly',
        'country_fallbackname' => 'Libya',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'ma',
        'country_fallbackname' => 'Morocco',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'mc',
        'country_fallbackname' => 'Monaco',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'md',
        'country_fallbackname' => 'Moldavia',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'mg',
        'country_fallbackname' => 'Madagascar',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'mh',
        'country_fallbackname' => 'Marshall Islands',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'mk',
        'country_fallbackname' => 'Macedonia',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'ml',
        'country_fallbackname' => 'Mali',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'mm',
        'country_fallbackname' => 'Myanmar',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'mn',
        'country_fallbackname' => 'Mongolia',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'mo',
        'country_fallbackname' => 'Macau',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'mp',
                'country_fallbackname' => 'Northern Mariana Islands',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'mq',
        'country_fallbackname' => 'Martinique',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'mr',
        'country_fallbackname' => 'Mauritania',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'ms',
        'country_fallbackname' => 'Montserrat',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'mt',
        'country_fallbackname' => 'Malta',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'mu',
        'country_fallbackname' => 'Mauritius',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'mv',
        'country_fallbackname' => 'Maldives',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'mw',
        'country_fallbackname' => 'Malawi',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'mx',
        'country_fallbackname' => 'Mexico',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'my',
        'country_fallbackname' => 'Malaysia',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'mz',
        'country_fallbackname' => 'Mozambique',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'na',
        'country_fallbackname' => 'Namibia',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'nc',
        'country_fallbackname' => 'New Caledonia',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'ne',
        'country_fallbackname' => 'Niger',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'nf',
        'country_fallbackname' => 'Norfolk Island',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'ng',
        'country_fallbackname' => 'Nigeria',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'ni',
        'country_fallbackname' => 'Nicaragua',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'nl',
        'country_fallbackname' => 'Netherlands',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'no',
        'country_fallbackname' => 'Norway',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'np',
        'country_fallbackname' => 'Nepal',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'nr',
        'country_fallbackname' => 'Nauru',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'nu',
        'country_fallbackname' => 'Niue',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'nz',
        'country_fallbackname' => 'New Zealand',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'om',
        'country_fallbackname' => 'Oman',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'pa',
        'country_fallbackname' => 'Panama',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'pe',
        'country_fallbackname' => 'Peru',
        'country_continent' => '6'
      ),
      array(
        'country_id' => 'pf',
        'country_fallbackname' => 'Polynesia (
        French)',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'pg',
        'country_fallbackname' => 'Papua New Guinea',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'ph',
        'country_fallbackname' => 'Philippines',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'pk',
        'country_fallbackname' => 'Pakistan',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'pl',
        'country_fallbackname' => 'Poland',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'pm',
                'country_fallbackname' => 'Saint Pierre and Miquelon',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'pn',
        'country_fallbackname' => 'Pitcairn Island',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'pr',
        'country_fallbackname' => 'Puerto Rico',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'pt',
        'country_fallbackname' => 'Portugal',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'pw',
        'country_fallbackname' => 'Palau',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'py',
        'country_fallbackname' => 'Paraguay',
        'country_continent' => '6'
      ),
      array(
        'country_id' => 'qa',
        'country_fallbackname' => 'Qatar',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 're',
        'country_fallbackname' => 'Reunion (
        French)',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'ro',
        'country_fallbackname' => 'Romania',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'ru',
        'country_fallbackname' => 'Russian Federation',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'rw',
        'country_fallbackname' => 'Rwanda',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'sa',
        'country_fallbackname' => 'Saudi Arabia',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'sb',
        'country_fallbackname' => 'Solomon Islands',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'sc',
        'country_fallbackname' => 'Seychelles',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'sd',
        'country_fallbackname' => 'Sudan',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'se',
        'country_fallbackname' => 'Sweden',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'sg',
        'country_fallbackname' => 'Singapore',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'sh',
        'country_fallbackname' => 'Saint Helena',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'si',
        'country_fallbackname' => 'Slovenia',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'sk',
        'country_fallbackname' => 'Slovak Republic',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'sl',
        'country_fallbackname' => 'Sierra Leone',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'sm',
        'country_fallbackname' => 'San Marino',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'sn',
        'country_fallbackname' => 'Senegal',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'so',
        'country_fallbackname' => 'Somalia',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'sr',
        'country_fallbackname' => 'Suriname',
        'country_continent' => '6'
      ),
      array(
        'country_id' => 'st',
        'country_fallbackname' => 'Sao Tome and Principe',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'sv',
        'country_fallbackname' => 'El Salvador',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'sy',
        'country_fallbackname' => 'Syria',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'sz',
        'country_fallbackname' => 'Swaziland',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'tc',
        'country_fallbackname' => 'Turks and Caicos Islands',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'td',
        'country_fallbackname' => 'Chad',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'tg',
        'country_fallbackname' => 'Togo',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'th',
        'country_fallbackname' => 'Thailand',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'tj',
        'country_fallbackname' => 'Tadjikistan',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'tk',
        'country_fallbackname' => 'Tokelau',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'tm',
        'country_fallbackname' => 'Turkmenistan',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'tn',
        'country_fallbackname' => 'Tunisia',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'to',
        'country_fallbackname' => 'Tonga',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'tp',
        'country_fallbackname' => 'East Timor',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'tr',
        'country_fallbackname' => 'Turkey',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'tt',
        'country_fallbackname' => 'Trinidad and Tobago',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'tv',
        'country_fallbackname' => 'Tuvalu',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'tw',
        'country_fallbackname' => 'Taiwan',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'tz',
        'country_fallbackname' => 'Tanzania',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'ua',
        'country_fallbackname' => 'Ukraine',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'ug',
        'country_fallbackname' => 'Uganda',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'uk',
        'country_fallbackname' => 'United Kingdom',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'um',
        'country_fallbackname' => 'USA Minor Outlying Islands',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'us',
        'country_fallbackname' => 'USA',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'uy',
        'country_fallbackname' => 'Uruguay',
        'country_continent' => '6'
      ),
      array(
        'country_id' => 'uz',
        'country_fallbackname' => 'Uzbekistan',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'va',
        'country_fallbackname' => 'Holy See',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'vc',
        'country_fallbackname' => 'Saint Vincent & Grenadines',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 've',
        'country_fallbackname' => 'Venezuela',
        'country_continent' => '6'
      ),
      array(
        'country_id' => 'vg',
        'country_fallbackname' => 'Virgin Islands (
        British)',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'vi',
        'country_fallbackname' => 'Virgin Islands (USA)',
        'country_continent' => '5'
      ),
      array(
        'country_id' => 'vn',
        'country_fallbackname' => 'Vietnam',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'vu',
        'country_fallbackname' => 'Vanuatu',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'wf',
        'country_fallbackname' => 'Wallis and Futuna Islands',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'ws',
        'country_fallbackname' => 'Samoa',
        'country_continent' => '3'
      ),
      array(
        'country_id' => 'ye',
        'country_fallbackname' => 'Yemen',
        'country_continent' => '2'
      ),
      array(
        'country_id' => 'yt',
        'country_fallbackname' => 'Mayotte',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'rs',
        'country_fallbackname' => 'Serbia',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'me',
        'country_fallbackname' => 'Montenegro',
        'country_continent' => '4'
      ),
      array(
        'country_id' => 'za',
        'country_fallbackname' => 'South Africa',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'zm',
        'country_fallbackname' => 'Zambia',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'zr',
        'country_fallbackname' => 'Zaire',
        'country_continent' => '1'
      ),
      array(
        'country_id' => 'zw',
        'country_fallbackname' => 'Zimbabwe',
        'country_continent' => '1')
    );

    $this->databaseInsertRecords($this->tableCountries, $countries);

    // Add the German country names if German is an available frontend language
    // Get the language id for German
    $german = $this->getLanguageByRegexp('de');
    if ($german > 0) {
      $countriesGerman = array(
        array(
          'countryname_countryid' => 'ad',
          'countryname_text' => 'Andorra',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ae',
          'countryname_text' => 'Vereinigte Arabische Emirate',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'af',
          'countryname_text' => 'Afghanistan',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ag',
          'countryname_text' => 'Antigua und Barbuda',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ai',
          'countryname_text' => 'Anguilla',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'al',
          'countryname_text' => 'Albanien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'am',
          'countryname_text' => 'Armenien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'an',
          'countryname_text' => 'Niederländische Antillen',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ao',
          'countryname_text' => 'Angola',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ar',
          'countryname_text' => 'Argentinien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'as',
          'countryname_text' => 'Amerikanisch-Samoa',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'at',
          'countryname_text' => 'Österreich',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'au',
          'countryname_text' => 'Australien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'aw',
          'countryname_text' => 'Aruba',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'az',
          'countryname_text' => 'Azerbaidschan',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ba',
          'countryname_text' => 'Bosnien-Herzegovina',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'bb',
          'countryname_text' => 'Barbados',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'bd',
          'countryname_text' => 'Bangladesch',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'be',
          'countryname_text' => 'Belgien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'bf',
          'countryname_text' => 'Burkina Faso',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'bg',
          'countryname_text' => 'Bulgarien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'bh',
          'countryname_text' => 'Bahrain',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'bi',
          'countryname_text' => 'Burundi',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'bj',
          'countryname_text' => 'Benin',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'bm',
          'countryname_text' => 'Bermuda',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'bn',
          'countryname_text' => 'Brunei',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'bo',
          'countryname_text' => 'Bolivien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'br',
          'countryname_text' => 'Brasilien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'bs',
          'countryname_text' => 'Bahamas',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'bt',
          'countryname_text' => 'Bhutan',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'bw',
          'countryname_text' => 'Botswana',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'by',
          'countryname_text' => 'Weißrussland',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'bz',
          'countryname_text' => 'Belize',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ca',
          'countryname_text' => 'Kanada',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'cc',
          'countryname_text' => 'Kokosinseln',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'cf',
          'countryname_text' => 'Zentralafrikanische Republik',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'cd',
          'countryname_text' => 'Kongo, Democratische Republik',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'cg',
          'countryname_text' => 'Kongo',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ch',
          'countryname_text' => 'Schweiz',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ci',
          'countryname_text' => 'Elfenbeinküste',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ck',
          'countryname_text' => 'Cookinseln',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'cl',
          'countryname_text' => 'Chile',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'cm',
          'countryname_text' => 'Kamerun',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'cn',
          'countryname_text' => 'China',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'co',
          'countryname_text' => 'Kolumbien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'cr',
          'countryname_text' => 'Costa Rica',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'cu',
          'countryname_text' => 'Kuba',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'cv',
          'countryname_text' => 'Kapverden',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'cx',
          'countryname_text' => 'Weihnachtsinsel',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'cy',
          'countryname_text' => 'Zypern',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'cz',
          'countryname_text' => 'Tschechische Republik',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'de',
          'countryname_text' => 'Deutschland',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'dj',
          'countryname_text' => 'Dschibuti',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'dk',
          'countryname_text' => 'Dänemark',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'dm',
          'countryname_text' => 'Dominica',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'do',
          'countryname_text' => 'Dominikanische Republik',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'dz',
          'countryname_text' => 'Algerien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ec',
          'countryname_text' => 'Ecuador',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ee',
          'countryname_text' => 'Estland',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'eg',
          'countryname_text' => 'Ägypten',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'eh',
          'countryname_text' => 'Westsahara',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'er',
          'countryname_text' => 'Eritrea',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'es',
          'countryname_text' => 'Spanien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'et',
          'countryname_text' => 'Äthiopien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'fi',
          'countryname_text' => 'Finnland',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'fj',
          'countryname_text' => 'Fidschi',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'fk',
          'countryname_text' => 'Falklandinseln',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'fm',
          'countryname_text' => 'Mikronesien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'fo',
          'countryname_text' => 'Färöer',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'fr',
          'countryname_text' => 'Frankreich',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ga',
          'countryname_text' => 'Gabun',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'gd',
          'countryname_text' => 'Grenada',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ge',
          'countryname_text' => 'Georgien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'gf',
          'countryname_text' => 'Französisch-Guayana',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'gh',
          'countryname_text' => 'Ghana',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'gi',
          'countryname_text' => 'Gibraltar',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'gm',
          'countryname_text' => 'Gambia',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'gn',
          'countryname_text' => 'Guinea',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'gp',
          'countryname_text' => 'Guadeloupe',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'gq',
          'countryname_text' => 'Äquatorialguinea',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'gr',
          'countryname_text' => 'Griechenland',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'gt',
          'countryname_text' => 'Guatemala',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'gu',
          'countryname_text' => 'Guam (USA
        )',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'gw',
          'countryname_text' => 'Guinea-Bissau',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'gy',
          'countryname_text' => 'Guyana',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'hk',
          'countryname_text' => 'Hongkong',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'hn',
          'countryname_text' => 'Honduras',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'hr',
          'countryname_text' => 'Kroatien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ht',
          'countryname_text' => 'Haiti',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'hu',
          'countryname_text' => 'Ungarn',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'id',
          'countryname_text' => 'Indonesien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ie',
          'countryname_text' => 'Irland',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'il',
          'countryname_text' => 'Israel',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'in',
          'countryname_text' => 'Indien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'io',
          'countryname_text' => 'Britisches Territorium im Ind. Ozean',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'iq',
          'countryname_text' => 'Irak',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ir',
          'countryname_text' => 'Iran',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'is',
          'countryname_text' => 'Island',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'it',
          'countryname_text' => 'Italien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'jm',
          'countryname_text' => 'Jamaika',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'jo',
          'countryname_text' => 'Jordanien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'jp',
          'countryname_text' => 'Japan',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ke',
          'countryname_text' => 'Kenia',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'kg',
          'countryname_text' => 'Kirgistan',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'kh',
          'countryname_text' => 'Kambodscha',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ki',
          'countryname_text' => 'Kiribati',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'km',
          'countryname_text' => 'Komoren',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'kn',
          'countryname_text' => 'Saint Kitts & Nevis Anguilla',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'kp',
          'countryname_text' => 'Nordkorea',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'kr',
          'countryname_text' => 'Südkorea',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'kw',
          'countryname_text' => 'Kuwait',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ky',
          'countryname_text' => 'Kaimaninseln',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'kz',
          'countryname_text' => 'Kasachstan',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'la',
          'countryname_text' => 'Laos',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'lb',
          'countryname_text' => 'Libanon',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'lc',
          'countryname_text' => 'St. Lucia',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'li',
          'countryname_text' => 'Liechtenstein',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'lk',
          'countryname_text' => 'Sri Lanka',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'lr',
          'countryname_text' => 'Liberia',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ls',
          'countryname_text' => 'Lesotho',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'lt',
          'countryname_text' => 'Litauen',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'lu',
          'countryname_text' => 'Luxemburg',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'lv',
          'countryname_text' => 'Lettland',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ly',
          'countryname_text' => 'Libyen',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ma',
          'countryname_text' => 'Marokko',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'mc',
          'countryname_text' => 'Monaco',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'md',
          'countryname_text' => 'Moldau',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'mg',
          'countryname_text' => 'Madagaskar',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'mh',
          'countryname_text' => 'Marshallinseln',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'mk',
          'countryname_text' => 'Mazedonien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ml',
          'countryname_text' => 'Mali',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'mm',
          'countryname_text' => 'Myanmar',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'mn',
          'countryname_text' => 'Mongolei',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'mo',
          'countryname_text' => 'Macao',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'mp',
          'countryname_text' => 'Nördliche Marianen',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'mq',
          'countryname_text' => 'Martinique',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'mr',
          'countryname_text' => 'Mauretanien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ms',
          'countryname_text' => 'Montserrat',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'mt',
          'countryname_text' => 'Malta',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'mu',
          'countryname_text' => 'Mauritius',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'mv',
          'countryname_text' => 'Malediven',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'mw',
          'countryname_text' => 'Malawi',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'mx',
          'countryname_text' => 'Mexiko',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'my',
          'countryname_text' => 'Malaysia',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'mz',
          'countryname_text' => 'Mosambik',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'na',
          'countryname_text' => 'Namibia',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'nc',
          'countryname_text' => 'Neukaledonien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ne',
          'countryname_text' => 'Niger',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'nf',
          'countryname_text' => 'Norfolkinsel',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ng',
          'countryname_text' => 'Nigeria',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ni',
          'countryname_text' => 'Nicaragua',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'nl',
          'countryname_text' => 'Niederland',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'no',
          'countryname_text' => 'Norwegen',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'np',
          'countryname_text' => 'Nepal',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'nr',
          'countryname_text' => 'Nauru',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'nu',
          'countryname_text' => 'Niue',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'nz',
          'countryname_text' => 'Neusealand',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'om',
          'countryname_text' => 'Oman',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'pa',
          'countryname_text' => 'Panama',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'pe',
          'countryname_text' => 'Peru',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'pf',
          'countryname_text' => 'Polynesien (frz.
        )',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'pg',
          'countryname_text' => 'Papua-Neuguinea',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ph',
          'countryname_text' => 'Philippinen',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'pk',
          'countryname_text' => 'Pakistan',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'pl',
          'countryname_text' => 'Polen',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'pm',
          'countryname_text' => 'Saint-Pierre und Miquelon',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'pn',
          'countryname_text' => 'Pitcairninseln',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'pr',
          'countryname_text' => 'Puerto Rico',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'pt',
          'countryname_text' => 'Portugal',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'pw',
          'countryname_text' => 'Palau',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'py',
          'countryname_text' => 'Paraguay',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'qa',
          'countryname_text' => 'Qatar',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 're',
          'countryname_text' => 'Réunion (frz.
        )',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ro',
          'countryname_text' => 'Rumänien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ru',
          'countryname_text' => 'Russische Föderation',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'rw',
          'countryname_text' => 'Ruanda',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'sa',
          'countryname_text' => 'Saudi-Arabien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'sb',
          'countryname_text' => 'Salomonen',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'sc',
          'countryname_text' => 'Seychellen',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'sd',
          'countryname_text' => 'Sudan',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'se',
          'countryname_text' => 'Schweden',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'sg',
          'countryname_text' => 'Singapur',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'sh',
          'countryname_text' => 'St. Helena',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'si',
          'countryname_text' => 'Slowenien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'sk',
          'countryname_text' => 'Slovakei',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'sl',
          'countryname_text' => 'Sierra Leone',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'sm',
          'countryname_text' => 'San Marino',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'sn',
          'countryname_text' => 'Senegal',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'so',
          'countryname_text' => 'Somalia',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'sr',
          'countryname_text' => 'Surinam',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'st',
          'countryname_text' => 'São Tomé und Príncipe',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'sv',
          'countryname_text' => 'El Salvador',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'sy',
          'countryname_text' => 'Syrien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'sz',
          'countryname_text' => 'Swasiland',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'tc',
          'countryname_text' => 'Turks- und Caicosinseln',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'td',
          'countryname_text' => 'Tschad',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'tg',
          'countryname_text' => 'Togo',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'th',
          'countryname_text' => 'Thailand',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'tj',
          'countryname_text' => 'Tadschikistan',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'tk',
          'countryname_text' => 'Tokelau',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'tm',
          'countryname_text' => 'Turkmenistan',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'tn',
          'countryname_text' => 'Tunesien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'to',
          'countryname_text' => 'Tonga',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'tp',
          'countryname_text' => 'Osttimor',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'tr',
          'countryname_text' => 'Türkei',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'tt',
          'countryname_text' => 'Trinidad und Tobago',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'tv',
          'countryname_text' => 'Tuvalu',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'tw',
          'countryname_text' => 'Taiwan',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'tz',
          'countryname_text' => 'Tansania',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ua',
          'countryname_text' => 'Ukraine',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ug',
          'countryname_text' => 'Uganda',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'uk',
          'countryname_text' => 'Vereinigtes Königreich',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'um',
          'countryname_text' => 'USA Minor Outlying Islands',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'us',
          'countryname_text' => 'Vereinigte Staaten',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'uy',
          'countryname_text' => 'Uruguay',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'uz',
          'countryname_text' => 'Usbekistan',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'va',
          'countryname_text' => 'Vatikan',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'vc',
          'countryname_text' => 'St. Vincent & Grenadinen',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 've',
          'countryname_text' => 'Venezuela',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'vg',
          'countryname_text' => 'Jungferninseln (brit.
        )',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'vi',
          'countryname_text' => 'Jungferninseln (US
        )',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'vn',
          'countryname_text' => 'Vietnam',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'vu',
          'countryname_text' => 'Vanuatu',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'wf',
          'countryname_text' => 'Wallis und Futuna',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ws',
          'countryname_text' => 'Samoa',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'ye',
          'countryname_text' => 'Jemen',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'yt',
          'countryname_text' => 'Mayotte',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'rs',
          'countryname_text' => 'Serbien',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'me',
          'countryname_text' => 'Montenegro',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'za',
          'countryname_text' => 'Südafrika',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'zm',
          'countryname_text' => 'Sambia',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'zr',
          'countryname_text' => 'Zaire',
          'countryname_lang' => $german
        ),
        array(
          'countryname_countryid' => 'zw',
          'countryname_text' => 'Simbabwe',
          'countryname_lang' => $german

        )

        );
      $this->databaseInsertRecords($this->tableCountryNames, $countriesGerman);
    }
    // Insert states (for now, only USA and Germany)
    // It's a lot easier because states have only got local names
    $statesGermany = array(
      'BW' => 'Baden-Württemberg',
      'BY' => 'Bayern',
      'BL' => 'Berlin',
      'BD' => 'Brandenburg',
      'HB' => 'Bremen',
      'HH' => 'Hamburg',
      'HE' => 'Hessen',
      'MV' => 'Mecklenburg-Vorpommern',
      'NN' => 'Niedersachsen',
      'NW' => 'Nordrhein-Westfalen',
      'RP' => 'Rheinland-Pfalz',
      'SL' => 'Saarland',
      'SN' => 'Sachsen',
      'ST' => 'Sachsen-Anhalt',
      'SH' => 'Schleswig-Holstein',
      'TH' => 'Thüringen'
    );
    foreach ($statesGermany as $abbr => $name) {
      $data = array(
        'state_id' => $abbr,
        'state_name' => $name,
        'state_country' => 'de'
       );
      $this->databaseInsertRecord($this->tableStates, NULL, $data);
    }
    $statesUSA = array(
      'AL' => 'Alabama',
      'AK' => 'Alaska',
      'AZ' => 'Arizona',
      'AR' => 'Arkansas',
      'CA' => 'California',
      'CO' => 'Colorado',
      'CT' => 'Connecticut',
      'DE' => 'Delaware',
      'FL' => 'Florida',
      'GA' => 'Georgia',
      'HI' => 'Hawaii',
      'ID' => 'Idaho',
      'IL' => 'Illinois',
      'IN' => 'Indiana',
      'IA' => 'Iowa',
      'KS' => 'Kansas',
      'KY' => 'Kentucky',
      'LA' => 'Louisiana',
      'ME' => 'Maine',
      'MD' => 'Maryland',
      'MA' => 'Massachusetts',
      'MI' => 'Michigan',
      'MN' => 'Minnesota',
      'MS' => 'Mississippi',
      'MO' => 'Missouri',
      'MT' => 'Montana',
      'NE' => 'Nebraska',
      'NV' => 'Nevada',
      'NH' => 'New Hampshire',
      'NJ' => 'New Jersey',
      'NM' => 'New Mexico',
      'NY' => 'New York',
      'NC' => 'North Carolina',
      'ND' => 'North Dakota',
      'OH' => 'Ohio',
      'OK' => 'Oklahoma',
      'OR' => 'Oregon',
      'PA' => 'Pennsylvania',
      'RI' => 'Rhode Island',
      'SC' => 'South Carolina',
      'SD' => 'South Dakota',
      'TN' => 'Tennessee',
      'TX' => 'Texas',
      'UT' => 'Utah',
      'VT' => 'Vermont',
      'VA' => 'Virginia',
      'WA' => 'Washington',
      'WV' => 'West Virginia',
      'WI' => 'Wisconsin',
      'WY' => 'Wyoming',
      'DC' => 'District of Columbia'
    );
    foreach ($statesUSA as $abbr => $name) {
      $data = array(
        'state_id' => $abbr,
        'state_name' => $name,
        'state_country' => 'us'
      );
      $this->databaseInsertRecord($this->tableStates, NULL, $data);
    }
  }

  /**
  * Search country id by language dependend name
  *
  * @param  string $countryName
  * @param  int    $lngId         language id (optional)
  * @return string
  */
  function searchCountryIdByName($countryName, $lngId = NULL) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_searchstringparser.php');
    $searchStringParser = new searchStringParser;

    if ($lngId > 0) {
      // search in language dependend country names
      $filter = $searchStringParser->getSQL($countryName, array('cn.countryname_text'));
      $sql = "SELECT cn.countryname_id
                FROM %2\$s cn
               WHERE cn.countryname_lang = '%3\$d'
                 AND ".$filter;

    } else {
      // search in fallback name
      $filter = $searchStringParser->getSQL($countryName, array('country_fallbackname'));
      $sql = "SELECT country_id
                FROM %1\$s
               WHERE ".$filter;
    }

    $sqlParams = array(
      $this->tableCountries,
      $this->tableCountryNames,
      $lngId
    );

    // return result
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      return $res->fetchField();
    }
    return FALSE;
  }
}
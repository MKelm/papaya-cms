<?php
/**
* Domain data connector
*
* Load domain specific values for other modules
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
* @subpackage Free-Domains
* @version $Id: connector_domains.php 37354 2012-08-03 14:22:06Z hapke $
*/

/**
* incusion of base or additional libraries
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');

/**
* Domain data connector
*
* Loads domain specific values for other modules
*
* @package Papaya-Modules
* @subpackage Free-Domains
*/
class connector_domains extends base_db {

  private $_domainObject = NULL;


  /***************************************************************************/
  /** Methods                                                                */
  /***************************************************************************/

  /**
  * load the values from the database, returns default values for empty ones
  *
  * @param array $fields name => identifier array
  * @param array $values name => defaultValue array
  * @access public
  * @return array
  */
  function readValues($fields, $values) {
    if (is_array($fields)) {
      $data = $this->loadValues(array_values($fields));
      $map = array_flip($fields);
      foreach ($data as $name => $value) {
        if (isset($map[$name])) {
          $values[$map[$name]] = $value;
        }
      }
    }
    return $values;
  }

  /**
  * return domain specific values for an identifier or a list of identifiers
  *
  * @param string | array $identifiers
  * @param boolean $reset optional, reset all values before loading
  * @access public
  * @return array identifier => value array
  */
  function loadValues($identifiers, $reset = FALSE) {
    static $values;
    if ($reset || !isset($values)) {
      $values = array();
    }
    if ($domainId = $this->getCurrentDomainId()) {
      if (is_array($identifiers)) {
        $ids = array();
        //build a list of identifiers for the database filter
        foreach ($identifiers as $identifier) {
          if (!isset($values[$identifier])) {
            $ids[] = $identifier;
          }
        }
      } elseif (isset($values[$identifiers])) {
        //single value, return it
        return array(
          $identifiers => $values[$identifiers]
        );
      } else {
        //single identifier - convert to list
        $ids = array($identifiers);
      }
      if (count($ids) > 0) {
        $filter = $this->databaseGetSQLCondition('f.field_ident', $ids);
        $sql = "SELECT f.field_ident, v.field_value
                  FROM %s as f, %s as v
                 WHERE v.field_id = f.field_id
                   AND v.domain_id = '%d'
                   AND $filter";
        $params = array(
          PAPAYA_DB_TABLEPREFIX.'_domain_fields',
          PAPAYA_DB_TABLEPREFIX.'_domain_values',
          $domainId
        );
        if ($res = $this->databaseQueryFmt($sql, $params)) {
          while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $values[$row['field_ident']] = $row['field_value'];
          }
        }
      }
      if (is_array($identifiers)) {
        //return all loaded or cached domain specific values
        $result = array();
        foreach ($identifiers as $identifier) {
          if (isset($values[$identifier])) {
            $result[$identifier] = $values[$identifier];
          }
        }
        return $result;
      } elseif (isset($values[$identifiers])) {
        //single value, return it
        return array(
          $identifiers => $values[$identifiers]
        );
      }
    }

    return array();
  }

  /**
  * get the current domain id from the domains object in the papaya page object
  *
  * @access public
  * @return integer | FALSE
  */
  function getCurrentDomainId() {
    static $domainId;
    if (!isset($domainId)) {
      $domainId == FALSE;
      if (isset($GLOBALS['PAPAYA_PAGE']) &&
          ($id = $GLOBALS['PAPAYA_PAGE']->getCurrentDomainId())) {
        $domainId = $id;
      }
    }
    return $domainId;
  }

  /**
  * get a select (combo) box with all field identifiers
  *
  * @param string $name
  * @param string $data
  * @param boolean $allowEmpty allow an "empty option"
  * @access public
  * @return string
  */
  function getIdentifierCombo($name, $data, $allowEmpty = FALSE) {
    $result = '';
    $result .= sprintf(
      '<select name="%s" class="dialogSelect dialogScale">'.LF,
      papaya_strings::escapeHTMLChars($name)
    );
    $identifiers = $this->getIdentifierList();
    if (is_array($identifiers) && count($identifiers) > 0) {
      if ($allowEmpty) {
        $selected = (empty($data)) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value=""%s>%s</option>'.LF,
          $selected,
          papaya_strings::escapeHTMLChars($this->_gt('None'))
        );
      }
      foreach ($identifiers as $identifier) {
        $selected = (strtolower($data) == strtolower($identifier))
          ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%s"%s>%s</option>'.LF,
          papaya_strings::escapeHTMLChars($identifier),
          $selected,
          papaya_strings::escapeHTMLChars($identifier)
        );
      }
    }
    $result .= '</select>'.LF;
    return $result;
  }

  /**
  * get a list of all field identifiers (for select boxes in backend)
  *
  * @access public
  * @return array
  */
  function getIdentifierList() {
    $result = array();
    $sql = "SELECT field_ident
              FROM %s
             ORDER BY field_ident";
    $tableName = PAPAYA_DB_TABLEPREFIX.'_domain_fields';
    if ($res = $this->databaseQueryFmt($sql, $tableName)) {
      while ($row = $res->fetchRow()) {
        $result[] = $row[0];
      }
    }
    return $result;
  }

  /**
  * Get list of registered domains
  *
  * @return array
  */
  public function getDomainList() {
    return $this->getDomainObject()->loadDomainList();
  }

  /**
  * Returns a list of domain property translations
  * @return array
  */
  public function getDomainPropertyTranslations() {
    $modeDesc = array(
      PAPAYA_DOMAIN_MODE_DEFAULT => 'PAPAYA_DOMAIN_MODE_DEFAULT',
      PAPAYA_DOMAIN_MODE_PAGE => 'PAPAYA_DOMAIN_MODE_PAGE',
      PAPAYA_DOMAIN_MODE_LANG => 'PAPAYA_DOMAIN_MODE_LANG',
      PAPAYA_DOMAIN_MODE_DOMAIN => 'PAPAYA_DOMAIN_MODE_DOMAIN',
      PAPAYA_DOMAIN_MODE_TREE => 'PAPAYA_DOMAIN_MODE_TREE'
    );
    $modeImgs = array(
      PAPAYA_DOMAIN_MODE_DEFAULT => 'items-page',        // page symbol
      PAPAYA_DOMAIN_MODE_PAGE => 'items-alias',       // alias symbol
      PAPAYA_DOMAIN_MODE_LANG => 'items-translation', // globe
      PAPAYA_DOMAIN_MODE_DOMAIN => 'items-link',        // link symbol
      PAPAYA_DOMAIN_MODE_TREE => 'categories-sitemap' // tree symbol
    );
    $protDesc = array(
      1 => 'http://',
      2 => 'https://'
    );

    return array($modeDesc, $modeImgs, $protDesc);
  }

  /**
  * Creates a new domain entry in database.
  *
  * @param array $domainData
  * @access public
  * @return integer|FALSE $newId
  */
  function insertDomain($domainData) {
    return $this->databaseInsertRecord(PAPAYA_DB_TABLEPREFIX.'_domains', 'domain_id', $domainData);
  }

  /**
  * Check if a given domain name exists.
  *
  * @param array $domainData
  * @access public
  * @return integer|FALSE $newId
  */
  function getIdByHostname($hostname) {
    $result = NULL;
    $sql = "SELECT domain_id
              FROM %s
             WHERE domain_hostname = '%s'";
    if ($res = $this->databaseQueryFmt($sql, array(PAPAYA_DB_TABLEPREFIX.'_domains', $hostname))) {
      if ($row = $res->fetchRow()) {
        $result = $row[0];
      }
    }
    return $result;
  }

  /***************************************************************************/
  /** Helper                                                                 */
  /***************************************************************************/

  /**
  * Instantiate a papaya_domains object if not already existing.
  * @return papaya_domains
  */
  protected function getDomainObject() {
    if (!(isset($this->_domainObject) && is_object($this->_domainObject))) {
      include_once(dirname(__FILE__).'/papaya_domains.php');
      $this->_domainObject = new papaya_domains();
    }
    return $this->_domainObject;
  }

  /**
  * Set the papaya_domains object to be used instead of the original one.
  * @param papaya_domains $domain
  */
  protected function setDomainObject(papaya_domains $domain) {
    $this->_domainObject = $domain;
  }
}

?>
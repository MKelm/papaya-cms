<?php
/**
* Domain Administration
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
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
* @version $Id: papaya_domains.php 37654 2012-11-08 16:20:49Z weinert $
*/

/**
* base domains class (superclass)
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_domains.php');
/**
* papaya options class
*/
require_once(PAPAYA_INCLUDE_PATH.'system/papaya_options.php');
/**
* xml abstraction class
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_simple_xmltree.php');


/**
* Papaya Domains Administration Backend
*
* This module allows an administrator to manage
* papaya-domain-forewarders using the backend.
*
* @package Papaya-Modules
* @subpackage Free-Domains
*/
class papaya_domains extends base_domains {

  /**
  * view modes table
  * @var string
  */
  var $tableViewModes = PAPAYA_DB_TBL_VIEWMODES;
  /**
  * default display limit
  * @var integer
  */
  var $defaultLimit = 20;
  /**
  * absolute domain record count
  * @var integer
  */
  var $absCount = 0;
  /**
  * dialog parameter name
  * @var string
  */
  var $paramName = 'dom';
  /**
  * dialog field size
  * @var string
  */
  var $inputFieldSize = 'large';
  /**
  * domain dialog object
  * @var base_dialog
  */
  var $domainDialog = NULL;
  /**
  * delete domain dialog object
  * @var base_msgdialog
  */
  var $deleteDomainDialog = NULL;
  /**
  * delete domain option dialog object
  * @var base_msgdialog
  */
  var $deleteOptionDialog = NULL;
  /**
  * current domain record
  * @var array
  */
  var $loadedDomain = array();
  /**
  * domain record list
  * @var array
  */
  var $domains = array();
  /**
  * content languages
  * @var array
  */
  var $languages = NULL;
  /**
  * viewmode extensions
  * @var array
  */
  var $knownExtensions = array();
  /**
  * domain specific option values
  * @var array
  */
  var $optionValues = array();
  /**
  * linked options
  * @var array
  */
  var $optionLinks = array();

  /**
  * Options are organized into groups.
  * @var array $optionGroups
  */
  var $optionGroups = array();

  /**
  * table for field definitions for domain dependent values
  * @var string $tableDomainFields
  */
  var $tableDomainFields;

  /**
  * table for domain dependent values
  * @var string $tableDomainValues
  */
  var $tableDomainValues;

  /**
  * edit domains (0) or fields (1)?
  * @var integer $categoryMode
  */
  var $categoryMode = 0;

  /**
  * list of defined domain dependent fields
  * @var array
  */
  var $fields = array();

  /**
  * list of domain values for the current domain
  * @var array
  */
  var $fieldValues = array();

  /**
  * generic editfields for domain records
  * @var array
  */
  var $domainPropertiesFields = array(
    'domain_hostname' => array('Hostname', 'isNoHTML', TRUE, 'input', 400),
    'domain_protocol' => array('Domain protocol', 'isNum', TRUE, 'combo',
      array(
        0 => 'both',
        1 => 'http',
        2 => 'https'
      )
    ),
    'domain_mode' => array('Domain mode', 'isNum', TRUE, 'combo',
      array(
        PAPAYA_DOMAIN_MODE_DEFAULT => 'default',
        PAPAYA_DOMAIN_MODE_DOMAIN => 'domain',
        PAPAYA_DOMAIN_MODE_PAGE => 'page',
        PAPAYA_DOMAIN_MODE_LANG => 'language',
        PAPAYA_DOMAIN_MODE_TREE => 'tree'
      )
    )
  );

  /**
  * call the inherited constructor and define additional database tables
  *
  * @access public
  */
  function __construct() {
    parent::__construct();
    $this->tableDomainFields = PAPAYA_DB_TABLEPREFIX.'_domain_fields';
    $this->tableDomainValues = PAPAYA_DB_TABLEPREFIX.'_domain_values';
  }

  /**
  * Get current combined URI into form.
  * Currently, this is just a wrapper to get plain text into the form.
  *
  * @return string xml
  */
  function getDomainDataString($name, $field, $data) {
    return $data;
  }

  /**
  * Form callback function to create a drop down with all known extensions.
  *
  * @return array|FALSE
  */
  function getExtensionsCombo($name, $field, $data) {
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">',
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars('target_extension')
    );

    if (isset($this->knownExtensions) && is_array($this->knownExtensions)) {
      foreach ($this->knownExtensions as $extension) {
        $selected = ($extension == $data) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%s"%s>%s</option>',
          papaya_strings::escapeHTMLChars($extension),
          $selected,
          papaya_strings::escapeHTMLChars($extension)
        );
      }
    }
    $result .= '</select>';
    return $result;
  }

  /**
  * Form callback function to create drop down menu to select one of the languages.
  *
  * @return string xml
  */
  function getDomainLanguageCombo($name, $field, $data) {
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">',
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars('target_language')
    );

    if (isset($this->languages) && is_array($this->languages)) {
      $result .= sprintf(
        '<option value="0">[%s]</option>',
        papaya_strings::escapeHTMLChars($this->_gt('Please select'))
      );
      foreach ($this->languages as $key => $language) {
        $selected = ($key == $data) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%s" %s>%s</option>',
          papaya_strings::escapeHTMLChars($key),
          $selected,
          papaya_strings::escapeHTMLChars($language)
        );
      }
    }
    $result .= '</select>';
    return $result;
  }

  /**
   * Load all sets for the current theme. Generate a select box to define one.
   *
   * @param string $name
   * @param array $element
   * @param string $data
   * @return string
   */
  function getThemeSetsCombo($name, $element, $data) {
    $themeSets = new PapayaContentThemeSets();
    $themeSets->load(
      array('theme_name' => $this->papaya()->options()->get('PAPAYA_LAYOUT_THEME'))
    );
    $result = '';
    $result .= sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name)
    );
    $result .= sprintf(
      '<option value="">%s</option>'.LF,
      new PapayaUiStringTranslated('None')
    );
    $current = $this->papaya()->options()->get('PAPAYA_LAYOUT_THEME_SET', '');
    foreach ($themeSets as $themeSet) {
      $selected = ($current == $data) ? ' selected="selected"' : '';
      $result .= sprintf(
        '<option value="%d - %s"%s>%s</option>'.LF,
        papaya_strings::escapeHTMLChars($themeSet['id']),
        papaya_strings::escapeHTMLChars($themeSet['title']),
        $selected,
        papaya_strings::escapeHTMLChars($themeSet['title'])
      );
    }
    $result .= '</select>'.LF;
    return $result;
  }


  /**
  * initialize -
  * Initializes this modules session as well as other required variables.
  *
  * @access public
  */
  function initialize() {
    $this->sessionParamName = 'PAPAYA_SESS_domains'.$this->paramName;
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam('mode');
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
    $this->params['offset_domains'] = 0;
    $this->loadLanguages();
    $this->loadExtensions();
  }


  /**
  * loads all fields of a domain-database record.
  * This method will split domain_data depending on the current domain_mode setting
  * of the record identified with $domainId.
  *
  * @return array|FALSE
  */
  function loadDetails($domainId) {
    $sql = "SELECT domain_id, domain_hostname, domain_hostlength,
                   domain_protocol, domain_mode,
                   domain_data, domain_options
              FROM %s
             WHERE domain_id = %d";
    $params = array($this->tableDomains, (int)$domainId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $snippets = array();
        // Depending on domain_mode, domain_data is devided into smaller parts.
        switch ($row['domain_mode']) {
        case PAPAYA_DOMAIN_MODE_DOMAIN:
          // http://www.ahost.de
          $domainPattern = '~(?:(https?)://)?((?:[\w|\-]+\.)+(?:\w+))~ix';
          if (preg_match($domainPattern, $row['domain_data'], $snippets)) {
            $row['target_protocol'] = $snippets[1];
            $row['target_hostname'] = $snippets[2];
          }
          break;
        case PAPAYA_DOMAIN_MODE_PAGE:
          // http://www.ahost.de/page.101.de.html
          // www.ahost.de/page.101.html
          $pagePattern = '~^(?:(https?)://)?((?:[\w|\-]+\.)+(?:\w+))(/.*)~ix';
          if (preg_match($pagePattern, $row['domain_data'], $snippets)) {
            $uriDecoded = $this->parseRequestURI($snippets[3]);
            $row['target_protocol'] = $snippets[1];
            $row['target_hostname'] = $snippets[2];
            $row['target_page'] = $uriDecoded['page_id'];
            $row['target_language'] = $uriDecoded['language'];
            $row['target_name'] = $uriDecoded['filename'];
            $row['target_extension'] = $uriDecoded['ext'];
          }
          break;
        case PAPAYA_DOMAIN_MODE_LANG:
          // /index.de.html
          if (preg_match('/^\/[\w|\-]+\.(\w+)\.\w+$/', $row['domain_data'], $snippets)) {
            $row['target_language'] = $snippets[1];
          }
          break;
        case PAPAYA_DOMAIN_MODE_TREE:
          // 13
          $row['target_page'] = $row['domain_data'];
          $this->unserializeDomainOptions($row['domain_options']);
          $this->prepareOptions();
          break;
        }
        return $row;
      }
    }
    return array();
  }

  /**
  * Derived from papaya_options to make options possible.
  * @return boolean
  */
  function prepareOptions() {
    $this->loadOptionGroups();

    unset($this->optionLinks);
    $this->optionLinks = array();

    foreach (papaya_options::$optFields as $optName => $optParams) {
      if (isset($this->domainOptions[$optName])) {
        if (isset($optParams[0])) {
          $group = (int)$optParams[0];
        } else {
          $group = 0;
        }
        $this->optionLinks[$group][] = $optName;
      }
    }
    return TRUE;
  }

  /**
  * Check special option
  *
  * @param string $option
  * @param integer $value
  * @return boolean
  */
  function checkOptionSpecial($option, $value) {
    switch ($option) {
    case 'PAPAYA_UI_HTTPS_ONLY' :
      if (0 != (int)$value) {
        if (!PapayaUtilServerProtocol::isSecure()) {
          $this->addMsg(MSG_ERROR, $this->_gt('You need HTTPS to use this feature.'));
          return FALSE;
        }
      }
      break;
    case 'PAPAYA_LOGIN_RESTRICTION' :
      include_once(PAPAYA_INCLUDE_PATH.'system/base_auth_secure.php');
      $authSec = new base_auth_secure();
      $ipStatus = $authSec->getIpStatus($_SERVER['REMOTE_ADDR']);
      if ($value == 3 && $ipStatus != 1) {
        $this->addMsg(MSG_ERROR, $this->_gt('Your IP is not in whitelist.'));
        return FALSE;
      }
      break;
    }
    return TRUE;
  }

  /**
  * loadOptionGroups - This method loads all known option groups into memory.
  * These are the same for domains as those used to setup papaya globally.
  *
  * @return boolean
  */
  function loadOptionGroups() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_statictables.php');
    foreach (base_statictables::getTableOptGroups() as $key => $val) {
      $this->optionGroups[$key] = $this->_gt($val);
    }
    return TRUE;
  }

  /**
  * saveDetails - saves all fields of a domain-database record.
  * This method will join target values into domain_data depending on the
  * current domain_mode setting of the record.
  *
  * @access public
  * @return array|FALSE
  */
  function saveDetails($fields) {
    $result = '';

    switch ($fields['domain_mode']) {
    case PAPAYA_DOMAIN_MODE_DOMAIN:
      if (isset($fields['target_protocol']) && $fields['target_protocol'] != '') {
        $result .= $fields['target_protocol'].'://';
      }
      if (isset($fields['target_hostname']) && $fields['target_hostname'] != '') {
        $result .= $fields['target_hostname'].'/';
      }
      break;
    case PAPAYA_DOMAIN_MODE_PAGE:
      $result = '';
      if (isset($fields['target_protocol']) && $fields['target_protocol'] != '') {
        $result .= $fields['target_protocol'].'://';
      }

      if (isset($fields['target_hostname']) && $fields['target_hostname']) {
        $result .= $fields['target_hostname'].'/';
      }

      if (isset($fields['target_name']) && $fields['target_name'] != '') {
        $result .= $fields['target_name'].'.';
      } else {
        $result .= 'page.';
      }
      if (isset($fields['target_page']) && (int)$fields['target_page'] > 0) {
        $result .= (empty($fields['target_page']) ? 0 : (int)$fields['target_page']).'.';
      }
      if ($fields['target_language'] && $fields['target_language'] != '') {
        $result .= $fields['target_language'].'.';
      }
      if (isset($fields['target_extension']) && $fields['target_extension'] != '') {
        $result .= $fields['target_extension'];
      }
      break;
    case PAPAYA_DOMAIN_MODE_LANG:
      $result = '/index'.
        (!empty($fields['target_language']) ? '.'.$fields['target_language'] : '').
        '.html';
      break;
    case PAPAYA_DOMAIN_MODE_TREE:
      $result = empty($fields['target_page']) ? '0' : $fields['target_page'];
    default:
      break;
    }

    // Check if there are more than one domains with this name and protocol.
    // In this case, return with FALSE.
    if (!(empty($fields['domain_hostname']) || empty($fields['domain_protocol']))) {
      $sql = "SELECT domain_id
                FROM %s
               WHERE domain_hostname = '%s'
                 AND (domain_protocol = '%s' OR domain_protocol = 0)";
      $params = array(
        $this->tableDomains,
        $fields['domain_hostname'],
        $fields['domain_protocol']
      );
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow()) {
          if ($row[0] != $fields['domain_id']) {
            $this->addMsg(
              MSG_ERROR,
              sprintf(
                $this->_gt('There allready is a domain "%s" defined for this protocol.'),
                $fields['domain_hostname']
              )
            );
            return FALSE;
          }
        }
      }
    }

    // When everything seems okay, store it.
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableDomains,
      array(
        'domain_hostname' => $fields['domain_hostname'],
        'domain_hostlength' => strlen($fields['domain_hostname']),
        'domain_protocol' => empty($fields['domain_protocol']) ? '' : $fields['domain_protocol'],
        'domain_mode' => empty($fields['domain_mode']) ? '' : $fields['domain_mode'],
        'domain_options' => empty($fields['domain_options']) ? '' : $fields['domain_options'],
        'domain_data' => $result
      ),
      'domain_id',
      (int)$fields['domain_id']
    );
  }

  /**
  * save field data to database
  *
  * @param array $values
  * @param integer $fieldId optional, default value 0
  * @access public
  * @return boolean
  */
  function saveField($values, $fieldId = 0) {
    $fieldIdent = strtoupper($values['field_ident']);
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE field_id = '%d'
                OR field_ident = '%s'";
    $params = array(
      $this->tableDomainFields,
      $fieldId,
      $fieldIdent
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $recordCount = $res->fetchField();
      if ($recordCount > 1 || ($fieldId == 0 && $recordCount > 0)) {
        $this->addMsg(MSG_ERROR, $this->_gt('Identifier has to be unique.'));
      } else {
        $data = array(
          'field_ident' => $fieldIdent,
          'field_description' => (string)$values['field_description'],
          'field_type' => $values['field_type'],
          'field_check' => $values['field_check']
        );
        if (isset($values['field_params'])) {
          $data['field_params'] = $values['field_params'];
        }
        if ($fieldId > 0) {
          $filter = array(
            'field_id' => $fieldId
          );
          return FALSE !== $this->databaseUpdateRecord($this->tableDomainFields, $data, $filter);
        } else {
          return $this->databaseInsertRecord($this->tableDomainFields, 'field_id', $data);
        }
      }
    }
    return FALSE;
  }

  /**
  * This function writes the field value for a given domain to the database.
  *
  * @param integer $domainId Id of the domain for which the field value is to be saved.
  * @param integer$fieldId Id of the field that defines the type of the field.
  * @param string $value field value to be written to the database
  * @return boolean FALSE if operation fails, otherwise TRUE
  */
  function saveValue($domainId, $fieldId, $value) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE domain_id = '%d'
               AND field_id = '%d'";
    $params = array(
      $this->tableDomainValues,
      $domainId,
      $fieldId
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $valueCount = $res->fetchField();
      if ($valueCount > 0 && empty($value)) {
        //delete
        $filter = array(
          'domain_id' => $domainId,
          'field_id' => $fieldId
        );
        return FALSE !== $this->databaseDeleteRecord($this->tableDomainValues, $filter);
      } elseif ($valueCount > 0) {
        //update
        $filter = array(
          'domain_id' => $domainId,
          'field_id' => $fieldId
        );
        $data = array(
          'field_value' => $value
        );
        return FALSE !== $this->databaseUpdateRecord($this->tableDomainValues, $data, $filter);
      } elseif (!empty($value)) {
        //insert
        $data = array(
          'domain_id' => $domainId,
          'field_id' => $fieldId,
          'field_value' => $value
        );
        return FALSE !== $this->databaseInsertRecord($this->tableDomainValues, NULL, $data);
      }
    }
    return FALSE;
  }

  /**
  * Loads a list of domain overviews into memory.
  *
  * @access public
  */
  function loadDomainList() {
    $this->domains = array();
    $sql = "SELECT domain_id, domain_hostname, domain_protocol, domain_mode
              FROM %s";
    $params = array($this->tableDomains);
    $this->domains = array();
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->domains[$row['domain_id']] = $row;
      }
    }
    if (is_array($this->domains) && count($this->domains) > 1) {
      uasort($this->domains, array(&$this, 'compareDomainRecords'));
    }

    return $this->domains;
  }

  /**
  * compare the host names by structur - first domain.tld,
  * followed by subdomains in ascending level order
  *
  * @param array $item1
  * @param array $item2
  * @access public
  * @return integer
  */
  function compareDomainRecords($item1, $item2) {
    $host1 = array_reverse(explode('.', $item1['domain_hostname']));
    $host2 = array_reverse(explode('.', $item2['domain_hostname']));
    $max1 = count($host1);
    $max2 = count($host2);
    $max = ($max1 < $max2) ? $max1 : $max2;
    if ($max1 > 1 && $max2 > 1 && $host1[1] != $host2[1]) {
      if ($host1[1] == '*') {
        return 1;
      } elseif ($host2[1] == '*') {
        return -1;
      } else {
        return strnatcasecmp($host1[1], $host2[1]);
      }
    }
    for ($i = 0; $i < $max; $i++) {
      if ($host1[$i] != $host2[$i]) {
        if ($host1[$i] == '*') {
          return 1;
        } elseif ($host2[$i] == '*') {
          return -1;
        } else {
          return strnatcasecmp($host1[$i], $host2[$i]);
        }
      }
    }
    if ($max1 > $max2) {
      return -1;
    } elseif ($max1 < $max2) {
      return 1;
    }
    return 0;
  }

  /**
  * load a list of the defined domain dependent fields
  *
  * @access public
  * @return void
  */
  function loadFieldList() {
    $this->fields = array();
    $sql = "SELECT field_id, field_ident
              FROM %s
             ORDER BY field_ident";
    $params = array($this->tableDomainFields);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->fields[$row['field_id']] = $row;
      }
    }
  }

  /**
  * load details for a field from database
  *
  * @param integer $fieldId
  * @access public
  * @return array
  */
  function loadFieldDetails($fieldId) {
    $sql = "SELECT field_id, field_ident, field_description,
                   field_type, field_params, field_check
              FROM %s
             WHERE field_id = '%d'";
    $params = array(
      $this->tableDomainFields,
      $fieldId
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        return $row;
      }
    }
    return FALSE;
  }

  /**
  * Load the fields and the values for the current domain from database.
  * Fields without a value will be loaded, too.
  *
  * @param integer $domainId
  * @access public
  * @return void
  */
  function loadValuesList($domainId) {
    $this->fieldValues = array();
    $sql = "SELECT f.field_id, f.field_ident, v.field_value
              FROM %s AS f
              LEFT JOIN %s AS v ON (v.field_id = f.field_id AND v.domain_id = '%d')
             ORDER BY f.field_ident";
    $params = array(
      $this->tableDomainFields,
      $this->tableDomainValues,
      $domainId
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->fieldValues[$row['field_id']] = $row;
      }
    }
  }

  /**
  * Loads a list of all currently known languages.
  */
  function loadLanguages() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_language_select.php');
    $lngSelect = &base_language_select::getInstance();
    unset($this->languages);
    $this->languages = array();
    foreach ($lngSelect->languages as $lng) {
      $this->languages[$lng['lng_ident']] = $lng['lng_title'];
    }
  }

  /**
  * executes domain managment editing commands
  *
  * @access public
  */
  function execute() {
    $fieldCommands = array(
      'show_fields', 'edit_field', 'add_field', 'delete_field'
    );
    if (!isset($this->params['cmd'])) {
      $this->params['cmd'] = '';
      $this->categoryMode = 0;
      $this->loadData();
      $this->initializeOptionDialog();
    } elseif (in_array($this->params['cmd'], $fieldCommands)) {
      $this->categoryMode = 1;
      $this->loadFieldList();
    } else {
      $this->categoryMode = 0;
      $this->loadData();
    }
    switch ($this->params['cmd']) {
    case 'open_option':
      // Used only when a domain is in tree mode.
      $this->sessionParams['opened'][$this->params['gid']] = TRUE;
      break;
    case 'close_option':
      unset($this->sessionParams['opened'][$this->params['gid']]);
      break;
    case 'edit_option':
      $this->initializeOptionDialog();
      if ($this->optionDialog->checkDialogInput() &&
          isset($this->params['id']) &&
          $this->checkOptionSpecial($this->params['id'], $this->params[$this->params['id']])) {
        if ($this->saveOption()) {
          $this->addMsg(MSG_INFO, $this->_gt('Option modified.'));
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('Cannot change option.'));
        }
      }
      break;
    case 'test' :
      if (!empty($this->params['test_domain'])) {
        if (isset($this->params['test_protocol']) &&
            in_array($this->params['test_protocol'], array(1,2))) {
          $protocol = $this->params['test_protocol'];
        } else {
          $protocol = NULL;
        }
        $data = $this->checkDomain($this->params['test_domain'], $protocol);
        if ($data) {
          $this->addMsg(
            MSG_INFO,
            sprintf($this->_gt('Match found for "%s".'), $this->params['test_domain'])
          );
          $this->params['domain_id'] = $data['domain_id'];
          $this->params['cmd'] = 'edit_domain';
          $this->loadData();
        } else {
          $this->addMsg(MSG_INFO, $this->_gt('No match found.'));
        }
      }
    case 'test_pageid_link' :
      $this->layout->add($this->getPageIdTestDialog());
      break;
    case 'add_domain':
    case 'edit_domain':
      $this->params['mode'] = 0;
      $this->initializeDomainPropertiesDialog($this->loadedDomain);
      if ($this->domainDialog->modified()) {
        if ($this->domainDialog->checkDialogInput()) {
          if (empty($this->domainDialog->data['domain_id'])) {
            if ($this->params['save'] == '1') {
              $this->params['domain_id'] = $this->insertDomain($this->domainDialog->data);
              if ($this->params['domain_id'] !== FALSE) {
                unset($this->loadedDomain);
                $this->loadData();
                unset($this->domainDialog);
                $this->initializeDomainPropertiesDialog($this->loadedDomain);
                $this->addMsg(
                  MSG_INFO,
                  sprintf(
                    $this->_gt('Domain "%s" (%d) has been created.'),
                    $this->loadedDomain['domain_hostname'],
                    $this->loadedDomain['domain_id']
                  )
                );
              } else {
                $this->addMsg(
                  MSG_ERROR,
                  $this->_gt("Database error! Cannot create domain!")
                );
              }
            }
          } else {
            if (isset($this->params['save']) && $this->params['save'] == '1') {
              if ($this->saveDetails($this->domainDialog->data) !== FALSE) {
                $this->loadedDomain = array();
                $this->loadData();
                unset($this->domainDialog);
                $this->initializeDomainPropertiesDialog($this->loadedDomain);
                $this->addMsg(
                  MSG_INFO,
                  sprintf(
                    $this->_gt('Domain "%s" (%d) has been saved.'),
                    $this->loadedDomain['domain_hostname'],
                    $this->loadedDomain['domain_id']
                  )
                );
              } else {
                $this->addMsg(
                  MSG_ERROR,
                  $this->_gt("Database error! Cannot save domain!")
                );
              }
            }
          }
        }
      }
      break;
    case 'del_domain':
      if (isset($this->params['confirm_delete']) &&
          $this->params['confirm_delete'] &&
          isset($this->params['domain_id'])) {
        if ($this->deleteDomain($this->params['domain_id'])) {
          $this->addMsg(
            MSG_INFO,
            sprintf(
              $this->_gt('Domain "%s" (%d) has been deleted.'),
              $this->loadedDomain['domain_hostname'],
              $this->loadedDomain['domain_id']
            )
          );
          unset($this->params['domain_id']);
          unset($this->loadedDomain);
          unset($this->domainDialog);
          $this->loadData();
        } else {
          $this->addMsg(
            MSG_ERROR,
            sprintf(
              $this->_gt('Database error! Cannot delete domain "%s" (%d).'),
              $this->loadedDomain['domain_hostname'],
              $this->loadedDomain['domain_id']
            )
          );
        }
      } else {
        $this->initializeDeleteDomainDialog();
      }
      break;
    case 'del_option' :
      if (isset($this->params['confirm_delete']) &&
          $this->params['confirm_delete'] &&
          isset($this->loadedDomain) &&
          isset($this->params['id'])) {
        if ($this->deleteDomainOption($this->params['id'])) {
          $this->addMsg(
            MSG_INFO,
            sprintf(
              $this->_gt('Domain option "%s" has been deleted.'),
              $this->params['id']
            )
          );
        }
      } else {
        $this->initializeDeleteOptionDialog();
      }
      break;
    case 'add_field' :
    case 'edit_field' :
      $this->initializeFieldDialog();
      if ($this->fieldDialog->modified('save')) {
        if ($this->fieldDialog->checkDialogInput()) {
          if (isset($this->params['field_id']) && $this->params['field_id'] > 0) {
            if ($this->saveField($this->fieldDialog->data, $this->params['field_id'])) {
              $this->addMsg(MSG_INFO, $this->_gt('Field modified.'));
              unset($this->fieldDialog);
              $this->loadFieldList();
            }
          } else {
            if ($newId = $this->saveField($this->fieldDialog->data)) {
              $this->addMsg(MSG_INFO, $this->_gt('Field added.'));
              $this->params['field_id'] = $newId;
              unset($this->fieldDialog);
              $this->loadFieldList();
            }
          }
        }
      }
      break;
    case 'delete_field' :
      if (isset($this->params['confirm_delete']) &&
          $this->params['confirm_delete'] &&
          isset($this->params['field_id'])) {
        if ($this->deleteField($this->params['field_id'])) {
          $this->addMsg(
            MSG_INFO,
            sprintf(
              $this->_gt('Field "%s" has been deleted.'),
              $this->fields[$this->params['field_id']]['field_ident']
            )
          );
          unset($this->params['field_id']);
          unset($this->fieldDialog);
          $this->loadFieldList();
        }
      } else {
        $this->initializeDeleteFieldDialog();
      }
      break;
    case 'edit_value' :
      if (isset($this->params['domain_id']) &&
          isset($this->params['field_id']) &&
          $this->initializeValueDialog()) {
        if ($this->valueDialog->modified('save')) {
          if ($this->valueDialog->checkDialogInput()) {
            if ($this->saveValue(
                  $this->params['domain_id'],
                  $this->params['field_id'],
                  $this->valueDialog->data['field_value'])) {
              $this->addMsg(MSG_INFO, $this->_gt('Value modified.'));
              $this->loadValuesList($this->params['domain_id']);
            }
          }
        }
      }
      break;
    case 'export_values' :
      if (isset($this->params['domain_id']) &&
          is_array($this->fieldValues) &&
          count($this->fieldValues) > 0) {
        include_once(PAPAYA_INCLUDE_PATH.'system/base_csv.php');
        $csv = &base_csv::getInstance();
        $data = 'field_ident,field_value'."\r\n";
        foreach ($this->fieldValues as $key => $value) {
          $data .= $csv->escapeForCSV($value['field_ident']).',';
          $data .= $csv->escapeForCSV($value['field_value'])."\r\n";
        }
        $csv->outputCSV(
          $data,
          papaya_strings::normalizeString('values_'.$this->loadedDomain['domain_hostname']).'.csv'
        );
      }
      break;
    }
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
  * Deletes a domain entry from database.
  *
  * @param integer $domainId
  * @access public
  * @return boolean
  */
  function deleteDomain($domainId) {
    return (
      FALSE !== $this->databaseDeleteRecord(
        $this->tableDomains, array('domain_id' => $domainId)
      )
    );
  }

  /**
  * Deletes a field entry from database.
  *
  * @param integer $fieldId
  * @access public
  * @return boolean
  */
  function deleteField($fieldId) {
    return (
      FALSE !== $this->databaseDeleteRecord(
        $this->tableDomainFields, array('field_id' => $fieldId)
      )
    );
  }

  /**
  * Creates a new domain entry in database.
  *
  * @param array $domainData
  * @access public
  * @return integer|FALSE $newId
  */
  function insertDomain($domainData) {
    $result = $this->databaseInsertRecord($this->tableDomains, 'domain_id', $domainData);
    return $result;
  }

  /**
  * loadData - Loads all relevant information from sources.
  *
  */
  function loadData() {
    $this->loadDomainList();
    if (isset($this->params['domain_id'])) {
      $this->loadedDomain = $this->loadDetails($this->params['domain_id']);
      if ($this->params['mode'] == 2) {
        $this->loadValuesList($this->params['domain_id']);
      }
    }
  }

  /**
  * Appends new fields to the domain-properties-dialog, depending on the currently
  * selected domain mode of the currently selected domain.
  *
  */
  function initializeDomainPropertiesDialog() {
    if (!(isset($this->domainDialog) && is_object($this->domainDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');

      $hidden = array(
        'cmd' => 'edit_domain',
        'save' => 1,
        'domain_id' =>
          isset($this->loadedDomain['domain_id']) ? $this->loadedDomain['domain_id'] : 0
      );

      $fields = $this->domainPropertiesFields;

      if (isset($this->loadedDomain['domain_mode'])) {
        switch ($this->loadedDomain['domain_mode']) {
        case PAPAYA_DOMAIN_MODE_DOMAIN:
          // In domain mode a domain needs a protocol and a hostname.
          $fields[] = 'Parameters';
          $fields['domain_data'] =
            array('Domain', 'isSomeText', FALSE, 'info', '', '', '', 'left');
          $fields['target_protocol'] = array(
            'Protocol', 'isSomeText', FALSE, 'combo',
            array(
              '' => 'current',
              'http' => 'http',
              'https' => 'https'
            )
          );
          $fields['target_hostname'] = array(
            'Hostname', 'isHTTPHost', TRUE, 'input', 400
          );
          break;
        case PAPAYA_DOMAIN_MODE_PAGE:
          // In page mode a domain needs:
          //   protocol, hostname, pagename, pageId, language and extension.
          $fields[] = 'Parameters';
          $fields['domain_data'] =
            array('URL', 'isSomeText', FALSE, 'info', '', '', '', 'left');
          $fields['target_protocol'] = array(
            'Protocol', 'isSomeText', FALSE, 'combo',
            array(
              '' => 'current',
              'http' => 'http',
              'https' => 'https'
            )
          );
          $fields['target_hostname'] = array('Hostname', 'isHTTPHost', FALSE, 'input', 400);
          $fields['target_name'] = array('Name', 'isSomeText', FALSE, 'input', 400);
          $fields['target_page'] = array('Page', 'isNum', FALSE, 'pageid', 800);
          $fields['target_language'] = array('Language', 'isSomeText', FALSE,
            'function', 'getDomainLanguageCombo');
          $fields['target_extension'] = array('Extension', 'isSomeText', FALSE,
            'function', 'getExtensionsCombo');
          break;
        case PAPAYA_DOMAIN_MODE_LANG:
          // In language mode a domain only needs the language shortcut.
          $fields[] = 'Parameters';
          $fields['target_language'] = array('Language', 'isSomeText', FALSE,
            'function', 'getDomainLanguageCombo');
          break;
        case PAPAYA_DOMAIN_MODE_TREE:
          // In tree mode only the pageId is relevant and nothing else will be stored.
          // Details are found in domain_options in this case.
          $fields[] = 'Parameters';
          $fields['target_page'] = array('Page', 'isNum', FALSE, 'pageid', 800);
          break;
        }
      }

      $this->domainDialog = new base_dialog(
        $this, $this->paramName, $fields, $this->loadedDomain, $hidden
      );
      $this->domainDialog->msgs = &$this->msgs;
      $this->domainDialog->loadParams();
      $this->domainDialog->inputFieldSize = $this->inputFieldSize;
      $this->domainDialog->baseLink = $this->baseLink;
      $this->domainDialog->dialogTitle = $this->_gt('Domain properties');
      $this->domainDialog->dialogDoubleButtons = TRUE;

    }
  }

  /**
  * initialize the confirmation dialog for domain delete
  * Creates a dialog to delete a domain entry.
  */
  function initializeDeleteDomainDialog() {
    if (!(isset($this->deleteDomainDialog) && is_object($this->deleteDomainDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
      $hidden = array(
        'cmd' => 'del_domain',
        'domain_id' => $this->loadedDomain['domain_id'],
        'confirm_delete' => 1,
      );
      $msg = sprintf(
        $this->_gt('Delete domain "%s" (%s)?'),
        $this->loadedDomain['domain_hostname'],
        (int)$this->loadedDomain['domain_id']
      );
      $this->deleteDomainDialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $this->deleteDomainDialog->msgs = &$this->msgs;
      $this->deleteDomainDialog->buttonTitle = 'Delete';
    }
  }

  /**
  * initialize the confirmation dialog for domain option delete
  */
  function initializeDeleteOptionDialog() {
    if (!(isset($this->deleteOptionDialog) && is_object($this->deleteOptionDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
      $hidden = array(
        'cmd' => 'del_option',
        'domain_id' => $this->loadedDomain['domain_id'],
        'id' => $this->params['id'],
        'confirm_delete' => 1,
      );
      $msg = sprintf(
        $this->_gt('Delete option "%s" from domain "%s"?'),
        $this->params['id'],
        $this->loadedDomain['domain_hostname']
      );
      $this->deleteOptionDialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $this->deleteOptionDialog->msgs = &$this->msgs;
      $this->deleteOptionDialog->buttonTitle = 'Delete';
    }
  }

  /**
  * initialize the confirmation dialog for field delete
  * @access public
  */
  function initializeDeleteFieldDialog() {
    if (!(isset($this->deleteFieldDialog) && is_object($this->deleteFieldDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
      $hidden = array(
        'cmd' => 'delete_field',
        'field_id' => $this->params['field_id'],
        'confirm_delete' => 1,
      );
      $msg = sprintf(
        $this->_gt('Delete field "%s"?'),
        $this->fields[$this->params['field_id']]['field_ident']
      );
      $this->deleteFieldDialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $this->deleteFieldDialog->msgs = &$this->msgs;
      $this->deleteFieldDialog->buttonTitle = 'Delete';
    }
  }

  /**
  * Creates a dialog for changing an option when a domain is edited which
  * currently has been switched into tree mode.
  */
  function initializeOptionDialog() {
    if (!(isset($this->optionDialog) && is_object($this->optionDialog)) &&
        isset($this->params['id'])) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      if (isset($this->optionValues[$this->params['id']])) {
        $value = $this->optionValues[$this->params['id']];
      } else {
        $value = papaya_options::$optFields[$this->params['id']][4];
      }
      $option = $this->params['id'];

      $hidden = array(
        'save'=> 1,
        'cmd'=> 'edit_option',
        'id' => $option,
        'domain_id' => $this->loadedDomain['domain_id']
      );
      $data = array(
        'opt_name' => $option,
        'opt_value' => $value,
      );
      $fields = array(
        'opt_name' => array('Name', '', FALSE, 'info', 0, '',
          $option, 'left')
      );
      if (isset(papaya_options::$optFields[$option]) &&
          is_array($optionField = papaya_options::$optFields[$option])) {
        if (isset($optionField[5])) {
          $needed = !(bool)$optionField[5];
        } else {
          $needed = TRUE;
        }
        $fields[$option] =
          array('Value', $optionField[1], $needed, $optionField[2],
            $optionField[3], '', $value);
      } else {
        $fields[$option] =
          array('Value', '', TRUE, 'info', '', '', $value);
      }
      $this->optionDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->optionDialog->dialogTitle = $this->_gt('Option');
      $this->optionDialog->baseLink = $this->baseLink;
      $this->optionDialog->msgs = &$this->msgs;
      $this->optionDialog->loadParams();
    }
  }

  /**
  * initalize the dialog needed to change a field definition
  *
  * @access public
  */
  function initializeFieldDialog() {
    if (!(isset($this->fieldDialog) && is_object($this->fieldDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      if (isset($this->params['field_id']) &&
          isset($this->fields[$this->params['field_id']]) &&
          $data = $this->loadFieldDetails($this->params['field_id'])) {
        $hidden = array(
          'cmd' => 'edit_field',
          'field_id' => $this->params['field_id'],
          'save' => 1
        );
      } else {
        $data = array();
        $hidden = array(
          'cmd' => 'add_field',
          'save' => 1
        );
      }
      $fieldTypes = array(
        'input' => $this->_gt('Input field'),
        'combo' => $this->_gt('Select box'),
        'textarea' => $this->_gt('Textarea'),
        'richtext' => $this->_gt('Richtext'),
        'simplerichtext' => $this->_gt('Richtext (simple)'),
        'pageid' => $this->_gt('Page Id')
      );
      $fields = array(
        'field_ident' => array(
          'Field name', 'isAlphaNum', TRUE, 'input', 60
        ),
        'field_description' => array(
          'Description', 'isSomeText', FALSE, 'textarea', 6
        ),
        'field_check' => array(
          'Check', 'isAlphaNum', TRUE, 'function', 'getCheckFunctionsCombo'
        ),
        'field_type' => array(
          'Type', 'isAlphaNum', TRUE, 'combo', $fieldTypes
        )
      );
      if (!empty($data['field_type'])) {
        $fields[] = 'Parameters';
        switch ($data['field_type']) {
        case 'combo' :
          $fields['field_params'] = array(
            'Elements', 'isSomeText', FALSE, 'textarea', 8
          );
          break;
        case 'input' :
        case 'pageid' :
          $fields['field_params'] = array(
            'Maximum Length', 'isNum', FALSE, 'input', 5
          );
          break;
        case 'textarea' :
        case 'richtext' :
        case 'simplerichtext' :
          $fields['field_params'] = array(
            'Rows', 'isNum', FALSE, 'input', 2
          );
          break;
        }
      }
      $this->fieldDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->fieldDialog->dialogTitle = $this->_gt('Field');
      $this->fieldDialog->baseLink = $this->baseLink;
      $this->fieldDialog->msgs = &$this->msgs;
      $this->fieldDialog->loadParams();
    }
  }

  /**
  * Get select control with check functions from checkit class
  *
  * @param string $name
  * @param array $field
  * @param mixed $data
  * @access public
  * @return string XML or ''
  */
  function getCheckFunctionsCombo($name, $field, $data) {
    $result = '';
    $result .= sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name)
    );
    $methods = get_class_methods('checkit');
    sort($methods);
    if (is_array($methods) && count($methods) > 0) {
      foreach ($methods as $method) {
        if (substr($method, 0, 2) == 'is') {
          $selected = (strtolower($data) == strtolower($method)) ?
            ' selected="selected"' : '';
          $result .= sprintf(
            '<option value="%s" %s>%s</option>'.LF,
            papaya_strings::escapeHTMLChars($method),
            $selected,
            papaya_strings::escapeHTMLChars($method)
          );
        }
      }
    }
    $result .= '</select>'.LF;
    return $result;
  }

  /**
  * initialize the dialog to edit the value of a domain specific field
  *
  * @access public
  * @return boolean
  */
  function initializeValueDialog() {
    if (!(isset($this->valueDialog) && is_object($this->valueDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      if (isset($this->params['field_id']) &&
          isset($this->fieldValues[$this->params['field_id']]) &&
          $field = $this->loadFieldDetails($this->params['field_id'])) {
        $hidden = array(
          'cmd' => 'edit_value',
          'save' => 1,
          'domain_id' => $this->params['domain_id'],
          'field_id' => $this->params['field_id']
        );
        switch ($field['field_type']) {
        case 'combo' :
          $fieldParams = array();
          if ($lines = preg_split('([\r\n]+)', $field['field_params'])) {
            foreach ($lines as $line) {
              if ($p = strpos($line, '=')) {
                $fieldParamValue = substr($line, $p + 1);
                if (!empty($fieldParamValue)) {
                  $fieldParams[substr($line, 0, $p)] = $fieldParamValue;
                }
              } elseif (!empty($line)) {
                $fieldParams[$line] = $line;
              }
            }
          }
          break;
        default :
          $fieldParams = $field['field_params'];
          break;
        }
        $data = $this->fieldValues[$this->params['field_id']];
        $editFields['field_value'] = array(
          'Value',
          $field['field_check'],
          FALSE,
          $field['field_type'],
          $fieldParams
        );
        $this->valueDialog = new base_dialog(
          $this, $this->paramName, $editFields, $data, $hidden
        );
        $this->valueDialog->dialogTitle = $this->_gtf(
          'Value for field "%s"', $field['field_ident']
        );
        $this->valueDialog->baseLink = $this->baseLink;
        $this->valueDialog->msgs = &$this->msgs;
        $this->valueDialog->loadParams();
        return TRUE;
      } else {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
  * This method will update one of the options of a domain which is a tree.
  *
  * @return boolean
  */
  function saveOption() {
    $option = $this->params['id'];
    $value = $this->params[$this->params['id']];
    $this->optionValues[$option] = $value;
    if ($this->params['id'] == 'PAPAYA_LAYOUT_THEME' &&
        isset($this->params['PAPAYA_LAYOUT_TEMPLATES']) &&
        !empty($this->params['PAPAYA_LAYOUT_TEMPLATES'])) {
      $this->optionValues['PAPAYA_LAYOUT_TEMPLATES'] = $this->params['PAPAYA_LAYOUT_TEMPLATES'];
    }
    $this->loadedDomain['domain_options'] = $this->serializeDomainOptions();
    $this->saveDetails($this->loadedDomain);
    return TRUE;
  }

  /**
  * Deletes a domain option entry from database.
  *
  * @param integer $optionId
  * @access public
  * @return boolean
  */
  function deleteDomainOption($optionId) {
    if (isset($this->optionValues[$optionId])) {
      unset($this->optionValues[$optionId]);
      $this->loadedDomain['domain_options'] = $this->serializeDomainOptions();
      $this->saveDetails($this->loadedDomain);
    }
    return TRUE;
  }

  /**
  * makes a storeable XML strukture out of the currently set options.
  * Takes information from option-arrays and encodes them into
  * an xml structure which can then be stored into the database.
  *
  * @return string xml
  */
  function serializeDomainOptions() {
    return simple_xmltree::serializeArrayToXML('data', $this->optionValues);
  }

  /**
  * The opposite of serializeDomainOptions.
  * When a domain is loaded which is a tree, its option xml field
  * will be decoded into a global array called options.
  *
  * @access public
  * @param string $xmlString
  */
  function unserializeDomainOptions($xmlString) {
    $this->optionValues = array();
    if (isset($xmlString) && $xmlString != '') {
      simple_xmltree::unserializeArrayFromXML('data', $this->optionValues, $xmlString);
    }
  }

  /**
  * Get the toolbar (containing the edit categories for a domain) into layout.
  */
  function getToolbar() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    $toolbar = new base_btnbuilder;
    $toolbar->images = &$this->images;

    $showToolbar = FALSE;
    if (isset($this->loadedDomain) &&
        is_array($this->loadedDomain) &&
        count($this->loadedDomain)) {
      $showToolbar = TRUE;
      $toolbar->addButton(
        'Properties',
        $this->getLink(
          array(
            'mode' => 0,
            'cmd' => 'edit_domain',
            'domain_id' => (int)$this->loadedDomain['domain_id']
          )
        ),
        'categories-properties',
        '',
        empty($this->params['mode']) || $this->params['mode'] == 0
      );

      if ($this->loadedDomain['domain_mode'] == PAPAYA_DOMAIN_MODE_TREE) {
        $toolbar->addButton(
          'Settings',
          $this->getLink(
            array(
              'mode' => 1,
              'domain_id' => (int)$this->loadedDomain['domain_id']
            )
          ),
          'items-option',
          '',
          isset($this->params['mode']) && $this->params['mode'] == 1
        );
        $toolbar->addButton(
          'Field values',
          $this->getLink(
            array(
              'mode' => 2,
              'domain_id' => (int)$this->loadedDomain['domain_id']
            )
          ),
          'categories-content',
          '',
          isset($this->params['mode']) && $this->params['mode'] == 2
        );
      }

      if ($str = $toolbar->getXML()) {
        $this->layout->add(sprintf('<toolbar>%s</toolbar>'.LF, $str));
      }
    }
  }

  /**
  * Builds backend-layout for this module.
  *
  * @access public
  */
  function getXML() {
    if ($this->categoryMode == 1) {
      $this->getFieldListXML();
      $this->initializeFieldDialog();
      if (isset($this->deleteFieldDialog) && is_object($this->deleteFieldDialog)) {
        $this->layout->add($this->deleteFieldDialog->getMsgDialog());
      } elseif (isset($this->fieldDialog) && is_object($this->fieldDialog)) {
        $this->layout->add($this->fieldDialog->getDialogXML());
      }
    } else {
      $this->getDomainListXML();
      if (isset($this->deleteOptionDialog) && is_object($this->deleteOptionDialog)) {
        $this->layout->add($this->deleteOptionDialog->getMsgDialog());
      } elseif (isset($this->deleteDomainDialog) && is_object($this->deleteDomainDialog)) {
        $this->layout->add($this->deleteDomainDialog->getMsgDialog());
      } elseif (empty($this->params['cmd']) || $this->params['cmd'] != 'test_pageid_link') {
        $this->initializeDomainPropertiesDialog();
        $this->getToolbar();
        if (!isset($this->params['mode'])) {
          $this->params['mode'] = 0;
        }
        if (!isset($this->params['mode'])) {
          $this->params['mode'] = 0;
        }
        switch($this->params['mode']) {
        case 2:
          $this->getValuesListXML();
          if ($this->initializeValueDialog()) {
            $data = $this->loadFieldDetails($this->params['field_id']);
            if (!empty($data)) {
              $result = '<sheet>';
              $result .= '<text><div style="padding: 10px;"><p>';
              $result .= papaya_strings::escapeHTMLChars($data['field_description']);
              $result .= '</p></div></text>';
              $result .= '</sheet>';
              $this->layout->add($result);
            }
            $this->layout->add($this->valueDialog->getDialogXML());
          }
          break;
        case 1:
          if (isset($this->loadedDomain['domain_mode']) &&
              $this->loadedDomain['domain_mode'] == PAPAYA_DOMAIN_MODE_TREE) {
            if (isset($this->params['id']) && $this->params['id'] == 'PAPAYA_LAYOUT_THEME') {
              $this->layout->add($this->getLayoutDialogXML());
            } else {
              if (isset($this->optionDialog) && is_object($this->optionDialog)) {
                $this->layout->add($this->optionDialog->getDialogXML());
              }
            }
            $this->layout->add($this->getOptionsListXML());
          }
          break;
        default :
          if (isset($this->domainDialog) && is_object($this->domainDialog)) {
            $this->layout->add($this->domainDialog->getDialogXML());
          }
        }
      }
    }
    $this->getButtonsXML();
  }

  /**
  * Generate the theme browser dialog output.
  *
  * @return string output xml
  */
  public function getLayoutDialogXML() {
    $result = '';
    try {
      // initialize dialog for retrieving hidden fields and token
      $this->initializeOptionDialog();
      // collect hidden fields for browser dialog
      $hiddenFields = array_merge(
        $this->optionDialog->hidden,
        array('token' => $this->optionDialog->getDialogToken())
      );
      // choose from where data gets its values
      if (isset($this->params['save'])) {
        // select requested data after saving
        $data = array(
          'opt_name' => $this->params['id'],
          'opt_value' => $this->params[$this->params['id']]
        );
      } else {
        // select loaded data from db
        $data = $this->optionDialog->data;
      }
      // use theme browser object to generate output xml
      $themeBrowser = new PapayaUiAdministrationBrowserTheme(
        $this,
        $this->params,
        $this->paramName,
        $data,
        $this->params['id'],
        $hiddenFields
      );
      $result = $themeBrowser->getXml();
    } catch (InvalidArgumentException $e) {
      $this->addMsg(MSG_ERROR, $this->_gt($e->getMessage()));
    }
    return $result;
  }

  /**
  * generate a list of domains and put it into the current layout.
  * The currently selected domain is highlighted.
  */
  function getDomainListXML() {
    $modeDesc = array(
      PAPAYA_DOMAIN_MODE_DEFAULT => 'PAPAYA_DOMAIN_MODE_DEFAULT',
      PAPAYA_DOMAIN_MODE_PAGE => 'PAPAYA_DOMAIN_MODE_PAGE',
      PAPAYA_DOMAIN_MODE_LANG => 'PAPAYA_DOMAIN_MODE_LANG',
      PAPAYA_DOMAIN_MODE_DOMAIN => 'PAPAYA_DOMAIN_MODE_DOMAIN',
      PAPAYA_DOMAIN_MODE_TREE => 'PAPAYA_DOMAIN_MODE_TREE'
    );
    $modeImgs = array(
      PAPAYA_DOMAIN_MODE_DEFAULT => 'items-page',	// page symbol
      PAPAYA_DOMAIN_MODE_PAGE => 'items-alias', // alias symbol
      PAPAYA_DOMAIN_MODE_LANG => 'items-translation', // globe
      PAPAYA_DOMAIN_MODE_DOMAIN => 'items-link', // link symbol
      PAPAYA_DOMAIN_MODE_TREE => 'categories-sitemap' // tree symbol
    );
    $protDesc = array(
      1 => 'http://',
      2 => 'https://'
    );
    $result = sprintf(
      '<listview width="300" title="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Domains'))
    );
    if (isset($this->domains) && is_array($this->domains) && count($this->domains) > 0) {
      $result .= $this->getDomainsPagingBar($this->absCount);
      $result .= '<items>'.LF;
      foreach ($this->domains as $domain) {
        $linkParams = array('cmd' => 'edit_domain', 'domain_id' => $domain['domain_id']);
        if (isset($this->loadedDomain['domain_id']) &&
            $domain['domain_id'] == $this->loadedDomain['domain_id']) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<listitem %s image="%s" hint="%s" href="%s" title="%s%s">'.LF,
          $selected,
          papaya_strings::escapeHTMLChars(
            $this->images[$modeImgs[$domain['domain_mode']]]
          ),
          papaya_strings::escapeHTMLChars($modeDesc[$domain['domain_mode']]),
          papaya_strings::escapeHTMLChars(
            $this->getLink($linkParams)
          ),
          papaya_strings::escapeHTMLChars(
            empty($protDesc[$domain['domain_protocol']])
              ? '' : $protDesc[$domain['domain_protocol']]
          ),
          papaya_strings::escapeHTMLChars($domain['domain_hostname'])
        );
        $result .= '</listitem>'.LF;
      }
      $result .= '</items>'.LF;
    } else {
      return '';
    }
    $result .= '</listview>'.LF;
    $this->layout->addLeft($result);
    $this->layout->addLeft($this->getDomainTestDialog());
  }

  /**
  * get a listview of defined fields
  *
  * @access public
  * @return void
  */
  function getFieldListXML() {
    if (isset($this->fields) && is_array($this->fields) && count($this->fields) > 0) {
      $result = sprintf(
        '<listview width="300" title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Fields'))
      );
      $result .= '<items>'.LF;
      foreach ($this->fields as $field) {
        $linkParams = array('cmd' => 'edit_field', 'field_id' => $field['field_id']);
        if (isset($this->params['field_id']) &&
            $field['field_id'] == $this->params['field_id']) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<listitem %s image="%s" href="%s" title="%s">'.LF,
          $selected,
          papaya_strings::escapeHTMLChars($this->images['categories-content']),
          papaya_strings::escapeHTMLChars($this->getLink($linkParams)),
          papaya_strings::escapeHTMLChars($field['field_ident'])
        );
        $result .= '</listitem>'.LF;
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->addLeft($result);
    }
  }

  /**
  * get a listview of defined field and values status for the current domain
  *
  * @access public
  * @return void
  */
  function getValuesListXML() {
    if (isset($this->fieldValues) &&
        is_array($this->fieldValues) &&
        count($this->fieldValues) > 0) {
      $result = sprintf(
        '<listview width="300" title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Fields'))
      );
      $result .= '<items>'.LF;
      foreach ($this->fieldValues as $field) {
        $linkParams = array(
          'cmd' => 'edit_value',
          'field_id' => $field['field_id'],
          'domain_id' => $this->loadedDomain['domain_id']
        );
        if (isset($this->params['field_id']) &&
            $field['field_id'] == $this->params['field_id']) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<listitem %s image="%s" href="%s" title="%s">'.LF,
          $selected,
          papaya_strings::escapeHTMLChars($this->images['categories-content']),
          papaya_strings::escapeHTMLChars($this->getLink($linkParams)),
          papaya_strings::escapeHTMLChars($field['field_ident'])
        );
        $result .= '<subitem align="right">';
        if (empty($field['field_value'])) {
          $imageIdx = 'status-sign-off';
          $hint = $this->_gt('Value not set');
        } else {
          $imageIdx = 'status-sign-ok';
          $hint = $this->_gt('Value is set');
        }
        $result .= sprintf(
          '<glyph src="%s" hint="%s" />',
          papaya_strings::escapeHTMLChars($this->images[$imageIdx]),
          papaya_strings::escapeHTMLChars($hint)
        );
        $result .= '</subitem>';
        $result .= '</listitem>'.LF;
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->addRight($result);
    }
  }

  /**
  * get backend dialog for domain matching test
  *
  * @access public
  * @return string
  */
  function getDomainTestDialog() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    $hidden = array(
      'cmd'=> 'test'
    );
    $data = array(
      'test_domain' => $this->getHostName(),
      'test_protocol' => PapayaUtilServerProtocol::isSecure() ? 2 : 1,
    );
    $protocols = array(
      1 => 'http',
      2 => 'https'
    );
    $fields = array(
      'test_domain' => array(
        'Domain', 'isHostName', TRUE, 'input', 100, ''
      ),
      'test_protocol' => array(
        'Protocol', 'isNum', TRUE, 'combo', $protocols
      )
    );
    $testDialog = new base_dialog(
      $this, $this->paramName, $fields, $data, $hidden
    );
    $testDialog->dialogTitle = $this->_gt('Test domain');
    $testDialog->buttonTitle = 'Test';
    $testDialog->baseLink = $this->baseLink;
    $testDialog->msgs = &$this->msgs;
    $testDialog->inputFieldSize = 'x-small';
    $testDialog->loadParams();
    return $testDialog->getDialogXML();
  }

  /**
   * get backend dialog for page id link test
   *
   * @access public
   * @return string
   */
  public function getPageIdTestDialog() {
    $dialog = new PapayaUiDialog();
    $dialog->parameterGroup($this->paramName);
    $dialog->caption(new PapayaUiStringTranslated('Test page id'));
    $dialog->hiddenFields['cmd'] = 'test_pageid_link';
    $dialog->fields[] = new PapayaUiDialogFieldInputPage(
      new PapayaUiStringTranslated('Page id'), 'page_id'
    );
    $dialog->buttons[] = new PapayaUiDialogButtonSubmit(new PapayaUiStringTranslated('Test'));
    $dialog->parameters()->set('link', '');
    if ($dialog->execute() && ($pageId = $dialog->data()->get('page_id', 0)) > 0) {
      $language = $this->papaya()->administrationLanguage->getCurrent()->id;
      $factory = new PapayaUiReferencePageFactory();
      $factory->setPreview(FALSE);
      $domain = $factory->getDomainData($language, $pageId);
      $reference = $factory->get($language, $pageId);
      $reference->setOutputMode($this->papaya()->options->get('PAPAYA_URL_EXTENSION', 'html'));
      $dialog->fields[] = $group = new PapayaUiDialogFieldGroup(
        new PapayaUiStringTranslated('Result')
      );
      $group->fields[] = new PapayaUiDialogFieldInputReadonly(
        'Domain', 'domain', isset($domain['host']) ? $domain['host'] : ''
      );
      $group->fields[] = new PapayaUiDialogFieldInputReadonly('Link', 'link', $reference->get());
    }
    return $dialog->getXml();
  }

  /**
  * Get list of options for a tree-set domain into the layout.
  */
  function getOptionsListXML() {
    if (isset($this->optionGroups) && is_array($this->optionGroups)) {
      $result = sprintf(
        '<listview title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Options'))
      );
      $result .= '<items>';

      $dataArr = array();
      $optionValues = array();
      simple_xmltree::unserializeArrayFromXML(
        'data', $dataArr, $this->loadedDomain['domain_options']);
      if (isset($dataArr) && is_array($dataArr) && count($dataArr) > 0) {
        foreach ($dataArr as $option => $value) {
          $optionValues[$option] = $value;
        }
      }

      foreach ($this->optionGroups as $groupId=>$optionGroup) {
        if (isset($this->optionLinks[$groupId]) &&
            is_array($this->optionLinks[$groupId]) &&
            isset($this->sessionParams['opened'][$groupId]) &&
            $this->sessionParams['opened'][$groupId]) {
          $nodeHref = $this->getLink(
            array(
              'cmd' => 'close_option',
              'gid' => $groupId,
              'domain_id' => $this->loadedDomain['domain_id']
            )
          );
          $node = sprintf(
            ' node="open" nhref="%s"',
            papaya_strings::escapeHTMLChars($nodeHref)
          );
        } elseif (isset($this->optionLinks[$groupId]) &&
                  is_array($this->optionLinks[$groupId])) {
          $nodeHref = $this->getLink(
            array(
              'cmd' => 'open_option',
              'gid' => $groupId,
              'domain_id' => $this->loadedDomain['domain_id']
            )
          );
          $node = sprintf(
            ' node="close" nhref="%s"',
            papaya_strings::escapeHTMLChars($nodeHref)
          );
        } else {
          continue;
        }
        if (isset($this->optionLinks[$groupId]) &&
            is_array($this->optionLinks[$groupId]) &&
            isset($this->sessionParams['opened'][$groupId]) &&
            ($this->sessionParams['opened'][$groupId])) {
          $imageIdx = 'status-folder-open';
        } else {
          $imageIdx = 'items-folder';
        }
        $result .= sprintf(
          '<listitem title="%s" image="%s"%s>'.LF,
          papaya_strings::escapeHTMLChars($optionGroup),
          papaya_strings::escapeHTMLChars($this->images[$imageIdx]),
          $node
        );
        $result .= '<subitem/>';
        $result .= '</listitem>';
        if (isset($this->optionLinks[$groupId]) &&
            is_array($this->optionLinks[$groupId]) &&
            isset($this->sessionParams['opened'][$groupId]) &&
            ($this->sessionParams['opened'][$groupId])) {
          foreach ($this->optionLinks[$groupId] as $optId) {
            if (isset(papaya_options::$optFields[$optId]) &&
                is_array(papaya_options::$optFields[$optId])) {
              $option = papaya_options::$optFields[$optId];
              if (isset($this->params['id']) && $this->params['id'] == $optId) {
                $selected = ' selected="selected"';
              } else {
                $selected = '';
              }
              $href = $this->getLink(
                array(
                  'id' => $optId,
                  'domain_id' => $this->loadedDomain['domain_id']
                )
              );
              $result .= sprintf(
                '<listitem title="%s" indent="2" href="%s" image="%s"%s>',
                papaya_strings::escapeHTMLChars($optId),
                papaya_strings::escapeHTMLChars($href),
                papaya_strings::escapeHTMLChars($this->images['items-option']),
                $selected
              );
              $result .= '<subitem>';
              if (isset($optionValues[$optId])) {
                $result .= papaya_strings::escapeHTMLChars($optionValues[$optId]);
              } elseif (defined($optId) && constant($optId) != '') {
                $result .= '('.papaya_strings::escapeHTMLChars(constant($optId)).')';
              }
              $result .= '</subitem>';
              $result .= '</listitem>'.LF;
            }
          }
        }
      }
      $result .= '</items>';
      $result .= '</listview>';

      $this->layout->add($result);
    }
  }

  /**
  * generate paging bar for domains
  *
  * @param integer $absCount total number of tags
  * @param string $cmd command for tag link
  */
  function getDomainsPagingBar($absCount, $cmd = NULL) {
    if ($cmd == NULL && isset($this->params['cmd'])) {
      $baseParams = array('cmd' => $this->params['cmd']);
    } else {
      $baseParams = array();
    }

    include_once(PAPAYA_INCLUDE_PATH.'system/papaya_paging_buttons.php');
    return papaya_paging_buttons::getPagingButtons(
      $this,
      $baseParams,
      $this->params['offset_domains'],
      $this->defaultLimit,
      $absCount,
      9,
      'offset_domains'
    );
  }

  /**
  * load a list of avaiable view-extensions
  * from whose a specific one can be selected for a domain relocator,
  * that relocates to a page.
  */
  function loadExtensions() {
    $sql = "SELECT viewmode_ext FROM %s";
    $params = array($this->tableViewModes);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow()) {
        $this->knownExtensions[] = $row[0];
      }
    }
  }

  /**
  * Get buttons into layout.
  */
  function getButtonsXML() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    $toolbar = new base_btnbuilder;
    $toolbar->images = &$this->images;

    $toolbar->addButton(
      'Domains',
      $this->getLink(array('cmd'=>'show_domains')),
      $this->module->getIconURI('domains.png'),
      '',
      $this->categoryMode == 0
    );
    $toolbar->addButton(
      'Fields',
      $this->getLink(array('cmd'=>'show_fields')),
      'items-dialog',
      '',
      $this->categoryMode == 1
    );
    $toolbar->addSeperator();

    if ($this->categoryMode == 1) {
      $toolbar->addButton(
        'Add field',
        $this->getLink(array('cmd'=>'add_field')),
        'actions-generic-add',
        '',
        FALSE
      );
      if (isset($this->params['field_id'])) {
        $toolbar->addButton(
          'Delete Field',
          $this->getLink(
            array('cmd' => 'delete_field', 'field_id' => $this->params['field_id'])
          ),
          'actions-generic-delete'
        );
      }
    } else {
      $toolbar->addButton(
        'Add domain',
        $this->getLink(array('cmd'=>'add_domain')),
        'actions-alias-add',
        '',
        FALSE
      );
      if (isset($this->loadedDomain) &&
          is_array($this->loadedDomain) &&
          count($this->loadedDomain)) {
        $toolbar->addButton(
          'Delete domain',
          $this->getLink(
            array('cmd' => 'del_domain', 'domain_id' => $this->loadedDomain['domain_id'])
          ),
          'actions-alias-delete'
        );
        if (isset($this->params['id'])) {
          $toolbar->addSeperator();
          $toolbar->addButton(
            'Delete option',
            $this->getLink(
              array(
                'cmd' => 'del_option',
                'domain_id' => $this->loadedDomain['domain_id'],
                'id' => $this->params['id']
              )
            ),
            'actions-option-delete'
          );
        }
        if (isset($this->params['mode']) &&
            $this->params['mode'] == '2' &&
            is_array($this->fieldValues) &&
            count($this->fieldValues) > 0) {
          $toolbar->addSeperator();
          $toolbar->addButton(
            'Export values',
            $this->getLink(
              array(
                'cmd' => 'export_values',
                'domain_id' => $this->loadedDomain['domain_id']
              )
            ),
            'actions-download'
          );
        }
      }
      $toolbar->addSeperator();
      $toolbar->addButton(
        'Test page link',
        $this->getLink(array('cmd'=>'test_pageid_link')),
        'items-link',
        '',
        isset($this->params['cmd']) && $this->params['cmd'] == 'test_pageid_link'
      );
    }
    $this->layout->addMenu(
      sprintf('<menu ident="%s">%s</menu>'.LF, 'edit', $toolbar->getXML())
    );
  }

  /**
  * Get language combo
  *
  * @param string $name
  * @param array $element
  * @param string $data
  * @access public
  * @return string XML
  */
  function getContentLanguageCombo($name, $element, $data) {
    $sql = "SELECT lng_id, lng_short, lng_title
              FROM %s
             WHERE is_content_lng = 1
             ORDER BY lng_title";
    $result = '';
    if ($res = $this->databaseQueryFmt($sql, PAPAYA_DB_TBL_LNG)) {
      $languages = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $languages[$row['lng_id']] = $row;
      }
      if (is_array($languages) && count($languages) > 0) {
        if (!isset($languages[$data])) {
          if (defined('PAPAYA_CONTENT_LANGUAGE')) {
            $data = PAPAYA_CONTENT_LANGUAGE;
          } else {
            $data = min(array_keys($languages));
          }
        }
        $result .= sprintf(
          '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name)
        );
        foreach ($languages as $lngId => $lng) {
          $selected = ($data > 0 && $lngId == $data) ? ' selected="selected"' : '';
          $result .= sprintf(
            '<option value="%d"%s>%s (%s)</option>'.LF,
            papaya_strings::escapeHTMLChars($lng['lng_id']),
            $selected,
            papaya_strings::escapeHTMLChars($lng['lng_title']),
            papaya_strings::escapeHTMLChars($lng['lng_short'])
          );
        }
        $result .= '</select>'.LF;
        $res->free();
      } else {
        $result = sprintf(
          '<input type="text" disabled="disabled" value="%s"/>',
          'No language found'
        );
      }
    }
    return $result;
  }
}
?>

<?php
/**
* Action box for HTML
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
* @version $Id: actbox_domain_html.php 32602 2009-10-14 15:37:28Z weinert $
*/

/**
* Basic class Aktion box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Action box for HTML
*
* @package Papaya-Modules
* @subpackage Free-Domains
*/
class actionbox_domain_html extends base_actionbox {

  /**
  * Preview allowed?
  * @var boolean $preview
  */
  var $preview = TRUE;

  var $domainConnectorGuid = '8ec0c5995d97c9c3cc9c237ad0dc6c0b';

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'field_identifer' => array('Field name', 'isAlphaNum', TRUE, 'function', 'getIdentifierCombo'),
    'text' => array('Default text', 'isSomeText', FALSE, 'textarea', 30)
  );

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    if (!empty($this->data['field_identifer'])) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
      $domainConnector = base_pluginloader::getPluginInstance(
        $this->domainConnectorGuid,
        $this
      );
      if (is_object($domainConnector)) {
        $data = $domainConnector->loadValues($this->data['field_identifer']);
        if (!empty($data[$this->data['field_identifer']])) {
          return $data[$this->data['field_identifer']];
        }
      }
    }
    if (isset($this->data['text'])) {
      return $this->data['text'];
    }
    return FALSE;
  }

  /**
  * callback for the dialog - get the identifier select box
  *
  * @access public
  * @return string
  */
  function getIdentifierCombo($name, $field, $data) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
    $domainConnector = base_pluginloader::getPluginInstance(
      $this->domainConnectorGuid,
      $this
    );
    if (is_object($domainConnector)) {
      return $domainConnector->getIdentifierCombo(
        $this->paramName.'['.$name.']',
        $data
      );
    }
    return '';
  }
}
?>
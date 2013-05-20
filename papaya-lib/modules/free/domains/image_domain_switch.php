<?php
/**
* Dynamic image generator that outputs the image specified in a domain variable.
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
* @version $Id: image_domain_switch.php 32527 2009-10-13 15:57:07Z weinert $
*/

/**
* Base class for image plugins
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_dynamicimage.php');

/**
* Dynamic image generator that outputs the image specified in a domain variable.
*
* @package Papaya-Modules
* @subpackage Free-Domains
*/
class image_domain_switch extends base_dynamicimage {


  /**
  * GUID for the domain connector
  * @var string $domainConnectorGuid
  */
  var $domainConnectorGuid = '8ec0c5995d97c9c3cc9c237ad0dc6c0b';

  /**
  * Attribute fields
  * @var array $attributeFields
  */
  var $attributeFields = array(
    'image_guid' => array('Default image', 'isGuid', FALSE, 'imagefixed', 32, '', ''),
    'image_guid_ident' => array('Domain identifier for the image',
      'isAlphaNum', FALSE,  'function', 'getIdentifierCombo', '', ''),
  );

  /**
  * generate the image
  *
  * @param object base_imagegenerator &$controller controller object
  * @access public
  * @return image $result resource image
  */
  function &generateImage(&$controller) {
    $return = NULL;
    $image_guid = $this->attributes['image_guid'];
    if (!empty($this->attributes['image_guid_ident'])) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
      $domainConnector = base_pluginloader::getPluginInstance(
        $this->domainConnectorGuid,
        $this
      );
      if (is_object($domainConnector)) {
        $values = $domainConnector->loadValues($this->attributes['image_guid_ident']);
        if (isset($values[$this->attributes['image_guid_ident']])) {
          $image_guid = $values[$this->attributes['image_guid_ident']];
        }
      }
    }
    if (!empty($image_guid) &&
        $image = &$controller->getMediaFileImage($image_guid)) {
      $return = $image;
    } else {
      $this->lastError = 'Can not load image.';
    }
    return $return;
  }

  /**
  * Get select box with available domain field identifiers
  * @param string $name
  * @param array $field
  * @param string $data
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
        $data,
        TRUE
      );
    }
    return '';
  }

  /**
  * @see papaya-lib/system/base_dynamicimage#getCacheId($appendString)
  */
  function getCacheId($appendString = '') {
    if (isset($_SERVER['HTTP_HOST'])) {
      return parent::getCacheId($_SERVER['HTTP_HOST'].'_'.$appendString);
    } else {
      return parent::getCacheId();
    }
  }
}
?>

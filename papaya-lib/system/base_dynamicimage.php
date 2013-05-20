<?php
/**
* Base class of dynamic image plugins
*
* image plugins must be inherited from this superclass
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
* @package Papaya
* @subpackage Modules
* @version $Id: base_dynamicimage.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Base class for plugins
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_plugin.php');

/**
* Check functions library
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_checkit.php');
/**
* Base class of dynamic image plugins
*
* image  plugins must be inherited from this superclass
*
* @package Papaya
* @subpackage Modules
*/
class base_dynamicimage extends base_plugin {

  /**
  * can be cached
  * @var boolean $cacheable
  */
  var $cacheable = TRUE;

  /**
  * Css class
  * @var string $inputFieldSize
  */
  var $inputFieldSize = 'x-large';

  /**
  * Allowed options / attributes in the papaya:image tags
  * @var array $attributeFields
  */
  var $attributeFields = array();

  /**
  * Array with the current attributes
  * @var array $attributes
  */
  var $attributes = array();

  /**
  * Last error message - for debugging
  * @var string
  */
  var $lastError = 'Unknown';

  /**
  * generate the image
  *
  * @access public
  * @return boolean FALSE
  */
  function generateImage() {
    $this->lastError = 'No image generator implemented.';
    return FALSE;
  }

  /**
  * get attribute dialog (for preview)
  *
  * @access public
  * @return string xml
  */
  function getAttributeDialog() {
    if (is_array($this->attributeFields) && count($this->attributeFields) > 0) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $hidden = array('set_attr' => 1);
      $dialog = new base_dialog(
        $this, $this->paramName, $this->attributeFields, $this->attributes, $hidden
      );
      $dialog->msgs = &$this->msgs;
      $dialog->loadParams();
      $dialog->inputFieldSize = $this->inputFieldSize;
      $dialog->baseLink = $this->baseLink;
      $dialog->dialogTitle = $this->_gt('Attributes');
      $dialog->buttonTitle = 'Show';
      $dialog->dialogDoubleButtons = TRUE;
      if ($dialog->modified('set_attr')) {
        if ($dialog->checkDialogInput()) {
          $this->attributes = $dialog->data;
        }
      }
      return $dialog->getDialogXML();
    }
  }

  /**
  * check an set the attributes
  *
  * @param array $attributes
  * @access public
  * @return boolean
  */
  function setAttributes($attributes) {
    $checkFunctions = get_class_methods('checkit');
    foreach ($checkFunctions as $idx => $functionName) {
      $checkFunctions[$idx] = strtolower($functionName);
    }
    $checkObj = new checkit;
    unset($this->inputErrors);
    $result = TRUE;
    if (isset($checkFunctions) && is_array($checkFunctions)) {
      foreach ($this->attributeFields as $key => $val) {
        if (isset($val) && is_array($val)) {
          if (strpos($val[3], 'disabled_') === 0) {
            $elementType = substr($val[3], strlen('disabled_'));
            //field does not allow user inputs - no data
            $this->attributes[$key] = NULL;
          } else {
            $elementType = $val[3];
            //active field
            $checkFunction = strtolower($val[1]);
            $attributeValue = isset($attributes[$key]) ? $attributes[$key] : '';
            if (in_array($checkFunction, $checkFunctions)) {
              if ($checkObj->$checkFunction($attributeValue, $val[2])) {
                $this->attributes[$key] = $attributeValue;
              } else {
                $result = FALSE;
              }
            } else {
              if (@preg_match($checkFunction, $attributeValue)) {
                $this->attributes[$key] = $attributeValue;
              } elseif ($val[2] == FALSE && empty($attributeValue)) {
                $this->attributes[$key] = $attributeValue;
              } else {
                $result = FALSE;
              }
            }
          }
        }
      }
    }
    return $result;
  }

  /**
  * Get output cache identifier
  *
  * @access public
  * @param $appendString
  * @return mixed Cache Id or FALSE
  */
  function getCacheId($appendString = '') {
    if ($this->cacheable) {
      return md5(serialize($this->attributes).'_'.$appendString);
    } else {
      return FALSE;
    }
  }
}
?>
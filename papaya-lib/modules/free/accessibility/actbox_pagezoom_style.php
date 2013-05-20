<?php
/**
* Action box page zoom - modifiy the basefont to zoom the whole page
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
* @subpackage Free-Accessibility
* @version $Id: actbox_pagezoom_style.php 32408 2009-10-12 11:54:58Z feder $
*/

/**
* action box superclass
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Action box page zoom - modifiy the basefont to zoom the whole page
*
* @package Papaya-Modules
* @subpackage Free-Accessibility
*/
class actionbox_pagezoom_style extends base_actionbox {

  /**
  * edit fields
  * @var array
  */
  var $editFields = array(
    'fontsize_default' => array (
      'Font size default', 'isFloat', TRUE, 'input', 6, 'font size in em', 1),
    'fontsize_min' => array (
      'Font size minimum', 'isFloat', TRUE, 'input', 6, 'font size in em', 0.5),
    'fontsize_max' => array (
      'Font size maximum', 'isFloat', TRUE, 'input', 6, 'font size in em', 3)
  );

  /**
  * Get parsed data, xml data to generate a css that modifies the base font size
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $this->setDefaultData();
    $defaultZoom = (float)$this->data['fontsize_default'];
    $minimumZoom = (float)$this->data['fontsize_min'];
    $maximumZoom = (float)$this->data['fontsize_max'];
    if ($minimumZoom < 0.1) {
      $minimumZoom = 0.5;
    }
    if ($maximumZoom > 10) {
      $maximumZoom = 10;
    }
    if (($defaultZoom < $minimumZoom) || ($defaultZoom > $maximumZoom)) {
      $defaultZoom = 1;
    }
    if (isset($GLOBALS['PAPAYA_PAGE']) && is_object($GLOBALS['PAPAYA_PAGE'])) {
      $currentZoom = $GLOBALS['PAPAYA_PAGE']->getPageOption('pagezoom');
      if (isset($_GET['zoom'])) {
        $newZoom = (float)$_GET['zoom'];
        if (($newZoom < $minimumZoom) || ($newZoom > $maximumZoom)) {
          $newZoom = $defaultZoom;
        }
        if ($newZoom != $currentZoom) {
          $GLOBALS['PAPAYA_PAGE']->setPageOption('pagezoom', $newZoom);
          $currentZoom = $newZoom;
        }
      } else {
        if (($currentZoom < $minimumZoom) || ($currentZoom > $maximumZoom)) {
          $currentZoom = $defaultZoom;
        }
      }
      $result = '<zoom>';
      $result .= $currentZoom;
      $result .= '</zoom>';
    }
    return $result;
  }

  /**
  * Get cache id, include current zoom value in cache id.
  *
  * @access public
  * @return string
  */
  function getCacheId() {
    $cacheIdent = '';
    if (isset($_GET['zoom'])) {
      $cacheIdent .= 'zoom='.round($_GET['zoom'] * 10000);
    } elseif (isset($GLOBALS['PAPAYA_PAGE']) && is_object($GLOBALS['PAPAYA_PAGE'])) {
      $cacheIdent .= 'zoom='.round($GLOBALS['PAPAYA_PAGE']->getPageOption('pagezoom') * 10000);
    }
    return parent::getCacheId($cacheIdent);
  }
}
?>
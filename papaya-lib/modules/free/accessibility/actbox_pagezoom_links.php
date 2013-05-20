<?php
/**
* Action box page zoom - output links for the actionbox_pagezoom_style
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
* @version $Id: actbox_pagezoom_links.php 35108 2010-11-03 16:50:31Z weinert $
*/

/**
* action box superclass
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Action box page zoom - output links for the actionbox_pagezoom_style
*
* @package Papaya-Modules
* @subpackage Free-Accessibility
*/
class actionbox_pagezoom_links extends base_actionbox {

  var $cacheDependency = array(
    'querystring' => FALSE,
    'page' => TRUE,
    'surfer' => TRUE
  );

  /**
  * Edit fields
  * @var array
  */
  var $editFields = array(
    'fontsize_default' => array (
      'Font size default', 'isFloat', TRUE, 'input', 6, 'font size in em', 1),
    'fontsize_min' => array (
      'Font size minimum', 'isFloat', TRUE, 'input', 6, 'font size in em', 0.5),
    'fontsize_max' => array (
      'Font size maximum', 'isFloat', TRUE, 'input', 6, 'font size in em', 3),
    'fontsize_step' => array (
      'Font size change step', 'isFloat', TRUE, 'input', 6, 'font size in em', 0.2),
    'link_displaymode' => array (
      'Display mode', 'isNum', TRUE, 'combo',
      array(0 => '+/-', 1 => 'AAA', 2 => '-A+'),
      '', 0
    )
  );

  /**
  * Generate and return zoom links xml
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $this->setDefaultData();
    $defaultZoom = (float)$this->data['fontsize_default'];
    $minimumZoom = (float)$this->data['fontsize_min'];
    $maximumZoom = (float)$this->data['fontsize_max'];
    $zoomStep = (float)$this->data['fontsize_step'];
    if ($minimumZoom < 0.1) {
      $minimumZoom = 0.5;
    }
    if ($maximumZoom > 10) {
      $maximumZoom = 10;
    }
    if (($defaultZoom < $minimumZoom) || ($defaultZoom > $maximumZoom)) {
      $defaultZoom = 1;
    }
    if (($zoomStep < 0.1) || ($zoomStep > 2)) {
      $zoomStep < 0.2;
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
      $result = '<zoomlinks>';
      $link = $this->getWebLink();
      switch($this->data['link_displaymode']) {
      case 2 :
        if (($currentZoom - $zoomStep) > $minimumZoom) {
          $href = $link.$this->recodeQueryString(
            $_SERVER['QUERY_STRING'], array('zoom' => $currentZoom - $zoomStep)
          );
          $result .= sprintf(
            '<link type="minus" href="%s" />',
            papaya_strings::escapeHTMLChars($href)
          );
        }
        $href = $link.$this->recodeQueryString(
          $_SERVER['QUERY_STRING'],
          array('zoom' => $defaultZoom)
        );
        $result .= sprintf(
          '<link type="default" href="%s" />',
          papaya_strings::escapeHTMLChars($href)
        );
        if (($currentZoom + $zoomStep) < $maximumZoom) {
          $href = $link.$this->recodeQueryString(
            $_SERVER['QUERY_STRING'], array('zoom' => $currentZoom + $zoomStep)
          );
          $result .= sprintf(
            '<link type="plus" href="%s" />',
            papaya_strings::escapeHTMLChars($href)
          );
        }
        break;
      case 1 :
        $href = $link.$this->recodeQueryString(
          $_SERVER['QUERY_STRING'],
          array('zoom' => $minimumZoom)
        );

        $selectedMinimumZoom = ($minimumZoom == $currentZoom) ? 'selected="selected"': '';
        $result .= sprintf(
          '<link type="min" href="%s" %s/>',
          papaya_strings::escapeHTMLChars($href),
          $selectedMinimumZoom
        );
        $href = $link.$this->recodeQueryString(
          $_SERVER['QUERY_STRING'],
          array('zoom' => $defaultZoom)
        );

        $selectedDefaultZoom = ($defaultZoom == $currentZoom) ? 'selected="selected"': '';
        $result .= sprintf(
          '<link type="default" href="%s" %s/>',
          papaya_strings::escapeHTMLChars($href),
          $selectedDefaultZoom
        );

        $href = $link.$this->recodeQueryString(
          $_SERVER['QUERY_STRING'],
          array('zoom' => $maximumZoom)
        );
        $selectedMaximumZoom = ($maximumZoom == $currentZoom) ? 'selected="selected"': '';
        $result .= sprintf(
          '<link type="max" href="%s" %s/>',
          papaya_strings::escapeHTMLChars($href),
          $selectedMaximumZoom
        );
        break;
      default :
        if (($currentZoom - $zoomStep) > $minimumZoom) {
          $href = $link.$this->recodeQueryString(
            $_SERVER['QUERY_STRING'],
            array('zoom' => $currentZoom - $zoomStep)
          );
          $result .= sprintf(
            '<link type="minus" href="%s" />',
            papaya_strings::escapeHTMLChars($href)
          );
        }
        if (($currentZoom + $zoomStep) < $maximumZoom) {
          $href = $link.$this->recodeQueryString(
            $_SERVER['QUERY_STRING'],
            array('zoom' => $currentZoom + $zoomStep)
          );
          $result .= sprintf(
            '<link type="plus" href="%s" />',
            papaya_strings::escapeHTMLChars($href)
          );
        }
        break;
      }
      $result .= '</zoomlinks>';
    }
    return $result;
  }

  /**
  * If the box finds one of its own parameters in the url it marks the url as no indexable.
  *
  * The links from this box increase the font size. So it is always a content duplication.
  *
  * @return array
  */
  function getParsedAttributes() {
    if (isset($_GET['zoom'])) {
      return array('noIndex' => 'yes');
    } else {
      return array();
    }
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
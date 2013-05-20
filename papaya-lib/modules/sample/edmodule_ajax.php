<?php
/**
* Sample for an admin module that uses Ajax to reload sub selectors
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
* @subpackage Sample
* @version $Id: edmodule_ajax.php 38291 2013-03-19 16:31:44Z kersken $
*/

/**
* Basic class modification module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_module.php');

/**
* Sample for an admin module that uses Ajax to reload sub selectors
*
* @package Papaya-Modules
* @subpackage Free-Actions
*/
class edmodule_ajax extends base_module {
  /**
  * Plugin option fields
  * @var array $pluginOptionFields
  */
  var $pluginOptionFields = array(
    'COUNTRY_AJAX' => array(
      'URL for state/city Ajax',
      'isNoHTML',
      TRUE,
      'input',
      255,
      'Use the state list from the countries package with a plain XML output filter',
      ''
    )
  );

  /**
  * Execute module
  *
  * @access public
  */
  function execModule() {
    include_once(dirname(__FILE__)."/admin_ajax_sample.php");
    $admin = new admin_ajax_sample($this->msgs);
    $admin->module = &$this;
    $admin->images = &$this->images;
    $admin->layout = &$this->layout;
    $admin->getXML($this->layout);
  }
}
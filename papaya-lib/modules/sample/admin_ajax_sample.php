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
* @subpackage Free-Actions
* @version $Id: admin_ajax_sample.php 38303 2013-03-21 12:01:43Z kersken $
*/

/**
* Action dispatcher management
*
* @package Papaya-Modules
* @subpackage Free-Actions
*/
class admin_ajax_sample extends base_object {
  /**
  * Constructor
  */
  function __construct(&$msgs, $paramName = 'smp') {
    parent::__construct($paramName);
    $this->paramName = $paramName;
    $this->msgs = $msgs;
  }

  /**
  * Get page layout
  *
  * Creates the page layout according to parameters
  *
  * @param object xsl_layout &$layout
  * @access public
  */
  function getXML(&$layout) {
    $this->initializeParams();
    $selectedCountry = '';
    if (isset($this->params['country']) && $this->params['country'] != '') {
      $selectedCountry = $this->params['country'];
    }
    $selectedState = '';
    if (isset($this->params['state']) && $this->params['state'] != '') {
      $selectedState = $this->params['state'];
    }
    $selectedCity = '';
    if (isset($this->params['city']) && $this->params['city'] != '') {
      $selectedCity = $this->params['city'];
    }
    include_once(PAPAYA_INCLUDE_PATH.'system/base_module_options.php');
    $guid = 'dbdcc59abde8a7bb581efcc58c991044';
    $url = base_module_options::readOption($guid, 'COUNTRY_AJAX', '');
    $this->layout->setParam('COLUMNWIDTH_LEFT', '300px');
    if ($url != '') {
      $this->layout->addScript(
        sprintf(
          '<script type="text/javascript">
            countryAjaxUrl = "%s";
            selectedCountry = "%s";
            selectedState = "%s";
            selectedCity = "%s";
           </script>
           <script type="text/javascript" src="%s"> </script>',
          papaya_strings::escapeHTMLChars($url),
          papaya_strings::escapeHTMLChars($selectedCountry),
          papaya_strings::escapeHTMLChars($selectedState),
          papaya_strings::escapeHTMLChars($selectedCity),
          PapayaUtilStringXml::escapeAttribute(
            $this->getLink(
              array(
                'module' => 'dbdcc59abde8a7bb581efcc58c991044',
                'src' => 'script/countries.js'
              ),
              '',
              'modglyph.php'
            )
          )
        )
      );
    }
    $layout->add($this->getDialog());
  }

  /**
  * Get the sample dialog
  *
  * @return string XML
  */
  function getDialog() {
    $result = '';
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    $fields = array(
      'country' => array('Country', 'isNoHTML', FALSE, 'function', 'getCountrySelector'),
      'state' => array('State', 'isNoHTML', FALSE, 'function', 'getStateSelector'),
      'city' => array('City', 'isNoHTML', FALSE, 'function', 'getCitySelector')
    );
    $data = array();
    $dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $dialog->dialogTitle = $this->_gt('Add action');
    $dialog->buttonTitle = 'Add';
    $dialog->baseLink = $this->baseLink;
    $dialog->loadParams();
    if (is_object($dialog)) {
      $result = $dialog->getDialogXML();
    }
    return $result;
  }

  function getCountrySelector($name, $field, $value) {
    $countriesObj = base_pluginloader::getPluginInstance('99db2c2898403880e1ddeeebf7ee726c', $this);
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale" id="countrySelector">',
      $this->paramName,
      $name
    );
    $result .= $countriesObj->getCountryOptionsXHTML($value);
    $result .= '</select>';
    return $result;
  }

  function getStateSelector($name, $field, $value) {
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale" id="stateSelector">',
      $this->paramName,
      $name
    );
    $result .= '<option value="">[Select a country first]</option>';
    $result .= '</select>';
    return $result;
  }

  function getCitySelector($name, $field, $value) {
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale" id="citySelector">',
      $this->paramName,
      $name
    );
    $result .= '<option value="">[Select country and state first]</option>';
    $result .= '</select>';
    return $result;
  }
}
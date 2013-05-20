<?php
/**
* output filter
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
* @version $Id: base_outputfilter.php 33978 2010-04-14 13:12:02Z weinert $
*/

/**
* Basic class plugins
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_plugin.php');
/**
* output filter
*
* @package Papaya
* @subpackage Modules
*/
class base_outputfilter extends base_plugin {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array();

  /**
  * default template subdirectory
  * @var string
  */
  var $templatePath = '';

  /**
  * Parse page
  *
  * @param object base_topic &$topic
  * @param string $xmlString
  * @access public
  * @return string ''
  */
  function parsePage(&$topic, &$layout) {
    return '';
  }

  /**
  * Parse box
  *
  * @param object base_topic &$topic
  * @param array &$box
  * @param string $xmlString
  * @access public
  * @return string ''
  */
  function parseBox(&$topic, &$box, $xmlString) {
    return '';
  }

  /**
  * parse some xml data
  *
  * @param object sys_xsl &$layout
  * @access public
  * @return string
  */
  function parseXML(&$layout) {
    return '';
  }

  /**
  * Check configuration
  *
  * @param boolean $page optional, default value TRUE
  * @access public
  * @return boolean FALSE
  */
  function checkConfiguration($page = TRUE) {
    return FALSE;
  }

  /**
  * Get dialog
  *
  * @see base_dialog:getDialogXML
  * @access public
  * @return string
  */
  function getDialog() {
    $this->initializeDialog();
    $this->dialog->dialogTitle = $this->_gt('Edit filter properties');
    $this->dialog->dialogDoubleButtons = FALSE;
    return $this->dialog->getDialogXML();
  }

  /**
  * callback from form to get the defined template path for file listing
  *
  * @access public
  * @return string
  */
  function getTemplatePath() {
    $templateHandler = new PapayaTemplateXsltHandler();
    return $templateHandler->getLocalPath().$this->templatePath.'/';
  }
}
?>
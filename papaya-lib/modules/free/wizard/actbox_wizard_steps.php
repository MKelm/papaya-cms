<?php
/**
* Action box for project wizard steps
*
* @copyright 2002-2008 by papaya Software GmbH - All rights reserved.
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
* @subpackage Free-Wizard
* @version $Id: actbox_wizard_steps.php 34957 2010-10-05 15:57:41Z weinert $
*/

/**
* Basic class Action box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Action box for project wizard steps
*
* @package Papaya-Modules
* @subpackage Free-Wizard
*/
class actbox_wizard_steps extends base_actionbox {

  /**
  * Edit fields
  *
  * @var array $editFields
  */
  var $editFields = array(
    'title' => array('Box Title', 'isNoHTML', FALSE, 'input', 200),
    'page' => array('Page', 'isNum', TRUE, 'input', 5, '', '0'),
    'count' => array('Count', 'isNum', TRUE, 'input', 5, '', 1),
    'sort' => array('Sort', 'isNum', TRUE, 'translatedcombo',
      array(0 => 'Ascending', 1 => 'Descending', 2 => 'Random'))
  );

  /**
  * Connector of papaya wizard object
  *
  * @var connector_wizard $wizardObject
  */
  var $wizardObject;

  /**
  * Get parsed data
  *
  * @return string $result
  */
  function getParsedData() {
    // Initialize
    $this->setDefaultData();
    $result = '';
    include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
    $this->wizardObject = base_pluginloader::getPluginInstance(
      '606a3253d4fba59ef3131fcb8c1a5c34', $this
    );

    $wizardSteps = $this->wizardObject->getStepList();
    $currentStep = $this->wizardObject->getActualStep();
    $resolvedStep = $this->wizardObject->getResolvedStep();

    $result .= '<wizard-step-box>';
    if (!empty($this->data['title'])) {
      $result .= sprintf(
        '<title>%s</title>',
        papaya_strings::escapeHTMLChars($this->data['title'])
      );
    }
    $result .= sprintf(
      '<wizard-steps total="%d">',
      count($wizardSteps)
    );
    foreach ($wizardSteps as $stepNum => $arrStepData) {
      $result .= sprintf(
        '<step number="%d" title="%s" available="%s" href="%s" %s/>',
        $stepNum,
        papaya_strings::escapeHTMLChars($arrStepData['topic_title']),
        ($stepNum <= $resolvedStep + 1) ? 'yes' : 'no',
        ($stepNum <= $resolvedStep + 1) ? $this->getWeblink($arrStepData['topic_id']) : '',
        ($stepNum == $currentStep) ? 'selected="selected"' : ''
      );
    }
    $result .= '</wizard-steps>';
    $result .= '</wizard-step-box>';
    return $result;
  }
}
?>
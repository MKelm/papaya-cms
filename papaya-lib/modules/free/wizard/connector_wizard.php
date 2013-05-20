<?php
/**
* Papaya Wizard Connector module
*
* Sypnosis:
*
* include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
* $wizardObject = base_pluginloader::getPluginInstance('606a3253d4fba59ef3131fcb8c1a5c34', $this);
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
* @version $Id: connector_wizard.php 32994 2009-11-11 13:54:13Z weinert $
*/


/**
* Basic class plugin
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_plugin.php');

/**
* Papaya Wizard Connector module
*
* @package Papaya-Modules
* @subpackage Free-Wizard
*/
class connector_wizard extends base_plugin {

  /**
  * Base object of wizard module
  * @var base_wizard
  */
  var $wizardObject;

  /**
  * Create object of the base class of wizard module, if it not set.
  *
  * @see base_wizard::initializeWizard()
  */
  function initializeWizardObject() {
    // Check if the right object already exist
    if (!(
          isset($this->wizardObject) &&
          is_object($this->wizardObject) &&
          is_a($this->wizardObject, 'base_wizard')
        )) {
      // If object not exist, create it!
      include_once(dirname(__FILE__).'/base_wizard.php');
      $this->wizardObject = new base_wizard($this);
      $this->wizardObject->parentObj = &$this->parentObj->parentObj;
      $this->wizardObject->initializeWizard();
    }
  }

  /**
  * Check if all prior steps from the actual are resolved.
  *
  * @return boolean TRUE if permitted, else FALSE
  *
  * @see initializeWizardObject()
  * @see base_wizard::checkForStepPermission()
  */
  function checkForStepPermission() {
    $this->initializeWizardObject();
    $this->wizardObject->checkForStepPermission();
  }

  /**
  * Return a List of Wizard Steps.
  *
  * @return array Wizard Step Topics
  *
  * @see initializeWizardObject()
  * @see base_wizard::checkForStepPermission()
  */
  function getStepList() {
    $this->initializeWizardObject();
    return $this->wizardObject->wizardSteps;
  }

  /**
  * Return Number of the actual Step.
  *
  * @return integer Step Number
  *
  * @see initializeWizardObject()
  * @see base_wizard::checkForStepPermission()
  */
  function getActualStep() {
    $this->initializeWizardObject();
    return $this->wizardObject->currentStep;
  }

  /**
  * Return number of the highest resolved step.
  *
  * @return integer Step Number
  *
  * @see initializeWizardObject()
  */
  function getResolvedStep() {
    $this->initializeWizardObject();
    return $this->wizardObject->resolvedStep;
  }

  /**
  * Save the submitted data to wizard session.
  *
  * @param mixed $value data to Save
  * @param string $name key for data to save
  *
  * @see initializeWizardObject()
  * @see base_wizard::saveWizardData()
  */
  function saveWizardData($value, $name=NULL) {
    $this->initializeWizardObject();
    return $this->wizardObject->saveWizardData($value, $name);
  }

  /**
  * Returns Data, saved in wizard session. You can speciefie the data to load, when you
  * set the $name param
  *
  * @param string $name
  *
  * @see initializeWizardObject()
  * @see base_wizard::loadWizardData()
  */
  function getWizardData($name=NULL) {
    $this->initializeWizardObject();
    $this->wizardObject->loadWizardData();
    if (!empty($name) && isset($this->wizardObject->wizardData[$name])) {
      // If name is set, return data for this array key
      return $this->wizardObject->wizardData[$name];
    } else {
      // if no name param set, return whole data
      return $this->wizardObject->wizardData;
    }
  }

  /**
  * Clear all the wizard session data.
  *
  * @see initializeWizardObject()
  * @see base_wizard::clearWizardSession()
  */
  function clearWizardSession() {
    $this->initializeWizardObject();
    $this->wizardObject->clearWizardSession();
  }

  /**
  * Mark the actual step as resolved and redirect to the next step.
  *
  * @see initializeWizardObject()
  * @see base_wizard::resolveStep()
  */
  function resolveStep() {
    $this->initializeWizardObject();
    $this->wizardObject->resolveStep();
  }

  /**
  * Resets a special step identified by its uniqe identifier.
  *
  * @param integer $stepNumber
  *
  * @see initializeWizardObject()
  * @see base_wizard::revertStep()
  */
  function revertStep($stepNumber=NULL) {
    $this->initializeWizardObject();
    $this->wizardObject->revertStep($stepNumber);
  }

  /**
  * go back to previous step
  *
  * @see initializeWizardObject()
  * @see base_wizard::goBack()
  */
  function goBack() {
    $this->initializeWizardObject();
    $this->wizardObject->goBack();
  }

  /**
  * checks if current step is last step
  *
  * @return boolean result
  *
  * @see initializeWizardObject()
  */
  function checkIsLastStep() {
    $this->initializeWizardObject();
    if (count($this->wizardObject->wizardSteps) <= $this->wizardObject->currentStep) {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * checks if current step is first step
  *
  * @return boolean result
  *
  * @see initializeWizardObject()
  */
  function checkIsFirstStep() {
    $this->initializeWizardObject();
    if ($this->wizardObject->currentStep == 1) {
      return TRUE;
    }
    return FALSE;
  }
}
?>
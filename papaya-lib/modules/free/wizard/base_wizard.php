<?php
/**
* Papaya Wizard Base Module
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
* @subpackage Beta-Wiki
* @version $Id: base_wizard.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic database plugin
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');

/**
* baser module of the papaya wizard
*
* @package Papaya-Modules
* @subpackage Beta-Wiki*/
class base_wizard extends base_db {

  /**
  * Wizard Identifier for use of multiple wizards, if necessary
  * @var string $wizardParamName
  */
  var $wizardParamName;

  /**
  * Array with Data saved in each Step
  * @var array $wizardData
  */
  var $wizardData = array();

  /**
  * Array with topic Data for each step
  * @var array
  */
  var $wizardSteps;

  /**
  * Unique identifier of the current step
  * @var integer
  */
  var $currentStep;

  /**
  * Unique identifier of recently finished step
  * @var integer
  */
  var $resolvedStep;

  /**
  * Initialize WizardData.
  *
  * Data like the current step, the last resolved step and the step
  * list will be loaded. If there are no information in the current session
  * default values are set.
  *
  * @see loadStepList()
  * @see getWizardSessionData()
  * @see setWizardSessionData()
  */
  function initializeWizard() {
    // Load List of all steps from topic
    $this->loadStepList();

    // get last Resolved Step
    $resolvedStep = $this->getWizardSessionData('resolved_step');
    if (empty($resolvedStep)) {
      $resolvedStep = 0;
      $this->setWizardSessionData('resolved_step', $resolvedStep);
    }
    $this->resolvedStep = $resolvedStep;

    // get actual Step number
    $actualTopicId = $this->parentObj->topic['topic_id'];
    $this->currentStep = $this->arrTopicIdToStep[$actualTopicId];
  }

  /**
  * Merges two arrays tol one
  *
  * @param array $arr1
  * @param array $arr2
  * @return array result of the array merger
  */
  function extendsArray($arr1 = NULL, $arr2 = NULL) {
    if (!is_array($arr1)) {
      $arr1 = array();
    }
    if (!is_array($arr2)) {
      $arr2 = array();
    }
    return array_merge($arr1, $arr2);
  }

  /**
  * Load some data from Wizard Session
  *
  * @param string $name Identifies data to load
  * @return mixed Data stored in session
  *
  * @see getSessionValue()
  */
  function getWizardSessionData($name) {
    $sessionParamName = 'PAPAYA_WIZARD';
    if (!empty($this->wizardParamName)) {
      $sessionParamName .= strtoupper('_'.$this->wizardParamName);
    }
    $sessionData = $this->getSessionValue($sessionParamName);
    return (isset($sessionData[$name])) ? $sessionData[$name] : FALSE;
  }

  /**
  * Save some data to Wizard Session
  *
  * @param string $name Identifies data to save
  * @param mixed  $data    Data wich is stored in session
  * @return boolean TRUE on success, else FALSE
  *
  * @see getSessionValue()
  * @see setSessionValue()
  */
  function setWizardSessionData($name, $value) {
    $sessionParamName = 'PAPAYA_WIZARD';
    if (!empty($this->wizardParamName)) {
      $sessionParamName .= strtoupper('_'.$this->wizardParamName);
    }
    $sessionData = $this->getSessionValue($sessionParamName);
    $sessionData[$name] = $value;
    return $this->setSessionValue($sessionParamName, $sessionData);
  }

  /**
  * Loads the data, wich was saved in the wizard steps, from wizard session
  *
  * @return boolean TRUE on success, else FALSE
  *
  * @see getWizardSessionData()
  */
  function loadWizardData() {
    if ($this->wizardData = $this->getWizardSessionData('wizard_data')) {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Save the Data from a step input to the wizard session. If $value is an array and
  * $name is not given, the array will be merged with the existand data. If $name is set,
  * it will be used as the key for $value.
  *
  * @param mixed $value
  * @param string $name optional
  * @return boolean TRUE on success
  *
  * @see loadWizardData()
  * @see extendsArray()
  */
  function saveWizardData($value, $name=NULL) {
    $this->loadWizardData();
    if (!empty($name)) {
      // if name is set, it will be used as key in the data array
      $arrWizardData = $this->wizardData;
      $arrWizardData[$name] = $value;
    } elseif (is_array($value)) {
      // if no name is set, but data is an array it will be merged
      // with the existant wizard data
      $arrWizardData = $this->extendsArray(
        $this->wizardData,
        $value);
    }
    if (isset($arrWizardData)) {
      // If all ok, write new data to session
      $this->setWizardSessionData('wizard_data', $arrWizardData);
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Clear all the Data, stored in wizard session.
  *
  * It includes WizardData, the current Step, the last resolved step etc.
  *
  * @return mixed
  *
  * @see setSessionValue()
  */
  function clearWizardSession() {
    $sessionParamName = 'PAPAYA_WIZARD';
    if (!empty($this->wizardParamName)) {
      $sessionParamName .= strtoupper('_'.$this->wizardParamName);
    }
    return $this->setSessionValue($sessionParamName, '');
  }

  /**
  * Check if all prior steps from the actual are resolved.
  *
  * If the check fails, redirect to the first unresolved step
  *
  * @return boolean TRUE on success
  */
  function checkForStepPermission() {
    $this->resetForNewSession();
    if (!($this->currentStep <= $this->resolvedStep + 1)) {
      $this->redirectToStep($this->resolvedStep + 1);
      return FALSE;
    }
    return TRUE;
  }

  /**
  * Check if the session has been changed since the WizardObject has been used.
  *
  * If the session is new, clear the WizardData and redirect to the first step.
  *
  * @see getWizardSessionData()
  * @see clearWizardSession()
  * @see initializeWizard()
  * @see redirectToStep()
  * @see setWizardSessionData()
  */
  function resetForNewSession() {
    $wizardSessionId = $this->getWizardSessionData('wizard_session_id');
    if ($wizardSessionId !== FALSE) {
      if ($wizardSessionId !== session_id()) {
        $this->clearWizardSession();
        $this->initializeWizard();
        $this->redirectToStep(1);
      }
    }
    $this->setWizardSessionData('wizard_session_id', session_id());
  }

  /**
  * Load all wizard step topics.
  *
  * This method will load all siblings of the actual topic.
  * Optional you can submit a $root id. In this case all children of this page id will be
  * retrieved as wizard step topics.
  *
  * @param $integer $rootId Optional id of parent page of wizard steps
  * @return boolean TRUE on success, else FALSE
  *
  * @see base_topiclist::loadList()
  */
  function loadStepList($rootId=NULL) {
    // If no rootId set, get it from parentObj
    if (empty($rootId)) {
      $rootId = $this->parentObj->topic['prev'];
    }
    // Initialize topic list object
    include_once(PAPAYA_INCLUDE_PATH.'system/base_topiclist.php');
    $topicObject = new base_topiclist;
    $topicObject->databaseURI = $this->parentObj->databaseURI;
    $topicObject->databaseURIWrite = $this->parentObj->databaseURIWrite;
    $topicObject->tableTopics = $this->parentObj->tableTopics;
    $topicObject->tableTopicsTrans = $this->parentObj->tableTopicsTrans;
    // load topic list
    $topicObject->loadList(
      (int)$rootId,
      $this->parentObj->topic['TRANSLATION']['lng_id'],
      TRUE,
      0
    );
    if (!empty($topicObject->topics)) {
      //assign topic data to wizard steps
      $stepCounter = 1;
      foreach ($topicObject->topics as $topicId => $arrTopicData) {
        $this->wizardSteps[$stepCounter] = $arrTopicData;
        $this->arrTopicIdToStep[$arrTopicData['topic_id']] = $stepCounter;
        ++$stepCounter;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Increase the counter for resolved steps and redirect to next step topic
  *
  * @see setWizardSessionData()
  * @see redirectToStep()
  */
  function resolveStep() {
    if (isset($this->currentStep) && isset($this->resolvedStep)) {
      if ($this->currentStep > $this->resolvedStep) {
        $this->setWizardSessionData('resolved_step', $this->currentStep);
      }
    }
    if ($this->currentStep < count($this->wizardSteps)) {
      $this->redirectToStep($this->currentStep + 1);
    }
  }

  /**
  * Resets a special step identified by its uniqe identifier.
  *
  * @param integer $stepNumber
  *
  * @see setWizardSessionData()
  */
  function revertStep($stepNumber) {
    if (empty($stepNumber)) {
      $stepNumber = $this->currentStep;
    }
    $this->setWizardSessionData('resolved_step', $stepNumber - 1);
  }

  /**
  * Redirects to previous step
  *
  * @see redirectToStep()
  */
  function goBack() {
    if ($this->currentStep > 1) {
      $this->redirectToStep($this->currentStep - 1);
    }
  }

  /**
  * Redirect to specified wizard step page
  *
  * @param integer $step ID of step for redirect to
  */
  function redirectToStep($step) {
    if (is_array($this->wizardSteps) && count($this->wizardSteps) > 0) {
      $href = $this->getAbsoluteURL($this->wizardSteps[$step]['topic_id']);
      if (isset($GLOBALS['PAPAYA_PAGE']) && is_object($GLOBALS['PAPAYA_PAGE'])) {
        $GLOBALS['PAPAYA_PAGE']->sendHTTPStatus(200);
        $GLOBALS['PAPAYA_PAGE']->sendHeader("Location: ".$href);
        exit;
      }
    }
    return FALSE;
  }
}
?>
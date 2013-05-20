<?php
/**
* Page module - Contact management
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
* @subpackage _Base-Community
* @version $Id: content_contact.php 37772 2012-12-04 14:22:48Z smekal $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Page module - Contact management
*
* @package Papaya-Modules
* @subpackage _Base-Community
*/
class content_contact extends base_content {

  /**
  * Papaya database table languages
  * @var string $tableLng
  */
  var $tableLng = PAPAYA_DB_TBL_LNG;

  /**
  * Papaya database table surfer
  * @var string $tableSurfer
  */
  var $tableSurfer = PAPAYA_DB_TBL_SURFER;

  /**
  * Papaya database table surferpermlink
  * @var string $tableSurfer
  */
  var $tableLink = PAPAYA_DB_TBL_SURFERPERMLINK;

  /**
  * Papaya database table surfer data
  * @var string $tableData
  */
  var $tableData = PAPAYA_DB_TBL_SURFERDATA;

  /**
  * Papaya database table surfer data titles
  * @var string $tableDataTitles
  */
  var $tableDataTitles = PAPAYA_DB_TBL_SURFERDATATITLES;

  /**
  * Papaya database table surfer data classes
  * @var string $tableDataClasses
  */
  var $tableDataClasses = PAPAYA_DB_TBL_SURFERDATACLASSES;

  /**
  * Papaya database table surfer data class titles
  * @var string $tableDataClassTitles
  */
  var $tableDataClassTitles = PAPAYA_DB_TBL_SURFERDATACLASSTITLES;

  /**
  * Papaya database table surfer contact data
  * @var string $tableContactData
  */
  var $tableContactData = PAPAYA_DB_TBL_SURFERCONTACTDATA;

  /**
  * Papaya database table surfer contact public
  * @var string $tableContactPublic
  */
  var $tableContactPublic = PAPAYA_DB_TBL_SURFERCONTACTPUBLIC;

  /**
  * Papaya database table with language content for pages
  * @var string $tableTopicsTrans
  */
  var $tableTopicsTrans = PAPAYA_DB_TBL_TOPICS_TRANS;

  /**
  * Current Category
  * @var int $category
  */
  var $category = 0;

  /**
  * List of change requests
  * @var array $surferChangeRequests
  */
  var $surferChangeRequests = NULL;

  /**
  * Base surfers
  * @var object $baseSurfers surfer_admin
  */
  var $baseSurfers = NULL;

  /**
  * Input error
  * @var string $inputError
  */
  var $inputError = '';

  /**
  * Token array
  * @var token array $tokenArray
  */
  var $tokenArray = array();

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'General',
    'title' => array(
      'Title', 'isNoHTML', TRUE, 'input', 200, ''
    ),
    'Multipage' => array(
      'Multipage', 'isNum', TRUE, 'yesno', '', '', 1
    ),
    'Messages',
    'Confirm_Save' => array(
      'Confirm Save', 'isNoHTML', TRUE, 'input', 200, '', 'Saved'
    ),
    'Error_Input' => array(
      'Input Error', 'isNoHTML', TRUE, 'input', 200, '', 'Input Error'
    ),
    'Error_Mandatory' => array(
      'Input mandatory', 'isNoHTML', TRUE, 'input', 200, '', 'Input mandatory'
    ),
    'Error_No_User' => array(
      'No User Error', 'isNoHTML', TRUE, 'input', 200, '', 'No user error'
    ),
    'Captions',
    'Caption_User_Profile' => array(
      'User Profile', 'isNoHTML', TRUE, 'input', 200, '', 'User profile'
    ),
    'Caption_Publish' => array(
      'Publish by default', 'isNoHTML', TRUE, 'input', 200, '', 'Publish by default'
    ),
    'Caption_Publish_all' => array(
      'Publish all by default', 'isNoHTML', TRUE, 'input', 200, '', 'Publish all by default'
    ),
    'Caption_Yes' => array(
      'Yes', 'isNoHTML', TRUE, 'input', 200, '', 'Yes'
    ),
    'Caption_No' => array(
      'No', 'isNoHTML', TRUE, 'input', 200, '', 'No'
    )
  );

  /**
  * Content contact constructor
  *
  * @access public
  * @param object &$owner owner object
  * @param string $paramName optional, default 'cnt'
  */
  function __construct(&$owner, $paramName = 'cnt') {
    parent::__construct($owner, $paramName);
    if (!is_object($this->baseSurfers)) {
      include_once(dirname(__FILE__).'/base_surfers.php');
      $this->baseSurfers = new surfer_admin($this->msgs);
    }
    include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
    $this->surferObj = &base_surfer::getInstance();
    $this->tokenArray = $this->getSessionValue('PAPAYA_SESS_DIALOG_TOKENS');
  }

  /**
  * Set configuration data in object.
  *
  * Explicitly read data from topic_trans table
  * before calling parent's setData() method.
  *
  * @access public
  * @param string $xmlData XML-data string
  */
  function setData($xmlData) {
    $sql = 'SELECT topic_content
              FROM %s
             WHERE topic_id = %d
               AND lng_id = %d';
    $dbParams = array(
      $this->tableTopicsTrans,
      $this->parentObj->topicId,
      $this->parentObj->topic['TRANSLATION']['lng_id']
    );
    $res = $this->baseSurfers->databaseQueryFmt($sql, $dbParams);
    if ($xmlData = $res->fetchField()) {
      return parent::setData($xmlData);
    } else {
      return NULL;
    }
  }

  /**
  * Get data by writing data array to xml string.
  *
  * Explicitly push params into data
  * before calling parent's getData() method
  *
  * @access public
  * @return string $result XML-data string
  */
  function getData() {
    $this->data = $this->params;
    return parent::getData();
  }

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $this->setDefaultData();
    $this->initializeParams();
    // Assume that there is no category change
    $categoryChange = FALSE;
    // Check whether multi-paged layout was set
    if ($this->data['Multipage']) {
      // If yes, check whether there's a category parameter
      if (isset($this->params['Category'])) {
        // Is this an actual category change, including the first choice of a category?
        if (!isset($this->params['Category_old']) ||
            $this->params['Category'] != $this->params['Category_old']) {
          $categoryChange = TRUE;
        }
        if ($this->params['Category'] == -1) {
          // The special value -1 means all categories at once
          $this->category = -1;
        } else {
          // Check whether the requested category exists
          $sql = 'SELECT COUNT(*)
                    FROM %s
                   WHERE surferdataclass_id=%d';
          $res = $this->baseSurfers->databaseQueryFmt(
            $sql,
            array($this->tableDataClasses, $this->params['Category'])
          );
          if ($num = $res->fetchField()) {
            if ($num > 0) {
              // Set current category to that number
              $this->category = $this->params['Category'];
            }
          }
        }
      }
    } else {
      // Set category to "all categories" without choice
      $this->category = -1;
    }
    $this->baseLink = $this->getBaseLink();
    $result = '';
    if (!empty($this->data['title'])) {
      $result .= sprintf(
        '<title>%s</title>'.LF,
        papaya_strings::escapeHTMLChars($this->data['title'])
      );
    }
    $result .= '<contactdata>';
    include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
    $this->surferObj = &base_surfer::getInstance();
    if (is_object($this->surferObj) &&
        $this->surferObj->isValid == TRUE) {
      $this->initializeOutputForm();
      // Check data if save parameter is set,
      // but discard any other changes on category change
      if (!$categoryChange &&
          isset($this->params['save']) &&
          $this->params['save'] > 0 ) {
        if ($this->checkDialogInput()) {
          if ($this->saveContactData()) {
            $result .= sprintf(
              '<message>%s</message>',
              papaya_strings::escapeHTMLChars($this->data['Confirm_Save'])
            );
          } else {
            $result .= sprintf(
              '<message>%s</message>',
              papaya_strings::escapeHTMLChars($this->data['Error_Input'])
            );
          }
        } else {
          $result .= sprintf(
            '<message>%s</message>',
            papaya_strings::escapeHTMLChars($this->inputError)
          );
        }
      }
      $result .= $this->getOutputForm();
    } else {
      $result .= sprintf(
        '<message>%s</message>',
        papaya_strings::escapeHTMLChars($this->data['Error_No_User'])
      );
    }
    $result .= '</contactdata>';
    return $result;
  }

  /**
  * Check dialog input.
  *
  * @access public
  * @return boolean Status (is valid)
  */
  function checkDialogInput() {
    include_once(PAPAYA_INCLUDE_PATH.'system/sys_checkit.php');
    $checkit = new checkit();
    // Check whether we're in frontend to prevent incompatible
    // token check in backend
    $frontend = FALSE;
    if (isset($this->params['user']) && $this->params['user'] == 1) {
      $frontend = TRUE;
    }
    // Check token if in frontend
    if ($frontend && !$this->checkToken()) {
      $this->inputError = $this->data['Error_Input'];
      return FALSE;
    }
    // Get an array of lowerstring check function names
    $checkFunctions = get_class_methods('checkit');
    foreach ($checkFunctions as $idx=>$functionName) {
      $checkFunctions[$idx] = strtolower($functionName);
    }
    // Now, we need to get information whether a field is mandatory
    // and the lowerstring checkit functions from database
    $sql = "SELECT surferdata_name,
                   surferdata_mandatory,
                   surferdata_check
              FROM %s
             WHERE surferdata_available=1";
    $res = $this->baseSurfers->databaseQueryFmt($sql, $this->tableData);
    $fieldChecks = array();
    while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
      $fieldChecks[$row['surferdata_name']] = array(
        'mandatory' => $row['surferdata_mandatory'],
        'check' => strtolower($row['surferdata_check'])
      );
    }
    // Assume that all input is correct
    $result = TRUE;
    // Now check all form data against the appropriate checkit function
    foreach ($this->params as $fieldName => $val) {
      if (preg_match("/^contact_(.*)/", $fieldName, $matchData)) {
        $field = $matchData[1];
        // First check whether the field is mandatory
        if ($fieldChecks[$field]['mandatory']) {
          // Check whether the field is empty
          if (trim($val) == '') {
            // Report an error and set result to false
            $this->inputError = $this->data['Error_Mandatory'].': '.$field;
            $result = FALSE;
            break;
          }
        }
        // We already checked whether the field was required =>
        //    check fields with contents only
        if ($val != '') {
          // Get the check function to call for that field
          $checkFunction = $fieldChecks[$field]['check'];
          // For security reasons: Check, whether this is an
          // official check function
          if (in_array($checkFunction, $checkFunctions)) {
            $fResult = $checkit->$checkFunction($val);
            // If field is invalid, set return value to false
            // and add error message
            if (!$fResult) {
              $this->inputError = $this->data['Error_Input'].': '.$field;
              $result = FALSE;
              break;
            }
          } else {
            // This is not a function but a regexp,
            // so check it manually
            if (!preg_match($checkFunction, $val)) {
              $this->inputError = $this->data['Error_Input'].': '.$field;
              $result = FALSE;
              break;
            }
          }
        }
      }
    }
    return $result;
  }

  /**
  * Get output form, create XML for user profile form.
  *
  * @access public
  * @return string $dialogXML
  */
  function getOutputForm() {
    $dialogXML = $this->initializeOutputForm();
    return $dialogXML;
  }

  /**
  * Get token key.
  *
  * Similar to base_dialog::getTokenKey(),
  * except for the hard-coded dialog name.
  *
  * @access public
  * @return string Token key
  */
  function getTokenKey() {
    return get_class($this).'_'.$this->paramName.'_contact_dialog';
  }

  /**
  * Get token.
  *
  * @access public
  * @return string $token
  */
  function getToken() {
    srand((double)microtime() * 1000000);
    $token = md5(uniqid(rand()));
    $tokenKey = $this->getTokenKey();
    $tokenArray[$tokenKey] = $token;
    $this->setSessionValue('PAPAYA_SESS_DIALOG_TOKENS', $tokenArray);
    return $token;
  }

  /**
  * Check token.
  *
  * @access public
  * @return boolean Status (valid token)
  */
  function checkToken() {
    $tokenKey = $this->getTokenKey();
    if (isset($this->tokenArray[$tokenKey])) {
      if (isset($this->params['token']) && !empty($this->params['token'])) {
        if ($this->tokenArray[$tokenKey] == $this->params['token']) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
  * Initialize user profile form
  *
  * @access public
  * @return string xml
  */
  function initializeOutputForm() {
    $result = sprintf(
      '<dialog title="Surfer Contact Data" action="%s" caption-yes="%s" caption-no="%s" '.
        'width="100%%">'.LF,
      papaya_strings::escapeHTMLChars($this->baseLink),
      papaya_strings::escapeHTMLChars($this->data['Caption_Yes']),
      papaya_strings::escapeHTMLChars($this->data['Caption_No'])
    );
    // Add JavaScript to check/uncheck all publishing settings
    // (only when there's a category)
    if ($this->category) {
      $result .= sprintf(
        '<publish-script caption="%s">
           <script>
           <![CDATA[
           function checkAll(state) {
             els = document.contactform.getElementsByTagName("input");
             for (var i = 0; el = els[i]; i++) {
               if (el.name.match(/publish_/)) {
                 if (state) {
                   el.checked = (el.value == 1) ? "checked" : "";
                 } else {
                   el.checked = (el.value == 0) ? "checked" : "";
                 }
               }
             }
           }
           //]]>
           </script>
         </publish-script>'.LF,
        papaya_strings::escapeHTMLChars($this->data['Caption_Publish_all'])
      );
    }
    $result .= sprintf(
      '<input type="hidden" name="%s[save]" value="1"/>'.LF,
      papaya_strings::escapeHTMLChars($this->paramName)
    );
    $result .= sprintf(
      '<input type="hidden" name="%s[user]" value="1"/>'.LF,
      papaya_strings::escapeHTMLChars($this->paramName)
    );
    // Manually add a dialog token -- this form is too dynamic to inherit from base_dialog
    $token = $this->getToken();
    $result .= sprintf(
      '<input type="hidden" name="%s[token]" value="%s"/>'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($token)
    );
    // Save current category into a hidden field if available
    if ($this->data['Multipage'] && isset($this->params['Category'])) {
      $result .= sprintf(
        '<input type="hidden" name="%s[Category_old]" value="%s"/>'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($this->params['Category'])
      );
    }
    $result .= '<lines class="dialogMedium">'.LF;
    if (!(isset($this->outputDialog) && is_object($this->outputDialog))) {
      // Get current topic language
      $lng = $this->parentObj->topic['TRANSLATION']['lng_id'];
      // Create category selector for multi-paged layout
      if ($this->data['Multipage']) {
        $result .= '<line caption="Category" fid="category">'.LF;
        $result .= sprintf(
          '<select name="%s[Category]" fid="category" onchange="this.form.submit();">'.LF,
          papaya_strings::escapeHTMLChars($this->paramName)
        );
        // Include selection request if no category was chosen
        if (!$this->category) {
          $result .= '<option value="0">[Please select]</option>'.LF;
        }
        $sql = "SELECT sc.surferdataclass_id, sc.surferdataclass_order,
                       sct.surferdataclasstitle_name,
                       sct.surferdataclasstitle_lang
                  FROM %s AS sc, %s AS sct
                 WHERE sc.surferdataclass_id = sct.surferdataclasstitle_classid
                   AND sct.surferdataclasstitle_lang = %d
              ORDER BY sc.surferdataclass_order, surferdataclass_id";
        $dbParams = array(
          $this->tableDataClasses,
          $this->tableDataClassTitles,
          $lng
        );
        $res = $this->baseSurfers->databaseQueryFmt($sql, $dbParams);
        $rows = array();
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $rows[$row['surferdataclass_id']] = $row;
        }
        foreach ($rows as $row) {
          if (isset($this->params['Category']) &&
              $this->params['Category'] == $row['surferdataclass_id']) {
            $selected = ' selected="selected"';
          } else {
            $selected = '';
          }
          $result .= sprintf(
            '<option value="%d"%s>%s</option>'.LF,
            papaya_strings::escapeHTMLChars($row['surferdataclass_id']),
            $selected,
            papaya_strings::escapeHTMLChars($row['surferdataclasstitle_name'])
          );
        }
        if (isset($this->params['Category']) &&
            $this->params['Category'] == -1) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<option value="-1"%s>[All]</option>'.LF, $selected
        );
        $result .= '</select>'.LF;
        $result .= '</line>'.LF;
      }
      // Only display any form fields if a category is chosen
      if ($this->category) {
        // Create form from database
        $sql = "SELECT s.surferdata_id,
                       s.surferdata_name, s.surferdata_type,
                       s.surferdata_values, s.surferdata_class,
                       s.surferdata_order, s.surferdata_mandatory,
                       s.surferdata_needsapproval,
                       s.surferdata_approvaldefault,
                       sc.surferdataclass_order
                  FROM %s AS s, %s AS sc
                 WHERE s.surferdata_available=1
                   AND s.surferdata_class=sc.surferdataclass_id";
        $sqlParams = array($this->tableData, $this->tableDataClasses);
        $res = $this->baseSurfers->databaseQueryFmt($sql, $sqlParams);
        $rows = array();
        // Store all fields in an array -- limited to a certain
        // category if one is set
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          if ($this->category != -1 && $row['surferdata_class'] != $this->category) {
            continue;
          }
          array_push($rows, $row);
        }
        // Try to determine the field and class titles
        // in the current language
        // or use field names/category numbers as fallback
        foreach ($rows as $index => $row) {
          $isql = "SELECT surferdatatitle_field,
                          surferdatatitle_title,
                          surferdatatitle_lang
                     FROM %s
                    WHERE surferdatatitle_field = %d
                      AND surferdatatitle_lang = '%s'";
          $isqlParams = array(
            $this->tableDataTitles,
            $row['surferdata_id'],
            $lng
          );
          $ires = $this->baseSurfers->databaseQueryFmt($isql, $isqlParams);
          $title = '';
          if ($irow = $ires->fetchRow(DB_FETCHMODE_ASSOC)) {
            $title = $irow['surferdatatitle_title'];
          }
          if (trim($title) != '') {
            $rows[$index]['surferdata_title'] = $title;
          } else {
            $rows[$index]['surferdata_title'] =
              sprintf('TBD [%s]', $row['surferdata_name']);
          }
          $isql = "SELECT surferdataclasstitle_classid,
                          surferdataclasstitle_name,
                          surferdataclasstitle_lang
                     FROM %s
                    WHERE surferdataclasstitle_classid = %d
                      AND surferdataclasstitle_lang = '%s'";
          $isqlParams = array($this->tableDataClassTitles,
                              $row['surferdata_class'],
                              $lng
                             );
          $ires = $this->baseSurfers->databaseQueryFmt($isql, $isqlParams);
          $ctitle = '';
          if ($irow = $ires->fetchRow(DB_FETCHMODE_ASSOC)) {
            $ctitle = $irow['surferdataclasstitle_name'];
          }
          if (trim($ctitle) != '') {
            $rows[$index]['surferdata_classtitle'] = $ctitle;
          } else {
            $rows[$index]['surferdata_classtitle'] =
              sprintf('TBD [%d]', $row['surferdata_class']);
          }
        }
        $oldclass = '';
        foreach ($rows as $row) {
          // Display a category title if necessary
          if ($row['surferdata_classtitle'] != $oldclass) {
            $result .= sprintf(
              '<subtitle caption="%s"/>'.LF,
              papaya_strings::escapeHTMLChars($row['surferdata_classtitle'])
            );
            $oldclass = $row['surferdata_classtitle'];
          }
          // Get data for current field, if available
          $isql = "SELECT sc.surfercontactdata_value
                    FROM %s AS s, %s AS sc
                   WHERE s.surferdata_id = sc.surfercontactdata_property
                     AND sc.surfercontactdata_surferid = '%s'
                     AND s.surferdata_name = '%s'";
          $idbParams = array(
            $this->tableData,
            $this->tableContactData,
            $this->surferObj->surfer['surfer_id'],
            $row['surferdata_name']
          );
          $ires = $this->baseSurfers->databaseQueryFmt($isql, $idbParams);
          if ($val = $ires->fetchField()) {
            $data = $val;
          } else {
            $data = '';
          }
          // Get publishing data for current field, if available
          $isql = "SELECT sp.surfercontactpublic_public
                     FROM %s AS s, %s AS sp
                    WHERE s.surferdata_id = sp.surfercontactpublic_data
                      AND sp.surfercontactpublic_surferid = '%s'
                      AND sp.surfercontactpublic_partner = ''
                      AND s.surferdata_name = '%s'";
          $idbParams = array(
            $this->tableData,
            $this->tableContactPublic,
            $this->surferObj->surfer['surfer_id'],
            $row['surferdata_name']
          );
          $ires = $this->baseSurfers->databaseQueryFmt($isql, $idbParams);
          if ($val = $ires->fetchField()) {
            $public = $val;
          } else {
            $public = $row['surferdata_approvaldefault'];
          }
          // Create form field(s)
          $fieldName = 'contact_'.$row['surferdata_name'];
          switch ($row['surferdata_type']) {
          case 'input' :
            $result .= sprintf(
              '<line caption="%s" fid="%s">'.LF,
              papaya_strings::escapeHTMLChars($row['surferdata_title']),
              papaya_strings::escapeHTMLChars($fieldName)
            );
            if (isset($row['surferdata_values']) && is_int($row['surferdata_values'])) {
              $size = ' size="'.papaya_strings::escapeHTMLChars($row['surferdata_values']).'"';
            } else {
              $size = '';
            }
            $result .= sprintf(
              '<input type="text" name="%s[%s]" value="%s"%s/>',
              papaya_strings::escapeHTMLChars($this->paramName),
              papaya_strings::escapeHTMLChars($fieldName),
              papaya_strings::escapeHTMLChars($data),
              $size
            );
            break;
          case 'textarea' :
            $result .= sprintf(
              '<line caption="%s" fid="%s">'.LF,
              papaya_strings::escapeHTMLChars($row['surferdata_title']),
              papaya_strings::escapeHTMLChars($fieldName)
            );
            if (isset($row['surferdata_values']) && is_int($row['surferdata_values'])) {
              $trows = ' rows="'.papaya_strings::escapeHTMLChars($row['surferdata_values']).'"';
            } else {
              $trows = '';
            }
            $result .= sprintf(
              '<textarea name="%s[%s]"%s>%s</textarea>'.LF,
              papaya_strings::escapeHTMLChars($this->paramName),
              papaya_strings::escapeHTMLChars($fieldName),
              $trows,
              papaya_strings::escapeHTMLChars($data)
            );
            break;
          case 'radio' :
          case 'combo' :
          case 'checkbox' :
            $result .= sprintf(
              '<line caption="%s" fid="%s">'.LF,
              papaya_strings::escapeHTMLChars($row['surferdata_title']),
              papaya_strings::escapeHTMLChars($fieldName)
            );
            $result .= sprintf(
              '<%s>'.LF,
              papaya_strings::escapeHTMLChars($row['surferdata_type'])
            );
            if ($data) {
              $result .= sprintf(
                '<checked>%s</checked>'.LF,
                papaya_strings::escapeHTMLChars($data)
              );
            }
            $result .= sprintf(
              '<name>%s[%s]</name>'.LF,
              papaya_strings::escapeHTMLChars($this->paramName),
              papaya_strings::escapeHTMLChars($fieldName)
            );
            $result .= sprintf(
              '%s</%s>'.LF,
              papaya_strings::escapeHTMLChars($row['surferdata_values']),
              papaya_strings::escapeHTMLChars($row['surferdata_type'])
            );
            /* Sample XML format for choice types (combo, radio, checkbox)
             with multilingual captions (or the values themselves as a fallback):
            <name>paramName[field_title]</name>
            <checked>single</checked>
            <options>
              <value>
                <content>single</content>
                <captions>
                  <en-US>single</en-US>
                  <de-DE>Single</de-DE>
                  <fr-FR></caption>
                  <es-ES></caption>
                </captions>
              </value>
              <value>
                <content>married</content>
                <captions>
                  <en-US>married</en-US>
                  <de-DE>verheiratet</de-DE>
                  <es-ES>marido</es-ES>
                </captions>
              </value>
              <value>
                <content>divorced</content>
                <captions>
                  <en-US>divorced</en-US>
                  <de-DE>geschieden</de-DE>
                  <es-ES></caption>
                </captions>
              </value>
              <value><content>widow</content></value>
            </options>
            Will be wrapped by <radio>...</radio> and the like
            to generate the correct type of form field

            [To do: There used to be a convenient editor for structures
                    like this in backend, yet it's incompatible with
                    papaya's new layout...]
            */
          }
          if ($row['surferdata_needsapproval']) {
            $result .= sprintf(
              '<publish name="%s[publish_%s]" default="%s"/>',
              papaya_strings::escapeHTMLChars($this->paramName),
              papaya_strings::escapeHTMLChars($row['surferdata_name']),
              papaya_strings::escapeHTMLChars($public)
            );
          }
          $result .= '</line>'.LF;
        }
      }
    }
    $result .= '</lines>'.LF;
    $result .= '<dlbutton value="Save"/>'.LF;
    $result .= '</dialog>'.LF;
    return $result;
  }

  /**
  * Save changed profile.
  *
  * @access public
  * @return boolean Status (saved)
  */
  function saveContactData() {
    $result = FALSE;
    foreach ($this->params as $field => $value) {
      if (preg_match("/^contact_/", $field)) {
        $field = preg_replace("/^contact_/", "", $field);
        // Get field id
        $sql = "SELECT surferdata_id FROM %s
                 WHERE surferdata_name='%s'";
        $res = $this->baseSurfers->databaseQueryFmt(
          $sql,
          array($this->tableData, $field)
        );
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $id = $row['surferdata_id'];
          // Does this field exist yet?
          $sql = 'SELECT surfercontactdata_id FROM %s
                   WHERE surfercontactdata_property=%d
                     AND surfercontactdata_surferid="%s"';
          $res2 = $this->baseSurfers->databaseQueryFmt(
            $sql,
            array($this->tableContactData, $id, $this->surferObj->surfer['surfer_id'])
          );
          if ($row2 = $res2->fetchRow(DB_FETCHMODE_ASSOC)) {
            $result = TRUE;
            // Empty input?
            if (trim($value) == '') {
              // Delete existing record
              $this->baseSurfers->databaseDeleteRecord(
                $this->tableContactData,
                'surfercontactdata_id',
                $row2['surfercontactdata_id']
              );
            } else {
              // Update existing record
              $data = array('surfercontactdata_value' => $value);
              $this->baseSurfers->databaseUpdateRecord(
                $this->tableContactData,
                $data,
                'surfercontactdata_id',
                $row2['surfercontactdata_id']
              );
            }
          } elseif (trim($value) != '') {
            // insert new record
            $result = TRUE;
            $data = array('surfercontactdata_property' => $id,
              'surfercontactdata_value' => $value,
              'surfercontactdata_surferid' => $this->surferObj->surfer['surfer_id']);
            $this->baseSurfers->databaseInsertRecord(
              $this->tableContactData,
              'surfercontactdata_id',
              $data
            );
          }
        }
      } elseif (preg_match("/^publish_/", $field)) {
        $field = preg_replace("/^publish_/", "", $field);
        // Get field id
        $sql = "SELECT surferdata_id FROM %s
                 WHERE surferdata_name='%s'";
        $res = $this->baseSurfers->databaseQueryFmt(
          $sql,
          array($this->tableData, $field)
        );
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $id = $row['surferdata_id'];
          // Does approval value for this field exist yet?
          $sql = "SELECT surfercontactpublic_id
                    FROM %s
                   WHERE surfercontactpublic_surferid='%s'
                     AND surfercontactpublic_partner=''
                     AND surfercontactpublic_data=%d";
          $res2 = $this->baseSurfers->databaseQueryFmt(
            $sql,
            array(
              $this->tableContactPublic,
              $this->surferObj->surfer['surfer_id'],
              $id
            )
          );
          if ($contactId = $res2->fetchField()) {
            // Update existing value
            $data = array('surfercontactpublic_public' => $value);
            $this->baseSurfers->databaseUpdateRecord(
              $this->tableContactPublic,
              $data,
              'surfercontactpublic_id',
              $contactId
            );
          } else {
            // Insert new value
            $data = array(
              'surfercontactpublic_surferid' => $this->surferObj->surfer['surfer_id'],
              'surfercontactpublic_partner' => '',
              'surfercontactpublic_data' => $id,
              'surfercontactpublic_public' => $value
            );
            $this->baseSurfers->databaseInsertRecord(
              $this->tableContactPublic, 'surfercontactpublic_id', $data
            );
          }
        }
      }
    }
    return $result;
  }
}

?>

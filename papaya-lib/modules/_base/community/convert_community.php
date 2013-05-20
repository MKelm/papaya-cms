<?php
/**
* Community user conversion
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
* @version $Id: convert_community.php 36881 2012-03-26 09:12:08Z yurtsever $
*/

/**
* Community user management base class
*/
require_once(dirname(__FILE__).'/base_surfers.php');

/**
 * Community user conversion
 *
 * Changes surfer ids from numeric values to md5 hashes and modificate related surfer data.
 *
 * @package Papaya-Modules
 * @subpackage _Base-Community
 */
class convert_community extends surfer_admin {
  /**
  * Database table with old community data
  * @var string
  */
  var $tableSurferOld = '';

  /**
   * Constructor
   * @param base_errors $msgs Error messages object
   * @param string $paramName Parameter groupd name
   */
  function __construct(&$msgs, $paramName = "sadm") {
    parent::__construct($msgs, $paramName);
    $this->initializeParams('PAPAYA_SESS_'.$paramName);
    $this->tableSurferOld = PAPAYA_DB_TABLEPREFIX.'_surfer_old';
  }

  /**
   * PHP-4-constructor
   * @param base_errors $msgs Error messages object
   * @param string $paramName Parameter groupd name
   */
  function convert_community(&$msgs, $paramName = "sadm") {
    $this->__construct($msgs, $paramName);
  }

  /**
  * Write data into the surfer table
  *
  * @todo Add status return value.
  * @access private
  * @param array &$data Surfer's data
  */
  function _copySurferData(&$data) {
    // Convert salutations to genders
    foreach ($data as $key => $row) {
      $data[$key]['surfer_gender'] = ($row['surfer_salutation'] == 0) ? 'm' : 'f';
      unset($data[$key]['surfer_salutation']);
    }
    $this->databaseInsertRecords($this->tableSurfer, $data);
  }

  /**
  * The main worker method fetches all surfers with numeric ids,
  * generates a md5 hash id, saves additional surfer data as dynamic data and
  * eventually adds a modified surfer entry to the database.
  *
  * @todo Add status return value.
  * @access public
  */
  function convertCommunity() {
    $advanced = FALSE;
    // Check whether there is anything to do
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE LENGTH(surfer_id) != 32";
    $sqlParams = array($this->tableSurfer);
    $numSurfers = 0;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($num = $res->fetchField()) {
        $numSurfers = $num;
      }
    }
    // Do we have a surfer_old table?
    $allTables = $this->databaseQueryTableNames();
    if (in_array($this->tableSurferOld, $allTables)) {
      $advanced = TRUE;
      $this->addMsg(
        MSG_INFO, $this->_gt('surfer_old table found; working in advanced mode')
      );
    }
    // If the current surfer table contains no data to convert...
    if ($numSurfers == 0) {
      // Check instead whether we've got data in the surfer_old table
      if ($advanced) {
        $sql = "SELECT COUNT(*)
                  FROM %s";
        $sqlParams = array($this->tableSurferOld);
        if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
          if ($num = $res->fetchField()) {
            $numSurfers = $num;
          }
        }
      }
      if ($numSurfers == 0) {
        $this->addMsg(
          MSG_INFO, $this->_gt('Nothing to be done -- the community looks alright.')
        );
        return;
      }
    }
    $this->addMsg(MSG_INFO, sprintf($this->_gt('Converting %d surfers'), $numSurfers));
    // In advanced mode, we first need to copy the basic data
    // from the old table to the new table
    if ($advanced) {
      $sql = "SELECT surfer_id, surfer_handle, surfergroup_id,
                     surfer_password, surfer_givenname,
                     surfer_surname, surfer_email, surfer_salutation,
                     surfer_valid, surfer_lastlogin
                FROM %s";
      $sqlParams = array($this->tableSurferOld);
      if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
        $buffer = array();
        $buffCounter = 0;
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $buffer[] = $row;
          $buffCounter++;
          if ($buffCounter >= 100) {
            $this->_copySurferData($buffer);
            $buffer = array();
            $buffCounter = 0;
          }
        }
        if ($buffCounter > 0) {
          $this->_copySurferData($buffer);
        }
      }
      // Now go for dynamic data, if we've got any
      $sql = "SELECT surfer_id, surfer_email_alt,
                     surfer_phone, surfer_phone_alt, surfer_title,
                     surfer_company, surfer_address, surfer_postal, surfer_city,
                     surfer_webpage
                FROM %s";
      $sqlParams = array($this->tableSurferOld);
      // Assume that we haven't got any additional data
      $dynData = FALSE;
      $dynSurfers = array();
      if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          if ((isset($row['surfer_email_alt']) && trim($row['surfer_email_alt']) != '') ||
              (isset($row['surfer_phone']) && trim($row['surfer_phone']) != '') ||
              (isset($row['surfer_phone_alt']) && trim($row['surfer_phone_alt']) != '') ||
              (isset($row['surfer_title']) && trim($row['surfer_title']) != '') ||
              (isset($row['surfer_company']) && trim($row['surfer_company']) != '') ||
              (isset($row['surfer_address']) && trim($row['surfer_address']) != '') ||
              (isset($row['surfer_postal']) && trim($row['surfer_postal']) != '') ||
              (isset($row['surfer_city']) && trim($row['surfer_city']) != '') ||
              (isset($row['surfer_webpage']) && trim($row['surfer_webpage']) != '')) {
            $dynSurfers[$row['surfer_id']] = array(
              'basic_email_alt' => $row['surfer_email_alt'],
              'basic_phone' => $row['surfer_phone'],
              'basic_phone_alt' => $row['surfer_phone_alt'],
              'basic_title' => $row['surfer_title'],
              'basic_company' => $row['surfer_company'],
              'basic_address' => $row['surfer_address'],
              'basic_postal' => $row['surfer_postal'],
              'basic_city' => $row['surfer_city'],
              'basic_webpage' => $row['surfer_webpage']
            );
            $dynData = TRUE;
          }
        }
      }
      if ($dynData) {
        $this->addMsg(MSG_INFO, 'Converting additional data to dynamic data fields');
        // First create a category and fields
        // Get smallest available order number
        $sql = "SELECT MAX(surferdataclass_order)
                  FROM %s";
        $sqlParams = array($this->tableDataClasses);
        $order = 0;
        if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
          if ($num = $res->fetchField()) {
            $order = $num;
          }
        }
        $classData = array(
          'surferdataclass_perm' => 0,
          'surferdataclass_order' => $order + 1
        );
        $classId = $this->databaseInsertRecord(
          $this->tableDataClasses, 'surferdataclass_id', $classData
        );
        // Set a default name of basic_data for the new category
        $nameData = array(
          'surferdataclasstitle_classid' => $classId,
          'surferdataclasstitle_lang' => 1,
          'surferdataclasstitle_name' => 'basic_data'
        );
        $this->databaseInsertRecord(
          $this->tableDataClassTitles, 'surferdataclasstitle_id', $nameData
        );
        // Now for the fields
        $fields = array(
          array(
            'surferdata_name' => 'basic_email_alt',
            'surferdata_type' => 'input',
            'surferdata_values' => '100',
            'surferdata_check' => 'isEMail',
            'surferdata_class' => $classId,
            'surferdata_available' => 1
          ),
          array(
            'surferdata_name' => 'basic_phone',
            'surferdata_type' => 'input',
            'surferdata_values' => '100',
            'surferdata_check' => 'isPhone',
            'surferdata_class' => $classId,
            'surferdata_available' => 1
          ),
          array(
            'surferdata_name' => 'basic_phone_alt',
            'surferdata_type' => 'input',
            'surferdata_values' => '100',
            'surferdata_check' => 'isPhone',
            'surferdata_class' => $classId,
            'surferdata_available' => 1
          ),
          array(
            'surferdata_name' => 'basic_title',
            'surferdata_type' => 'input',
            'surferdata_values' => '100',
            'surferdata_check' => 'isNoHTML',
            'surferdata_class' => $classId,
            'surferdata_available' => 1
          ),
          array(
            'surferdata_name' => 'basic_company',
            'surferdata_type' => 'input',
            'surferdata_values' => '100',
            'surferdata_check' => 'isNoHTML',
            'surferdata_class' => $classId,
            'surferdata_available' => 1
          ),
          array(
            'surferdata_name' => 'basic_address',
            'surferdata_type' => 'input',
            'surferdata_values' => '100',
            'surferdata_check' => 'isNoHTML',
            'surferdata_class' => $classId,
            'surferdata_available' => 1
          ),
          array(
            'surferdata_name' => 'basic_postal',
            'surferdata_type' => 'input',
            'surferdata_values' => '100',
            'surferdata_check' => 'isNoHTML',
            'surferdata_class' => $classId,
            'surferdata_available' => 1
          ),
          array(
            'surferdata_name' => 'basic_city',
            'surferdata_type' => 'input',
            'surferdata_values' => '100',
            'surferdata_check' => 'isNoHTML',
            'surferdata_class' => $classId,
            'surferdata_available' => 1
          ),
          array(
            'surferdata_name' => 'basic_webpage',
            'surferdata_type' => 'input',
            'surferdata_values' => '100',
            'surferdata_check' => 'isHTTPX',
            'surferdata_class' => $classId,
            'surferdata_available' => 1
          )
        );
        $this->databaseInsertRecords($this->tableData, $fields);
        foreach ($dynSurfers as $id => $data) {
          $this->setDynamicData($id, $data);
        }
      }
    }
    // Now, we can change the surfer ids
    $this->addMsg(MSG_INFO, $this->_gt('Converting surfer ids'));
    $usedIds = array();
    if ($advanced) {
      $idMap = array();
    }
    $sql = "SELECT surfer_id
              FROM %s
             WHERE LENGTH(surfer_id) != 32";
    $sqlParams = array($this->tableSurfer);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($id = $res->fetchField()) {
        $idMap[$id] = '';
      }
      foreach ($idMap as $id => $irrelevant) {
        do {
          $newId = $this->createSurferId();
        } while (in_array($newId, $usedIds));
        $idMap[$id] = $newId;
        $usedIds[] = $newId;
      }
      foreach ($idMap as $oldId => $newId) {
        $this->databaseUpdateRecord(
          $this->tableSurfer,
          array('surfer_id' => $newId),
          'surfer_id',
          $oldId
        );
        if ($advanced && $dynData) {
          $this->databaseUpdateRecord(
            $this->tableContactData,
            array('surfercontactdata_surferid' => $newId),
            'surfercontactdata_surferid',
            $oldId
          );
        }
      }
    }
    $this->addMsg(MSG_INFO, $this->_gt('Conversion done'));
  }

  /**
  * Execute commands by command parameter.
  *
  * @todo Add status return value.
  */
  function execute() {
    if (isset($this->params['cmd']) && trim($this->params['cmd']) != '') {
      switch ($this->params['cmd']) {
      case 'do_convert' :
        if ($this->module->hasPerm(1)) {
          $this->convertCommunity();
        } else {
          $this->addMsg(
            MSG_ERROR,
            $this->_gt('You do not have permission to convert the community data')
          );
        }
        break;
      }
    }
  }

  /**
  * Get layout XML by command parameter.
  *
  * @todo Add status return value.
  */
  function get() {
    if (isset($this->params['cmd']) && trim($this->params['cmd']) != '') {
      switch ($this->params['cmd']) {
      case 'convert' :
        if ($this->module->hasPerm(1)) {
          $this->layout->add($this->confirmConvert());
        } else {
          $this->addMsg(
            MSG_ERROR,
            $this->_gt('You do not have permission to convert the community data')
          );
        }
        break;
      }
    }
  }

  /**
  * Setup and return a form to confirm a conversion.
  *
  * An administrator who wants to do the conversion
  * needs to accept the terms in this form.
  *
  * @access public
  * @return base_msgdialog $dialog Confirm question dialog
  */
  function confirmConvert() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $hidden = array(
      'cmd' => 'do_convert',
    );
    $text = 'Advanced conversion is available if you rename your existing ';
    $text .= 'surfer table to '.PAPAYA_DB_TABLEPREFIX.'_surfer_old before ';
    $text .= 'doing table conversion in the Modules or Install/Upgrade sections. ';
    $text .= <<<TEXT_END
Before you proceed, please understand that this conversion is an operation
that may severely damage your existing papaya installation:
Please check whether you use any extra modules with tables that make use of surfer ids.
These will be converted from automatically incremented integers to 32-digit hex strings.
So each link to surfer ids in other tables will break!
You've been warned.
Do you really want to convert the existing community?
TEXT_END;
    $msg = $this->_gt($text);
    $dialog = new base_msgdialog(
      $this, $this->paramName, $hidden, $msg, 'question'
    );
    $dialog->msgs = &$this->msgs;
    $dialog->buttonTitle = 'Convert';
    $dialog->baseLink = $this->baseLink;
    return $dialog->getMsgDialog();
  }

  /**
  * The main menu is added to the layout object.
  *
  * @todo Add status return value.
  */
  function getButtons() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    $toolbar = new base_btnbuilder;
    $toolbar->images = &$this->images;

    if ($this->module->hasPerm(1)) {
      // The convert button (the only one available here)
      $params = array(
        'cmd' => 'convert'
      );
      $toolbar->addButton(
        'Convert',
        $this->getLink($params),
        'actions-edit-convert',
        'Convert an old papaya community',
        FALSE
      );
      if ($str = $toolbar->getXML()) {
        $this->layout->addMenu(sprintf('<menu ident="%s">%s</menu>'.LF, 'edit', $str));
      }
    }
  }
}

?>

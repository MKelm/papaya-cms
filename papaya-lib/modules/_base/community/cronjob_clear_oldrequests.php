<?php
/**
* Cronjob module that deletes old registrations for surfers who never became valid,
* as well as expired change requests.
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
* @version $Id: cronjob_clear_oldrequests.php 35135 2010-11-12 16:09:18Z zerebecki $
*/

/**
* Basic class Cronjobs
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_cronjob.php');

/**
* Cronjob module that deletes old registrations for surfers who never became valid,
* as well as expired change requests.
*
* @package Papaya-Modules
* @subpackage _Base-Community
*/
class cronjob_clear_oldrequests extends base_cronjob {
  /**
  * Edit fields
  * @var array
  */
  var $editFields = array(
    'expired_since' => array ('Expired since (days)', 'isNum', TRUE, 'input', 14, '')
  );

  /**
  * Execute the cronjob
  *
  * The method itself is extremely short because it only calls
  * a utility method in surfer_admin
  *
  * @access public
  * @return integer 0
  */
  function execute() {
    include_once(dirname(__FILE__).'/base_surfers.php');
    $surfers = surfer_admin::getInstance();
    list($numSurferIds, $numChangeRequests) = $surfers->clearOldRegistrations(
      $this->data['expired_since'] * 86400
    );
    $this->logMsg(
      MSG_INFO,
      PAPAYA_LOGTYPE_SURFER,
      sprintf(
        'Cronjob has deleted %d registration(s) and %d change request(s)',
        $numSurferIds,
        $numChangeRequests
      )
    );
    // This always works, whether there are records to delete or not,
    // so we can return a hardcoded 0 (UNIX exit code for 'success')
    return 0;
  }

  /**
  * Check execution parameters
  *
  * @access public
  * @return boolean Execution possible?
  */
  function checkExecParams() {
    if (isset($this->data['expired_since']) && $this->data['expired_since'] > 0) {
      return TRUE;
    }
    return FALSE;
  }
}

<?php
/**
* Cronjob-module updates pagerating summary (caching)
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
* @package Papaya-Modules
* @subpackage Free-PageRating
* @version $Id: cronjob_pagerating.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class Cronjobs
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_cronjob.php');

/**
* Cronjob-module updates pagerating summary (caching)
*
* @package Papaya-Modules
* @subpackage Free-PageRating
*/
class cronjob_pagerating extends base_cronjob {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'No input necessary',
  );

  /**
  * Execute
  *
  * @access public
  * @return mixed integer or string
  */
  function execute() {
    include_once(dirname(__FILE__)."/base_pagerating.php");
    $pageratingObj = new base_pagerating;
    if ($pageratingObj->loadStatisticData()) {
      return 0;
    } else {
      return 'Statistics Data could not be updated.';
    }
  }

  /**
  * Check execution parameters
  *
  * @access public
  * @return boolean
  */
  function checkExecParams() {
    return TRUE;
  }

}

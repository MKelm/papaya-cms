<?php
/**
* Basic object of all time calculation modules
*
* Objects must inherit this class
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
* @version $Id: base_crontime.php 32574 2009-10-14 14:00:46Z weinert $
*/

/**
* Basic class plugin
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_plugin.php');

/**
* Basic object of all time calculation modules
*
* Objects must inherit this class
*
* @package Papaya
* @subpackage Modules
*/
class base_crontime extends base_plugin {
  /**
  * next point of time
  * 0 = stop execution
  *
  * @access public
  * @return string
  */
  function getNextDateTime($from) {
    return 0;
  }
}

?>
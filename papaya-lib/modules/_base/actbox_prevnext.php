<?php
/**
* Navigation box to navigate on the current level of the site tree (previous/next)
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
* @subpackage _Base
* @version $Id: actbox_prevnext.php 36224 2011-09-20 08:00:57Z weinert $
*/


/**
* Basic class Action box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Navigation box to navigate on the current level of the site tree (previous/next)
*
* @package Papaya-Modules
* @subpackage _Base
*/
class actionbox_prevnext extends base_actionbox {

  /**
  * more detailed cache dependencies
  * @var array
  */
  var $cacheDependency = array(
    'querystring' => FALSE,
    'page' => TRUE,
    'surfer' => TRUE
  );

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'sort' => array('Sort', 'isNum', TRUE, 'combo',
      array(0 => 'Ascending', 1 => 'Descending'), '', 0)
  );

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $this->setDefaultData();
    include_once(PAPAYA_INCLUDE_PATH.'system/base_topic_navigation.php');
    $this->map = new base_topic_navigation($this->parentObj, $this->data);
    return $this->map->getXML();
  }
}
?>
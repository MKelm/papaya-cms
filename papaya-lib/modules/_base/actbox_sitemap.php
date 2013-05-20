<?php
/**
* Navigation box
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
* @version $Id: actbox_sitemap.php 36224 2011-09-20 08:00:57Z weinert $
*/


/**
* Basic class action box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Navigation box
*
* @package Papaya-Modules
* @subpackage _Base
*/
class actionbox_sitemap extends base_actionbox {

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
    'sort' => array(
      'Sort', 'isNum', TRUE, 'combo', array(0 => 'Ascending', 1 => 'Descending'), '', 0
    ),
    'base_url' => array(
      'Basic URL', 'isHTTPX', FALSE, 'input', 500, '', ''
    ),
    'root' => array('Base', 'isNum', FALSE, 'pageid', 5, '', 0),
    'format' => array('View', 'isAlpha', TRUE, 'combo',
      array('breadcrumb' => 'breadcrumb', 'path' => 'path', 'static' => 'static'),
      '', 'path'),
    'forstart' => array('Offset', 'isNum', FALSE, 'input', 2, '', 1),
    'forend' => array('Depth', 'isNum', FALSE, 'input', 2, '', 2),
    'focus' => array('Focus', 'isAlpha', TRUE, 'combo',
      array('dyna' => 'relative', 'root' => 'absolute'), '', 'dyna'),
    'foclevels' => array('Focus levels', 'isNum', FALSE, 'input', 2, '', 0),
    'ignore_empty' => array('Ignore empty sitemap', 'isNum', TRUE, 'combo',
      array(0 => 'no', 1 => 'yes'), '', 1)
  );

  /**
  * Returns the sitemap xml for this navigation box as a string.
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $this->setDefaultData();
    include_once(PAPAYA_INCLUDE_PATH.'system/base_sitemap.php');
    $this->map = new base_sitemap($this->parentObj, $this->data, $this->data['base_url']);
    $result = $this->map->getXML();
    if (!empty($this->data['ignore_empty']) &&
        $this->data['ignore_empty'] == 1) {
      if (!empty($this->map->rootIds) &&
         is_array($this->map->rootIds) &&
         count($this->map->rootIds) > 0) {
        return $result;
      } else {
        return '';
      }
    }
    return $result;
  }
}

?>

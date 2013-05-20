<?php
/**
* action box for background images
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
* @subpackage _Base
* @version $Id: actbox_background_image.php 38230 2013-03-01 12:34:20Z weinert $
*/

/**
* Basic class action box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* action box for background images
*
* @package Papaya-Modules
* @subpackage _Base
*/
class actionbox_background_image extends base_actionbox {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'image' => array('Image', 'isSomeText', TRUE, 'imagefixed', 100, '', '')
  );

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    if (empty($this->data['image'])) {
      $result = '';
    } else {
      $result = sprintf(
        '<image src="%s" />'.LF,
        PapayaUtilStringXml::escapeAttribute(
          $this->getWebMediaLink($this->data['image'])
        )
      );
    }
    return $result;
  }

  /**
  * Get parsed attributes - this allows to use the image in the page template directly
  *
  * @access public
  * @return string
  */
  function getParsedAttributes() {
    $result = array();
    if (!empty($this->data['image'])) {
      $result['image'] = $this->getWebMediaLink($this->data['image']);
    }
    return $result;
  }
}
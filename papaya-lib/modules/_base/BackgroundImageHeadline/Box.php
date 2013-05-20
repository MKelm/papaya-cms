<?php
/**
* action box for images with an additional headline and configurable image alt text
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
* @version $Id: Box.php 36012 2011-08-02 06:33:27Z kersken $
*/

/**
* Basic class action box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* action box for images with an additional headline and configurable image alt text
*
* @package Papaya-Modules
* @subpackage _Base
*/
class BackgroundImageHeadlineBox extends base_actionbox {
  /**
  * Instance of the base class
  * @var BackgroundImageHeadlineBoxBase
  */
  private $_baseObject = NULL;

  /**
  * Edit fields for box configuration
  * @var array
  */
  public $editFields = array(
    'headline' => array('Headline', 'isNoHTML', TRUE, 'input', 100, '', ''),
    'image' => array('Image', 'isSomeText', TRUE, 'imagefixed', 100, '', ''),
    'alt' => array('Alt text', 'isNoHTML', TRUE, 'input', 100, '', '')
  );

  /**
  * Get XML output
  *
  * @return string XML
  */
  public function getParsedData() {
    $this->setDefaultData();
    $baseObject = $this->getBaseObject();
    $baseObject->setOwner($this);
    $baseObject->setBoxData($this->data);
    return $baseObject->getBoxXml();
  }

  /**
  * Set the instance of the base class to be used
  *
  * @param BackgroundImageHeadlineBoxBase $baseObject
  */
  public function setBaseObject($baseObject) {
    $this->_baseObject = $baseObject;
  }

  /**
  * Get (and, if necessary, initialize) an instance of the base class
  *
  * @return BackgroundImageHeadlineBoxBase
  */
  public function getBaseObject() {
    if (!is_object($this->_baseObject)) {
      include_once(dirname(__FILE__).'/Box/Base.php');
      $this->_baseObject = new BackgroundImageHeadlineBoxBase();
    }
    return $this->_baseObject;
  }
}

?>

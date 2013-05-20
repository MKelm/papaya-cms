<?php
/**
* Base class for the action box for images 
* with an additional headline and configurable image alt text
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
* @version $Id: Base.php 35085 2010-10-28 08:01:28Z kersken $
*/

/**
* Base class for the action box for images
* with an additional headline and configurable image alt text
*
* @package Papaya-Modules
* @subpackage _Base
*/
class BackgroundImageHeadlineBoxBase {
  /**
  * Owner object
  * @var BackgroundImageHeadlineBox
  */
  private $_owner = NULL;

  /**
  * Box configuration data
  * @var array
  */
  private $_data = array();

  /**
  * Set the owner object
  *
  * @param BackgroundImageHeadlineBox $owner
  */
  public function setOwner($owner) {
    $this->_owner = $owner;
  }

  /**
  * Set box configuration data
  *
  * @param array $data
   */
  public function setBoxData($data) {
    $this->_data = $data;
  }

  /**
  * Get XML outpupt for the box
  *
  * @return string XML
   */
  public function getBoxXml() {
    $result = '<logo>'.LF;
    $result .= sprintf(
      '<headline>%s</headline>'.LF,
      papaya_strings::escapeHTMLChars($this->_data['headline'])
    );
    $result .= sprintf(
      '<image src="%s" alt="%s" />'.LF,
      $this->_owner->getWebMediaLink($this->_data['image']),
      papaya_strings::escapeHTMLChars($this->_data['alt'])
    );
    $result .= '</logo>'.LF;
    return $result;
  }
}

?>

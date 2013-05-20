<?php
/**
* Abstract superclass for controls inside a panel.
*
* @copyright 2011 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Ui
* @version $Id: Panel.php 36477 2011-12-03 13:25:26Z weinert $
*/

/**
* Abstract superclass for controls inside a panel.
*
* @package Papaya-Library
* @subpackage Ui
*/
abstract class PapayaUiPanel extends PapayaUiControl {

  /**
  * Panel caption/title
  *
  * @var string
  */
  protected $_caption = '';

  /**
  * Panel caption/title
  *
  * @var PapayaUiToolbars
  */
  protected $_toolbars = NULL;

  /**
  * Append panel to output xml
  *
  * @param PapayaXmlElement $parent
  * @return PapayaXmlElement $panel
  */
  public function appendTo(PapayaXmlElement $parent) {
    $panel = $parent->appendElement('panel');
    if (!empty($this->_caption)) {
      $panel->setAttribute('title', (string)$this->_caption);
    }
    $this->toolbars()->appendTo($panel);
    return $panel;
  }

  /**
  * Set a caption for the panel
  *
  * @param PapayaUiString|string $caption
  */
  public function setCaption($caption) {
    $this->_caption = $caption;
  }

  /**
  * Toolbars for the four corners of the panel
  *
  * @param PapayaUiToolbars $toolbars
  */
  public function toolbars(PapayaUiToolbars $toolbars = NULL) {
    if (isset($toolbars)) {
      $this->_toolbars = $toolbars;
    }
    if (is_null($this->_toolbars)) {
      $this->_toolbars = new PapayaUiToolbars($this);
      $this->_toolbars->papaya($this->papaya());
    }
    return $this->_toolbars;
  }

}
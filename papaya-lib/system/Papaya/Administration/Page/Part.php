<?php
/**
* Administration page parts are interactive ui controls, with access to a toolbar.
*
* @copyright 2012 by papaya Software GmbH - All rights reserved.
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
* @subpackage Administration
* @version $Id: Part.php 38094 2013-02-07 17:11:36Z weinert $
*/

/**
* Administration page parts are interactive ui controls, with access to a toolbar.
*
* @package Papaya-Library
* @subpackage Administration
*/
abstract class PapayaAdministrationPagePart extends PapayaUiControlInteractive {

  private $_toolbar = NULL;

  /**
  * Toolbar Set, Getter/Setter
  *
  * @param PapayaUiToolbarSet $toolbarSet
  * @return PapayaUiToolbarSet
  */
  public function toolbar(PapayaUiToolbarSet $toolbar = NULL) {
    if (isset($toolbar)) {
      $this->_toolbar = $toolbar;
    } elseif (is_null($this->_toolbar)) {
      $this->_toolbar = new PapayaUiToolbarSet();
      PapayaUtilConstraints::assertInstanceOf('PapayaUiToolbarSet', $this->_toolbar);
      $this->_toolbar->papaya($this->papaya());
    }
    return $this->_toolbar;
  }
}

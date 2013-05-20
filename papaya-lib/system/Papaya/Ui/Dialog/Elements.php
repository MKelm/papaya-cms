<?php
/**
* A list of dialog elements
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Elements.php 37459 2012-08-22 19:40:01Z weinert $
*/

/**
* A list of dialog elements
*
* @package Papaya-Library
* @subpackage Ui
*/
abstract class PapayaUiDialogElements extends PapayaUiControlCollection {

  /**
  * Only PapayaUiDialogElement objects are allows in this list
  * @var string
  */
  protected $_itemClass = 'PapayaUiDialogElement';

  /**
  * Initialize object an set owner dialog if available.
  *
  * @param PapayaUiDialog $dialog
  */
  public function __construct(PapayaUiDialog $dialog = NULL) {
    if (isset($dialog)) {
      $this->owner($dialog);
    }
  }

  /**
  * Collect data from elements (buttons/fields)
  */
  public function collect() {
    foreach ($this->_items as $item) {
      $item->collect();
    }
  }
}
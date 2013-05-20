<?php
/**
* A field that output a message inside the dialog
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
* @version $Id: Message.php 37439 2012-08-20 16:04:40Z weinert $
*/

/**
* A field that output a message inside the dialog
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFieldMessage extends PapayaUiDialogFieldInformation {

  /**
  * Message image
  *
  * @var string
  */
  private $_images = array(
    PapayaMessage::TYPE_INFO => 'status-dialog-information',
    PapayaMessage::TYPE_WARNING => 'status-dialog-warning',
    PapayaMessage::TYPE_ERROR => 'status-dialog-error'
  );

  /**
  * Create object and assign needed values
  *
  * @param string|PapayaUiString $message
  * @param string $image
  */
  public function __construct($severity, $message) {
    $severity = isset($this->_images[$severity]) ? $severity : PapayaMessage::TYPE_INFO;
    parent::__construct($message, $this->_images[$severity]);
  }
}
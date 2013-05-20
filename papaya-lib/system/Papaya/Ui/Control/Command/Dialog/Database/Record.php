<?php
/**
* A command that executes a dialog. After dialog creation, and after successfull/failed execution
* callbacks are executed. This class adds record handling
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
* @version $Id: Record.php 37606 2012-10-26 15:46:42Z weinert $
*/

/**
* A command that executes a dialog. After dialog creation, and after successfull/failed execuution
* callbacks are executed. This class adds record handling.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiControlCommandDialogDatabaseRecord extends PapayaUiControlCommandDialog {

  private $_record = NULL;

  /**
   * This dialog command uses database record objects
   *
   * @param PapayaDatabaseInterfaceRecord $record
   */
  public function __construct(PapayaDatabaseInterfaceRecord $record) {
    $this->record($record);
  }

  /**
   * Getter/Setter for the database record
   *
   * @param PapayaDatabaseInterfaceRecord $record
   */
  public function record(PapayaDatabaseInterfaceRecord $record = NULL) {
    if (isset($record)) {
      $this->_record = $record;
    }
    return $this->_record;
  }
}
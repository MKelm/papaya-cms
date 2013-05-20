<?php
/**
* Field factory profiles for a rte field using a simpler configuration.
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
* @subpackage Ui
* @version $Id: Individual.php 37402 2012-08-15 14:52:17Z weinert $
*/

/**
* Field factory profiles for a rte field using a simpler configuration wiht less elements.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFieldFactoryProfileRichtextIndividual
  extends PapayaUiDialogFieldFactoryProfileRichtext {

  /**
   * @see PapayaUiDialogFieldFactoryProfile::getField()
   * @return PapayaUiDialogFieldRichtext
   */
  public function getField() {
    $field = parent::getField();
    $field->setRteMode(PapayaUiDialogFieldTextareaRichtext::RTE_INDIVIDUAL);
    return $field;
  }
}
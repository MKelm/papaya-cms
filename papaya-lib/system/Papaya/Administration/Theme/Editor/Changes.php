<?php
/**
* Main part of the theme sets editor (dynamic values for a theme)
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
* @subpackage Administration
* @version $Id: Changes.php 37606 2012-10-26 15:46:42Z weinert $
*/

/**
* Main part of the theme sets editor (dynamic values for a theme)
*
* @package Papaya-Library
* @subpackage Administration
*/
class PapayaAdministrationThemeEditorChanges extends PapayaAdministrationPagePart {

  private $_commands = NULL;
  private $_toolbarSet = NULL;
  /**
   * @var PapayaContentThemeSet
   */
  private $_themeSet = NULL;

  /**
  * Append changes commands to parent xml element
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $parent->append($this->commands());
  }

  /**
  * Commands, actual actions
  *
  * @param PapayaUiControlCommandController $commands
  * @return PapayaUiControlCommandController
  */
  public function commands(PapayaUiControlCommandController $commands = NULL) {
    if (isset($commands)) {
      $this->_commands = $commands;
    } elseif (is_null($this->_commands)) {
      $this->_commands = new PapayaUiControlCommandController('cmd');
      $this->_commands->owner($this);
      $this->_commands['set_edit'] =
        $command = new PapayaAdministrationThemeEditorChangesSetChange($this->themeSet());
      $this->_commands['set_delete'] =
        $command = new PapayaAdministrationThemeEditorChangesSetRemove($this->themeSet());
      $this->_commands['values_edit'] =
        $command = new PapayaAdministrationThemeEditorChangesDialog($this->themeSet());
    }
    return $this->_commands;
  }

  /**
   * The theme set the the database record wrapper object.
   *
   * @param PapayaContentThemeSet $handler
   * @return PapayaContentThemeSet
   */
  public function themeSet(PapayaContentThemeSet $themeSet = NULL) {
    if (isset($themeSet)) {
      $this->_themeSet = $themeSet;
    } elseif (NULL === $this->_themeSet) {
      $this->_themeSet = new PapayaContentThemeSet();
    }
    return $this->_themeSet;
  }
}